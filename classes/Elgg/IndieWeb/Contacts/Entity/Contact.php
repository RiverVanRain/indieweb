<?php

namespace Elgg\IndieWeb\Contacts\Entity;

class Contact extends \ElggObject {

	const SUBTYPE = 'indieweb_contact';

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
	public function getNickname(): string {
		return $this->nickname;
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function getPhoto(): string {
		return $this->photo;
	}
}
