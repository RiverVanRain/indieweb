<?php

/**
 * Outputs webmentions
 *
 */

$entity = elgg_extract('entity', $vars);

if (!$entity instanceof \ElggObject) {
    return;
}

if (!(bool) elgg_get_plugin_setting("can_webmention:object:$entity->subtype", 'indieweb')) {
    return;
}

$options = [
    'type' => 'object',
    'subtype' => \Elgg\IndieWeb\Webmention\Entity\Webmention::SUBTYPE,
    'full_view' => true,
    'limit' => elgg_get_config('default_limit'),
    'offset' => (int) get_input('offset'),
    'distinct' => false,
    'list_class' => 'webmention-list',
    'pagination' => true,
    'preload_owners' => true,
    'metadata_name_value_pairs' => [
        [
            'name' => 'target_guid',
            'value' => $entity->guid,
        ],
        [
            'name' => 'published',
            'value' => 1,
        ],
        [
            'name' => 'status',
            'value' => 1,
        ],
    ],
];

$webmentions = elgg_get_entities($options);

$count_options = $options;
unset($count_options['offset']);
$options['count'] = elgg_count_entities($count_options);

$content = elgg_view_entity_list($webmentions, $options);

if (empty($content)) {
    return;
}

echo elgg_format_element('div', ['id' => 'webmentions']);

echo elgg_view_module('webmentions', elgg_echo('collection:object:webmention'), $content);
