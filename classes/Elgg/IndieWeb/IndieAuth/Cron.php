<?php
/**
 * IndieWeb
 * @author Nikolai Shcherbin
 * @license GNU Affero General Public License version 3
 * @copyright (c) Nikolai Shcherbin 2022
 * @link https://wzm.me
**/

namespace Elgg\IndieWeb\IndieAuth;

use Elgg\IndieWeb\IndieAuth\Entity\IndieAuthAuthorizationCode;

class Cron {
	
	public static function processCodes(\Elgg\Hook $hook) {
		
		echo "Processes expired authorization codes starting" . PHP_EOL;
		elgg_log("Processes expired authorization codes starting", 'NOTICE');
		
		// ignore access
		elgg_call(ELGG_IGNORE_ACCESS, function() {
			$auth_codes = elgg_get_entities([
                'type' => 'object',
                'subtype' => IndieAuthAuthorizationCode::SUBTYPE,
                'metadata_name_value_pairs' => [
                    [
                        'name' => 'expire',
                        'value' => time(),
                        'operand' => '<',
                    ]
                ],
                'limit' => false,
                'batch' => true,
                'batch_inc_offset' => false
            ]);
			
			if (empty($auth_codes)) {
				return true;
			}
			
			foreach ($auth_codes as $code) {
                $code->delete();
            }
			
		// restore access
		});
		
		echo "Finished expired authorization codes processing" . PHP_EOL;
		elgg_log("Finished expired authorization codes processing", 'NOTICE');
	}
}
