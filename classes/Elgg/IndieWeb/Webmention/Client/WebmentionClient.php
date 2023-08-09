<?php
/**
 * IndieWeb
 * @author Nikolai Shcherbin
 * @license GNU Affero General Public License version 3
 * @copyright (c) Nikolai Shcherbin 2022
 * @link https://wzm.me
**/

namespace Elgg\IndieWeb\Webmention\Client;

use Elgg\Traits\Di\ServiceFacade;
use Elgg\IndieWeb\Webmention\Entity\Webmention;

class WebmentionClient {
	
	use ServiceFacade;
	
	public static function name() {
		return 'webmention';
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
	public function get($url) {
		$client = new \GuzzleHttp\Client();
		
		return $client->request('GET', $url);
	}
	
	/**
	 * Checks if url is a silo URL or not. Only handles Twitter urls right now.
	 *
	 * e.g. https://twitter.com/subwebz/status/1576317527466119168 should be marked as a silo url.
	 *
	 * @param $url
	 *
	 * @return bool
	 */
	public function isSiloURL($url) {
		$is_silo_url = false;

		if (strpos($url, 'twitter.com') !== false) {
			$is_silo_url = true;
		}

		return $is_silo_url;
	}

	public function sourceExistsAsSyndication(Webmention $webmention) {
		$exists = false;
		
		if (strpos($webmention->getSource(), 'brid-gy.appspot') !== false) {
			$parts = parse_url($webmention->getSource());
			
			$path_parts = explode('/', $parts['path']);
			
			if (!empty($path_parts[4])) {
				$exists = $this->checkIdenticalSyndication($webmention, $path_parts[5]);
			}
		}

		return $exists;
	}
	
	public function checkIdenticalSyndication(Webmention $webmention, $like) {
		return elgg_get_entities([
			'type' => 'object',
			'subtype' => Webmention::SUBTYPE,
			'guid' => $webmention->guid,
			'search_name_value_pairs' => [
				'name' => ['source'],
				'value' => "%$like%",
				'operand' => 'LIKE',
				'case_sensitive' => false,
			],
		]);
	}
	
	public function createComment(Webmention $webmention) {
		if ($webmention->hasCapability('commentable')) {
			if ($webmention->getProperty() === 'in-reply-to' && !empty($webmention->getPlainContent())) {
				$container_guid = 0;
				
				$target_guid = $webmention->getTargetGuid();

				try {
					$entity = get_entity($target_guid);
				
					//We support only commentable entities
					if ($entity instanceof \ElggObject && $entity->hasCapability('commentable')) {
						$container_guid = (int) $entity->guid;
						
						// This can be a reply on a comment
						if ($entity instanceof \ElggComment) {
							$container_guid = (int) $entity->container_guid;
						}
						
						if ($container_guid !=0) {
							$comment = new \ElggComment();
							$comment->owner_guid = elgg_get_site_entity()->guid;
							$comment->container_guid = $container_guid;
							$comment->access_id = ACCESS_PUBLIC;
							$comment->time_created = $webmention->getCreatedTime();
							$comment->save();
						}
					}
				}
				
				catch (Exception $e) {
					elgg_log(elgg_echo('webmention:create_comment:error', [$e->getMessage()]), 'error');
					return false;
				}
			}
		}
		
		return true;
	}
	
	public static function getSyndicationTargets(): array {
		$syndication_targets = elgg_get_plugin_setting('webmention_syndication_targets', 'indieweb', 'Twitter (bridgy)|https://brid.gy/publish/twitter');
		$syndication_targets = preg_split('/\\r\\n?|\\n/', $syndication_targets);
		$syndication_targets = array_filter($syndication_targets);

		return $syndication_targets;
	}
}
