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

$item = false;
$item_url = false;
$item_name = false;

if ($entity->entity_id > 0) {
	$item = get_entity($entity->entity_id);
	if ($item instanceof \ElggEntity) {
		$item_url = $item->getURL();
		$item_name = $item->getDisplayName();
	}
}

$params = [
	'icon' => false,
	'time_href' => $item_url,
	'access' => false,
	'title' => $entity->entity_type_id,
	'show_summary' => true,
	'content' => $item_name,
	'imprint' => elgg_extract('imprint', $vars, []),
	'byline' => false,
	'class' => elgg_extract_class($vars),
];

$params = $params + $vars;

echo elgg_view('object/elements/summary', $params);
