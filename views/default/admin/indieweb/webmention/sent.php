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
	'subtype' => \Elgg\IndieWeb\Webmention\Entity\Webmention::SUBTYPE,
	'count' => true,
	'limit' => (int) max(get_input('limit', max(25, _elgg_services()->config->default_limit)), 0),
	'offset' => (int) max(get_input('offset', 0), 0),
	'metadata_name_value_pairs' => [
		[
			'name' => 'property',
			'value' => 'send',
		],
	],
];

$count = elgg_get_entities($options);

if (!empty($count)) {
	echo elgg_view('navigation/pagination', [
		'base_url' => elgg_normalize_url('admin/indieweb/webmention/sent'),
		'count' => (int) $count,
		'limit' => (int) max(get_input('limit', max(25, _elgg_services()->config->default_limit)), 0),
		'offset' => (int) max(get_input('offset', 0), 0),
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
		]));
		// source
		$row[] = elgg_format_element('td', ['width' => '20%'], elgg_view('output/url', [
			'text' => (string) $entity->source,
			'href' => (string) $entity->source,
		]));
		// target
		$row[] = elgg_format_element('td', ['width' => '20%'], elgg_view('output/url', [
			'text' => (string) $entity->target,
			'href' => (string) $entity->target,
		]));
		// published
		$published = ((bool) $entity->published) ? elgg_echo('option:yes') : elgg_echo('option:no');
		$row[] = elgg_format_element('td', ['width' => '10%'], $published);
		
		$rows[] = elgg_format_element('tr', [], implode('', $row));
	}
	
	$header_row = [
		elgg_format_element('th', ['width' => '50%'], elgg_echo('item:object:webmention')),
		elgg_format_element('th', ['width' => '20%'], elgg_echo('indieweb:webmention:source')),
		elgg_format_element('th', ['width' => '20%'], elgg_echo('indieweb:webmention:target')),
		elgg_format_element('th', ['width' => '10%'], elgg_echo('indieweb:webmention:published')),
	];
	$header = elgg_format_element('tr', [], implode('', $header_row));
	
	$table_content = elgg_format_element('thead', [], $header);
	$table_content .= elgg_format_element('tbody', [], implode('', $rows));
	
	echo elgg_format_element('table', ['class' => 'elgg-table'], $table_content);
} else {
	echo elgg_format_element('div', [], elgg_echo('indieweb:webmention:none'));
}


