<?php
/**
 * IndieWeb
 * @author Nikolai Shcherbin
 * @license GNU Affero General Public License version 3
 * @copyright (c) Nikolai Shcherbin 2022
 * @link https://wzm.me
**/

namespace Elgg\IndieWeb\Webmention\Events;

use Elgg\IndieWeb\Webmention\Client\MentionClient;

class Events {
	// Listen to object publish events and see if we can send webmentions. Currently looks for urls in description
	public static function createObject(\Elgg\Event $event) {
		if (!(bool) elgg_get_plugin_setting('enable_webmention', 'indieweb')) {
		   return;
		}

		$entity = $event->getObject();
		
		if (!$entity instanceof \ElggObject) {
			return;
		}
		
		if (!(bool) elgg_get_plugin_setting("can_webmention:object:$entity->subtype", 'indieweb')) {
			return;
		}

		if ($entity->access_id !== ACCESS_PUBLIC) {
			return;
		}
		
		if ($entity->published_status === 'draft' || $entity->status === 'draft') {
			return;
		}
		
		if (empty($entity->description)) {
			return;
		}
		
		$client = new MentionClient();
		
		if (is_string(elgg_get_plugin_setting('webmention_proxy', 'indieweb'))) {
			$client->setProxy(elgg_get_plugin_setting('webmention_proxy', 'indieweb'));
		}
		
		if (is_string(elgg_get_plugin_setting('webmention_user_agent', 'indieweb'))) {
			$client->setUserAgent(elgg_get_plugin_setting('webmention_user_agent', 'indieweb'));
		}
		
		if ((bool) elgg_get_plugin_setting('webmention_enable_debug', 'indieweb')) {
			$client->enableDebug();
		}
		
		elgg_log('Sending mentions', 'NOTICE');
		
		$targets = unserialize($entity->syndication_targets);
		
		if (!empty($targets[0])) {
			foreach ($targets as $target) {
				if (empty($target)) {
					continue;
				}
				
				$client->sendWebmention($entity->getURL(), $target);
				self::objectSyndication($entity->guid, $entity->getURL());
				self::objectWebmention($entity->getURL(), $target);
			}
		} else {
			$client->sendMentions($entity->getURL(), $entity->description);
		}
	}
	
	public static function objectSyndication($guid, $source) {
		elgg_call(ELGG_IGNORE_ACCESS, function () use ($guid, $source) {
			$syndication = new \Elgg\IndieWeb\Webmention\Entity\Syndication();
			$syndication->owner_guid = elgg_get_site_entity()->guid;
			$syndication->container_guid = elgg_get_site_entity()->guid;
			$syndication->access_id = ACCESS_PRIVATE;
			$syndication->source_id = $guid;
			$syndication->source_url = $source;
			$syndication->save();
		});
	}
	
	public static function objectWebmention($source, $target) {
		elgg_call(ELGG_IGNORE_ACCESS, function () use ($source, $target) {
			$webmention = new \Elgg\IndieWeb\Webmention\Entity\Webmention();
			$webmention->owner_guid = elgg_get_site_entity()->guid;
			$webmention->container_guid = elgg_get_site_entity()->guid;
			$webmention->access_id = ACCESS_PRIVATE;
			$webmention->source = $source;
			$webmention->target = $target;
			$webmention->property = 'send';
			$webmention->published = 0;
			$webmention->status = 0;
			$webmention->save();
					
			elgg_log(elgg_echo('webmention:send:success', [$webmention->guid]), 'NOTICE');
		});
	}
	
}
