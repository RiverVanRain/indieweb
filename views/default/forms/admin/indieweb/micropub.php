<?php

$entity = elgg_get_plugin_from_id('indieweb');

//Basic
echo elgg_view_field([
    '#type' => 'fieldset',
    'legend' => elgg_echo('settings:indieweb:micropub'),
    'fields' => [
        [
            '#type' => 'checkbox',
            '#label' => elgg_echo('settings:indieweb:enable_micropub'),
            '#help' => elgg_echo('settings:indieweb:enable_micropub:help'),
            'name' => 'params[enable_micropub]',
            'value' => 1,
            'default' => 0,
            'checked' => (bool) $entity->enable_micropub,
            'switch' => true,
        ],
        [
            '#type' => 'checkbox',
            '#label' => elgg_echo('settings:indieweb:enable_micropub_media'),
            '#help' => elgg_echo('settings:indieweb:enable_micropub_media:help'),
            'name' => 'params[enable_micropub_media]',
            'value' => 1,
            'default' => 0,
            'checked' => (bool) $entity->enable_micropub_media,
            'switch' => true,
        ],
        [
            '#type' => 'checkbox',
            '#label' => elgg_echo('settings:indieweb:micropub_enable_update'),
            '#help' => elgg_echo('settings:indieweb:micropub_enable_update:help'),
            'name' => 'params[micropub_enable_update]',
            'value' => 1,
            'default' => 0,
            'checked' => (bool) $entity->micropub_enable_update,
            'switch' => true,
        ],
        [
            '#type' => 'checkbox',
            '#label' => elgg_echo('settings:indieweb:micropub_enable_delete'),
            '#help' => elgg_echo('settings:indieweb:micropub_enable_delete:help'),
            'name' => 'params[micropub_enable_delete]',
            'value' => 1,
            'default' => 0,
            'checked' => (bool) $entity->micropub_enable_delete,
            'switch' => true,
        ],
        [
            '#type' => 'checkbox',
            '#label' => elgg_echo('settings:indieweb:micropub_enable_source'),
            '#help' => elgg_echo('settings:indieweb:micropub_enable_source:help'),
            'name' => 'params[micropub_enable_source]',
            'value' => 1,
            'default' => 0,
            'checked' => (bool) $entity->micropub_enable_source,
            'switch' => true,
        ],
        [
            '#type' => 'checkbox',
            '#label' => elgg_echo('settings:indieweb:micropub_enable_category'),
            '#help' => elgg_echo('settings:indieweb:micropub_enable_category:help'),
            'name' => 'params[micropub_enable_category]',
            'value' => 1,
            'default' => 0,
            'checked' => (bool) $entity->micropub_enable_category,
            'switch' => true,
        ],
        [
            '#type' => 'checkbox',
            '#label' => elgg_echo('settings:indieweb:micropub_enable_geo'),
            '#help' => elgg_echo('settings:indieweb:micropub_enable_geo:help'),
            'name' => 'params[micropub_enable_geo]',
            'value' => 1,
            'default' => 0,
            'checked' => (bool) $entity->micropub_enable_geo,
            'switch' => true,
        ],
        [
            '#type' => 'checkbox',
            '#label' => elgg_echo('settings:indieweb:micropub_enable_contact'),
            '#help' => elgg_echo('settings:indieweb:micropub_enable_contact:help'),
            'name' => 'params[micropub_enable_contact]',
            'value' => 1,
            'default' => 0,
            'checked' => (bool) $entity->micropub_enable_contact,
            'switch' => true,
        ],
        [
            '#type' => 'checkbox',
            '#label' => elgg_echo('settings:indieweb:micropub_log_payload'),
            'name' => 'params[micropub_log_payload]',
            'value' => 1,
            'default' => 0,
            'checked' => (bool) $entity->micropub_log_payload,
            'switch' => true,
        ],
    ],
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
