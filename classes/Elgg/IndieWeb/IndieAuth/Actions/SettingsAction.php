<?php

namespace Elgg\IndieWeb\IndieAuth\Actions;

class SettingsAction {

	public function __invoke(\Elgg\Request $request) {

		$params = $request->getParam('params');
		$flush_cache = $request->getParam('flush_cache');
		$plugin_id = $request->getParam('plugin_id');
		$plugin = elgg_get_plugin_from_id($plugin_id);
		
		if (!$plugin) {
			return elgg_error_response(elgg_echo('plugins:settings:save:fail', [$plugin_id]));
		}

		$plugin_name = $plugin->getDisplayName();

		$result = false;

		foreach ($params as $k => $v) {
			if (is_array($v)) {
				$v = serialize($v);
			}
			
			$result = $plugin->setSetting($k, $v);
			if (!$result) {
				return elgg_error_response(elgg_echo('plugins:settings:save:fail', [$plugin_name]));
			}
		}
		
		// Generate keys if the checkbox is toggled
		if ((bool) elgg_extract('indieauth_generate_keys', $params)) {
			$paths = elgg()->indieauth->generateKeys();
			if (!$paths) {
				return elgg_error_response(elgg_echo('indieweb:indieauth:keys:generate_keys'));
			} else {
				$plugin->setSetting('indieauth_public_key', $paths['public_key']);
				$plugin->setSetting('indieauth_private_key', $paths['private_key']);
			}
		}
		
		if ($flush_cache) {
			elgg_clear_caches();
		}

		return elgg_ok_response('', elgg_echo('plugins:settings:save:ok', [$plugin_name]));

	}
}