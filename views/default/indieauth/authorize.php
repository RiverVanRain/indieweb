<?php

if (!(bool) elgg_get_plugin_setting('enable_indieauth_login', 'indieweb')) {
	return;
}

$user = elgg_extract('entity', $vars, elgg_get_page_owner_entity());
if (!$user instanceof \ElggUser) {
	return;
}

$icon = '<i class="openwebicons-indieauth" style="font-size: 20px;"></i>';

$text_provider = elgg_format_element('span', ['class' => 'mlm mrl'], elgg_echo('indieauth:provider'));
	
$title = $icon . $text_provider;

if ((bool) $user->indieauth_login) {
	$body = elgg_format_element('div', ['class' => 'mtm mbm'], elgg_view('output/url', [
		'text' => elgg_echo('indieauth:account:deauthorise'),
		'href' => elgg_generate_action_url('indieauth/cancel', [
			'guid' => $user->guid,
		]),
		'class' => 'elgg-button elgg-button-cancel',
		'confirm' => true,
	]));
} else {
	$body = elgg_format_element('div', ['class' => 'mtm mbm'], elgg_view('output/url', [
		'text' => elgg_echo('indieauth:account:authorise'),
		'href' => elgg_generate_url('indieauth:login'),
		'class' => 'elgg-button elgg-button-action',
	]));
}

echo elgg_view_module('info', $title, $body);