<?php

/**
 * Displays information about the author of the post
 *
 */

$entity = elgg_extract('entity', $vars);
if (!$entity instanceof \Elgg\IndieWeb\Webmention\Entity\Webmention) {
    return;
}

$parts = [];

$author_name = $entity->author_name ?? false;

if ($author_name) {
    $owner_text = elgg_view('output/url', [
        'text' => $author_name,
        'href' => $entity->author_url ?? false,
    ]);

    $parts[] = elgg_echo('indieweb:webmention:byline', [$owner_text]);
}

$byline_str = implode(' ', $parts);

if (elgg_is_empty($byline_str)) {
    return;
}

echo elgg_view('object/elements/imprint/element', [
    'content' => $byline_str,
    'class' => 'elgg-listing-byline',
]);
