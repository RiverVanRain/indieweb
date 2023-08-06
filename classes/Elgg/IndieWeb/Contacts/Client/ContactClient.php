<?php
/**
 * IndieWeb
 * @author Nikolai Shcherbin
 * @license GNU Affero General Public License version 3
 * @copyright (c) Nikolai Shcherbin 2022
 * @link https://wzm.me
**/

namespace Elgg\IndieWeb\Contacts\Client;

use Elgg\Traits\Di\ServiceFacade;
use Elgg\IndieWeb\Contacts\Entity\Contact;

class ContactClient {
	
	use ServiceFacade;
	
	public static function name() {
		return 'indieweb.contact';
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function __get($name) {
		return $this->$name;
	}
	
	public function storeContact(array $values) {
		if (!empty($values['name'])) {
			$contacts = elgg_get_entities([
                'type' => 'object',
                'subtype' => Contact::SUBTYPE,
                'metadata_name_value_pairs' => [
                    [
                        'name' => 'title',
                        'value' => $values['name'],
                    ],
                ],
                'limit' => false,
                'batch' => true,
                'batch_inc_offset' => false,
            ]);
			
			if (empty($contacts)) {
				// Get nickname if the url is from twitter.
				if (empty($values['nickname']) && !empty($values['url']) && strpos($values['url'], 'twitter.com') !== false) {
					$parsed = parse_url($values['url']);
					if (!empty($parsed['path'])) {
						$values['nickname'] = str_replace('/', '', $parsed['path']);
					}
				}
				
				try {
					$entity = new Contact();
					$entity->owner_guid = elgg_get_site_entity()->guid;
					$entity->container_guid = elgg_get_site_entity()->guid;
					$entity->access_id = ACCESS_PUBLIC;
					$entity->setDisplayName($values['name']);
					
					if (!empty($values['nickname'])) {
						$entity->setMetadata('nickname', $values['nickname']);
					}
					if (!empty($values['url'])) {
						$entity->setMetadata('website', $values['url']);
					}
					if (!empty($values['photo'])) {
						$entity->setMetadata('photo', $values['photo']);
						
						$image = _elgg_services()->mediaCacher->saveImageFromUrl($values['photo']);
						/** \Elgg\IndieWeb\Cache\MediaCacher **/
						
						$entity->setMetadata('thumbnail_url', elgg_get_inline_url($image));
					}
					return $entity->save();
				}
        
				catch (\Exception $ignored) {}
			}
		}

		return null;
	}
}
