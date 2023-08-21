<?php

use Elgg\Values;

$entity = elgg_extract('entity', $vars);
if (!$entity instanceof \ElggEntity) {
	return;
}

if ($entity instanceof \ElggComment) {
	return;
}

$title = elgg_format_element('span', ['class' => 'p-name'], $entity->getDisplayName());

$link = elgg_format_element('a', ['class' => 'u-url', 'href' => $entity->getURL()], $entity->getDisplayName());

$repost = false;
if (!empty($entity->website)) {
	$repost = elgg_format_element('span', ['class' => 'u-repost-of'], $entity->website);
}

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
	$author = elgg_format_element('div', ['class' => 'p-author h-card hidden'], $author_link);
}

echo elgg_format_element('div', ['class' => 'hidden'], $author . $link . $title . $repost . $created . $updated);
