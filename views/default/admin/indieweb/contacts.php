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
	'subtype' => \Elgg\IndieWeb\Contacts\Entity\Contact::SUBTYPE,
	'count' => true,
	'offset' => (int) max(get_input('offset', 0), 0),
	'limit' => (int) max(get_input('limit', max(25, _elgg_services()->config->default_limit)), 0),
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


