<?php

namespace Elgg\IndieWeb\IndieAuth\Permissions;

use Elgg\Event;
use Elgg\IndieWeb\IndieAuth\Entity\IndieAuthAuthorizationCode;

class AuthorizationCode {
	
	/**
	 * Set AuthorizationCode editing permissions
	 *
	 * @param string $event   "permissions_check"
	 * @param string $type   "object"
	 * @param bool   $return Permission
	 * @param array  $params Event params
	 * @return bool
	 */
	public static function canEdit(Event $event) {

		$entity = $event->getEntityParam();
		if (!$entity instanceof IndieAuthAuthorizationCode) {
			return;
		}

		if (elgg_is_admin_logged_in()) {
			return true;
		}

		return false;
	}

	/**
	 * Set AuthorizationCode delete permissions
	 *
	 * @param string $event   "permissions_check:delete"
	 * @param string $type   "object"
	 * @param bool   $return Permission
	 * @param array  $params Event params
	 * @return bool
	 */
	public static function canDelete(Event $event) {

		$entity = $event->getEntityParam();

		if (!$entity instanceof IndieAuthAuthorizationCode) {
			return;
		}

		if (elgg_is_admin_logged_in()) {
			return true;
		}
		
		return false;
	}
}
