<?php
/**
 * IndieWeb
 * @author Nikolai Shcherbin
 * @license GNU Affero General Public License version 3
 * @copyright (c) Nikolai Shcherbin 2022
 * @link https://wzm.me
**/

namespace Elgg\IndieWeb\Microsub\Entity;

class MicrosubSource extends \ElggObject {

	const SUBTYPE = 'microsub_source';

	/**
	 * {@inheritdoc}
	 */
	protected function initializeAttributes() {
		parent::initializeAttributes();
		$this->attributes['subtype'] = self::SUBTYPE;
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function getStatus(): bool {
		return $this->status;
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function getHash() {
		return $this->hash;
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function getInterval() {
		return $this->fetch_interval;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getItemsInFeed(): int {
		return $this->items_in_feed ?? 0;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getKeepItemsInFeed(): int {
		return $this->items_to_keep ?? 0;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getNextFetch() {
		return $this->fetch_next;
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function setNextFetch($next_fetch = null) {
		if (!isset($next_fetch)) {
			$next_fetch = time() + $this->getInterval();
		}
		$this->fetch_next = (int) $next_fetch;
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getChanged() {
		return $this->changed;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getTries() {
		return $this->fetch_tries;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getPostContext(): array {
		$return = [];
		
		$objects = [
			'reply',
			'repost',
			'bookmark',
			'like',
		];
		
		foreach ($objects as $subtype) {
			if ((bool) $this->{"microsub_source:post_context:$subtype"}) {
				$return[] = $subtype;
			}
		}
		
		return $return;
	}
	  
	/**
	 * {@inheritdoc}
	 */
	public function getUnreadCount($count = true) {
		$options = [
			'type' => 'object',
			'subtype' => \Elgg\IndieWeb\Microsub\Entity\MicrosubItem::SUBTYPE,
			'limit' => false,
			'batch' => true,
			'batch_inc_offset' => false,
			'metadata_name_value_pairs' => [
				[
					'name' => 'source_id',
					'value' => $this->guid,
				],
				[
					'name' => 'status',
					'value' => 1,
				],
				[
					'name' => 'is_read',
					'value' => 0,
				],
			],
			'count' => $count,
		];
		
		return elgg_get_entities($options);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getItemCount() {
		$options = [
			'type' => 'object',
			'subtype' => \Elgg\IndieWeb\Microsub\Entity\MicrosubItem::SUBTYPE,
			'limit' => false,
			'batch' => true,
			'batch_inc_offset' => false,
			'metadata_name_value_pairs' => [
				[
					'name' => 'source_id',
					'value' => $this->guid,
				],
			],
			'count' => true,
		];
		
		return elgg_get_entities($options);
	}

	/**
	 * {@inheritdoc}
	 */
	public function usesWebSub(): bool {
		return $this->websub;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getEtag() {
		return $this->etag;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getLastModified() {
		return $this->modified;
	}
}

