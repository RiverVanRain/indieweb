<?php

/**
 * Change status of MicrosubSource
 */

namespace Elgg\IndieWeb\Microsub\Actions\MicrosubSource;

class ToggleStatusAction {

	public function __invoke(\Elgg\Request $request) {

		$entity = $request->getEntityParam();
		
		if (!$entity instanceof \Elgg\IndieWeb\Microsub\Entity\MicrosubSource) {
			throw new \Elgg\Exceptions\Http\EntityNotFoundException();
		}
		
		if (!$entity->canEdit()) {
			throw new \Elgg\Exceptions\Http\EntityPermissionsException();
		}
		
		$enabled = (bool) $entity->getStatus();
		
		if ($enabled) {
			$entity->status = 0;
			$message = elgg_echo('indieweb:microsub:microsub_source:disable:success');
		} else {
			$entity->status = 1;
			$message = elgg_echo('indieweb:microsub:microsub_source:enable:success');
		}
		
		if (!$entity->save()) {
			return elgg_error_response(elgg_echo('indieweb:microsub:microsub_source:status:fail'));
		}

		return elgg_ok_response('', $message);
	}
}