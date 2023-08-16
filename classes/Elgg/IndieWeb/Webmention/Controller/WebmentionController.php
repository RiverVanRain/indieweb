<?php
/**
 * IndieWeb
 * @author Nikolai Shcherbin
 * @license GNU Affero General Public License version 3
 * @copyright (c) Nikolai Shcherbin 2022
 * @link https://wzm.me
**/

namespace Elgg\IndieWeb\Webmention\Controller;

use Elgg\Exceptions\Http\BadRequestException;

class WebmentionController {

	/**
	 * Routing callback: internal webmention endpoint.
	 */
	public static function callback(\Elgg\Request $request) {
		$response_code = 400;
		
		if (!(bool) elgg_get_plugin_setting('enable_webmention', 'indieweb')) {
			throw new \Elgg\Exceptions\Http\PageNotFoundException();
		}
		
		if (!empty(elgg_get_plugin_setting('webmention_server', 'indieweb'))) {
			throw new BadRequestException();
		}
		
		elgg_set_http_header('Link: <' . elgg_generate_url('default:view:webmention') . '>; rel="webmention"');
		
		$source = $request->getParam('source');
		if (!$source) {
			throw new BadRequestException(elgg_echo('webmention:source:error'));
		}
		
		$target = $request->getParam('target');
		if (!$target) {
			throw new BadRequestException(elgg_echo('webmention:target:error'));
		}
		
		if (!filter_var($source, FILTER_VALIDATE_URL)) {
			throw new BadRequestException(elgg_echo('webmention:source:url:error'));
		}
		
		if (!filter_var($target, FILTER_VALIDATE_URL)) {
			throw new BadRequestException(elgg_echo('webmention:target:url:error'));
		}
		
		elgg_log('Accepting mentions', 'NOTICE');
		
		// We validate the request and store it as a webmention which we'll handle later in cron.
		if (($source != $target) && (parse_url($source, PHP_URL_HOST) != parse_url($target, PHP_URL_HOST))) {
			// Check if the source is blocked.
			if (!self::sourceIsBlocked($source)) {
				elgg_call(ELGG_IGNORE_ACCESS, function () use ($source, $target) {
					$webmention = new \Elgg\IndieWeb\Webmention\Entity\Webmention();
					$webmention->owner_guid = elgg_get_site_entity()->guid;
					$webmention->container_guid = elgg_get_site_entity()->guid;
					$webmention->access_id = ACCESS_PRIVATE;
					$webmention->source = $source;
					$webmention->target = $target;
					$webmention->property = 'received';
					$webmention->published = 0;
					$webmention->status = 0;
					$webmention->save();
					
					elgg_log(elgg_echo('webmention:recieved:success', [$webmention->guid]), 'NOTICE');
				});
				
				$response_code = 202;
			}
		}
		
		return elgg_ok_response([], '', REFERRER, $response_code);
	}
	
	/**
	 * Validates if a source is blocked.
     *
	 * @param $source
     *
     * @return bool
     */
	protected static function sourceIsBlocked(string $source): bool {
		$blocked = false;
		
		$domains = explode("\n", trim(elgg_get_plugin_setting('webmention_blocked_domains', 'indieweb')));
		
		if (!empty($domains)) {
			foreach ($domains as $domain) {
				$trim = trim($domain);
				if (strlen($trim) > 0) {
					if (strpos($source, $domain) !== false) {
						$blocked = true;
						elgg_log(elgg_echo('webmention:blocked:domain', [$source]), 'error');
						break;
					}
				}
			}
		}
		
		return $blocked;
	}
}
