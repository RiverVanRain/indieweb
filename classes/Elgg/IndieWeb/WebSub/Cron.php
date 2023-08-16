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
	
	public static function processWebSubPub(\Elgg\Hook $hook) {
		if (!(bool) elgg_get_plugin_setting('enable_websub', 'indieweb')) {
		   return;
		}
		
		if (!(bool) elgg_get_plugin_setting('websub_send', 'indieweb')) {
		   return;
		}
		
		echo "Processes published websub starting" . PHP_EOL;
		elgg_log("Processes published websub starting", 'NOTICE');
		
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
                        'value' => 0 ?? false,
                    ],
                ],
                'limit' => false,
                'batch' => true,
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
					elgg_log('Publish response for ' . $entity->guid . ' : ' . $response->getStatusCode() . ' - ' . print_r($response->getBody()->getContents(), 1), 'NOTICE');
				}
				
				$websubpub->setMetadata('published', 1);
			}

		// restore access
		});
		
		echo "Finished published websub processing" . PHP_EOL;
		elgg_log("Finished published websub processing", 'NOTICE');
	}
	
	public static function processSubscribe(\Elgg\Hook $hook) {
		if (!(bool) elgg_get_plugin_setting('enable_websub', 'indieweb')) {
		   return;
		}
		
		if (!(bool) elgg_get_plugin_setting('websub_resubscribe', 'indieweb')) {
		   return;
		}
		
		echo "Processes websub resubscribe to subscriptions starting" . PHP_EOL;
		elgg_log("Processes websub resubscribe to subscriptions starting", 'NOTICE');
		
		// ignore access
		elgg_call(ELGG_IGNORE_ACCESS, function() {
			$sources = elgg_get_entities([
                'type' => 'object',
                'subtype' => MicrosubSource::SUBTYPE,
                'metadata_name_value_pairs' => [
                    [
                        'name' => 'status',
                        'value' => 1 ?? true,
                    ],
					[
                        'name' => 'websub',
                        'value' => 1 ?? true,
                    ],
                ],
                'limit' => false,
                'batch' => true,
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
		
		echo "Finished websub resubscribe to subscriptions processing" . PHP_EOL;
		elgg_log("Finished websub resubscribe to subscriptions processing", 'NOTICE');
	}
	
	public static function processNotifications(\Elgg\Hook $hook) {
		if (!(bool) elgg_get_plugin_setting('enable_websub', 'indieweb')) {
		   return;
		}
		
		if (!(bool) elgg_get_plugin_setting('websub_notification', 'indieweb')) {
		   return;
		}
		
		echo "Processes websub incoming notifications from hubs starting" . PHP_EOL;
		elgg_log("Processes websub incoming notifications from hubs starting", 'NOTICE');
		
		// ignore access
		elgg_call(ELGG_IGNORE_ACCESS, function() {
			$notifications = elgg_get_entities([
                'type' => 'object',
                'subtype' => WebSubNotification::SUBTYPE,
                'limit' => false,
                'batch' => true,
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
		
		echo "Finished websub incoming notifications from hubs processing" . PHP_EOL;
		elgg_log("Finished websub incoming notifications from hubs processing", 'NOTICE');
	}
	
	public static function cleanupWebSubPub(\Elgg\Hook $hook) {
		if (!(bool) elgg_get_plugin_setting('enable_websub', 'indieweb')) {
		   return;
		}
		
		if (!(bool) elgg_get_plugin_setting('websubpub_clean', 'indieweb')) {
		   return;
		}
		
		echo "Processes cleanup WebSubPub starting" . PHP_EOL;
		elgg_log("Processes cleanup WebSubPub starting", 'NOTICE');
		
		// ignore access
		elgg_call(ELGG_IGNORE_ACCESS, function() {
			$websubpubs = elgg_get_entities([
                'type' => 'object',
                'subtype' => WebSubPub::SUBTYPE,
                'metadata_name_value_pairs' => [
                    [
                        'name' => 'published',
                        'value' => 1 ?? true,
                    ],
                ],
                'limit' => false,
                'batch' => true,
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
		
		echo "Finished cleanup WebSubPub processing" . PHP_EOL;
		elgg_log("Finished cleanup WebSubPub processing", 'NOTICE');
	}
	
	public static function emptyWebSubPub(\Elgg\Hook $hook) {
		if (!(bool) elgg_get_plugin_setting('enable_websub', 'indieweb')) {
		   return;
		}
		
		echo "Processes empty WebSubPub starting" . PHP_EOL;
		elgg_log("Processes empty WebSubPub starting", 'NOTICE');
		
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
		
		echo "Finished empty WebSubPub processing" . PHP_EOL;
		elgg_log("Finished empty WebSubPub processing", 'NOTICE');
	}
}
