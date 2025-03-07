<?php

use Elgg\IndieWeb\Microsub\Forms\EditMicrosubChannel;
use Elgg\IndieWeb\Microsub\Entity\MicrosubChannel;

$guid = (int) elgg_extract('guid', $vars);
elgg_entity_gatekeeper($guid, 'object', MicrosubChannel::SUBTYPE);

/* @var $entity MicrosubChannel */
$entity = get_entity($guid);

$container_guid = (int) elgg_extract('container_guid', $vars);

if (!$entity->canEdit()) {
    throw new \Elgg\Exceptions\Http\EntityPermissionsException();
}

$title = elgg_echo('edit:object:microsub_channel', [$entity->getDisplayName()]);

$form = new EditMicrosubChannel($entity, $container_guid);

$content = elgg_view_form('microsub/channel/edit', [], $form());

echo elgg_view_module('info', $title, $content);
