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

    // WEBMENTION
    'admin:indieweb:webmention' => 'Webmention',
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
    'settings:indieweb:webmention_enable_likes' => 'Allow likes',
    'settings:indieweb:webmention_enable_likes:help' => 'Webmentions can be liked',
    'settings:indieweb:webmention_objects_slugs' => 'Add objects URLs slugs',
    'settings:indieweb:webmention_objects_slugs:help' => 'For posting, we will check the presence of subtypes names in the links. Typically, this name is equivalent to the subtype, for example, in "domain.com/blog/view/123/title-of-the-post/", "blog" means the subtype "blog". But sometimes links can have other names, for example in "domain.com/stories/view/123/title-of-the-post/" "stories" are not equivalent to the subtype "story". In these cases, specify such URLs slugs here. Separate words with commas.',

    'settings:indieweb:webmention_clean' => 'Clean the failed Webmentions',
    'settings:indieweb:webmention_clean:help' => 'Unsent and unreceived Webmentions (Webmentions with errors) will be deleted daily',

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

    'settings:indieweb:webmention:sending' => 'Sending',
    'settings:indieweb:webmention:sending:help' => "The easiest way to syndicate content on social networks is by using https://brid.gy.
You have to create an account by signing in with your preferred social network. Bridgy is open source so you can also host the service yourself.

Syndicating and sending webmentions can be done per entity in the 'Publish to' fieldset, which is protected with the 'send webmentions' permission.
If no targets are configured, there is nothing to do. There is a syndication field on every entity and comment type available to render your syndications for POSSE-Post-Discovery https://indieweb.org/posse-post-discovery
If comments are enabled, put those fields only on the microformat view mode. The comment itself is available on comment/indieweb/cid and it is this URL that will be used for sending webmentions.",

    'settings:indieweb:syndication_targets' => 'Syndication targets',
    'settings:indieweb:syndication_targets:help' => "Enter every target line by line if you want to publish content, in following format:
<div>Name|webmention_url|selected|extra class</div>
<div>- selected is optional. Set to 1 so the target is default selected on the entity form.</div>
<div>- extra class is optional. Add a custom class to the link.</div>
<div>Example</div>
<div>Fediverse|https://fed.brid.gy/</div>
<div>Granary|https://granary.io/</div>
<div>IndieNews|https://news.indieweb.org/en|0|u-category</div>
<div>
When you add or remove targets, extra fields will be enabled on the manage display screens of every entity type (you will have to clear cache to see them showing up).
These need to be added on the page (usually on the 'full' view mode) because bridgy will check for the url in the markup, along with the proper microformat classes.
The field will print them hidden in your markup, even if you do not publish to that target, that will be altered later.
These targets are also used for the syndicate-to request if you are using Micropub.
Consult the README file that comes with this plugin if you want to integrate with the Fediverse.",

    'settings:indieweb:webmention_syndication_targets_custom' => 'Custom URL',
    'settings:indieweb:webmention_syndication_targets_custom:help' => 'Add a textfield to enter a custom URL to send a Webmention to.',

    'settings:indieweb:use_webmentions' => 'Select objects you want to enable Webmention for',

    'indieweb:webmention:stored' => 'Stored',
    'admin:indieweb:webmention:all' => 'All webmentions',
    'settings:indieweb:webmention:all' => 'All',
    'admin:indieweb:webmention:received' => 'Received webmentions',
    'settings:indieweb:webmention:received' => 'Received',
    'admin:indieweb:webmention:sent' => 'Sent webmentions',
    'settings:indieweb:webmention:sent' => 'Sent',
    'admin:indieweb:webmention:syndications' => 'Syndications',

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
    'webmention:send:success' => 'Webmention with GUID %s has been send and saved',
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
    'indieweb:webmention:none' => 'No webmentions yet.',
    'indieweb:webmention:byline' => 'By %s',
    'indieweb:webmention:byline:on' => 'on %s',
    'indieweb:webmention:syndication_targets_custom' => 'Enter a custom URL',
    'indieweb:webmention:publish' => 'Publish to',
    'indieweb:webmention:syndication:view' => '(View)',
    'indieweb:syndication:source_id' => 'Source GUID',
    'indieweb:syndication:url' => 'Source URL',
    'indieweb:syndication:none' => 'No Syndications yet.',

    // pingback
    'settings:indieweb:pingback_blocked_domains' => 'Block domains',
    'settings:indieweb:pingback_blocked_domains:help' => 'Block domains from sending pingbacks. Enter domains line per line.',
    'pingback:blocked:domain' => 'Domain %s is blocked to send pingbacks',

    // notifications
    'notification:object:webmention:create' => 'Send a notification when a webmention is created',
    'webmention:notification:subject' => 'New webmention on %s',
    'webmention:notification:body' => "You have a new webmention:

%s

To view the original item, click %s
",

    //CONTACTS
    'admin:indieweb:contacts' => 'Contacts',
    'item:object:indieweb_contact' => 'Contact',
    'collection:object:indieweb_contact' => 'Contacts',
    'indieweb:contacts:none' => 'No Contacts yet.',

    // MICROPUB
    'admin:indieweb:micropub' => 'Micropub',
    'settings:indieweb:micropub' => 'Basic Config',
    'settings:indieweb:enable_micropub' => 'Enable Micropub',
    'settings:indieweb:enable_micropub:help' => 'Allow posting to your Eldd app. Before you can post, you need to authenticate and enable the IndieAuth Authentication API.
See <u><a href="' . elgg_normalize_url('admin/indieweb/indieauth') . '">IndieAuth</a></u> to configure.',

    'settings:indieweb:enable_micropub_media' => 'Enable media endpoint',
    'settings:indieweb:enable_micropub_media:help' => 'This will enable the Micropub media endpoint to receive files, limited to images (JPG, PNG, GIF).',
    'settings:indieweb:micropub_enable_update' => 'Enable post updates',
    'settings:indieweb:micropub_enable_update:help' => 'Allow sending update requests to update any post or comment. Updating posts is currently limited to title, body and published status.',
    'settings:indieweb:micropub_enable_delete' => 'Enable post deletes',
    'settings:indieweb:micropub_enable_delete:help' => 'Allow sending delete requests to delete any post, comment or webmention.',
    'settings:indieweb:micropub_enable_source' => 'Enable post queries',
    'settings:indieweb:micropub_enable_source:help' => 'Allow clients to query for posts via q=source. This is experimental, leave disabled if your client does not support it.',
    'settings:indieweb:micropub_enable_category' => 'Enable category request',
    'settings:indieweb:micropub_enable_category:help' => 'Allow sending a request to return a list of tags.',
    'settings:indieweb:micropub_enable_geo' => 'Enable geo location request',
    'settings:indieweb:micropub_enable_geo:help' => 'Allow sending a request to return a geo location.',
    'settings:indieweb:micropub_enable_contact' => 'Enable contacts',
    'settings:indieweb:micropub_enable_contact:help' => 'This will allow requests on q=contact for managing Contacts. This includes listing, but also creating, updating or deleting Contacts via Micropub requests.',
    'settings:indieweb:micropub_log_payload' => 'Log the payload and responses',

    // posts
    'admin:indieweb:micropub:posts' => 'Micropub posts',
    'settings:indieweb:micropub:posts' => 'Posts',
    'indieweb:micropub:view:article' => 'Article',
    'indieweb:micropub:view:article:desc' => "An article request contains 'content', 'name' and the 'h' value is 'entry'. Think of it as a blog post.",
    'indieweb:micropub:view:note' => 'Note',
    'indieweb:micropub:view:note:desc' => "A note request contains 'content', but no 'name' and the 'h' value is 'entry'. Think of it as a Tweet or The Wire post.",
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

    'settings:indieweb:micropub:posts:enable' => 'Enable',
    'settings:indieweb:micropub:posts:status' => 'Status published',
    'settings:indieweb:micropub:posts:status:help' => 'When the payload contains the "post-status" property, its value will take precedence over this one. Disable to switch "Unpublished" status. See https://indieweb.org/Micropub-extensions#Post_Status',
    'settings:indieweb:micropub:posts:author' => 'Author of the post',
    'settings:indieweb:micropub:posts:author:help' => 'Default user when a post is created. If you are using the internal IndieAuth endpoint, the author of the post will be the owner of the token used in the Micropub request.',
    'settings:indieweb:micropub:posts:type' => 'Post type',
    'settings:indieweb:micropub:posts:type:help' => 'Select the post type to use for creating a post',
    'settings:indieweb:micropub:posts:field:link' => 'Link field',
    'settings:indieweb:micropub:posts:field:link:help' => 'Enable to store the link in the imprint, summary. Make sure the field exists on the post type.',
    'settings:indieweb:micropub:posts:send_webmention' => 'Send webmention',
    'settings:indieweb:micropub:posts:send_webmention:help' => 'Automatically send a Webmention to the URL that is found in the link field.',
    'settings:indieweb:micropub:posts:field:content' => 'Content field',
    'settings:indieweb:micropub:posts:field:content:help' => 'Enable to store the content in the body (text, description). Make sure the field exists on the post type.',
    'settings:indieweb:micropub:posts:field:upload' => 'File upload field',
    'settings:indieweb:micropub:posts:field:upload:help' => 'Enable to allow uploads the audio, images and videos, multiple if the field has this option. Make sure the field exists on the post type.',
    'settings:indieweb:micropub:posts:field:upload:limit' => 'File upload limit',
    'settings:indieweb:micropub:posts:field:tags' => 'Tags field',
    'settings:indieweb:micropub:posts:field:tags:help' => 'Enable to store the tags. Make sure the field exists on the post type.',
    'settings:indieweb:micropub:posts:field:date' => 'Date field',
    'settings:indieweb:micropub:posts:field:date:help' => 'Enable to store the date (for events, e.g. date range field). Make sure the field exists on the post type.',
    'settings:indieweb:micropub:posts:field:location' => 'Location field',
    'settings:indieweb:micropub:posts:field:location:help' => 'Enable to store the geo location. Make sure the field exists on the post type.',
    'settings:indieweb:micropub:posts:reply_create_comment' => 'Enable comment creation',
    'settings:indieweb:micropub:posts:reply_create_comment:help' => 'If a reply post comes in and the reply-to-url is a post or comment on this site, or a webmention which target is a post or comment and property "in-reply-to", create a (child) comment from this reply.
<u><a href="' . elgg_normalize_url('admin/indieweb/webmention') . '">Create a comment</a></u> needs to be enabled and configured for this to work. In case the target is a webmention, the original url can be stored if a link field is configured on the comment type.',

    //RSVP
    'field:rsvp' => 'RSVP',
    'field:rsvp:help' => 'An RSVP is a reply to an event that says whether the sender is attending, is not attending, might attend, or is merely interested.',
    'field:rsvp:yes' => 'I will attend',
    'field:rsvp:no' => 'I am not going',
    'field:rsvp:maybe' => 'I might attend',
    'field:rsvp:interested' => 'I am interested',

    'indieweb:micropub:rsvp:title' => 'RSVP on %s',
    'indieweb:micropub:checkin:title' => 'Checked in at %s',
    'indieweb:micropub:repost:title' => 'Repost of %s',
    'indieweb:micropub:bookmark:title' => 'Bookmark of %s',
    'indieweb:micropub:like:title' => 'Like of %s',
    'indieweb:micropub:reply:title' => 'In reply to %s',
    'error:post:save' => 'The post could not be saved.',

    // MICROSUB
    'admin:indieweb:microsub' => 'Microsub',
    'settings:indieweb:microsub' => 'Basic Config',
    'settings:indieweb:enable_microsub' => 'Enable Microsub',
    'settings:indieweb:enable_microsub:help' => 'Microsub is an early draft of a spec that provides a standardized way for clients to consume and interact with feeds collected by a server. 
<a href="https://indieweb.org/Microsub#Clients">Readers</a> are Indigenous (iOS and Android), Monocle and Together (both web) and many others to come. 
<a href="https://indieweb.org/Microsub#Servers">Servers</a> are Aperture, Ekster etc. 
Allows you to expose a microsub header link which can either be the built-in microsub server or set to an external service.',
    'settings:indieweb:microsub_endpoint' => 'External microsub endpoint',
    'settings:indieweb:microsub_endpoint:help' => 'Enter a custom microsub endpoint URL in case you do not use the built-in endpoint. Leave blank to use internal Microsub endpoint',
    'indieweb:microsub:anonymous' => 'Enable anonymous requests',
    'indieweb:microsub:anonymous:help' => "Whether anonymous requests on the Microsub endpoint are allowed or not.

This allows getting channels and the posts in that channel.
Write operations (like managing channels, subscribing, search, marking (un)read etc) will not be allowed when the request is anonymous.",

    'indieweb:microsub:cleanup_feeds' => 'Cleanup feed items',
    'indieweb:microsub:cleanup_feeds:help' => 'You can configure the number of items to keep per feed.',
    'indieweb:microsub:mark_unread' => 'Mark items unread on first import',
    'indieweb:microsub:mark_unread:help' => 'On a first import of a feed, items are marked as read. Switch on this setting to still mark them as unread.',
    'indieweb:microsub:allow_video' => 'Allow video in feeds',
    'indieweb:microsub:allow_video:help' => 'By default videos embedded with an iframe in content are stripped. Switch on this setting to allow YouTube and Vimeo in content.',
    'settings:indieweb:microsub_user_agent' => 'Default User agent when calling feeds',
    'indieweb:microsub:context' => 'Post context',
    'indieweb:microsub:context:label' => 'Fetch content',
    'indieweb:microsub:context:help' => 'When you create a post with a link which is a reply, like, repost or bookmark of an external post, you can fetch content from that URL so you can render more context.
You can also enable fetching of contexts on microsub items when you use the built-in microsub server.',
    'indieweb:microsub:aggregated_feeds' => 'Aggregated feeds',
    'indieweb:microsub:aggregated_feeds:help' => 'Some readers support viewing feeds per author (source), but this will not work in case of aggregated feeds.
Enter the base url line by line which, in case they match will trigger a search instead internally on the author name so the response will work.
<div>For example, you can use <u><a href="' . elgg_normalize_url('mod/indieweb/lib/aggregated_feeds.md') . '">this list of the feeds</a></u>.</div>',
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

    'indieweb:microsub:external:help' => "Don't forget to fill in the 'External microsub endpoint option'",

    'item:object:microsub_channel' => 'Microsub channel',
    'collection:object:microsub_channel' => 'Microsub channels',
    'admin:indieweb:microsub:channels' => 'Channels',
    'indieweb:microsub:channels:title' => 'Configure your channels and sources for the built-in Microsub server. In case the server is not enabled, no items will be fetched.',
    'add:object:microsub_channel' => 'Add Channel',
    'edit:object:microsub_channel' => 'Edit Channel: %s',
    'add:object:microsub_channel:notifications' => 'Add Notifications channel',
    'add:object:microsub_channel:notifications:help' => 'To start work with Channels, you should create an internal Notifications channel',
    'indieweb:microsub_channel:notifications:create' => 'Click on Save to add Notifications channel',
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
    'indieweb:microsub_channel:notifications' => 'Notifications',
    'indieweb:microsub:microsub_channel:enable:success' => 'Microsub channel has been enabled',
    'indieweb:microsub:microsub_channel:disable:success' => 'Microsub channel has been disabled',
    'indieweb:microsub:microsub_channel:status:fail' => 'Failed to change a status',

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
    'indieweb:microsub:microsub_source:none' => 'No Microsub sources added yet.',
    'indieweb:microsub:microsub_source:next_update:imminently' => 'Imminently',
    'indieweb:microsub:microsub_source:fetch_interval:websub:ended' => 'WebSub subscription ended',
    'indieweb:microsub:microsub_source:next_update:left' => '%s left',
    'indieweb:microsub:microsub_source:websub:left' => 'WebSub subscription ends in %s',
    'indieweb:microsub:microsub_source:enable:success' => 'Microsub source has been enabled',
    'indieweb:microsub:microsub_source:disable:success' => 'Microsub source has been disabled',
    'indieweb:microsub:microsub_source:status:fail' => 'Failed to change a status',

    'item:object:microsub_item' => 'Microsub source item',
    'collection:object:microsub_item' => 'Microsub source items',
    'admin:indieweb:microsub:items' => 'Microsub source items',
    'indieweb:microsub:microsub_item:saved' => 'Saved Microsub source item: GUID %s',
    'indieweb:microsub:microsub_source:items:list' => 'Items in source: %s',
    'indieweb:microsub:microsub_source:items:view' => 'View items',
    'indieweb:microsub:microsub_item:none' => 'No Microsub items added yet.',
    'indieweb:microsub:microsub_item:is_read' => 'Is read?',
    'indieweb:microsub:microsub_item:read' => 'Yes',
    'indieweb:microsub:microsub_item:no_read' => 'No',
    'indieweb:microsub:microsub_item:data' => 'Data',
    'indieweb:microsub:microsub_item:data_view' => 'View data',

    'indieweb:microsub:notification:new' => 'You have one new notification',
    'indieweb:microsub:notification:count' => 'You have %s new notifications',
    'indieweb:microsub:notification:author' => 'You have a notification from %s',
    'indieweb:microsub:notification:bookmark' => 'Bookmark available on <a href="%s">%s</a>',
    'indieweb:microsub:notification:mention' => 'You were mentioned',

    // INDIEAUTH
    'admin:indieweb:indieauth' => 'IndieAuth',
    'settings:indieweb:indieauth' => 'Basic Config',
    'settings:indieweb:indieauth:tokens' => 'Tokens',
    'settings:indieweb:indieauth:codes' => 'Authorization codes',
    'admin:indieweb:indieauth:tokens' => 'IndieAuth Tokens',
    'admin:indieweb:indieauth:codes' => 'IndieAuth Authorization codes',
    'settings:indieweb:indieauth:api' => 'Authentication',
    'settings:indieweb:indieauth:login' => 'Enable login',
    'settings:indieweb:indieauth:login:help' => 'Allow users to login into this site by using their domain. A "Sign-In" block is available where users can enter their domain to login.
<div>After authentication a new user account will be created if this domain does not exist yet. The account will automatically be verified</div>.',
    'settings:indieweb:indieauth:endpoint' => 'Use built-in authentication endpoint',
    'settings:indieweb:indieauth:endpoint:help' => "Use the internal authorize and token endpoints to authenticate with an Elgg user. The user needs the site administrator permission.
<div>The endpoints are available at <strong>" . elgg_get_site_url() . "indieauth/auth</strong> and <strong>" . elgg_get_site_url() . "indieauth/token</strong></div>",
    'settings:indieweb:indieauth:keys' => 'Keys',
    'settings:indieweb:indieauth:keys:public_key' => 'Public key',
    'settings:indieweb:indieauth:keys:public_key:help' => 'The path to the public key file',
    'settings:indieweb:indieauth:keys:private_key' => 'Private key',
    'settings:indieweb:indieauth:keys:private_key:help' => 'The path to the private key file',
    'settings:indieweb:indieauth:keys:generate_keys' => 'Generate keys on save',
    'settings:indieweb:indieauth:keys:help' => 'Configure the paths to the public and private keys which are used for encrypting the access tokens.
<div>If you choose to generate keys, the default path where these keys are stored is set to DATA_DIRECTORY/indieweb/indieauth.</div>
<div>Check the README for more information.</div>',
    'settings:indieweb:indieauth:external' => 'External endpoint',
    'settings:indieweb:indieauth:external:auth' => 'External authorization endpoint',
    'settings:indieweb:indieauth:external:endpoint' => 'External token endpoint',
    'settings:indieweb:indieauth:notes' => 'If you use apps like Quill (https://quill.p3k.io - web) or Indigenous (iOS, Android) or other clients which can post via Micropub or read via Microsub, the easiest way to let those clients log you in with your domain is by using indieauth.com and exchange access tokens for further requests. Only expose those links if you want to use Micropub or Microsub.
<div><strong>Important</strong>: if you add the token endpoint manually, and the endpoint is an external service, you still need to enter the URL here because it is used by the Micropub and/or Microsub endpoint.</div>',

    'item:object:indieauth_token' => 'IndieAuth token',
    'collection:object:indieauth_token' => 'IndieAuth tokens',
    'add:object:indieauth_token' => 'Add token',
    'indieweb:indieauth:token:no_results' => 'No tokens yet.',
    'indieweb:indieauth:token' => 'Token',
    'indieweb:indieauth:token:status' => 'Status',
    'indieweb:indieauth:token:status:active' => 'Active',
    'indieweb:indieauth:token:status:revoked' => 'Revoked',
    'indieweb:indieauth:token:client' => 'Client',
    'indieweb:indieauth:token:access' => 'Last access',
    'indieweb:indieauth:token:actions' => 'Actions',

    'item:object:indieauth_code' => 'IndieAuth authorization code',
    'collection:object:indieauth_code' => 'IndieAuth authorization codes',
    'indieweb:indieauth:code:no_results' => 'No codes yet.',
    'indieweb:indieauth:code' => 'Code',
    'indieweb:indieauth:code:status' => 'Status',
    'indieweb:indieauth:code:client' => 'Client',
    'indieweb:indieauth:code:expires' => 'Expires',
    'indieweb:indieauth:code:actions' => 'Actions',
    'indieweb:indieauth:code:status:active' => 'Active',
    'indieweb:indieauth:code:status:revoked' => 'Revoked',

    'indieweb:indieauth:keys:generate_keys' => 'Something went wrong generating the keys, please check your logs.',
    'indieweb:indieauth:auth:no_login' => 'Login first with your account. You will be redirected to the authorize screen on success.',
    'indieweb:indieauth:auth:invalid' => 'Invalid request, missing parameters.',
    'indieweb:indieauth:auth:permission' => 'You do not have permission to authorize.',

    'indieweb:indieauth:authorize' => 'Authorize with IndieAuth',
    'indieweb:indieauth:authorize:title' => 'The app <strong>%s</strong> would like to access your app, using the credentials of <strong>%s</strong>',
    'indieweb:indieauth:authorize:scopes' => 'The app is requesting the following <a href="https://indieweb.org/scope" target="_blank">scopes</a>',
    'indieweb:indieauth:authorize:redirect' => 'You will be redirected to <strong>%s</strong> after authorizing this app.',
    'indieweb:indieauth:authorize:submit' => 'Authorize',
    'indieweb:indieauth:authorize:authorized' => 'Authorized',
    'indieweb:indieauth:authorize:cancel' => 'Cancelled',
    'indieweb:indieauth:authorize:fail' => 'Authorization Code creation failed',
    'indieweb:indieauth:deauthorize' => 'Deauthorize IndieAuth',
    'indieweb:indieauth:login' => 'Authorize with IndieAuth',
    'indieweb:indieauth:login:label' => 'Add web address',
    'indieweb:indieauth:login:help' => 'In order to sign in to ' . elgg_get_site_entity()->getDisplayName() . ', you will need the built-in IndieAuth auth and token endpoints.',
    'indieweb:indieauth:login:email' => 'Add your valid email',
    'indieweb:indieauth:login:email:help' => elgg_get_site_entity()->getDisplayName() . ' requires an email address to register a new account. 
<div>If you have already registered on ' . elgg_get_site_entity()->getDisplayName() . ', add the email address from your account.</div>',
    'indieweb:indieauth:login:success' => 'You have successfully authorized with IndieAuth.',
    'indieweb:indieauth:login:success:already' => 'You are already authorized with this URL.',
    'indieweb:indieauth:login:fail' => 'No authorization endpoint found.',

    'indieweb:indieauth:token:save:success' => 'IndieAuth token has been saved',
    'indieweb:indieauth:token:save:fail' => 'Failed to save IndieAuth token',
    'indieweb:indieauth:token:activate' => 'Activate',
    'indieweb:indieauth:token:activate:success' => 'IndieAuth token has been activated',
    'indieweb:indieauth:token:revoke' => 'Revoke',
    'indieweb:indieauth:token:revoke:success' => 'IndieAuth token has been revoked',
    'indieweb:indieauth:token:status:fail' => 'Failed to change a status',
    'indieweb:indieauth:view_jwt' => 'View JWT token',
    'indieweb:indieauth:view_jwt:title' => 'JWT token',
    'indieweb:indieauth:view_jwt:info' => 'Copy this string which can be used for requests to the Micropub or Microsub endpoint.',

    'indieauth:token:scope' => 'Scope',
    'indieauth:token:scope:help' => 'Separate scopes by space',
    'indieauth:token:client_id' => 'Client',

    'indieauth:provider' => 'IndieAuth',
    'indieauth:account:authorise' => 'Connect now',
    'indieauth:account:deauthorise' => 'Disconnect now',
    'indieauth:accounts' => 'IndieAuth account',

    //WEBSUB
    'admin:indieweb:websub' => 'WebSub',
    'settings:indieweb:websub' => 'Basic Config',
    'settings:indieweb:enable_websub' => 'Enable WebSub',
    'settings:indieweb:enable_websub:help' => 'Allows to notify hubs when you publish content. You can subscribe to WebSub enabled feeds in all Microsub sources.',
    'settings:indieweb:websub_endpoint' => 'Hub endpoint',
    'settings:indieweb:websub_endpoint:help' => 'Configure the hub used to publish and where people can subscribe.',
    'settings:indieweb:websub_pages' => 'Discovery',
    'settings:indieweb:websub_pages:help' => "Specify pages by using their paths to which people can subscribe to. Enter one path per line and do not use wildcards. You can also include RSS pages. 
<div>We recommend add the pages of those objects that you have chosen for publication to the hub. E.g., for blog it would be <em>" . elgg_get_site_url() . "blog/all</em>, <em>" . elgg_get_site_url() . "blog/all?view=rss</em></div>",
    'settings:indieweb:websub_send' => 'Send the publication to the hub',
    'settings:indieweb:websub_send:help' => 'Publications are not send immediately, but are stored in a queue when the content is published.',
    'settings:indieweb:websub_resubscribe' => 'Resubscribe to subscriptions',
    'settings:indieweb:websub_resubscribe:help' => 'Subscriptions are active for a limited time, usually not more than two weeks.
This allows you to automatically resubscribe, leave disabled if you do not have any WebSub subscriptions.',
    'settings:indieweb:websub_notification' => 'Handle content notifications from hubs',
    'settings:indieweb:websub_notification:help' => 'Incoming notifications from hubs with content your are subscribed to are not saved immediately but stored in a queue.',
    'settings:indieweb:websub_micropub_publish' => 'Publish to the hub when you create a post with Micropub',
    'settings:indieweb:websub_microsub_subscribe' => 'Send a subscribe or unsubscribe request when managing feeds through the Microsub API',
    'settings:indieweb:websubpub_clean' => 'Clean the published WebSub publications',
    'settings:indieweb:websubpub_clean:help' => 'Published WebSubPubs will be deleted daily',
    'settings:indieweb:websub_log_payload' => 'Log the payload and responses',
    'settings:indieweb:use_websub' => 'Select objects you want to send a publication to the hub',

    'admin:indieweb:websub:pub' => 'WebSubPub',

    'indieweb:websub:hub_publication' => 'WebSub',
    'indieweb:websub:hub_publication:label' => 'Publish to hub',
    'indieweb:websub:create:notification:item' => 'Error create notification item: URL - %s',

    'indieweb:websub:hub_publication' => 'WebSub',
    'indieweb:websub:hub:subject' => 'WebSub notification',
    'indieweb:websub:hub:body' => 'You have received the following content:
%s

Source: %s
',
    'item:object:websubpub' => 'WebSubPub',
    'indieweb:websub:entity_type_id' => 'Entity type',
    'indieweb:websub:entity_id' => 'Entity GUID',
    'indieweb:websub:published' => 'Published',
    'indieweb:websub:none' => 'No WebSupPubs yet.',
    'indieweb:websub:websubpub:view' => '(View)',
];
