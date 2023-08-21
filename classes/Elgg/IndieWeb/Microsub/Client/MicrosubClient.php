<?php
/**
 * IndieWeb
 * @author Nikolai Shcherbin
 * @license GNU Affero General Public License version 3
 * @copyright (c) Nikolai Shcherbin 2022
 * @link https://wzm.me
**/

namespace Elgg\IndieWeb\Microsub\Client;

use Elgg\Traits\Di\ServiceFacade;
use Elgg\Database\QueryBuilder;
use Elgg\Database\Clauses\OrderByClause;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use p3k\XRay\Formats\HTML;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use function mf2\Parse;
use p3k\XRay;
use Elgg\IndieWeb\Webmention\Entity\Webmention;
use Elgg\IndieWeb\Microsub\Entity\MicrosubChannel;
use Elgg\IndieWeb\Microsub\Entity\MicrosubSource;
use Elgg\IndieWeb\Microsub\Entity\MicrosubItem;

class MicrosubClient {
	
	use ServiceFacade;
	
	/**
	 * {@inheritdoc}
	 */
	public static function name() {
		return 'microsub';
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
	public function fetchItems($url = '', $content = '') {
		if (!(bool) elgg_get_plugin_setting('enable_microsub', 'indieweb')) {
			return;
		}
		
		$xray = new XRay();
		$set_next_fetch = true;
		$parse_options = ['expect' => 'feed'];

		// Cleanup old items
		$cleanup_old_items = (bool) elgg_get_plugin_setting('microsub_cleanup_feeds', 'indieweb');

		// Mark unread on first import
		$mark_unread_on_first_import = (bool) elgg_get_plugin_setting('microsub_mark_unread', 'indieweb');

		// Allow video
		if ((bool) elgg_get_plugin_setting('microsub_allow_video', 'indieweb')) {
			$parse_options['allowIframeVideo'] = true;
		}
		
		$options = [
			'type' => 'object',
			'subtype' => MicrosubSource::SUBTYPE,
			'limit' => 0,
			'batch' => true,
			'batch_inc_offset' => false,
		];
		
		//Get sources
		if ($url) {
			$set_next_fetch = false;
			
			$options['metadata_name_value_pairs'] = [
				[
					'name' => 'url',
					'value' => $url,
				],
			];
			
			$sources = elgg_call(ELGG_IGNORE_ACCESS, function () use (&$options) {
				return elgg_get_entities($options);
			});
		} else {
			$options['metadata_name_value_pairs'] = [
				[
					'name' => 'status',
					'value' => 1,
				],
				[
					'name' => 'websub',
					'value' => 0,
				],
				[
					'name' => 'fetch_next',
					'value' => time() + 60,
					'operand' => '<',
					'type' => ELGG_VALUE_INTEGER,
				],
			];
			
			$sources = elgg_call(ELGG_IGNORE_ACCESS, function () use (&$options) {
				return elgg_get_entities($options);
			});
		}
		
		foreach ($sources as $source) {
			// Continue if the channel is disabled
			if (!$source->getContainerEntity()->getStatus()) {
				continue;
			}
			
			$url = $source->url;
			$tries = $source->getTries();
			$item_count = $source->getItemCount();
			$empty = $item_count == 0;
			if ($mark_unread_on_first_import && $empty) {
				$empty = false;
			}
			$source_id = $source->guid;
			$channel_id = $source->container_guid;
			$exclude = $source->getContainerEntity()->getPostTypesToExclude();

			$tries++;

			try {
				$parse = true;

				// Body can already be supplied, e.g. via WebSub
				if (!empty($content)) {
					$body = ltrim($content);
				} else {
					$request = new Request('GET', $url);
					// Generate conditional GET headers.
					if ($source->getEtag()) {
						$request = $request->withAddedHeader('If-None-Match', $source->getEtag());
					}
					if ($source->getLastModified()) {
						$request = $request->withAddedHeader('If-Modified-Since', gmdate(DateTimePlus::RFC7231, $source->getLastModified()));
					}
					$request->withAddedHeader('User-Agent', indieweb_microsub_http_client_user_agent());

					$actual_uri = null;
					$config = [
						'allow_redirects' => [
							'on_redirect' => function (RequestInterface $request, ResponseInterface $response, UriInterface $uri) use (&$actual_uri) {
								$actual_uri = (string) $uri;
							},
						],
					];
					$response = $this->http_client($config)->send($request);
					
					if ($response->hasHeader('ETag')) {
						$source->setMetadata('etag', $response->getHeaderLine('ETag'));
					}
					if ($response->hasHeader('Last-Modified')) {
						$source->setMetadata('modified', strtotime($response->getHeaderLine('Last-Modified')));
					}

					$body = ltrim($response->getBody()->getContents());

					// In case of a 304 Not Modified, there is no new content, so return
					// false.
					if ($response->getStatusCode() === 304) {
						$parse = false;
					}
				}

				$hash = md5($body);
				if ($parse && $source->getHash() != $hash) {
					// Parse the body.
					$parsed = $xray->parse($url, $body, $parse_options);
					if ($parsed && isset($parsed['data']['type']) && $parsed['data']['type'] === 'feed') {
						$context = $post_context_enabled ? $source->getPostContext() : [];
						$items_to_keep = $source->getKeepItemsInFeed();
						$items_in_feed = $source->getItemsInFeed();
						
						// Sort by published time
						$items_sorted = [];
						$items = $parsed['data']['items'];
						$total_items = count($items);
						
						foreach ($items as $i => $item) {
							if (isset($item['published'])) {
								$time = strtotime($item['published']);
								$items_sorted[$time . '.' . $i] = $item;
							} else {
								$items_sorted[] = $item;
							}
						}
						
						krsort($items_sorted);
						
						$c = 0;
						$ids = [];
						
						foreach ($items_sorted as $item) {
							// Exclude post types
							if ($exclude && !empty($item['post-type']) && in_array($item['post-type'], $exclude)) {
								continue;
							}
							
							//Save MicrosubItem
							$ids[] = $this->saveItem($item, $tries, $source_id, $channel_id, $empty, $context);
							
							// If we have number of items to keep and we hit the amount, break
							// the loop so we don't keep importing everything over and over.
							if (!$empty && $items_to_keep && $c > $items_to_keep) {
								break;
							}

							$c++;
						}

						if ($total_items) {
							$source->setMetadata('items_in_feed', $total_items);
						}

						// Cleanup old items if we can
						if (!$empty && $cleanup_old_items && $items_in_feed && $items_to_keep && $item_count >= $items_to_keep) {
							// Add five more items to keep so we don't hit exceptions like:
							//   - feeds with pinned items (e.g. mastodon).
							//   - posts that have been deleted.
							// We also pass on the ids that were found during the fetch so
							// they are not deleted.
							$items_to_keep += 5;
							
							elgg_call(ELGG_IGNORE_ACCESS, function () use ($items_to_keep, $channel_id, $source_id, &$ids) {
								$batch = elgg_get_entities([
									'type' => 'object',
									'subtype' => MicrosubItem::SUBTYPE,
									'metadata_name_value_pairs' => [
										[
											'name' => 'timestamp',
											'value' => time(),
											'operand' => '<',
										],
										[
											'name' => 'channel_id',
											'value' => $channel_id,
										],
										[
											'name' => 'source_id',
											'value' => $source_id,
										],
										[
											'name' => 'is_read',
											'value' => 1,
										],
									],
									'wheres' => function (QueryBuilder $qb, $from_alias = 'e') use (&$ids) {
										$md_alias = $qb->joinMetadataTable($from_alias, 'guid', 'id');
										return $qb->compare("$md_alias.value", 'NOT IN', $ids, ELGG_VALUE_INTEGER);
									},
									'limit' => $items_to_keep,
									'order_by' => new OrderByClause('e.time_created', 'ASC'),
								]);
									
								foreach ($batch as $item) {
									$item->delete();
								}
							});
						}
					}

					// Set changed
					$source->setMetadata('changed', time());

					// Set new hash
					$source->setMetadata('hash', $hash);
				}

				if ($set_next_fetch) {
					$source->setNextFetch();
				}
			
				$source->setMetadata('fetch_tries', $tries);
			
				$source->save();
		    } catch (\Exception $e) {
				elgg_log('Error fetching new items for ' . $url  . ' : ' . $e->getMessage(), 'ERROR');
				return false;
			}
		}
	}

	/**
	 * Saves an item
	 *
     * @param $data
     * @param int $tries
     * @param int $source_id
     * @param int $channel_id
	 * @param bool $empty
	 * @param array $context
	 *
	 * @return string
	 */
	protected function saveItem(&$data, $tries = 0, $source_id = 0, $channel_id = 0, $empty = false, $context = []) {
		// Prefer uid, then url, then hash the content
		if (isset($data['uid'])) {
		  $id = '@' . $data['uid'];
		} else if (isset($data['url'])) {
		  $id = $data['url'];
		} else {
		  $id = '#' . md5(json_encode($data));
		}
		
		// Check if this MicrosubItem exists
		$exists = elgg_call(ELGG_IGNORE_ACCESS, function () use ($source_id, $id) {
			return elgg_get_entities([
				'type' => 'object',
				'subtype' => MicrosubItem::SUBTYPE,
				'metadata_name_value_pairs' => [
					[
						'name' => 'id',
						'value' => $id,
					],
					[
						'name' => 'source_id',
						'value' => $source_id,
					],
				],
				'limit' => 1,
				'callback' => function ($row) {
					return $row->guid;
				},
			]);
		});
		
		if ($exists[0] > 0) {
			return $id;
		}

		// Reset tries
		$tries = 0;
		
		// Save MicrosubItem
		elgg_call(ELGG_IGNORE_ACCESS, function () use ($id, &$data, $source_id, $channel_id, $empty) {
			$entity = new MicrosubItem();
			$entity->owner_guid = elgg_get_site_entity()->guid;
			$entity->container_guid = $source_id;
			$entity->access_id = ACCESS_PUBLIC;
			$entity->channel_id = $channel_id;
			$entity->source_id = $source_id;
			$entity->data = json_encode($data);
			$entity->id = $id;
			$entity->is_read = $empty ? 1 : 0;
			$entity->post_context = '';
			$entity->post_type = isset($data['post-type']) ? $data['post-type'] : 'unknown';
			
			if (isset($data['published'])) {
				$timestamp = strtotime($data['published']);
				if (empty($timestamp) || !$timestamp || (is_numeric($timestamp) && $timestamp < 0)) {
					$timestamp = time();
				}
				
				$entity->timestamp = $timestamp;
			} else {
				$entity->timestamp = time();
			}

			$entity->time_created = $empty ? $entity->timestamp : time();

			return $entity->save();
		});
		
		// Save post context in queue
		if (!empty($context) && in_array($entity->post_type, $context)) {
			foreach ($context as $post_type) {
				$key = '';
				switch ($post_type) {
					case 'reply':
						$key = 'in-reply-to';
						break;
					case 'like':
						$key = 'like-of';
						break;
					case 'bookmark':
						$key = 'bookmark-of';
						break;
					case 'repost':
						$key = 'repost-of';
						break;
				}

				if ($key && !empty($data[$key][0])) {
					elgg()->postcontext->createMicrosubItem($entity, $data[$key][0]);
				}
			}
		}

		return $id;
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function sendNotification(Webmention $webmention, $parsed = null, $channel_id = 0) {
		if (!(bool) elgg_get_plugin_setting('enable_microsub', 'indieweb')) {
			return;
		}
		
		$microsub_endpoint = (bool) elgg_get_plugin_setting('microsub_endpoint', 'indieweb');
		$microsub_aperture_send_push = (bool) elgg_get_plugin_setting('microsub_aperture_send_push', 'indieweb');
		$microsub_aperture_api = elgg_get_plugin_setting('microsub_aperture_api', 'indieweb');

		// Send to aperture
		if (!$microsub_endpoint && $microsub_aperture_send_push && !empty($microsub_aperture_api)) {
			if ($post = $this->getPost($webmention)) {
				$client = elgg()->aperture;
				$client->sendPost($microsub_aperture_api, $post);
			}
		}

		// Send to internal notifications channel
		if ($microsub_endpoint) {
			$xray = new XRay();
			$url = $webmention->source;
			$target = elgg_get_site_url() . $webmention->target;
			
			try {
				// Get content if parsed is not set
				if (!isset($parsed)) {
					$options = ['headers' => ['User-Agent' => indieweb_microsub_http_client_user_agent()]];
					$response = $this->http_client()->get($url, $options);
					
					$body = $response->getBody()->getContents();
					$parsed = $xray->parse($url, $body);
				}
				
				if ($parsed && isset($parsed['data']['type']) && $parsed['data']['type'] == 'entry') {
					$data = $parsed['data'];
					
					foreach (['like-of', 'repost-of', 'bookmark-of', 'in-reply-to', 'mention-of'] as $item_url) {
						if (isset($data[$item_url]) && !empty($data[$item_url][0])) {
							$data[$item_url][0] = $target;
							
							// Make sure the array is unique
							$data[$item_url] = array_unique($data[$item_url]);
						}
					}
					
					// Set url to canonical webmention for in-reply-to. This makes sure
					// that you can  reply to it from a reader as the micropub endpoint
					// will get the right entity.
					if (isset($data['in-reply-to']) && !empty($data['in-reply-to'][0])) {
						$data['url'] = $target;
					}

					$tries = 0;
					$this->saveItem($data, $tries, 1, $channel_id);
				}
			}
			catch (\Exception $e) {
				elgg_log('Error saving notification for ' . $url  . ' : ' . $e->getMessage(), 'ERROR');
				return false;
			}
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function sendPushNotification($webmentions) {
		if (!(bool) elgg_get_plugin_setting('enable_microsub', 'indieweb')) {
			return;
		}
		
		$microsub_endpoint = (bool) elgg_get_plugin_setting('microsub_endpoint', 'indieweb');
		$microsub_indigenous_send_push = (bool) elgg_get_plugin_setting('microsub_indigenous_send_push', 'indieweb');
		$microsub_indigenous_api = elgg_get_plugin_setting('microsub_indigenous_api', 'indieweb');
		
		// Send to Indigenous
		if ($microsub_endpoint && $microsub_indigenous_send_push && !empty($microsub_indigenous_api) && !empty($webmentions)) {
			if (count($webmentions) == 1) {
				/** @var \Elgg\IndieWeb\Webmention\Entity\Webmention $webmention */
				$webmention = reset($webmentions);
				
				if (!empty($webmention->getAuthorName())) {
					$content = elgg_echo('indieweb:microsub:notification:author', [$webmention->getAuthorName()]);
				} else {
					$content = elgg_echo('indieweb:microsub:notification:new');
				}
			} else {
				$content = elgg_echo('indieweb:microsub:notification:count', [count($webmentions)]);
			}
			
			$post = [
				'apiToken' => $microsub_indigenous_api,
				'content' => $content,
			];
			
			$config = [
				'headers' => ['User-Agent' => indieweb_microsub_http_client_user_agent()],
			];

			try {
				$this->http_client($config)->post('https://indigenous.realize.be/send-notification', ['form_params' => $post]);
			} catch (\Exception $e) {
				elgg_log('Error sending push notification: ' . $e->getMessage(), 'ERROR');
				return false;
			}
		}
	}

	/**
	 * Get post ready for Aperture notification
	 *
	 * @param \Elgg\IndieWeb\Webmention\Entity\Webmention $webmention
	 *
	 * @return \stdClass
	*/
	protected function getPost(Webmention $webmention) {
		$properties = [];
		$base_url = elgg_get_site_url();
		$type = $webmention->property;

		switch ($type) {
			case 'like-of':
				$properties['like-of'] = [$base_url . $webmention->target];
				break;
			case 'repost-of':
				$properties['repost-of'] = [$base_url . $webmention->target];
				break;
			case 'bookmark-of':
				$properties['bookmark-of'] = [$base_url . $webmention->target];
				$properties['content'][0]['html'] = elgg_echo('indieweb:microsub:notification:bookmark', [$webmention->source, $webmention->source]);
				break;
			case 'in-reply-to':
				$properties['in-reply-to'] = [$base_url . $webmention->target];
				$content = $webmention->content_text;
				$properties['content'] = [$content];
				break;
			case 'mention-of':
				$properties['name'] = [elgg_echo('indieweb:microsub:notification:mention')];
				if (!empty($webmention->content_text)) {
					$properties['content'] = [$webmention->content_text];
				}
				break;
		}

		if (!empty($properties)) {
			$date = \Elgg\Values::normalizeTime(time());
			$properties['published'] = [$date->format('c')];
			$properties['url'] = [$base_url . $webmention->target];
			$this->getAuthor($properties, $webmention);

			$post = new \stdClass();
			$post->type = ['h-entry'];
			$post->properties = $properties;
			return $post;
		}
	}

	/**
	 * Adds the author the post
	 *
	 * @param $post The post to create
	 * @param \Elgg\IndieWeb\Webmention\Entity\Webmention $webmention The incoming webmention
	 */
	protected function getAuthor(&$post, $webmention) {
		$author = [];

		if (!empty($webmention->author_name)) {
			$author['type'] = ['h-card'];
			$properties = [];
			$properties['name'] = [$webmention->author_name];
			if ($author_url = $webmention->author_url) {
				$properties['url'] = [$author_url];
			}
			if ($author_photo = $webmention->author_photo) {
				$properties['photo'] = [$author_photo];
			}
			$author['properties'] = $properties;
		}

		if (!empty($author)) {
			$post['author'] = [$author];
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function searchFeeds($url, $body = null) {
		$return = [];
		$feeds = [];

		try {
			$contentType = '';
			if (!isset($body)) {
				$options = ['headers' => ['User-Agent' => indieweb_microsub_http_client_user_agent()]];
				$response = $this->http_client($options)->get($url, $options);
				
				$body = $response->getBody()->getContents();
				$contentType = $response->getHeader('Content-Type');
				if (!empty($contentType) && is_array($contentType)) {
					$contentType = $contentType[0];
				}
			}

			if (strpos($contentType, 'application/atom+xml') !== false || strpos(substr($body, 0, 50), '<feed ') !== false) {
				$feeds[] = [
					'url' => $url,
					'type' => 'atom'
				];
			} else if (strpos($contentType, 'application/rss+xml') !== false || strpos($contentType, 'text/xml') !== false || strpos($contentType, 'application/xml') !== false || strpos(substr($body, 0, 50), '<rss ') !== false) {
				$feeds[] = [
					'url' => $url,
					'type' => 'rss'
				];
			} else if (strpos($contentType, 'application/json') !== false && substr($body, 0, 1) == '{') {
				$feeddata = json_decode($body, true);
				if($feeddata && isset($feeddata['version']) && $feeddata['version'] == 'https://jsonfeed.org/version/1') {
					$feeds[] = [
						'url' => $url,
						'type' => 'jsonfeed'
					];
				}
			} else if (strpos($url, 'instagram.com') !== false) {
				// Add ending slash
				if (substr($url, -1) != '/') {
					$url .= '/';
				}
				
				$feeds[] = [
					'url' => $url,
					'type' => 'microformats'
				];
			} else {
				$mf2 = Parse($body, $url);
				
				if (isset($mf2['rel-urls'])) {
					foreach ($mf2['rel-urls'] as $rel => $info) {
						// We assume when 'rels[0]' is 'feed', this is a microformats feed
						if (isset($info['rels']) && isset($info['rels'][0]) && count($info['rels']) == 1 && $info['rels'][0] == 'feed') {
							$feeds[] = [
								'url' => $rel,
								'type' => 'microformats'
							];
						}
						
						if (isset($info['rels']) && in_array('alternate', $info['rels'])) {
							if (isset($info['type'])) {
								if (strpos($info['type'], 'application/json') !== false) {
									$feeds[] = [
										'url' => $rel,
										'type' => 'jsonfeed'
									];
								}
								
								if (strpos($info['type'], 'application/atom+xml') !== false) {
									$feeds[] = [
										'url' => $rel,
										'type' => 'atom'
									];
								}
								
								if (strpos($info['type'], 'application/rss+xml') !== false) {
									$feeds[] = [
										'url' => $rel,
										'type' => 'rss'
									];
								}
							}
						}
					}
				}

				// Insert our empty HTTP client so XRay doesn't start getting the json URL's
				$emptyHttpClient = new EmptyHTTP();
				$parsed = HTML::parse($emptyHttpClient, ['body' => $body, 'url' => $url, 'code' => 200], ['expect' => 'feed']);
				if ($parsed && isset($parsed['data']['type']) && $parsed['data']['type'] == 'feed') {
					$feeds[] = [
						'url' => $url,
						'type' => 'microformats'
					];
				}
			}

			// Sort feeds by priority
			$rank = ['microformats' => 0, 'jsonfeed' => 1, 'atom' => 2, 'rss' => 3];
			usort($feeds, function($a, $b) use ($rank) {
				return ($rank[$a['type']] < $rank[$b['type']]) ? -1 : 1;
			});

			$return['feeds'] = $feeds;
		}
		catch (\Exception $e) {
			elgg_log('Error fetching feeds for ' . $url . ': ' . $e->getMessage(), 'ERROR');
			return false;
		}

		return $return;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getTimeline(\Elgg\Request $request, $is_authenticated, $search = null) {
		$response = ['items' => []];
		
		$aggregated_feeds = $this->aggregatedFeeds(); // WIP

		$items = [];

		/** @var \Elgg\IndieWeb\Microsub\Entity\MicrosubItem[] $microsub_items */
		$microsub_items = [];

		// WIP -- offset ?
		// Set pager
		$page = $request->getParam('after', 0);
		if ($page > 0) {
			$request->getHttpRequest()->query->set('page', $page);
		}

		// Is read
		$is_read = $request->getParam('is_read');

		// Get source and channel variables
		$source = $request->getParam('source');
		$channel = $request->getParam('channel');
		
		// Get items from a channel

		// Notifications is stored as channel 0
		if ($channel === 'notifications' && $is_authenticated) {
			$channel = 0;
		}

		if (($channel || $channel === 0) && empty($search) && empty($source)) {
			$microsub_items = elgg_call(ELGG_IGNORE_ACCESS, function () use ($channel, $is_read) {
				return elgg_get_entities([
					'type' => 'object',
					'subtype' => MicrosubItem::SUBTYPE,
					'metadata_name_value_pairs' => [
						[
							'name' => 'channel_id',
							'value' => $channel,
						],
						[
							'name' => 'is_read',
							'value' => $is_read,
						],
					],
					'limit' => 0,
				]);
			});
		}

		// Search in a channel
		$is_source_search = false;

		// Check source as it may be a compound variable which triggers a search
		if ($source && strpos($source, ':') !== false) {
			[$source, $search] = explode(':', $source);
			$is_source_search = true;
		}

		if (!empty($search)) {
			$filter_by_channel = ($channel || $channel === 0) ? $channel : null;
			
			$microsub_items = elgg_call(ELGG_IGNORE_ACCESS, function () use ($search, $filter_by_channel, $is_read) {
				$query = $this->escape($search);
				
				return elgg_get_entities([
					'type' => 'object',
					'subtype' => MicrosubItem::SUBTYPE,
					'metadata_name_value_pairs' => [
						[
							'name' => 'channel_id',
							'value' => $filter_by_channel,
						],
						[
							'name' => 'is_read',
							'value' => $is_read,
						],
					],
					'wheres' => function (QueryBuilder $qb, $from_alias = 'e') use ($query) {
						$md_alias_1 = $qb->joinMetadataTable($from_alias, 'guid', ['id']);
						$md_alias_2 = $qb->joinMetadataTable($from_alias, 'guid', ['data']);
	
						return $qb->merge([
							$qb->compare("$md_alias_1.value", 'LIKE', "%$query%", ELGG_VALUE_STRING),
							$qb->compare("$md_alias_2.value", 'LIKE', "%$query%", ELGG_VALUE_STRING)
						], 'OR');
					},
					'limit' => 0,
				]);
			});
		}

		// Get items from a source
		if ($source && !$is_source_search) {
			$microsub_items = elgg_call(ELGG_IGNORE_ACCESS, function () use ($source, $is_read) {
				return elgg_get_entities([
					'type' => 'object',
					'subtype' => MicrosubItem::SUBTYPE,
					'metadata_name_value_pairs' => [
						[
							'name' => 'source_id',
							'value' => $source,
						],
						[
							'name' => 'is_read',
							'value' => $is_read,
						],
					],
					'limit' => 0,
				]);
			});
		}

		// If microsub items found, go get them
		if (!empty($microsub_items)) {
			$author_name = '';
			
			foreach ($microsub_items as $item) {
				$data = $item->getData();
				$fields_to_fix = ['in-reply-to', 'like-of', 'repost-of'];
				foreach ($fields_to_fix as $field) {
					if (isset($data->{$field})) {
						$flat = [];
						foreach ($data->{$field} as $field_value) {
							$flat[] = $field_value;
						}
						$data->{$field} = $flat;
					}
				}

				$channel_id = $item->getChannelId();

				// Check author name
				if ($channel_id > 0 && $item->container_guid > 0) {
					$author_name = $item->getContainerEntity()->url;
				}
			
				if (!empty($data->author->name)) {
					$author_name = $data->author->name;
				} else if (!empty($data->author->url)) {
					$author_name = $data->author->url;
				}

				// Apply media cache
				$this->applyCache($data);
				
				$entry = $data;
				$entry->_id = $item->id;
				$entry->_is_read = $is_authenticated ? $item->isRead() : true;
				$entry->_source = $item->getSourceIdForTimeline($author_name, $this->aggregatedFeeds());

				// Channel information
				if ($channel_id > 0 && $item->container_guid > 0) {
					$channel = $item->getChannel()->getDisplayName();
				} else {
					$channel = 'Notifications';
				}
				$entry->_channel = ['name' => $channel, 'id' => $channel_id];

				// Get context
				if (!isset($entry->refs) && ($context = $item->getContext())) {
					// SOMEDAY fix when https://github.com/indieweb/jf2/issues/41 lands.
					$entry->refs = $context;
				}

				$items[] = $entry;
			}

			// Calculate pager and after
			// WIP -- offset ?
			$page++;
			
			$response = ['paging' => ['after' => $page], 'items' => $items];
			
			if ($source || $is_source_search) {
				$microsub_source = get_entity($source);
				
				if ($microsub_source instanceof MicrosubSource) {
					$source_name = $microsub_source->url;
					if (strpos($source_name, 'granary') !== false) {
						$source_name = 'Granary';
					} else if (!empty($author_name)) {
						$source_name = $author_name;
					}
					$response['source'] = ['name' => $source_name];
				}
			}
		}
		
		return elgg_ok_response($response);
	}

	/**
	 * Apply cache settings.
	 *
	 * @param $data
	 */
	protected function applyCache($data) {
		// Author images
		if (isset($data->author->photo)) {
			$image = elgg()->mediacacher->saveImageFromUrl($data->author->photo);
			/** \Elgg\IndieWeb\Cache\MediaCacher **/
			$data->author->photo = elgg_get_inline_url($image);
		  
		}

		// Photos
		if (isset($data->photo) && !empty($data->photo) && is_array($data->photo)) {
			foreach ($data->photo as $i => $p) {
				$image = elgg()->mediacacher->saveImageFromUrl($p, 'photo');
				/** \Elgg\IndieWeb\Cache\MediaCacher **/
				$data->photo[$i] =  elgg_get_inline_url($image);
			}
		}

		// References
		if (isset($data->refs) && !empty($data->refs)) {
			foreach ($data->refs as $key => $ref) {
				// Author images
				if (isset($ref->author->photo) && !empty($ref->author->photo)) {
					$image = elgg()->mediacacher->saveImageFromUrl($p, 'photo');
					/** \Elgg\IndieWeb\Cache\MediaCacher **/
					$data->refs->{$key}->author->photo = elgg_get_inline_url($image);
				}
				
				// Photos
				if (isset($ref->photo) && !empty($ref->photo) && is_array($ref->photo)) {
					foreach ($ref->photo as $i => $p) {
						$image = elgg()->mediacacher->saveImageFromUrl($p, 'photo');
						/** \Elgg\IndieWeb\Cache\MediaCacher **/
						$data->refs->{$key}->photo[$i] = elgg_get_inline_url($image);
					}
				}
			}
		}
	}

	/**
	 * Escape a string.
	 *
	 * @param $string
	 *
	 * @return false|string
	 */
	protected function escape($string) {
		$s = json_encode($string);
		$s = substr_replace($s, '', 0, 1);
		$s = substr_replace($s, '', -1, 1);
		return $s;
	}

	public static function aggregatedFeeds() {
		$normalize = function($url) {
			$url = trim($url);
			return $url;
		};
		
		$feeds = elgg_get_plugin_setting('microsub_aggregated_feeds', 'indieweb', '');
		
		if (empty($feeds)) {
			return [];
		}
		
		if (is_string($feeds)) {
			$feeds = preg_split('/$\R?^/m', $feeds);
		}
		
		$feeds = array_filter($feeds);

		return array_map($normalize, $feeds);
	}
	
	public function http_client($options = []) {
		$config = [
			'verify' => true,
			'timeout' => 30,
		];
		$config = $config + $options;
		
		return new Client($config);
	}

}
