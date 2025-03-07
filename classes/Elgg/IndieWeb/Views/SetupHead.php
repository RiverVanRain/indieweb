<?php

/**
 * IndieWeb
 * @author Nikolai Shcherbin
 * @license GNU Affero General Public License version 3
 * @copyright (c) Nikolai Shcherbin 2022
 * @link https://wzm.me
**/

namespace Elgg\IndieWeb\Views;

class SetupHead
{
    public function __invoke(\Elgg\Event $event)
    {

        $return = $event->getValue();

        //webmention
        if ((bool) elgg_get_plugin_setting('enable_webmention', 'indieweb')) {
            $webmention_server = !empty(elgg_get_plugin_setting('webmention_server', 'indieweb')) ? elgg_get_plugin_setting('webmention_server', 'indieweb') : elgg_generate_url('default:view:webmention');

            $return['links'][] = [
                'rel' => 'webmention',
                'href' => $webmention_server,
            ];
        }

        //micropub
        if ((bool) elgg_get_plugin_setting('enable_micropub', 'indieweb')) {
            $return['links'][] = [
                'rel' => 'micropub',
                'href' => elgg_generate_url('default:view:micropub'),
            ];
        }

        //microsub
        if ((bool) elgg_get_plugin_setting('enable_microsub', 'indieweb')) {
            $microsub_endpoint = !empty(elgg_get_plugin_setting('microsub_endpoint', 'indieweb')) ? elgg_get_plugin_setting('microsub_endpoint', 'indieweb') : elgg_generate_url('default:view:microsub');

            $return['links'][] = [
                'rel' => 'microsub',
                'href' => !empty(elgg_get_plugin_setting('microsub_endpoint', 'indieweb')) ? elgg_get_plugin_setting('microsub_endpoint', 'indieweb') : elgg_generate_url('default:view:microsub'),
            ];
        }

        //indieauth
        $return['links'][] = [
            'rel' => 'me',
            'href' => 'mailto:' . elgg_get_site_entity()->email,
        ];

        if ((bool) elgg_get_plugin_setting('enable_indieauth_endpoint', 'indieweb')) {
            $return['links'][] = [
                'rel' => 'authorization_endpoint',
                'href' => elgg_generate_url('indieauth:auth'),
            ];
            $return['links'][] = [
                'rel' => 'token_endpoint',
                'href' => elgg_generate_url('indieauth:token'),
            ];
        } else {
            $return['links'][] = [
                'rel' => 'authorization_endpoint',
                'href' => elgg_get_plugin_setting('indieauth_external_auth', 'indieweb', 'https://indieauth.com/auth'),
            ];
            $return['links'][] = [
                'rel' => 'token_endpoint',
                'href' => elgg_get_plugin_setting('indieauth_external_endpoint', 'indieweb', 'https://tokens.indieauth.com/token'),
            ];
        }

        //websub
        if ((bool) elgg_get_plugin_setting('enable_websub', 'indieweb')) {
            $return['links'][] = [
                'rel' => 'hub',
                'href' => elgg_get_plugin_setting('websub_endpoint', 'indieweb'),
            ];
            $return['links'][] = [
                'rel' => 'self',
                'href' => elgg_get_site_url(),
            ];
        }

        //feeds
        $return['links'][] = [
            'rel' => 'alternate',
            'type' => 'application/jf2feed+json',
            'href' => elgg_http_add_url_query_elements(elgg_get_current_url(), [
                'view' => 'jf2feed',
            ]),
        ];

        return $return;
    }
}
