<?php

/**
 * Change status of MicrosubChannel
 */

namespace Elgg\IndieWeb\Microsub\Actions\MicrosubChannel;

class ToggleStatusAction
{
    public function __invoke(\Elgg\Request $request)
    {

        $entity = $request->getEntityParam();

        if (!$entity instanceof \Elgg\IndieWeb\Microsub\Entity\MicrosubChannel) {
            throw new \Elgg\Exceptions\Http\EntityNotFoundException();
        }

        if (!$entity->canEdit()) {
            throw new \Elgg\Exceptions\Http\EntityPermissionsException();
        }

        $enabled = (bool) $entity->getStatus();

        if ($enabled) {
            $entity->status = 0;
            $message = elgg_echo('indieweb:microsub:microsub_channel:disable:success');
        } else {
            $entity->status = 1;
            $message = elgg_echo('indieweb:microsub:microsub_channel:enable:success');
        }

        if (!$entity->save()) {
            return elgg_error_response(elgg_echo('indieweb:microsub:microsub_channel:status:fail'));
        }

        return elgg_ok_response('', $message);
    }
}
