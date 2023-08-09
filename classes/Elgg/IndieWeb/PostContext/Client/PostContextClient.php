<?php
/**
 * IndieWeb
 * @author Nikolai Shcherbin
 * @license GNU Affero General Public License version 3
 * @copyright (c) Nikolai Shcherbin 2022
 * @link https://wzm.me
**/

namespace Elgg\IndieWeb\PostContext\Client;


use Elgg\Traits\Di\ServiceFacade;
use DOMDocument;
use DOMXPath;
use p3k\XRay;
use Elgg\IndieWeb\Webmention\Entity\Webmention;
use Elgg\IndieWeb\Microsub\Entity\MicrosubChannel;
use Elgg\IndieWeb\Microsub\Entity\MicrosubSource;
use Elgg\IndieWeb\Microsub\Entity\MicrosubItem;

class PostContextClient {
	
	use ServiceFacade;
	
	/**
	 * {@inheritdoc}
	 */
	public static function name() {
		return 'postcontext';
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
	public function createMicrosubItem(MicrosubItem $entity, $url) {
		if (!(bool) elgg_get_plugin_setting('microsub_post_context', 'indieweb')) {
		   return;
		}

		// mobile.twitter.com doesn't have the necessary tags.
		if (strpos($url, 'mobile.twitter.com') !== false) {
			$url = str_replace('mobile.twitter.com', 'twitter.com', $url);
		}

		$data = [
			'url' => $url,
			'entity' => $entity,
		];

		$xray = new XRay();
		
		if (isset($data['url']) && !empty($data['entity'])) {
			$reference = null;

			try {
				$svc = elgg()->webmention;
				$response = $svc->get($data['url']);
				
				$body = $response->getBody()->getContents();
				
				// Get silo content with our own parser. XRay has support for external services, but usually with API keys. We add support using simple techniques.
				if (strpos($data['url'], 'coord.info') !== false || strpos($data['url'], 'geocaching.com') !== false) {
					$reference = $this->parseGeocaching($body);
				}
				
				// Parse with XRay
				if (!$reference) {
					$parsed = $xray->parse($data['url'], $body, ['expect'=>'feed']);
					
					if ($parsed && isset($parsed['data']['type']) && $parsed['data']['type'] == 'feed') {
						$reference = $parsed['data']['items'][0];
					}
				}
			} catch (\Exception $e) {
				elgg_log('Error getting post context for ' . $url  . ' : ' . $e->getMessage(), 'ERROR');
				return false;
			}
			
			// Save reference, if any
			if ($reference) {
				if (!isset($reference['url'])) {
					$reference['url'] = $data['url'];
				}
				
				elgg_call(ELGG_IGNORE_ACCESS, function () use ($entity, &$reference) {
					$entity->post_context = json_encode($reference);
					$entity->save();
				});
			}
		}
	}
	
	/**
	* Create context from geocaching.
	*
	* @param $body
	*
	* @return array|bool
	*/
	public function parseGeocaching($body) {
		$text = '';

		libxml_use_internal_errors(true);
		$doc = new DOMDocument();
		$doc->loadHTML($body);
		$xpath = new DOMXPath($doc);
		// There are two description on the page ...
		$description = $xpath->evaluate('//meta[@name="description"]/@content')->item(1);
		if (!empty($description->value)) {
			$text .= $description->value;
		}

		if ($text) {
			return [
				'type' => 'entry',
				'content' => [
					'text' => $text,
				],
				'post-type' => 'note',
			];
		}

		return false;
	}
}
