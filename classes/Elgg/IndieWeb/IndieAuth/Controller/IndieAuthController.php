<?php
/**
 * IndieWeb
 * @author Nikolai Shcherbin
 * @license GNU Affero General Public License version 3
 * @copyright (c) Nikolai Shcherbin 2022
 * @link https://wzm.me
**/

namespace Elgg\IndieWeb\IndieAuth\Controller;

use GuzzleHttp\Client;
use Elgg\IndieWeb\IndieAuth\Entity\IndieAuthAuthorizationCode;

class IndieAuthController {
	/**
	* The parameters needed for a authentication verification request.
	*
	* @var array
	*/
	static $auth_verify_parameters = [
		'client_id',
		'code',
		'redirect_uri',
	];

	/**
	* Routing callback: authorization screen
	*
	* @param \Symfony\Component\HttpFoundation\Request $request
	*/
	public function __invoke(\Elgg\Request $request) {
		// Early return when internal server is not enabled
		if (!(bool) elgg_get_plugin_setting('enable_indieauth_endpoint', 'indieweb')) {
			throw new \Elgg\Exceptions\Http\PageNotFoundException();
		}
		
		if ((bool) elgg_get_plugin_setting('enable_indieauth_endpoint', 'indieweb')) {
			elgg_set_http_header('Link: <' . elgg_generate_url('indieauth:auth') . '>; rel="authorization_endpoint"');
		} else {
			elgg_set_http_header('Link: <' . elgg_get_plugin_setting('indieauth_external_auth', 'indieweb', 'https://indieauth.com/auth') . '>; rel="authorization_endpoint"');
		}

		// Get the method
		$method = $request->getMethod();

		// POST request: verify a authentication request.
		// See https://indieauth.spec.indieweb.org/#authorization-code-verification
		if ($method === 'POST') {
			$reason = '';
			$params = [];
			$valid_request = true;
			
			self::validateAuthenticationRequestParameters($request, $reason, $valid_request, $params);
			
			if (!$valid_request) {
				elgg_log('IndieAuth controller: Missing or invalid parameters to authentication request: ' . $reason, 'ERROR');
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
				elgg_log('IndieAuth controller: No authorization code found for '. $params['code'], 'ERROR');
				return elgg_error_response('Authorization code not found', REFERRER, 404);
			}

			if (!$authorization_code->isValid()) {
				elgg_log('IndieAuth controller: Authorization expired for '. $params['code'], 'ERROR');
				return elgg_error_response('Authorization code expired', REFERRER, 403);
			}

			// Verify the data from the request matches with the stored data
			$stored_data = [];
			foreach (self::$auth_verify_parameters as $parameter) {
				$stored = $authorization_code->$parameter;
				$stored_data[] = $stored;
				if ($stored != $params[$parameter]) {
					$valid_request = false;
					break;
				}
			}

			if (!$valid_request) {
				elgg_log('IndieAuth controller: Stored values do not match with request values: '. $stored_data . ' - ' . print_r($params, 1), 'ERROR');
				return elgg_error_response('Session and request values do not match', REFERRER, 400);
			}

			// Good to go
			$response = [
				'me' => $authorization_code->getMe(),
				'profile' => $this->getProfile($request, $authorization_code->getOwnerId()),
			];
			
			// Remove old code
			$authorization_code->delete();
			
			return elgg_ok_response($response);
		}

		// GET request: redirect to the auth/form url. We work like this since submitting the 'Authorize' form, we get into a POST request, 
		// which gets into this controller again and we want to verify authorization requests here as well.
		// See https://indieauth.spec.indieweb.org/#authentication-request
		if ($method === 'GET') {
			$response = elgg_generate_url('indieauth:auth:form');
			$response .= '?redirect_uri=' . $request->getParam('redirect_uri');
			$response .= '&client_id=' . $request->getParam('client_id');
			$response .= '&me=' . $request->getParam('me');
			$response .= '&state=' . $request->getParam('state');
			$response .= '&scope=' . $request->getParam('scope');
			return elgg_redirect_response($response);
		}
	}

	/**
	* Validate authentication verification request.
	*
	* @param \Symfony\Component\HttpFoundation\Request $request
	* @param $reason
	* @param $valid_request
	* @param $params
	*/
	public static function validateAuthenticationRequestParameters(\Elgg\Request $request, &$reason, &$valid_request, &$params) {
		foreach (self::$auth_verify_parameters as $parameter) {
			$check = $request->getParam($parameter);
			
			if (empty($check)) {
				$reason = "$parameter is empty";
				$valid_request = false;
				break;
			}

			// Store the params
			if (is_array($params)) {
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
	
	/**
	* {@inheritdoc}
	*/
	public function http_client($options = []) {
		$config = [
			'verify' => true,
			'timeout' => 30,
		];
		$config = $config + $options;
		
		return new Client($config);
	}

}
