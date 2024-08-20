<?php

$entity = elgg_get_plugin_from_id('indieweb');

//Basic
echo elgg_view_field([
	'#type' => 'fieldset',
	'legend' => elgg_echo('settings:indieweb:websub'),
	'fields' => [
		[
			'#type' => 'checkbox',
			'#label' => elgg_echo('settings:indieweb:enable_websub'),
			'#help' => elgg_echo('settings:indieweb:enable_websub:help'),
			'name' => 'params[enable_websub]',
			'value' => 1,
			'default' => 0,
			'checked' => (bool) $entity->enable_websub,
			'switch' => true,
		],
		[
			'#type' => 'url',
			'name' => 'params[websub_endpoint]',
			'value' => $entity->websub_endpoint ?: 'https://switchboard.p3k.io/',
			'#label' => elgg_echo('settings:indieweb:websub_endpoint'),
			'#help' => elgg_echo('settings:indieweb:websub_endpoint:help'),
			'required' => true,
		],
		[
			'#type' => 'plaintext',
			'name' => 'params[websub_pages]',
			'value' => $entity->websub_pages ?: elgg_get_site_url(),
			'#label' => elgg_echo('settings:indieweb:websub_pages'),
			'#help' => elgg_echo('settings:indieweb:websub_pages:help'),
			'required' => true,
		],
		[
			'#type' => 'checkbox',
			'#label' => elgg_echo('settings:indieweb:websub_send'),
			'#help' => elgg_echo('settings:indieweb:websub_send:help'),
			'name' => 'params[websub_send]',
			'value' => 1,
			'default' => 0,
			'checked' => (bool) $entity->websub_send,
			'switch' => true,
		],
		[
			'#type' => 'checkbox',
			'#label' => elgg_echo('settings:indieweb:websub_resubscribe'),
			'#help' => elgg_echo('settings:indieweb:websub_resubscribe:help'),
			'name' => 'params[websub_resubscribe]',
			'value' => 1,
			'default' => 0,
			'checked' => (bool) $entity->websub_resubscribe,
			'switch' => true,
		],
		[
			'#type' => 'checkbox',
			'#label' => elgg_echo('settings:indieweb:websub_notification'),
			'#help' => elgg_echo('settings:indieweb:websub_notification:help'),
			'name' => 'params[websub_notification]',
			'value' => 1,
			'default' => 0,
			'checked' => (bool) $entity->websub_notification,
			'switch' => true,
		],
		[
			'#type' => 'checkbox',
			'#label' => elgg_echo('settings:indieweb:websub_micropub_publish'),
			'name' => 'params[websub_micropub_publish]',
			'value' => 1,
			'default' => 0,
			'checked' => (bool) $entity->websub_micropub_publish,
			'switch' => true,
		],
		[
			'#type' => 'checkbox',
			'#label' => elgg_echo('settings:indieweb:websub_microsub_subscribe'),
			'name' => 'params[websub_microsub_subscribe]',
			'value' => 1,
			'default' => 0,
			'checked' => (bool) $entity->websub_microsub_subscribe,
			'switch' => true,
		],
		[
			'#type' => 'checkbox',
			'#label' => elgg_echo('settings:indieweb:websubpub_clean'),
			'#help' => elgg_echo('settings:indieweb:websubpub_clean:help'),
			'name' => 'params[websubpub_clean]',
			'value' => 1,
			'default' => 0,
			'checked' => (bool) $entity->websubpub_clean,
			'switch' => true,
		],
		[
			'#type' => 'checkbox',
			'#label' => elgg_echo('settings:indieweb:websub_log_payload'),
			'name' => 'params[websub_log_payload]',
			'value' => 1,
			'default' => 0,
			'checked' => (bool) $entity->websub_log_payload,
			'switch' => true,
		],
	],
]);

//Objects
$objects = (array) elgg_extract('object', elgg_entity_types_with_capability('searchable'), []);

ob_start();
foreach ($objects as $subtype) {
	if (in_array($subtype, ['river_object', 'messages', 'newsletter', 'static', 'file', 'event', 'poll', 'comment'])) {
		continue;
	}

	echo elgg_view_field([
		'#type' => 'checkbox',
		'name' => "params[can_websub:object:$subtype]",
		'value' => 1,
		'default' => 0,
		'checked' => (bool) $entity->{"can_websub:object:$subtype"},
		'#label' => elgg_echo("collection:object:$subtype"),
		'switch' => true,
	]);
}
$inputs = ob_get_clean();

echo elgg_view('elements/forms/field', [
	'input' => $inputs,
	'label' => elgg_echo('settings:indieweb:use_websub'),
	'class' => 'fa-1x3 elgg-loud',
]);

echo elgg_view_field([
	'#type' => 'hidden',
	'name' => 'plugin_id',
	'value' => 'indieweb',
]);

$footer = elgg_view_field([
	'#type' => 'submit',
	'text' => elgg_echo('save'),
]);

elgg_set_form_footer($footer);
