<?php
/**
 * IndieWeb
 * @author Nikolai Shcherbin
 * @license GNU Affero General Public License version 3
 * @copyright (c) Nikolai Shcherbin 2022
 * @link https://wzm.me
**/

namespace Elgg\IndieWeb\Micropub\Controller;

use Elgg\Request;
use Elgg\Exceptions\Http\EntityPermissionsException;
use Elgg\Exceptions\Http\BadRequestException;
use Elgg\Exceptions\Http\PageNotFoundException;
use Elgg\Database\Select;
use Symfony\Component\HttpFoundation\Response;
use p3k\XRay;
use IndieWeb\MentionClient;

class MicropubController {
	
	/**
	* The action of the request.
	*
	* @var string
	*/
	public $action = '';

	/**
	* The object type.
	*/
	public $object_type = null;

	/**
	* The request input.
	*
	* @var array
	*/
	public $input = [];

	/**
	* An object URL to act on.
	*
	* @var null
	*/
	public $object_url = null;

	/**
	* The original payload
	*
	* @var null
	*/
	public $payload_original = null;

	/**
	* The entity which is being created.
	*
	* @var \ElggObject
	*/
	public $new_entity = null;
	
	/**
	* The comment which is being created.
	*
	* @var \ElggComment
	*/
	public $comment = null;

	/**
	* Location properties.
	*
	* @var array
	*/
	protected $location = [];

	/**
	* @var \Elgg\IndieWeb\IndieAuth\Client\IndieAuthClient
	*/
	protected $indieAuth;

	/**
	* Routing callback: Micropub post endpoint.
	*/
	public function postEndpoint(\Elgg\Request $request) {
		$this->indieAuth = elgg()->indieauth;
		
		// Early response when endpoint is not enabled.
		if (!(bool) elgg_get_plugin_setting('enable_micropub', 'indieweb')) {
			throw new PageNotFoundException();
		}

		// Default response code and message.
		$response_code = 400;
		$response_message = [];

		// Micropub query.
		$micropub_query = $request->getParam('q');

		// q=syndicate-to request.
		if ($micropub_query === 'syndicate-to') {
			// Get authorization header, response early if none found.
			$auth_header = $this->indieAuth->getAuthorizationHeader();
			if (!$auth_header) {
				return elgg_error_response('Missing Authorization Header', REFERRER, 401);
			}

			if ($this->indieAuth->isValidToken($auth_header)) {
				$response_code = 200;
				$response_message = [
					'syndicate-to' => $this->getSyndicationTargets(),
				];
			} else {
				return elgg_error_response('No Valid Token', REFERRER, 403);
			}
			
			return elgg_ok_response($response_message, '', REFERRER, $response_code);
		}

		// q=config request.
		if ($micropub_query === 'config') {
			// Get authorization header, response early if none found.
			$auth_header = $this->indieAuth->getAuthorizationHeader();
			if (!$auth_header) {
				return elgg_error_response('Missing Authorization Header', REFERRER, 401);
			}

			if ($this->indieAuth->isValidToken($auth_header)) {
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
				return elgg_error_response('No Valid Token', REFERRER, 403);
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
			$auth_header = $this->indieAuth->getAuthorizationHeader();
			if (!$auth_header) {
				return elgg_error_response('Missing Authorization Header', REFERRER, 401);
			}

			if ($this->indieAuth->isValidToken($auth_header)) {
				$response_code = 200;
				$response_message = $this->getSourceResponse($request);
			} else {
				return elgg_error_response('No Valid Token', REFERRER, 403);
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
			$auth_header = $this->indieAuth->getAuthorizationHeader();
			if (!$auth_header) {
				return elgg_error_response('Missing Authorization Header', REFERRER, 401);
			}

			if ($this->indieAuth->isValidToken($auth_header)) {
				$response_code = 200;
				$response_message = $this->getContactsResponse($request);
			} else {
				return elgg_error_response('No Valid Token', REFERRER, 403);
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
			$auth_header = $this->indieAuth->getAuthorizationHeader();
			if (!$auth_header) {
				return elgg_error_response('Missing Authorization Header', REFERRER, 401);
			}

			if ($this->indieAuth->isValidToken($auth_header)) {
				$response_code = 200;
				$response_message = $this->getGeoResponse($request);
			} else {
				return elgg_error_response('No Valid Token', REFERRER, 403);
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
			$auth_header = $this->indieAuth->getAuthorizationHeader();
			if (!$auth_header) {
				return elgg_error_response('Missing Authorization Header', REFERRER, 401);
			}

			if ($this->indieAuth->isValidToken($auth_header)) {
				$response_code = 200;
				$response_message = ['categories' => $this->getCategories()];
			} else {
				return elgg_error_response('No Valid Token', REFERRER, 403);
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
		
		if ($micropub_request instanceof \p3k\Micropub\Request && $micropub_request->action) {
			$this->action = $micropub_request->action;

			if ($this->action === 'update') {
				$this->input = $micropub_request->update;
				$this->object_url = $micropub_request->url;
			} else if ($this->action === 'delete') {
				$this->object_url = $micropub_request->url;
			} else {
				$mf2 = $micropub_request->toMf2();
				$this->object_type = !empty($mf2['type'][0]) ? $mf2['type'][0] : '';
				$this->input = $mf2['properties'];
				$this->input += $micropub_request->commands;
			}
		} else {
			$description = $micropub_request->error_description ?: 'Unknown error';
			elgg_log('Error parsing incoming request: ' . $description ' - ' . print_r($input, 1), 'error');
			throw new BadRequestException();
		}

		// Attempt to delete a post, comment or webmention.
		if ($this->action === 'delete' && (bool) elgg_get_plugin_setting('micropub_enable_delete', 'indieweb') && !empty($this->object_url)) {
			// Get authorization header, response early if none found.
			$auth_header = $this->indieAuth->getAuthorizationHeader();
			if (!$auth_header) {
				return elgg_error_response('Missing Authorization Header', REFERRER, 401);
			}

			// Validate token. Return early if it's not valid.
			$valid_token = $this->indieAuth->isValidToken($auth_header, 'delete');
			if (!$valid_token) {
				return elgg_error_response('No Valid Token', REFERRER, 403);
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

			return new Response($response_message, $response_code);
		}

		// Attempt to update a post
		if ($this->action === 'update' && (bool) elgg_get_plugin_setting('micropub_enable_update', 'indieweb') && !empty($this->object_url) && !empty($this->input['replace'])) {
			// Get authorization header, response early if none found.
			$auth_header = $this->indieAuth->getAuthorizationHeader();
			if (!$auth_header) {
				return elgg_error_response('Missing Authorization Header', REFERRER, 401);
			}

			// Validate token. Return early if it's not valid.
			$valid_token = $this->indieAuth->isValidToken($auth_header, 'update');
			if (!$valid_token) {
				return elgg_error_response('No Valid Token', REFERRER, 403);
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
							
							if (isset($entity->getMetadata('title'))) {
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

			return new Response($response_message, $response_code);
		}

		// Attempt to create a post
		if (!empty($this->input) && $this->action === 'create') {
			// Get authorization header, response early if none found.
			$auth_header = $this->indieAuth->getAuthorizationHeader();
			if (!$auth_header) {
				return elgg_error_response('Missing Authorization Header', REFERRER, 401);
			}

			// Validate token. Return early if it's not valid.
			$valid_token = $this->indieAuth->isValidToken($auth_header, 'create');
			if (!$valid_token) {
				return elgg_error_response('No Valid Token', REFERRER, 403);
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
					if ($tokenOwnerId = $this->indieAuth->checkAuthor()) {
						$contact['uid'] = $tokenOwnerId;
					}

					$entityContact = elgg()->{'indieweb.contact'}->storeContact($contact);

					header('Location: ' . $entityContact->getURL());
					return new Response('', 201);
				}
			}

			// Checkin support.
			if ($checkin && $this->createEntityFromPostType('checkin') && $this->hasRequiredInput(['checkin']) && $this->isHEntry()) {
				$checkin_title = elgg_echo('indieweb:micropub:view:checkin');
				if (!empty($this->location['name'])) {
					$checkin_title = elgg_echo('indieweb:micropub:checkin:title', [$this->location['name']]);
				}

				$this->createEntity($checkin_title, 'checkin');
				$response = $this->saveEntity();
				if ($response instanceof Response) {
					return $response;
				}
			}

			// Event support.
			if ($this->createEntityFromPostType('event') && $this->isHEvent() && $this->hasRequiredInput(['start', 'end', 'name'])) {
				$this->createEntity($this->input['name'], 'event');

				// Date.
				if ((bool) elgg_get_plugin_setting('micropub_field_date_event', 'indieweb')) {
					$this->new_entity->setMetadata('event_start', strtotime($this->input['start'][0]));
					$this->new_entity->setMetadata('event_end', strtotime($this->input['end'][0]));
				}

				$response = $this->saveEntity();
				if ($response instanceof Response) {
					return $response;
				}
			}

			// RSVP support.
			if ($this->createEntityFromPostType('rsvp') && $this->isHEntry() && $this->hasRequiredInput(['in-reply-to', 'rsvp'])) {
				$this->createEntity(elgg_echo('indieweb:micropub:rsvp:title', [$this->input['in-reply-to'][0]]), 'rsvp', 'in-reply-to');

				// RSVP field
				if ((bool) elgg_get_plugin_setting('enable_micropub_rsvp', 'indieweb')) {
					$this->new_entity->setMetadata('rsvp', $this->input['rsvp']);
				}

				$response = $this->saveEntity();
				if ($response instanceof Response) {
					return $response;
				}
			}

			// Repost support.
			if ($this->createEntityFromPostType('repost') && $this->isHEntry() && $this->hasRequiredInput(['repost-of'])) {
				$this->createEntity(elgg_echo('indieweb:micropub:repost:title', [$this->input['repost-of'][0]]), 'repost', 'repost-of');
				
				$response = $this->saveEntity();
				if ($response instanceof Response) {
					return $response;
				}
			}

			// Bookmark support.
			if ($this->createEntityFromPostType('bookmark') && $this->isHEntry() && $this->hasRequiredInput(['bookmark-of'])) {
				$this->createEntity(elgg_echo('indieweb:micropub:bookmark:title', [$this->input['bookmark-of'][0]]), 'bookmark', 'bookmark-of');
				
				$response = $this->saveEntity();
				if ($response instanceof Response) {
					return $response;
				}
			}

			// Like support.
			if ($this->createEntityFromPostType('like') && $this->isHEntry() && $this->hasRequiredInput(['like-of'])) {
				// This can be a like on a webmention, usually a reply or mention. Get the url of the webmention and replace the like-of value.
				$like = $this->input['like-of'][0];
				
				$guid = (int) indieweb_get_guid($like);
				
				try {
					if ($guid > 0) {
						$webmention_target = get_entity($guid);
						if ($webmention_target instanceof \Elgg\IndieWeb\Webmention\Entity\Webmention) {
							$url = $webmention_target->getURL();
							$this->input['like-of'][0] = $url;
						}
					}
				} catch (\Exception $ignored) {}

				$this->createEntity(elgg_echo('indieweb:micropub:bookmark:title', [$this->input['like-of'][0]]), 'like', 'like-of');
				
				$response = $this->saveEntity();
				if ($response instanceof Response) {
					return $response;
				}
			}

			// Issue support.
			if ($this->createEntityFromPostType('issue') && $this->isHEntry() && $this->hasRequiredInput(['content', 'name', 'in-reply-to']) && $this->hasNoKeysSet(['bookmark-of', 'repost-of', 'like-of'])) {
				$this->createEntity($this->input['name'], 'issue');
				
				$response = $this->saveEntity();
				if ($response instanceof Response) {
					return $response;
				}
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
								if ($target instanceof \Elgg\IndieWeb\Webmention\Entity\Webmention && $webmention_target->getProperty() === 'in-reply-to') {
									$container_guid = $target->guid;
									$link_field_url = $target->getURL();
								} else if ($target instanceof \ElggComment) {
									if ((int) elgg_get_config('comments_max_depth') > 0) {
										$container_guid = $target->guid;
										$link_field_url = $target->getURL();
									} else {
										$container_guid = $target->container_guid;
										$link_field_url = $target->getContainerEntity()->getURL();
									}
								}
							}
							
							catch (\Exception $ignored) {}
							
							// Create comment.
							$comment = new \ElggComment();
							$comment->description = htmlspecialchars($this->input['content'][0], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
	
							if ($target instanceof \ElggComment) {
								$comment->level = $target->getLevel() + 1;
								$comment->parent_guid = $target->guid;
								$comment->thread_guid = $target->getThreadGUID();
								
								// make sure comment is contained in the content
								$target = $target->getContainerEntity();
							}

							$comment->owner_guid = elgg_get_site_entity()->guid;
							$comment->container_guid = $container_guid;
							$comment->access_id = $target->access_id;
								
							if (!$comment->save()) {
								return elgg_error_response(elgg_echo('generic_comment:failure'));
							}
							
							// Check if there's an image field.
							if (elgg_is_active_plugin('theme')) {
								$files = $this->saveUpload('upload');
								if ($files && $comment->hasCapability('allow_attachments')) {
									foreach ($files as $file) {
										apps_attach($comment, $file);
									}
								}
							}

							// Check link field.
							if (!empty($link_field_url) && (bool) elgg_get_plugin_setting('micropub_send_webmention_reply', 'indieweb')) {
								// Syndicate.
								if (isset($this->input['mp-syndicate-to'])) {
									$this->input['mp-syndicate-to'][] = $link_field_url;
								} else {
									$this->input['mp-syndicate-to'] = [$link_field_url];
								}
							}

							$response = $this->saveComment();
							if ($response instanceof Response) {
								return $response;
							}
						}
					} catch (\Exception $e) {
						elgg_log('Error trying to create a comment from reply: ' . $e->getMessage(), 'error');
					}
				}

				// We got here, so it's a standard post
				$this->createEntity(elgg_echo('indieweb:micropub:reply:title', [$this->input['in-reply-to'][0]]), 'reply', 'in-reply-to');
				
				$response = $this->saveEntity();
				if ($response instanceof Response) {
					return $response;
				}
			}

			// Note post type.
			if ($this->createEntityFromPostType('note') && $this->isHEntry() && $this->hasRequiredInput(['content']) && $this->hasNoKeysSet(['name', 'in-reply-to', 'bookmark-of', 'repost-of', 'like-of'])) {
				$this->createEntity(elgg_echo('indieweb:micropub:view:note'), 'note');
				$response = $this->saveEntity();
				
				if ($response instanceof Response) {
					return $response;
				}
			}

			// Article post type.
			if ($this->createEntityFromPostType('article') && $this->isHEntry() && $this->hasRequiredInput(['content', 'name']) && $this->hasNoKeysSet(['in-reply-to', 'bookmark-of', 'repost-of', 'like-of'])) {
				$this->createEntity($this->input['name'], 'article');
				$response = $this->saveEntity();
				
				if ($response instanceof Response) {
					return $response;
				}
			}
		}
		
		return elgg_ok_response($response_message, '', REFERRER, $response_code);
	}

	/**
	* Upload files through the media endpoint.
	*
	*/
	public function mediaEndpoint(\Elgg\Request $request) {
		$this->indieAuth = elgg()->indieauth;
		
		// Early response when endpoint is not enabled.
		if (!(bool) elgg_get_plugin_setting('enable_micropub_media', 'indieweb')) {
			throw new PageNotFoundException();
		}

		// Default message.
		$response_message = '';

		// Get authorization header, response early if none found.
		$auth_header = $this->indieAuth->getAuthorizationHeader();
		if (!$auth_header) {
			return elgg_error_response('Missing Authorization Header', REFERRER, 401);
		}

		if ($this->indieAuth->isValidToken($auth_header, 'media') && in_array($request->getMethod(), ['GET', 'POST'])) {
			$user_guid = 1;
			// Override user guid if using internal indieauth.
			if ($tokenOwnerId = $this->indieAuth->checkAuthor()) {
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
				$response_code = 200;
				
				$files = $this->saveUpload('upload');
				
				if (!empty($files)) {
					foreach ($files as $file) {
						elgg_call(ELGG_IGNORE_ACCESS, function () use ($file) {
							// Set owner
							$file->owner_guid = $user_guid;
							$file->save();
						});
					}
					
					// Return the url in Location.
					$response_code = 201;
					
					$file_url = $files[0]->getURL();
					$response_message = [
						'url' => $file_url
					];
					
					header('Location: ' . $file_url);
				}
			}
		} else {
			return elgg_error_response('No Valid Token', REFERRER, 403);
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
		// Get user guid.
		if (elgg_is_active_plugin('theme')) {
			$owner_guid = (int) elgg_get_plugin_setting('micropub_author_' . $post_type, 'indieweb');
		} else {
			$username = elgg_get_plugin_setting('micropub_author_' . $post_type, 'indieweb');
			$owner_guid = get_user_by_username($username)->guid;
		}
		
		if (empty($owner_guid)) {
			$owner_guid = 1;
		}
		
		$subtype = elgg_get_plugin_setting('micropub_type_' . $post_type, 'indieweb');

		$status = 'draft';
		if ((bool) elgg_get_plugin_setting('micropub_status_' . $post_type, 'indieweb')) {
			$status = 'published';
		}
		
		// Override user guid.
		if ($tokenOwnerId = $this->indieAuth->checkAuthor()) {
			$owner_guid = $tokenOwnerId;
		}
		
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

		// Create node.
		$this->new_entity = new \ElggObject;
		$this->new_entity->setSubtype($subtype);
		$this->new_entity->owner_guid = $owner_guid;
		$this->new_entity->time_created = $time_created;
		$this->new_entity->access_id = ACCESS_PUBLIC;
		$this->new_entity->title = $title;
		
		if (elgg_is_active_plugin('theme')) {
			$this->new_entity->published_status = $status;
		} else {
			$this->new_entity->status = $status;
		}

		// Content.
		if (!empty($this->input['content'][0]) && (bool) elgg_get_plugin_setting('micropub_field_content_' . $post_type, 'indieweb')) {
			$description = htmlspecialchars($this->input['content'][0], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

			$this->new_entity->description = $description;
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
				$this->new_entity->address = $uri;
			}
		}

		// Uploads.
		if ((bool) elgg_get_plugin_setting('micropub_field_upload_' . $post_type, 'indieweb')) {
			$this->handleUploads($post_type, $owner_guid);
		}

		// Categories.
		if ((bool) elgg_get_plugin_setting('micropub_field_tags_' . $post_type, 'indieweb')) { 
			$this->handleCategories($post_type);
		}

		// Geo location.
		if ((bool) elgg_get_plugin_setting('micropub_field_location_' . $post_type, 'indieweb')) { 
			$this->handleGeoLocation($post_type);
		}
	}

	/**
	* Saves the post.
	*
	* @return \Symfony\Component\HttpFoundation\Response
	*
	*/
	protected function saveEntity() {
		if ($this->new_entity->save()) {
			// Syndicate.
			if ((bool) elgg_get_plugin_setting('enable_webmention', 'indieweb')) {
				$this->syndicateToPost();
			}

			// WebSub.
			if ((bool) elgg_get_plugin_setting('enable_websub', 'indieweb') && (bool) elgg_get_plugin_setting('websub_micropub_publish', 'indieweb')) {
				$this->publishToHub();
			}

			// Allow plugins to react after the post is saved.
			elgg_trigger_plugin_hook('indieweb_micropub_post_saved', $this->new_entity->subtype, $this->input, []);

			header('Location: ' . $this->new_entity->getURL());
			return new Response('', 201);
		}
		
		return elgg_error_response(elgg_echo('error:post:save'));
	}

	/**
	* Saves the comment.
	*
	* @return \Symfony\Component\HttpFoundation\Response
	*
	*/
	protected function saveComment() {
		if ($this->comment->save()) {
			// only river for top level comments
			if ($this->comment->getLevel() === 1) {
				// Add to river
				elgg_create_river_item([
					'view' => 'river/object/comment/create',
					'action_type' => 'comment',
					'object_guid' => $this->comment->guid,
					'target_guid' => $this->comment->getContainerEntity()->guid,
				]);
			}
			
			// Syndicate.
			if ((bool) elgg_get_plugin_setting('enable_webmention', 'indieweb')) {
				$this->syndicateToComment();
			}

			header('Location: ' . $this->comment->getURL());
			return new Response('', 201);
		}
		
		return elgg_error_response(elgg_echo('generic_comment:failure'));
	}

	/**
	* Helper function to upload file(s).
	*
	* @param $file_key
	*   The key in the $_FILES variable to look for in upload.
	* @param $limit
	*   Limit number of uploads, 10 files maximum
	*
	* @return array $files
	*
	*/
	protected function saveUpload($file_key, int $limit = 1) {
		$files = [];
		
		$file_bag = _elgg_services()->request->files;
		
		$uploaded_files = $file_bag->get($file_key);
		if (!$uploaded_files) {
			return [];
		}
		if (!is_array($uploaded_files)) {
			$uploaded_files = [$uploaded_files];
		}
		
		foreach ($uploaded_files as $upload) {
			if (!$upload->isValid()) {
				continue;
			}
			
			if ($limit && $upload >= $limit) {
				continue;
			}
			
			elgg_call(ELGG_IGNORE_ACCESS, function () use ($upload, &$files) {
				$file = new \ElggFile;
				/* @var $file ElggFile */
				
				$originalfilename = $upload->getClientOriginalName();
				$file->originalfilename = $originalfilename;
				$file->title = htmlspecialchars($file->originalfilename, ENT_QUOTES, 'UTF-8');
				$file->upload_time = time();
				$prefix = $file->filestore_prefix ? : 'file';
				$prefix = trim($prefix, '/');
				$filename = elgg_strtolower("$prefix/{$file->upload_time}{$file->originalfilename}");
				$file->setFilename($filename);
				$file->filestore_prefix = $prefix;
				
				$file->owner_guid = elgg_get_site_entity()->guid;
				$file->access_id = ACCESS_PUBLIC;
				
				$mime_type = elgg()->mimetype->getMimeType($file->getFilenameOnFilestore(), $upload->getClientMimeType());
				$simpletype = elgg()->mimetype->getSimpleType($mime_type);
				
				if (!in_array($simpletype, ['audio', 'image', 'video'])) {
					$file->delete();
					continue;
				}
				
				$file->simpletype = $simpletype;

				$hook_params = [
					'file' => $file,
					'upload' => $upload,
				];

				$uploaded = _elgg_services()->hooks->trigger('upload', 'file', $hook_params);
				if ($uploaded !== true && $uploaded !== false) {
					$filestorename = $file->getFilenameOnFilestore();
					try {
						$uploaded = $upload->move(pathinfo($filestorename, PATHINFO_DIRNAME), pathinfo($filestorename, PATHINFO_BASENAME));
					} catch (\Symfony\Component\HttpFoundation\File\Exception\FileException $ex) {
						elgg_log($ex->getMessage(), 'ERROR');
						$uploaded = false;
					}
				}

				if (!$uploaded) {
					continue;
				}

				$mime_type = elgg()->mimetype->getMimeType($file->getFilenameOnFilestore(), $upload->getClientMimeType());
				if ($mime_type === 'image/vnd.djvu' || $mime_type === 'image/vnd.djvu+multipage'){
					$file->setMimeType('application/x-ext-djvu');
				} else {
					$file->setMimeType($mime_type);
				}
				
				_elgg_services()->events->triggerAfter('upload', 'file', $file);

				if (!$file->save() || !$file->exists()) {
					$file->delete();
					continue;
				}

				if (($file->getMimeType() === 'image/jpeg' || $file->getMimeType() === 'image/png' || $file->getMimeType() === 'image/gif' || $file->getMimeType() === 'image/webp') && $file->saveIconFromElggFile($file)) {
					$file->thumbnail = $file->getIcon('small')->getFilename();
					$file->smallthumb = $file->getIcon('medium')->getFilename();
					$file->largethumb = $file->getIcon('large')->getFilename();
				}
				
				$files[] = $file;
			});
		}
		
		return $files;
	}

	/**
	* Handle uploads.
	*
	* @param string $post_type
	*
	* @param int $owner_guid
	*
	*/
	protected function handleUploads($post_type, int $owner_guid = 1) {
		foreach (['photo', 'audio', 'video'] as $upload_key) {
			$limit = (int) elgg_get_plugin_setting('micropub_field_upload_limit_' . $post_type, 'indieweb', 1);
	
			$files = $this->saveUpload($upload_key, $limit);
			
			$entity = $this->new_entity;
			
			if ($files) {
				elgg_call(ELGG_IGNORE_ACCESS, function () use ($entity, &$files) {
					foreach ($files as $file) {
						$file->owner_guid = $owner_guid;
						$file->container_guid = $this->new_entity->guid;
						$file->save();
						
						$entity->addRelationship($file->guid, 'attached');
					}
				}
			}
		}
	}

	/**
	* Handle and set categories.
	*
	* @param string $post_type
	*
	*/
	protected function handleCategories($post_type) {
		if (!empty($this->input['category'])) {
			$values = [];
			
			foreach ($this->input['category'] as $category) {
				$values[] = $category;
			}

			if (!empty($values)) {
				$this->new_entity->tags = $values;
			}
		}
	}

	/**
	* Handles geo location input.
	*
	* @param string $post_type
	*
	*/
	protected function handleGeoLocation($post_type) {
		if (!empty($this->location['lat']) && !empty($this->location['lon'])) {
			try {
				$this->new_entity->setLatLong($this->location['lat'], $this->location['lon']);
			} catch (\Exception $e) {
				elgg_log('Error saving geo location: ' . $e->getMessage(), 'error');
			}
		}
	}

	/**
	* Syndicate to for posts.
	*
	*/
	protected function syndicateToPost() {
		$entity = $this->new_entity;
		
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
					$webmention->published = false;
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
	protected function publishToHub() {
		$entity = $this->new_entity;
		
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
					elgg_log(elgg_echo('websub:send:success', [$websub->guid]), 'NOTICE');
				}
			});
		}
	}

	/**
	* Syndicate to for comments.
	*/
	protected function syndicateToComment() {
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
			
			$entity = $this->comment;
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
					$webmention->published = false;
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
				$post_types[] = (object) [
					'type' => $type,
					'name' => ucfirst($type),
				];
			}
		}

		if ((bool) elgg_get_plugin_setting('micropub_enable_contact', 'indieweb')) {
			$post_types[] = (object) [
				'type' => 'venue',
				'name' => 'Venue',
			];
		}

		$post_types[] = (object) [
			'type' => 'comment',
			'name' => 'Commments'),
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
						
						$return = ['properties' => (object) $properties];
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
					$item->properties = (object) $this->getEntityProperties($entity);
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
				$return['paging'] = (object) ['after' => $after];
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
			
			$return->geo = (object) ['label' => $location, 'latitude' => $request->getParam('lat'), 'longitude' => $request->getParam('lon')];
		}

		return $return;
	}
}
