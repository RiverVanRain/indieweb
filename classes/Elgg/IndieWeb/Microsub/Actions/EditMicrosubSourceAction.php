<?php

namespace Elgg\IndieWeb\Microsub\Actions;

use Elgg\IndieWeb\Microsub\Entity\MicrosubChannel;
use Elgg\IndieWeb\Microsub\Entity\MicrosubSource;

class EditMicrosubSourceAction
{
    public function __invoke(\Elgg\Request $request)
    {
        elgg_make_sticky_form('microsub/source/edit');

        $guid = (int) $request->getParam('guid');
        $container_guid = $request->getParam('container_guid', 0);
        $title = $request->getParam('title');
        $url = $request->getParam('url');
        $status = (bool) $request->getParam('status', 1);
        $fetch_interval = $request->getParam('fetch_interval', 3600);
        $items_to_keep = (int) $request->getParam('items_to_keep', 0);
        $websub = (bool) $request->getParam('websub', 0);

        $post_context = [];

        $objects = [
            'reply',
            'repost',
            'bookmark',
            'like',
        ];

        foreach ($objects as $subtype) {
            $post_context["microsub_source:post_context:$subtype"] = (bool) $request->getParam("microsub_source:post_context:$subtype", 0);
        }

        if (empty($title) || empty($url)) {
            return elgg_error_response(elgg_echo('error:missing_data'));
        }

        if (!empty($guid)) {
            $entity = get_entity($guid);
            if (!$entity instanceof MicrosubSource) {
                throw new \Elgg\Exceptions\Http\EntityNotFoundException();
            }

            if (!$entity->canEdit()) {
                throw new \Elgg\Exceptions\Http\EntityPermissionsException();
            }
        } else {
            $entity = new MicrosubSource();
        }

        $channel_id = $container_guid;

        if (is_array($container_guid)) {
            $channel_id = $container_guid[0];
        }

        if ($channel_id > 0) {
            $container = get_entity($channel_id);
            if (!$container instanceof MicrosubChannel) {
                throw new \Elgg\Exceptions\Http\EntityNotFoundException();
            }
        } else {
            return elgg_error_response(elgg_echo('error:missing_data'));
        }

        $entity->title = $title;
        $entity->url = $url;
        $entity->status = $status;
        $entity->fetch_interval = $fetch_interval;
        $entity->items_to_keep = $items_to_keep;
        $entity->websub = $websub;
        $entity->channel_id = $container->guid;
        $entity->uid = 1;

        foreach ($post_context as $key => $value) {
            $entity->$key = $value;
        }

        if (empty($guid)) {
            $entity->setNextFetch();
        }

        $entity->owner_guid = elgg_get_site_entity()->guid;
        $entity->container_guid = $container->guid;
        $entity->access_id = ACCESS_PUBLIC;

        if (!$entity->save()) {
            return elgg_error_response(elgg_echo('save:fail'));
        }

        elgg_clear_sticky_form('microsub/source/edit');

        return elgg_ok_response('', elgg_echo('save:success'));
    }
}
