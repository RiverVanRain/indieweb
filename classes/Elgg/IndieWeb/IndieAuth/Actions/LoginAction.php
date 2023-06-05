<?php

namespace Elgg\IndieWeb\IndieAuth\Actions;

use IndieAuth\Client;
use p3k\HTTP;

class LoginAction {

	public function __invoke(\Elgg\Request $request) {
		if (!(bool) elgg_get_plugin_setting('enable_indieauth_login', 'indieweb')) {
			throw new \Elgg\Exceptions\Http\PageNotFoundException();
		}
		
		$domain = $request->getParam('domain');
		
		// Add trailing slash if necessary
		if (substr($domain, -1, 1) != '/') {
			$domain .= '/';
		}
		
		// Get the authorization endpoint for the domain
		$r1 = rand(0, 9999);
		$r2 = rand(0, 99);
		$generate_ua = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/111.0.5563.{$r1} Safari/537.{$r2}";
		
		$client = new Client();
		$httpClient = new HTTP();
		$httpClient->set_user_agent($generate_ua);
		$client::$http = $httpClient;
		
		$authorization_endpoint = $client::discoverAuthorizationEndpoint($domain);

		if (!empty($authorization_endpoint)) {
			$_SESSION['indieauth_domain'] = $domain;
			$_SESSION['indieauth_authorization_endpoint'] = $authorization_endpoint;
			$_SESSION['indieauth_state'] = _elgg_services()->crypto->getRandomString(32);
			$_SESSION['indieauth_email'] = $request->getParam('email');
			
			$response = $authorization_endpoint;
			$response .= '?redirect_uri=' . elgg_generate_url('indieauth:login');
			$response .= '&client_id=' . elgg_get_site_url();
			$response .= '&me=' . $domain;
			$response .= '&state=' . $_SESSION['indieauth_state'];
			
			// Redirect to auth provider
			return elgg_redirect_response($response);
		} else {
			return elgg_error_response(elgg_echo('indieweb:indieauth:login:fail'));
		}
	}
}