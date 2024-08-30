<?php
/**
 * IndieWeb
 * @author Nikolai Shcherbin
 * @license GNU Affero General Public License version 3
 * @copyright (c) Nikolai Shcherbin 2022
 * @link https://wzm.me
**/

namespace Elgg\IndieWeb;

use Elgg\Includer;
use Elgg\DefaultPluginBootstrap;

class Bootstrap extends DefaultPluginBootstrap {

	/**
	 * Get plugin root
	 * @return string
	 */
	protected function getRoot() {
		return $this->plugin->getPath();
	}

	/**
	 * Executed during 'plugin_boot:before', 'system' event
	 *
	 * Allows the plugin to require additional files, as well as configure services prior to booting the plugin
	 *
	 * @return void
	 */
	public function load() {
		Includer::requireFileOnce($this->getRoot() . '/lib/functions.php');
	}
	
	/**
	 * Executed during 'plugin_boot:before', 'system' event
	 *
	 * Allows the plugin to register handlers for 'plugin_boot', 'system' and 'init', 'system' events,
	 * as well as implement boot time logic
	 *
	 * @return void
	 */
	public function boot() {

	}

	/**
	 * Executed during 'init', 'system' event
	 *
	 * Allows the plugin to implement business logic and register all other handlers
	 *
	 * @return void
	 */
	public function init() {
		elgg_register_external_file('css', 'openwebicons', elgg_get_simplecache_url('openwebicons/css/openwebicons.min.css'));
		elgg_load_external_file('css', 'openwebicons');
		
		if (elgg_is_active_plugin('elgg_hybridauth')) {
			elgg_extend_view('hybridauth/extend_connections', 'indieauth/authorize');
		}
		
		if (!elgg_is_active_plugin('theme')) {
			$objects = (array) elgg_extract('object', elgg_entity_types_with_capability('searchable'), []);
			foreach ($objects as $subtype) {
				if (in_array($subtype, ['river_object', 'messages', 'newsletter', 'static', 'file', 'event', 'poll', 'comment'])) {
					continue;
				}
				
				$form_view = elgg_view_exists("forms/$subtype/save") ? "forms/$subtype/save" : (elgg_view_exists("forms/$subtype/add") ? "forms/$subtype/add" : false);
				
				if ((bool) elgg_get_plugin_setting('enable_webmention', 'indieweb') && (bool) elgg_get_plugin_setting("can_webmention:object:$subtype", 'indieweb') && $form_view) {
					elgg_extend_view($form_view, 'input/webmention/syndication_targets');
				}

				if ((bool) elgg_get_plugin_setting('enable_websub', 'indieweb') && (bool) elgg_get_plugin_setting("can_websub:object:$subtype", 'indieweb') && $form_view) {
					elgg_extend_view($form_view, 'input/websub/hub_publication');
				}
			}
		}
	}
	
	/**
	 * Executed during 'ready', 'system' event
	 *
	 * Allows the plugin to implement logic after all plugins are initialized
	 *
	 * @return void
	 */
	public function ready() {

	}

	/**
	 * Executed during 'shutdown', 'system' event
	 *
	 * Allows the plugin to implement logic during shutdown
	 *
	 * @return void
	 */
	public function shutdown() {

	}
	
	/**
	 * Executed when plugin is activated, after 'activate', 'plugin' event and before activate.php is included
	 *
	 * @return void
	 */
	public function activate() {

	}

	/**
	 * Executed when plugin is deactivated, after 'deactivate', 'plugin' event and before deactivate.php is included
	 *
	 * @return void
	 */
	public function deactivate() {

	}

	/**
	 * Registered as handler for 'upgrade', 'system' event
	 *
	 * Allows the plugin to implement logic during system upgrade
	 *
	 * @return void
	 */
	public function upgrade() {

	}

}