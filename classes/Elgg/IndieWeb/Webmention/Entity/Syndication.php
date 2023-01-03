<?php
/**
 * IndieWeb
 * @author Nikolai Shcherbin
 * @license GNU Affero General Public License version 3
 * @copyright (c) Nikolai Shcherbin 2022
 * @link https://wzm.me
**/

namespace Elgg\IndieWeb\Webmention\Entity;

class Syndication extends \ElggObject {
	
	const SUBTYPE = 'syndication';

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
	public function getURL() {
		$url = $this->url;
		return elgg_normalize_url($url);
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function getSourceId(): int {
		return $this->source_id ?? 0;
	}
}
