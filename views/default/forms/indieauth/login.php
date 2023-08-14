<?php

if (!(bool) elgg_get_plugin_setting('enable_indieauth_login', 'indieweb')) {
	throw new \Elgg\Exceptions\Http\PageNotFoundException();
}

if (elgg_is_logged_in() && (bool) elgg_get_logged_in_user_entity()->indieauth_login) {
	$forward_url = elgg_generate_url('settings:account', [
		'username' => elgg_get_logged_in_user_entity()->username,
	]);
	$exception = new \Elgg\Exceptions\HttpException();
	$exception->setRedirectUrl($forward_url);
	throw $exception;
}

$fields = [
	[
		'#type' => 'url',
		'name' => 'domain',
		'#label' => elgg_echo('indieweb:indieauth:login:label'),
		'#help' => elgg_echo('indieweb:indieauth:login:help'),
		'#placeholder' => 'https://example.com',
		'required' => true,
	],
	[
		'#type' => 'hidden',
		'name' => 'me',
		'value' => elgg_extract('me', $vars, get_input('me')),
	],
	[
		'#type' => 'hidden',
		'name' => 'client_id',
		'value' => elgg_extract('client_id', $vars, get_input('client_id')),
	],
	[
		'#type' => 'hidden',
		'name' => 'state',
		'value' => elgg_extract('state', $vars, get_input('state')),
	],
	[
		'#type' => 'hidden',
		'name' => 'code',
		'value' => elgg_extract('code', $vars, get_input('code')),
	],
];

foreach ($fields as $field) {
	echo elgg_view_field($field);
}

if (!elgg_is_logged_in()) {
	echo elgg_view_field([
		'#type' => 'email',
		'name' => 'email',
		'#label' => elgg_echo('indieweb:indieauth:login:email'),
		'#help' => elgg_echo('indieweb:indieauth:login:email:help'),
		'required' => true,
	]);
}

//Submit
$submit = elgg_view_field([
	'#type' => 'submit',
	'value' => elgg_echo('indieweb:indieauth:authorize:submit'),
]);

elgg_set_form_footer($submit);


