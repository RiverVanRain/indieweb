<?php
/**
 * IndieWeb
 * @author Nikolai Shcherbin
 * @license GNU Affero General Public License version 3
 * @copyright (c) Nikolai Shcherbin 2022
 * @link https://wzm.me
**/

namespace Elgg\IndieWeb\WebSub;

use Elgg\IndieWeb\WebSub\Entity\WebSubPub;
use Elgg\IndieWeb\WebSub\Entity\WebSubNotification;
use Elgg\IndieWeb\Microsub\Entity\MicrosubSource;
use GuzzleHttp\Client;

class Cron {
	
	public static function processWebSubPub(\Elgg\Event $event) {
		if (!(bool) elgg_get_plugin_setting('enable_websub', 'indieweb')) {
		   return;
		}
		
		if (!(bool) elgg_get_plugin_setting('websub_send', 'indieweb')) {
		   return;
		}

		// ignore access
		elgg_call(ELGG_IGNORE_ACCESS, function() {
			$pages = [];
			$config_pages = explode("\n", elgg_get_plugin_setting('websub_pages', 'indieweb'));
			
			foreach ($config_pages as $page) {
				$page = trim($page);
				if (!empty($page)) {
					$pages[] = $page;
				}
			}
			
			$websubpubs = elgg_get_entities([
                'type' => 'object',
                'subtype' => WebSubPub::SUBTYPE,
                'metadata_name_value_pairs' => [
                    [
                        'name' => 'published',
                        'value' => 0,
                    ],
                ],
                'limit' => false,
                'batch' => true,
				'batch_size' => 50,
                'batch_inc_offset' => false
            ]);
			
			if (empty($websubpubs)) {
				return true;
			}
			
			foreach ($websubpubs as $websubpub) {
				$entity = get_entity($websubpub->entity_id);
				
				if (!$entity instanceof \ElggEntity) {
					continue;
				}
				
				$pages[] = $entity->getURL();
				
				$options = [
					'form_params' => [
						'hub.mode' => 'publish',
						'hub.url' => $pages,
					]
				];
				
				$hub_endpoint = elgg_get_plugin_setting('websub_endpoint', 'indieweb');
				
				$client = new Client();
				$response = $client->post($hub_endpoint, $options);
				
				if ((bool) elgg_get_plugin_setting('websub_log_payload', 'indieweb')) {
					elgg_log('Publish response for ' . $entity->guid . ' : ' . $response->getStatusCode() . ' - ' . print_r($response->getBody()->getContents(), true), 'NOTICE');
				}
				
				$websubpub->setMetadata('published', 1);
			}

		// restore access
		});
	}
	
	public static function processSubscribe(\Elgg\Event $event) {
		if (!(bool) elgg_get_plugin_setting('enable_websub', 'indieweb')) {
		   return;
		}
		
		if (!(bool) elgg_get_plugin_setting('websub_resubscribe', 'indieweb')) {
		   return;
		}
		
		// ignore access
		elgg_call(ELGG_IGNORE_ACCESS, function() {
			$sources = elgg_get_entities([
                'type' => 'object',
                'subtype' => MicrosubSource::SUBTYPE,
                'metadata_name_value_pairs' => [
                    [
                        'name' => 'status',
                        'value' => 1,
                    ],
					[
                        'name' => 'websub',
                        'value' => 1,
                    ],
                ],
                'limit' => false,
                'batch' => true,
				'batch_size' => 50,
                'batch_inc_offset' => false
            ]);
			
			if (empty($sources)) {
				return true;
			}
			
			foreach ($sources as $source) {
				$url = $source->url;
				
				/** @var \Elgg\IndieWeb\WebSub\Client\WebSubClient */
				$svc = elgg()->websub;
				
				if ($info = $svc->discoverHub($url)) {
					$svc->subscribe($info['self'], $info['hub'], 'subscribe');
				}
			}

		// restore access
		});
	}
	
	public static function processNotifications(\Elgg\Event $event) {
		if (!(bool) elgg_get_plugin_setting('enable_websub', 'indieweb')) {
		   return;
		}
		
		if (!(bool) elgg_get_plugin_setting('websub_notification', 'indieweb')) {
		   return;
		}
		
		// ignore access
		elgg_call(ELGG_IGNORE_ACCESS, function() {
			$notifications = elgg_get_entities([
                'type' => 'object',
                'subtype' => WebSubNotification::SUBTYPE,
                'limit' => false,
                'batch' => true,
				'batch_size' => 50,
                'batch_inc_offset' => false
            ]);
			
			if (empty($notifications)) {
				return true;
			}
			
			foreach ($notifications as $notification) {
				$url = $notification->url;
				$content = $notification->content;
				
				/** @var \Elgg\IndieWeb\Microsub\Client\MicrosubClient */
				$svc = elgg()->microsub;
				
				if (!empty($url) && !empty($content)) {
					$svc->fetchItems($url, $content);
				}
				
				$notification->delete();
			}

		// restore access
		});
		
	}
	
	public static function cleanupWebSubPub(\Elgg\Event $event) {
		if (!(bool) elgg_get_plugin_setting('enable_websub', 'indieweb')) {
		   return;
		}
		
		if (!(bool) elgg_get_plugin_setting('websubpub_clean', 'indieweb')) {
		   return;
		}
		
		// ignore access
		elgg_call(ELGG_IGNORE_ACCESS, function() {
			$websubpubs = elgg_get_entities([
                'type' => 'object',
                'subtype' => WebSubPub::SUBTYPE,
                'metadata_name_value_pairs' => [
                    [
                        'name' => 'published',
                        'value' => 1,
                    ],
                ],
                'limit' => false,
                'batch' => true,
				'batch_size' => 50,
                'batch_inc_offset' => false
            ]);
			
			if (empty($websubpubs)) {
				return true;
			}
			
			foreach ($websubpubs as $websubpub) {
				$websubpub->delete();
			}

		// restore access
		});
	}
	
	public static function emptyWebSubPub(\Elgg\Event $event) {
		if (!(bool) elgg_get_plugin_setting('enable_websub', 'indieweb')) {
		   return;
		}

		// ignore access
		elgg_call(ELGG_IGNORE_ACCESS, function() {
			$websubpubs = elgg_get_entities([
                'type' => 'object',
                'subtype' => WebSubPub::SUBTYPE,
                'wheres' => [
					function (\Elgg\Database\QueryBuilder $qb) {
						$md_alias = $qb->joinMetadataTable('e', 'guid', 'entity_id', 'left');
						
						return $qb->compare("$md_alias.value", 'IS NULL');
					},
				],
                'limit' => false,
                'batch' => true,
				'batch_size' => 50,
                'batch_inc_offset' => false
            ]);
			
			if (empty($websubpubs)) {
				return true;
			}
			
			foreach ($websubpubs as $websubpub) {
				$websubpub->delete();
			}

		// restore access
		});
	}
}
