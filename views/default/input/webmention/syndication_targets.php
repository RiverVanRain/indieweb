<?php

$entity = elgg_extract('entity', $vars);

if ($entity instanceof \ElggObject) {
	return;
}

$svc = elgg()->webmention;
$syndication_targets = $svc->getSyndicationTargets();

if (empty($syndication_targets)) {
	return;
}

$fields = [];

foreach ($syndication_targets as $target) {
	$target_name = stristr($target, '|', true);
	$target_url = explode('|', $target);
	
	$field = [
		'#type' => 'checkbox',
		'name' => 'syndication_targets[]',
		'value' => $target_url[1],
		'data-provider' => $target,
		'class' => 'webmention-syndication-targets-checkbox',
		'default' => false,
		'label' => $target_name,
		'switch' => true,
	];

	$fields[] = $field;
}

if ((bool) elgg_get_plugin_setting('webmention_syndication_targets_custom', 'indieweb')) {
   $fields[] = [
		'#type' => 'url',
		'class' => 'webmention-syndication-targets-custom',
		'name' => 'syndication_targets_custom_url[]',
		'data-provider' => 'custom_url',
		'#label' => elgg_echo('indieweb:webmention:syndication_targets_custom'),
	];
}

if (!empty($fields)) {
	echo elgg_view_field([
		'#type' => 'fieldset',
		'#class' => 'elgg-field elgg-col elgg-col-1of1',
		'#label' => elgg_echo('indieweb:webmention:publish'),
		'fields' => $fields,
	]);
}

