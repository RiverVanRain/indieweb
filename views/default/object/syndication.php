<?php
/**
 * Syndication view.
 *
 */

$entity = elgg_extract('entity', $vars);
if (!$entity instanceof \Elgg\IndieWeb\Webmention\Entity\Syndication) {
	return;
}

$vars['entity'] = $entity;

if (!elgg_is_admin_logged_in()) {
	throw new \Elgg\Exceptions\Http\EntityNotFoundException();
}

$item = false;
$item_url = false;
$item_name = false;

if ($entity->source_id > 0) {
	$item = get_entity($entity->source_id);
	if ($item instanceof \ElggEntity) {
		$item_url = $item->getURL();
		$item_name = $item->getDisplayName();
	}
}

$params = [
	'icon' => false,
	'time_href' => $item->getURL(),
	'access' => false,
	'title' => $item_name,
	'show_summary' => true,
	'content' => $entity->source_url,
	'imprint' => elgg_extract('imprint', $vars, []),
	'byline' => false,
	'class' => elgg_extract_class($vars),
];

$params = $params + $vars;

echo elgg_view('object/elements/summary', $params);
