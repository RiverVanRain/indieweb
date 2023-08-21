<?php
/**
 * IndieWeb
 * @author Nikolai Shcherbin
 * @license GNU Affero General Public License version 3
 * @copyright (c) Nikolai Shcherbin 2022
 * @link https://wzm.me
**/

namespace Elgg\IndieWeb\IndieAuth\Controller;

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Rsa\Sha512;
use Elgg\IndieWeb\IndieAuth\Entity\IndieAuthAuthorizationCode;
use Elgg\IndieWeb\IndieAuth\Entity\IndieAuthToken;

class TokenController {
	/**
	* The parameters needed for a token request.
	*
	* @var array
	*/
	static $token_parameters = [
		'code',
		'me',
		'redirect_uri',
		'client_id',
		'grant_type',
		'code_verifier',
	];
	
	/**
	* Routing callback: token endpoint
	*
	* @return array|\Symfony\Component\HttpFoundation\Response
	*/
	public function __invoke(\Elgg\Request $request) {
		// Early return when internal server is not enabled
		if (!(bool) elgg_get_plugin_setting('enable_indieauth_endpoint', 'indieweb')) {
			throw new \Elgg\Exceptions\Http\PageNotFoundException();
		}
		
		if ((bool) elgg_get_plugin_setting('enable_indieauth_endpoint', 'indieweb')) {
			elgg_set_http_header('Link: <' . elgg_generate_url('indieauth:token') . '>; rel="token_endpoint"');
		} else {
			elgg_set_http_header('Link: <' . elgg_get_plugin_setting('indieauth_external_endpoint', 'indieweb', 'https://tokens.indieauth.com/token') . '>; rel="token_endpoint"');
		}
		
		/** @var \Elgg\IndieWeb\IndieAuth\Client\IndieAuthClient $indieAuthClient */
		$indieAuthClient = elgg()->indieauth;

		// GET request, verify token
		if ($request->getMethod() === 'GET') {
			$auth_header = $indieAuthClient->getAuthorizationHeader($request->getHttpRequest());
			if (!$auth_header) {
				elgg_log('Token controller: Missing Authorization Header', 'ERROR');
				return elgg_error_response('Missing Authorization Header', REFERRER, 401);
			}

			$response = '';
			$response_code = 404;
			if ($indieAuthClient->isValidToken($auth_header)) {
				if ($target_id = $indieAuthClient->checkAuthor()) {
					$response_code = 200;
					$token = $indieAuthClient->getToken();
					$response = [
						'me' => $token->getMe(),
						'client_id' => $token->getClientId(),
						'scope' => $token->getScopesAsString(),
						'profile' => $this->getProfile($request, $target_id)
					];
				}
			}
			
			return elgg_ok_response($response, '', REFERRER, $response_code);
		}

		// If not a get request, and not POST either, bail out
		if ($request->getMethod() != 'POST') {
			throw new \Elgg\Exceptions\Http\PageNotFoundException();
		}

		// Token revocation request
		if ($request->getHttpRequest()->request->has('action') && $request->getParam('action') === 'revoke' && ($token = $request->getParam('token'))) {
			$indieAuthClient->revokeToken($token);
			return elgg_ok_response([], '', REFERRER, 200);
		}

		// Access token request
		$params = [];
		$valid_request = true;
		self::validateTokenRequestParameters($request, $reason, $valid_request, $params);
		
		if (!$valid_request) {
			elgg_log('Token controller: Missing or invalid parameters to obtain code: '. $reason, 'ERROR');
			return elgg_error_response('Missing or invalid parameters', REFERRER, 400);
		}

		// Get authorization code
		/** @var \Elgg\IndieWeb\IndieAuth\Entity\IndieAuthAuthorizationCode $authorization_code */
		$code = $params['code'];
		$authorization_code = elgg_call(ELGG_IGNORE_ACCESS, function () use ($code) {
			$codes = elgg_get_entities([
				'type' => 'object',
				'subtype' => IndieAuthAuthorizationCode::SUBTYPE,
				'limit' => false,
				'metadata_name_value_pairs' => [
					'name' => 'code',
					'value' => $code,
				],
			]);
				
			return count($codes) === 1 ? array_shift($codes) : null;
		});
		
		if (!$authorization_code instanceof IndieAuthAuthorizationCode) {
			elgg_log('Token controller: No Authorization code found for ' . $params['code'], 'ERROR');
			return elgg_error_response('Authorization code not found', REFERRER, 404);
		}

		if (!$authorization_code->isValid()) {
			elgg_log('Token controller: Authorization expired for ' . $params['code'], 'ERROR');
			return elgg_error_response('Authorization code expired', REFERRER, 403);
		}

		// Validate redirect_uri, me and client_id, and scope is not empty
		if ($authorization_code->getClientId() != $params['client_id']) {
			elgg_log('Token controller: Client ID does not match ' . $params['client_id'], 'ERROR');
			return elgg_error_response('Client ID does not match', REFERRER, 400);
		}
		
		if ($authorization_code->getRedirectURI() != $params['redirect_uri']) {
			elgg_log('Token controller: Redirect URI does not match ' . $params['redirect_uri'], 'ERROR');
			return elgg_error_response('Redirect URI does not match', REFERRER, 400);
		}
		
		// Hack: For some reasons many clients don't send 'me' parameter
		//if ($authorization_code->getMe() != $params['me']) {
		if ($authorization_code->getMe() != elgg_get_site_url()) {	
			//elgg_log('Token controller: Me does not match ' . $params['me'], 'ERROR');
			elgg_log('Token controller: Me does not match ' . elgg_get_site_url(), 'ERROR');
			return elgg_error_response('Me does not match', REFERRER, 400);
		}
		
		if (empty($authorization_code->getScopes())) {
			elgg_log('Token controller: Scope is empty, can not issue access token', 'ERROR');
			return elgg_error_response('Scope is empty, can not issue access token', REFERRER, 400);
		}

		// Validate PKCE if available
		if ($code_challenge = $authorization_code->getCodeChallenge()) {
			if (empty($params['code_verifier'])) {
				elgg_log('Token controller: No code verifier found to verify the code challenge', 'ERROR');
				return elgg_error_response('No code verifier found to verify the code challenge', REFERRER, 400);
			} else if (!$this->verifyPKCE($code_challenge, $params['code_verifier'], $authorization_code->getCodeChallengeMethod())) {
				elgg_log('Token controller: Failed PKCE validation: verifier: ' . $params['code_verifier'] . ' - challenge: ' . $authorization_code->getCodeChallenge() . ' - method: ' . $authorization_code->getCodeChallengeMethod(), 'ERROR');
				return elgg_error_response('PKCE validation failed', REFERRER, 400);
			}
		}

		// Good to go, create a token
		try {
			$created = new \DateTimeImmutable();
			$created_value = $created->getTimestamp();
		} catch (\Exception $ignored) {
			$created = time();
			$created_value = $created;
		}
		
		$access_token = _elgg_services()->crypto->getRandomString(120);
		$signer = new Sha512();

		$key = Key\InMemory::plainText(file_get_contents(elgg_get_plugin_setting('indieauth_private_key', 'indieweb')));
		$config = Configuration::forSymmetricSigner($signer, $key);
		$JWT = $config->builder()
			->issuedBy($request->getHttpRequest()->getSchemeAndHttpHost())
			->permittedFor($authorization_code->getClientId())
			->identifiedBy($access_token)
			->withHeader('jti', $access_token)
			->issuedAt($created)
			->withClaim('target_id', $authorization_code->getOwnerId())
			->getToken($config->signer(), $config->signingKey());

		$values = [
			'expire' => 0,
			'changed' => 0,
			'created' => $created_value,
			'access_token' => $access_token,
			'client_id' => $authorization_code->getClientId(),
			'me' => $authorization_code->getMe(),
			'target_id' => $authorization_code->getOwnerId(),
			'scope' => implode(' ', $authorization_code->getScopes()),
		];
		
		$new_token = elgg_call(ELGG_IGNORE_ACCESS, function () use ($authorization_code, &$values) {
			$token = new \Elgg\IndieWeb\IndieAuth\Entity\IndieAuthToken();
			$token->access_id = ACCESS_PRIVATE;
			$token->owner_guid = elgg_get_site_entity()->guid;
			$token->container_guid = elgg_get_site_entity()->guid;
			
			foreach($values as $name => $value) {
				$token->setMetadata($name, $value);
			}
			
			$token->setMetadata('status', 1);
			
			if (!$token->save()) {
				$token->delete();
				elgg_log('IndieAuthToken creation failed', 'ERROR');
				return elgg_error_response('IndieAuthToken creation failed', REFERRER, 400);
			}
			
			// Remove old code
			$authorization_code->delete();
			
			return $token;
		});

		$data = [
			'me' => elgg_get_site_url(), //$params['me'],
			'token_type' => 'Bearer',
			'scope' => $new_token->getScopesAsString(),
			'access_token' => $JWT->toString(),
			'profile' => $this->getProfile($request, $new_token->getOwnerId()),
		];
		
		return elgg_ok_response($data);
	}
	
	/**
	* Check request parameters for an IndieAuth code request.
	*
	* @param $request
	* @param $reason
	* @param $valid_request
	* @param $params
	*/
	public static function validateTokenRequestParameters(\Elgg\Request $request, &$reason, &$valid_request, &$params = null) {
		foreach (self::$token_parameters as $parameter) {
			$check = $request->getParam($parameter);
			
			// For some reasons many clients don't send 'me' parameter
			if (empty($check) && !in_array($parameter, ['me', 'code_verifier'])) {
				$reason = "$parameter is empty";
				$valid_request = false;
				break;
			} else if ($parameter === 'grant_type' && $check != 'authorization_code') {
				$reason = "grant_type is not authorization_code";
				$valid_request = false;
				break;
			}

			// Store the params
			if (is_array($params) && !empty($check)) {
				$params[$parameter] = $check;
			}
		}
	}
	
	/**
	* Returns profile information.
	*
	* @param \Symfony\Component\HttpFoundation\Request $request
	* @param $target_id
	*
	* @return \stdClass
	*
	*/
	protected function getProfile(\Elgg\Request $request, int $target_id = 0) {
		$profile = [];
		
		$account = get_entity($target_id);

		if ($account instanceof \ElggUser) {
			$profile['name'] = $account->getDisplayName();
			$profile['url'] = $account->getURL();

			$profile['photo'] = $account->getIconUrl([
				'type' => 'icon',
				'size' => 'large',
			]);
		}

		return $profile;
	}
	
	/**
    * Verifies the code challenge.
	*
	* @see https://tools.ietf.org/html/rfc7636#appendix-A
	*
	* @param $code_challenge
	* @param $code_verifier
	* @param $method
	*
	* @return bool
	*/
	protected function verifyPKCE($code_challenge, $code_verifier, $method) {
		if ('S256' === $method) {
			$code_verifier = rtrim(strtr(base64_encode(hash('sha256', $code_verifier, true)), '+/', '-_' ), '=');
		}
		
		return (0 === strcmp($code_challenge, $code_verifier));
	}

}
