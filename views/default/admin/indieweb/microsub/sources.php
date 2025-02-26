<?php

$guid = (int) get_input('guid');

$entity = get_entity($guid);

if (!$entity instanceof \Elgg\IndieWeb\Microsub\Entity\MicrosubChannel) {
	throw new \Elgg\Exceptions\Http\EntityNotFoundException();
}

elgg_register_menu_item('title', [
	'name' => 'add:source',
	'icon' => 'plus',
	'text' => elgg_echo('add:object:microsub_source'),
	'href' => elgg_generate_url('add:object:microsub_source', [
		'container_guid' => (int) $entity->guid,
	]),
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

echo elgg_format_element('div', ['class' => 'mbm'], elgg_echo('indieweb:microsub:microsub_channel:sources:list', [
	elgg_view('output/url', [
		'text' => (string) $entity->getDisplayName(),
		'href' => elgg_normalize_url('admin/indieweb/microsub/channels'),
	])
]));

$options = [
	'type' => 'object',
	'subtype' => \Elgg\IndieWeb\Microsub\Entity\MicrosubSource::SUBTYPE,
	'container_guid' => (int) $entity->guid,
	'count' => true,
	'offset' => (int) max(get_input('offset', 0), 0),
	'limit' => (int) max(get_input('limit', max(25, _elgg_services()->config->default_limit)), 0),
];

$count = elgg_get_entities($options);

if (!empty($count)) {
	echo elgg_view('navigation/pagination', [
		'base_url' => elgg_http_add_url_query_elements(elgg_normalize_url('admin/indieweb/microsub/sources'), [
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
		$row[] = elgg_format_element('td', ['width' => '50%'], elgg_view_entity($entity, [
			'full_view' => false,
			'icon' => false,
			'byline' => false,
			'access' => false,
			'content' => (string) $entity->getURL(),
		]));
		// status
		$status = ((bool) $entity->status) ? elgg_echo('indieweb:microsub:microsub_source:enable') : elgg_echo('indieweb:microsub:microsub_source:disable');
		$row[] = elgg_format_element('td', ['width' => '10%'], $status);
		// total items
		$view_items = false;
		
		if ($entity->getItemCount() > 0) {
			$view_items = elgg_view('output/url', [
				'href' => elgg_http_add_url_query_elements(elgg_normalize_url('admin/indieweb/microsub/items'), [
					'guid' => (int) $entity->guid,
				]),
				'text' => elgg_echo('indieweb:microsub:microsub_source:items:view'),
			]);
		}
		
		$row[] = elgg_format_element('td', ['width' => '10%'], $entity->getItemCount() . ' ' . $view_items);
		// next update
		$next = $entity->getNextFetch();
		$fetch_next = '/';
		if (($entity->getStatus() && $entity->getContainerEntity()->getStatus()) || $entity->usesWebSub()) {
			if ($next < time()) {
				$fetch_next = elgg_echo('indieweb:microsub:microsub_source:next_update:imminently');
				if ($entity->usesWebSub()) {
					$fetch_next = elgg_echo('indieweb:microsub:microsub_source:fetch_interval:websub:ended');
				}
			} else {
				$now = new DateTime();
				$date = new DateTime("@{$next}");
				$diff = $date->diff($now);

				$time = $diff->days . ' days ' . $diff->h .'h ' . $diff->i . 'm ' . $diff->s .'s';
				
				$fetch_next = elgg_echo('indieweb:microsub:microsub_source:next_update:left', [$time]);
				if ($entity->usesWebSub()) {
					$fetch_next = elgg_echo('indieweb:microsub:microsub_source:websub:left', [$time]);
				}
			}
		}
		
		$row[] = elgg_format_element('td', ['width' => '10%'], $fetch_next);
		// feed/keep
		$row[] = elgg_format_element('td', ['width' => '10%'], $entity->getItemsInFeed() . '/' . $entity->getKeepItemsInFeed());
		// last update
		$changed = $entity->getChanged() ? elgg_get_friendly_time($entity->getChanged()) : '/';
		$row[] = elgg_format_element('td', ['width' => '10%'], $changed);
		
		$rows[] = elgg_format_element('tr', [], implode('', $row));
	}
	
	$header_row = [
		elgg_format_element('th', ['width' => '50%'], elgg_echo('item:object:microsub_source')),
		elgg_format_element('th', ['width' => '10%'], elgg_echo('indieweb:microsub:microsub_source:status')),
		elgg_format_element('th', ['width' => '10%'], elgg_echo('indieweb:microsub:microsub_source:items')),
		elgg_format_element('th', ['width' => '10%'], elgg_echo('indieweb:microsub:microsub_source:next_update')),
		elgg_format_element('th', ['width' => '10%'], elgg_echo('indieweb:microsub:microsub_source:feed_keep')),
		elgg_format_element('th', ['width' => '10%'], elgg_echo('indieweb:microsub:microsub_source:last_update')),
	];
	$header = elgg_format_element('tr', [], implode('', $header_row));
	
	$table_content = elgg_format_element('thead', [], $header);
	$table_content .= elgg_format_element('tbody', [], implode('', $rows));
	
	echo elgg_format_element('table', ['class' => 'elgg-table'], $table_content);
} else {
	echo elgg_format_element('div', [], elgg_echo('indieweb:microsub:microsub_source:none'));
}

