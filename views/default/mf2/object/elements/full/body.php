<?php

use Elgg\Values;

$entity = elgg_extract('entity', $vars);
if (!$entity instanceof \ElggEntity) {
	return;
}

$title = elgg_format_element('span', ['class' => 'p-name'], $entity->getDisplayName());

$date_created = Values::normalizeTime($entity->time_created);
$date_updated = Values::normalizeTime($entity->time_updated);

$created = elgg_format_element('time', ['class' => 'dt-published', 'datetime' => $date_created->format('c')]);
$updated = elgg_format_element('time', ['class' => 'dt-updated hidden', 'datetime' => $date_updated->format('c')]);

echo elgg_format_element('div', ['class' => 'hidden'], $title . $created . $updated);

//author
$user = $entity->getOwnerEntity();

if (!$user instanceof \ElggUser) {
	return;
}

$fullname = htmlspecialchars($user->getDisplayName() ?? '', ENT_QUOTES, 'UTF-8', false);

$link = elgg_format_element('a', ['class' => 'u-url u-uid', 'rel' => 'me', 'href' => $user->getURL()], $fullname);

$image = elgg_format_element('span', ['class' => 'u-photo'], $user->getIconURL('large'));

$name = elgg_format_element('span', ['class' => 'p-name fn'], $fullname);

$nickname = elgg_format_element('span', ['class' => 'p-nickname nickname'], $user->username);

echo elgg_format_element('div', ['class' => 'p-author h-card vcard hidden'], $name . $nickname . $link . $image);
