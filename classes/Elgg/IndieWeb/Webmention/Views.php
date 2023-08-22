<?php

namespace Elgg\IndieWeb\Webmention;

class Views {
	
	/**
	 * Adds menu items to the social menu
	 *
	 * @param \Elgg\Event $event 'view_vars', 'object/elements/imprint/contents'
	 *
	 * @return array
	 */
	public function __invoke(\Elgg\Event $event) {
		
		$vars = $event->getValue();
		
		$entity = elgg_extract('entity', $vars);
		if (!$entity instanceof \Elgg\IndieWeb\Webmention\Entity\Webmention) {
			return;
		}
		
		$imprint = elgg_extract('imprint', $vars, []);
		
		//source
		$imprint['webmention_source'] = [
			'icon_name' => 'link',
			'content' => elgg_view('output/url', [
				'text' => elgg_echo('indieweb:webmention:source'),
				'href' => $entity->source,
			]),
		];
/*		
		//target
		$target = elgg_view('output/url', [
			'text' => elgg_echo('indieweb:webmention:target'),
			'href' => $entity->target,
		]);
		
		$imprint['webmention_target'] = [
			'icon_name' => false,
			'content' => elgg_echo('indieweb:webmention:byline:on', [$target]),
		];
*/		
		$vars['imprint'] = $imprint;
		
		return $vars;
	}
}
