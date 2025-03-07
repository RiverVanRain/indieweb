<?php

$guid = (int) get_input('guid');

$entity = get_entity($guid);

if (!$entity instanceof \Elgg\IndieWeb\Microsub\Entity\MicrosubSource) {
    throw new \Elgg\Exceptions\Http\EntityNotFoundException();
}

echo elgg_format_element('div', ['class' => 'mbm'], elgg_echo('indieweb:microsub:microsub_source:items:list', [
    elgg_view('output/url', [
        'text' => $entity->getDisplayName(),
        'href' => elgg_http_add_url_query_elements(elgg_normalize_url('admin/indieweb/microsub/sources'), [
            'guid' => (int) $entity->channel_id,
        ]),
    ])
]));

$options = [
    'type' => 'object',
    'subtype' => \Elgg\IndieWeb\Microsub\Entity\MicrosubItem::SUBTYPE,
    'metadata_name_value_pairs' => [
        [
            'name' => 'source_id',
            'value' => (int) $entity->guid,
        ],
    ],
    'count' => true,
    'offset' => (int) max(get_input('offset', 0), 0),
    'limit' => (int) max(get_input('limit', max(25, _elgg_services()->config->default_limit)), 0),
];

$count = elgg_get_entities($options);

if (!empty($count)) {
    echo elgg_view('navigation/pagination', [
        'base_url' => elgg_http_add_url_query_elements(elgg_normalize_url('admin/indieweb/microsub/items'), [
            'guid' => (int) $entity->guid,
        ]),
        'offset' => (int) max(get_input('offset', 0), 0),
        'count' => (int) $count,
        'limit' => (int) max(get_input('limit', max(25, _elgg_services()->config->default_limit)), 0),
    ]);

    $rows = [];

    $options['count'] = false;
    $entities = elgg_get_entities($options);

    /* @var $entity ElggEntity */
    foreach ($entities as $entity) {
        $row = [];

        // name
        $row[] = elgg_format_element('td', ['width' => '70%'], elgg_view_entity($entity, [
            'full_view' => false,
            'title' => (string) $entity->post_type,
            'icon' => false,
            'byline' => false,
            'access' => false,
            'content' => (string) $entity->id,
        ]));

        //data
        $row[] = elgg_format_element('td', ['width' => '15%'], elgg_view('output/url', [
            'href' => elgg_http_add_url_query_elements('ajax/view/resources/microsub/source/item', [
                'guid' => (int) $entity->guid,
            ]),
            'text' => elgg_echo('indieweb:microsub:microsub_item:data_view'),
            'class' => 'elgg-lightbox',
            'data-colorbox-opts' => json_encode([
                'width' => '1000px',
                'height' => '98%',
                'maxWidth' => '98%',
            ]),
            'deps' => ['elgg/lightbox'],
        ]));

        // is read
        $is_read = ((bool) $entity->is_read) ? elgg_echo('indieweb:microsub:microsub_item:read') : elgg_echo('indieweb:microsub:microsub_item:no_read');
        $row[] = elgg_format_element('td', ['width' => '15%'], $is_read);

        $rows[] = elgg_format_element('tr', [], implode('', $row));
    }

    $header_row = [
        elgg_format_element('th', ['width' => '70%'], elgg_echo('item:object:microsub_item')),
        elgg_format_element('th', ['width' => '15%'], elgg_echo('indieweb:microsub:microsub_item:data')),
        elgg_format_element('th', ['width' => '15%'], elgg_echo('indieweb:microsub:microsub_item:is_read')),
    ];
    $header = elgg_format_element('tr', [], implode('', $header_row));

    $table_content = elgg_format_element('thead', [], $header);
    $table_content .= elgg_format_element('tbody', [], implode('', $rows));

    echo elgg_format_element('table', ['class' => 'elgg-table'], $table_content);
} else {
    echo elgg_format_element('div', [], elgg_echo('indieweb:microsub:microsub_item:none'));
}
