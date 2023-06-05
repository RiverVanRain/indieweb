<?php

if (!(bool) elgg_get_plugin_setting('enable_indieauth_login', 'indieweb') || elgg_is_logged_in()) {
	return;
}

echo elgg_format_element('div', ['class' => 'mtm mbm'], elgg_view('output/url', [
	'text' => elgg_echo('indieweb:indieauth:authorize'),
	'href' => elgg_generate_url('indieauth:login'),
	'class' => 'elgg-button elgg-button-submit',
]));