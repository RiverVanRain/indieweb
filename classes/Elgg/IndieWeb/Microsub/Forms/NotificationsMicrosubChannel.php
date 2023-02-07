<?php

namespace Elgg\IndieWeb\Microsub\Forms;

use Elgg\IndieWeb\Microsub\Entity\MicrosubChannel;

class NotificationsMicrosubChannel {
	
	/**
	 * @var MicrosubChannel entity being edited
	 */
	protected $entity;
	protected $container_guid;
	
	public function __construct(MicrosubChannel $entity = null, $container_guid = null) {
		$this->entity = $entity;
		$this->container_guid = $container_guid;
	}
	
	public function __invoke() {
		
		$result = [
			'title' => elgg_echo('indieweb:microsub_channel:notifications'),
			'status' => 1,
			'read_indicator' => 1,
			'weight' => 0,
			'channel_id' => 0,
			'uid' =>'notifications',
		];
		
		if(!empty($this->container_guid)) {
			$result['container_guid'] = $this->container_guid;
		}
		
		// sticky form
		$sticky = elgg_get_sticky_values('microsub/channel/notifications');
		if (!empty($sticky)) {
			foreach ($sticky as $key => $value) {
				$result[$key] = $value;
			}
			
			elgg_clear_sticky_form('microsub/channel/notifications');
		}
		
		return $result;
	}
}
