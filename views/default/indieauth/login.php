<?php

if (!(bool) elgg_get_plugin_setting('enable_indieauth_login', 'indieweb') || elgg_is_logged_in()) {
	return;
}

$footer = elgg_get_form_footer();

elgg_set_form_footer($footer . elgg_format_element('div', ['class' => 'indieauth-login'], elgg_view('output/url', [
	'text' => elgg_echo('indieweb:indieauth:authorize'),
	'href' => elgg_generate_url('indieauth:login'),
	'class' => 'elgg-button elgg-button-action',
])));