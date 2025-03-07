<?php

namespace Elgg\IndieWeb\Microsub\Forms;

use Elgg\IndieWeb\Microsub\Entity\MicrosubChannel;
use Elgg\IndieWeb\Microsub\Entity\MicrosubSource;

class EditMicrosubSource
{
    /**
     * @var MicrosubSource entity being edited
     */
    protected $entity;

    public function __construct(MicrosubSource $entity = null)
    {
        $this->entity = $entity;
    }

    public function __invoke()
    {

        $result = [
            'title' => '',
            'url' => '',
            'status' => 1,
            'fetch_interval' => 3600,
            'items_to_keep' => 0,
            'websub' => 0,
        ];

        $objects = [
            'reply',
            'repost',
            'bookmark',
            'like',
        ];

        foreach ($objects as $subtype) {
            $result["microsub_source:post_context:$subtype"] = 0;
        }

        // edit
        if ($this->entity instanceof MicrosubSource) {
            foreach ($result as $key => $value) {
                $result[$key] = $this->entity->$key;
            }

            $result['entity'] = $this->entity;
        }

        // sticky form
        $sticky = elgg_get_sticky_values('microsub/source/edit');
        if (!empty($sticky)) {
            foreach ($sticky as $key => $value) {
                $result[$key] = $value;
            }

            elgg_clear_sticky_form('microsub/source/edit');
        }

        return $result;
    }
}
