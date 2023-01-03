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
		$return['links'][] = [
			'rel' => 'webmention',
			'href' => is_string(elgg_get_plugin_setting('webmention_server', 'indieweb')) ? elgg_get_plugin_setting('webmention_server', 'indieweb') : elgg_generate_url('default:view:webmention'),
		];
		
		//microsub
		if ((bool) elgg_get_plugin_setting('enable_microsub', 'indieweb')) {
			$return['links'][] = [
				'rel' => 'microsub',
				'href' => is_string(elgg_get_plugin_setting('microsub_endpoint', 'indieweb')) ? elgg_get_plugin_setting('microsub_endpoint', 'indieweb') : elgg_generate_url('default:view:microsub'),
			];
		}

		return $return;
	}
}
