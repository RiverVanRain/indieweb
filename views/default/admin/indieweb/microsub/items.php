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
			'guid' => $entity->channel_id,
		]),
	])
]));

$offset = (int) get_input('offset');

$options = [
	'type' => 'object',
	'subtype' => \Elgg\IndieWeb\Microsub\Entity\MicrosubItem::SUBTYPE,
	'metadata_name_value_pairs' => [
		[
			'name' => 'source_id',
			'value' => $entity->guid,
		],
	],
	'count' => true,
	'offset' => $offset,
	'limit' => elgg_get_config('default_limit'),
];

$count = elgg_get_entities($options);

if (!empty($count)) {
	echo elgg_view('navigation/pagination', [
		'base_url' => elgg_http_add_url_query_elements(elgg_normalize_url('admin/indieweb/microsub/items'), [
			'guid' => $entity->guid,
		]),
		'offset' => $offset,
		'count' => $count,
		'limit' => elgg_get_config('default_limit'),
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
			'title' => $entity->post_type,
			'icon' => false,
			'byline' => false,
			'access' => false,
			'content' => $entity->id,
		]));
		
		//data
		$row[] = elgg_format_element('td', ['width' => '15%'], elgg_view('output/url', [
			'href' => elgg_http_add_url_query_elements('ajax/view/resources/microsub/source/item', [
				'guid' => $entity->guid,
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

