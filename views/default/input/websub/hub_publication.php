<?php

$entity = elgg_extract('entity', $vars);

if ($entity instanceof \ElggObject) {
	return;
}

echo elgg_view_field([
	'#type' => 'fieldset',
	'#class' => 'elgg-field elgg-col elgg-col-1of1',
	'#label' => elgg_echo('indieweb:websub:hub_publication'),
	'fields' => [
		[
			'#type' => 'checkbox',
			'name' => 'websub_hub_publication',
			'value' => 1,
			'default' => 0,
			'label' => elgg_echo('indieweb:websub:hub_publication:label'),
			'switch' => true,
		],
	],
]);

