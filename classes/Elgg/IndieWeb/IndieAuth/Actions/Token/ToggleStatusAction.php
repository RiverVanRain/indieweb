<?php

/**
 * Change status of an IndieAuth token
 */

namespace Elgg\IndieWeb\IndieAuth\Actions\Token;

class ToggleStatusAction
{
    public function __invoke(\Elgg\Request $request)
    {

        $entity = $request->getEntityParam();

        if (!$entity instanceof \Elgg\IndieWeb\IndieAuth\Entity\IndieAuthToken) {
            throw new \Elgg\Exceptions\Http\EntityNotFoundException();
        }

        if (!$entity->canEdit()) {
            throw new \Elgg\Exceptions\Http\EntityPermissionsException();
        }

        if ($entity->isRevoked()) {
            $entity->activate();
            $message = elgg_echo('indieweb:indieauth:token:activate:success');
        } else {
            $entity->revoke();
            $message = elgg_echo('indieweb:indieauth:token:revoke:success');
        }

        if (!$entity->save()) {
            return elgg_error_response(elgg_echo('indieweb:indieauth:token:status:fail'));
        }

        return elgg_ok_response('', $message);
    }
}
