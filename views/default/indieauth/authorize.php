<?php

if (!(bool) elgg_get_plugin_setting('enable_indieauth_login', 'indieweb')) {
	return;
}

$user = elgg_extract('entity', $vars, elgg_get_page_owner_entity());
if (!$user instanceof \ElggUser) {
	return;
}

if ((bool) $user->indieauth_login) {
	echo elgg_format_element('div', ['class' => 'mtm mbm'], elgg_view('output/url', [
		'text' => elgg_echo('indieweb:indieauth:deauthorize'),
		'href' => elgg_generate_action_url('indieauth/cancel', [
			'guid' => $user->guid,
		]),
		'class' => 'elgg-button elgg-button-action',
		'confirm' => true,
	]));
} else {
	echo elgg_format_element('div', ['class' => 'mtm mbm'], elgg_view('output/url', [
		'text' => elgg_echo('indieweb:indieauth:authorize'),
		'href' => elgg_generate_url('indieauth:login'),
		'class' => 'elgg-button elgg-button-submit',
	]));
}