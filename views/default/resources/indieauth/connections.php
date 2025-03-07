<?php

$user = elgg_get_page_owner_entity();
if (!$user instanceof \ElggUser) {
    $user = elgg_get_logged_in_user_entity();
    elgg_set_page_owner_guid($user->guid);
}

if (!$user->canEdit()) {
    throw new \Elgg\Exceptions\Http\EntityPermissionsException();
}

elgg_set_context('settings');

$content = elgg_view('indieauth/authorize', ['entity' => $user]);

echo elgg_view_page(elgg_echo('indieauth:accounts'), [
    'content' => $content,
    'show_owner_block_menu' => false,
]);
