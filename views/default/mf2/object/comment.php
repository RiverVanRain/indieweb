<?php

use Elgg\Values;

$entity = elgg_extract('entity', $vars);
$container = $entity->getContainerEntity();

if (!$entity instanceof \ElggComment) {
	return;
}

if (!$container instanceof \ElggEntity) {
	return;
}

//author
$user = $entity->getOwnerEntity();

$fullname = htmlspecialchars($user->getDisplayName() ?? '', ENT_QUOTES, 'UTF-8', false);

$author = elgg_format_element('span', ['class' => 'p-author h-card'], elgg_view('output/url', [
	'class' => 'p-name u-url fn',
	'text' => $fullname,
	'href' => $user->getURL(),
]));

$date_created = Values::normalizeTime($entity->time_created);
$created = elgg_format_element('time', ['class' => 'dt-published', 'datetime' => $date_created->format('c')]);

$name = elgg_format_element('span', ['class' => 'p-name'], elgg_get_excerpt($entity->description, 75));
$summary = elgg_format_element('span', ['class' => 'p-summary'], $entity->description);

$reply = elgg_view('output/url', [
	'class' => 'u-in-reply-to',
	'text' => htmlspecialchars($container->getDisplayName() ?? '', ENT_QUOTES, 'UTF-8', false),
	'href' => $container->getURL(),
]);

echo elgg_format_element('div', ['class' => 'h-cite p-comment hidden'], $author . $created . $name . $summary . $reply);

