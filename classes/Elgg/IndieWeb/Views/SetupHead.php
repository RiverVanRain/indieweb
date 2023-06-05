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

		$owner = elgg_get_page_owner_entity();
		
		if ((elgg_in_context('profile') || elgg_in_context('profile_view') || elgg_in_context('creator')) && $owner instanceof \ElggUser) {
			$return['links'][] = [
				'rel' => 'me',
				'href' => $owner->getURL(),
			];
		}

		return $return;
	}
}
