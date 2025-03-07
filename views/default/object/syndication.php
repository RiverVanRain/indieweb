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

if ((int) $entity->source_id > 0) {
    $item = get_entity((int) $entity->source_id);
    if ($item instanceof \ElggEntity) {
        $item_url = (string) $item->getURL();
        $item_name = (string) $item->getDisplayName();
    }
}

$params = [
    'icon' => false,
    'time_href' => $item_url,
    'access' => false,
    'title' => $item_name,
    'show_summary' => true,
    'content' => (string) $entity->source_url,
    'imprint' => (array) elgg_extract('imprint', $vars, []),
    'byline' => false,
    'class' => elgg_extract_class($vars),
];

$params = $params + $vars;

echo elgg_view('object/elements/summary', $params);
