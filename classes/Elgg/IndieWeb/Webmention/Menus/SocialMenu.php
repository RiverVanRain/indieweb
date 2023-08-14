<?php
/**
 * IndieWeb
 * @author Nikolai Shcherbin
 * @license GNU Affero General Public License version 3
 * @copyright (c) Nikolai Shcherbin 2022
 * @link https://wzm.me
**/

namespace Elgg\IndieWeb\Webmention\Menus;

class SocialMenu {

	/**
	 * Setup page menu
	 *
	 * @param Hook $hook Hook
	 */
	public function __invoke(\Elgg\Hook $hook) {
		$entity = $hook->getEntityParam();
		
		if (!$entity instanceof \ElggEntity || $entity instanceof \Elgg\IndieWeb\Webmention\Entity\Webmention) {
			return;
		}
		
		if(!(bool) elgg_get_plugin_setting("can_webmention:object:$entity->subtype", 'indieweb')) {
			return;
		}

		$menu = $hook->getValue();
		/* @var $menu \Elgg\Menu\MenuItems */
		
		$count = elgg_count_entities([
			'type' => 'object',
			'subtype' => \Elgg\IndieWeb\Webmention\Entity\Webmention::SUBTYPE,
			'metadata_name_value_pairs' => [
				[
					'name' => 'target_guid',
					'value' => $entity->guid,
				],
				[
					'name' => 'published',
					'value' => 1,
				],
				[
					'name' => 'status',
					'value' => 1,
				],
			],
		]);
		
		if ($count > 0) {
			$menu[] = \ElggMenuItem::factory([
				'name' => 'webmentions',
				'icon' => '<i class="openwebicons-webmention" style="font-size: 16px;"></i>',
				'badge' => $count,
				'text' => false,
				'title' => elgg_echo('collection:object:webmention'),
				'href' => $entity->getURL() . '#webmentions',
			]);
		}
		
		return $menu;
	}
}
