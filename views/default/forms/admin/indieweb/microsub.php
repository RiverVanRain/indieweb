<?php

$entity = elgg_get_plugin_from_id('indieweb');

//Basic
echo elgg_view_field([
	'#type' => 'fieldset',
	'legend' => elgg_echo('settings:indieweb:microsub'),
	'fields' => [
		[
			'#type' => 'checkbox',
			'#label' => elgg_echo('settings:indieweb:enable_microsub'),
			'#help' => elgg_echo('settings:indieweb:enable_microsub:help'),
			'name' => 'params[enable_microsub]',
			'value' => 1,
			'default' => 0,
			'checked' => (bool) $entity->enable_microsub,
			'switch' => true,
		],
		[
			'#type' => 'url',
			'name' => 'params[microsub_endpoint]',
			'value' => $entity->microsub_endpoint ?: '',
			'#label' => elgg_echo('settings:indieweb:microsub_endpoint'),
			'#help' => elgg_echo('settings:indieweb:microsub_endpoint:help'),
		],
		[
			'#type' => 'checkbox',
			'#label' => elgg_echo('indieweb:microsub:anonymous'),
			'#help' => elgg_echo('indieweb:microsub:anonymous:help'),
			'name' => 'params[microsub_anonymous]',
			'value' => 1,
			'default' => 0,
			'checked' => (bool) $entity->microsub_anonymous,
			'switch' => true,
		],
		[
			'#type' => 'checkbox',
			'#label' => elgg_echo('indieweb:microsub:cleanup_feeds'),
			'#help' => elgg_echo('indieweb:microsub:cleanup_feeds:help'),
			'name' => 'params[microsub_cleanup_feeds]',
			'value' => 1,
			'default' => 0,
			'checked' => (bool) $entity->microsub_cleanup_feeds,
			'switch' => true,
		],
		[
			'#type' => 'checkbox',
			'#label' => elgg_echo('indieweb:microsub:mark_unread'),
			'#help' => elgg_echo('indieweb:microsub:mark_unread:help'),
			'name' => 'params[microsub_mark_unread]',
			'value' => 1,
			'default' => 0,
			'checked' => (bool) $entity->microsub_mark_unread,
			'switch' => true,
		],
		[
			'#type' => 'checkbox',
			'#label' => elgg_echo('indieweb:microsub:allow_video'),
			'#help' => elgg_echo('indieweb:microsub:allow_video:help'),
			'name' => 'params[microsub_allow_video]',
			'value' => 1,
			'default' => 0,
			'checked' => (bool) $entity->microsub_allow_video,
			'switch' => true,
		],
		[
			'#type' => 'text',
			'name' => 'params[microsub_user_agent]',
			'value' => $entity->microsub_user_agent ?: 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/96.0.4664.55 Safari/537.36',
			'#label' => elgg_echo('settings:indieweb:microsub_user_agent'),
		],
	],
]);

//Post context
echo elgg_view_field([
	'#type' => 'fieldset',
	'legend' => elgg_echo('indieweb:microsub:context'),
	'fields' => [
		[
			'#type' => 'checkbox',
			'#label' => elgg_echo('indieweb:microsub:context:label'),
			'#help' => elgg_echo('indieweb:microsub:context:help'),
			'name' => 'params[microsub_post_context]',
			'value' => 1,
			'default' => 0,
			'checked' => (bool) $entity->microsub_post_context,
			'switch' => true,
		],
	],
]);

//Aggregated feeds
$feeds = \Elgg\IndieWeb\Microsub\Client\MicrosubClient::aggregatedFeeds();

echo elgg_view_field([
	'#type' => 'fieldset',
	'legend' => elgg_echo('indieweb:microsub:aggregated_feeds'),
	'fields' => [
		[
			'#type' => 'plaintext',
			'#label' => false,
			'#help' => elgg_echo('indieweb:microsub:aggregated_feeds:help'),
			'name' => 'params[microsub_aggregated_feeds]',
			'value' => (!empty($feeds)) ? implode("\r\n", $feeds) : '',
		],
	],
]);

//Indigenous
echo elgg_view_field([
	'#type' => 'fieldset',
	'legend' => elgg_echo('indieweb:microsub:indigenous'),
	'fields' => [
		[
			'#type' => 'checkbox',
			'#label' => elgg_echo('indieweb:microsub:indigenous_send_push'),
			'name' => 'params[microsub_indigenous_send_push]',
			'value' => 1,
			'default' => 0,
			'checked' => (bool) $entity->microsub_indigenous_send_push,
			'switch' => true,
		],
		[
			'#type' => 'text',
			'name' => 'params[microsub_indigenous_api]',
			'value' => $entity->microsub_indigenous_api ?: '',
			'#label' => elgg_echo('indieweb:microsub:indigenous_api'),
			'#help' => elgg_echo('indieweb:microsub:indigenous_api:help'),
		],
	],
]);

//Aperture
echo elgg_view_field([
	'#type' => 'fieldset',
	'legend' => elgg_echo('indieweb:microsub:aperture'),
	'fields' => [
		[
			'#type' => 'checkbox',
			'#label' => elgg_echo('indieweb:microsub:aperture_send_push'),
			'name' => 'params[microsub_aperture_send_push]',
			'value' => 1,
			'default' => 0,
			'checked' => (bool) $entity->microsub_aperture_send_push,
			'switch' => true,
		],
		[
			'#type' => 'text',
			'name' => 'params[microsub_aperture_api]',
			'value' => $entity->microsub_aperture_api ?: '',
			'#label' => elgg_echo('indieweb:microsub:aperture_api'),
			'#help' => elgg_echo('indieweb:microsub:aperture_api:help'),
		],
	],
]);

echo elgg_view_field([
	'#type' => 'hidden',
	'name' => 'plugin_id',
	'value' => 'indieweb',
]);

$footer = elgg_view_field([
	'#type' => 'submit',
	'value' => elgg_echo('save'),
]);

elgg_set_form_footer($footer);
