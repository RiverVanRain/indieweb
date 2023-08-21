<?php

$entity = elgg_get_plugin_from_id('indieweb');

//Basic
echo elgg_view_field([
	'#type' => 'fieldset',
	'legend' => elgg_echo('settings:indieweb:webmention'),
	'fields' => [
		[
			'#type' => 'checkbox',
			'#label' => elgg_echo('settings:indieweb:enable_webmention'),
			'name' => 'params[enable_webmention]',
			'value' => 1,
			'default' => 0,
			'checked' => (bool) $entity->enable_webmention,
			'switch' => true,
		],
		[
			'#type' => 'text',
			'#label' => elgg_echo('settings:indieweb:webmention_proxy'),
			'name' => 'params[webmention_proxy]',
			'value' => $entity->webmention_proxy ?: '',
		],
		[
			'#type' => 'text',
			'#label' => elgg_echo('settings:indieweb:webmention_user_agent'),
			'name' => 'params[webmention_user_agent]',
			'value' => $entity->webmention_user_agent ?: '',
		],
		[
			'#type' => 'checkbox',
			'#label' => elgg_echo('settings:indieweb:webmention_enable_debug'),
			'name' => 'params[webmention_enable_debug]',
			'value' => 1,
			'default' => 0,
			'checked' => (bool) $entity->webmention_enable_debug,
			'switch' => true,
		],
		[
			'#type' => 'checkbox',
			'#label' => elgg_echo('settings:indieweb:webmention_enable_comment_create'),
			'#help' => elgg_echo('settings:indieweb:webmention_enable_comment_create:help'),
			'name' => 'params[webmention_enable_comment_create]',
			'value' => 1,
			'default' => 0,
			'checked' => (bool) $entity->webmention_enable_comment_create,
			'switch' => true,
		],
		[
			'#type' => 'checkbox',
			'#label' => elgg_echo('settings:indieweb:webmention_enable_likes'),
			'#help' => elgg_echo('settings:indieweb:webmention_enable_likes:help'),
			'name' => 'params[webmention_enable_likes]',
			'value' => 1,
			'default' => 0,
			'checked' => (bool) $entity->webmention_enable_likes,
			'switch' => true,
		],
		[
			'#type' => 'plaintext',
			'#label' => elgg_echo('settings:indieweb:webmention_blocked_domains'),
			'#help' => elgg_echo('settings:indieweb:webmention_blocked_domains:help'),
			'name' => 'params[webmention_blocked_domains]',
			'value' => (!empty($entity->webmention_blocked_domains)) ? implode("\r\n", $entity->webmention_blocked_domains) : '',
		],
		[
			'#type' => 'checkbox',
			'#label' => elgg_echo('settings:indieweb:webmention_clean'),
			'#help' => elgg_echo('settings:indieweb:webmention_clean:help'),
			'name' => 'params[webmention_clean]',
			'value' => 1,
			'default' => 0,
			'checked' => (bool) $entity->webmention_clean,
			'switch' => true,
		],
	],
]);

//Receiving
echo elgg_view_field([
	'#type' => 'fieldset',
	'legend' => elgg_echo('settings:indieweb:webmention:receiving'),
	'fields' => [
		[
			'#html' => elgg_format_element('div', ['class' => 'elgg-text-help mbm'], elgg_echo('settings:indieweb:webmention:receiving:help')),
		],
		[
			'#type' => 'url',
			'name' => 'params[webmention_server]',
			'value' => $entity->webmention_server ?: '',
			'#label' => elgg_echo('settings:indieweb:webmention_server'),
			'#help' => elgg_echo('settings:indieweb:webmention_server:help'),
		],
		[
			'#type' => 'checkbox',
			'#label' => elgg_echo('settings:indieweb:webmention_create_contact'),
			'#help' => elgg_echo('settings:indieweb:webmention_create_contact:help'),
			'name' => 'params[webmention_create_contact]',
			'value' => 1,
			'default' => 0,
			'checked' => (bool) $entity->webmention_create_contact,
			'switch' => true,
		],
		[
			'#type' => 'checkbox',
			'#label' => elgg_echo('settings:indieweb:webmention_detect_identical'),
			'#help' => elgg_echo('settings:indieweb:webmention_detect_identical:help'),
			'name' => 'params[webmention_detect_identical]',
			'value' => 1,
			'default' => 0,
			'checked' => (bool) $entity->webmention_detect_identical,
			'switch' => true,
		],
		[
			'#type' => 'number',
			'#label' => elgg_echo('settings:indieweb:webmention_excerpt'),
			'#help' => elgg_echo('settings:indieweb:webmention_excerpt:help'),
			'name' => 'params[webmention_excerpt]',
			'value' => $entity->webmention_excerpt ?: 0,
			'min' => 0,
			'max' => 10000,
		],
	],
]);

//Sending
$svc = elgg()->webmention;
$syndication_targets = $svc->getSyndicationTargets();

echo elgg_view_field([
	'#type' => 'fieldset',
	'legend' => elgg_echo('settings:indieweb:webmention:sending'),
	'fields' => [
		[
			'#html' => elgg_format_element('div', ['class' => 'elgg-text-help mbm'], elgg_echo('settings:indieweb:webmention:sending:help')),
		],
		[
			'#type' => 'plaintext',
			'name' => 'params[webmention_syndication_targets]',
			'value' => implode("\r\n", $syndication_targets),
			'#label' => elgg_echo('settings:indieweb:syndication_targets'),
			'#help' => elgg_echo('settings:indieweb:syndication_targets:help'),
		],
		[
			'#type' => 'checkbox',
			'#label' => elgg_echo('settings:indieweb:webmention_syndication_targets_custom'),
			'#help' => elgg_echo('settings:indieweb:webmention_syndication_targets_custom:help'),
			'name' => 'params[webmention_syndication_targets_custom]',
			'value' => 1,
			'default' => 0,
			'checked' => (bool) $entity->webmention_syndication_targets_custom,
			'switch' => true,
		],
	],
]);

//Objects
$objects = (array) elgg_extract('object', elgg_entity_types_with_capability('searchable'), []);

ob_start();
foreach ($objects as $subtype) {
	if (in_array($subtype, ['river_object', 'messages', 'newsletter', 'static', 'file', 'comment'])) {
		continue;
	}

	echo elgg_view_field([
		'#type' => 'checkbox',
		'name' => "params[can_webmention:object:$subtype]",
		'value' => 1,
		'default' => 0,
		'checked' => (bool) $entity->{"can_webmention:object:$subtype"},
		'#label' => elgg_echo("collection:object:$subtype"),
		'switch' => true,
	]);
}
$inputs = ob_get_clean();

echo elgg_view('elements/forms/field', [
	'input' => $inputs,
	'label' => elgg_echo('settings:indieweb:use_webmentions'),
	'class' => 'fa-1x3 elgg-loud',
]);

//slugs
echo elgg_view_field([
	'#type' => 'text',
	'#label' => elgg_echo('settings:indieweb:webmention_objects_slugs'),
	'#help' => elgg_echo('settings:indieweb:webmention_objects_slugs:help'),
	'name' => 'params[webmention_objects_slugs]',
	'value' => $entity->webmention_objects_slugs ?: '',
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
