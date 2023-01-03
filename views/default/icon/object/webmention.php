<?php
/**
 * Webmention icon view.
 *
 */

$entity = elgg_extract('entity', $vars);
if (!$entity instanceof \Elgg\IndieWeb\Webmention\Entity\Webmention) {
	return;
}

$size = elgg_extract('size', $vars, 'small');

$wrapper_class = [
	'elgg-avatar',
	"elgg-avatar-$size",
	'webmention-author-photo'
];
$wrapper_class = elgg_extract_class($vars, $wrapper_class);

$author_name = $entity->author_name ?? false;
$author_url = $entity->author_url ?? false;
$author_photo = $entity->author_photo ?? elgg_get_simplecache_url("icon/user/default/$size.gif");

if (isset($entity->author_thumbnail_url)) {
	$author_photo = $entity->author_thumbnail_url;
}

$icon = elgg_view('output/img', [
	'src' => $author_photo,
	'alt' => $author_name,
	'title' => $author_name,
	'class' => elgg_extract_class($vars, [], 'img_class'),
]);

if (empty($icon)) {
	return;
}

$content = '';

if (elgg_extract('use_link', $vars, true)) {
	$content .= elgg_view('output/url', [
		'href' => $author_url,
		'text' => $icon,
		'is_trusted' => true,
		'class' => elgg_extract_class($vars, [], 'link_class'),
	]);
} else {
	$content .= elgg_format_element('a', [], $icon);
}

echo elgg_format_element('div', ['class' => $wrapper_class], $content);
