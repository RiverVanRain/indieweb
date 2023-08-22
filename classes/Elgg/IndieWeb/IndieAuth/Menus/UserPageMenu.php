<?php

namespace Elgg\IndieWeb\IndieAuth\Menus;

use Elgg\Menu\MenuItems;

class UserPageMenu {

	/**
	 * Setup page menu
	 *
	 * @param Event $event Event
	 */
	public function __invoke(\Elgg\Event $event): ?MenuItems {
		if (elgg_is_active_plugin('elgg_hybridauth')) {
			return null;
		}

		$menu = $event->getValue();
		/* @var $menu \Elgg\Menu\MenuItems */
		
		if (!elgg_is_logged_in()) {
			return null;
		}
		
		if (!elgg_in_context('settings')) {
			return null;
		}

		$menu[] = \ElggMenuItem::factory([
			'name' => 'indieauth:accounts',
			'href' => elgg_generate_url('indieauth:accounts', [
				'username' => elgg_get_logged_in_user_entity()->username,
			]),
			'text' => elgg_echo('indieauth:accounts'),
			'icon' => '<i class="openwebicons-indieauth" style="font-size: 16px;"></i>',
			'context' => ['settings'],
		]);
		

		return $menu;
	}
}
