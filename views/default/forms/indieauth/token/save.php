<?php

$guid = (int) elgg_extract('guid', $vars, 0);
$token = false;

if ($guid !== 0) {
	$token = get_entity($guid);
	if (!$token instanceof \Elgg\IndieWeb\IndieAuth\Entity\IndieAuthToken) {
		throw new \Elgg\Exceptions\Http\EntityNotFoundException();
	}
}

$fields = [
	[
		'#type' => 'url',
		'name' => 'client_id',
		'#label' => elgg_echo('indieauth:token:client_id'),
		'#placeholder' => 'https://example.com',
		'value' => $token ? $token->getClientId() : '',
		'required' => true,
	],
	[
		'#type' => 'text',
		'name' => 'scope',
		'#label' => elgg_echo('indieauth:token:scope'),
		'#help' => elgg_echo('indieauth:token:scope:help'),
		'value' => $token ? $token->getScopesAsString() : '',
	],
	[
		'#type' => 'hidden',
		'name' => 'guid',
		'value' => $guid,
	],
];

foreach ($fields as $field) {
	echo elgg_view_field($field);
}

$footer = elgg_view('input/submit', [
	'value' => elgg_echo('save'),
	'name' => 'save',
]);

elgg_set_form_footer($footer);
