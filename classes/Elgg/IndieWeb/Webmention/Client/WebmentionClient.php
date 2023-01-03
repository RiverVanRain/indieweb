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
use GuzzleHttp\ClientInterface as GuzzleClient;
use Elgg\IndieWeb\Webmention\Entity\Webmention;
use IndieWeb\MentionClient;

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

	//WIP
	/*
	public function sourceExistsAsSyndication(Webmention $webmention) {
		$exists = false;
		
		if (strpos($webmention->getSource(), 'brid-gy.appspot') !== false) {
			$parts = parse_url($webmention->getSource());
			
			$path_parts = explode('/', $parts['path']);
			
			
			if (!empty($path_parts[4])) {
				$exists = \Drupal::entityTypeManager()->getStorage('indieweb_syndication')->checkIdenticalSyndication($path_parts[5]);
			}
		}

		return $exists;
	}
	*/
	
	public function createComment(Webmention $webmention) {
		if ($webmention->hasCapability('commentable')) {
			if ($webmention->getProperty() == 'in-reply-to' && !empty($webmention->getPlainContent())) {
				
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
							$comment = new \Elgg\Comment();
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
	
//WIP
/*

  public function createQueueItem($source, $target, $entity_id = '', $entity_type_id = '') {
    static $seen = [];

    // If the target is not a URL, don't send.
    if (!filter_var($target, FILTER_VALIDATE_URL)) {
      return;
    }

    $data = [
      'source' => $source,
      'target' => $target,
      'entity_id' => $entity_id,
      'entity_type_id' => $entity_type_id,
    ];

    // Different domain for content.
    $config = \Drupal::config('indieweb_webmention.settings');
    $content_domain = $config->get('webmention_content_domain');
    if (!empty($content_domain)) {
      $data['source'] = str_replace(\Drupal::request()->getSchemeAndHttpHost(), $content_domain, $data['source']);
    }

    // Check if this is a silo url. In case it is, swap with a potential
    // syndication target like Brid.gy. At the moment the silo url only
    // returns for twitter, so check https://brid.gy/publish/twitter.
    if ($this->isSiloURL($target)) {
      $targets = indieweb_get_syndication_targets();
      if (isset($targets['https://brid.gy/publish/twitter'])) {
        $data['target'] = 'https://brid.gy/publish/twitter';
      }
      else {
        // No need to go through because Twitter doesn't support webmentions
        // anyway.
        return;
      }
    }

    // Calculate hash.
    $hash = md5(implode(",", $data));

    // No need to it the queue more than once.
    if (isset($seen[$hash])) {
      return;
    }

    $seen[$hash] = $hash;

    try {
      \Drupal::queue(INDIEWEB_WEBMENTION_QUEUE)->createItem($data);
    }
    catch (\Exception $e) {
      \Drupal::logger('indieweb_queue')->notice('Error creating queue item: @message', ['@message' => $e->getMessage()]);
	  return false;
    }
  }


*/
}
