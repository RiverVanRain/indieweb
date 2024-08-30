<?php

elgg_import_esm('forms/admin/indieweb/settings');

$entity = elgg_get_plugin_from_id('indieweb');

//Basic
echo elgg_view_field([
	'#type' => 'fieldset',
	'legend' => elgg_echo('settings:indieweb:indieauth:api'),
	'fields' => [
		[
			'#type' => 'checkbox',
			'#label' => elgg_echo('settings:indieweb:indieauth:login'),
			'#help' => elgg_echo('settings:indieweb:indieauth:login:help'),
			'name' => 'params[enable_indieauth_login]',
			'value' => 1,
			'default' => 0,
			'checked' => (bool) $entity->enable_indieauth_login,
			'switch' => true,
		],
		[
			'#type' => 'checkbox',
			'#label' => elgg_echo('settings:indieweb:indieauth:endpoint'),
			'#help' => elgg_echo('settings:indieweb:indieauth:endpoint:help'),
			'name' => 'params[enable_indieauth_endpoint]',
			'value' => 1,
			'default' => 0,
			'checked' => (bool) $entity->enable_indieauth_endpoint,
			'switch' => true,
			'id' => 'enable_indieauth_endpoint',
		],
	],
]);

//Keys
echo elgg_view_field([
	'#type' => 'fieldset',
	'legend' => elgg_echo('settings:indieweb:indieauth:keys'),
	'class' => (bool) $entity->enable_indieauth_endpoint ? '' : 'hidden',
	'id' => 'settings-indieauth-keys',
	'fields' => [
		[
			'#type' => 'text',
			'#label' => elgg_echo('settings:indieweb:indieauth:keys:public_key'),
			'#help' => elgg_echo('settings:indieweb:indieauth:keys:public_key:help'),
			'name' => 'params[indieauth_public_key]',
			'value' => $entity->indieauth_public_key ?: '',
		],
		[
			'#type' => 'text',
			'#label' => elgg_echo('settings:indieweb:indieauth:keys:private_key'),
			'#help' => elgg_echo('settings:indieweb:indieauth:keys:private_key:help'),
			'name' => 'params[indieauth_private_key]',
			'value' => $entity->indieauth_private_key ?: '',
		],
		[
			'#type' => 'checkbox',
			'#label' => elgg_echo('settings:indieweb:indieauth:keys:generate_keys'),
			'name' => 'params[indieauth_generate_keys]',
			'value' => 1,
			'default' => 0,
			'checked' => (bool) $entity->indieauth_generate_keys,
			'switch' => true,
		],
		[
			'#html' => elgg_format_element('div', ['class' => 'elgg-text-help'], elgg_echo('settings:indieweb:indieauth:keys:help')),
		],
	],
]);

//External endpoint
echo elgg_view_field([
	'#type' => 'fieldset',
	'legend' => elgg_echo('settings:indieweb:indieauth:external'),
	'class' => (bool) $entity->enable_indieauth_endpoint ? 'hidden' : '',
	'id' => 'settings-indieauth-external',
	'fields' => [
		[
			'#type' => 'url',
			'#label' => elgg_echo('settings:indieweb:indieauth:external:auth'),
			'name' => 'params[indieauth_external_auth]',
			'value' => $entity->indieauth_external_auth ?: 'https://indieauth.com/auth',
		],
		[
			'#type' => 'url',
			'#label' => elgg_echo('settings:indieweb:indieauth:external:endpoint'),
			'name' => 'params[indieauth_external_endpoint]',
			'value' => $entity->indieauth_external_endpoint ?: 'https://tokens.indieauth.com/token',
		],
	],
]);

echo elgg_view_field([
	'#html' => elgg_format_element('div', ['class' => 'mtm mbm elgg-text-help'], elgg_echo('settings:indieweb:indieauth:notes')),
]);

echo elgg_view_field([
	'#type' => 'hidden',
	'name' => 'plugin_id',
	'value' => 'indieweb',
]);

$footer = elgg_view_field([
	'#type' => 'submit',
	'text' => elgg_echo('save'),
]);

elgg_set_form_footer($footer);
