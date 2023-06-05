<?php

elgg_register_menu_item('title', [
	'name' => 'add:token',
	'icon' => 'plus',
	'text' => elgg_echo('add:object:indieauth_token'),
	'href' => elgg_normalize_url('ajax/form/indieauth/token/save'),
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

$offset = (int) get_input('offset');

$options = [
	'type' => 'object',
	'subtype' => \Elgg\IndieWeb\IndieAuth\Entity\IndieAuthToken::SUBTYPE,
	'count' => true,
	'offset' => $offset,
	'limit' => elgg_get_config('default_limit'),
];

$count = elgg_get_entities($options);

if (!empty($count)) {
	$rows = [];
	
	$options['count'] = false;
	$entities = elgg_get_entities($options);
	
	/* @var $entity ElggEntity */
	foreach ($entities as $entity) {
		$row = [];
		
		// token
		$row[] = elgg_format_element('td', ['width' => '50%'], $entity->getAccessToken());
		// status
		$status = ((bool) $entity->getStatus()) ? elgg_format_element('span', ['class' => 'text-success'], elgg_echo('indieweb:indieauth:token:status:active')) : elgg_format_element('span', ['class' => 'text-danger'], elgg_echo('indieweb:indieauth:token:status:revoked'));
		$row[] = elgg_format_element('td', ['width' => '10%'], $status);
		// client
		$row[] = elgg_format_element('td', ['width' => '20%'], $entity->getClientId());
		// last access
		$changed = $entity->getChanged() ? elgg_get_friendly_time($entity->getChanged()) : '/';
		$row[] = elgg_format_element('td', ['width' => '15%'], $changed);
		// menu
		$row[] = elgg_format_element('td', ['width' => '5%'], elgg_view_menu('entity', [
			'entity' => $entity,
			'prepare_dropdown' => true,
		]));
		
		$rows[] = elgg_format_element('tr', [], implode('', $row));
	}
	
	$header_row = [
		elgg_format_element('th', ['width' => '50%'], elgg_echo('indieweb:indieauth:token')),
		elgg_format_element('th', ['width' => '10%'], elgg_echo('indieweb:indieauth:token:status')),
		elgg_format_element('th', ['width' => '20%'], elgg_echo('indieweb:indieauth:token:client')),
		elgg_format_element('th', ['width' => '15%'], elgg_echo('indieweb:indieauth:token:access')),
		elgg_format_element('th', ['width' => '5%'], elgg_echo('indieweb:indieauth:token:actions')),
	];
	$header = elgg_format_element('tr', [], implode('', $header_row));
	
	$table_content = elgg_format_element('thead', [], $header);
	$table_content .= elgg_format_element('tbody', [], implode('', $rows));
	
	echo elgg_format_element('table', ['class' => 'elgg-table'], $table_content);
	
	echo elgg_view('navigation/pagination', [
		'base_url' => '/admin/indieweb/indieauth/tokens',
		'offset' => $offset,
		'count' => $count,
		'limit' => elgg_get_config('default_limit'),
	]);
} else {
	echo elgg_format_element('div', [], elgg_echo('indieweb:indieauth:token:no_results'));
}
