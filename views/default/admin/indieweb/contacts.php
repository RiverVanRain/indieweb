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
	'subtype' => \Elgg\IndieWeb\Contacts\Entity\Contact::SUBTYPE,
	'count' => true,
	'offset' => $offset,
	'limit' => elgg_get_config('default_limit'),
	'pagination' => true,
	'full_view' => false,
];

$count = elgg_get_entities($options);

if (!empty($count)) {
	unset($options['count']);
	
	echo elgg_list_entities($options);
} else {
	echo elgg_format_element('div', [], elgg_echo('indieweb:contacts:none'));
}


