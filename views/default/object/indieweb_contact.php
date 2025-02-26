<?php
/**
 * Contact view.
 *
 */

$entity = elgg_extract('entity', $vars);
if (!$entity instanceof \Elgg\IndieWeb\Contacts\Entity\Contact) {
	return;
}

$vars['entity'] = $entity;

$params = [
	'icon' => elgg_view_entity_icon($entity, 'small'),
	'time_href' => (string) $entity->website ?: false,
	'access' => false,
	'show_summary' => true,
	'content' => false,
	'imprint' => (array) elgg_extract('imprint', $vars, []),
	'byline' => false,
	'class' => elgg_extract_class($vars),
];

// nickname
if (!empty($entity->nickname)) {
	$params['imprint'][] = [
		'icon_name' => 'at',
		'content' => (string) $entity->nickname,
	];
}

$params = $params + $vars;

echo elgg_view('object/elements/summary', $params);
