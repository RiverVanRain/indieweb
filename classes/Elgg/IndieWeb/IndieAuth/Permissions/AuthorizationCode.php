<?php

namespace Elgg\IndieWeb\IndieAuth\Permissions;

use Elgg\Hook;
use Elgg\IndieWeb\IndieAuth\Entity\IndieAuthAuthorizationCode;

class AuthorizationCode {
	
	/**
	 * Set AuthorizationCode editing permissions
	 *
	 * @param string $hook   "permissions_check"
	 * @param string $type   "object"
	 * @param bool   $return Permission
	 * @param array  $params Hook params
	 * @return bool
	 */
	public static function canEdit(Hook $hook) {

		$entity = $hook->getEntityParam();
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
	 * @param string $hook   "permissions_check:delete"
	 * @param string $type   "object"
	 * @param bool   $return Permission
	 * @param array  $params Hook params
	 * @return bool
	 */
	public static function canDelete(Hook $hook) {

		$entity = $hook->getEntityParam();

		if (!$entity instanceof IndieAuthAuthorizationCode) {
			return;
		}

		if (elgg_is_admin_logged_in()) {
			return true;
		}
		
		return false;
	}
}
