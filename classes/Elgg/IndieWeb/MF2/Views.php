<?php
/**
 * IndieWeb
 * @author Nikolai Shcherbin
 * @license GNU Affero General Public License version 3
 * @copyright (c) Nikolai Shcherbin 2022
 * @link https://wzm.me
**/

namespace Elgg\IndieWeb\MF2;

class Views {
	
	public static function fullBody(\Elgg\Event $event) {
		$return = $event->getValue();
		
		$entity = elgg_extract('entity', $event->getParam('vars'));
		
		if ($entity instanceof \ElggComment) {
			return $return;
		}
		
		$class = (array) elgg_extract('class', $return, []);
		
		$return['class'] = elgg_extract_class($class, ['h-entry']);
		
		return $return;
	}
	
	public static function contentBody(\Elgg\Event $event) {
		$return = $event->getValue();
		
		$classes = ['e-content'];
		
		$body_params = (array) elgg_extract('body_params', $return, []);
		$body_params['class'] = elgg_extract_class($body_params, $classes);
		
		$return['body_params'] = $body_params;
		
		return $return;
	}
}