<?php
/**
 * IndieWeb
 * @author Nikolai Shcherbin
 * @license GNU Affero General Public License version 3
 * @copyright (c) Nikolai Shcherbin 2022
 * @link https://wzm.me
**/

namespace Elgg\IndieWeb\Entity\Events;

class Events {
	public static function createObject(\Elgg\Event $event) {
		$entity = $event->getObject();
		
		if (!$entity instanceof \ElggObject) {
			return;
		}
		
		$entity_syndication_targets = (array) get_input('syndication_targets', []);
		$entity_syndication_targets_custom_url = (array) get_input('syndication_targets_custom_url', []);
			
		if (!empty($entity_syndication_targets) && !empty($entity_syndication_targets_custom_url)) {
			$syndication_targets = array_merge($entity_syndication_targets, $entity_syndication_targets_custom_url);
			$entity->syndication_targets = serialize($syndication_targets);
		} else if (!empty($entity_syndication_targets)) {
			$entity->syndication_targets = serialize($entity_syndication_targets);
		} else if (!empty($entity_syndication_targets_custom_url)) {
			$entity->syndication_targets = serialize($entity_syndication_targets_custom_url);
		}
			
		if ((bool) elgg_get_plugin_setting("can_websub:object:$entity->subtype", 'indieweb')
			$entity->websub_hub_publication = (bool) get_input('websub_hub_publication');
		}
			
		$entity->save();
	}
}
