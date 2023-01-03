<?php

use Elgg\IndieWeb\Microsub\Forms\EditMicrosubSource;

$entity = elgg_get_logged_in_user_entity();
	
if (!$entity->isAdmin()) {
	throw new \Elgg\Exceptions\Http\EntityPermissionsException();
}

$title = elgg_echo('add:object:microsub_source');

$form = new EditMicrosubSource(null);

$content = elgg_view_form('microsub/source/edit', [], $form());

echo elgg_view_module('info', $title, $content);
