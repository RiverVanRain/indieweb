<?php

namespace Elgg\IndieWeb\IndieAuth\Menus;

class UserPageMenu {

	/**
	 * Setup page menu
	 *
	 * @param Hook $hook Hook
	 */
	public function __invoke(\Elgg\Hook $hook) {
		if (elgg_is_active_plugin('elgg_hybridauth')) {
			return;
		}

		$menu = $hook->getValue();
		/* @var $menu \Elgg\Menu\MenuItems */
		
		if (!elgg_is_logged_in()) {
			return;
		}
		
		if (!elgg_in_context('settings')) {
			return;
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
