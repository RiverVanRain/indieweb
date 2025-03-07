<?php

/**
 * IndieWeb
 * @author Nikolai Shcherbin
 * @license GNU Affero General Public License version 3
 * @copyright (c) Nikolai Shcherbin 2022
 * @link https://wzm.me
**/

namespace Elgg\IndieWeb\Microsub\Entity;

class MicrosubItem extends \ElggObject
{
    const SUBTYPE = 'microsub_item';

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
    public function getChannel()
    {
        return $this->getContainerEntity()->getContainerEntity();
    }

    /**
     * {@inheritdoc}
     */
    public function isRead()
    {
        return $this->is_read;
    }

    /**
     * {@inheritdoc}
     */
    public function getChannelId(): int
    {
        return $this->channel_id ?: $this->guid;
    }

    /**
     * {@inheritdoc}
     */
    public function getSourceIdForTimeline($author, $urls = [])
    {
        $return = $this->container_guid;

        if ($this->getChannelId() > 0 && $return > 0) {
            $url = $this->getContainerEntity()->url;
            foreach ($urls as $u) {
                if (strpos($url, $u) !== false) {
                    $return = $this->container_guid . ':' . $author;
                    break;
                }
            }
        }

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        $return = null;

        $data = $this->data;
        if (!empty($data)) {
            $return = json_decode($data);
        }

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function getContext()
    {
        $return = null;

        $value = $this->post_context;

        if (!empty($value)) {
            $temp = json_decode($value);

            if (isset($temp->url)) {
                $return = new \stdClass();
                $object = new \stdClass();
                $content = '';

                if (!empty($temp->content->text)) {
                    $content = $temp->content->text;
                } elseif (!empty($temp->summary)) {
                    $content = $temp->summary;
                }

                $object->content = $content;
                $object->name = isset($temp->name) ? $temp->name : '';
                $return->{$temp->url} = $object;
            }
        }

        return $return;
    }
}
