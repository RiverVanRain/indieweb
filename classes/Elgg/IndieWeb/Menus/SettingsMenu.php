<?php
/**
 * IndieWeb
 * @author Nikolai Shcherbin
 * @license GNU Affero General Public License version 3
 * @copyright (c) Nikolai Shcherbin 2022
 * @link https://wzm.me
**/

namespace Elgg\IndieWeb\Menus;

use Elgg\Menu\MenuItems;

class SettingsMenu {

	/**
	 * Setup page menu
	 *
	 * @param Event $event Event
	 */
	public function __invoke(\Elgg\Event $event): ?MenuItems {
		if (!elgg_is_admin_logged_in() || !elgg_in_context('admin')) {
			return null;
		}
		
		$menu = $event->getValue();
		/* @var $menu \Elgg\Menu\MenuItems */
		
		$menu[] = \ElggMenuItem::factory([
			'name' => 'indieweb',
			'href' => false,
			'text' => elgg_echo('settings:indieweb'),
			'icon' => '<i class="openwebicons-indieweb" style="font-size: 16px;"></i>',
		]);

		//Webmention
		$menu[] = \ElggMenuItem::factory([
			'name' => 'indieweb:webmention',
			'parent_name' => 'indieweb',
			'href' => false,
			'text' => elgg_echo('admin:indieweb:webmention'),
			'priority' => 100,
		]);
		
		$menu[] = \ElggMenuItem::factory([
			'name' => 'indieweb:webmention:basic',
			'parent_name' => 'indieweb:webmention',
			'href' => elgg_normalize_url('admin/indieweb/webmention'),
			'text' => elgg_echo('settings:indieweb:webmention'),
			'priority' => 110,
		]);
		
		$menu[] = \ElggMenuItem::factory([
			'name' => 'indieweb:webmention:stored',
			'parent_name' => 'indieweb:webmention',
			'href' => false,
			'text' => elgg_echo('indieweb:webmention:stored'),
			'priority' => 120,
		]);
		
		$menu[] = \ElggMenuItem::factory([
			'name' => 'indieweb:webmention:all',
			'parent_name' => 'indieweb:webmention:stored',
			'href' => elgg_normalize_url('admin/indieweb/webmention/all'),
			'text' => elgg_echo('settings:indieweb:webmention:all'),
			'priority' => 121,
		]);
		
		$menu[] = \ElggMenuItem::factory([
			'name' => 'indieweb:webmention:received',
			'parent_name' => 'indieweb:webmention:stored',
			'href' => elgg_normalize_url('admin/indieweb/webmention/received'),
			'text' => elgg_echo('settings:indieweb:webmention:received'),
			'priority' => 122,
		]);
	
		$menu[] = \ElggMenuItem::factory([
			'name' => 'indieweb:webmention:sent',
			'parent_name' => 'indieweb:webmention:stored',
			'href' => elgg_normalize_url('admin/indieweb/webmention/sent'),
			'text' => elgg_echo('settings:indieweb:webmention:sent'),
			'priority' => 123,
		]);
		
		$menu[] = \ElggMenuItem::factory([
			'name' => 'indieweb:webmention:syndications',
			'parent_name' => 'indieweb:webmention',
			'href' => elgg_normalize_url('admin/indieweb/webmention/syndications'),
			'text' => elgg_echo('admin:indieweb:webmention:syndications'),
			'priority' => 150,
		]);
		
		//Micropub
		$menu[] = \ElggMenuItem::factory([
			'name' => 'indieweb:micropub',
			'parent_name' => 'indieweb',
			'href' => false,
			'text' => elgg_echo('admin:indieweb:micropub'),
			'priority' => 200,
		]);
		
		$menu[] = \ElggMenuItem::factory([
			'name' => 'indieweb:micropub:basic',
			'parent_name' => 'indieweb:micropub',
			'href' => elgg_normalize_url('admin/indieweb/micropub'),
			'text' => elgg_echo('settings:indieweb:micropub'),
			'priority' => 210,
		]);
		
		$menu[] = \ElggMenuItem::factory([
			'name' => 'indieweb:micropub:posts',
			'parent_name' => 'indieweb:micropub',
			'href' => elgg_normalize_url('admin/indieweb/micropub/posts'),
			'text' => elgg_echo('settings:indieweb:micropub:posts'),
			'priority' => 220,
		]);
		
		//Microsub
		$menu[] = \ElggMenuItem::factory([
			'name' => 'indieweb:microsub',
			'parent_name' => 'indieweb',
			'href' => false,
			'text' => elgg_echo('admin:indieweb:microsub'),
			'priority' => 300,
		]);
		$menu[] = \ElggMenuItem::factory([
			'name' => 'indieweb:microsub:basic',
			'parent_name' => 'indieweb:microsub',
			'href' => elgg_normalize_url('admin/indieweb/microsub'),
			'text' => elgg_echo('settings:indieweb:microsub'),
			'priority' => 310,
		]);
		
		$menu[] = \ElggMenuItem::factory([
			'name' => 'indieweb:microsub:channels',
			'parent_name' => 'indieweb:microsub',
			'href' => elgg_normalize_url('admin/indieweb/microsub/channels'),
			'text' => elgg_echo('admin:indieweb:microsub:channels'),
			'priority' => 320,
		]);
		
		//IndieAuth
		$menu[] = \ElggMenuItem::factory([
			'name' => 'indieweb:indieauth',
			'parent_name' => 'indieweb',
			'href' => false,
			'text' => elgg_echo('admin:indieweb:indieauth'),
			'priority' => 400,
		]);
		
		$menu[] = \ElggMenuItem::factory([
			'name' => 'indieweb:indieauth:basic',
			'parent_name' => 'indieweb:indieauth',
			'href' => elgg_normalize_url('admin/indieweb/indieauth'),
			'text' => elgg_echo('settings:indieweb:indieauth'),
			'priority' => 410,
		]);
		
		$menu[] = \ElggMenuItem::factory([
			'name' => 'indieweb:indieauth:tokens',
			'parent_name' => 'indieweb:indieauth',
			'href' => elgg_normalize_url('admin/indieweb/indieauth/tokens'),
			'text' => elgg_echo('settings:indieweb:indieauth:tokens'),
			'priority' => 420,
		]);
		
		$menu[] = \ElggMenuItem::factory([
			'name' => 'indieweb:indieauth:codes',
			'parent_name' => 'indieweb:indieauth',
			'href' => elgg_normalize_url('admin/indieweb/indieauth/codes'),
			'text' => elgg_echo('settings:indieweb:indieauth:codes'),
			'priority' => 430,
		]);
		
		//WebSub
		$menu[] = \ElggMenuItem::factory([
			'name' => 'indieweb:websub',
			'parent_name' => 'indieweb',
			'href' => false,
			'text' => elgg_echo('admin:indieweb:websub'),
			'priority' => 500,
		]);
		
		$menu[] = \ElggMenuItem::factory([
			'name' => 'indieweb:websub:basic',
			'parent_name' => 'indieweb:websub',
			'href' => elgg_normalize_url('admin/indieweb/websub'),
			'text' => elgg_echo('settings:indieweb:websub'),
			'priority' => 510,
		]);
		
		$menu[] = \ElggMenuItem::factory([
			'name' => 'indieweb:websub:pub',
			'parent_name' => 'indieweb:websub',
			'href' => elgg_normalize_url('admin/indieweb/websub/pub'),
			'text' => elgg_echo('admin:indieweb:websub:pub'),
			'priority' => 520,
		]);
		
		//Contacts
		$menu[] = \ElggMenuItem::factory([
			'name' => 'indieweb:contacts',
			'parent_name' => 'indieweb',
			'href' => elgg_normalize_url('admin/indieweb/contacts'),
			'text' => elgg_echo('admin:indieweb:contacts'),
			'priority' => 600,
		]);

		return $menu;
	}
}
