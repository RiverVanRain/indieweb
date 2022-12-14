<?php
/**
 * IndieWeb
 * @author Nikolai Shcherbin
 * @license GNU Affero General Public License version 3
 * @copyright (c) Nikolai Shcherbin 2022
 * @link https://wzm.me
**/

namespace Elgg\IndieWeb\Menus;

use Elgg\Hook;
use ElggMenuItem;

class SettingsMenu {

	/**
	 * Setup page menu
	 *
	 * @param Hook $hook Hook
	 */
	public function __invoke(Hook $hook) {

		$menu = $hook->getValue();
		/* @var $menu \Elgg\Menu\MenuItems */
		
		if (!elgg_in_context('admin')) {
			return null;
		}

		$menu[] = \ElggMenuItem::factory([
			'name' => 'indieweb',
			'href' => false,
			'text' => elgg_echo('settings:indieweb'),
			'context' => ['admin'],
			'section' => 'configure',
		]);

		//Webmention
		$menu[] = \ElggMenuItem::factory([
			'name' => 'indieweb:webmention',
			'parent_name' => 'indieweb',
			'href' => 'admin/indieweb/webmention',
			'text' => elgg_echo('admin:indieweb:webmention'),
			'context' => ['admin'],
			'section' => 'configure',
			'priority' => 100,
		]);
		
		$menu[] = \ElggMenuItem::factory([
			'name' => 'indieweb:webmention:received',
			'parent_name' => 'indieweb:webmention',
			'href' => 'admin/indieweb/webmention/received',
			'text' => elgg_echo('admin:indieweb:webmention:received'),
			'context' => ['admin'],
			'section' => 'configure',
			'priority' => 110,
		]);
/*		
		$menu[] = \ElggMenuItem::factory([
			'name' => 'indieweb:webmention:send',
			'parent_name' => 'indieweb:webmention',
			'href' => 'admin/indieweb/webmention/send',
			'text' => elgg_echo('admin:indieweb:webmention:send'),
			'context' => ['admin'],
			'section' => 'configure',
			'priority' => 120,
		]);
*/		
		//Micropub
		$menu[] = \ElggMenuItem::factory([
			'name' => 'indieweb:micropub',
			'parent_name' => 'indieweb',
			'href' => 'admin/indieweb/micropub',
			'text' => elgg_echo('admin:indieweb:micropub'),
			'context' => ['admin'],
			'section' => 'configure',
			'priority' => 200,
		]);
		
		//Microsub
		$menu[] = \ElggMenuItem::factory([
			'name' => 'indieweb:microsub',
			'parent_name' => 'indieweb',
			'href' => 'admin/indieweb/microsub',
			'text' => elgg_echo('admin:indieweb:microsub'),
			'context' => ['admin'],
			'section' => 'configure',
			'priority' => 300,
		]);
		
		$menu[] = \ElggMenuItem::factory([
			'name' => 'indieweb:microsub:channels',
			'parent_name' => 'indieweb:microsub',
			'href' => 'admin/indieweb/microsub/channels',
			'text' => elgg_echo('admin:indieweb:microsub:channels'),
			'context' => ['admin'],
			'section' => 'configure',
			'priority' => 310,
		]);
		
		return $menu;
	}
}
