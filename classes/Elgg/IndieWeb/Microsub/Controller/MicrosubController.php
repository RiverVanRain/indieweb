<?php
/**
 * IndieWeb
 * @author Nikolai Shcherbin
 * @license GNU Affero General Public License version 3
 * @copyright (c) Nikolai Shcherbin 2022
 * @link https://wzm.me
**/

namespace Elgg\IndieWeb\Microsub\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Elgg\Exceptions\Http\BadRequestException;

class MicrosubController {
	
	 /**
	   * @var  \Drupal\Core\Config\Config
	   */
	  protected $config;

	  /**
	   * A collection of urls for aggregated feeds.
	   *
	   * @var array
	   */
	  protected $aggregated_feeds;

	  /**
	   * Request object.
	   *
	   * @var \Symfony\Component\HttpFoundation\Request $request
	   */
	  protected $request;

	  /**
	   * @var \Drupal\indieweb_indieauth\IndieAuthClient\IndieAuthClientInterface
	   */
	  protected $indieAuth;

	  /**
	   * Whether this is an authenticated request or not.
	   *
	   * @var bool
	   */
	  protected $isAuthenticatedRequest = FALSE;

	  /**
	   * Whether anonymous requests on the Microsub endpoint are allowed or not.
	   *
	   * This allows getting channels and the posts in that channel. Write
	   * operations (like managing channels, subscribing, search, marking (un)read
	   * etc) will not be allowed when enabled and the request is anonymous.
	   *
	   * @return boolean
	   */
	  private function allowAnonymousRequest() {
		return Settings::get('indieweb_microsub_anonymous', FALSE);
	  }

	  /**
	   * Whether this is an authenticated request or not.
	   *
	   * @return bool
	   */
	  private function isAuthenticatedRequest() {
		return $this->isAuthenticatedRequest;
	  }

	  /**
	   * Search feeds based on URL.
	   *
	   * @param \Symfony\Component\HttpFoundation\Request $request
	   *
	   * @return \Symfony\Component\HttpFoundation\JsonResponse
	   */
	  public function searchFeeds(Request $request) {
		$results = [];

		// Get the typed string from the URL, if it exists.
		if (($input = $request->query->get('q')) && strlen($input) > 6) {

		  // Add a protocol if needed.
		  if ( $parts = parse_url($input) ) {
			if ( !isset($parts["scheme"]) ) {
			  $input = "http://$input";
			}
		  }

		  if (filter_var($input, FILTER_VALIDATE_URL) !== FALSE) {
			/** @var \Drupal\indieweb_microsub\MicrosubClient\MicrosubClientInterface $microsubClient */
			$microsubClient = \Drupal::service('indieweb.microsub.client');
			$feeds = $microsubClient->searchFeeds($input);
			if (!empty($feeds['feeds'])) {
			  foreach ($feeds['feeds'] as $feed) {
				$results[] = (object) [
				  'value' => $feed['url'],
				  'label' => $feed['url'] . ' (' . $feed['type'] . ')',
				];
			  }
			}
		  }
		}

		return new JsonResponse($results);
	  }
	

	/**
	 * Routing callback: internal webmention endpoint.
	 */
	public static function callback(Request $request) {
		if (!(bool) elgg_get_plugin_setting('enable_microsub', 'indieweb')) {
			throw new \Elgg\Exceptions\Http\PageNotFoundException();
		}
		
		if(!(empty) elgg_get_plugin_setting('microsub_endpoint', 'indieweb')) {
			throw new BadRequestException();
		}
		
		// Default response code and message.
		$response = [
		  'message' => 'Bad request',
		  'code' => 400,
		];

		// Determine scope.
		$scope = NULL;
		$request_method = $request->getMethod();
		$action = $request->get('action');

		if ($action == 'channels' && $request_method == 'POST') {
			$scope = 'channels';
		}
		elseif (in_array($action, ['follow', 'unfollow', 'search', 'preview'])) {
			$scope = 'follow';
		}
		elseif ($action == 'channels' || $action == 'timeline') {
			$scope = 'read';
		}

		// Get authorization header, response early if none found.
		$auth_header = $this->indieAuth->getAuthorizationHeader();
		if (!$auth_header) {

		  $response_code = 401;
		  $response_message = '';

		  // Check anonymous requests.
		  if ($this->allowAnonymousRequest() && $scope == 'read' && $request_method == 'GET' && in_array($action, ['channels', 'timeline'])) {
			  switch ($action) {

				case 'channels':
				  $response = $this->getChannelList();
				  break;

				case 'timeline':
				  $response = $this->getTimeline();
				  break;
			  }

			  $response_message = isset($response['response']) ? $response['response'] : '';
			  $response_code = isset($response['code']) ? $response['code'] : 200;
			}

		  return new JsonResponse($response_message, $response_code);
		}

		// Validate token.
		if (!$this->indieAuth->isValidToken($auth_header, $scope)) {
		  return new JsonResponse('', 403);
		}

		// If we get to here, this is an authenticated request.
		$this->isAuthenticatedRequest = TRUE;

		// ---------------------------------------------------------
		// GET actions.
		// ---------------------------------------------------------

		if ($request_method == 'GET') {

		  switch ($action) {

			case 'channels':
			  $response = $this->getChannelList();
			  break;

			case 'timeline':
			  $response = $this->getTimeline();
			  break;

			case 'follow':
			  $response = $this->getSources();
			  break;

			case 'search':
			  $response = $this->search();
			  break;

			case 'preview':
			  $response = $this->previewUrl();
			  break;

		  }
		}

		// ---------------------------------------------------------
		// POST actions.
		// ---------------------------------------------------------

		if ($request_method == 'POST') {
		  switch ($action) {

			// ---------------------------------------------------------
			// Channels
			// ---------------------------------------------------------

			case 'channels':
			  $method = $request->get('method');
			  if (!$method) {
				$method = 'create';
				if ($id = $request->get('channel')) {
				  $method = 'update';
				}
			  }

			  if ($method == 'create') {
				$response = $this->createChannel();
			  }

			  if ($method == 'update') {
				$response = $this->updateChannel();
			  }

			  if ($method == 'order') {
				$response = $this->orderChannels();
			  }

			  if ($method == 'delete') {
				$response = $this->deleteChannel();
			  }
			  break;

			// ---------------------------------------------------------
			// Timeline
			// ---------------------------------------------------------

			case 'timeline':
			  $method = $request->get('method');
			  if (in_array($method, ['mark_read', 'mark_unread'])) {
				$status = $method == 'mark_read' ? 1 : 0;
				$response = $this->timelineChangeReadStatus($status);
			  }

			  if ($method == 'remove') {
				$response = $this->removeItem();
			  }

			  if ($method == 'move') {
				$response = $this->moveItem();
			  }

			  break;

			// ---------------------------------------------------------
			// Follow, Unfollow, Search and Preview
			// ---------------------------------------------------------

			case 'follow':
			  $response = $this->followSource();
			  break;

			case 'unfollow':
			  $response = $this->deleteSource();
			  break;

			case 'search':
			  $response = $this->search();
			  break;

			case 'preview':
			  $response = $this->previewUrl();
			  break;

		  }
		}

		$response_message = isset($response['response']) ? $response['response'] : [];
		$response_code = isset($response['code']) ? $response['code'] : 200;

		return new JsonResponse($response_message, $response_code);
		
	}
	
	
	 /**
	  * Handle channels request.
	  *
	  * @return array $response
	 */
	protected function getChannelList() {
		$channels = [];

		$tree = $this->request->get('method') === 'tree';

		$ids = $this->entityTypeManager()
		  ->getStorage('indieweb_microsub_channel')
		  ->getQuery()
		  ->accessCheck()
		  ->condition('status', 1)
		  ->sort('weight', 'ASC')
		  ->execute();

		$channels_list = $this->entityTypeManager()->getStorage('indieweb_microsub_channel')->loadMultiple($ids);

		// Notifications channel.
		if ($this->isAuthenticatedRequest()) {
		  $notifications = \Drupal::entityTypeManager()->getStorage('indieweb_microsub_item')->getUnreadCountByChannel(0);
		  $channels[] = (object) [
			'uid' => 'notifications',
			'name' => 'Notifications',
			'unread' => (int) $notifications,
		  ];
		}

		/** @var \Drupal\indieweb_microsub\Entity\MicrosubChannelInterface $channel */
		foreach ($channels_list as $channel) {
		  $unread = $sources = [];

		  // Unread can either an int, boolean or omitted.
		  if (($indicator = $channel->read_indicator) && $this->isAuthenticatedRequest()) {
			if ($indicator == 1) {
			  $unread['unread'] = (int) $channel->getUnreadCount(true);
			}
			elseif ($indicator == 2) {
			  $unread['unread'] = (bool) $channel->getUnreadCount(false);
			}
		  }
		  else {
			$unread['unread'] = 0;
		  }

		  if ($tree) {
			$channel_sources = $this->getSources($channel->id(), TRUE);
			if (!empty($channel_sources['response']->items)) {
			  $sources = ['sources' => $channel_sources['response']->items];
			}
		  }

		  $channels[] = (object) ([
			'uid' => $channel->id(),
			'name' => $channel->label(),
		  ] + $unread + $sources);

		}

		return ['response' => ['channels' => $channels]];
	}

	  /**
	   * Handle timeline request.
	   *
	   * @param $search
	   *   Searches in posts.
	   *
	   * @return array $response
	   */
	  protected function getTimeline($search = NULL) {
		return \Drupal::service('indieweb.microsub.client')->getTimeline($this->isAuthenticatedRequest(), $search);
	  }

	  /**
	   * Create a channel.
	   *
	   * @return array
	   *
	   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
	   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
	   * @throws \Drupal\Core\Entity\EntityStorageException
	   */
	  protected function createChannel() {
		$return = ['response' => [], 'code' => 400];

		$name = $this->request->get('name');
		if (!empty($name)) {
		  $uid = 1;
		  if ($token_uid = $this->indieAuth->checkAuthor()) {
			$uid = $token_uid;
		  }
		  $values = ['title' => $name, 'uid' => $uid];
		  $channel = $this->entityTypeManager()->getStorage('indieweb_microsub_channel')->create($values);
		  $channel->save();
		  if ($channel->label()) {
			$return = ['response' => ['uid' => $channel->id(), 'name' => $channel->label()], 'code' => 200];
		  }
		}

		return $return;
	  }

	  /**
	   * Updates a channel.
	   *
	   * @return array
	   *
	   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
	   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
	   * @throws \Drupal\Core\Entity\EntityStorageException
	   */
	  protected function updateChannel() {
		$return = ['response' => [], 'code' => 400];

		$id = $this->request->get('channel');
		$name = $this->request->get('name');
		if (!empty($name) && !empty($id)) {
		  /** @var \Drupal\indieweb_microsub\Entity\MicrosubChannelInterface $channel */
		  $channel = $this->entityTypeManager()->getStorage('indieweb_microsub_channel')->load($id);
		  if ($channel) {
			$channel->set('title', $name)->save();
			$return = ['response' => ['uid' => $channel->id(), 'name' => $channel->label()], 'code' => 200];
		  }
		}

		return $return;
	  }

	  /**
	   * Deletes a channel.
	   *
	   * @return array
	   *
	   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
	   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
	   * @throws \Drupal\Core\Entity\EntityStorageException
	   */
	  protected function deleteChannel() {
		$return = ['response' => [], 'code' => 400];

		$id = $this->request->get('channel');
		if (!empty($id)) {
		  $channel = $this->entityTypeManager()->getStorage('indieweb_microsub_channel')->load($id);
		  if ($channel) {
			$channel->delete();
			$return = ['response' => [], 'code' => 200];
		  }
		}

		return $return;
	  }

	  /**
	   * Orders channels.
	   *
	   * @return array
	   *
	   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
	   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
	   * @throws \Drupal\Core\Entity\EntityStorageException
	   */
	  protected function orderChannels() {
		$return = ['response' => [], 'code' => 400];

		$ids = $this->request->get('channels');
		if (!empty($ids)) {
		  $weight = -20;
		  ksort($ids);
		  foreach ($ids as $id) {
			/** @var \Drupal\indieweb_microsub\Entity\MicrosubChannelInterface $channel */
			$channel = $this->entityTypeManager()->getStorage('indieweb_microsub_channel')->load($id);
			if ($channel) {
			  $channel->set('weight', $weight);
			  $channel->save();
			  $weight++;
			}
		  }
		  $return = ['response' => [], 'code' => 200];
		}

		return $return;
	  }

	  /**
	   * Mark items (un)read for a channel.
	   *
	   * @param int $status
	   *   The status.
	   *
	   * @return array
	   *
	   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
	   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
	   */
	  protected function timelineChangeReadStatus($status) {

		$channel_id = $this->request->get('channel');

		// Notifications is stored as channel 0.
		if ($channel_id == 'notifications') {
		  $channel_id = 0;
		}

		// Check entry or last_read_entry. If last_read_entry is passed in, we
		// completely ignore entries, this usually just means 'Mark all as read'.
		$entries = $this->request->get('entry');
		if ($channel_id != 'global') {
		  $last_read_entry = $this->request->get('last_read_entry');
		  if (!empty($last_read_entry)) {
			$entries = NULL;
		  }
		}

		if ($channel_id || $channel_id === 0) {
		  $this->entityTypeManager()->getStorage('indieweb_microsub_item')->changeReadStatus($channel_id, $status, $entries);
		}

		return ['response' => [], 'code' => 200];
	  }

	  /**
	   * Follow or update a source.
	   *
	   * @return array
	   *
	   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
	   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
	   * @throws \Drupal\Core\Entity\EntityStorageException
	   */
	  protected function followSource() {
		$return = ['response' => [], 'code' => 400];

		$url = $this->request->get('url');
		$channel_id = $this->request->get('channel');
		$method = $this->request->get('method');

		if (!empty($channel_id) && !empty($url)) {
		  $channel = $this->entityTypeManager()->getStorage('indieweb_microsub_channel')->load($channel_id);
		  if ($channel) {

			$uid = 1;
			if ($token_uid = $this->indieAuth->checkAuthor()) {
			  $uid = $token_uid;
			}

			if ($method == 'update') {
			  /** @var \Drupal\indieweb_microsub\Entity\MicrosubSourceInterface $source */
			  $sources = $this->entityTypeManager()->getStorage('indieweb_microsub_source')->loadByProperties(['url' => $url]);
			  if (!empty($sources) && count($sources) == 1) {
				$source = array_shift($sources);
				$source->set('channel_id', $channel_id);
				$source->save();
			  }
			}
			else {
			  $values = [
				'uid' => $uid,
				'url' => $url,
				'channel_id' => $channel_id,
				'fetch_interval' => Settings::get('microsub_api_subscribe_interval', 86400),
				'items_to_keep' => Settings::get('microsub_api_subscribe_keep', 20),
			  ];
			  $source = $this->entityTypeManager()->getStorage('indieweb_microsub_source')->create($values);
			  $source->save();

			  // Send subscribe request.
			  if (\Drupal::moduleHandler()->moduleExists('indieweb_websub') && $this->config('indieweb_websub.settings')->get('microsub_api_subscribe')) {
				/** @var \Drupal\indieweb_websub\WebSubClient\WebSubClientInterface $websub_service */
				$websub_service = \Drupal::service('indieweb.websub.client');
				if ($info = $websub_service->discoverHub($source->label())) {
				  $source->set('url', $info['self'])->save();
				  $websub_service->subscribe($info['self'], $info['hub'], 'subscribe');
				}
			  }

			}
			$return = ['response' => ['type' => 'feed', 'url' => $url], 'code' => 200];
		  }
		}

		return $return;
	}

	  /**
	   * Delete a source.
	   *
	   * @return array
	   *
	   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
	   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
	   * @throws \Drupal\Core\Entity\EntityStorageException
	   */
	  protected function deleteSource() {
		$return = ['response' => [], 'code' => 400];

		$url = $this->request->get('url');
		$channel_id = $this->request->get('channel');
		if (!empty($channel_id) && !empty($url)) {
		  /** @var \Drupal\indieweb_microsub\Entity\MicrosubSourceInterface[] $sources */
		  $sources = $this->entityTypeManager()->getStorage('indieweb_microsub_source')->loadByProperties(['url' => $url, 'channel_id' => $channel_id]);
		  if (count($sources) == 1) {
			$source = array_shift($sources);
			$source->delete();

			// Send unsubscribe request.
			if (\Drupal::moduleHandler()->moduleExists('indieweb_websub') && $this->config('indieweb_websub.settings')->get('microsub_api_subscribe')) {
			  if ($source->usesWebSub()) {
				/** @var \Drupal\indieweb_websub\WebSubClient\WebSubClientInterface $websub_service */
				$websub_service = \Drupal::service('indieweb.websub.client');
				if ($info = $websub_service->discoverHub($source->label())) {
				  $websub_service->subscribe($info['self'], $info['hub'], 'unsubscribe');
				}
			  }
			}

			$return = ['response' => [], 'code' => 200];
		  }
		}

		return $return;
	  }

	  /**
	   * Get sources.
	   *
	   * @param null $channel_id
	   * @param bool $include_unread
	   *
	   * @return array
	   *
	   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
	   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
	   */
	  protected function getSources($channel_id = NULL, $include_unread = FALSE) {
		$return = ['response' => [], 'code' => 400];

		if (!isset($channel_id)) {
		  $channel_id = $this->request->get('channel');
		}

		if (!empty($channel_id)) {
		  /** @var \Drupal\indieweb_microsub\Entity\MicrosubSourceInterface[] $sources */
		  $sources = $this->entityTypeManager()->getStorage('indieweb_microsub_source')->loadByProperties(['channel_id' => $channel_id, 'status' => 1]);
		  if (!empty($sources)) {
			$source_list = [];
			foreach ($sources as $source) {

			  $unread = 0;
			  if ($include_unread) {
				// Unread can either an int, boolean or omitted.
				if (($channel = $source->getChannel()) && ($indicator = $channel->read_indicator) && $this->isAuthenticatedRequest()) {
				  if ($indicator == 1) {
					$unread = (int) $source->getUnreadCount(true);
				  }
				  elseif ($indicator == 2) {
					$unread = (bool) $source->getUnreadCount(false);
				  }
				}
			  }

			  $source_list[] = (object) [
				'type' => 'feed',
				'uid' => $source->id(),
				'url' => $source->label(),
				'name' => !empty($source->getName()) ? $source->getName() : "",
				'last_update' => $source->getChanged() ? \Drupal::service('date.formatter')->format($source->getChanged(), 'custom', 'Y-m-d\TH:i:s') : NULL,
				'unread' => $unread,
			  ];
			}
			$return = ['response' => (object) ['items' => $source_list], 'code' => 200];
		  }
		}

		return $return;
	  }

	  /**
	   * Search.
	   *
	   * This either searches for posts, or for new feeds to subscribe to.
	   *
	   * @return array
	   *
	   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
	   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
	   */
	  protected function search() {
		$return = ['response' => [], 'code' => 400];

		$channel = NULL;
		$query = $this->request->get('query');

		// Search for posts, but only in a POST request.
		if ($this->request->getMethod() == 'POST') {
		  $channel = $this->request->get('channel');

		  // Notifications is stored as channel 0.
		  if ($channel == 'notifications') {
			$channel = 0;
		  }
		}

		if (!empty($query)) {

		  // Search for posts.
		  if ($channel || $channel === 0) {
			$return = $this->getTimeline($query);
		  }

		  // Search for feeds.
		  else {
			/** @var \Drupal\indieweb_microsub\MicrosubClient\MicrosubClientInterface $microsubClient */
			$microsubClient = \Drupal::service('indieweb.microsub.client');
			$feeds = $microsubClient->searchFeeds($query);
			if (!empty($feeds['feeds'])) {
			  $result_list = [];
			  foreach ($feeds['feeds'] as $feed) {
				$result_list[] = (object) [
				  'type' => $feed['type'],
				  'url' => $feed['url'],
				];
			  }
			  $return = ['response' => (object) ['results' => $result_list], 'code' => 200];
			}
		  }

		}

		return $return;
	  }

	  /**
	   * Preview url.
	   *
	   * @return array
	   */
	  protected function previewUrl() {
		$return = ['response' => [], 'code' => 400];

		$url = $this->request->get('url');
		if (!empty($url)) {
		  try {
			$xray = new XRay();
			$options = ['headers' => ['User-Agent' => indieweb_microsub_http_client_user_agent()]];
			$response = \Drupal::httpClient()->get($url, $options);
			$body = $response->getBody()->getContents();
			$parsed = $xray->parse($url, $body, ['expect' => 'feed']);
			if ($parsed && isset($parsed['data']['type']) && $parsed['data']['type'] == 'feed') {
			  $return = ['response' => (object) ['items' => $parsed['data']['items']], 'code' => 200];
			}
		  }
		  catch (\Exception $e) {
			\Drupal::logger('indieweb_microsub')->notice('Error fetching preview for @url : @message', ['@url' => $url, '@message' => $e->getMessage()]);
		  }
		}

		return $return;
	  }

	  /**
	   * Removes a microsub item
	   *
	   * @return array
	   *
	   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
	   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
	   */
	  protected function removeItem() {

		$entry_id = $this->request->get('entry');
		if ($entry_id) {
		 $this->entityTypeManager()->getStorage('indieweb_microsub_item')->removeItem($entry_id);
		}

		return ['response' => [], 'code' => 200];
	  }

	  /**
	   * Moves a microsub item.
	   *
	   * @return array
	   *
	   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
	   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
	   */
	  protected function moveItem() {

		$channel_id = $this->request->get('channel');

		// Notifications is stored as channel 0.
		if ($channel_id == 'notifications') {
		  $channel_id = 0;
		}

		$entries = $this->request->get('entry');
		if ($entries && ($channel_id || $channel_id === 0)) {
		  $this->entityTypeManager()->getStorage('indieweb_microsub_item')->moveItem($entries, $channel_id);
		}

		return ['response' => [], 'code' => 200];
	  }

	  /**
	   * Microsub channel overview.
	   *
	   * @return array
	   */
	  public function channelOverview() {
		return $this->entityTypeManager()->getListBuilder('indieweb_microsub_channel')->render();
	  }

	  /**
	   * Microsub sources overview.
	   *
	   * @param \Drupal\indieweb_microsub\Entity\MicrosubChannelInterface $indieweb_microsub_channel
	   *
	   * @return array
	   */
	  public function sourcesOverview(MicrosubChannelInterface $indieweb_microsub_channel) {
		return $this->entityTypeManager()->getListBuilder('indieweb_microsub_source')->render($indieweb_microsub_channel);
	  }

	  /**
	   * Reset fetch next time for a source.
	   *
	   * @param \Drupal\indieweb_microsub\Entity\MicrosubSourceInterface $indieweb_microsub_source
	   *
	   * @return \Symfony\Component\HttpFoundation\RedirectResponse
	   *
	   * @throws \Drupal\Core\Entity\EntityStorageException
	   */
	  public function resetNextFetch(MicrosubSourceInterface $indieweb_microsub_source) {
		$indieweb_microsub_source->setNextFetch(0)->save();
		$this->messenger()->addMessage($this->t('Next update reset for %source', ['%source' => $indieweb_microsub_source->label()]));
		return new RedirectResponse(Url::fromRoute('indieweb.admin.microsub_sources', ['indieweb_microsub_channel' => $indieweb_microsub_source->getChannelId()])->toString());
	  }
}
