<?php

/**
 * Change status of an IndieAuth token
 */

namespace Elgg\IndieWeb\IndieAuth\Actions\Token;

class SaveAction
{
    public function __invoke(\Elgg\Request $request)
    {
        $guid = (int) $request->getParam('guid');

        if ($guid) {
            $entity = get_entity($guid);
            if ($entity instanceof \Elgg\IndieWeb\IndieAuth\Entity\IndieAuthToken && $entity->canEdit()) {
                $token = $entity;
            } else {
                throw new \Elgg\Exceptions\Http\EntityNotFoundException();
            }
        } else {
            $token = new \Elgg\IndieWeb\IndieAuth\Entity\IndieAuthToken();
            $token->access_id = ACCESS_PRIVATE;
            $token->owner_guid = elgg_get_site_entity()->guid;
            $token->container_guid = elgg_get_site_entity()->guid;
            $token->expire = 0;
            $token->changed = 0;
            $token->status = 1;
            $token->created = time();
            $token->access_token = _elgg_services()->crypto->getRandomString(120);
        }

        $values = [
            'me' => elgg_get_site_url(),
            'target_id' => elgg_get_logged_in_user_guid(),
            'client_id' => $request->getParam('client_id'),
            'scope' => $request->getParam('scope'),
        ];

        foreach ($values as $name => $value) {
            $token->setMetadata($name, $value);
        }

        if (!$token->save()) {
            return elgg_error_response(elgg_echo('indieweb:indieauth:token:save:fail'));
        }

        return elgg_ok_response('', elgg_echo('indieweb:indieauth:token:save:success'));
    }
}
