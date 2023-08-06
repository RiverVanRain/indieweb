<?php
/**
 * IndieWeb
 * @author Nikolai Shcherbin
 * @license GNU Affero General Public License version 3
 * @copyright (c) Nikolai Shcherbin 2022
 * @link https://wzm.me
**/

$offset = (int) get_input('offset');

$options = [
	'type' => 'object',
	'subtype' => \Elgg\IndieWeb\Webmention\Entity\Syndication::SUBTYPE,
	'count' => true,
	'offset' => $offset,
	'limit' => elgg_get_config('default_limit'),
];

$count = elgg_get_entities($options);

if (!empty($count)) {
	echo elgg_view('navigation/pagination', [
		'base_url' => '/admin/indieweb/webmention/syndications',
		'offset' => $offset,
		'count' => $count,
		'limit' => elgg_get_config('default_limit'),
	]);
	
	$rows = [];
	
	$options['count'] = false;
	$entities = elgg_get_entities($options);
	$item = false;
	
	/* @var $entity ElggEntity */
	foreach ($entities as $entity) {
		$item = get_entity($entity->source_id);
		$row = [];
		
		$row[] = elgg_format_element('td', ['width' => '30%'], elgg_view('object/syndication', [
			'entity' => $entity,
		]));
		$row[] = elgg_format_element('td', ['width' => '50%'], $item->getURL());
		$row[] = elgg_format_element('td', ['width' => '20%'], $entity->source_id . elgg_view('output/url', [
			'href' => $item->getURL(),
			'text' => elgg_echo('indieweb:webmention:syndication:view'),
			'title' => $item->getDisplayName(),
			'class' => 'mls',
		]));
		
		$rows[] = elgg_format_element('tr', [], implode('', $row));
	}
	
	$header_row = [
		elgg_format_element('th', ['width' => '30%'], elgg_echo('item:object:syndication')),
		elgg_format_element('th', ['width' => '50%'], elgg_echo('indieweb:syndication:url')),
		elgg_format_element('th', ['width' => '20%'], elgg_echo('indieweb:syndication:source_id')),
	];
	$header = elgg_format_element('tr', [], implode('', $header_row));
	
	$table_content = elgg_format_element('thead', [], $header);
	$table_content .= elgg_format_element('tbody', [], implode('', $rows));
	
	echo elgg_format_element('table', ['class' => 'elgg-table'], $table_content);
} else {
	echo elgg_format_element('div', [], elgg_echo('indieweb:syndication:none'));
}


