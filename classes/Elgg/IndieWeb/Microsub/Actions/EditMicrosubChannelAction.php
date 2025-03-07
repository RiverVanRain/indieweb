<?php

namespace Elgg\IndieWeb\Microsub\Actions;

use Elgg\IndieWeb\Microsub\Entity\MicrosubChannel;

class EditMicrosubChannelAction
{
    public function __invoke(\Elgg\Request $request)
    {
        elgg_make_sticky_form('microsub/channel/edit');

        $guid = (int) $request->getParam('guid');
        $title = $request->getParam('title');
        $status = (bool) $request->getParam('status', 1);
        $read_indicator = $request->getParam('read_indicator', 1);
        $weight = (int) $request->getParam('weight', 0);

        $exclude_post_type = [];

        $objects = [
            'reply',
            'repost',
            'bookmark',
            'like',
            'note',
            'article',
            'photo',
            'video',
            'checkin',
            'rsvp',
        ];

        foreach ($objects as $subtype) {
            $exclude_post_type["microsub_channel:exclude_post_type:$subtype"] = (bool) $request->getParam("microsub_channel:exclude_post_type:$subtype", 0);
        }

        if (empty($title)) {
            return elgg_error_response(elgg_echo('error:missing_data'));
        }

        if (!empty($guid)) {
            $entity = get_entity($guid);
            if (!$entity instanceof MicrosubChannel) {
                throw new \Elgg\Exceptions\Http\EntityNotFoundException();
            }

            if (!$entity->canEdit()) {
                throw new \Elgg\Exceptions\Http\EntityPermissionsException();
            }
        } else {
            $entity = new MicrosubChannel();
        }

        $entity->title = $title;
        $entity->status = $status;
        $entity->read_indicator = $read_indicator;
        $entity->weight = $weight;

        foreach ($exclude_post_type as $key => $value) {
            $entity->$key = $value;
        }

        $entity->owner_guid = elgg_get_site_entity()->guid;
        $entity->container_guid = elgg_get_site_entity()->guid;
        $entity->access_id = ACCESS_PUBLIC;

        if (!$entity->save()) {
            return elgg_error_response(elgg_echo('save:fail'));
        }

        $entity->setMetadata('channel_id', $entity->guid);

        elgg_clear_sticky_form('microsub/channel/edit');

        return elgg_ok_response('', elgg_echo('save:success'));
    }
}
