<?php
/**
 * IndieWeb
 * @author Nikolai Shcherbin
 * @license GNU Affero General Public License version 3
 * @copyright (c) Nikolai Shcherbin 2022
 * @link https://wzm.me
**/

/**
 * Returns the path without the hostname from a URL.
 *
 * @param $url
 *
 * @return string $path  The path
 */
function indieweb_get_path(string $url): string {
	$path = str_replace(elgg_get_site_url(), '', $url);
	
	if (empty($path)) {
		$path = '/';
	}
	
	return $path;
}

/**
 * Returns GUID from a URL.
 *
 * @param $url
 *
 * @return int $guid
 */
function indieweb_get_guid(string $url): int {
	$target = indieweb_get_path($url);
	
	$objects = (array) elgg_extract('object', elgg_entity_types_with_capability('commentable'), []);

	$guid = [];
	
	foreach ($objects as $subtype) {
		if (!(bool) elgg_get_plugin_setting("can_webmention:object:$subtype", 'indieweb')) {
			continue;
		}
		
		if (strpos($target, "{$subtype}/view/") === false) {
			continue;
		}

		$id = str_replace("{$subtype}/view/", '', $target);
		$id = explode('/', $id);
		$guid[] = $id[0];
	}

	return (!empty($guid)) ? $guid[0] : 0;
}

/**
 * Returns the default user agent when calling feeds.
 *
 * @return mixed|null
 */
function indieweb_microsub_http_client_user_agent() {
	$r1 = rand(0, 9999);
	$r2 = rand(0, 99);
	$generate_ua = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/111.0.5563.{$r1} Safari/537.{$r2}";
	
	return elgg_get_plugin_setting('microsub_user_agent', 'indieweb', $generate_ua);
}

/**
 * Returns the syndication targets.
 *
 * @param boolean $return_all_config Whether to return the syndication targets as an array with 'options' and 'default' key.
 *
 * @return array
 */
function indieweb_get_syndication_targets($return_all_config = false): array {
	if (!(bool) elgg_get_plugin_setting('enable_webmention', 'indieweb')) {
		return [];
	}
	
	$syndication_targets = [];
	$config = elgg_get_plugin_setting('webmention_syndication_targets', 'indieweb', 'Twitter (bridgy)|https://brid.gy/publish/twitter');
		
	if (!empty($config)) {
		$lines = explode("\n", $config);
		foreach ($lines as $line) {
			$line = trim($line);
			if (!empty($line)) {
				$explode = explode('|', $line);
				if (!empty($explode[0]) && !empty($explode[1])) {
					if ($return_all_config) {
						$syndication_targets['options'][$explode[1]] = $explode[0];

						// Selected by default on the form.
						if (isset($explode[2]) && !empty($explode[2]) && $explode[2] == '1') {
							$syndication_targets['default'][] = $explode[1];
						}
					} else {
						$syndication_targets[$explode[1]] = $explode[0];
					}
				}
			}
		}
	}

	return $syndication_targets;
}
