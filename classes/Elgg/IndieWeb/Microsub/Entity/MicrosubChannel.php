<?php

/**
 * IndieWeb
 * @author Nikolai Shcherbin
 * @license GNU Affero General Public License version 3
 * @copyright (c) Nikolai Shcherbin 2022
 * @link https://wzm.me
**/

namespace Elgg\IndieWeb\Microsub\Entity;

class MicrosubChannel extends \ElggObject
{
    const SUBTYPE = 'microsub_channel';

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
    public function getStatus(): bool
    {
        return $this->status;
    }

    /**
     * {@inheritdoc}
     */
    public function getPostTypesToExclude(): array
    {
        $return = [];

        $objects = [
            'reply',
            'repost',
            'bookmark',
            'like',
            'note',
            'article',
            'photo',
            'video',
            'checkin',
            'rsvp',
        ];

        foreach ($objects as $subtype) {
            if ((bool) $this->{"microsub_channel:exclude_post_type:$subtype"}) {
                $return[] = $subtype;
            }
        }

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * {@inheritdoc}
     */
    public function getSources($count = false)
    {
        $options = [
            'type' => 'object',
            'subtype' => \Elgg\IndieWeb\Microsub\Entity\MicrosubSource::SUBTYPE,
            'container_guid' => $this->guid,
            'limit' => false,
            'batch' => true,
            'batch_size' => 50,
            'batch_inc_offset' => false,
            'count' => $count,
        ];

        return elgg_get_entities($options);
    }

    /**
     * {@inheritdoc}
     */
    public function getUnreadCount($count = true)
    {
        $return = elgg_count_entities([
            'type' => 'object',
            'subtype' => \Elgg\IndieWeb\Microsub\Entity\MicrosubItem::SUBTYPE,
            'limit' => false,
            'batch' => true,
            'batch_size' => 50,
            'batch_inc_offset' => false,
            'metadata_name_value_pairs' => [
                [
                    'name' => 'channel_id',
                    'value' => $this->guid,
                ],
                [
                    'name' => 'is_read',
                    'value' => 0,
                ],
            ],
        ]);

        if ((bool) $count) {
            return $return;
        } else {
            if ($return > 0) {
                return true;
            }
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getItemCount()
    {
        return elgg_count_entities([
            'type' => 'object',
            'subtype' => \Elgg\IndieWeb\Microsub\Entity\MicrosubItem::SUBTYPE,
            'limit' => false,
            'batch' => true,
            'batch_size' => 50,
            'batch_inc_offset' => false,
            'metadata_name_value_pairs' => [
                [
                    'name' => 'channel_id',
                    'value' => $this->guid,
                ],
            ],
        ]);
    }
}
