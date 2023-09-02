<?php
/**
 * IndieWeb
 * @author Nikolai Shcherbin
 * @license GNU Affero General Public License version 3
 * @copyright (c) Nikolai Shcherbin 2022
 * @link https://wzm.me
**/

namespace Elgg\IndieWeb\WebSub\Client;

use Elgg\Traits\Di\ServiceFacade;
use GuzzleHttp\Client;
use p3k\WebSub\Client as WebSubPubClient;
use Elgg\IndieWeb\Microsub\Entity\MicrosubSource;
use Elgg\IndieWeb\Microsub\Client\MicrosubClient;
use Elgg\IndieWeb\WebSub\Entity\WebSubNotification;

class WebSubClient {
	
	use ServiceFacade;
	
	/**
	 * {@inheritdoc}
	 */
	public static function name() {
		return 'websub';
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function __get($name) {
		return $this->$name;
	}

	/**
	 * {@inheritdoc}
	 */
	public function subscribe($url, $hub, $mode) {
		$options = [
			'form_params' => [
				'hub.mode' => $mode,
				'hub.topic' => $url,
				'hub.callback' => elgg_generate_url('default:view:websub', [
					'websub_hash' => $this->getHash($url),
				]),
			]
		];

		try {
			$client = new Client();
			$response = $client->post($hub, $options);

			if ((bool) elgg_get_plugin_setting('websub_log_payload', 'indieweb')) {
				elgg_log('Subscribe response for ' . $url . ', ' . $hub . ', ' . $mode . ' - code: ' . $response->getStatusCode() . ' - ' . print_r($response->getBody()->getContents(), 1), 'NOTICE');
			}
		} catch (\Exception $e) {
			elgg_log('Error sending subscribe request: ' . $e->getMessage(), 'ERROR');
		}

		return true;
	}

	/**
	 * {@inheritDoc}
     */
	public function discoverHub($url, $debug = false) {
		$webSubClient = new WebSubPubClient();

		try {
			$response = $webSubClient->discover($url);
			
			if (!empty($response['hub']) && !empty($response['self'])) {
				if ($debug) {
					elgg_log(print_r($response), 'NOTICE');
				}
				
				return [
					'hub' => $response['hub'],
					'self' => $response['self']
				];
			}
		} catch (\Exception $e) {
			elgg_log('Error discovering hub: ' . $e->getMessage(), 'ERROR');
		}

		return false;
	}

	/**
     *{@inheritdoc}
     */
	public function getHash($url) {
		$hash = base64_encode(hash('sha256', $url, true));
		return str_replace(['+', '/', '='], ['-', '_', ''], $hash);
	}
	
	/**
	 * {@inheritdoc}
     */
	public function createNotificationQueueItem($url, $content) {
		if (!(bool) elgg_get_plugin_setting('websub_notification', 'indieweb')) {
		   return;
		}
		
		elgg_call(ELGG_IGNORE_ACCESS, function() use($url, $content) {
			$entity = new WebSubNotification();
			$entity->owner_guid = elgg_get_site_entity()->guid;
			$entity->container_guid = elgg_get_site_entity()->guid;
			$entity->access_id = ACCESS_PRIVATE;
			$entity->url = $url;
			$entity->content = $content;

			if (!$entity->save()) {
				elgg_log(elgg_echo('indieweb:websub:create:notification:item', [$url]), 'error');
			}
		});
	}
}
