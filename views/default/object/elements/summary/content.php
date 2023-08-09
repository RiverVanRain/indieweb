<?php

/**
 * Outputs object summary content
 * @uses $vars['content'] Summary content
 */

use Elgg\Values;

$entity = elgg_extract('entity', $vars);
if (!$entity instanceof \ElggEntity) {
	return;
}

$content = elgg_extract('content', $vars);
if (!$content) {
	return;
}

if (!elgg_extract('full_view', $vars)) {	
	$content = elgg_format_element('div', ['class' => 'e-content'], $content); 

	$classes = 'h-entry';
	
	$title = elgg_format_element('span', ['class' => 'p-name'], $entity->getDisplayName());

	$link = elgg_format_element('a', ['class' => 'u-url', 'href' => $entity->getURL()], $entity->getDisplayName());

	$date_created = Values::normalizeTime($entity->time_created);
	$date_updated = Values::normalizeTime($entity->time_updated);

	$created = elgg_format_element('time', ['class' => 'dt-published', 'datetime' => $date_created->format('c')]);
	$updated = elgg_format_element('time', ['class' => 'dt-updated', 'datetime' => $date_updated->format('c')]);
	
	//author
	$author = false;
	$user = $entity->getOwnerEntity();
	if ($user instanceof \ElggUser) {
		$fullname = htmlspecialchars($user->getDisplayName() ?? '', ENT_QUOTES, 'UTF-8', false);
		$name = elgg_format_element('span', ['class' => 'p-name'], $fullname);
		$image = elgg_format_element('span', ['class' => 'u-photo', 'alt' => $fullname], $user->getIconURL('large'));
		$author_link = elgg_format_element('a', ['class' => 'u-url', 'href' => $user->getURL()], $image . $name);
		$author = elgg_format_element('div', ['class' => 'u-author h-card hidden'], $author_link);
	}

	$content .= elgg_format_element('div', ['class' => 'hidden'], $author . $link . $title . $created . $updated);
}

echo elgg_format_element('div', [
	'class' => [
		'elgg-listing-summary-content',
		'elgg-content',
		$classes
	]
], $content);
