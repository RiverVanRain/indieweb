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
	'mediaCacher' => \DI\autowire(\Elgg\IndieWeb\Cache\MediaCacher::class),
	'postContext' => \DI\create(\Elgg\IndieWeb\PostContext\Client\PostContextClient::class),
	'indieauth' => \DI\create(\Elgg\IndieWeb\IndieAuth\Client\IndieAuthClient::class),
];
