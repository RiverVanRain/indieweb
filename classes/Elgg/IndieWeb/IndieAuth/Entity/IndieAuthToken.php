<?php
/**
 * IndieWeb
 * @author Nikolai Shcherbin
 * @license GNU Affero General Public License version 3
 * @copyright (c) Nikolai Shcherbin 2022
 * @link https://wzm.me
**/

namespace Elgg\IndieWeb\IndieAuth\Entity;

class IndieAuthToken extends \ElggObject {
	const SUBTYPE = 'indieauth_token';

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
	public function getAccessToken() {
		return $this->access_token;
	}

	/**
	* {@inheritdoc}
	*/
	public function activate() {
		$this->status = 1;
		return $this;
	}

	/**
	* {@inheritdoc}
	*/
	public function revoke() {
		$this->status = 0;
		return $this;
	}

	/**
	* {@inheritdoc}
	*/
	public function isRevoked() {
		return !$this->status;
	}

	/**
	* {@inheritdoc}
	*/
	public function isExpired() {
		$expire = $this->expire;
		return $expire > 0 && $expire < time();
	}

	/**
	* {@inheritdoc}
	*/
	public function isValid() {
		return !$this->isRevoked() && !$this->isExpired();
	}

	/**
	* {@inheritdoc}
	*/
	public function getStatus() {
		return (bool) $this->status;
	}

	/**
	* {@inheritdoc}
	*/
	public function getScopes() {
		return explode(' ', $this->scope);
	}

	/**
	* {@inheritdoc}
	*/
	public function getScopesAsString() {
		return $this->scope;
	}

	/**
	* {@inheritdoc}
	*/
	public function getClientId() {
		return $this->client_id;
	}

	/**
	* {@inheritdoc}
	*/
	public function getMe() {
		return $this->me;
	}

	/**
	* {@inheritdoc}
	*/
	public function getCreated() {
		return $this->created;
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
	public function getOwnerId() {
		return $this->target_id;
	}

}
