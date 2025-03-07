<?php

/**
 * IndieWeb
 * @author Nikolai Shcherbin
 * @license GNU Affero General Public License version 3
 * @copyright (c) Nikolai Shcherbin 2022
 * @link https://wzm.me
**/

return [
    'webmention' => \DI\create(\Elgg\IndieWeb\Webmention\Client\WebmentionClient::class),
    'indieweb.contact' => \DI\create(\Elgg\IndieWeb\Contacts\Client\ContactClient::class),
    'microsub' => \DI\create(\Elgg\IndieWeb\Microsub\Client\MicrosubClient::class),
    'aperture' => \DI\create(\Elgg\IndieWeb\Microsub\Client\ApertureClient ::class),
    'mediacacher' => \DI\create(\Elgg\IndieWeb\Cache\MediaCacher::class),
    'postcontext' => \DI\create(\Elgg\IndieWeb\PostContext\Client\PostContextClient::class),
    'indieauth' => \DI\create(\Elgg\IndieWeb\IndieAuth\Client\IndieAuthClient::class),
    'websub' => \DI\create(\Elgg\IndieWeb\WebSub\Client\WebSubClient::class),
    'httpClient' => \DI\create(\Elgg\IndieWeb\Services\HttpClient::class),
];
