<?php

use Elgg\IndieWeb\Microsub\Entity\MicrosubChannel;

$entity = elgg_extract('entity', $vars);

if ($entity instanceof MicrosubChannel) {
    echo elgg_view_field([
        '#type' => 'hidden',
        'name' => 'guid',
        'value' => $entity->guid,
    ]);
}

echo elgg_view_field([
    '#type' => 'text',
    '#label' => elgg_echo('title'),
    'name' => 'title',
    'value' => elgg_extract('title', $vars),
    'required' => true,
]);

echo elgg_view_field([
    '#type' => 'checkbox',
    '#label' => elgg_echo('indieweb:microsub:microsub_channel:enable'),
    'name' => 'status',
    'value' => 1,
    'default' => 0,
    'checked' => (bool) elgg_extract('status', $vars),
    'switch' => true,
]);


echo elgg_view_field([
    '#type' => 'select',
    '#label' => elgg_echo('indieweb:microsub:microsub_channel:read_indicator'),
    'name' => 'read_indicator',
    'value' => elgg_extract('read_indicator', $vars),
    'options_values' => [
        1 => elgg_echo('indieweb:microsub:microsub_channel:read_indicator:count'),
        2 => elgg_echo('indieweb:microsub:microsub_channel:read_indicator:indicator'),
        3 => elgg_echo('indieweb:microsub:microsub_channel:read_indicator:disabled'),
    ],
]);

//Types
$objects = [
    'reply',
    'repost',
    'bookmark',
    'like',
    'note',
    'article',
    'photo',
    'video',
    'checkin',
    'rsvp',
];

ob_start();
foreach ($objects as $subtype) {
    echo elgg_view_field([
        '#type' => 'checkbox',
        'name' => "microsub_channel:exclude_post_type:$subtype",
        '#label' => elgg_echo("indieweb:microsub:post_type:$subtype"),
        'value' => 1,
        'default' => 0,
        'checked' => (bool) elgg_extract("microsub_channel:exclude_post_type:$subtype", $vars),
        'switch' => true,
    ]);
}
$inputs = ob_get_clean();

echo elgg_view('elements/forms/field', [
    'input' => $inputs,
    'label' => elgg_echo('indieweb:microsub:microsub_channel:exclude_post_type'),
    'class' => 'elgg-loud',
]);

echo elgg_view_field([
    '#type' => 'number',
    '#label' => elgg_echo('indieweb:microsub:microsub_channel:weight'),
    'name' => 'weight',
    'value' => (int) elgg_extract('weight', $vars, 0),
    'min' => -10,
    'max' => 10,
    'step' => 1,
]);

// form footer
$footer = elgg_view_field([
    '#type' => 'submit',
    'text' => elgg_echo('save'),
]);

elgg_set_form_footer($footer);
