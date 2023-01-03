<?php

use Elgg\IndieWeb\Microsub\Forms\EditMicrosubSource;
use Elgg\IndieWeb\Microsub\Entity\MicrosubSource;

$guid = (int) elgg_extract('guid', $vars);
elgg_entity_gatekeeper($guid, 'object', MicrosubSource::SUBTYPE);

/* @var $entity MicrosubSource */
$entity = get_entity($guid);

if (!$entity->canEdit()) {
	throw new \Elgg\Exceptions\Http\EntityPermissionsException();
}

$title = elgg_echo('edit:object:microsub_source', [$entity->getDisplayName()]);

$form = new EditMicrosubSource($entity);

$content = elgg_view_form('microsub/source/edit', [], $form());

echo elgg_view_module('info', $title, $content);
