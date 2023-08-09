<?php

$entity = elgg_get_plugin_from_id('indieweb');

// Posts
$posts = ['article', 'note', 'like', 'reply', 'repost', 'bookmark', 'event', 'rsvp', 'issue', 'checkin'];

foreach ($posts as $post) {
	echo elgg_format_element('div', [], elgg_view('output/url', [
		'href' => '#' . $post,
		'text' => elgg_echo("indieweb:micropub:view:$post"),
	]));
}

$types = [];
$objects = (array) elgg_extract('object', elgg_entity_types_with_capability('searchable'), []);

foreach ($objects as $subtype) {
	if (in_array($subtype, ['river_object', 'messages', 'newsletter', 'static'])) {
		continue;
	}
	
	$types[$subtype] = elgg_echo("item:object:$subtype");
}

foreach ($posts as $post) {
	echo elgg_view_field([
		'#html' => elgg_view('output/url', [
			'id' => $post,
			'href' => false,
			'text' => false,
		]),
	]);
	
	$reply_create_comment = [
		'#html' => ''
	];
	
	if ($post === 'reply') {
		$reply_create_comment = [
			'#type' => 'checkbox',
			'#label' => elgg_echo('settings:indieweb:micropub:posts:reply_create_comment'),
			'#help' => elgg_echo('settings:indieweb:micropub:posts:reply_create_comment:help'),
			'name' => 'params[micropub_reply_create_comment]',
			'value' => 1,
			'default' => 0,
			'checked' => (bool) $entity->micropub_reply_create_comment,
			'switch' => true,
		];
	}
	
	echo elgg_view_field([
		'#type' => 'fieldset',
		'legend' => elgg_echo("indieweb:micropub:view:$post"),
		'fields' => [
			[
				'#type' => 'checkbox',
				'#label' => elgg_echo('settings:indieweb:micropub:posts:enable'),
				'#help' => elgg_echo("indieweb:micropub:view:$post:desc"),
				'name' => "params[enable_micropub_$post]",
				'value' => 1,
				'default' => 0,
				'checked' => (bool) $entity->{"enable_micropub_$post"},
				'switch' => true,
			],
			$reply_create_comment,
			[
				'#type' => 'checkbox',
				'#label' => elgg_echo('settings:indieweb:micropub:posts:status'),
				'#help' => elgg_echo('settings:indieweb:micropub:posts:status:help'),
				'name' => "params[micropub_status_$post]",
				'value' => 1,
				'default' => 0,
				'checked' => (bool) $entity->{"micropub_status_$post"},
				'switch' => true,
			],
			[
				'#type' => 'autocomplete',
				'#label' => elgg_echo('settings:indieweb:micropub:posts:author'),
				'#help' => elgg_echo('settings:indieweb:micropub:posts:author:help'),
				'name' => "params[micropub_author_$post]",
				'value' => $entity->{"micropub_author_$post"} ?: elgg_get_logged_in_user_guid(),
				'multiple' => false,
				'match_on' => 'users',
				'limit' => 1,
			],
			[
				'#type' => 'select',
				'#label' => elgg_echo('settings:indieweb:micropub:posts:type'),
				'#help' => elgg_echo('settings:indieweb:micropub:posts:type:help'),
				'name' => "params[micropub_type_$post]",
				'value' => $entity->{"micropub_type_$post"},
				'required' => true,
				'options_values' => $types,
			],
			[
				'#type' => 'checkbox',
				'#label' => elgg_echo('settings:indieweb:micropub:posts:field:link'),
				'#help' => elgg_echo('settings:indieweb:micropub:posts:field:link:help'),
				'name' => "params[micropub_field_link_$post]",
				'value' => 1,
				'default' => 0,
				'checked' => (bool) $entity->{"micropub_field_link_$post"},
				'switch' => true,
			],
			[
				'#type' => 'checkbox',
				'#label' => elgg_echo('settings:indieweb:micropub:posts:send_webmention'),
				'#help' => elgg_echo('settings:indieweb:micropub:posts:send_webmention:help'),
				'name' => "params[micropub_send_webmention_$post]",
				'value' => 1,
				'default' => 0,
				'checked' => (bool) $entity->{"micropub_send_webmention_$post"},
				'switch' => true,
			],
			[
				'#type' => 'checkbox',
				'#label' => elgg_echo('settings:indieweb:micropub:posts:field:content'),
				'#help' => elgg_echo('settings:indieweb:micropub:posts:field:content:help'),
				'name' => "params[micropub_field_content_$post]",
				'value' => 1,
				'default' => 0,
				'checked' => (bool) $entity->{"micropub_field_content_$post"},
				'switch' => true,
			],
			[
				'#type' => 'checkbox',
				'#label' => elgg_echo('settings:indieweb:micropub:posts:field:upload'),
				'#help' => elgg_echo('settings:indieweb:micropub:posts:field:upload:help'),
				'name' => "params[micropub_field_upload_$post]",
				'value' => 1,
				'default' => 0,
				'checked' => (bool) $entity->{"micropub_field_upload_$post"},
				'switch' => true,
			],
			[
				'#type' => 'number',
				'#label' => elgg_echo('settings:indieweb:micropub:posts:field:upload:limit'),
				'name' => "params[micropub_field_upload_limit_$post]",
				'min' => 1,
				'max' => 10,
				'value' => (bool) $entity->{"micropub_field_upload_limit_$post"} ?: 1,
			],
			[
				'#type' => 'checkbox',
				'#label' => elgg_echo('settings:indieweb:micropub:posts:field:tags'),
				'#help' => elgg_echo('settings:indieweb:micropub:posts:field:tags:help'),
				'name' => "params[micropub_field_tags_$post]",
				'value' => 1,
				'default' => 0,
				'checked' => (bool) $entity->{"micropub_field_tags_$post"},
				'switch' => true,
			],
			[
				'#type' => 'checkbox',
				'#label' => elgg_echo('settings:indieweb:micropub:posts:field:date'),
				'#help' => elgg_echo('settings:indieweb:micropub:posts:field:date:help'),
				'name' => "params[micropub_field_date_$post]",
				'value' => 1,
				'default' => 0,
				'checked' => (bool) $entity->{"micropub_field_date_$post"},
				'switch' => true,
			],
			[
				'#type' => 'checkbox',
				'#label' => elgg_echo('settings:indieweb:micropub:posts:field:location'),
				'#help' => elgg_echo('settings:indieweb:micropub:posts:field:location:help'),
				'name' => "params[micropub_field_location_$post]",
				'value' => 1,
				'default' => 0,
				'checked' => (bool) $entity->{"micropub_field_location_$post"},
				'switch' => true,
			],
		],
	]);
}

echo elgg_view_field([
	'#type' => 'hidden',
	'name' => 'plugin_id',
	'value' => 'indieweb',
]);

$footer = elgg_view_field([
	'#type' => 'submit',
	'value' => elgg_echo('save'),
]);

elgg_set_form_footer($footer);
