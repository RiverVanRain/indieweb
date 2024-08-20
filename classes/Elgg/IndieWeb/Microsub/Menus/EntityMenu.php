<?php

namespace Elgg\IndieWeb\Microsub\Menus;

use Elgg\Event;
use ElggMenuItem;
use Elgg\Menu\MenuItems;

/**
 * @access private
 */
class EntityMenu {
	
	public static function microsubChannelEntityMenu(Event $event): ?MenuItems {
		$entity = $event->getEntityParam();
		if (!$entity instanceof \Elgg\IndieWeb\Microsub\Entity\MicrosubChannel) {
			return null;
		}
		
		if (!$entity->canEdit()) {
			return null;
		}
		
		$menu = $event->getValue();
		
		if (isset($entity->channel_id) && $entity->channel_id === 0) {
			$menu->remove('edit');
			$menu->remove('delete');
			
			return $menu;
		}
		
		// Change status
		$enabled = (bool) $entity->getStatus();

		$menu->add(ElggMenuItem::factory([
			'name' => 'enable',
			'text' => elgg_echo('indieweb:microsub:microsub_channel:enable'),
			'icon' => 'check',
			'href' => elgg_generate_action_url('microsub/channel/toggle_status', [
				'guid' => $entity->guid,
			]),
			'item_class' => $enabled ? 'hidden' : '',
			'priority' => 177,
			'data-toggle' => 'disable',
		]));
		
		$menu->add(ElggMenuItem::factory([
			'name' => 'disable',
			'text' => elgg_echo('indieweb:microsub:microsub_channel:disable'),
			'icon' => 'ban',
			'href' => elgg_generate_action_url('microsub/channel/toggle_status', [
				'guid' => $entity->guid,
			]),
			'item_class' => $enabled ? '' : 'hidden',
			'priority' => 178,
			'data-toggle' => 'enable',
		]));
		
		// Edit
		$menu->add(ElggMenuItem::factory([
			'name' => 'edit',
			'text' => elgg_echo('edit'),
			'href' => elgg_generate_url('edit:object:microsub_channel', [
				'guid' => $entity->guid,
			]),
			'link_class' => 'elgg-lightbox',
			'data-colorbox-opts' => json_encode([
				'width' => '1000px',
				'height' => '98%',
				'maxWidth' => '98%',
			]),
			'deps' => ['elgg/lightbox'],
			'icon' => 'edit',
			'priority' => 800,
		]));
		
		return $menu;
	}
	
	public static function microsubSourceEntityMenu(Event $event): ?MenuItems {
		$entity = $event->getEntityParam();
		if (!$entity instanceof \Elgg\IndieWeb\Microsub\Entity\MicrosubSource) {
			return null;
		}
		
		if (!$entity->canEdit()) {
			return null;
		}
		
		$menu = $event->getValue();
		
		// Change status
		$enabled = (bool) $entity->getStatus();

		$menu->add(ElggMenuItem::factory([
			'name' => 'enable',
			'text' => elgg_echo('indieweb:microsub:microsub_source:enable'),
			'icon' => 'check',
			'href' => elgg_generate_action_url('microsub/source/toggle_status', [
				'guid' => $entity->guid,
			]),
			'item_class' => $enabled ? 'hidden' : '',
			'priority' => 177,
			'data-toggle' => 'disable',
		]));
		
		$menu->add(ElggMenuItem::factory([
			'name' => 'disable',
			'text' => elgg_echo('indieweb:microsub:microsub_source:disable'),
			'icon' => 'ban',
			'href' => elgg_generate_action_url('microsub/source/toggle_status', [
				'guid' => $entity->guid,
			]),
			'item_class' => $enabled ? '' : 'hidden',
			'priority' => 178,
			'data-toggle' => 'enable',
		]));
		
		// Edit
		$menu->add(ElggMenuItem::factory([
			'name' => 'edit',
			'text' => elgg_echo('edit'),
			'href' => elgg_generate_url('edit:object:microsub_source', [
				'guid' => $entity->guid,
			]),
			'link_class' => 'elgg-lightbox',
			'data-colorbox-opts' => json_encode([
				'width' => '1000px',
				'height' => '98%',
				'maxWidth' => '98%',
			]),
			'deps' => ['elgg/lightbox'],
			'icon' => 'edit',
			'priority' => 800,
		]));
		
		return $menu;
	}
}
