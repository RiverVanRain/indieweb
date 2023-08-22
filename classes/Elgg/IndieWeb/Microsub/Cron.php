<?php
/**
 * IndieWeb
 * @author Nikolai Shcherbin
 * @license GNU Affero General Public License version 3
 * @copyright (c) Nikolai Shcherbin 2022
 * @link https://wzm.me
**/

namespace Elgg\IndieWeb\Microsub;

use Elgg\Event;
use Elgg\IndieWeb\Microsub\Entity\MicrosubSource;

class Cron {
	//15 min
	public static function fifteenminFeedUpdate(Event $event) {
		if (!(bool) elgg_get_plugin_setting('enable_microsub', 'indieweb')) {
			return;
		}
		
		echo "Processes Feed Update starting" . PHP_EOL;
		elgg_log("Processes Feed Update starting", 'NOTICE');
		
		// ignore access
		elgg_call(ELGG_IGNORE_ACCESS, function() {
			$sources = elgg_get_entities([
                'type' => 'object',
                'subtype' => MicrosubSource::SUBTYPE,
                'limit' => false,
                'batch' => true,
                'batch_inc_offset' => false,
				'metadata_name_value_pairs' => [
					[
						'name' => 'status',
						'value' => 1,
					],
					[
						'name' => 'fetch_interval',
						'value' => 900,
					],
				],
            ]);
			
			if (empty($sources)) {
				return true;
			}
			
			foreach ($sources as $source) {
				elgg()->microsub->fetchItems($source->url);
			}

		// restore access
		});
		
		echo "Finished Feed Update processing" . PHP_EOL;
		elgg_log("Finished Feed Update processing", 'NOTICE');
	}
	
	//30 min
	public static function halfhourFeedUpdate(Event $event) {
		if (!(bool) elgg_get_plugin_setting('enable_microsub', 'indieweb')) {
			return;
		}
		
		echo "Processes Feed Update starting" . PHP_EOL;
		elgg_log("Processes Feed Update starting", 'NOTICE');
		
		// ignore access
		elgg_call(ELGG_IGNORE_ACCESS, function() {
			$sources = elgg_get_entities([
                'type' => 'object',
                'subtype' => MicrosubSource::SUBTYPE,
                'limit' => false,
                'batch' => true,
                'batch_inc_offset' => false,
				'metadata_name_value_pairs' => [
					[
						'name' => 'status',
						'value' => 1,
					],
					[
						'name' => 'fetch_interval',
						'value' => 1800,
					],
				],
            ]);
			
			if (empty($sources)) {
				return true;
			}
			
			foreach ($sources as $source) {
				elgg()->microsub->fetchItems($source->url);
			}

		// restore access
		});
		
		echo "Finished Feed Update processing" . PHP_EOL;
		elgg_log("Finished Feed Update processing", 'NOTICE');
	}
	
	//1 hour
	public static function hourlyFeedUpdate(Event $event) {
		if (!(bool) elgg_get_plugin_setting('enable_microsub', 'indieweb')) {
			return;
		}
		
		echo "Processes Feed Update starting" . PHP_EOL;
		elgg_log("Processes Feed Update starting", 'NOTICE');
		
		// ignore access
		elgg_call(ELGG_IGNORE_ACCESS, function() {
			$sources = elgg_get_entities([
                'type' => 'object',
                'subtype' => MicrosubSource::SUBTYPE,
                'limit' => false,
                'batch' => true,
                'batch_inc_offset' => false,
				'metadata_name_value_pairs' => [
					[
						'name' => 'status',
						'value' => 1,
					],
					[
						'name' => 'fetch_interval',
						'value' => 3600,
					],
				],
            ]);
			
			if (empty($sources)) {
				return true;
			}
			
			foreach ($sources as $source) {
				elgg()->microsub->fetchItems($source->url);
			}

		// restore access
		});
		
		echo "Finished Feed Update processing" . PHP_EOL;
		elgg_log("Finished Feed Update processing", 'NOTICE');
	}
	
	//1 day
	public static function dailyFeedUpdate(Event $event) {
		if (!(bool) elgg_get_plugin_setting('enable_microsub', 'indieweb')) {
			return;
		}
		
		echo "Processes Feed Update starting" . PHP_EOL;
		elgg_log("Processes Feed Update starting", 'NOTICE');
		
		// ignore access
		elgg_call(ELGG_IGNORE_ACCESS, function() {
			$sources = elgg_get_entities([
                'type' => 'object',
                'subtype' => MicrosubSource::SUBTYPE,
                'limit' => false,
                'batch' => true,
                'batch_inc_offset' => false,
				'metadata_name_value_pairs' => [
					[
						'name' => 'status',
						'value' => 1,
					],
					[
						'name' => 'fetch_interval',
						'value' => 86400,
					],
				],
            ]);
			
			if (empty($sources)) {
				return true;
			}
			
			foreach ($sources as $source) {
				elgg()->microsub->fetchItems($source->url);
			}

		// restore access
		});
		
		echo "Finished Feed Update processing" . PHP_EOL;
		elgg_log("Finished Feed Update processing", 'NOTICE');
	}
	
	//1 week
	public static function weeklyFeedUpdate(Event $event) {
		if (!(bool) elgg_get_plugin_setting('enable_microsub', 'indieweb')) {
			return;
		}
		
		echo "Processes Feed Update starting" . PHP_EOL;
		elgg_log("Processes Feed Update starting", 'NOTICE');
		
		// ignore access
		elgg_call(ELGG_IGNORE_ACCESS, function() {
			$sources = elgg_get_entities([
                'type' => 'object',
                'subtype' => MicrosubSource::SUBTYPE,
                'limit' => false,
                'batch' => true,
                'batch_inc_offset' => false,
				'metadata_name_value_pairs' => [
					[
						'name' => 'status',
						'value' => 1,
					],
					[
						'name' => 'fetch_interval',
						'value' => 604800,
					],
				],
            ]);
			
			if (empty($sources)) {
				return true;
			}
			
			foreach ($sources as $source) {
				elgg()->microsub->fetchItems($source->url);
			}

		// restore access
		});
		
		echo "Finished Feed Update processing" . PHP_EOL;
		elgg_log("Finished Feed Update processing", 'NOTICE');
	}
	
	//1 month
	public static function monthlyFeedUpdate(Event $event) {
		if (!(bool) elgg_get_plugin_setting('enable_microsub', 'indieweb')) {
			return;
		}
		
		echo "Processes Feed Update starting" . PHP_EOL;
		elgg_log("Processes Feed Update starting", 'NOTICE');
		
		// ignore access
		elgg_call(ELGG_IGNORE_ACCESS, function() {
			$sources = elgg_get_entities([
                'type' => 'object',
                'subtype' => MicrosubSource::SUBTYPE,
                'limit' => false,
                'batch' => true,
                'batch_inc_offset' => false,
				'metadata_name_value_pairs' => [
					[
						'name' => 'status',
						'value' => 1,
					],
					[
						'name' => 'fetch_interval',
						'value' => 2419200,
					],
				],
            ]);
			
			if (empty($sources)) {
				return true;
			}
			
			foreach ($sources as $source) {
				elgg()->microsub->fetchItems($source->url);
			}

		// restore access
		});
		
		echo "Finished Feed Update processing" . PHP_EOL;
		elgg_log("Finished Feed Update processing", 'NOTICE');
	}
}
