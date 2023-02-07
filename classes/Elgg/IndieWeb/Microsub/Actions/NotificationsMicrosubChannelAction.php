<?php

namespace Elgg\IndieWeb\Microsub\Actions;

use Elgg\IndieWeb\Microsub\Entity\MicrosubChannel;

class NotificationsMicrosubChannelAction {
	
	public function __invoke(\Elgg\Request $request) {
		elgg_make_sticky_form('microsub/channel/notifications');

		$entity = new MicrosubChannel();

		$entity->title = elgg_echo('indieweb:microsub_channel:notifications');
		$entity->status = 1;
		$entity->read_indicator = 1;
		$entity->weight = 0;
		$entity->channel_id = 0;
		$entity->uid = 'notifications';
		
		$entity->owner_guid = elgg_get_site_entity()->guid;
		$entity->container_guid = elgg_get_site_entity()->guid;

		if (!$entity->save()) {
			return elgg_error_response(elgg_echo('save:fail'));
		}

		elgg_clear_sticky_form('microsub/channel/notifications');

		return elgg_ok_response('', elgg_echo('save:success'));
		
	}
}

