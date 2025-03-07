<?php

/**
 * IndieWeb
 * @author Nikolai Shcherbin
 * @license GNU Affero General Public License version 3
 * @copyright (c) Nikolai Shcherbin 2022
 * @link https://wzm.me
**/

namespace Elgg\IndieWeb\Webmention\Entity;

class Webmention extends \ElggObject
{
    const SUBTYPE = 'webmention';

    /**
     * {@inheritdoc}
     */
    protected function initializeAttributes()
    {
        parent::initializeAttributes();
        $this->attributes['subtype'] = self::SUBTYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * {@inheritdoc}
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * {@inheritdoc}
     */
    public function getTargetGuid(): int
    {
        return $this->target_guid ?? 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getObjectType()
    {
        return $this->object_type;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorName()
    {
        return $this->author_name;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorAvatar()
    {
        return $this->author_photo;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorUrl()
    {
        return $this->author_url;
    }

    /**
     * {@inheritdoc}
     */
    public function getPlainContent()
    {
        return $this->content_text;
    }

    /**
     * {@inheritdoc}
     */
    public function getHTMLContent()
    {
        return $this->content_html;
    }

    /**
     * {@inheritdoc}
     */
    public function getProperty()
    {
        return $this->property;
    }

    /**
     * {@inheritdoc}
     */
    public function getParentTarget(): int
    {
        return $this->parent_target ?? 0;
    }

    /**
     * {@inheritdoc}
     */
    public function isPublished(): bool
    {
        return $this->published;
    }

    /**
     * {@inheritdoc}
     */
    public function getPhoto()
    {
        return $this->photo;
    }

    /**
     * {@inheritdoc}
     */
    public function getVideo()
    {
        return $this->video;
    }

    /**
     * {@inheritdoc}
     */
    public function getAudio()
    {
        return $this->audio;
    }
}
