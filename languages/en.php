<?php
/**
 * IndieWeb
 * @author Nikolai Shcherbin
 * @license GNU Affero General Public License version 3
 * @copyright (c) Nikolai Shcherbin 2022
 * @link https://wzm.me
**/

return [
	// GENERAL
	'admin:indieweb' => 'IndieWeb',
	'settings:indieweb' => 'IndieWeb',
	'admin:indieweb:webmention' => 'Webmention',
	'admin:indieweb:micropub' => 'Micropub',
	'admin:indieweb:microsub' => 'Microsub',
	
	// WEBMENTION
	'settings:indieweb:webmention' => 'Basic Config',
	'settings:indieweb:enable_webmention' => 'Enable Webmention',
	'settings:indieweb:webmention_proxy' => 'Set proxy',
	'settings:indieweb:webmention_user_agent' => 'Set User agent',
	'settings:indieweb:webmention_enable_debug' => 'Enable debug',
	'settings:indieweb:webmention_blocked_domains' => 'Block domains',
	'settings:indieweb:webmention_blocked_domains:help' => 'Block domains from sending webmentions. Enter domains line per line.',
	'settings:indieweb:webmention_enable_comment_create' => 'Create a comment',
	'settings:indieweb:webmention_enable_comment_create:help' => "When a webmention is saved and is of property 'in-reply-to', it is possible to create a comment if the target of the webmention has comments enabled.
Every comment is available also at comment/indieweb/GUID so this URL can also be a target for a webmention. If a webmention is send to this target, a comment will be created on the entity, with the target GUID as the parent.
Also, last but not least, don't forget to set public access level to view webmentions.",
	
	'settings:indieweb:webmention:receiving' => 'Receiving',
	'settings:indieweb:webmention:receiving:help' => 'The easiest way to start receiving webmentions and pingbacks for any page is by using https://webmention.io. You have to create an account by signing in with your domain. For more information how to sign in with your domain, see the IndieAuth configuration. Webmention.io is open source so you can also host the service yourself. You can also use the built-in webmention endpoint to receive webmentions. All collected webmentions and pingbacks can be viewed on the webmention overview page at /admin/content/webmention.',

	'settings:indieweb:webmention_server' => 'Add Webmention server URL',
	'settings:indieweb:webmention_server:help' => 'Leave blank to use internal Webmention server',
	'settings:indieweb:webmention_create_contact' => 'Create a contact when a webmention is received',
	'settings:indieweb:webmention_create_contact:help' => 'Stores contacts optionally on incoming webmentions. Contacts can be managed at admin/content/contacts.',
	'settings:indieweb:webmention_detect_identical' => 'Detect identical webmentions',
	'settings:indieweb:webmention_detect_identical:help' => 'On some occasions it might be possible multiple webmentions are send with the same source, target and property. Enable to detect duplicates and not store those.',
	'settings:indieweb:webmention_excerpt' => 'Use an excerpt from Webmention content',
	'settings:indieweb:webmention_excerpt:help' => 'Set character limits to excerpt from Webmention content. Leave 0 to disable excerpt.',
	
	'settings:indieweb:use_webmentions' => 'Select objects you want to enable Webmention for',
	
	'admin:indieweb:webmention:received' => 'Received',
	'admin:indieweb:webmention:send' => 'Send',

	'item:object:webmention' => 'Webmention',
	'collection:object:webmention' => 'Webmentions',
	'item:object:syndication' => 'Syndication',
	'collection:object:syndication' => 'Syndications',
	
	'webmention:source:error' => 'Missing source URL',
	'webmention:target:error' => 'Missing target URL',
	'webmention:source:url:error' => 'Invalid source URL',
	'webmention:target:url:error' => 'Invalid target URL',
	'webmention:blocked:domain' => 'Domain %s is blocked to send webmentions',
	'webmention:recieved:success' => 'Webmention with GUID %s has been recieved and saved',
	'webmention:syndication:source:error' => 'Duplicate source URL %s to target URL %s for Syndication with GUID: %s',
	'webmention:duplicate:error' => 'Duplicate source %s, target %s and property %s',
	'webmention:create_comment:error' => 'Failed to create a comment: %s',
	
	'indieweb:webmention:source' => 'Source',
	'indieweb:webmention:target' => 'Target',
	'indieweb:webmention:parent_target' => 'Parent target',
	'indieweb:webmention:object_type' => 'Type',
	'indieweb:webmention:property' => 'Property',
	'indieweb:webmention:author_name' => 'Author name',
	'indieweb:webmention:author_photo' => 'Author avatar',
	'indieweb:webmention:author_url' => 'Author URL',
	'indieweb:webmention:data_url' => 'Data URL',
	'indieweb:webmention:rsvp' => 'RSVP',
	'indieweb:webmention:photo' => 'Photo',
	'indieweb:webmention:video' => 'Video',
	'indieweb:webmention:audio' => 'Audio',
	'indieweb:webmention:content_html' => 'HTML content',
	'indieweb:webmention:content_text' => 'Text content',
	'indieweb:webmention:status' => 'Status',
	'indieweb:webmention:published' => 'Published',
	'indieweb:webmention:none' => 'No webmentions received yet.',
	'indieweb:webmention:byline' => 'By %s',
	'indieweb:webmention:byline:on' => 'on %s',
	
	// pingback
	'settings:indieweb:wpingback_blocked_domains' => 'Block domains',
	'settings:indieweb:pingback_blocked_domains:help' => 'Block domains from sending pingbacks. Enter domains line per line.',
	'pingback:blocked:domain' => 'Domain %s is blocked to send pingbacks',
	
	// MICROPUB
	'settings:indieweb:micropub' => 'Basic Config',
	'settings:indieweb:enable_micropub' => 'Enable Micropub',
	'settings:indieweb:enable_micropub:help' => 'Allow posting to your site. Before you can post, you need to authenticate and enable the IndieAuth Authentication API.
See IndieAuth to configure. <a href="https://indieweb.org/Micropub">More information about micropub</a>.

A very good client to test is https://quill.p3k.io. A full list is available at https://indieweb.org/Micropub/Clients.
Indigenous (iOS and Android) are also microsub readers.',

	'indieweb:micropub:view:title' => 'Micropub endpoint',
	'indieweb:micropub:view:subtitle' => 'Even if you do not decide to use the micropub endpoint, this screen gives you a good overview what kind of content types and fields you can create which can be used for sending webmentions or read by microformat parsers.',
	
	'indieweb:micropub:view:article' => 'Article',
	'indieweb:micropub:view:article:desc' => "An article request contains 'content', 'name' and the 'h' value is 'entry'. Think of it as a blog post.",
	'indieweb:micropub:view:note' => 'Note',
	'indieweb:micropub:view:note:desc' => "A note request contains 'content', but no 'name' and the 'h' value is 'entry'. Think of it as a Tweet.",
	'indieweb:micropub:view:like' => 'Like',
	'indieweb:micropub:view:like:desc' => "A like request contains a URL in 'like-of' and 'h' value is 'entry'.",
	'indieweb:micropub:view:reply' => 'Reply',
	'indieweb:micropub:view:reply:desc' => "A reply request contains a URL in 'in-reply-to', has content and 'h' value is 'entry'.",
	'indieweb:micropub:view:repost' => 'Repost',
	'indieweb:micropub:view:repost:desc' => "A repost request contains a URL in 'repost-of' and 'h' value is 'entry'. In case content is stored, the link will be rendered as a quotation by the formatter.",
	'indieweb:micropub:view:bookmark' => 'Bookmark',
	'indieweb:micropub:view:bookmark:desc' => "A bookmark request contains a URL in 'bookmark-of' and 'h' value is 'entry'.",
	'indieweb:micropub:view:event' => 'Event',
	'indieweb:micropub:view:event:desc' => "An event request contains a start and end date and the 'h' value is 'event'.",
	'indieweb:micropub:view:rsvp' => 'Rsvp',
	'indieweb:micropub:view:rsvp:desc' => 'A RSVP request contains an RSVP field.',
	'indieweb:micropub:view:issue' => 'Issue',
	'indieweb:micropub:view:issue:desc' => "An issue request contains 'content', 'name', a URL in 'in-reply-to' (which is the URL of a repository) and the 'h' value is 'entry'.",
	'indieweb:micropub:view:checkin' => 'Checkin',
	'indieweb:micropub:view:checkin:desc' => "Experimental! A checkin request contains 'checkin' which is an URL and optionally a name or an h-card which contains url, name, latitude and longitude. 'Content' and 'name' are optional and the 'h' value is 'entry'.",
	'indieweb:micropub:view:geocache' => 'Geocache',
	'indieweb:micropub:view:geocache:desc' => "Experimental! A geocache request contains 'p-geocache-log-type', 'checkin' which is an URL and optionally a name or an h-card which contains url, name, latitude and longitude. 'Content' and 'name' are optional and the 'h' value is 'entry'.",
	'indieweb:micropub:view:trip' => 'Trip',
	'indieweb:micropub:view:trip:desc' => "Experimental! A trip request contains 'route' which is an collection of Geo URI 's. 'Content is optional and the 'h' value is 'entry'.",
	
	// MICROSUB
	'settings:indieweb:microsub' => 'Basic Config',
	'settings:indieweb:enable_microsub' => 'Enable Microsub',
	'settings:indieweb:enable_microsub:help' => 'Microsub is an early draft of a spec that provides a standardized way for clients to consume and interact with feeds collected by a server. 
<a href="https://indieweb.org/Microsub#Clients">Readers</a> are Indigenous (iOS and Android), Monocle and Together (both web) and many others to come. 
<a href="https://indieweb.org/Microsub#Servers">Servers</a> are Aperture, Ekster etc. 
Allows you to expose a microsub header link which can either be the built-in microsub server or set to an external service.',
	'settings:indieweb:microsub_endpoint' => 'External microsub endpoint',
	'settings:indieweb:microsub_endpoint:help' => 'Enter a custom microsub endpoint URL in case you do not use the built-in endpoint. Leave blank to use internal Microsub endpoint',
	'indieweb:microsub:cleanup_feeds' => 'Cleanup feed items',
	'indieweb:microsub:cleanup_feeds:help' => 'You can configure the number of items to keep per feed.',
	'indieweb:microsub:mark_unread' => 'Mark items unread on first import',
	'indieweb:microsub:mark_unread:help' => 'On a first import of a feed, items are marked as read. Switch on this setting to still mark them as unread.',
	'indieweb:microsub:allow_video' => 'Allow video in feeds',
	'indieweb:microsub:allow_video:help' => 'By default videos embedded with an iframe in content are stripped. Switch on this setting to allow YouTube and Vimeo in content.',
	'settings:indieweb:microsub_user_agent' => 'Default User agent when calling feeds',
	'indieweb:microsub:aggregated_feeds' => 'Aggregated feeds',
	'indieweb:microsub:aggregated_feeds:help' => "Some readers support viewing feeds per author (source), but this will not work in case of aggregated feeds.
Enter the base url's line by line which, in case they match will trigger a search instead internally on the author name so the response will work.",
	'indieweb:microsub:indigenous' => 'Indigenous',
	'indieweb:microsub:indigenous_send_push' => 'Send push notification to Indigenous',
	'indieweb:microsub:indigenous_api' => 'Push notification API key',
	'indieweb:microsub:indigenous_api:help' => 'If you use <a href="https://indigenous.realize.be/">Indigenous for Android</a>, you can send a push notification when a webmention is added to the notifications channel.
You need an account and a registered device, see https://indigenous.realize.be/push-notifications.
This feature only works if you use the built-in webmention and microsub endpoint.',
	'indieweb:microsub:aperture' => 'Aperture',
	'indieweb:microsub:aperture_send_push' => 'Send micropub request to Aperture',
	'indieweb:microsub:aperture_api' => 'Channel API key',
	'indieweb:microsub:aperture_api:help' => 'If you use <a href="https://aperture.p3k.io/">Aperture</a> as your Microsub server, you can send a micropub post to one channel when a webmention is received by this site.
The canonical example is to label that channel name as "Notifications" so you can view incoming webmentions on readers like Monocle or Indigenous.
Following webmentions are send: likes, reposts, bookmarks, mentions and replies.',

	'item:object:microsub_channel' => 'Microsub channel',
	'collection:object:microsub_channel' => 'Microsub channels',
	'admin:indieweb:microsub:channels' => 'Channels',
	'indieweb:microsub:channels:title' => 'Configure your channels and sources for the built-in Microsub server. In case the server is not enabled, no items will be fetched.',
	'add:object:microsub_channel' => 'Add Channel',
	'edit:object:microsub_channel' => 'Edit Channel: %s',
	'indieweb:microsub:microsub_channel:enable' => 'Enabled',
	'indieweb:microsub:microsub_channel:disable' => 'Disabled',
	'indieweb:microsub:microsub_channel:weight' => 'Weight',
	'indieweb:microsub:microsub_channel:read_indicator' => 'Read tracking',
	'indieweb:microsub:microsub_channel:read_indicator:count' => 'Show unread count',
	'indieweb:microsub:microsub_channel:read_indicator:indicator' => 'Show unread indicator',
	'indieweb:microsub:microsub_channel:read_indicator:disabled' => 'Disabled',
	'indieweb:microsub:microsub_channel:status' => 'Status',
	'indieweb:microsub:microsub_channel:items' => 'Items',
	'indieweb:microsub:microsub_channel:sources' => 'Sources',
	'indieweb:microsub:microsub_channel:exclude_post_type' => 'Exclude post types in timeline',
	'indieweb:microsub:post_type:reply' => 'Reply',
	'indieweb:microsub:post_type:repost' => 'Repost',
	'indieweb:microsub:post_type:bookmark' => 'Bookmark',
	'indieweb:microsub:post_type:like' => 'Like',
	'indieweb:microsub:post_type:note' => 'Note',
	'indieweb:microsub:post_type:article' => 'Article',
	'indieweb:microsub:post_type:photo' => 'Photo',
	'indieweb:microsub:post_type:video' => 'Video',
	'indieweb:microsub:post_type:checkin' => 'Checkin',
	'indieweb:microsub:post_type:rsvp' => 'Rsvp',
	'indieweb:microsub:microsub_channel:none' => 'No Microsub channels created yet.',
	'indieweb:microsub:microsub_channel:sources:view' => 'View sources list',
	'indieweb:microsub:microsub_channel:sources:list' => 'Sources in channel: %s',
	
	'item:object:microsub_source' => 'Microsub source',
	'collection:object:microsub_source' => 'Microsub sources',
	'admin:indieweb:microsub:sources' => 'Sources',
	'add:object:microsub_source' => 'Add Source',
	'edit:object:microsub_source' => 'Edit Source: %s',
	'indieweb:microsub:microsub_source:url' => 'URL',
	'indieweb:microsub:microsub_source:enable' => 'Enabled',
	'indieweb:microsub:microsub_source:disable' => 'Disabled',
	'indieweb:microsub:microsub_source:status' => 'Status',
	'indieweb:microsub:microsub_source:items' => 'Total Items',
	'indieweb:microsub:microsub_source:next_update' => 'Next update',
	'indieweb:microsub:microsub_source:feed_keep' => 'Feed/keep',
	'indieweb:microsub:microsub_source:last_update' => 'Last update',
	'indieweb:microsub:microsub_source:channel' => 'Channel',
	'indieweb:microsub:microsub_source:post_context' => 'Get post context for',
	'indieweb:microsub:microsub_source:fetch_interval' => 'Update interval',
	'indieweb:microsub:microsub_source:fetch_interval:help' => 'The length of time between feed updates. Requires a correctly configured cron task.',
	'indieweb:microsub:microsub_source:items_to_keep' => 'Items to keep',
	'indieweb:microsub:microsub_source:items_to_keep:help' => 'The number of items to keep when cleaning up feeds. Set to 0 to keep all.',
	'indieweb:microsub:microsub_source:websub' => 'Subscribe WebSub',
	'indieweb:microsub:microsub_source:websub:help' => 'If the feed supports WebSub, updates will come in via PuSH notifications.
A subscribe request will be send after submit.',
	'indieweb:microsub:microsub_source:fetch_interval:none' => 'None',
	'indieweb:microsub:microsub_source:fetch_interval:900' => '15 min',
	'indieweb:microsub:microsub_source:fetch_interval:1800' => '30 min',
	'indieweb:microsub:microsub_source:fetch_interval:3600' => '1 hour',
	'indieweb:microsub:microsub_source:fetch_interval:86400' => '1 day',
	'indieweb:microsub:microsub_source:fetch_interval:604800' => '1 week',
	'indieweb:microsub:microsub_source:fetch_interval:2419200' => '4 weeks',
	'indieweb:microsub:microsub_sources:none' => 'No Microsub sources added yet.',
	'indieweb:microsub:microsub_source:next_update:imminently' => 'Imminently',
	'indieweb:microsub:microsub_source:fetch_interval:websub:ended' => 'WebSub subscription ended',
	'indieweb:microsub:microsub_source:next_update:left' => '%s left',
	'indieweb:microsub:microsub_source:websub:left' => 'WebSub subscription ends in %s',
	
	'item:object:microsub_item' => 'Microsub source item',
	'collection:object:microsub_item' => 'Microsub source items',
	'indieweb:microsub:microsub_item:saved' => 'Saved Microsub source item: GUID %s',
	
	'indieweb:microsub:notification:new' => 'You have one new notification',
	'indieweb:microsub:notification:count' => 'You have %s new notifications',
	'indieweb:microsub:notification:author' => 'You have a notification from %s',
	'indieweb:microsub:notification:bookmark' => 'Bookmark available on <a href="%s">%s</a>',
	'indieweb:microsub:notification:mention' => 'You were mentioned',

];









