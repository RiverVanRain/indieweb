<?php
/**
 * IndieWeb
 * @author Nikolai Shcherbin
 * @license GNU Affero General Public License version 3
 * @copyright (c) Nikolai Shcherbin 2022
 * @link https://wzm.me
**/

$options = [
	'type' => 'object',
	'subtype' => \Elgg\IndieWeb\Webmention\Entity\Syndication::SUBTYPE,
	'count' => true,
	'limit' => (int) max(get_input('limit', max(25, _elgg_services()->config->default_limit)), 0),
	'offset' => (int) max(get_input('offset', 0), 0),
];

$count = elgg_get_entities($options);

if (!empty($count)) {
	echo elgg_view('navigation/pagination', [
		'base_url' => elgg_normalize_url('admin/indieweb/webmention/syndications'),
		'count' => $count,
		'limit' => (int) max(get_input('limit', max(25, _elgg_services()->config->default_limit)), 0),
		'offset' => (int) max(get_input('offset', 0), 0),
	]);
	
	$rows = [];
	
	$options['count'] = false;
	$entities = elgg_get_entities($options);
	
	/* @var $entity ElggEntity */
	foreach ($entities as $entity) {
		$row = [];
		
		$row[] = elgg_format_element('td', ['width' => '90%'], elgg_view('object/syndication', [
			'entity' => $entity,
		]));
		
		$item = false;
		$item_url = false;

		if ($entity->source_id > 0) {
			$item = get_entity($entity->source_id);
			
			if ($item instanceof \ElggEntity) {
				$item_url = elgg_view('output/url', [
					'href' => $item->getURL(),
					'text' => elgg_echo('indieweb:webmention:syndication:view'),
					'title' => $item->getDisplayName(),
				]);
			}
		}
		
		$row[] = elgg_format_element('td', ['width' => '10%'], $entity->source_id . ' ' . $item_url);
		
		$rows[] = elgg_format_element('tr', [], implode('', $row));
	}
	
	$header_row = [
		elgg_format_element('th', ['width' => '90%'], elgg_echo('item:object:syndication')),
		elgg_format_element('th', ['width' => '10%'], elgg_echo('indieweb:syndication:source_id')),
	];
	$header = elgg_format_element('tr', [], implode('', $header_row));
	
	$table_content = elgg_format_element('thead', [], $header);
	$table_content .= elgg_format_element('tbody', [], implode('', $rows));
	
	echo elgg_format_element('table', ['class' => 'elgg-table'], $table_content);
} else {
	echo elgg_format_element('div', [], elgg_echo('indieweb:syndication:none'));
}


