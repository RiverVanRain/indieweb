<?php
/**
 * IndieWeb
 * @author Nikolai Shcherbin
 * @license GNU Affero General Public License version 3
 * @copyright (c) Nikolai Shcherbin 2022
 * @link https://wzm.me
**/

namespace Elgg\IndieWeb\Views;

class SetupHead {

	public function __invoke(\Elgg\Hook $hook) {

		$return = $hook->getValue();

		//webmention
		if ((bool) elgg_get_plugin_setting('enable_webmention', 'indieweb')) {
			$webmention_server = !empty(elgg_get_plugin_setting('webmention_server', 'indieweb')) ? elgg_get_plugin_setting('webmention_server', 'indieweb') : elgg_generate_url('default:view:webmention');
			
			$return['links'][] = [
				'rel' => 'webmention',
				'href' => $webmention_server,
			];
			
			elgg_set_http_header('Link: <' . $webmention_server . '>; rel="webmention"');
		}
		
		//microsub
		if ((bool) elgg_get_plugin_setting('enable_microsub', 'indieweb')) {
			$microsub_endpoint = !empty(elgg_get_plugin_setting('microsub_endpoint', 'indieweb')) ? elgg_get_plugin_setting('microsub_endpoint', 'indieweb') : elgg_generate_url('default:view:microsub');
			
			$return['links'][] = [
				'rel' => 'microsub',
				'href' => !empty(elgg_get_plugin_setting('microsub_endpoint', 'indieweb')) ? elgg_get_plugin_setting('microsub_endpoint', 'indieweb') : elgg_generate_url('default:view:microsub'),
			];
			
			elgg_set_http_header('Link: <' . $microsub_endpoint . '>; rel="microsub"');
		}
		
		//indieauth
		$return['links'][] = [
			'rel' => 'me',
			'href' => 'mailto:' . elgg_get_site_entity()->email,
		];
			
		if ((bool) elgg_get_plugin_setting('enable_indieauth_endpoint', 'indieweb')) {
			$return['links'][] = [
				'rel' => 'authorization_endpoint',
				'href' => elgg_generate_url('indieauth:auth'),
			];
			$return['links'][] = [
				'rel' => 'token_endpoint',
				'href' => elgg_generate_url('indieauth:token'),
			];
			
			elgg_set_http_header('Link: <' . elgg_generate_url('indieauth:auth') . '>; rel="authorization_endpoint"');
			elgg_set_http_header('Link: <' . elgg_generate_url('indieauth:token') . '>; rel="token_endpoint"');
		} else {
			$return['links'][] = [
				'rel' => 'authorization_endpoint',
				'href' => elgg_get_plugin_setting('indieauth_external_auth', 'indieweb', 'https://indieauth.com/auth'),
			];
			$return['links'][] = [
				'rel' => 'token_endpoint',
				'href' => elgg_get_plugin_setting('indieauth_external_endpoint', 'indieweb', 'https://tokens.indieauth.com/token'),
			];
			
			elgg_set_http_header('Link: <' . elgg_get_plugin_setting('indieauth_external_auth', 'indieweb', 'https://indieauth.com/auth') . '>; rel="authorization_endpoint"');
			elgg_set_http_header('Link: <' . elgg_get_plugin_setting('indieauth_external_endpoint', 'indieweb', 'https://tokens.indieauth.com/token') . '>; rel="token_endpoint"');
		}
		
		//websub
		if ((bool) elgg_get_plugin_setting('enable_websub', 'indieweb') && !empty(elgg_get_plugin_setting('websub_endpoint', 'indieweb'))) {
			$return['links'][] = [
				'rel' => 'hub',
				'href' => elgg_get_plugin_setting('websub_endpoint', 'indieweb'),
			];
			$return['links'][] = [
				'rel' => 'self',
				'href' => elgg_get_site_url(),
			];
			
			elgg_set_http_header('Link: <' . elgg_get_plugin_setting('websub_endpoint', 'indieweb') . '>; rel="hub"');
			elgg_set_http_header('Link: <' . elgg_get_site_url() . '>; rel="self"');
		}

		return $return;
	}
}
