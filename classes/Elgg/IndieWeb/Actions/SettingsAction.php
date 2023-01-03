<?php

namespace Elgg\IndieWeb\Actions;

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
			$result = $plugin->setSetting($k, $v);
			if (!$result) {
				return elgg_error_response(elgg_echo('plugins:settings:save:fail', [$plugin_name]));
			}
		}

		if ($flush_cache) {
			elgg_clear_caches();
		}

		return elgg_ok_response('', elgg_echo('plugins:settings:save:ok', [$plugin_name]));

	}
}