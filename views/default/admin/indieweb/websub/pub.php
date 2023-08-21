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
	'subtype' => \Elgg\IndieWeb\WebSub\Entity\WebSubPub::SUBTYPE,
	'count' => true,
	'offset' => $offset,
	'limit' => elgg_get_config('default_limit'),
];

$count = elgg_get_entities($options);

if (!empty($count)) {
	echo elgg_view('navigation/pagination', [
		'base_url' => elgg_normalize_url('admin/indieweb/websub/pub'),
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

		$row[] = elgg_format_element('td', ['width' => '80%'], elgg_view('object/websubpub', [
			'entity' => $entity,
		]));
		
		$item = false;
		$item_url = false;

		if ($entity->entity_id > 0) {
			$item = get_entity($entity->entity_id);
			if ($item instanceof \ElggEntity) {
				$item_url = elgg_view('output/url', [
					'href' => $item->getURL(),
					'text' => elgg_echo('indieweb:websub:websubpub:view'),
					'title' => $item->getDisplayName(),
				]);
			}
		}
		
		$row[] = elgg_format_element('td', ['width' => '10%'], $entity->entity_id . ' ' . $item_url);

		// published
		$published = ((bool) $entity->published) ? elgg_echo('option:yes') : elgg_echo('option:no');
		$row[] = elgg_format_element('td', ['width' => '10%'], $published);
		
		$rows[] = elgg_format_element('tr', [], implode('', $row));
	}
	
	$header_row = [
		elgg_format_element('th', ['width' => '80%'], elgg_echo('item:object:websubpub')),
		elgg_format_element('th', ['width' => '10%'], elgg_echo('indieweb:websub:entity_id')),
		elgg_format_element('th', ['width' => '10%'], elgg_echo('indieweb:websub:published')),
	];
	$header = elgg_format_element('tr', [], implode('', $header_row));
	
	$table_content = elgg_format_element('thead', [], $header);
	$table_content .= elgg_format_element('tbody', [], implode('', $rows));
	
	echo elgg_format_element('table', ['class' => 'elgg-table'], $table_content);
} else {
	echo elgg_format_element('div', [], elgg_echo('indieweb:websub:none'));
}


