<?php

use Elgg\IndieWeb\Microsub\Forms\NotificationsMicrosubChannel;

$entity = elgg_get_logged_in_user_entity();

if (!$entity->isAdmin()) {
    throw new \Elgg\Exceptions\Http\EntityPermissionsException();
}

$title = elgg_echo('add:object:microsub_channel:notifications');

$form = new NotificationsMicrosubChannel(null, $entity->guid);

$content = elgg_view_form('microsub/channel/notifications', [], $form());

echo elgg_view_module('info', $title, $content);
