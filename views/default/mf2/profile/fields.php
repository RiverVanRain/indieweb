<?php
/**
 * @uses $vars['entity']       The user entity
 * @uses $vars['microformats'] Mapping of fieldnames to microformats
 */

$user = elgg_extract('entity', $vars);
if (!$user instanceof \ElggUser) {
	return;
}

$fullname = htmlspecialchars($user->getDisplayName() ?? '', ENT_QUOTES, 'UTF-8', false);

$link = elgg_format_element('a', ['class' => 'u-url u-uid', 'rel' => 'me', 'href' => $user->getURL()], $fullname);

$image = elgg_format_element('span', ['class' => 'u-photo'], $user->getIconURL('large'));

$name = elgg_format_element('span', ['class' => 'p-name fn'], $fullname);

$nickname = elgg_format_element('span', ['class' => 'p-nickname nickname'], $user->username);

echo elgg_format_element('div', ['class' => 'h-card vcard hidden'], $name . $nickname . $link . $image);
