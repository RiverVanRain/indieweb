<?php
/**
 * IndieWeb
 * @author Nikolai Shcherbin
 * @license GNU Affero General Public License version 3
 * @copyright (c) Nikolai Shcherbin 2022
 * @link https://wzm.me
**/

namespace Elgg\IndieWeb\WebSub\Events;

use GuzzleHttp\Client;
use p3k\WebSub\Client as WebSubClient;

class Events {
	public static function createObject(\Elgg\Event $event) {

		if (!(bool) elgg_get_plugin_setting('enable_websub', 'indieweb')) {
		   return;
		}
		
		if (!(bool) elgg_get_plugin_setting('websub_send', 'indieweb')) {
		   return;
		}

		$entity = $event->getObject();
		
		if (!$entity instanceof \ElggObject) {
			return;
		}

		if(!(bool) elgg_get_plugin_setting("can_websub:object:$entity->subtype", 'indieweb')) {
			return;
		}

		if ($entity->access_id !== ACCESS_PUBLIC) {
			return;
		}

		if ($entity->published_status === 'draft' || $entity->status === 'draft') {
			return;
		}
		
		if (!(bool) $entity->websub_hub_publication) {
			return;
		}
		
		self::objectWebSubPub($entity);
	}
	
	public static function objectWebSubPub(\ElggEntity $entity) {
		elgg_call(ELGG_IGNORE_ACCESS, function () use ($entity) {
			$websubpub = new \Elgg\IndieWeb\WebSub\Entity\WebSubPub();
			$websubpub->owner_guid = elgg_get_site_entity()->guid;
			$websubpub->container_guid = elgg_get_site_entity()->guid;
			$websubpub->access_id = ACCESS_PRIVATE;
			$websubpub->entity_id = $entity->guid;
			$websubpub->entity_type_id = $entity->subtype;
			$websubpub->published = false;
			$websubpub->save();
			
			if ((bool) elgg_get_plugin_setting('websub_log_payload', 'indieweb')) {
				elgg_log(elgg_echo('websub:send:success', [$websub->guid]), 'NOTICE');
			}
		});
	}
}
