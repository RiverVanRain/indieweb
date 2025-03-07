<?php

/**
 * Webmention view.
 *
 */

$entity = elgg_extract('entity', $vars);
if (!$entity instanceof \Elgg\IndieWeb\Webmention\Entity\Webmention) {
    return;
}

$vars['entity'] = $entity;

if (!(bool) $entity->isPublished() && !elgg_is_admin_logged_in()) {
    throw new \Elgg\Exceptions\Http\EntityNotFoundException();
}

$body = '';

// content
$description = '';

if (!empty((string) $entity->content_html)) {
    $description = (string) $entity->content_html;
} elseif (!empty((string) $entity->content_text)) {
    $description = (string) $entity->content_text;
}

$excerpt = (int) elgg_get_plugin_setting('webmention_excerpt', 'indieweb');

if (!empty($description)) {
    $body .= elgg_view('output/longtext', [
        'value' => ($excerpt > 0) ? elgg_get_excerpt((string) $description, $excerpt) : $description,
    ]);
}

// photo
if (!empty((string) $entity->photo)) {
    $body .= elgg_format_element('div', ['class' => 'webmention-photo'], elgg_view('output/img', [
        'src' => (string) $entity->photo,
    ]));
}

// video
if (!empty((string) $entity->video)) {
    $body .= elgg_format_element('div', ['class' => 'webmention-video'], "<video width='100%' preload='metadata' controls><source src='{$entity->video}'></video>");
}

// audio
if (!empty((string) $entity->audio)) {
    $body .= elgg_format_element('div', ['class' => 'webmention-audio'], "<audio preload='none' controls><source src='{$entity->audio}'></audio>");
}

if (elgg_extract('full_view', $vars)) {
    if ($entity->hasCapability('commentable')) {
        $body .= elgg_view('object/elements/full/responses', $vars);
    }
}

$params = [
    'icon' => elgg_view_entity_icon($entity, 'small'),
    'time_href' => (string) $entity->source,
    'access' => false,
    'title' => false,
    'show_summary' => true,
    'content' => $body,
    'imprint' => (array) elgg_extract('imprint', $vars, []),
    'byline' => elgg_view('object/webmention/byline', $vars),
    'class' => elgg_extract_class($vars),
];

$params = $params + $vars;

echo elgg_view('object/elements/summary', $params);
