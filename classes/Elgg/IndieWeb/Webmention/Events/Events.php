<?php
/**
 * IndieWeb
 * @author Nikolai Shcherbin
 * @license GNU Affero General Public License version 3
 * @copyright (c) Nikolai Shcherbin 2022
 * @link https://wzm.me
**/

namespace Elgg\IndieWeb\Webmention\Events;

use Elgg\Event;
use IndieWeb\MentionClient;

/**
 * @access private
 */
final class Events {
	
	// Listen to object create events and see if we can send webmentions. Currently looks for urls in description
	public static function createObject(Event $event) {

		if (!(bool) elgg_get_plugin_setting('enable_webmention', 'indieweb')) {
		   return;
		}

		$item = $event->getObject();
		
		if (!$item instanceof \ElggRiverItem) {
			return;
		}
		
		$entity = $item->getObjectEntity();
		if (!$entity instanceof \ElggObject) {
			return;
		}

		if(!(bool) elgg_get_plugin_setting("can_webmention:object:$entity->subtype", 'indieweb')) {
			return false;
		}

		if ($entity->access_id !== ACCESS_PUBLIC) {
			return;
		}

		if ($entity->published_status === H_DRAFT) {
			return;
		}

		if (empty($entity->description)) {
			elgg_log("No description", 'error');
			return;
		}
		
		$client = new MentionClient();
		
		if (is_string(elgg_get_plugin_setting('webmention_proxy', 'indieweb'))) {
		   $client->setProxy(elgg_get_plugin_setting('webmention_proxy', 'indieweb'));
		}
		
		if (is_string(elgg_get_plugin_setting('webmention_user_agent', 'indieweb'))) {
		   $client->setUserAgent(elgg_get_plugin_setting('webmention_user_agent', 'indieweb'));
		}
		
		if ((bool) (elgg_get_plugin_setting('webmention_enable_debug', 'indieweb'))) {
			$client->enableDebug();
		}
		
		elgg_log("Sending mentions", 'NOTICE');
		
		$client->sendMentions($entity->getURL(), $entity->description);
	}
	
}
