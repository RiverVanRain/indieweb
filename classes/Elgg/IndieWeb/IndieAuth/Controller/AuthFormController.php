<?php
/**
 * IndieWeb
 * @author Nikolai Shcherbin
 * @license GNU Affero General Public License version 3
 * @copyright (c) Nikolai Shcherbin 2022
 * @link https://wzm.me
**/

namespace Elgg\IndieWeb\IndieAuth\Controller;

class AuthFormController {
	/**
	* The parameters needed for an authorize request.
	*
	* @var array
	*/
	static $auth_parameters = [
		'response_type',
		'redirect_uri',
		'client_id',
		'me',
		'scope',
		'state',
		'code_challenge',
		'code_challenge_method',
	];

	/**
	* Authorize form screen.
	*/
	public function __invoke(\Elgg\Request $request) {
		// Early return when internal server is not enabled
		if (!(bool) elgg_get_plugin_setting('enable_indieauth_endpoint', 'indieweb')) {
			throw new \Elgg\Exceptions\Http\PageNotFoundException();
		}

		$reason = '';
		$params = [];
		$valid_request = true;

		// Redirect to user login if this is an anonymous user. Start a session so we don't expose the details of the request on the user login page.
		if (!elgg_is_admin_logged_in()) {
			self::validateAuthorizeRequestParameters($request, $reason, $valid_request, false, $params);

			if ($valid_request) {
				$_SESSION['indieauth'] = $params;
				
				elgg_register_error_message(elgg_echo('indieweb:indieauth:auth:no_login'));

				$shell = elgg_get_config('walled_garden') ? 'walled_garden' : 'default';
				
				$body = elgg_view_form('login', ['ajax' => true], ['returntoreferer' => true]);

				return elgg_ok_response(elgg_view_page(elgg_echo('login'), [
					'content' => elgg_view_module('aside', false, $body),
					'sidebar' => false,
				], $shell));
			}

			elgg_log('Missing or invalid parameters to authorize as anonymous: '. $reason, 'ERROR');
			return elgg_error_response(elgg_echo('indieweb:indieauth:auth:invalid'));
		} else if (!isset($_SESSION['indieauth'])) {
			// Authenticated user: Store in session in case the indieauth key does not exist yet.
			self::validateAuthorizeRequestParameters($request, $reason, $valid_request, false, $params);
			$_SESSION['indieauth'] = $params;
		}
		
		// Check permission and required parameters as authenticated user
		if (!elgg_is_admin_logged_in()) {
			return elgg_error_response(elgg_echo('indieweb:indieauth:auth:permission'));
		}
		
		self::validateAuthorizeRequestParameters($request, $reason, $valid_request, true);
		
		if (!$valid_request) {
			unset($_SESSION['indieauth']);
			
			elgg_log('Missing or invalid parameters to authorize as user: '. $reason, 'ERROR');
			return elgg_error_response(elgg_echo('indieweb:indieauth:auth:invalid'));
		}

		// Good to go, show the authorize form
		$content = elgg_view_form('indieauth/authorize', ['class' => 'elgg-form-register'], $params);
			
		return elgg_ok_response(elgg_view_page(elgg_echo('indieweb:indieauth:authorize'), [
			'content' => $content,
			'sidebar' => false,
		]));
	}

	/**
	* Check request parameters for an IndieAuth authorize request.
	*
	* response_type and code are optional.
	*
	* @param $request
	* @param $reason
	* @param $valid_request
	* @param $in_session
	* @param $params
	*/
	public static function validateAuthorizeRequestParameters(\Elgg\Request $request, &$reason, &$valid_request, $in_session = false, &$params = null) {
		foreach (self::$auth_parameters as $parameter) {
			$value = $in_session ? (isset($_SESSION['indieauth'][$parameter]) ? $_SESSION['indieauth'][$parameter] : '') : $request->getParam($parameter); // $request->getParams($parameter)
			
			if (empty($value) && !in_array($parameter, ['response_type', 'scope', 'code_challenge', 'code_challenge_method'])) {
				$reason = "$parameter is empty";
				$valid_request = false;
				break;
			} else if ($parameter === 'response_type') {
				if (!empty($value) && ($value != 'code' && $value != 'id')) {
					$valid_request = false;
					$reason = "response type is not code or id ($value)";
					break;
				}
				
				// Set default value in case it was empty
				// See https://indieauth.spec.indieweb.org/#authentication-request
				$value = 'id';
			}

			// Store the params
			if (is_array($params) && !empty($value)) {
				$params[$parameter] = $value;
			}
		}
	}

}
