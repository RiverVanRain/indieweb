<?php
/**
 * IndieWeb
 * @author Nikolai Shcherbin
 * @license GNU Affero General Public License version 3
 * @copyright (c) Nikolai Shcherbin 2022
 * @link https://wzm.me
**/

namespace Elgg\IndieWeb\WebSub\Controller;

use function IndieWeb\http_rels;
use Elgg\IndieWeb\Microsub\Entity\MicrosubSource;

class WebSubController {

	public function __invoke(\Elgg\Request $request) {
		if (!(bool) elgg_get_plugin_setting('enable_websub', 'indieweb')) {
			throw new \Elgg\Exceptions\Http\PageNotFoundException();
		}
		
		$websub_hash = $request->getParam('websub_hash');
		
		elgg_set_http_header('Link: <' . elgg_get_plugin_setting('websub_endpoint', 'indieweb') . '>; rel="hub"');
		elgg_set_http_header('Link: <' . elgg_get_site_url() . '>; rel="self"');
		
		$response_code = 404;
		$response = '';

		/** @var \Elgg\IndieWeb\WebSub\Client\WebSubClient $websub_client */
		$websub_client = elgg()->websub;

		// Notification callback.
		if ($request->getMethod() === 'POST') {
			$url = '';
			
			// Check link header.
			try {
				// Note: we don't check on hub since not all hubs send that header.
				$result = http_rels($request->getHttpRequest()->headers);
				if (!empty($result['self'])) {
					$url = $result['self'][0];
				}
				// This happens when the hub is not send along.
				else if (!empty($result['self";'])) {
					$url = $result['self";'][0];
				}
			} catch (\Exception $ignored) {}
			
			if (!empty($url) && hash_equals($websub_hash, $websub_client->getHash($url))) {
				$websub_client->createNotificationQueueItem($url, (string) $request->getHttpRequest()->getContent());
				$status = 200;
			}
			
			// Log payload
			if ((bool) elgg_get_plugin_setting('websub_log_payload', 'indieweb')) {
				elgg_log('Notification callback:' . print_r($request->getHttpRequest()->headers->all(), true) . ' - ' . print_r($request->getHttpRequest()->getContent(), true), 'NOTICE');
			}
		}
		
		// Subscribe or unsubscribe callback.
		else if ($request->getParam('hub_mode') && $request->getParam('hub_topic') && $request->getParam('hub_challenge') && hash_equals($websub_hash, $websub_client->getHash($request->getParam('hub_topic')))) {
			$method = $request->getParam('hub_mode');

			switch ($method) {
				case 'subscribe':
					$url = $request->getParam('hub_topic');
					$seconds = $request->getParam('hub_lease_seconds', 0);
					
					if (!$seconds) {
						$seconds = 86400 * 7;
					}

					$result = elgg_call(ELGG_IGNORE_ACCESS, function() use ($url, $seconds) {
						$sources = elgg_get_entities([
							'type' => 'object',
							'subtype' => MicrosubSource::SUBTYPE,
							'metadata_name_value_pairs' => [
								[
									'name' => 'url',
									'value' => $url,
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
							$source->setMetadata('fetch_next', $seconds);
							$source->setMetadata('websub', 1);
						}
					});
					
					if ($result) {
						$response_code = 200;
						$response = $request->getParam('hub_challenge');
					}
				break;

				case 'unsubscribe':
					$url = $request->getParam('hub_topic');
					
					$result = elgg_call(ELGG_IGNORE_ACCESS, function() use ($url) {
						$sources = elgg_get_entities([
							'type' => 'object',
							'subtype' => MicrosubSource::SUBTYPE,
							'metadata_name_value_pairs' => [
								[
									'name' => 'url',
									'value' => $url,
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
							$source->setMetadata('fetch_next', 0);
							$source->setMetadata('websub', 0);
						}
					});
					
					if ($result) {
						$response_code = 200;
						$response = $request->getParam('hub_challenge');
					}
				break;
			}
			
			if ((bool) elgg_get_plugin_setting('websub_log_payload', 'indieweb')) {
				elgg_log('Subscribe callback: ' . print_r($request->getHttpRequest()->headers->all(), true) . ' - ' . print_r($request->getHttpRequest()->query->all(), true), 'NOTICE');
			}
		}

		return elgg_ok_response($response, '', REFERRER, $response_code);
	}

}
