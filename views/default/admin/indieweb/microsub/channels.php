<?php

$offset = (int) get_input('offset');

$options = [
	'type' => 'object',
	'subtype' => \Elgg\IndieWeb\Microsub\Entity\MicrosubChannel::SUBTYPE,
	'count' => true,
	'offset' => $offset,
	'limit' => elgg_get_config('default_limit'),
];

$count = elgg_get_entities($options);

if (empty($count)) {
	elgg_register_menu_item('title', [
		'name' => 'add:channel:notifications',
		'icon' => 'plus',
		'text' => elgg_echo('add:object:microsub_channel:notifications'),
		'href' => elgg_generate_url('add:object:microsub_channel:notifications'),
		'link_class' => [
			'elgg-button',
			'elgg-button-action',
			'elgg-lightbox',
		],
		'data-colorbox-opts' => json_encode([
			'width' => '1000px',
			'height' => '98%',
			'maxWidth' => '98%',
		]),
		'deps' => ['elgg/lightbox'],
	]);
	
	echo elgg_format_element('div', [], elgg_echo('add:object:microsub_channel:notifications:help'));
	return;
}

elgg_register_menu_item('title', [
	'name' => 'add:channel',
	'icon' => 'plus',
	'text' => elgg_echo('add:object:microsub_channel'),
	'href' => elgg_generate_url('add:object:microsub_channel'),
	'link_class' => [
		'elgg-button',
		'elgg-button-action',
		'elgg-lightbox',
	],
	'data-colorbox-opts' => json_encode([
		'width' => '1000px',
		'height' => '98%',
		'maxWidth' => '98%',
	]),
	'deps' => ['elgg/lightbox'],
]);

elgg_register_menu_item('title', [
	'name' => 'add:source',
	'icon' => 'plus',
	'text' => elgg_echo('add:object:microsub_source'),
	'href' => elgg_generate_url('add:object:microsub_source'),
	'link_class' => [
		'elgg-button',
		'elgg-button-action',
		'elgg-lightbox',
	],
	'data-colorbox-opts' => json_encode([
		'width' => '1000px',
		'height' => '98%',
		'maxWidth' => '98%',
	]),
	'deps' => ['elgg/lightbox'],
]);

echo elgg_format_element('div', ['class' => 'mbm'], elgg_echo('indieweb:microsub:channels:title'));

$offset = (int) get_input('offset');

$options = [
	'type' => 'object',
	'subtype' => \Elgg\IndieWeb\Microsub\Entity\MicrosubChannel::SUBTYPE,
	'count' => true,
	'offset' => $offset,
	'limit' => elgg_get_config('default_limit'),
];

$count = elgg_get_entities($options);

if (!empty($count)) {
	echo elgg_view('navigation/pagination', [
		'base_url' => elgg_normalize_url('admin/indieweb/microsub/channels'),
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
		$row[] = elgg_format_element('td', ['width' => '60%'], elgg_view_entity($entity, [
			'full_view' => false,
			'icon' => false,
			'byline' => false,
			'access' => false,
		]));
		// status
		$status = ((bool) $entity->status) ? elgg_echo('indieweb:microsub:microsub_channel:enable') : elgg_echo('indieweb:microsub:microsub_channel:disable');
		$row[] = elgg_format_element('td', ['width' => '10%'], $status);
		// items
		$row[] = elgg_format_element('td', ['width' => '10%'], $entity->getItemCount());
		// sources
		$sources = $entity->getSources(true);
		
		$list = false;
		if ($sources > 0) {
			$list = elgg_format_element('span', ['class' => 'mlm'], elgg_view('output/url', [
				'href' => elgg_http_add_url_query_elements(elgg_normalize_url('admin/indieweb/microsub/sources'), [
					'guid' => $entity->guid,
				]),
				'text' => elgg_echo('indieweb:microsub:microsub_channel:sources:view'),
			]));
		}
		$row[] = elgg_format_element('td', ['width' => '20%'], $sources . $list);
		
		$rows[] = elgg_format_element('tr', [], implode('', $row));
	}
	
	$header_row = [
		elgg_format_element('th', ['width' => '60%'], elgg_echo('item:object:microsub_channel')),
		elgg_format_element('th', ['width' => '10%'], elgg_echo('indieweb:microsub:microsub_channel:status')),
		elgg_format_element('th', ['width' => '10%'], elgg_echo('indieweb:microsub:microsub_channel:items')),
		elgg_format_element('th', ['width' => '20%'], elgg_echo('indieweb:microsub:microsub_channel:sources')),
	];
	$header = elgg_format_element('tr', [], implode('', $header_row));
	
	$table_content = elgg_format_element('thead', [], $header);
	$table_content .= elgg_format_element('tbody', [], implode('', $rows));
	
	echo elgg_format_element('table', ['class' => 'elgg-table'], $table_content);
} else {
	echo elgg_format_element('div', [], elgg_echo('indieweb:microsub:microsub_channel:none'));
}

