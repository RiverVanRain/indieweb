<?php

/**
 * Displays a dropdown of RSVP
 */

$options = [
    'yes' => elgg_echo('field:rsvp:yes'),
    'no' => elgg_echo('field:rsvp:no'),
    'maybe' => elgg_echo('field:rsvp:maybe'),
    'interested' => elgg_echo('field:rsvp:interested'),
];

$vars['options_values'] = [];

foreach ($options as $value => $text) {
    $vars['options_values'][] = [
        'text' => $text,
        'value' => $value,
    ];
}

if (!isset($vars['name'])) {
    $vars['name'] = 'rsvp';
}

echo elgg_view('input/select', $vars);
