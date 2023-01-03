<?php

$title = elgg_echo('indieweb:micropub:view:title');

$body = elgg_format_element('div', ['class' => 'mbl'], elgg_echo('indieweb:micropub:view:subtitle'));

$article = elgg_format_element('span', ['class' => 'elgg-loud mrm'], elgg_echo('indieweb:micropub:view:article'));
$body .= elgg_format_element('div', ['class' => 'mbm'], $article . elgg_echo('indieweb:micropub:view:article:desc')); 

$note = elgg_format_element('span', ['class' => 'elgg-loud mrm'], elgg_echo('indieweb:micropub:view:note'));
$body .= elgg_format_element('div', ['class' => 'mbm'], $note . elgg_echo('indieweb:micropub:view:note:desc')); 

$like = elgg_format_element('span', ['class' => 'elgg-loud mrm'], elgg_echo('indieweb:micropub:view:like'));
$body .= elgg_format_element('div', ['class' => 'mbm'], $like . elgg_echo('indieweb:micropub:view:like:desc')); 

$reply = elgg_format_element('span', ['class' => 'elgg-loud mrm'], elgg_echo('indieweb:micropub:view:reply'));
$body .= elgg_format_element('div', ['class' => 'mbm'], $reply . elgg_echo('indieweb:micropub:view:reply:desc')); 

$repost = elgg_format_element('span', ['class' => 'elgg-loud mrm'], elgg_echo('indieweb:micropub:view:repost'));
$body .= elgg_format_element('div', ['class' => 'mbm'], $repost . elgg_echo('indieweb:micropub:view:repost:desc')); 

$bookmark = elgg_format_element('span', ['class' => 'elgg-loud mrm'], elgg_echo('indieweb:micropub:view:bookmark'));
$body .= elgg_format_element('div', ['class' => 'mbm'], $bookmark . elgg_echo('indieweb:micropub:view:bookmark:desc')); 

$event = elgg_format_element('span', ['class' => 'elgg-loud mrm'], elgg_echo('indieweb:micropub:view:event'));
$body .= elgg_format_element('div', ['class' => 'mbm'], $event . elgg_echo('indieweb:micropub:view:event:desc')); 

$rsvp = elgg_format_element('span', ['class' => 'elgg-loud mrm'], elgg_echo('indieweb:micropub:view:rsvp'));
$body .= elgg_format_element('div', ['class' => 'mbm'], $rsvp . elgg_echo('indieweb:micropub:view:rsvp:desc')); 

$issue = elgg_format_element('span', ['class' => 'elgg-loud mrm'], elgg_echo('indieweb:micropub:view:issue'));
$body .= elgg_format_element('div', ['class' => 'mbm'], $issue . elgg_echo('indieweb:micropub:view:issue:desc')); 

$checkin = elgg_format_element('span', ['class' => 'elgg-loud mrm'], elgg_echo('indieweb:micropub:view:checkin'));
$body .= elgg_format_element('div', ['class' => 'mbm'], $checkin . elgg_echo('indieweb:micropub:view:checkin:desc')); 

$geocache = elgg_format_element('span', ['class' => 'elgg-loud mrm'], elgg_echo('indieweb:micropub:view:geocache'));
$body .= elgg_format_element('div', ['class' => 'mbm'], $geocache . elgg_echo('indieweb:micropub:view:geocache:desc')); 

$trip = elgg_format_element('span', ['class' => 'elgg-loud mrm'], elgg_echo('indieweb:micropub:view:trip'));
$body .= elgg_format_element('div', ['class' => 'mbm'], $trip . elgg_echo('indieweb:micropub:view:trip:desc')); 

echo elgg_view_module('aside', $title, $body);
