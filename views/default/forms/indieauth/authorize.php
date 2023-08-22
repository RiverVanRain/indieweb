<?php

if (!elgg_is_admin_logged_in()) {
	throw new \Elgg\Exceptions\Http\EntityPermissionsException();
}

$client_id = elgg_extract('client_id', $vars, get_input('client_id'));
$redirect_uri = elgg_extract('redirect_uri', $vars, get_input('redirect_uri'));

echo elgg_view_field([
	'#html' => elgg_format_element('div', ['class' => 'mbm'], elgg_echo('indieweb:indieauth:authorize:title', [$client_id, elgg_get_logged_in_user_entity()->getDisplayName()])),
]);

$fields = [
	[
		'#html' => elgg_format_element('div', ['class' => 'mtm mbm'], elgg_echo('indieweb:indieauth:authorize:redirect', [$redirect_uri])),
	],
	[
		'#type' => 'hidden',
		'name' => 'guid',
		'value' => elgg_get_logged_in_user_guid(),
	],
	[
		'#type' => 'hidden',
		'name' => 'me',
		'value' => elgg_extract('me', $vars, get_input('me')),
	],
	[
		'#type' => 'hidden',
		'name' => 'client_id',
		'value' => $client_id,
	],
	[
		'#type' => 'hidden',
		'name' => 'code_challenge',
		'value' => !empty(elgg_extract('code_challenge', $vars)) ? elgg_extract('code_challenge', $vars, get_input('code_challenge')) : '',
	],
	[
		'#type' => 'hidden',
		'name' => 'code_challenge_method',
		'value' => !empty(elgg_extract('code_challenge_method', $vars)) ? elgg_extract('code_challenge_method', $vars, 'plain') : '',
	],
	[
		'#type' => 'hidden',
		'name' => 'redirect_uri',
		'value' => $redirect_uri,
	],
	[
		'#type' => 'hidden',
		'name' => 'state',
		'value' => elgg_extract('state', $vars, get_input('state')),
	],
	[
		'#type' => 'hidden',
		'name' => 'scope',
		'value' => elgg_extract('scope', $vars, get_input('scope')),
	],
];

foreach ($fields as $field) {
	echo elgg_view_field($field);
}

//Submit
$submit_button = elgg_format_element('div', [], elgg_view_field([
	'#type' => 'submit',
	'text' => elgg_echo('indieweb:indieauth:authorize:submit'),
	'class' => 'elgg-button elgg-button-submit',
]));

//Cancel
$cancel_button = elgg_format_element('div', [], elgg_view('output/url', [
	'text' => elgg_echo('cancel'),
	'href' => elgg_generate_action_url('indieauth/deauthorize', [
		'redirect_uri' => $redirect_uri,
	]),
	'confirm' => true,
	'class' => 'elgg-button elgg-button-cancel',
]));

elgg_set_form_footer($submit_button . $cancel_button);


