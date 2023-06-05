<?php

namespace Elgg\IndieWeb\IndieAuth\Actions;

class CancelAction {

	public function __invoke(\Elgg\Request $request) {
		if (!(bool) elgg_get_plugin_setting('enable_indieauth_login', 'indieweb')) {
			throw new \Elgg\Exceptions\Http\PageNotFoundException();
		}
		
		$entity = $request->getEntityParam();
		if (!$entity instanceof \ElggUser) {
			throw new \Elgg\Exceptions\Http\EntityNotFoundException();
		}
		
		if (!$entity->canEdit()) {
			throw new \Elgg\Exceptions\Http\EntityPermissionsException();
		}
		
		if (!(bool) $entity->indieauth_login) {
			throw new \Elgg\Exceptions\Http\BadRequestException();
		}
		
		$entity->removePluginSetting('indieweb', 'indieauth');
		$entity->indieauth_login = 0;
		$entity->save();

		return elgg_ok_response('', elgg_echo('indieweb:indieauth:authorize:cancel'));
	}
}