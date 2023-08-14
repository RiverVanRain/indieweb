<?php
/**
 * IndieWeb
 * @author Nikolai Shcherbin
 * @license GNU Affero General Public License version 3
 * @copyright (c) Nikolai Shcherbin 2022
 * @link https://wzm.me
**/

namespace Elgg\IndieWeb\Menus;

class SettingsMenu {

	/**
	 * Setup page menu
	 *
	 * @param Hook $hook Hook
	 */
	public function __invoke(\Elgg\Hook $hook) {

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
			'icon' => '<i class="openwebicons-indieweb" style="font-size: 16px;"></i>',
		]);

		//Webmention
		$menu[] = \ElggMenuItem::factory([
			'name' => 'indieweb:webmention',
			'parent_name' => 'indieweb',
			'href' => false,
			'text' => elgg_echo('admin:indieweb:webmention'),
			'context' => ['admin'],
			'section' => 'configure',
			'priority' => 100,
		]);
		
		$menu[] = \ElggMenuItem::factory([
			'name' => 'indieweb:webmention:basic',
			'parent_name' => 'indieweb:webmention',
			'href' => elgg_normalize_url('admin/indieweb/webmention'),
			'text' => elgg_echo('settings:indieweb:webmention'),
			'context' => ['admin'],
			'section' => 'configure',
			'priority' => 110,
		]);
		
		$menu[] = \ElggMenuItem::factory([
			'name' => 'indieweb:webmention:received',
			'parent_name' => 'indieweb:webmention',
			'href' => elgg_normalize_url('admin/indieweb/webmention/received'),
			'text' => elgg_echo('admin:indieweb:webmention:received'),
			'context' => ['admin'],
			'section' => 'configure',
			'priority' => 120,
		]);
	
		$menu[] = \ElggMenuItem::factory([
			'name' => 'indieweb:webmention:send',
			'parent_name' => 'indieweb:webmention',
			'href' => elgg_normalize_url('admin/indieweb/webmention/send'),
			'text' => elgg_echo('admin:indieweb:webmention:send'),
			'context' => ['admin'],
			'section' => 'configure',
			'priority' => 130,
		]);
		
		$menu[] = \ElggMenuItem::factory([
			'name' => 'indieweb:webmention:syndications',
			'parent_name' => 'indieweb:webmention',
			'href' => elgg_normalize_url('admin/indieweb/webmention/syndications'),
			'text' => elgg_echo('admin:indieweb:webmention:syndications'),
			'context' => ['admin'],
			'section' => 'configure',
			'priority' => 130,
		]);
		
		//Micropub
		$menu[] = \ElggMenuItem::factory([
			'name' => 'indieweb:micropub',
			'parent_name' => 'indieweb',
			'href' => false,
			'text' => elgg_echo('admin:indieweb:micropub'),
			'context' => ['admin'],
			'section' => 'configure',
			'priority' => 200,
		]);
		
		$menu[] = \ElggMenuItem::factory([
			'name' => 'indieweb:micropub:basic',
			'parent_name' => 'indieweb:micropub',
			'href' => elgg_normalize_url('admin/indieweb/micropub'),
			'text' => elgg_echo('settings:indieweb:micropub'),
			'context' => ['admin'],
			'section' => 'configure',
			'priority' => 210,
		]);
		
		$menu[] = \ElggMenuItem::factory([
			'name' => 'indieweb:micropub:posts',
			'parent_name' => 'indieweb:micropub',
			'href' => elgg_normalize_url('admin/indieweb/micropub/posts'),
			'text' => elgg_echo('settings:indieweb:micropub:posts'),
			'context' => ['admin'],
			'section' => 'configure',
			'priority' => 220,
		]);
		
		//Microsub
		$menu[] = \ElggMenuItem::factory([
			'name' => 'indieweb:microsub',
			'parent_name' => 'indieweb',
			'href' => false,
			'text' => elgg_echo('admin:indieweb:microsub'),
			'context' => ['admin'],
			'section' => 'configure',
			'priority' => 300,
		]);
		$menu[] = \ElggMenuItem::factory([
			'name' => 'indieweb:microsub:basic',
			'parent_name' => 'indieweb:microsub',
			'href' => elgg_normalize_url('admin/indieweb/microsub'),
			'text' => elgg_echo('settings:indieweb:microsub'),
			'context' => ['admin'],
			'section' => 'configure',
			'priority' => 310,
		]);
		
		$menu[] = \ElggMenuItem::factory([
			'name' => 'indieweb:microsub:channels',
			'parent_name' => 'indieweb:microsub',
			'href' => elgg_normalize_url('admin/indieweb/microsub/channels'),
			'text' => elgg_echo('admin:indieweb:microsub:channels'),
			'context' => ['admin'],
			'section' => 'configure',
			'priority' => 320,
		]);
		
		//IndieAuth
		$menu[] = \ElggMenuItem::factory([
			'name' => 'indieweb:indieauth',
			'parent_name' => 'indieweb',
			'href' => false,
			'text' => elgg_echo('admin:indieweb:indieauth'),
			'context' => ['admin'],
			'section' => 'configure',
			'priority' => 400,
		]);
		
		$menu[] = \ElggMenuItem::factory([
			'name' => 'indieweb:indieauth:basic',
			'parent_name' => 'indieweb:indieauth',
			'href' => elgg_normalize_url('admin/indieweb/indieauth'),
			'text' => elgg_echo('settings:indieweb:indieauth'),
			'context' => ['admin'],
			'section' => 'configure',
			'priority' => 410,
		]);
		
		$menu[] = \ElggMenuItem::factory([
			'name' => 'indieweb:indieauth:tokens',
			'parent_name' => 'indieweb:indieauth',
			'href' => elgg_normalize_url('admin/indieweb/indieauth/tokens'),
			'text' => elgg_echo('settings:indieweb:indieauth:tokens'),
			'context' => ['admin'],
			'section' => 'configure',
			'priority' => 420,
		]);
		
		$menu[] = \ElggMenuItem::factory([
			'name' => 'indieweb:indieauth:codes',
			'parent_name' => 'indieweb:indieauth',
			'href' => elgg_normalize_url('admin/indieweb/indieauth/codes'),
			'text' => elgg_echo('settings:indieweb:indieauth:codes'),
			'context' => ['admin'],
			'section' => 'configure',
			'priority' => 430,
		]);
		
		//WebSub
		$menu[] = \ElggMenuItem::factory([
			'name' => 'indieweb:websub',
			'parent_name' => 'indieweb',
			'href' => false,
			'text' => elgg_echo('admin:indieweb:websub'),
			'context' => ['admin'],
			'section' => 'configure',
			'priority' => 500,
		]);
		
		$menu[] = \ElggMenuItem::factory([
			'name' => 'indieweb:websub:basic',
			'parent_name' => 'indieweb:websub',
			'href' => elgg_normalize_url('admin/indieweb/websub'),
			'text' => elgg_echo('settings:indieweb:websub'),
			'context' => ['admin'],
			'section' => 'configure',
			'priority' => 510,
		]);
		
		$menu[] = \ElggMenuItem::factory([
			'name' => 'indieweb:websub:pub',
			'parent_name' => 'indieweb:websub',
			'href' => elgg_normalize_url('admin/indieweb/websub/pub'),
			'text' => elgg_echo('admin:indieweb:websub:pub'),
			'context' => ['admin'],
			'section' => 'configure',
			'priority' => 520,
		]);
		
		return $menu;
	}
}
