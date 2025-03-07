<?php

/**
 * IndieWeb
 * @author Nikolai Shcherbin
 * @license GNU Affero General Public License version 3
 * @copyright (c) Nikolai Shcherbin 2022
 * @link https://wzm.me
**/

namespace Elgg\IndieWeb\WebSub\Entity;

class WebSubPub extends \ElggObject
{
    const SUBTYPE = 'websubpub';

    /**
     * {@inheritdoc}
     */
    protected function initializeAttributes()
    {
        parent::initializeAttributes();
        $this->attributes['subtype'] = self::SUBTYPE;
    }
}
