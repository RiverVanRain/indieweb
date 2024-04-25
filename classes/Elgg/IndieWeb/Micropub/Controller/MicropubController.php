<?php
/**
 * IndieWeb
 * @author Nikolai Shcherbin
 * @license GNU Affero General Public License version 3
 * @copyright (c) Nikolai Shcherbin 2022
 * @link https://wzm.me
**/

namespace Elgg\IndieWeb\Micropub\Controller;

use Elgg\Exceptions\Http\EntityPermissionsException;
use Elgg\Exceptions\Http\BadRequestException;
use Elgg\Exceptions\Http\PageNotFoundException;
use Elgg\Database\Select;
use Symfony\Component\HttpFoundation\Response;
use p3k\XRay;
use Elgg\IndieWeb\Webmention\Client\MentionClient;

class MicropubController {
	
	/**
	* The object type.
	*/
	public $object_type;

	/**
	* The request input.
	*
	* @var array
	*/
	public $input;

	/**
	* An object URL to act on.
	*
	* @var null
	*/
	public $object_url;

	/**
	* The original payload
	*
	* @var null
	*/
	public $payload_original;

	/**
	* Location properties.
	*
	* @var array
	*/
	protected $location;
	
	/**
	 * Constructor
	 */
	public function __construct($object_type = null, $input = [], $object_url = null, $payload_original = null, $location = []) {
		$this->object_type = $object_type;
		$this->input = $input;
		$this->object_url = $object_url;
		$this->payload_original = $payload_original;
		$this->location = $location;
	}

	/**
	* Routing callback: Micropub post endpoint.
	*/
	public function __invoke(\Elgg\Request $request) {
		$indieAuth = elgg()->indieauth;
		
		// Early response when endpoint is not enabled.
		if (!(bool) elgg_get_plugin_setting('enable_micropub', 'indieweb')) {
			throw new PageNotFoundException();
		}
		
		elgg_set_http_header('Link: <' . elgg_generate_url('default:view:micropub') . '>; rel="micropub"');

		// Default response code and message.
		$response_code = 400;
		$response_message = [];

		// Micropub query.
		$micropub_query = $request->getParam('q');

		// q=syndicate-to request.
		if ($micropub_query === 'syndicate-to') {
			// Get authorization header, response early if none found.
			$auth_header = $indieAuth->getAuthorizationHeader($request->getHttpRequest());
			if (!$auth_header) {
				return elgg_error_response('Missing Authorization Header', REFERRER, 401);
			}

			if ($indieAuth->isValidToken($auth_header)) {
				$response_code = 200;
				$response_message = [
					'syndicate-to' => $this->getSyndicationTargets(),
				];
			} else {
				return elgg_error_response('syndicate-to: No Valid Token', REFERRER, 403);
			}
			
			return elgg_ok_response($response_message, '', REFERRER, $response_code);
		}

		// q=config request.
		if ($micropub_query === 'config') {
			// Get authorization header, response early if none found.
			$auth_header = $indieAuth->getAuthorizationHeader($request->getHttpRequest());
			if (!$auth_header) {
				return elgg_error_response('Missing Authorization Header', REFERRER, 401);
			}

			if ($indieAuth->isValidToken($auth_header)) {
				$response_code = 200;

				$supported_queries = ['config', 'syndicate-to'];
				$supported_properties = [];

				if ((bool) elgg_get_plugin_setting('micropub_enable_source', 'indieweb')) {
					$supported_queries[] = 'source';
				}

				if ((bool) elgg_get_plugin_setting('micropub_enable_category', 'indieweb')) {
					$supported_queries[] = 'category';
				}

				if ((bool) elgg_get_plugin_setting('micropub_enable_contact', 'indieweb')) {
					$supported_queries[] = 'contact';
				}

				$response_message = [
					'syndicate-to' => $this->getSyndicationTargets(),
					'q' => $supported_queries,
					'properties' => $supported_properties,
				];

				// Get post-types.
				$response_message['post-types'] = $this->getPostTypes();

				// Check media endpoint.
				if ((bool) elgg_get_plugin_setting('enable_micropub_media', 'indieweb')) {
					$response_message['media-endpoint'] = elgg_generate_url('view:micropub:media');
				}
			} else {
				return elgg_error_response('config: No Valid Token', REFERRER, 403);
			}

			return elgg_ok_response($response_message, '', REFERRER, $response_code);
		}

		// q=source request.
		if ($micropub_query === 'source') {
			// Early response when this is not enabled.
			if (!(bool) elgg_get_plugin_setting('micropub_enable_source', 'indieweb')) {
				throw new PageNotFoundException();
			}

			// Get authorization header, response early if none found.
			$auth_header = $indieAuth->getAuthorizationHeader($request->getHttpRequest());
			if (!$auth_header) {
				return elgg_error_response('Missing Authorization Header', REFERRER, 401);
			}

			if ($indieAuth->isValidToken($auth_header)) {
				$response_code = 200;
				$response_message = $this->getSourceResponse($request);
			} else {
				return elgg_error_response('source: No Valid Token', REFERRER, 403);
			}

			return elgg_ok_response($response_message, '', REFERRER, $response_code);
		}

		// q=contact request.
		if ($micropub_query === 'contact') {
			// Early response when this is not enabled.
			if (!(bool) elgg_get_plugin_setting('micropub_enable_contact', 'indieweb')) {
				throw new PageNotFoundException();
			}

			// Get authorization header, response early if none found.
			$auth_header = $indieAuth->getAuthorizationHeader($request->getHttpRequest());
			if (!$auth_header) {
				return elgg_error_response('Missing Authorization Header', REFERRER, 401);
			}

			if ($indieAuth->isValidToken($auth_header)) {
				$response_code = 200;
				$response_message = $this->getContactsResponse($request);
			} else {
				return elgg_error_response('contact: No Valid Token', REFERRER, 403);
			}

			return elgg_ok_response($response_message, '', REFERRER, $response_code);
		}
    
		// q=geo request.
		if ($micropub_query === 'geo') {
			// Early response when this is not enabled.
			if (!(bool) elgg_get_plugin_setting('micropub_enable_geo', 'indieweb')) {
				throw new PageNotFoundException();
			}

			// Get authorization header, response early if none found.
			$auth_header = $indieAuth->getAuthorizationHeader($request->getHttpRequest());
			if (!$auth_header) {
				return elgg_error_response('Missing Authorization Header', REFERRER, 401);
			}

			if ($indieAuth->isValidToken($auth_header)) {
				$response_code = 200;
				$response_message = $this->getGeoResponse($request);
			} else {
				return elgg_error_response('geo: No Valid Token', REFERRER, 403);
			}

			return elgg_ok_response($response_message, '', REFERRER, $response_code);
		}

		// q=category request.
		if ($micropub_query === 'category') {
			// Early response when this is not enabled.
			if (!(bool) elgg_get_plugin_setting('micropub_enable_category', 'indieweb')) {
				throw new PageNotFoundException();
			}

			// Get authorization header, response early if none found.
			$auth_header = $indieAuth->getAuthorizationHeader($request->getHttpRequest());
			if (!$auth_header) {
				return elgg_error_response('Missing Authorization Header', REFERRER, 401);
			}

			if ($indieAuth->isValidToken($auth_header)) {
				$response_code = 200;
				$response_message = ['categories' => $this->getCategories()];
			} else {
				return elgg_error_response('category: No Valid Token', REFERRER, 403);
			}

			return elgg_ok_response($response_message, '', REFERRER, $response_code);
		}

		// Indigenous sends POST vars along with multipart, we use all().
		if (strpos($request->getHttpRequest()->headers->get('Content-Type') ?: '', 'multipart/form-data') !== false) {
			$input = $request->getHttpRequest()->request->all();
		} else {
			$input = $request->getHttpRequest()->getContent();
		}
		
		// Determine action and input from request. This can either be POST or JSON request. We use p3k/Micropub to handle that part.
		$micropub_request = \p3k\Micropub\Request::create($input);
		
		$action = '';
		
		if ($micropub_request instanceof \p3k\Micropub\Request && $micropub_request->action) {
			$action = $micropub_request->action;

			if ($action === 'update') {
				$this->input = $micropub_request->update;
				$this->object_url = $micropub_request->url;
			} else if ($action === 'delete') {
				$this->object_url = $micropub_request->url;
			} else {
				$mf2 = $micropub_request->toMf2();
				$this->object_type = !empty($mf2['type'][0]) ? $mf2['type'][0] : '';
				$this->input = $mf2['properties'];
				$this->input += $micropub_request->commands;
			}
		} else {
			$description = $micropub_request->error_description ?: 'Unknown error';
			elgg_log('Error parsing incoming request: ' . $description . ' - ' . print_r($input, 1), 'error');
			throw new BadRequestException();
		}

		// Attempt to delete a post, comment or webmention.
		if ($action === 'delete' && (bool) elgg_get_plugin_setting('micropub_enable_delete', 'indieweb') && !empty($this->object_url)) {
			// Get authorization header, response early if none found.
			$auth_header = $indieAuth->getAuthorizationHeader($request->getHttpRequest());
			if (!$auth_header) {
				return elgg_error_response('Missing Authorization Header', REFERRER, 401);
			}

			// Validate token. Return early if it's not valid.
			$valid_token = $indieAuth->isValidToken($auth_header, 'delete');
			if (!$valid_token) {
				return elgg_error_response('delete: No Valid Token', REFERRER, 403);
			}

			$response_message = '';
			$response_code = 400;

			$guid = (int) indieweb_get_guid($this->object_url);
			
			try {
				if ($guid > 0) {
					$entity = get_entity($guid);
					if ($entity instanceof \ElggObject) {
						$response_message = '';
						$response_code = 200;
						$entity->delete();
					}
				}
			} catch (\Exception $e) {
				elgg_log('Error in deleting post: ' . $e->getMessage(), 'error');
			}

			return elgg_ok_response($response_message, '', REFERRER, $response_code);
		}

		// Attempt to update a post
		if ($action === 'update' && (bool) elgg_get_plugin_setting('micropub_enable_update', 'indieweb') && !empty($this->object_url) && !empty($this->input['replace'])) {
			// Get authorization header, response early if none found.
			$auth_header = $indieAuth->getAuthorizationHeader($request->getHttpRequest());
			if (!$auth_header) {
				return elgg_error_response('Missing Authorization Header', REFERRER, 401);
			}

			// Validate token. Return early if it's not valid.
			$valid_token = $indieAuth->isValidToken($auth_header, 'update');
			if (!$valid_token) {
				return elgg_error_response('update: No Valid Token', REFERRER, 403);
			}

			$guid = (int) indieweb_get_guid($this->object_url);
			
			try {
				if ($guid > 0) {
					$entity = get_entity($guid);
					
					if ($entity instanceof \ElggObject) {
						$update = false;

						// Post status.
						if (!empty($this->input['replace']['post-status'][0])) {
							$status = null;
							if ($this->input['replace']['post-status'][0] === 'draft') {
								$status = false;
							}
							if ($this->input['replace']['post-status'][0] === 'published') {
								$status = true;
							}

							if (isset($status)) {
								$update = true;
								if (elgg_is_active_plugin('theme')) {
									$status ? $entity->setMetadata('published_status', 'published') : $entity->setMetadata('published_status', 'draft');
								} else {
									$status ? $entity->setMetadata('status', 'published') : $entity->setMetadata('status', 'draft');
								}
							}
						}

						// Title.
						if (!empty($this->input['replace']['name'][0])) {
							$update = true;
							
							if (!empty($entity->title)) {
								$entity->setDisplayName($this->input['replace']['name'][0]);
							}
						}

						// Body.
						if (!empty($this->input['replace']['content'][0])) {
							$update = true;
							$entity->setMetadata('description', $this->input['replace']['content'][0]);
						}

						// Indieweb contact.
						if ($entity instanceof \Elgg\IndieWeb\Contacts\Entity\Contact) {
							foreach (['nickname', 'url', 'photo'] as $k) {
								if (!empty($this->input['replace'][$k][0])) {
									$update = true;
									$entity->setMetadata($k, $this->input['replace'][$k][0]);
								}
							}
						}

						if ($update) {
							$entity->save();
						}

						$response_message = '';
						$response_code = 200;
					}
				}
			} catch (\Exception $e) {
				elgg_log('Error in updating object: ' . $e->getMessage(), 'error');
			}

			return elgg_ok_response($response_message, '', REFERRER, $response_code);
		}

		// Attempt to create a post
		if (!empty($this->input) && $action === 'create') {
			// Get authorization header, response early if none found.
			$auth_header = $indieAuth->getAuthorizationHeader($request->getHttpRequest());
			if (!$auth_header) {
				return elgg_error_response('Missing Authorization Header', REFERRER, 401);
			}

			// Validate token. Return early if it's not valid.
			$valid_token = $indieAuth->isValidToken($auth_header, 'create');
			
			if (!$valid_token) {
				return elgg_error_response('create: No Valid Token', REFERRER, 403);
			}

			// Store original input so it can be inspected by hooks.
			$this->payload_original = $this->input;

			// Log payload.
			if ((bool) elgg_get_plugin_setting('micropub_log_payload', 'indieweb')) {
				elgg_log('New entity - type:' . $this->object_type . ', input: ' . print_r($this->input, 1), 'NOTICE');
			}

			// Check if we have a location or checkin property in the payload.
			$checkin = false;
			
			if (!empty($this->input['location'][0]) || !empty($this->input['checkin'][0])) {
				$geouri = '';
				if (!empty($this->input['location'][0])) {
					$geouri = $this->input['location'][0];
				}

				if (!empty($this->input['checkin'][0])) {
					$checkin = true;
					$geouri = $this->input['checkin'][0];
				}

				// Only go if this is a string.
				if (is_string($geouri)) {
					// Latitude and longitude.
					$geo = explode(':', $geouri);

					if (!empty($geo[0]) && $geo[0] === 'geo' && !empty($geo[1])) {
						$lat_lon = explode(',', $geo[1]);
						
						if (!empty((float) $lat_lon[0]) && !empty((float) $lat_lon[1])) {
							$lat = trim($lat_lon[0]);
							$lon = trim($lat_lon[1]);
							if (!empty($lat) && !empty($lon)) {
								$this->location['lat'] = $lat;
								$this->location['lon'] = $lon;
							}
						}

						// Additional parameters.
						$additional_params = explode(';', $geouri);
						
						foreach ($additional_params as $additional_param) {
							$ex = explode('=', $additional_param);
							if (!empty($ex[0]) && !empty($ex[1])) {
								$this->location[$ex[0]] = $ex[1];
							}
						}
					}
				}
			}

			// The order here is of importance. Don't change it, unless there's a good reason for, see https://indieweb.org/post-type-discovery. 
			// This does not follow the exact rules, because we can be more flexible in Elgg.

			// HCard.
			if ($this->isHCard() && (bool) elgg_get_plugin_setting('micropub_enable_contact', 'indieweb') && $this->hasRequiredInput(['name'])) {
				$contact = [];
				foreach (['name', 'nickname', 'url', 'photo'] as $key) {
					if (!empty($this->input[$key][0])) {
						$contact[$key] = $this->input[$key][0];
					}
				}
			
				if (!empty($contact)) {
					// Set default uid.
					$contact['uid'] = 1;

					// Override uid.
					if ($tokenOwnerId = $indieAuth->checkAuthor()) {
						$contact['uid'] = $tokenOwnerId;
					}

					$entityContact = elgg()->{'indieweb.contact'}->storeContact($contact);

					return elgg_ok_response();
				}
			}

			// Checkin support.
			if ($checkin && $this->createEntityFromPostType('checkin') && $this->hasRequiredInput(['checkin']) && $this->isHEntry()) {
				$checkin_title = elgg_echo('indieweb:micropub:view:checkin');
				if (!empty($this->location['name'])) {
					$checkin_title = elgg_echo('indieweb:micropub:checkin:title', [$this->location['name']]);
				}

				$entity = $this->createEntity($checkin_title, 'checkin');
				return $this->saveEntity($entity);
			}

			// Event support.
			if ($this->createEntityFromPostType('event') && $this->isHEvent() && $this->hasRequiredInput(['start', 'end', 'name'])) {
				$entity = $this->createEntity($this->input['name'], 'event');

				// Date.
				if ((bool) elgg_get_plugin_setting('micropub_field_date_event', 'indieweb')) {
					$entity->setMetadata('event_start', strtotime($this->input['start'][0]));
					$entity->setMetadata('event_end', strtotime($this->input['end'][0]));
				}

				return $this->saveEntity($entity);
			}

			// RSVP support.
			if ($this->createEntityFromPostType('rsvp') && $this->isHEntry() && $this->hasRequiredInput(['in-reply-to', 'rsvp'])) {
				$entity = $this->createEntity(elgg_echo('indieweb:micropub:rsvp:title', [$this->input['in-reply-to'][0]]), 'rsvp', 'in-reply-to');

				// RSVP field
				if ((bool) elgg_get_plugin_setting('enable_micropub_rsvp', 'indieweb')) {
					$entity->setMetadata('rsvp', $this->input['rsvp']);
				}

				return $this->saveEntity($entity);
			}

			// Repost support.
			if ($this->createEntityFromPostType('repost') && $this->isHEntry() && $this->hasRequiredInput(['repost-of'])) {
				$entity = $this->createEntity(elgg_echo('indieweb:micropub:repost:title', [$this->input['repost-of'][0]]), 'repost', 'repost-of');
				return $this->saveEntity($entity);
			}

			// Bookmark support.
			if ($this->createEntityFromPostType('bookmark') && $this->isHEntry() && $this->hasRequiredInput(['bookmark-of'])) {
				$entity = $this->createEntity(elgg_echo('indieweb:micropub:bookmark:title', [$this->input['bookmark-of'][0]]), 'bookmark', 'bookmark-of');
				return $this->saveEntity($entity);
			}

			// Like support.
			if ($this->createEntityFromPostType('like') && $this->isHEntry() && $this->hasRequiredInput(['like-of'])) {
				// This can be a like on a webmention, usually a reply or mention. Get the url of the webmention and replace the like-of value.
				$like = $this->input['like-of'][0];
				
				$guid = (int) indieweb_get_guid($like);
				
				try {
					if ($guid > 0) {
						$target = get_entity($guid);
						
						if (elgg_is_active_plugin('likes') && $target instanceof \ElggObject && $target->hasCapability('likable')) {
							$this->input['like-of'][0] = $target->getURL();
						}
						
						return $this->createLike($target, 'like', 'like-of');
					}
				} catch (\Exception $ignored) {}
			}

			// Issue support.
			if ($this->createEntityFromPostType('issue') && $this->isHEntry() && $this->hasRequiredInput(['content', 'name', 'in-reply-to']) && $this->hasNoKeysSet(['bookmark-of', 'repost-of', 'like-of'])) {
				$entity = $this->createEntity($this->input['name'], 'issue');
				return $this->saveEntity($entity);
			}

			// Reply support.
			if ($this->createEntityFromPostType('reply') && $this->isHEntry() && $this->hasRequiredInput(['in-reply-to', 'content']) && $this->hasNoKeysSet(['rsvp'])) {
				// Check if we should create a comment.
				if ((bool) elgg_get_plugin_setting('micropub_reply_create_comment', 'indieweb') && (bool) elgg_get_plugin_setting('webmention_enable_comment_create', 'indieweb')) {
					$container_guid = 0;
					$link_field_url = '';
					$reply = $this->input['in-reply-to'][0];
					
					$guid = (int) indieweb_get_guid($reply);

					try {
						if ($guid > 0) {
								$target = get_entity($guid);
								
								try {
									// This can be a reply on a comment, or set via a webmention. 
									// Get the post to attach the comment there and set container_guid.
									if ($target instanceof \Elgg\IndieWeb\Webmention\Entity\Webmention && $target->getProperty() === 'in-reply-to') {
										$container_guid = $target->guid;
										$link_field_url = $target->getURL();
									} else if ($target instanceof \ElggObject && $target->hasCapability('commentable')) {
										$container_guid = $target->guid;
										$link_field_url = $target->getURL();
									}
								} catch (\Exception $ignored) {}
								
								elgg_call(ELGG_IGNORE_ACCESS, function () use ($target, $container_guid, $link_field_url) {
									$indieAuth = elgg()->indieauth;
			
									// Get user guid.
									if (elgg_is_active_plugin('theme')) {
										$owner_guid = (int) elgg_get_plugin_setting('micropub_author_reply', 'indieweb');
									} else {
										$username = elgg_get_plugin_setting('micropub_author_reply', 'indieweb');
										$owner_guid = elgg_get_user_by_username($username)->guid;
									}
									
									// Override user guid.
									if ($tokenOwnerId = $indieAuth->checkAuthor()) {
										$owner_guid = $tokenOwnerId;
									}
									
									$session = _elgg_services()->session_manager;
									$owner = get_entity($owner_guid);
									$session->setLoggedInUser($owner);
									
									// Create comment.
									$comment = new \ElggComment();
									
									$description = $this->input['content'][0];
									if (is_array($description)) {
										$description = implode(' ', $description);
									}
									
									$comment->description = htmlspecialchars($description, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
			
									if ($target instanceof \ElggComment) {
										$comment->level = $target->getLevel() + 1;
										$comment->parent_guid = $target->guid;
										$comment->thread_guid = $target->getThreadGUID();
										
										// make sure comment is contained in the content
										$target = $target->getContainerEntity();
									}

									$comment->owner_guid = $owner_guid;
									$comment->container_guid = $container_guid;
									$comment->access_id = $target->access_id;
										
									if (!$comment->save()) {
										return elgg_error_response(elgg_echo('generic_comment:failure'));
									}
									
									// Check if there's an image field.
									$this->handleUploads($comment, 'reply', $owner_guid);
									/*
									if (elgg_is_active_plugin('theme')) {
										$files = $this->saveUpload('upload', 'reply');
										$this->handleUploads($entity, $post_type, $owner_guid);
										if ($files && $comment->hasCapability('allow_attachments')) {
											foreach ($files as $file) {
												apps_attach($comment, $file);
											}
										}
									}
									*/

									// Check link field.
									if (!empty($link_field_url) && (bool) elgg_get_plugin_setting('micropub_send_webmention_reply', 'indieweb')) {
										// Syndicate.
										if (isset($this->input['mp-syndicate-to'])) {
											$this->input['mp-syndicate-to'][] = $link_field_url;
										} else {
											$this->input['mp-syndicate-to'] = [$link_field_url];
										}
									}

									return $this->saveComment($comment);
								});
						}
					} catch (\Exception $e) {
						elgg_log('Error trying to create a comment from reply: ' . $e->getMessage(), 'error');
					}
				} else {
					// We got here, so it's a standard post
					$entity = $this->createEntity(elgg_echo('indieweb:micropub:reply:title', [$this->input['in-reply-to'][0]]), 'reply', 'in-reply-to');
					return $this->saveEntity($entity);
				}
			}

			// Note post type.
			if ($this->createEntityFromPostType('note') && $this->isHEntry() && $this->hasRequiredInput(['content']) && $this->hasNoKeysSet(['name', 'in-reply-to', 'bookmark-of', 'repost-of', 'like-of'])) {
				$entity = $this->createEntity(elgg_echo('indieweb:micropub:view:note'), 'note');
				return $this->saveEntity($entity);
			}

			// Article post type.
			if ($this->createEntityFromPostType('article') && $this->isHEntry() && $this->hasRequiredInput(['content', 'name']) && $this->hasNoKeysSet(['in-reply-to', 'bookmark-of', 'repost-of', 'like-of'])) {
				$entity = $this->createEntity($this->input['name'], 'article');
				return $this->saveEntity($entity);
			}
		}
		
		return elgg_ok_response($response_message, '', REFERRER, $response_code);
	}

	/**
	* Upload files through the media endpoint.
	*
	*/
	public static function mediaEndpoint(\Elgg\Request $request) {
		$indieAuth = elgg()->indieauth;
		
		// Early response when endpoint is not enabled.
		if (!(bool) elgg_get_plugin_setting('enable_micropub_media', 'indieweb')) {
			throw new PageNotFoundException();
		}
		
		// Default message.
		$response_message = '';

		// Get authorization header, response early if none found.
		$auth_header = $indieAuth->getAuthorizationHeader($request->getHttpRequest());
		if (!$auth_header) {
			return elgg_error_response('Missing Authorization Header', REFERRER, 401);
		}

		if ($indieAuth->isValidToken($auth_header, 'media') && in_array($request->getMethod(), ['GET', 'POST'])) {
			$user_guid = 1;
			// Override user guid if using internal indieauth.
			if ($tokenOwnerId = $indieAuth->checkAuthor()) {
				$user_guid = $tokenOwnerId;
			}

			// Last uploaded file.
			if ($request->getMethod() === 'GET' && $request->getParam('q') === 'last') {
				$response_code = 200;

				$files = elgg_get_entities([
					'type' => 'object',
					'subtype' => 'file',
					'owner_guid' => $user_guid,
					'limit' => 1,
					'metadata_name_value_pairs' => [
						[
							'name' => 'simpletype',
							'value' => ['audio', 'image', 'video'],
						],
					],
				]);

				if ($files) {
					$file = array_pop($files);
					$response_message = ['url' => $file->getURL()];
				}
			} else {
				// Upload file.
				$files = $this->saveUpload('upload');
				
				if (!empty($files)) {
					foreach ($files as $file) {
						elgg_call(ELGG_IGNORE_ACCESS, function () use ($file) {
							// Set owner
							$file->owner_guid = $user_guid;
							$file->save();
						});
					}
					
					return elgg_ok_response();
				}
			}
		} else {
			return elgg_error_response('upload: No Valid Token', REFERRER, 403);
		}

		return elgg_ok_response($response_message, '', REFERRER, $response_code);
	}

	/**
	* Returns whether the input type is a h-entry.
	*
	* @return bool
	*/
	protected function isHEntry() {
		return isset($this->object_type) && $this->object_type === 'h-entry';
	}

	/**
	* Returns whether the input type is a h-card.
	*
	* @return bool
	*/
	protected function isHCard() {
		return isset($this->object_type) && $this->object_type === 'h-card';
	}

	/**
	* Returns whether the input type is a h-event.
	*
	* @return bool
	*/
	protected function isHEvent() {
		return isset($this->object_type) && $this->object_type === 'h-event';
	}

	/**
	* Checks if required values are in input.
	*
	* @param array $keys
	*
	* @return bool
	*/
	protected function hasRequiredInput($keys = []) {
		$has_required_values = true;

		foreach ($keys as $key) {
			if (empty($this->input[$key])) {
				$has_required_values = false;
				break;
			}
		}

		return $has_required_values;
	}

	/**
	* Check that none of the keys are set in input.
	*
	* @param $keys
	*
	* @return bool
	*/
	protected function hasNoKeysSet($keys) {
		$has_no_keys_set = true;

		foreach ($keys as $key) {
			if (isset($this->input[$key])) {
				$has_no_keys_set = false;
				break;
			}
		}

		return $has_no_keys_set;
	}

	/**
	* Whether creating an entity for this post type is enabled.
	*
	* @param $post_type
	*
	* @return bool
	*/
	protected function createEntityFromPostType($post_type): bool {
		return elgg_get_plugin_setting('enable_micropub_' . $post_type, 'indieweb');
	}

	/**
	* Create an entity.
	*
	* @param $title
	*   The title for the entity.
	* @param $post_type
	*   The IndieWeb post type.
	* @param $link_input_name
	*   The name of the property in input for auto syndication.
	*
	*/
	protected function createEntity($title, $post_type, $link_input_name = null) {
		return elgg_call(ELGG_IGNORE_ACCESS, function () use ($title, $post_type, $link_input_name) {
			$indieAuth = elgg()->indieauth;
			
			// Get user guid.
			if (elgg_is_active_plugin('theme')) {
				$owner_guid = (int) elgg_get_plugin_setting('micropub_author_' . $post_type, 'indieweb');
			} else {
				$username = elgg_get_plugin_setting('micropub_author_' . $post_type, 'indieweb');
				$owner_guid = elgg_get_user_by_username($username)->guid;
			}
			
			$subtype = elgg_get_plugin_setting('micropub_type_' . $post_type, 'indieweb');

			$status = 'draft';
			if ((bool) elgg_get_plugin_setting('micropub_status_' . $post_type, 'indieweb')) {
				$status = 'published';
			}
			
			// Override user guid.
			if ($tokenOwnerId = $indieAuth->checkAuthor()) {
				$owner_guid = $tokenOwnerId;
			}
			
			$session = _elgg_services()->session_manager;
			$owner = get_entity($owner_guid);
			$session->setLoggedInUser($owner);
			
			$time_created = time();

			// Published date.
			if (!empty($this->input['published'][0])) {
				$time_created = strtotime($this->input['published'][0]);
			}

			// Check post-status.
			if (!empty($this->input['post-status'][0])) {
				if ($this->input['post-status'][0] === 'published') {
					$status = 'published';
				}
				if ($this->input['post-status'][0] === 'draft') {
					$status = 'draft';
				}
			}

			// Add link to syndicate to.
			if ($link_input_name && (bool) elgg_get_plugin_setting('micropub_send_webmention_' . $post_type, 'indieweb')) {
				if (isset($this->input['mp-syndicate-to'])) {
					$this->input['mp-syndicate-to'][] = $this->input[$link_input_name][0];
				} else {
					$this->input['mp-syndicate-to'] = $this->input[$link_input_name];
				}
			}

			// Create post
			$entity = new \ElggObject;
			$entity->setSubtype($subtype);
			$entity->owner_guid = $owner_guid;
			$entity->time_created = $time_created;
			$entity->access_id = ACCESS_PUBLIC;
			
			if ($post_type === 'repost' && (bool) elgg_get_plugin_setting('micropub_field_content_repost', 'indieweb')) {
				$entity->title = elgg_echo('indieweb:micropub:view:repost');
				$entity->description = $title;
			} else {
				$entity->title = $title;
			}
			
			if (elgg_is_active_plugin('theme')) {
				$entity->published_status = $status;
			} else {
				$entity->status = $status;
			}

			// Content.
			if (!empty($this->input['content'][0]) && (bool) elgg_get_plugin_setting('micropub_field_content_' . $post_type, 'indieweb')) {
				$entity->description = $this->input['content'][0];
				/*
				if (is_array($description)) {
					$description = implode(' ', $description);
				}
				$entity->description = htmlspecialchars($description, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
				*/
			}

			// Link.
			if ((bool) elgg_get_plugin_setting('micropub_field_link_' . $post_type, 'indieweb')) {
				$uri = '';
				
				if (!empty($this->location['url'])) {
					$uri = $this->location['url'];
				} else if (!empty($this->input[$link_input_name][0])) {
					$uri = $this->input[$link_input_name][0];
				}

				if ($uri) {
					$entity->website = $uri;
				}
			}
			
			if (!$entity->save()) {
				elgg_log('Error creating entity from post', 'ERROR');
				return elgg_error_response('Error creating entity from post', REFERRER, 403);
			}

			// Uploads.
			if ((bool) elgg_get_plugin_setting('micropub_field_upload_' . $post_type, 'indieweb')) {
				$this->handleUploads($entity, $post_type, $owner_guid);
			}

			// Categories.
			if ((bool) elgg_get_plugin_setting('micropub_field_tags_' . $post_type, 'indieweb')) { 
				$this->handleCategories($entity, $post_type);
			}

			// Geo location.
			if ((bool) elgg_get_plugin_setting('micropub_field_location_' . $post_type, 'indieweb')) { 
				$this->handleGeoLocation($entity, $post_type);
			}
			
			return $entity;
		});
	}

	/**
	* Saves the post.
	*
	* @return \Symfony\Component\HttpFoundation\Response
	*
	*/
	protected function saveEntity(\ElggObject $entity) {
		return elgg_call(ELGG_IGNORE_ACCESS, function () use ($entity) {
			// Syndicate.
			if ((bool) elgg_get_plugin_setting('enable_webmention', 'indieweb')) {
				$this->syndicateToPost($entity);
			}

			// WebSub.
			if ((bool) elgg_get_plugin_setting('enable_websub', 'indieweb') && (bool) elgg_get_plugin_setting('websub_micropub_publish', 'indieweb')) {
				$this->publishToHub($entity);
			}

			// Allow plugins to react after the post is saved.
			elgg_trigger_event_results('indieweb_micropub_post_saved', $entity->subtype, $this->input, []);

			return elgg_ok_response();
		});
	}

	/**
	* Saves the comment.
	*
	* @return \Symfony\Component\HttpFoundation\Response
	*
	*/
	protected function saveComment(\ElggComment $comment) {
		return elgg_call(ELGG_IGNORE_ACCESS, function () use ($comment) {
			// only river for top level comments
			if ($comment->getLevel() === 1) {
				// Add to river
				elgg_create_river_item([
					'view' => 'river/object/comment/create',
					'action_type' => 'comment',
					'object_guid' => $comment->guid,
					'target_guid' => $comment->container_guid,
				]);
			}
				
			// Syndicate.
			if ((bool) elgg_get_plugin_setting('enable_webmention', 'indieweb')) {
				$this->syndicateToComment($comment);
			}
				
			return elgg_ok_response();
		});
	}
	
	/**
	* Create a like.
	*
	* @param $entity
	*   \ElggObject
	* @param $post_type
	*   The IndieWeb post type.
	* @param $link_input_name
	*   The name of the property in input for auto syndication.
	*
	*/
	protected function createLike(\ElggObject $entity, $post_type, $link_input_name = null) {
		return elgg_call(ELGG_IGNORE_ACCESS, function () use ($entity, $post_type, $link_input_name) {
			$indieAuth = elgg()->indieauth;
			
			// Get user guid.
			if (elgg_is_active_plugin('theme')) {
				$owner_guid = (int) elgg_get_plugin_setting('micropub_author_' . $post_type, 'indieweb');
			} else {
				$username = elgg_get_plugin_setting('micropub_author_' . $post_type, 'indieweb');
				$owner_guid = elgg_get_user_by_username($username)->guid;
			}
			
			// Override user guid.
			if ($tokenOwnerId = $indieAuth->checkAuthor()) {
				$owner_guid = $tokenOwnerId;
			}
			
			$session = _elgg_services()->session_manager;
			$owner = get_entity($owner_guid);
			$session->setLoggedInUser($owner);

			// Add link to syndicate to.
			if ($link_input_name && (bool) elgg_get_plugin_setting('micropub_send_webmention_' . $post_type, 'indieweb')) {
				if (isset($this->input['mp-syndicate-to'])) {
					$this->input['mp-syndicate-to'][] = $this->input[$link_input_name][0];
				} else {
					$this->input['mp-syndicate-to'] = $this->input[$link_input_name];
				}
			}
			
			if (!$entity->canAnnotate($owner_guid, 'likes')) {
				elgg_log('Error creating annotation from likes', 'ERROR');
				return elgg_error_response('Error creating annotation from likes', REFERRER, 403);
			}
			
			$annotation_id = $entity->annotate('likes', 'likes', ACCESS_PUBLIC);

			// tell user annotation didn't work if that is the case
			if (!$annotation_id) {
				elgg_log(elgg_echo('likes:failure'), 'ERROR');
				return elgg_error_response(elgg_echo('likes:failure'), REFERRER, 403);
			}
			
			if ($entity->owner_guid === $owner_guid) {
				return elgg_ok_response();
			}
			
			$owner = $entity->getOwnerEntity();

			$annotation = elgg_get_annotation_from_id($annotation_id);

			$title_str = $entity->getDisplayName();
			if (!$title_str) {
				$title_str = elgg_get_excerpt($entity->description, 80);
			}
			
			$user = get_entity($owner_guid);

			$site = elgg_get_site_entity();

			// summary for site_notifications
			$summary = elgg_echo('likes:notifications:subject', [
					$user->getDisplayName(),
					$title_str,
				],
				$owner->language
			);

			// prevent long subjects in mail
			$title_str = elgg_get_excerpt($title_str, 80);
			$subject = elgg_echo('likes:notifications:subject', [
					$user->getDisplayName(),
					$title_str,
				],
				$owner->language
			);

			$body = elgg_echo('likes:notifications:body', [
					$user->getDisplayName(),
					$title_str,
					$site->getDisplayName(),
					$entity->getURL(),
					$user->getURL(),
				],
				$owner->language
			);

			notify_user(
				$entity->owner_guid,
				$user->guid,
				$subject,
				$body,
				[
					'action' => 'create',
					'object' => $annotation,
					'summary' => $summary,
					'url' => $entity->getURL(),
				]
			);

			return elgg_ok_response();
		});
	}

	/**
	* Helper function to upload file(s).
	*
	* @param $file_key
	*   The key in the $_FILES variable to look for in upload.
	* @param $post_type
	*   Micropub port type
	* @param $limit
	*   Limit number of uploads, 10 files maximum
	*
	* @return array $files
	*
	*/
	protected function saveUpload($file_key, $post_type = '', int $limit = 1) {
		return elgg_call(ELGG_IGNORE_ACCESS, function () use ($file_key, $post_type, $limit) {
			$files = [];
			
			$file_bag = _elgg_services()->request->files;

			$uploaded_files = $file_bag->get($file_key);
			if (!$uploaded_files) {
				return [];
			}
			if (!is_array($uploaded_files)) {
				$uploaded_files = [$uploaded_files];
			}
			
			$owner_guid = elgg_get_site_entity()->guid;
			
			$indieAuth = elgg()->indieauth;
			
			// Get user guid.
			if (!empty($post_type) && elgg_is_active_plugin('theme')) {
				$owner_guid = (int) elgg_get_plugin_setting('micropub_author_' . $post_type, 'indieweb');
			} else if (!empty($post_type)) {
				$username = elgg_get_plugin_setting('micropub_author_' . $post_type, 'indieweb');
				$owner_guid = elgg_get_user_by_username($username)->guid;
			}
			
			// Override user guid.
			if ($tokenOwnerId = $indieAuth->checkAuthor()) {
				$owner_guid = $tokenOwnerId;
			}
			
			$session = _elgg_services()->session_manager;
			$owner = get_entity($owner_guid);
			
			if ($owner instanceof \ElggUser) {
				$session->setLoggedInUser($owner);
			}
			
			foreach ($uploaded_files as $upload) {
				if (!$upload->isValid()) {
					continue;
				}
				
				if ($limit && $upload > $limit) {
					continue;
				}
				
				$file = new \ElggFile;
				/* @var $file ElggFile */
					
				$originalfilename = $upload->getClientOriginalName();
				$file->originalfilename = $originalfilename;
				$file->title = htmlspecialchars($file->originalfilename, ENT_QUOTES, 'UTF-8');
				$file->upload_time = time();
				$file->owner_guid = $owner_guid;
				$file->access_id = ACCESS_PUBLIC;
				$file->save();
				
				// remove old icons
				$sizes = elgg_get_icon_sizes($file->getType(), $file->getSubtype());
				$master_location = null;
				foreach ($sizes as $size => $props) {
					$icon = $file->getIcon($size);
					if ($size === 'master') {
						// needs to be kept in case upload fails
						$master_location = $icon->getFilenameOnFilestore();
						continue;
					}
					
					$icon->delete();
				}
				
				if (!$file->acceptUploadedFile($upload)) {
					$file->delete();
					continue;
				}
				
				if (!$file->save() || !$file->exists()) {
					$file->delete();
					continue;
				}
				
				if (!in_array($file->getSimpleType(), ['audio', 'image', 'video'])) {
					$file->delete();
					continue;
				}
				
				// update icons
				if ($file->getSimpleType() === 'image') {
					$file->saveIconFromElggFile($file);
				}
					
				$files[] = $file;
			}
			
			return $files;
		});
	}

	/**
	* Handle uploads.
	*
	* @param string $post_type
	*
	* @param int $owner_guid
	*
	*/
	protected function handleUploads(\ElggObject $entity, $post_type, int $owner_guid = 1) {
		foreach (['photo', 'audio', 'video'] as $upload_key) {
			$limit = (int) elgg_get_plugin_setting('micropub_field_upload_limit_' . $post_type, 'indieweb', 1);
	
			$files = $this->saveUpload($upload_key, $post_type, $limit);
			
			if ($files) {
				elgg_call(ELGG_IGNORE_ACCESS, function () use ($entity, $owner_guid, &$files) {
					foreach ($files as $file) {
						$file->owner_guid = $owner_guid;
						$file->container_guid = $entity->guid;
						if ($file->save()) {
							$entity->addRelationship($file->guid, 'attached');
						}
					}
				});
			}
		}
	}

	/**
	* Handle and set categories.
	*
	* @param string $post_type
	*
	*/
	protected function handleCategories(\ElggObject $entity, $post_type) {
		if (!empty($this->input['category'])) {
			$values = [];
			
			foreach ($this->input['category'] as $category) {
				$values[] = $category;
			}

			if (!empty($values)) {
				$entity->tags = $values;
			}
		}
	}

	/**
	* Handles geo location input.
	*
	* @param string $post_type
	*
	*/
	protected function handleGeoLocation(\ElggObject $entity, $post_type) {
		if (!empty($this->location['lat']) && !empty($this->location['lon'])) {
			try {
				$entity->setLatLong($this->location['lat'], $this->location['lon']);
			} catch (\Exception $e) {
				elgg_log('Error saving geo location: ' . $e->getMessage(), 'error');
			}
		}
	}

	/**
	* Syndicate to for posts.
	*
	*/
	protected function syndicateToPost(\ElggObject $entity) {
		if (!empty($this->input['mp-syndicate-to']) && ($entity->published_status === 'published' || $entity->status === 'published')) {
			$client = new MentionClient();
		
			if (is_string(elgg_get_plugin_setting('webmention_proxy', 'indieweb'))) {
				$client->setProxy(elgg_get_plugin_setting('webmention_proxy', 'indieweb'));
			}
			
			if (is_string(elgg_get_plugin_setting('webmention_user_agent', 'indieweb'))) {
				$client->setUserAgent(elgg_get_plugin_setting('webmention_user_agent', 'indieweb'));
			}
			
			if ((bool) elgg_get_plugin_setting('webmention_enable_debug', 'indieweb')) {
				$client->enableDebug();
			}
			
			$source = $entity->getURL();
			$guid = $entity->guid;
			
			foreach ($this->input['mp-syndicate-to'] as $target) {
				if (parse_url($source, PHP_URL_HOST) === parse_url($target, PHP_URL_HOST)) {
					continue;
				}
				
				$client->sendWebmention($source, $target);
				
				elgg_call(ELGG_IGNORE_ACCESS, function () use ($guid, $source) {
					$syndication = new \Elgg\IndieWeb\Webmention\Entity\Syndication();
					$syndication->owner_guid = elgg_get_site_entity()->guid;
					$syndication->container_guid = elgg_get_site_entity()->guid;
					$syndication->access_id = ACCESS_PRIVATE;
					$syndication->source_id = $guid;
					$syndication->source_url = $source;
					$syndication->save();
				});
				
				elgg_call(ELGG_IGNORE_ACCESS, function () use ($source, $target) {
					$webmention = new \Elgg\IndieWeb\Webmention\Entity\Webmention();
					$webmention->owner_guid = elgg_get_site_entity()->guid;
					$webmention->container_guid = elgg_get_site_entity()->guid;
					$webmention->access_id = ACCESS_PRIVATE;
					$webmention->source = $source;
					$webmention->target = $target;
					$webmention->property = 'send';
					$webmention->published = 0;
					$webmention->status = 0;
					$webmention->save();
							
					elgg_log(elgg_echo('webmention:send:success', [$webmention->guid]), 'NOTICE');
				});
			}
		}
	}

	/**
	* Publish to hub for WebSub.
	*/
	protected function publishToHub(\ElggObject $entity) {
		if ($entity->published_status === 'published' || $entity->status === 'published') {
			elgg_call(ELGG_IGNORE_ACCESS, function () use ($entity) {
				$websubpub = new \Elgg\IndieWeb\WebSub\Entity\WebSubPub();
				$websubpub->owner_guid = elgg_get_site_entity()->guid;
				$websubpub->container_guid = elgg_get_site_entity()->guid;
				$websubpub->access_id = ACCESS_PRIVATE;
				$websubpub->entity_id = $entity->guid;
				$websubpub->entity_type_id = $entity->subtype;
				$websubpub->published = false;
				$websubpub->save();
				
				if ((bool) elgg_get_plugin_setting('websub_log_payload', 'indieweb')) {
					elgg_log(elgg_echo('websub:send:success', [$websubpub->guid]), 'NOTICE');
				}
			});
		}
	}

	/**
	* Syndicate to for comments.
	*/
	protected function syndicateToComment(\ElggComment $entity) {
		if (!empty($this->input['mp-syndicate-to'])) {
			$client = new MentionClient();
		
			if (is_string(elgg_get_plugin_setting('webmention_proxy', 'indieweb'))) {
				$client->setProxy(elgg_get_plugin_setting('webmention_proxy', 'indieweb'));
			}
			
			if (is_string(elgg_get_plugin_setting('webmention_user_agent', 'indieweb'))) {
				$client->setUserAgent(elgg_get_plugin_setting('webmention_user_agent', 'indieweb'));
			}
			
			if ((bool) elgg_get_plugin_setting('webmention_enable_debug', 'indieweb')) {
				$client->enableDebug();
			}
			
			$source = $entity->getURL();
			$guid = $entity->guid;
			
			foreach ($this->input['mp-syndicate-to'] as $target) {
				$client->sendWebmention($source, $target);
				
				elgg_call(ELGG_IGNORE_ACCESS, function () use ($guid, $source) {
					$syndication = new \Elgg\IndieWeb\Webmention\Entity\Syndication();
					$syndication->owner_guid = elgg_get_site_entity()->guid;
					$syndication->container_guid = elgg_get_site_entity()->guid;
					$syndication->access_id = ACCESS_PRIVATE;
					$syndication->source_id = $guid;
					$syndication->source_url = $source;
					$syndication->save();
				});
				
				elgg_call(ELGG_IGNORE_ACCESS, function () use ($source, $target) {
					$webmention = new \Elgg\IndieWeb\Webmention\Entity\Webmention();
					$webmention->owner_guid = elgg_get_site_entity()->guid;
					$webmention->container_guid = elgg_get_site_entity()->guid;
					$webmention->access_id = ACCESS_PRIVATE;
					$webmention->source = $source;
					$webmention->target = $target;
					$webmention->property = 'send';
					$webmention->published = 0;
					$webmention->status = 0;
					$webmention->save();
							
					elgg_log(elgg_echo('webmention:send:success', [$webmention->guid]), 'NOTICE');
				});
			}
		}
	}

	/**
	* Get post types.
	*/
	protected function getPostTypes() {
		$post_types = [];

		foreach (['article', 'note', 'like', 'reply', 'repost', 'bookmark', 'event', 'rsvp', 'issue', 'checkin'] as $type) {
			if ((bool) elgg_get_plugin_setting('enable_micropub_' . $type, 'indieweb')) {
				$post_types[] = [
					'type' => $type,
					'name' => ucfirst($type),
				];
			}
		}

		if ((bool) elgg_get_plugin_setting('micropub_enable_contact', 'indieweb')) {
			$post_types[] = [
				'type' => 'venue',
				'name' => 'Venue',
			];
		}

		$post_types[] = [
			'type' => 'comment',
			'name' => 'Commment',
		];

		return $post_types;
	}

	/**
	* Gets syndication targets.
	*
	* @return array
	*/
	protected function getSyndicationTargets(): array {
		$syndication_targets = [];
		
		$targets = indieweb_get_syndication_targets(true);
		
		if (!empty($targets)) {
			foreach ($targets['options'] as $url => $name) {
				$target = [
					'uid' => $url,
					'name' => $name,
				];

				if (isset($targets['default']) && in_array($url, $targets['default'])) {
					$target['checked'] = true;
				}

				$syndication_targets[] = $target;
			}
		}

		return $syndication_targets;
	}

	/**
	* Gets categories: return a list of tags.
	*
	* @return array $tags A list of tags.
	*
	*/
	protected function getCategories() {
		$tags = [];
		
		$select = Select::fromTable('metadata', 'md');

		$ors = [];
		$ors[] = $select->compare('md.name', 'in', 'tags', ELGG_VALUE_STRING);

		$select->select('md.value')
			->addSelect('count(md.id) AS total')
			->where($select->merge($ors, 'OR'))
			->groupBy('md.value');

		$alias = $select->joinEntitiesTable('md', 'entity_guid');
		$select->andWhere($select->compare("{$alias}.type", '=', 'object', ELGG_VALUE_STRING));

		$select->orderBy('total', 'DESC');

		$select->setMaxResults(50);

		$results = $select->execute()->fetchAllAssociative();

		foreach ($results as $result) {
			$tags[] = $result['value'];
		}
		
		return $tags;
	}

	/**
	* Returns the source response. This can either be a list of post items, or a single post with properties.
	*
	* @see https://github.com/indieweb/micropub-extensions/issues/4
	*
	* @return array $return. Either list of posts or a single item with properties.
	*
	*/
	protected function getSourceResponse(Request $request) {
		$return = [];

		// Single URL.
		if (!empty($request->getParam('url'))) {
			$guid = (int) indieweb_get_guid($request->get('url'));
			
			try {
				if ($guid > 0) {
					$entity = get_entity($guid);
					if ($entity instanceof \ElggObject) {
						$properties = [];
						
						$path = $entity->getURL();
						
						try {
							$properties = [];
							
							$xray = new XRay();
							$data = $xray->parse($path, false, []);
							
							if (!empty($data['data'])) {
								foreach ($data['data'] as $key => $value) {
									if (is_string($value)) {
										$data['data'][$key] = [$value];
									} else if (in_array($key, ['content', 'author'])) {
										$data['data'][$key] = [$value];
									}
								}
								
								$properties = $data['data'];
								$properties['url'] = [$path];
								$properties['post-status'] = [$entity->published_status ?: $entity->status];
							}
						} catch (\Exception $e) {
							elgg_log('Error parsing node for source: ' . $e->getMessage(), 'NOTICE');
						}
						
						$return = ['properties' => $properties];
					}
				}
			} catch (\Exception $e) {
				elgg_log('Error in getting url post: ' . $e->getMessage(), 'NOTICE');
			}
		} else {
			// List of posts.
			$offset = 0;
			$range = 10;
			$after = 1;
			$items = [];
			$subtype = '';

			// Subtype of post-type.
			if (!empty($request->getParam('post-type'))) {
				$type = $request->getParam('post-type');
				
				$post_types = $this->getPostTypes();
					
				foreach ($post_types as $post_type) {
					if ($post_type->type === $type) {
						$subtype = elgg_get_plugin_setting('micropub_type_' . $type, 'indieweb');
						break;
					}
				}
			} else {
				$objects = (array) elgg_extract('object', elgg_entity_types_with_capability('searchable'), []);

				foreach ($objects as $object) {
					if (in_array($object, ['river_object', 'messages', 'newsletter', 'static'])) {
						continue;
					}
					
					$subtype .= $object;
				}
			}
			
			if (is_string($subtype)) {
				$subtype = [$subtype];
			}

			// Override limit.
			$limit = $request->getParam('limit');
			if (isset($limit) && is_numeric($limit) && $limit > 0 && $limit <= 100) {
				$range = $limit;
			}

			// Override offset.
			$after_query = $request->getParam('after');
			if (isset($after_query) && is_numeric($after_query)) {
				$offset = $range * $after_query;
				$after = $after_query + 1;
			}

			// Get entities.
			$entities = elgg_call(ELGG_IGNORE_ACCESS, function() use ($range, $offset, &$subtype) {
				return elgg_get_entities([
					'type' => 'object',
					'subtype' => $subtype,
					'limit' => $range,
					'offset' => $offset,
					'wheres' => [
						function(\Elgg\Database\QueryBuilder $qb, $main_alias) {
							return $qb->compare("{$main_alias}.access_id", '=', ACCESS_PUBLIC, ELGG_VALUE_INTEGER);
						},
					],
				]);
			});
			
			if (!empty($entities)) {
				foreach ($entities as $entity) {
					$item = new \stdClass();
					$item->type = ['h-entry'];
					$item->properties = $this->getEntityProperties($entity);
					$items[$entity->time_created . '.' . $entity->guid] = $item;
				}
			}

			krsort($items);
			$items_sorted = [];
			foreach ($items as $item) {
				$items_sorted[] = $item;
			}
			$return['items'] = $items_sorted;

			if (!empty($items_sorted)) {
				$return['paging'] = ['after' => $after];
			}
		}

		return $return;
	}

	/**
	* Get entity properties.
	*
	*/
	private function getEntityProperties(\ElggObject $entity) {
		$properties = [];
		
		$date_created = \Elgg\Values::normalizeTime($entity->time_created);

		$properties['url'] = [$entity->getURL()];
		$properties['name'] = [$entity->getDisplayName() ?: elgg_echo("item:object:$entity->subtype")];
		$properties['content'] = [$entity->description];
		
		$properties['post-status'] = [$entity->published_status ?: $entity->status];
		$properties['published'] = [$date_created->format('c')];

		return $properties;
	}

	/**
	* Get contacts.
	*
	* @return array
	*/
	private function getContactsResponse(Request $request) {
		$contacts = [];
		
		$options = [
			'type' => 'object',
			'subtype' => \Elgg\IndieWeb\Contacts\Entity\Contact::SUBTYPE,
			'limit' => 100,
			'sort_by' => [
				'property' => 'name',
				'property_type' => 'metadata',
				'direction' => 'asc',
			],
		];
		
		if ($request->getParam('search')) {
			$search = $request->getParam('search');
			
			$options['metadata_name_value_pairs'] = [
				[
					'name' => 'name',
					'value' => "%{$search}%",
					'operand' => 'LIKE',
					'case_sensitive' => false,
				],
				[
					'name' => 'nickname',
					'value' => "%{$search}%",
					'operand' => 'LIKE',
					'case_sensitive' => false,
				],
			];
			
			$options['metadata_name_value_pairs_operator'] = 'OR';
		}
		
		$entities = elgg_get_entities($options);
		
		if (!empty($entities)) {
			foreach ($entities as $entity) {
				$contacts[] = [
					'name' => $entity->getDisplayName(),
					'nickname' => $entity->getNickname(),
					'photo' => $contact->thumbnail_url,
					'url' => $entity->website,
					'_internal_url' => $entity->getURL(),
				];
			}
		}

		return ['contacts' => $contacts];
	}

	/**
	* Returns geo information.
	*
	* @return \stdClass
	*/
	private function getGeoResponse(Request $request) {
		$return = new \stdClass();

		if (elgg_is_active_plugin('theme') && !empty($request->getParam('lat')) && !empty($request->getParam('lon'))) {
			$svc = elgg()->maps;
			/* @var $svc \wZm\Maps\MapsService */
			$location = $svc->reverse($request->getParam('lat'), $request->getParam('lon'));
			
			$return->geo = ['label' => $location, 'latitude' => $request->getParam('lat'), 'longitude' => $request->getParam('lon')];
		}

		return $return;
	}
}
