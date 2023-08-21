<?php

use Elgg\IndieWeb\Microsub\Entity\MicrosubSource;

$entity = elgg_extract('entity', $vars);

if ($entity instanceof MicrosubSource) {
	echo elgg_view_field([
		'#type' => 'hidden',
		'name' => 'guid',
		'value' => $entity->guid,
	]);
}

echo elgg_view_field([
	'#type' => 'text',
	'#label' => elgg_echo('title'),
	'name' => 'title',
	'value' => elgg_extract('title', $vars),
	'required' => true,
]);

echo elgg_view_field([
	'#type' => 'url',
	'#label' => elgg_echo('indieweb:microsub:microsub_source:url'),
	'name' => 'url',
	'value' => elgg_extract('url', $vars),
	'required' => true,
]);

echo elgg_view_field([
	'#type' => 'checkbox',
	'#label' => elgg_echo('indieweb:microsub:microsub_source:enable'),
	'name' => 'status',
	'value' => 1,
	'default' => 0,
	'checked' => (bool) elgg_extract('status', $vars),
	'switch' => true,
]);

echo elgg_view_field([
	'#type' => 'objectpicker',
	'#label' => elgg_echo('indieweb:microsub:microsub_source:channel'),
	'name' => 'container_guid',
	'value' => ($entity) ? $entity->container_guid : (int) elgg_extract('container_guid', $vars, get_input('container_guid')),
	'required' => true,
	'limit' => 1,
	'match_on' => 'objects',
	'subtype' => \Elgg\IndieWeb\Microsub\Entity\MicrosubChannel::SUBTYPE,
]);

//Types
$objects = [
	'reply',
	'repost',
	'bookmark',
	'like',
];

ob_start();
foreach ($objects as $subtype) {
	echo elgg_view_field([
		'#type' => 'checkbox',
		'name' => "microsub_source:post_context:$subtype",
		'#label' => elgg_echo("indieweb:microsub:post_type:$subtype"),
		'value' => 1,
		'default' => 0,
		'checked' => (bool) elgg_extract("microsub_source:post_context:$subtype", $vars),
		'switch' => true,
	]);
}
$inputs = ob_get_clean();

echo elgg_view('elements/forms/field', [
	'input' => $inputs,
	'label' => elgg_echo('indieweb:microsub:microsub_source:post_context'),
	'class' => 'elgg-loud',
]);

echo elgg_view_field([
	'#type' => 'select',
	'#label' => elgg_echo('indieweb:microsub:microsub_source:fetch_interval'),
	'#help' => elgg_echo('indieweb:microsub:microsub_source:fetch_interval:help'),
	'name' => 'fetch_interval',
	'value' => elgg_extract('fetch_interval', $vars),
	'options_values' => [
		0 => elgg_echo('indieweb:microsub:microsub_source:fetch_interval:none'),
		900 => elgg_echo('indieweb:microsub:microsub_source:fetch_interval:900'),
		1800 => elgg_echo('indieweb:microsub:microsub_source:fetch_interval:1800'),
		3600 => elgg_echo('indieweb:microsub:microsub_source:fetch_interval:3600'),
		86400 => elgg_echo('indieweb:microsub:microsub_source:fetch_interval:86400'),
		604800 => elgg_echo('indieweb:microsub:microsub_source:fetch_interval:604800'),
		2419200 => elgg_echo('indieweb:microsub:microsub_source:fetch_interval:2419200'),
	],
]);

echo elgg_view_field([
	'#type' => 'select',
	'#label' => elgg_echo('indieweb:microsub:microsub_source:items_to_keep'),
	'#help' => elgg_echo('indieweb:microsub:microsub_source:items_to_keep:help'),
	'name' => 'items_to_keep',
	'value' => elgg_extract('items_to_keep', $vars),
	'options_values' => [
		0 => 0,
		5 => 5,
		10 => 10,
		15 => 15,
		20 => 20,
		30 => 30,
		40 => 40,
		50 => 50,
		60 => 60,
		70 => 70,
		80 => 80,
		90 => 90,
		100 => 100,
		200 => 200,
		250 => 250,
		500 => 500,
		1000 => 1000,
	],
	'required' => true,
]);

echo elgg_view_field([
	'#type' => 'checkbox',
	'#label' => elgg_echo('indieweb:microsub:microsub_source:websub'),
	'#help' => elgg_echo('indieweb:microsub:microsub_source:websub:help'),
	'name' => 'websub',
	'value' => 1,
	'default' => 0,
	'checked' => (bool) elgg_extract('websub', $vars),
	'switch' => true,
]);

// form footer
$footer = elgg_view_field([
	'#type' => 'submit',
	'value' => elgg_echo('save'),
]);

elgg_set_form_footer($footer);
