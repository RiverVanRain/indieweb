<?php

/**
 * Generic icon view.
 *
 * @uses $vars['entity']     The entity the icon represents - uses getIconURL() method
 * @uses $vars['size']       topbar, tiny, small, medium (default), large, master
 * @uses $vars['use_link']   Hyperlink the icon
 * @uses $vars['href']       Optional override for link
 * @uses $vars['img_class']  Optional CSS class added to img
 * @uses $vars['link_class'] Optional CSS class for the link
 */

$entity = elgg_extract('entity', $vars);
if (!$entity instanceof \Elgg\IndieWeb\Contacts\Entity\Contact) {
    return;
}

$size = elgg_extract('size', $vars, 'small');

$wrapper_class = [
    'elgg-avatar',
    "elgg-avatar-$size",
    'webmention-author-photo'
];
$wrapper_class = elgg_extract_class($vars, $wrapper_class);

$author_name = $entity->getDisplayName();
$author_url = $entity->website ?? false;
$author_photo = $entity->photo ?? elgg_get_simplecache_url("icon/user/default/$size.gif");

if (isset($entity->thumbnail_url)) {
    $author_photo = $entity->thumbnail_url;
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
