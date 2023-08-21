<?php

elgg_admin_gatekeeper();

$guid = (int) elgg_extract('guid', $vars);
$entity = get_entity($guid);

if (!$entity instanceof \Elgg\IndieWeb\Microsub\Entity\MicrosubItem) {
	throw new \Elgg\Exceptions\Http\EntityNotFoundException();
}

$modaltitle = elgg_format_element('h3', ['class' => 'modal-title'], elgg_echo('indieweb:microsub:microsub_item:data'));
$header = elgg_format_element('div', ['class' => 'modal-header'], $modaltitle);

$content = elgg_format_element('pre', [], elgg_view('output/longtext', [
	'value' => $entity->data
]));
	
echo elgg_format_element('div', ['class' => 'ui-front'], $header . $content);
