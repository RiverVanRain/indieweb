<?php

namespace Elgg\IndieWeb\IndieAuth\Actions;

class AuthorizeAction {

	public function __invoke(\Elgg\Request $request) {
		unset($_SESSION['indieauth']);

		$guid = (int) $request->getParam('guid');
		$user = get_entity($guid);
		if (!$user instanceof \ElggUser) {
			throw new \Elgg\Exceptions\Http\EntityNotFoundException();
		}
		
		$scopes = [];
		$scope = (array) $request->getParam('scope', []);
		foreach ($scope as $key => $value) {
			if ($key === $value) {
				$scopes[] = $key;
			}
		}

		// Generate code
		$code = _elgg_services()->crypto->getRandomString(120);
		
		$values = [
			'code' => $code,
			'status' => 1,
			'me' => $request->getParam('me'),
			'target_id' => $guid,
			'client_id' => $request->getParam('client_id'),
			'scope' => implode(' ', $scopes),
			'redirect_uri' => $request->getParam('redirect_uri'),
			'code_challenge' => $request->getParam('code_challenge'),
			'code_challenge_method' => $request->getParam('code_challenge_method'),
			'expire' => time() + 3600,
		];
		
		elgg_call(ELGG_IGNORE_ACCESS, function () use (&$values) {
			$authorization_code = new \Elgg\IndieWeb\IndieAuth\Entity\IndieAuthAuthorizationCode();
			$authorization_code->access_id = ACCESS_PRIVATE;
			$authorization_code->owner_guid = elgg_get_site_entity()->guid;
			$authorization_code->container_guid = elgg_get_site_entity()->guid;
			
			foreach($values as $name => $value) {
				$authorization_code->setMetadata($name, $value);
			}
			
			if (!$authorization_code->save()) {
				$authorization_code->delete();
				
				elgg_log('Authorization Code creation failed', 'ERROR');
				return elgg_error_response(elgg_echo('indieweb:indieauth:authorize:fail'));
			}
		});

		$query = '?state=' . $request->getParam('state') . '&me=' . $request->getParam('me') . '&code=' . $code;
		$response = $request->getParam('redirect_uri') . $query;
		
		return elgg_ok_response('', elgg_echo('indieweb:indieauth:authorize:authorized'), $response);

	}
}