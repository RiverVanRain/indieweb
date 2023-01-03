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
	return elgg_get_plugin_setting('microsub_user_agent', 'indieweb');
}
