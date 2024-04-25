<?php
/**
 * IndieWeb
 * @author Nikolai Shcherbin
 * @license GNU Affero General Public License version 3
 * @copyright (c) Nikolai Shcherbin 2022
 * @link https://wzm.me
**/

use Elgg\Router\Middleware\AdminGatekeeper;

$webmention_commentable = false;
$webmention_likable = false;

if ((bool) elgg_get_plugin_setting('enable_webmention', 'indieweb') && (bool) elgg_get_plugin_setting('webmention_enable_comment_create', 'indieweb')) {
	$webmention_commentable = true;
}

if ((bool) elgg_get_plugin_setting('enable_webmention', 'indieweb') && (bool) elgg_get_plugin_setting('webmention_enable_likes', 'indieweb')) {
	$webmention_likable = true;
}

return [
	'plugin' => [
		'name' => 'IndieWeb',
		'version' => '1.1.4',
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
				'likable' => $webmention_likable,
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
		//IndieAuth
		[
			'type' => 'object',
			'subtype' => 'indieauth_token',
			'class' => \Elgg\IndieWeb\IndieAuth\Entity\IndieAuthToken::class,
			'capabilities' => [
				'commentable' => false,
				'likable' => false,
				'searchable' => false,
			],
		],
		[
			'type' => 'object',
			'subtype' => 'indieauth_code',
			'class' => \Elgg\IndieWeb\IndieAuth\Entity\IndieAuthAuthorizationCode::class,
			'capabilities' => [
				'commentable' => false,
				'likable' => false,
				'searchable' => false,
			],
		],
		//WebSub 
		[
			'type' => 'object',
			'subtype' => 'websubpub',
			'class' => \Elgg\IndieWeb\WebSub\Entity\WebSubPub::class,
			'capabilities' => [
				'commentable' => false,
				'likable' => false,
				'searchable' => false,
			],
		],
		[
			'type' => 'object',
			'subtype' => 'websubnotofication',
			'class' => \Elgg\IndieWeb\WebSub\Entity\WebSubNotification::class,
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
		'admin/indieweb/micropub/posts' => [
			'controller' => \Elgg\IndieWeb\Actions\SettingsAction::class,
			'access' => 'admin',
		],
		'admin/indieweb/microsub' => [
			'controller' => \Elgg\IndieWeb\Actions\SettingsAction::class,
			'access' => 'admin',
		],
		'admin/indieweb/indieauth' => [
			'controller' => \Elgg\IndieWeb\IndieAuth\Actions\SettingsAction::class,
			'access' => 'admin',
		],
		'admin/indieweb/websub' => [
			'controller' => \Elgg\IndieWeb\Actions\SettingsAction::class,
			'access' => 'admin',
		],
		//microsub
		'microsub/channel/edit' => [
			'controller' => \Elgg\IndieWeb\Microsub\Actions\EditMicrosubChannelAction::class,
			'access' => 'admin',
		],
		'microsub/channel/toggle_status' => [
			'controller' => \Elgg\IndieWeb\Microsub\Actions\MicrosubChannel\ToggleStatusAction::class,
			'access' => 'admin',
		],
		'microsub/channel/notifications' => [
			'controller' => \Elgg\IndieWeb\Microsub\Actions\NotificationsMicrosubChannelAction::class,
			'access' => 'admin',
		],
		'microsub/source/edit' => [
			'controller' => \Elgg\IndieWeb\Microsub\Actions\EditMicrosubSourceAction::class,
			'access' => 'admin',
		],
		'microsub/source/toggle_status' => [
			'controller' => \Elgg\IndieWeb\Microsub\Actions\MicrosubSource\ToggleStatusAction::class,
			'access' => 'admin',
		],
		//indieauth
		'indieauth/authorize' => [
			'controller' => \Elgg\IndieWeb\IndieAuth\Actions\AuthorizeAction::class,
			'access' => 'admin',
		],
		'indieauth/deauthorize' => [
			'controller' => \Elgg\IndieWeb\IndieAuth\Actions\DeauthorizeAction::class,
			'access' => 'admin',
		],
		'indieauth/login' => [
			'controller' => \Elgg\IndieWeb\IndieAuth\Actions\LoginAction::class,
			'access' => 'public',
		],
		'indieauth/cancel' => [
			'controller' => \Elgg\IndieWeb\IndieAuth\Actions\CancelAction::class,
		],
		'indieauth/token/toggle_status' => [
			'controller' => \Elgg\IndieWeb\IndieAuth\Actions\Token\ToggleStatusAction::class,
			'access' => 'admin',
		],
		'indieauth/token/save' => [
			'controller' => \Elgg\IndieWeb\IndieAuth\Actions\Token\SaveAction::class,
			'access' => 'admin',
		],
		//core
		'blog/save' => [
			'controller' => \Elgg\IndieWeb\Actions\Blog\SaveAction::class,
		],
	],

	//EVENTS
	'events' => [
		'create:after' => [
			'object' => [
				//webmention
				'Elgg\IndieWeb\Webmention\Events\Events::createObject' => [],
				//websub
				'Elgg\IndieWeb\WebSub\Events\Events::createObject' => [],
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
		'cron' => [
			'fiveminute' => [
				// Webmention
				'Elgg\IndieWeb\Webmention\Cron::processWebmentions' => [],
				// WebSub
				'Elgg\IndieWeb\WebSub\Cron::processWebSubPub' => [],
				'Elgg\IndieWeb\WebSub\Cron::processNotifications' => [],
			],
			'fifteenmin' => [
				// Microsub
				'Elgg\IndieWeb\Microsub\Cron::fifteenminFeedUpdate' => [],
			],
			'halfhour' => [
				// Microsub
				'Elgg\IndieWeb\Microsub\Cron::halfhourFeedUpdate' => [],
			],
			'hourly' => [
				// Microsub
				'Elgg\IndieWeb\Microsub\Cron::hourlyFeedUpdate' => [],
			],
			'daily' => [
				// Webmention
				'Elgg\IndieWeb\Webmention\Cron::emptySyndications' => [],
				'Elgg\IndieWeb\Webmention\Cron::emptyFailedWebmentions' => [],
				// IndieAuth
				'Elgg\IndieWeb\IndieAuth\Cron::processCodes' => [],
				// WebSub
				'Elgg\IndieWeb\WebSub\Cron::cleanupWebSubPub' => [],
				'Elgg\IndieWeb\WebSub\Cron::emptyWebSubPub' => [],
				// Microsub
				'Elgg\IndieWeb\Microsub\Cron::dailyFeedUpdate' => [],
			],
			'weekly' => [
				// WebSub
				'Elgg\IndieWeb\WebSub\Cron::processSubscribe' => [],
				// Microsub
				'Elgg\IndieWeb\Microsub\Cron::weeklyFeedUpdate' => [],
			],
			'monthly' => [
				// Microsub
				'Elgg\IndieWeb\Microsub\Cron::monthlyFeedUpdate' => [],
			],
		],
		'head' => [
			'page' => [
				\Elgg\IndieWeb\Views\SetupHead::class => [],
			],
		],
		'permissions_check' => [
			'object' => [
				'Elgg\IndieWeb\IndieAuth\Permissions\AuthorizationCode::canEdit' => [],
				'Elgg\IndieWeb\IndieAuth\Permissions\Token::canEdit' => [],
			],
		],
		'permissions_check:delete' => [
			'object' => [
				'Elgg\IndieWeb\IndieAuth\Permissions\AuthorizationCode::canDelete' => [],
				'Elgg\IndieWeb\IndieAuth\Permissions\Token::canDelete' => [],
			],
		],
		'register' => [
			'menu:admin_header' => [
				\Elgg\IndieWeb\Menus\SettingsMenu::class => [],
			],
			'menu:entity' => [
				// Microsub
				'Elgg\IndieWeb\Microsub\Menus\EntityMenu::microsubChannelEntityMenu' => [],
				'Elgg\IndieWeb\Microsub\Menus\EntityMenu::microsubSourceEntityMenu' => [],
				// IndieAuth
				'Elgg\IndieWeb\IndieAuth\Menus\EntityMenu::codeEntityMenu' => [],
				'Elgg\IndieWeb\IndieAuth\Menus\EntityMenu::tokenEntityMenu' => [],
			],
			'menu:page' => [
				// IndieAuth
				\Elgg\IndieWeb\IndieAuth\Menus\UserPageMenu::class => [],
			],
			'menu:social' => [
				// Webmention
				\Elgg\IndieWeb\Webmention\Menus\SocialMenu::class => [],
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
	
	//ROUTES
	'routes' => [
		//webmention
		'default:view:webmention' => [
			'path' => '/webmention',
			'controller' => [\Elgg\IndieWeb\Webmention\Controller\WebmentionController::class, 'callback'],
			'walled' => false,
		],
		//micropub
		'default:view:micropub' => [
			'path' => '/micropub',
			'controller' => \Elgg\IndieWeb\Micropub\Controller\MicropubController::class,
			'walled' => false,
		],
		'view:micropub:media' => [
			'path' => '/micropub/media',
			'controller' => [\Elgg\IndieWeb\Micropub\Controller\MicropubController::class, 'mediaEndpoint'],
			'walled' => false,
		],
		//microsub
		'default:view:microsub' => [
			'path' => '/microsub',
			'controller' => \Elgg\IndieWeb\Microsub\Controller\MicrosubController::class,
			'walled' => false,
		],
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
		'add:object:microsub_channel:notifications' => [
			'path' => '/microsub/channel/add/notifications/{guid?}',
			'resource' => 'microsub/channel/add_notifications',
			'middleware' => [
				AdminGatekeeper::class,
			],
		],
		//indieauth
		'indieauth:login' => [
			'path' => '/indieauth/login',
			'controller' => \Elgg\IndieWeb\IndieAuth\Controller\LoginController::class,
			'walled' => false,
		],
		'indieauth:auth' => [
			'path' => '/indieauth/auth',
			'controller' => \Elgg\IndieWeb\IndieAuth\Controller\IndieAuthController::class,
			'walled' => false,
		],
		'indieauth:auth:form' => [
			'path' => '/indieauth/authorize',
			'controller' => \Elgg\IndieWeb\IndieAuth\Controller\AuthFormController::class,
			'walled' => false,
		],
		'indieauth:token' => [
			'path' => '/indieauth/token',
			'controller' => \Elgg\IndieWeb\IndieAuth\Controller\TokenController::class,
			'walled' => false,
		],
		'indieauth:accounts' => [
            'path' => '/indieauth/accounts/{username?}',
            'resource' => 'indieauth/connections',
			'detect_page_owner' => true,
			'middleware' => [
				\Elgg\Router\Middleware\Gatekeeper::class,
			],
        ],
		//websub
		'default:view:websub' => [
			'path' => '/websub/{websub_hash}',
			'controller' => \Elgg\IndieWeb\WebSub\Controller\WebSubController::class,
			'walled' => false,
		],
	],
	
	//VIEWS
	'view_extensions' => [
		'elgg.css' => [
            'theme/indieweb.css' => ['priority' => 900],
        ],
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
		'forms/login' => [
			'indieauth/login' => [
				'priority' => 1000
			],
		],
		'forms/register' => [
			'indieauth/login' => [
				'priority' => 1000
			],
		],
    ],
	
	'views' => [
		'default' => [
			'openwebicons/' => __DIR__ . '/vendor/pfefferle/openwebicons/',
		],
	],
	
	'view_options' => [
		// Microsub
		'resources/microsub/channel/add' => ['ajax' => true],
		'resources/microsub/channel/edit' => ['ajax' => true],
		'resources/microsub/source/add' => ['ajax' => true],
		'resources/microsub/source/edit' => ['ajax' => true],
		'resources/microsub/source/item' => ['ajax' => true],
		'resources/microsub/channel/add_notifications' => ['ajax' => true],
		// IndieAuth
		'indieauth/token/jwt' => ['ajax' => true],
		'forms/indieauth/token/save' => ['ajax' => true],
	],
	
	//SETTINGS
	'settings' => [
		// Webmention
		'enable_webmention' => true,
		'webmention_enable_debug' => false,
		'webmention_enable_comment_create' => false,
		'webmention_enable_likes' => false,
		'webmention_create_contact' => false,
		'webmention_syndication_targets_custom' => true,
		'webmention_clean' => false,
		// Micropub
		'enable_micropub' => false,
		'enable_micropub_media' => false,
		'micropub_enable_update' => false,
		'micropub_enable_delete' => false,
		'micropub_enable_source' => false,
		'micropub_enable_category' => false,
		'micropub_enable_geo' => false,
		'micropub_enable_contact' => false,
		'micropub_log_payload' => false,
		// Microsub
		'enable_microsub' => false,
		'microsub_anonymous' => true,
		// IndieAuth
		'enable_indieauth_login' => false,
		'enable_indieauth_endpoint' => false,
		'indieauth_generate_keys' => false,
		// WebSub
		'enable_websub' => false,
		'websub_send' => false,
		'websub_resubscribe' => false,
		'websub_notification' => false,
		'websub_micropub_publish' => false,
		'websub_microsub_subscribe' => false,
		'websub_log_payload' => false,
		'websub_endpoint' => 'https://switchboard.p3k.io/',
		'websubpub_clean' => false,
	],
];