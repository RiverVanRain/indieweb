<?php
/**
 * IndieWeb
 * @author Nikolai Shcherbin
 * @license GNU Affero General Public License version 3
 * @copyright (c) Nikolai Shcherbin 2022
 * @link https://wzm.me
**/

namespace Elgg\IndieWeb\Webmention;

use Elgg\IndieWeb\Webmention\Entity\Webmention;
use Elgg\IndieWeb\Webmention\Entity\Syndication;
use p3k\XRay;

class Cron {
	
	public static function processWebmentions(\Elgg\Hook $hook) {
		if (!(bool) elgg_get_plugin_setting('enable_webmention', 'indieweb')) {
			return;
		}
		
		echo "Processes received webmentions starting" . PHP_EOL;
		elgg_log("Processes received webmentions starting", 'NOTICE');
		
		// ignore access
		elgg_call(ELGG_IGNORE_ACCESS, function() {
			$xray = new XRay();
			
			// Store valid webmentions for push notifications.
			$valid_webmentions = [];
			
            $webmentions = elgg_get_entities([
                'type' => 'object',
                'subtype' => Webmention::SUBTYPE,
                'metadata_name_value_pairs' => [
                    [
                        'name' => 'status',
                        'value' => 0,
                    ],
					[
                        'name' => 'published',
                        'value' => 0,
                    ],
                ],
                'limit' => false,
                'batch' => true,
                'batch_inc_offset' => false
            ]);
			
			if (empty($webmentions)) {
				return true;
			}

            foreach ($webmentions as $webmention) {
				$error = false;
				$parsed = [];
				
				try {
					// Get the source body
					$source = $webmention->getSource();
					$target = $webmention->getTarget();
					
					/** Elgg\IndieWeb\Webmention\Client\WebmentionClient **/
					$svc = elgg()->webmention;
					$response = $svc->get($source);
					$body = $response->getBody()->getContents();
					
					// Parse the body. Make sure the target is found
					$parsed = $xray->parse($source, $body, ['target' => $target]);
					
					// Target url was found on source and doc is valid, start parsing.
					// There is a possibility a feed was found, so check for that first.
					// If there's a feed, take the first item.
					
					if ($parsed && isset($parsed['data']['type']) && $parsed['data']['type'] == 'feed') {
						$parsed = ['data' => $parsed['data']['items'][0]];
					}
					
					if ($parsed && isset($parsed['data']['type']) && $parsed['data']['type'] == 'entry') {
						$data = $parsed['data'];
						
						// Type
						$type = 'entry';

						if (!empty($data['type'])) {
							$type = $data['type'];
						}
						
						$webmention->setMetadata('object_type', $type);
						
						// Author
						$author_values = [];
						
						foreach (['name', 'photo', 'url'] as $key) {
							if (!empty($data['author'][$key])) {
								$author_value = trim($data['author'][$key]);
								if (!empty($author_value)) {
									$webmention->setMetadata('author_' . $key, $author_value);
									
									$author_values[$key] = $author_value;
									
									// Cache the avatar
									if ($key === 'photo') {
										$image = elgg()->mediacacher->saveImageFromUrl($author_value);
										/** \Elgg\IndieWeb\Cache\MediaCacher **/
										
										$webmention->setMetadata('author_thumbnail_url', elgg_get_inline_url($image));
									}
								}
							}
						}

						// Contacts 
						if (!empty($author_values) && (bool) elgg_get_plugin_setting('webmention_create_contact', 'indieweb')) {
							elgg_call(ELGG_IGNORE_ACCESS, function () use (&$author_values) {
								/** \Elgg\IndieWeb\Contacts\Entity\Contact\ContactClient **/
								$svc_contact = elgg()->{'indieweb.contact'};
								$svc_contact->storeContact($author_values);
							});
						}
						
						// Content
						foreach (['html', 'text'] as $key) {
							if (!empty($data['content'][$key])) {
								$webmention->setMetadata('content_' . $key, $data['content'][$key]);
							}
						}
						
						// Media
						foreach (['photo', 'video', 'audio'] as $key) {
							if (!empty($data[$key])) {
								$webmention->setMetadata($key, $data[$key]);
							}
						}
						
						// Published
						if (isset($data['published']) && !empty($data['published'])) {
							$webmention->time_created = strtotime($data['published']);
						}
						
						// Updated
						if (isset($data['updated']) && !empty($data['updated'])) {
							$webmention->time_updated = strtotime($data['updated']);
						}
						
						// Property. 'mention-of' is the default if we can't detect anything specific. In case rsvp is set, set $data['url'] to 'in-reply-to'.
						$property = 'mention-of';
						$urls = [];
						$properties = ['rsvp', 'like-of', 'repost-of', 'in-reply-to', 'mention-of', 'bookmark-of', 'follow-of'];
						foreach ($properties as $p) {
							if (isset($data[$p]) && !empty($data[$p])) {
								$property = $p;
								$urls = $data[$p];
								break;
							}
						}
						
						$webmention->setMetadata('property', $property);

						// RSVP
						if ($property === 'rsvp') {
							$webmention->setMetadata('rsvp', $data['rsvp']);
						}
						
						// Url
						if (!empty($data['url'])) {
							$webmention->setMetadata('data_url', $data['url']);
						}
						
						// Get the target guid
						$target_guid = indieweb_get_guid($target);
						$webmention->setMetadata('target_guid', $target_guid);
						
						$target = indieweb_get_path($target);
						$webmention->setMetadata('target', $target);

						// In case of a comment, let's set the parent target to the node.
						if (strpos($target, 'comment') !== false) {
							$cid = str_replace(['/comment/indieweb/', '/comment/view/'], '', $target);
							$cid = explode('/', $cid);
							$comment_id = $cid[0];
							
							/** @var \ElggComment $comment */
							$comment = get_entity($comment_id);
							
							if ($comment instanceof \ElggComment) {
								$webmention->setMetadata('parent_target', $comment->container_guid);
							}
						}
						
						// Check the urls in case of in-reply-to. When there are at least two, and there is a twitter url, then it comes from brid.gy which sends back to all parents
						if ($property === 'in-reply-to' && count($urls) > 1 && strpos($webmention->getSource(), 'brid-gy.appspot') !== false) {
							foreach ($urls as $u) {
								if ((bool) $svc->isSiloURL($u)) {
									$syndications = elgg_get_entities([
										'type' => 'object',
										'subtype' => Syndication::SUBTYPE,
										'metadata_name_value_pairs' => [
											[
												'name' => 'source_url',
												'value' => $u,
											],
										],
										'limit' => false,
										'batch' => true,
										'batch_inc_offset' => false
									]);
									
									if (!empty($syndications)) {
										$syndication = array_shift($syndications);
										
										try {
											$syndication_source = get_entity($syndication->getSourceId());
											
											if ($syndication_source instanceof \ElggEntity) {
												$syndication_source_url = indieweb_get_path($syndication_source->getURL());
												
												if ($syndication_source_url != $target) {
													$error = true;
													$parsed['error'] = 'duplicate';
													$parsed['error_description'] = elgg_echo('webmention:syndication:source:error', [$syndication_source_url, $target, $syndication->guid]);
													break;
												}
											}
										}
										
										catch (\Exception $ignored) {}
									}
								}
							}
						}

						// Check identical webmentions. If the source, target and property are the same, trigger an error.
						if ((bool) elgg_get_plugin_setting('webmention_detect_identical', 'indieweb')) {
							$exists = elgg_get_entities([
								'type' => 'object',
								'subtype' => Webmention::SUBTYPE,
								'metadata_name_value_pairs' => [
									[
										'name' => 'source',
										'value' => $source,
									],
									[
										'name' => 'target',
										'value' => $target,
									],
									[
										'name' => 'property',
										'value' => $property,
									],
									[
										'name' => 'published',
										'value' => 1,
									],
								],
								'limit' => false,
								'callback' => function ($row) {
									return $row->guid;
								},
							]);
							
							if ($exists) {
								$error = true;
								$parsed['error'] = 'duplicate';
								$parsed['error_description'] = elgg_echo('webmention:duplicate:error', [$source, $target, $property]);
							}
						}
					}
					
					// Error while parsing
					else if (isset($parsed['error'])) {
						$error = true;
					}
					
					// Unknown error
					else {
						$error = true;
						$parsed['error'] = 'unknown';
						$parsed['error_description'] = elgg_echo('unknown_error');
					}
				}
				catch (\Exception $e) {
					$error = true;
					$parsed['error'] = 'exception';
					$parsed['error_description'] = $e->getMessage();
				}
				
				// Valid webmention
				if (!$error) {
					// Set to published and save.
					$webmention->access_id = ACCESS_PUBLIC;
					$webmention->status = 1;
					$webmention->published = 1;
					$webmention->property = 'received';
					$webmention->save();
					
					// Check syndication. If it exists, no need for further actions.
					if (!$svc->sourceExistsAsSyndication($webmention)) {
						// Notification
						if ((bool) elgg_get_plugin_setting('enable_microsub', 'indieweb')) {
							$microsub_client = elgg()->microsub;
							$microsub_client->sendNotification($webmention, $parsed);
							$valid_webmentions[] = $webmention;
						}
							
						// Create a comment
						if ((bool) elgg_get_plugin_setting('webmention_enable_comment_create', 'indieweb') && $webmention->target_guid != 0) {
							$svc->createComment($webmention);
						}
					}
				}
				
				// Reset the type and property.
				else {
					$error_type = isset($parsed['error']) ? $parsed['error'] : 'unknown';

					$webmention->setMetadata('object_type', $error_type);
					$webmention->setMetadata('property', $error_type);
					$webmention->status = 1;
					$webmention->save();
					
					// Log the error message if configured.
					if ((bool) elgg_get_plugin_setting('webmention_enable_debug', 'indieweb')) {
						$message = isset($parsed['error_description']) ? $parsed['error_description'] : 'Unknown parsing error';
						
						elgg_log($message . ' - Error processing webmention GUID: ' . $webmention->guid, 'error');
					}
				}
			}
			
			// Send push notification.
			if (!empty($valid_webmentions)) {
				if ((bool) elgg_get_plugin_setting('enable_microsub', 'indieweb')) {
					$microsub_client = elgg()->microsub;
					$microsub_client->sendPushNotification($valid_webmentions);
				}
			}
			
		// restore access
		});
		
		echo "Finished received webmentions processing" . PHP_EOL;
		elgg_log("Finished received webmentions processing", 'NOTICE');
	}
	
	public static function emptySyndications(\Elgg\Hook $hook) {
		if (!(bool) elgg_get_plugin_setting('enable_websub', 'indieweb')) {
		   return;
		}
		
		echo "Processes empty Syndications starting" . PHP_EOL;
		elgg_log("Processes empty Syndications starting", 'NOTICE');
		
		// ignore access
		elgg_call(ELGG_IGNORE_ACCESS, function() {
			$syndications = elgg_get_entities([
                'type' => 'object',
                'subtype' => Syndication::SUBTYPE,
                'limit' => false,
                'batch' => true,
                'batch_inc_offset' => false
            ]);
			
			if (empty($syndications)) {
				return true;
			}
			
			foreach ($syndications as $syndication) {
				$entity = get_entity($syndication->source_id);
				
				if (!$entity instanceof \ElggObject) {
					$syndication->delete();
				}
			}

		// restore access
		});
		
		echo "Finished empty Syndications processing" . PHP_EOL;
		elgg_log("Finished empty Syndications processing", 'NOTICE');
	}
	
	public static function emptyFailedWebmentions(\Elgg\Hook $hook) {
		if (!(bool) elgg_get_plugin_setting('enable_webmention', 'indieweb')) {
			return;
		}
		
		if (!(bool) elgg_get_plugin_setting('webmention_clean', 'indieweb')) {
			return;
		}
		
		echo "Processes empty failed Webmentions starting" . PHP_EOL;
		elgg_log("Processes empty failed Webmentions starting", 'NOTICE');
		
		// ignore access
		elgg_call(ELGG_IGNORE_ACCESS, function() {
			$webmentions = elgg_get_entities([
                'type' => 'object',
                'subtype' => Webmention::SUBTYPE,
                'limit' => false,
                'batch' => true,
                'batch_inc_offset' => false,
				'metadata_name_value_pairs' => [
					[
						'name' => 'property',
						'value' => ['send', 'received', 'rsvp', 'like-of', 'repost-of', 'in-reply-to', 'mention-of', 'bookmark-of', 'follow-of'],
						'operand' => '!=',
					],
				],
            ]);
			
			if (empty($webmentions)) {
				return true;
			}
			
			foreach ($webmentions as $webmention) {
				$webmention->delete();
			}

		// restore access
		});
		
		echo "Finished empty failed Webmentions processing" . PHP_EOL;
		elgg_log("Finished empty failed Webmentions processing", 'NOTICE');
	}
}
