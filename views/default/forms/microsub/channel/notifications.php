<?php

use Elgg\IndieWeb\Microsub\Entity\MicrosubChannel;

echo elgg_view_field([
	'#html' => elgg_format_element('div', [], elgg_echo('indieweb:microsub_channel:notifications:create')),
]);

// form footer
$footer = elgg_view_field([
	'#type' => 'submit',
	'value' => elgg_echo('save'),
]);

elgg_set_form_footer($footer);
