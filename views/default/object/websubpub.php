<?php
/**
 * WebSubPub view.
 *
 */

$entity = elgg_extract('entity', $vars);
if (!$entity instanceof \Elgg\IndieWeb\WebSub\Entity\WebSubPub) {
	return;
}

$vars['entity'] = $entity;

if (!elgg_is_admin_logged_in()) {
	throw new \Elgg\Exceptions\Http\EntityNotFoundException();
}

$params = [
	'icon' => false,
	'time_href' => $entity->time_created,
	'access' => false,
	'title' => false,
	'show_summary' => true,
	'content' => false,
	'imprint' => elgg_extract('imprint', $vars, []),
	'byline' => false,
	'class' => elgg_extract_class($vars),
];

$params = $params + $vars;

echo elgg_view('object/elements/summary', $params);
