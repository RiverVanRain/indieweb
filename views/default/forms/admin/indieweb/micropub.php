<?php

$entity = elgg_get_plugin_from_id('indieweb');

//Basic
echo elgg_view_field([
	'#type' => 'fieldset',
	//'legend' => elgg_echo('settings:indieweb:micropub'),
	'fields' => [
		[
			'#type' => 'checkbox',
			'#label' => elgg_echo('settings:indieweb:enable_micropub'),
			'#help' => elgg_echo('settings:indieweb:enable_micropub:help'),
			'name' => 'params[enable_micropub]',
			'value' => 1,
			'default' => 0,
			'checked' => (bool) $entity->enable_micropub,
			'switch' => true,
		],
	],
]);

echo elgg_view_field([
	'#html' => elgg_view('micropub/view'),
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
