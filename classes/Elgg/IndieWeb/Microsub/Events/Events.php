<?php
/**
 * IndieWeb
 * @author Nikolai Shcherbin
 * @license GNU Affero General Public License version 3
 * @copyright (c) Nikolai Shcherbin 2022
 * @link https://wzm.me
**/

namespace Elgg\IndieWeb\Microsub\Events;

use Elgg\Event;
use Elgg\Database\QueryBuilder;

/**
 * @access private
 */
final class Events {
	
	public static function deleteChannel(Event $event) {
		$entity = $event->getObject();
		
		if (!$entity instanceof \Elgg\IndieWeb\Microsub\Entity\MicrosubChannel) {
			return;
		}

		elgg_call(ELGG_IGNORE_ACCESS, function () use ($entity) {
			$sources = $entity->getSources();
			
			if (!empty($sources)) {
				foreach ($sources as $source) {
					$source->delete();
				}
			}
		});
	}
	
	public static function deleteSource(Event $event) {
		$entity = $event->getObject();
		
		if (!$entity instanceof \Elgg\IndieWeb\Microsub\Entity\MicrosubSource) {
			return;
		}

		elgg_call(ELGG_IGNORE_ACCESS, function () use ($entity) {
			$batch = elgg_get_entities([
				'type' => 'object',
				'subtype' => \Elgg\IndieWeb\Microsub\Entity\MicrosubItem::SUBTYPE,
				'wheres' => function (QueryBuilder $qb, $from_alias = 'e') use ($entity) {
					$md_alias = $qb->joinMetadataTable($from_alias, 'guid', ['source_id']);
					return $qb->compare("$md_alias.value", '=', $entity->guid, ELGG_VALUE_INTEGER);
				},
				'limit' => 0,
				'batch' => true,
				'batch_size' => 50,
				'batch_inc_offset' => false,
			]);

			if (!empty($batch)) {
				foreach ($batch as $item) {
					$item->delete();
				}
			}
		});
	}
	
	public static function updateSource(Event $event) {
		$entity = $event->getObject();
		
		if (!$entity instanceof \Elgg\IndieWeb\Microsub\Entity\MicrosubSource) {
			return;
		}
		
		elgg_call(ELGG_IGNORE_ACCESS, function () use ($entity) {
			$batch = elgg_get_entities([
				'type' => 'object',
				'subtype' => \Elgg\IndieWeb\Microsub\Entity\MicrosubItem::SUBTYPE,
				'wheres' => function (QueryBuilder $qb, $from_alias = 'e') use ($entity) {
					$md_alias = $qb->joinMetadataTable($from_alias, 'guid', ['source_id']);
					return $qb->compare("$md_alias.value", '=', $entity->guid, ELGG_VALUE_INTEGER);
				},
				'limit' => 0,
				'batch' => true,
				'batch_size' => 50,
				'batch_inc_offset' => false,
			]);

			if (!empty($batch)) {
				foreach ($batch as $item) {
					$item->channel_id = $entity->container_guid;
					$item->save();
				}
			}
		});
	}
	
}
