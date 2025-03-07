<?php

namespace Elgg\IndieWeb\IndieAuth\Menus;

use Elgg\Event;
use ElggMenuItem;
use Elgg\Menu\MenuItems;

/**
 * @access private
 */
class EntityMenu
{
    public static function tokenEntityMenu(Event $event): ?MenuItems
    {
        $entity = $event->getEntityParam();
        if (!$entity instanceof \Elgg\IndieWeb\IndieAuth\Entity\IndieAuthToken) {
            return null;
        }

        if (!$entity->canEdit()) {
            return null;
        }

        $menu = $event->getValue();

        // Change status
        $enabled = (bool) $entity->getStatus();

        $menu->add(ElggMenuItem::factory([
            'name' => 'activate',
            'text' => elgg_echo('indieweb:indieauth:token:activate'),
            'icon' => 'check',
            'href' => elgg_generate_action_url('indieauth/token/toggle_status', [
                'guid' => $entity->guid,
            ]),
            'item_class' => $enabled ? 'hidden' : '',
            'priority' => 177,
            'data-toggle' => 'revoke',
        ]));

        $menu->add(ElggMenuItem::factory([
            'name' => 'revoke',
            'text' => elgg_echo('indieweb:indieauth:token:revoke'),
            'icon' => 'delete',
            'href' => elgg_generate_action_url('indieauth/token/toggle_status', [
                'guid' => $entity->guid,
            ]),
            'item_class' => $enabled ? '' : 'hidden',
            'priority' => 178,
            'data-toggle' => 'activate',
        ]));

        // View JWT
        $menu->add(ElggMenuItem::factory([
            'name' => 'jwt',
            'text' => elgg_echo('indieweb:indieauth:view_jwt'),
            'icon' => 'info',
            'href' => elgg_http_add_url_query_elements('ajax/view/indieauth/token/jwt', [
                'guid' => $entity->guid,
            ]),
            'class' => 'elgg-lightbox',
            'data-colorbox-opts' => json_encode([
                'width' => '1000px',
                'height' => '98%',
                'maxWidth' => '98%',
            ]),
            'deps' => ['elgg/lightbox'],
            'priority' => 180,
        ]));

        // Edit
        $menu->add(ElggMenuItem::factory([
            'name' => 'edit',
            'text' => elgg_echo('edit'),
            'icon' => 'edit',
            'href' => elgg_http_add_url_query_elements('ajax/form/indieauth/token/save', [
                'guid' => $entity->guid,
            ]),
            'class' => 'elgg-lightbox',
            'data-colorbox-opts' => json_encode([
                'width' => '1000px',
                'height' => '98%',
                'maxWidth' => '98%',
            ]),
            'deps' => ['elgg/lightbox'],
            'priority' => 800,
        ]));

        return $menu;
    }

    public static function codeEntityMenu(Event $event): ?MenuItems
    {
        $menu = $event->getValue();

        $entity = $event->getEntityParam();
        if (!$entity instanceof \Elgg\IndieWeb\IndieAuth\Entity\IndieAuthAuthorizationCode) {
            return null;
        }

        if (!$entity->canEdit()) {
            return null;
        }

        $menu->remove('edit');

        return $menu;
    }
}
