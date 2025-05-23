<?php

/**
 * IndieWeb
 * @author Nikolai Shcherbin
 * @license GNU Affero General Public License version 3
 * @copyright (c) Nikolai Shcherbin 2022
 * @link https://wzm.me
**/

namespace Elgg\IndieWeb\Microsub\Controller;

use Elgg\Request;
use Elgg\IndieWeb\Microsub\Entity\MicrosubChannel;
use Elgg\IndieWeb\Microsub\Entity\MicrosubSource;
use Elgg\IndieWeb\Microsub\Entity\MicrosubItem;

class MicrosubController
{
    /**
     * Whether this is an authenticated request or not.
     *
     * @var bool
     */
    protected $isAuthenticatedRequest = false;

    /**
     * Whether anonymous requests on the Microsub endpoint are allowed or not.
     *
     * This allows getting channels and the posts in that channel. Write
     * operations (like managing channels, subscribing, search, marking (un)read
     * etc) will not be allowed when enabled and the request is anonymous.
     *
     * @return boolean
     */
    private function allowAnonymousRequest(): bool
    {
        return elgg_get_plugin_setting('microsub_anonymous', 'indieweb');
    }

    /**
     * Whether this is an authenticated request or not.
     *
     * @return bool
     */
    private function isAuthenticatedRequest()
    {
        return $this->isAuthenticatedRequest;
    }

    /**
     * Search feeds based on URL.
     *
     */
    public function searchFeeds(Request $request)
    {
        $results = [];

        // Get the typed string from the URL, if it exists.
        if (($input = $request->getParam('q')) && strlen($input) > 6) {
            // Add a protocol if needed.
            if ($parts = parse_url($input)) {
                if (!isset($parts["scheme"])) {
                    $input = "http://$input";
                }
            }

            if (filter_var($input, filter_validate_url) !== false) {

                /** @var \Elgg\IndieWeb\Microsub\Client\MicrosubClient $microsubClient */
                $microsubClient = elgg()->microsub;
                $feeds = $microsubClient->searchFeeds($input);

                if (!empty($feeds['feeds'])) {
                    foreach ($feeds['feeds'] as $feed) {
                        $results[] = [
                            'value' => $feed['url'],
                            'label' => $feed['url'] . ' (' . $feed['type'] . ')',
                        ];
                    }
                }
            }
        }

        return elgg_ok_response($results);
    }

    /**
     * Routing callback: internal webmention endpoint.
     */
    public function __invoke(Request $request)
    {
        if (!(bool) elgg_get_plugin_setting('enable_microsub', 'indieweb')) {
            throw new \Elgg\Exceptions\Http\PageNotFoundException();
        }

        if (!empty(elgg_get_plugin_setting('microsub_endpoint', 'indieweb'))) {
            throw new \Elgg\Exceptions\Http\BadRequestException();
        }

        elgg_set_http_header('Link: <' . elgg_generate_url('default:view:microsub') . '>; rel="microsub"');

        // Default response code and message.
        $response_code = 400;
        $response_message = 'Bad request';

        // Determine scope.
        $scope = null;
        $request_method = $request->getMethod();
        $action = $request->getParam('action');

        if ($action === 'channels' && $request_method === 'POST') {
            $scope = 'channels';
        } elseif (in_array($action, ['follow', 'unfollow', 'search', 'preview'])) {
            $scope = 'follow';
        } elseif ($action == 'channels' || $action == 'timeline') {
            $scope = 'read';
        }

        // Get authorization header, response early if none found.
        $auth_header = elgg()->indieauth->getAuthorizationHeader($request->getHttpRequest());
        if (!$auth_header) {
            // Check anonymous requests.
            if ($this->allowAnonymousRequest() && $scope === 'read' && $request_method === 'GET' && in_array($action, ['channels', 'timeline'])) {
                switch ($action) {
                    case 'channels':
                        $response = $this->getChannelList($request);
                        break;
                    case 'timeline':
                        $response = $this->getTimeline($request);
                        break;
                }
                $response_message = $response->getContent() ? : '';
                $response_code = (int) $response->getStatusCode() ? : 200;
            }

            return elgg_ok_response($response_message, '', REFERRER, $response_code);
        }

        // Validate token.
        if (!elgg()->indieauth->isValidToken($auth_header, $scope)) {
            return elgg_error_response('No Valid Token', REFERRER, 403);
        }

        // If we get to here, this is an authenticated request.
        $this->isAuthenticatedRequest = true;

        // GET actions.
        if ($request_method === 'GET') {
            switch ($action) {
                case 'channels':
                    $response = $this->getChannelList($request);
                    break;

                case 'timeline':
                    $response = $this->getTimeline($request);
                    break;

                case 'follow':
                    $response = $this->getSources($request);
                    break;

                case 'search':
                    $response = $this->search($request);
                    break;

                case 'preview':
                    $response = $this->previewUrl($request);
                    break;
            }
        }

        // POST actions.
        if ($request_method === 'POST') {
            switch ($action) {
                // Channels
                case 'channels':
                    $method = $request->getParam('method');
                    if (!$method) {
                        $method = 'create';
                        if ($id = $request->getParam('channel')) {
                            $method = 'update';
                        }
                    }

                    if ($method === 'create') {
                        $response = $this->createChannel($request);
                    }

                    if ($method === 'update') {
                        $response = $this->updateChannel($request);
                    }

                    if ($method === 'order') {
                        $response = $this->orderChannels($request);
                    }

                    if ($method === 'delete') {
                        $response = $this->deleteChannel($request);
                    }
                    break;

                // Timeline
                case 'timeline':
                    $method = $request->getParam('method');
                    if (in_array($method, ['mark_read', 'mark_unread'])) {
                        $status = $method == 'mark_read' ? 1 : 0;
                        $response = $this->timelineChangeReadStatus($request, $status);
                    }

                    if ($method === 'remove') {
                        $response = $this->removeItem();
                    }

                    if ($method === 'move') {
                        $response = $this->moveItem();
                    }
                    break;

                // Follow, Unfollow, Search and Preview
                case 'follow':
                    $response = $this->followSource($request);
                    break;

                case 'unfollow':
                    $response = $this->deleteSource($request);
                    break;

                case 'search':
                    $response = $this->search($request);
                    break;

                case 'preview':
                    $response = $this->previewUrl($request);
                    break;
            }
        }

        $response_message = $response->getContent() ?: '';
        $response_code = (int) $response->getStatusCode() ?: 200;

        return elgg_ok_response($response_message, '', REFERRER, $response_code);
    }

    /**
     * Handle channels request.
     *
     * @return array $response
    */
    public function getChannelList(Request $request)
    {
        $channels = [];

        $tree = $request->getParam('method') === 'tree';

        $channels_list = elgg_get_entities([
            'type' => 'object',
            'subtype' => MicrosubChannel::SUBTYPE,
            'metadata_name_value_pairs' => [
                [
                    'name' => 'status',
                    'value' => 1 ?? true,
                ],
            ],
            'limit' => 0,
            'batch' => true,
            'batch_size' => 50,
            'batch_inc_offset' => false,
        ]);

        $notifications = 0;

        // Notifications channel
        if ($this->isAuthenticatedRequest()) {
            $notifications = elgg_count_entities([
                'type' => 'object',
                'subtype' => MicrosubItem::SUBTYPE,
                'limit' => false,
                'batch' => true,
                'batch_size' => 50,
                'batch_inc_offset' => false,
                'metadata_name_value_pairs' => [
                    [
                        'name' => 'channel_id',
                        'value' => 0,
                    ],
                    [
                        'name' => 'is_read',
                        'value' => 0,
                    ],
                ],
            ]);

            $channels[] = [
                'uid' => 'notifications',
                'name' => elgg_echo('indieweb:microsub_channel:notifications'),
                'unread' => (int) $notifications,
            ];
        }

        foreach ($channels_list as $channel) {
            $channels[] = [
                'uid' => $channel->guid,
                'name' => $channel->title,
            ];

            $unread = $sources = [];

            // Unread can either an int, boolean or omitted.
            if (($indicator = $channel->read_indicator) && $this->isAuthenticatedRequest()) {
                if ($indicator === 1) {
                    $unread['unread'] = (int) $channel->getUnreadCount(true);
                } elseif ($indicator === 2) {
                    $unread['unread'] = (bool) $channel->getUnreadCount(false);
                }
            } else {
                $unread['unread'] = 0;
            }

            if ($tree) {
                $channel_sources = $this->getSources($request, $channel->guid, true)->getContent();
                if (!empty($channel_sources['response']->items)) {
                    $sources = ['sources' => $channel_sources['response']->items];
                }
            }

            $channels[] = $unread + $sources;
        }

        return elgg_ok_response(['channels' => $channels]);
    }

    /**
     * Handle timeline request.
     *
     * @param $search
     *  Searches in posts.
     *
     * @return array $response
    */
    public function getTimeline(Request $request, $search = null)
    {
        return elgg()->microsub->getTimeline($request, $this->isAuthenticatedRequest(), $search);
    }

    /**
     * Create a channel.
     *
     * @return array
    */
    public function createChannel(Request $request)
    {
        $return = [];
        $response_code = 400;

        $name = $request->getParam('name');
        if (!empty($name)) {
            $uid = 1;

            if ($token_uid = elgg()->indieauth->checkAuthor()) {
                $uid = $token_uid;
            }

            elgg_call(ELGG_IGNORE_ACCESS, function () use ($name, $uid) {
                $entity = new MicrosubChannel();

                $entity->title = $name;
                $entity->status = 1;
                $entity->read_indicator = 1;
                $entity->weight = 0;
                $entity->uid = $uid;

                $entity->owner_guid = elgg_get_site_entity()->guid;
                $entity->container_guid = elgg_get_site_entity()->guid;
                $entity->access_id = ACCESS_PUBLIC;

                if ($entity->save()) {
                    $return = [
                        'uid' => $entity->guid,
                        'name' => $entity->title
                    ];
                    $response_code = 200;
                }
            });
        }

        return elgg_ok_response($return, '', REFERRER, $response_code);
    }

    /**
     * Updates a channel.
     *
     * @return array
    */
    public function updateChannel(Request $request)
    {
        $return = [];
        $response_code = 400;

        $guid = $request->getParam('channel');
        $title = $request->getParam('name');

        if (!empty($title) && !empty($guid)) {
            $channel = get_entity($guid);
            if ($channel instanceof MicrosubChannel) {
                $channel->title = $title;
                $return = [
                    'uid' => $channel->guid,
                    'name' => $channel->title
                ];
                $response_code = 200;
            }
        }

        return elgg_ok_response($return, '', REFERRER, $response_code);
    }

    /**
     * Deletes a channel.
     *
     * @return array
    */
    public function deleteChannel(Request $request)
    {
        $return = [];
        $response_code = 400;

        $id = $request->getParam('channel');

        $entiny = get_entity($id);

        if ($entiny instanceof MicrosubChannel) {
            $entiny->delete();
            $response_code = 200;
        }

        return elgg_ok_response($return, '', REFERRER, $response_code);
    }

    /**
     * Orders channels.
     *
     * @return array
     *
    */
    public function orderChannels(Request $request)
    {
        $return = [];
        $response_code = 400;

        $ids = $request->getParam('channels');

        if (!empty($ids)) {
            $weight = -20;
            ksort($ids);
            foreach ($ids as $id) {
                $channel = get_entity($id);
                if (!$channel instanceof MicrosubChannel) {
                    continue;
                }

                $channel->weight = $weight;
                $channel->save();
                $weight++;
            }

            $response_code = 200;
        }

        return elgg_ok_response($return, '', REFERRER, $response_code);
    }

    /**
     * Mark items (un)read for a channel.
     *
     * @param int $status
     *   The status.
     *
     * @return array
    */
    public function timelineChangeReadStatus(Request $request, $status)
    {
        $channel_id = $request->getParam('channel');

        // Notifications is stored as channel 0.
        if ($channel_id === 'notifications') {
            $channel_id = 0;
        }

        // Check entry or last_read_entry. If last_read_entry is passed in, we
        // completely ignore entries, this usually just means 'Mark all as read'.
        $entries = $request->getParam('entry'); //microsub_item
        if ($channel_id !== 'global') {
            $last_read_entry = $request->getParam('last_read_entry');

            if (!empty($last_read_entry)) {
                $entries = null;
            }
        }

        if (($channel_id || $channel_id === 0) && !empty($entries)) {
            if (!is_array($entries)) {
                $entries = [$entries];
            }

            $items = elgg_get_entities([
                'type' => 'object',
                'subtype' => MicrosubItem::SUBTYPE,
                'limit' => false,
                'batch' => true,
                'batch_size' => 50,
                'batch_inc_offset' => false,
                'metadata_name_value_pairs' => [
                    [
                        'name' => 'channel_id',
                        'value' => $channel_id,
                    ],
                    [
                        'name' => 'id',
                        'value' => $entries,
                        'operand' => 'IN',
                    ],
                ],
            ]);

            foreach ($items as $item) {
                $item->setMetadata('is_read', $status);
            }
        }

        return elgg_ok_response('');
    }

    /**
     * Follow or update a source.
     *
     * @return array
     *
    */
    public function followSource(Request $request)
    {
        $return = [];
        $response_code = 400;

        $url = $request->getParam('url');
        $channel_id = $request->getParam('channel');
        $method = $request->getParam('method');

        if (!empty($channel_id) && !empty($url)) {
            $channel = get_entity($channel_id);

            if ($channel instanceof MicrosubChannel) {
                $uid = 1;

                if ($token_uid = elgg()->indieauth->checkAuthor()) {
                    $uid = $token_uid;
                }

                if ($method === 'update') {
                    $sources = elgg_get_entities([
                        'type' => 'object',
                        'subtype' => MicrosubSource::SUBTYPE,
                        'limit' => 1,
                        'metadata_name_value_pairs' => [
                            [
                                'name' => 'url',
                                'value' => $url,
                            ],
                        ],
                    ]);

                    if (!empty($sources)) {
                        $source = array_shift($sources);
                        $source->setMetadata('channel_id', $channel_id);
                    }
                } else {
                    // Save MicrosubItem
                    elgg_call(ELGG_IGNORE_ACCESS, function () use ($channel, $channel_id, $uid, $url) {
                        $source = new MicrosubSource();
                        $source->owner_guid = elgg_get_site_entity()->guid;
                        $source->container_guid = $channel->guid;
                        $source->access_id = ACCESS_PUBLIC;
                        $source->channel_id = $channel_id;
                        $source->uid = $uid;
                        $source->url = $url;
                        $source->fetch_interval = 86400;
                        $source->items_to_keep = 20;
                        $source->status = 1;

                        $source->save();
                    });

                    // WIP
                    // Send subscribe request.
                    if ((bool) elgg_get_plugin_setting('enable_websub', 'indieweb') && (bool) elgg_get_plugin_setting('websub_microsub_subscribe', 'indieweb')) {
                        $websub_service = elgg()->websub;

                        if ($info = $websub_service->discoverHub($source->label())) {
                            $source->set('url', $info['self'])->save();
                            $websub_service->subscribe($info['self'], $info['hub'], 'subscribe');
                        }
                    }
                }

                $return = [
                    'type' => 'feed',
                    'url' => $url
                ];

                $response_code = 200;
            }
        }

        return elgg_ok_response($return, '', REFERRER, $response_code);
    }

    /**
     * Delete a source.
     *
     * @return array
     *
    */
    public function deleteSource(Request $request)
    {
        $return = [];
        $response_code = 400;

        $url = $request->getParam('url');
        $channel_id = $request->getParam('channel');

        if (!empty($channel_id) && !empty($url)) {
            $sources = elgg_get_entities([
                'type' => 'object',
                'subtype' => MicrosubSource::SUBTYPE,
                'limit' => 1,
                'metadata_name_value_pairs' => [
                    [
                        'name' => 'url',
                        'value' => $url,
                    ],
                    [
                        'name' => 'channel_id',
                        'value' => $channel_id,
                    ],
                ],
            ]);

            if (!empty($sources)) {
                $source = array_shift($sources);
                $source->delete();

                // WIP
                // Send unsubscribe request.
                if ((bool) elgg_get_plugin_setting('enable_websub', 'indieweb') && (bool) elgg_get_plugin_setting('websub_microsub_subscribe', 'indieweb')) {
                    if ($source->usesWebSub()) {
                        $websub_service = elgg()->websub;

                        if ($info = $websub_service->discoverHub($source->label())) {
                            $websub_service->subscribe($info['self'], $info['hub'], 'unsubscribe');
                        }
                    }
                }

                $response_code = 200;
            }
        }

        return elgg_ok_response($return, '', REFERRER, $response_code);
    }

    /**
     * Get sources.
     *
     * @param null $channel_id
     * @param bool $include_unread
     *
     * @return array
     *
    */
    public function getSources(Request $request, int $channel_id = 0, bool $include_unread = false)
    {
        $return = [];
        $response_code = 400;

        if (!isset($channel_id)) {
            $channel_id = $request->getParam('channel');
        }

        if (!empty($channel_id)) {
            $sources = elgg_get_entities([
                'type' => 'object',
                'subtype' => MicrosubSource::SUBTYPE,
                'limit' => false,
                'metadata_name_value_pairs' => [
                    [
                        'name' => 'status',
                        'value' => 1 ?? true,
                    ],
                    [
                        'name' => 'channel_id',
                        'value' => $channel_id,
                    ],
                ],
                'batch' => true,
                'batch_size' => 50,
                'batch_inc_offset' => false,
            ]);

            if (!empty($sources)) {
                $source_list = [];

                foreach ($sources as $source) {
                    $unread = 0;

                    if ($include_unread) {
                        // Unread can either an int, boolean or omitted.
                        if (($channel = $source->getContainerEntity()) && ($indicator = $channel->read_indicator) && $this->isAuthenticatedRequest()) {
                            if ($indicator === 1) {
                                $unread = (int) $source->getUnreadCount(true);
                            } elseif ($indicator === 2) {
                                $unread = (bool) $source->getUnreadCount(false);
                            }
                        }
                    }

                    $source_list[] = [
                        'type' => 'feed',
                        'uid' => $source->id,
                        'url' => $source->url,
                        'name' => $source->title,
                        'last_update' => $source->getChanged() ? date('Y-m-d H:i:s', $source->getChanged()) : null,
                        'unread' => $unread,
                    ];
                }

                $return = [
                    'items' => $source_list
                ];

                $response_code = 200;
            }
        }

        return elgg_ok_response($return, '', REFERRER, $response_code);
    }

    /**
     * Search.
     *
     * This either searches for posts, or for new feeds to subscribe to.
     *
     * @return array
     *
    */
    public function search(Request $request)
    {
        $return = [];
        $response_code = 400;

        $channel = null;
        $query = $request->getParam('query');

        // Search for posts, but only in a POST request.
        if ($request->getMethod() === 'POST') {
            $channel = $request->getParam('channel');

            // Notifications is stored as channel 0.
            if ($channel === 'notifications') {
                $channel = 0;
            }
        }

        if (!empty($query)) {
            // Search for posts.
            if ($channel || $channel === 0) {
                $return = $this->getTimeline($request, $query)->getContent();
                $response_code = 200;
            }

            // Search for feeds.
            else {
                $microsub_client = elgg()->microsub;

                $feeds = $microsub_client->searchFeeds($query);

                if (!empty($feeds['feeds'])) {
                    $result_list = [];

                    foreach ($feeds['feeds'] as $feed) {
                        $result_list[] = [
                            'type' => $feed['type'],
                            'url' => $feed['url'],
                        ];
                    }

                    $return = ['results' => $result_list];
                    $response_code = 200;
                }
            }
        }

        return elgg_ok_response($return, '', REFERRER, $response_code);
    }

    /**
     * Preview url.
     *
     * @return array
    */
    public function previewUrl(Request $request)
    {
        $return = [];
        $response_code = 400;

        $url = $request->getParam('url');

        if (!empty($url)) {
            try {
                $xray = new XRay();

                $options = ['headers' => ['User-Agent' => indieweb_microsub_http_client_user_agent()]];
                $response = elgg()->httpClient->setup()->get($url, $options);
                $body = $response->getBody()->getContents();

                $parsed = $xray->parse($url, $body, ['expect' => 'feed']);

                if ($parsed && isset($parsed['data']['type']) && $parsed['data']['type'] === 'feed') {
                    $return = ['items' => $parsed['data']['items']];
                    $response_code = 200;
                }
            } catch (\Exception $e) {
                elgg_log('Error fetching preview for ' . $url  . ' : ' . $e->getMessage(), \Psr\Log\LogLevel::ERROR);
                return false;
            }
        }

        return elgg_ok_response($return, '', REFERRER, $response_code);
    }

    /**
     * Removes a microsub item
     *
     * @return array
     *
    */
    public function removeItem(Request $request)
    {
        $entry_id = $request->getParam('entry');

        if ($entry_id) {
            $item = elgg_get_entities([
                'type' => 'object',
                'subtype' => MicrosubItem::SUBTYPE,
                'limit' => 1,
                'metadata_name_value_pairs' => [
                    [
                        'name' => 'id',
                        'value' => $entry_id,
                    ],
                ],
            ]);

            $item->delete();
        }

        return elgg_ok_response('');
    }

    /**
     * Moves a microsub item.
     *
     * @return array
     *
    */
    public function moveItem(Request $request)
    {
        $channel_id = $request->getParam('channel');

        // Notifications is stored as channel 0.
        if ($channel_id === 'notifications') {
            $channel_id = 0;
        }

        $entries = $request->getParam('entry');

        if (($channel_id || $channel_id === 0) && !empty($entries)) {
            if (!is_array($entries)) {
                $entries = [$entries];
            }

            elgg_call(ELGG_IGNORE_ACCESS, function () use ($channel_id, &$entries) {
                $items = elgg_get_entities([
                    'type' => 'object',
                    'subtype' => MicrosubItem::SUBTYPE,
                    'limit' => false,
                    'batch' => true,
                    'batch_inc_offset' => false,
                    'metadata_name_value_pairs' => [
                        [
                            'name' => 'id',
                            'value' => $entries,
                            'operand' => 'IN',
                        ],
                    ],
                ]);

                $notifications = elgg_get_entities([
                    'type' => 'object',
                    'subtype' => MicrosubChannel::SUBTYPE,
                    'limit' => 1,
                    'metadata_name_value_pairs' => [
                        [
                            'name' => 'channel_id',
                            'value' => 0,
                        ],
                    ],
                    'callback' => function ($row) {
                        return $row->guid;
                    },
                ]);

                foreach ($items as $item) {
                    $item->setMetadata('channel_id', $channel_id);

                    $source = $item->getContainerEntity();

                    if ($channel_id === 0) {
                        $source->container_guid = $notifications;
                    } else {
                        $source->container_guid = $channel_id;
                    }

                    $source->save();
                }
            });
        }

        return elgg_ok_response('');
    }
}
