<?php
/**
 * IndieWeb
 * @author Nikolai Shcherbin
 * @license GNU Affero General Public License version 3
 * @copyright (c) Nikolai Shcherbin 2022
 * @link https://wzm.me
**/

namespace Elgg\IndieWeb\IndieAuth\Controller;

class LoginController {
	
	public function __invoke(\Elgg\Request $request) {

		// Early return when internal server is not enabled
		if (!(bool) elgg_get_plugin_setting('enable_indieauth_login', 'indieweb')) {
			throw new \Elgg\Exceptions\Http\PageNotFoundException();
		}
		
		// Verify code
		$session_id = $_SESSION['indieauth_state'] ?? '';
		if (!empty($request->getParam('code')) && $request->getParam('state') === $session_id) {
			// Validate the code
			$valid_code = false;
			$domain = '';
			
			try {
				$client = elgg()->httpClient->setup();
				$body = [
					'code' => $request->getParam('code'),
					'client_id' => elgg_get_site_url(),
					'redirect_uri' => elgg_generate_url('indieauth:login'),
				];

				$headers = ['Accept' => 'application/json'];
				$authorization_endpoint = $_SESSION['indieauth_authorization_endpoint'];
				
				$response = $client->post($authorization_endpoint, ['form_params' => $body, 'headers' => $headers]);
				
				$json = json_decode($response->getBody()->getContents());
        
				if (isset($json->me) && isset($_SESSION['indieauth_domain']) && rtrim($json->me, '/') === rtrim($_SESSION['indieauth_domain'], '/')) {
					$domain = $_SESSION['indieauth_domain'];
					$email = $_SESSION['indieauth_email'];
					unset($_SESSION['indieauth_domain']);
					unset($_SESSION['indieauth_start']);
					unset($_SESSION['indieauth_authorization_endpoint']);
					unset($_SESSION['indieauth_email']);
					$valid_code = true;
				}
			} catch (\Exception $e) {
				elgg_log('Error validating the code: ' . $e->getMessage(), 'ERROR');
			}

			// We have a valid token
			if ($valid_code && !empty($domain)) {
				// Create authname. Strip schemes.
				$authname = str_replace(['https://', 'http://', '/', '.', '-'], '', $domain);
				
				try {
					// Map with existing account
					if (elgg_is_logged_in()) {
						$account = elgg_get_logged_in_user_entity();
						$this->mapUsers($authname, 'indieauth', $account);
						
						return elgg_ok_response('', elgg_echo('indieweb:indieauth:login:success'), elgg_get_site_url());
					} else if (!is_null($this->loginUser($authname, 'indieauth'))) {
						// Login the user
						$account = $this->loginUser($authname, 'indieauth');
						elgg_login($account);
						
						return elgg_ok_response('', elgg_echo('indieweb:indieauth:login:success'), elgg_generate_url('settings:account', [
							'username' => $account->username,
						]));
					} else {
						// Register the user
						if (!_elgg_services()->config->allow_registration) {
							return elgg_error_response(elgg_echo('registerdisabled'));
						}
						
						$username = $name = $authname;
						if (strlen($username) > 128) {
							$username = substr($username, 0, 128);
						}

						$password = $password2 = elgg_generate_password();

						$username = trim($username);
						$email = trim($email);
						
						$validation = elgg_validate_registration_data($username, [$password, $password2], $name, $email);
						$failures = $validation->getFailures();
						if ($failures) {
							$messages = array_map(function (\Elgg\Validation\ValidationResult $e) {
								return $e->getError();
							}, $failures);

							throw new \Elgg\Exceptions\Configuration\RegistrationException(implode(PHP_EOL, $messages));
						}

						$account = elgg_register_user([
							'username' => $username,
							'password' => $password,
							'name' => $name,
							'email' => $email,
							'validated' => false,
							'subtype' => 'user',
						]);

						$fail = function () use ($account) {
							elgg_call(ELGG_IGNORE_ACCESS, function () use ($account) {
								$account->delete();
							});
						};

						try {
							$account->setValidationStatus(true, 'indieauth');
								
							$account->setPluginSetting('indieweb', 'indieauth', $authname);
							elgg_trigger_event_results('indieauth:authenticate', 'indieauth', ['entity' => $account]);
								
							$account->indieauth_login = 1;
							
							$subject = elgg_echo('useradd:subject', [], $account->getLanguage());
							$body = elgg_echo('useradd:body', [
								$name,
								elgg_get_site_entity()->url,
								$username,
								$password,
							], $account->getLanguage());

							notify_user($account->guid, elgg_get_site_entity()->guid, $subject, $body);
							
							elgg_login($account);
							
							return elgg_ok_response('', elgg_echo('indieweb:indieauth:login:success'), elgg_get_site_url());
						} catch (\Exception $e) {
							// Catch all exception to make sure there are no incomplete user entities left behind
							$fail();
							throw $e;
						}
					}
          
					if ($account) {
						$content = elgg_view_form('indieauth/login', ['class' => 'elgg-form-login'], ['account' => $account, 'indieauth_response' => $json]);
			
						return elgg_ok_response(elgg_view_page(elgg_echo('indieweb:indieauth:login'), [
							'content' => $content,
							'sidebar' => false,
						]));
					} else {
						$message = 'Unknown user, please try again.';
						elgg_register_error_message($message);
					}
				} catch (\Exception $e) {
					elgg_log('Error on login: ' . $e->getMessage(), 'ERROR');
					$message = 'Unknown user, please try again. : ' . $e->getMessage();
					elgg_register_error_message($message);
				}
			} else {
				$message = 'Invalid code, please try again.';
				elgg_register_error_message($message);
			}
		}
		
		$content = elgg_view_form('indieauth/login', ['class' => 'elgg-form-login'], []);
			
		return elgg_ok_response(elgg_view_page(elgg_echo('indieweb:indieauth:login'), [
			'content' => $content,
			'sidebar' => false,
		]));
	}
	
	/**
	* {@inheritdoc}
	*/
	public function mapUsers(string $authname, string $provider, \ElggUser $account) {
		if (!$account) {
			return false;
		}
		
		$users = elgg_get_entities([
			'type' => 'user',
			'guid' => (int) $account->guid,
			'metadata_name_value_pairs' => [
				'name' => "plugin:user_setting:indieweb:{$provider}",
				'value' => $authname
			],
			'limit' => false,
		]);
		
		if (count($users) === 1) {
			return elgg_ok_response('', elgg_echo('indieweb:indieauth:login:success:already'), elgg_get_site_url());
		}
		
		$account->setPluginSetting('indieweb', $provider, $authname);
		$account->indieauth_login = 1;
		$account->save();
	}
	
	/**
	* {@inheritdoc}
	*/
	public function loginUser(string $authname, string $provider) {
		$users = elgg_get_entities([
			'type' => 'user',
			'metadata_name_value_pairs' => [
				'name' => "plugin:user_setting:indieweb:{$provider}",
				'value' => $authname
			],
			'limit' => false,
		]);
		
		return count($users) === 1 ? array_shift($users) : null;
	}
}
