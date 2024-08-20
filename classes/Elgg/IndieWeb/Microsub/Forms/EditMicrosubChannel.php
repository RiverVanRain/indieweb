<?php

namespace Elgg\IndieWeb\Microsub\Forms;

use Elgg\IndieWeb\Microsub\Entity\MicrosubChannel;

class EditMicrosubChannel {
	
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
			'title' => '',
			'status' => 1,
			'read_indicator' => 1,
			'weight' => 0,
		];
		
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
			$result["microsub_channel:exclude_post_type:$subtype"] = 0;
		}
		
		// edit
		if ($this->entity instanceof MicrosubChannel) {
			foreach ($result as $key => $value) {
				$result[$key] = $this->entity->$key;
			}
			
			$result['entity'] = $this->entity;
		}
		
		if (!empty($this->container_guid)) {
			$result['container_guid'] = $this->container_guid;
		}
		
		// sticky form
		$sticky = elgg_get_sticky_values('microsub/channel/edit');
		if (!empty($sticky)) {
			foreach ($sticky as $key => $value) {
				$result[$key] = $value;
			}
			
			elgg_clear_sticky_form('microsub/channel/edit');
		}
		
		return $result;
	}
}
