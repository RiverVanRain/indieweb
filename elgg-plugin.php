<?php
/**
 * IndieWeb
 * @author Nikolai Shcherbin
 * @license GNU Affero General Public License version 3
 * @copyright (c) Nikolai Shcherbin 2022
 * @link https://wzm.me
**/

use Elgg\Router\Middleware\AjaxGatekeeper;
use Elgg\Router\Middleware\Gatekeeper;
use Elgg\Router\Middleware\AdminGatekeeper;

$webmention_commentable = false;

if ((bool) elgg_get_plugin_setting('enable_webmention', 'indieweb') && (bool) elgg_get_plugin_setting('webmention_enable_comment_create', 'indieweb')) {
	$webmention_commentable = true;
}

return [
	'plugin' => [
		'name' => 'IndieWeb',
		'version' => '1.0.0',
	],
	
	'bootstrap' => \Elgg\IndieWeb\Bootstrap::class,
	
	//ENTITIES
	'entities' => [
		//Webmention
		[
			'type' => 'object',
			'subtype' => 'webmention',
			'class' => \Elgg\IndieWeb\Webmention\Entity\Webmention::class,
			'capabilities' => [
				'commentable' => $webmention_commentable,
				'likable' => false,
				'searchable' => false,
			],
		],
		[
			'type' => 'object',
			'subtype' => 'syndication',
			'class' => \Elgg\IndieWeb\Webmention\Entity\Syndication::class,
			'capabilities' => [
				'commentable' => false,
				'likable' => false,
				'searchable' => false,
			],
		],
		//Contacts
		[
			'type' => 'object',
			'subtype' => 'indieweb_contact',
			'class' => \Elgg\IndieWeb\Contacts\Entity\Contact::class,
			'capabilities' => [
				'commentable' => false,
				'likable' => false,
				'searchable' => false,
			],
		],
		//MicroSub
		[
			'type' => 'object',
			'subtype' => 'microsub_channel',
			'class' => \Elgg\IndieWeb\Microsub\Entity\MicrosubChannel::class,
			'capabilities' => [
				'commentable' => false,
				'likable' => false,
				'searchable' => false,
			],
		],
		[
			'type' => 'object',
			'subtype' => 'microsub_source',
			'class' => \Elgg\IndieWeb\Microsub\Entity\MicrosubSource::class,
			'capabilities' => [
				'commentable' => false,
				'likable' => false,
				'searchable' => false,
			],
		],
		[
			'type' => 'object',
			'subtype' => 'microsub_item',
			'class' => \Elgg\IndieWeb\Microsub\Entity\MicrosubItem::class,
			'capabilities' => [
				'commentable' => false,
				'likable' => false,
				'searchable' => false,
			],
		],
	],
	
	//ACTIONS
	'actions' => [
		'admin/indieweb/webmention' => [
			'controller' => \Elgg\IndieWeb\Actions\SettingsAction::class,
			'access' => 'admin',
		],
		'admin/indieweb/micropub' => [
			'controller' => \Elgg\IndieWeb\Actions\SettingsAction::class,
			'access' => 'admin',
		],
		'admin/indieweb/microsub' => [
			'controller' => \Elgg\IndieWeb\Actions\SettingsAction::class,
			'access' => 'admin',
		],
		//microsub
		'microsub/channel/edit' => [
			'controller' => \Elgg\IndieWeb\Microsub\Actions\EditMicrosubChannelAction::class,
			'access' => 'admin',
		],
		'microsub/source/edit' => [
			'controller' => \Elgg\IndieWeb\Microsub\Actions\EditMicrosubSourceAction::class,
			'access' => 'admin',
		],
	],
	
	//HOOKS
	'hooks' => [
        'cron' => [
			'fiveminute' => [
				// Webmention
				'Elgg\IndieWeb\Webmention\Cron::processWebmentions' => [],
			],
		],
		'head' => [
			'page' => [
				\Elgg\IndieWeb\Views\SetupHead::class => [],
			],
		],
		'register' => [
			'menu:entity' => [
				'Elgg\IndieWeb\Microsub\Menus\EntityMenu::microsubChannelEntityMenu' => [],
				'Elgg\IndieWeb\Microsub\Menus\EntityMenu::microsubSourceEntityMenu' => [],
			],
			'menu:page' => [
				\Elgg\IndieWeb\Menus\SettingsMenu::class => [],
			],
		],
		'view_vars' => [
			'object/elements/full' => [
				'Elgg\IndieWeb\MF2\Views::fullBody' => ['priority' => 800],
			],
			'object/elements/full/body' => [
				'Elgg\IndieWeb\MF2\Views::contentBody' => ['priority' => 800],
			],
			'object/elements/imprint/contents' => [
				// Webmention
				\Elgg\IndieWeb\Webmention\Views::class => ['priority' => 600],
			],
		],
    ],
	
	//EVENTS
	'events' => [
		'create:after' => [
			'river' => [
				'Elgg\IndieWeb\Webmention\Events\Events::createObject' => [],
			],
		],
		'delete' => [
			'object' => [
				// microsub
				'Elgg\IndieWeb\Microsub\Events\Events::deleteChannel' => [],
				'Elgg\IndieWeb\Microsub\Events\Events::deleteSource' => [],
			],
		],
		'update:after' => [
			'object' => [
				// microsub
				'Elgg\IndieWeb\Microsub\Events\Events::updateSource' => [],
			],
		],
	],
	
	//ROUTES
	'routes' => [
		//webmention
		'default:view:webmention' => [
			'path' => '/webmention',
			'controller' => 'Elgg\IndieWeb\Webmention\Controller\WebmentionController::callback',
			'walled' => false,
		],
		//microsub
		'add:object:microsub_channel' => [
			'path' => '/microsub/channel/add/{guid?}',
			'resource' => 'microsub/channel/add',
			'middleware' => [
				AdminGatekeeper::class,
			],
		],
		'edit:object:microsub_channel' => [
			'path' => '/microsub/channel/edit/{guid?}',
			'resource' => 'microsub/channel/edit',
			'middleware' => [
				AdminGatekeeper::class,
			],
		],
		'add:object:microsub_source' => [
			'path' => '/microsub/source/add/{guid?}',
			'resource' => 'microsub/source/add',
			'middleware' => [
				AdminGatekeeper::class,
			],
		],
		'edit:object:microsub_source' => [
			'path' => '/microsub/source/edit/{guid?}',
			'resource' => 'microsub/source/edit',
			'middleware' => [
				AdminGatekeeper::class,
			],
		],
		'default:view:microsub' => [
			'path' => '/microsub',
			'controller' => 'Elgg\IndieWeb\Microsub\Controller\MicrosubController::callback',
			'walled' => false,
		],
	],
	
	//VIEWS
	'view_extensions' => [
		'object/elements/full/body' => [
            'mf2/object/elements/full/body' => [],
        ],
		'object/elements/full/responses' => [
            'webmention/responses/webmentions' => ['priority' => 600],
        ],
		'profile/fields' => [
			'mf2/profile/fields' => [],
		],
		'object/comment' => [
			'mf2/object/comment' => [],
        ],
    ],
	
	'view_options' => [
		//Microsub
		'resources/microsub/channel/add' => ['ajax' => true],
		'resources/microsub/channel/edit' => ['ajax' => true],
		'resources/microsub/source/add' => ['ajax' => true],
		'resources/microsub/source/edit' => ['ajax' => true],
	],
	
	//SETTINGS
	'settings' => [
		'enable_webmention' => true,
		'webmention_enable_debug' => false,
		'webmention_enable_comment_create' => false,
		'webmention_create_contact' => false,
		'enable_micropub' => false,
		'enable_microsub' => false,
	],
];