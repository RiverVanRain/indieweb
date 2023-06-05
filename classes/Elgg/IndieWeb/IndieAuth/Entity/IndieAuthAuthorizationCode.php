<?php
/**
 * IndieWeb
 * @author Nikolai Shcherbin
 * @license GNU Affero General Public License version 3
 * @copyright (c) Nikolai Shcherbin 2022
 * @link https://wzm.me
**/

namespace Elgg\IndieWeb\IndieAuth\Entity;

class IndieAuthAuthorizationCode extends \ElggObject {
	const SUBTYPE = 'indieauth_code';

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
	public function activate() {
		$this->status = true;
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
	public function getScopes() {
		return explode(' ', $this->scope);
	}

	/**
	* {@inheritdoc}
	*/
	public function getOwnerId() {
		return $this->target_id;
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
	public function getExpiretime() {
		return $this->expire;
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
	public function getRedirectURI() {
		return $this->redirect_uri;
	}

	/**
	* {@inheritdoc}
	*/
	public function getCode() {
		return $this->code;
	}

	/**
	* {@inheritdoc}
	*/
	public function getCodeChallenge() {
		return $this->code_challenge;
	}

	/**
	* {@inheritdoc}
	*/
	public function getCodeChallengeMethod() {
		return $this->code_challenge_method;
	}

}
