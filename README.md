IndieWeb
=========
![Elgg 6.1](https://img.shields.io/badge/Elgg-6.1-purple.svg?style=flat-square)

IndieWeb integration for Elgg

## About

Integrates the philosophy of [IndieWeb](https://indieweb.org/) in your Elgg website.
Based on the original [IndieWeb](https://www.drupal.org/project/indieweb) Drupal plugin by [Kristof De Jaeger aka swentel](https://git.drupalcode.org/swentel).

## Features

* [Webmention](https://www.w3.org/TR/webmention): send and receive webmentions and pingbacks; send webmentions and syndicate content, likes etc via [brid.gy](https://brid.gy), store syndications; auto-create comments from `in-reply-to`; reply on comments and send webmention via Webmention.io and another external or internal endpoint 
* [Microformats](https://microformats.org/wiki/microformats2): apply Microformats2 to your markup; Microformats for content, images and more
* [IndieAuth](https://indieweb.org/IndieAuth): allow users to login and create accounts with IndieAuth; built-in IndieAuth Authorization and Authentication API; expose endpoints and use external or internal endpoint
* [Micropub](https://indieweb.org/Micropub): Create, update and delete content note, article, event, rsvp, reply, like, repost, bookmark, checkin and issue
* [Microsub](https://indieweb.org/Microsub): Microsub built-in server or use external service; expose a Microsub endpoint, external or internal
* Feeds: create JF2 feeds
* Media cache: store images locally for internal webmention and microsub endpoint
* [WebSub](https://indieweb.org/WebSub): WebSub PuSH 0.4 for publishing and subscribing, integrates with Microsub support for feed subscription
* Contacts: store contacts for Micropub contact query, allowing for autocomplete


## Webmentions

Webmention.io is a hosted service created to easily handle `webmentions` on any web page. 
Webmentions in Elgg exposes an endpoint `/webmention` to receive Webmentions via this service. 

[Webmention.io](https://webmention.io/) is open source so you can also host the service yourself.

You need an account for receiving the webhooks at [Webmention.io](https://webmention.io) As soon as one Webmention is recorded at that
service, you can set the webhook to [https://your_domain/webmention](https://your_domain/webmention) and enter a secret.

To create an account, you need to authenticate with [IndieAuth](https://indieauth.com/) which requires you to add the `rel=me` attribute on links to your social accounts. 
See [Setup Instructions](https://indieauth.com/setup) for full instructions. See also `IndieAuth` further below.

You can also use the built-in endpoint so you don't need to rely on external services.

Configuration is at `/admin/indieweb/webmention`
Overview of all recieved Webmentions are at `/admin/indieweb/webmention/received`

### Sending Webmentions and Syndicating content with Bridgy

Syndicating and sending Webmentions in Elgg can be done per publishing a new entity in the `Publish to` fieldset, which is protected with the `Sending` configuration at `/admin/indieweb/webmention`

For Syndicating, a `checkbox` will be available on the entity form for sending your content per target (e.g. Fediverse etc). 
There is also a `syndication field` available to render your syndications for [POSSE-Post-Discovery](https://indieweb.org/posse-post-discovery)

You can also configure to just enter a `Custom URL` field to send a Webmention to.

When you toggle to syndicate and an entity is created, Webmention will send to Bridgy for instance.

[Bridgy](https://brid.gy) pulls comments, likes, and reshares on social networks back to your web site. You can also use it to post to
social networks - or comment, like, reshare, or even RSVP - from your own web site. Bridgy is open source so you can
also host the service yourself. To receive content from those networks, bridgy will send a webmention, so you only need
to enable the webmention endpoint.

Your content needs to have proper `Microformat` classes on your content, images etc.

Note: Bridgy prefers `p-summary` over `e-content`, but for original tweets `p-name` first. 
See [https://brid.gy/about#microformats](https://brid.gy/about#microformats)

You can preview your posts on Bridgy to verify your markup is ok.

Overview of all sent Webmentions are at `/admin/indieweb/webmention/sent`

## Microformats

Microformats in Elgg are extensions to HTML for marking up people, organizations, events, locations, blog posts, products,
reviews, resumes, recipes etc.

Sites use [Microformats](https://indieweb.org/microformats) to publish a standard API that is consumed and used by search engines, aggregators, and other tools. 

An example:

```
  <p class="h-card">My name is <a class="u-url p-name" rel="me" href="/">Your name</a>
```

**Classes added for publication (or other functionality)**

- `h-entry`: added on entity wrapper
- `h-event`: added on entity wrapper for an event
- `dt-published`, `u-url` and `p-name` in entity `metadata`
- `e-content`: added on default `body` field
- `p-summary`: added on default `summary` field
- `u-photo`: added on image styles
- `u-video`: added on video styles

Several field formatters for links, categories, RSVP, geocache, checkin and geo are also available.

There is a special case for quotations: in case you have `link` field and a `body`, the repost formatter will set a static
variable so that the `body` field and the `link` field are moved inside a special container. This only works with the `body`
field, so make sure you use that field for content on an entity subtype.

## Micropub

Allow posting to your Elgg app. 

Before you can post, you need to authenticate and enable the IndieAuth Authentication API.
Every request will contain an access token which will be verified to make sure it is really you who is posting. 

See IndieAuth to configure. 
More information about [Micropub](https://indieweb.org/Micropub).

A very good client to test is [quill](https://quill.p3k.io). 
A full list is available at [Micropub Clients](https://indieweb.org/Micropub/Clients).
Indigenous (for iOS and Android) are also micropub readers.

Even if you do not decide to use the Micropub endpoint, the configuration screen gives you a good overview what kind of
content types and fields you can create which can be used for sending Webmentions or read by Microformat parsers.

A media endpoint is also available where you can upload files (audio, images and videos only).

You can configure Micropub at `/admin/indieweb/micropub`

### Supported post types

- Article: a blog post
- Note: a small post, think of it as a tweet or The Wire post
- Reply: reply on a URL
- Repost: repost a URL
- Like: like a URL
- Bookmark: bookmark a URL
- Event: create an event
- RSVP: create a RSVP
- Issue: create an issue on a repo
- Checkin: checkin at a location

Read more about [Posts](https://indieweb.org/posts).

You can configure this at `/admin/indieweb/micropub/posts`

### Contacts

Allows storing contacts which can be used for a Micropub contact query.

The Micropub `contact` endpoint can be enabled at `/admin/indieweb/micropub`.

More info at [Nicknames](https://indieweb.org/nicknames-cache)

## Microsub

[Microsub](https://indieweb.org/Microsub) is an early draft of a spec that provides a standardized way for clients to consume and interact with feeds collected by a server. 

Readers are Indigenous (iOS and Android), Monocle and Together and many others to come.
Servers are Aperture, Ekster etc.

For more information see [Microsub-spec](https://indieweb.org/Microsub-spec)

Microsub in Elgg allows you to expose a Microsub header link which can either be the built-in Microsub server or set to an external service. 

Channels and sources for the built-in server are managed at `/admin/indieweb/microsub/channels`

Note: Servers must always have a channel with the `uid` `notifications`, and must always have at least one other channel for a user.

Microsub actions implemented:

- `GET action=channels`: retrieve the list of channels
- `GET action=timeline`: retrieve the list of items in a channel
- `POST action=timeline`: mark entries as read, move or remove an entry from a channel
- `POST action=channels`: create, update, order and delete channels
- `POST action=follow, unfollow`: subscribe, unsubscribe to feed, update feed channel
- `POST/GET action=search, preview`: search and preview url

Tip: use HTML formatting to get the best context on posts.

Note: when you configure a feed to `Cleanup feed items`, internally we count 5 items more by default. The reason is that
some feeds use pinned items (e.g. Mastodon) which can come and go and mix up the total visual items on a page. Or simply because a post was deleted later.

**Aperture**

If you use [Aperture](https://aperture.p3k.io/) as your Microsub server, you can send a Micropub post to one channel when a Webmention is received by this site. 
The canonical example is to label that channel name as `Notifications` so you can view incoming Webmentions on readers like Monocle or Indigenous. 
Following Webmentions are send: `likes`, `reposts`, `bookmarks`, `mentions` and `replies`.

**Requests**

By default, all requests on the Microsub endpoint are anonymous.
This allows getting channels and the posts in that channel.
Write operations (like managing channels, subscribing, search, marking (un)read etc) will not be allowed when the request is anonymous.

This is ideal for showcasing an endpoint in a reader for instance.

Note: You can still have authenticated requests too, but items will always be marked unread because of the anonymous requests.

## Media cache

When using the built-in Webmention/Microsub endpoint or Contacts, a lot of file urls are stored to external images. These files will be downloaded and cached locally. 
The cache is generated when the Webmention or Microsub items are processed so the impact on request is minimal.

## IndieAuth

[IndieAuth](https://indieweb.org/IndieAuth) is a way to use your own domain name to sign in to websites. It works by linking your website to one or more authentication providers such as Twitter or Google, then entering your domain name in the login form on websites that support IndieAuth. 

[Indieauth.com](https://indieauth.com) and [Indielogin.com](https://indielogin.com) is a hosted service that does this for you and the latter also provides Authentication API. 
Both are open source so you can also host the service yourself.

The easy way is to add `rel="me"` links on your homepage which point to your social media accounts and on each of those services adding a link back to your home page. 
They can even be hidden.

```
  <a href="https://twitter.com/elgg" target="_blank" title="Twitter" rel="me"></a>
```

You can also use a PGP key if you don't want to use a third party service. 
See https://indieauth.com/setup for full details. 

This plugin does not expose any of these links or help you with the PGP setup, you will have to manage this yourself.

If you use apps like [Quill](https://quill.p3k.io) - web or Indigenous (iOS, Android) or other clients which can post via Micropub or read via Microsub, the easiest way to let those clients log you in with your domain is by using https://indieauth.com too and exchange access tokens for further requests. 
Only expose these header links if you want to use Micropub or Microsub.

You can also use the built-in auth and token endpoints. 
You then authorize yourself with an Elgg user (for site administrators only).
PKCE support is included.

### Public and private keys

When using the built-in endpoint, access tokens are encrypted using a private key and decrypted with a public key.
You can generate those via the UI, or manually create them by running following commands:

```
openssl genrsa -out private.key 2048
openssl rsa -in private.key -pubout > public.key
```

Ideally, those keys live in a folder outside your webroot. 
If that is not possible, make sure the permissions are set to 600. 
Fill in the path afterwards at `/admin/indieweb/indieauth`.

## WebSub

WebSub (previously known as PubSubHubbub or PuSH, and briefly PubSub) is a notification-based protocol for web
publishing and subscribing to streams and legacy feed files in real time. 

WebSub for Elgg allows you to publish your content to a hub and also receive notifications from a hub for Microsub feeds so that polling isn't necessary. 

The default hub for publishing is [switchboard](https://switchboard.p3k.io). 

[pubsubhubbub](https://pubsubhubbub.appspot.com/) and [superfeedr](https://superfeedr.com/) work as well for getting subscription and notifications. 

When you toggle to publish, an entry is created in the queue which you can either handle by cron. 
This will send a request to the configured hub. 
An overview of published content is at `/admin/indieweb/websub/pub`.

More configuration is at configuration at `/admin/indieweb/websub`.

For more information see [How to publish and consume WebSub](https://indieweb.org/How_to_publish_and_consume_WebSub) and [WebSub](https://www.w3.org/TR/websub/).

## Feeds

Generate feeds in JF2.

You will need feeds when:

* you use Bridgy: the service will look for html link headers with `rel="feed"` and use those pages to crawl so it knows to which content it needs to send webmentions to.
* you want to allow IndieWeb readers (Monocle, Together, Indigenous) to subscribe to your content. These are alternate types which can either link to a page with microformat entries. It's advised to have an `h-card` on that page too as some parsers don't go to the homepage to fetch that content.

For example, use `yourdomain.com/blog/all?view=jf2feed` to view feeds.

For more information see:

* [Feed](https://indieweb.org/feed)
* [JF2](https://indieweb.org/jf2)

## Fediverse via Bridgy Fed or ActivityPub plugin

Bridgy Fed lets you interact with federated social networks like Mastodon and Hubzilla from your Elgg app. 
It translates replies, likes, and reposts from Webmentions to federated social networking protocols like ActivityPub and OStatus, and vice versa. 
Bridgy Fed is open source so you can also host the service yourself. 
See [Bridgy Fed](https://fed.brid.gy/)

Currently supports Mastodon, with more coming. You don't need any account at all on any of the social networks.

Just add `Fediverse|https://fed.brid.gy/` as a syndication target and select it when posting new content.. 
Posts, replies, likes, boosts and follows work fine.

* Check [Bridgy Fed setup](https://fed.brid.gy/docs#setup) for additional setup.
* If you use a microsub server, you can subscribe to fediverse users through the microformats feed.

If you want to interact with the Fediverse using Elgg itself, you can also install the [ActivityPub plugin](https://github.com/RiverVanRain/activitypub) for Elgg.

## IndieWebify.me / sturdy-backbone.glitch.me / xray.p3k.io

Use [IndieWebify.me](https://indiewebify.me/) to perform initial checks to see if your site is Indieweb ready. 
It can scan for certain markup after you've done the configuration with this module (and optionally more yourself).

Note that author discovery doesn't fully work 100% on IndieWebify for posts, use [https://sturdy-backbone.glitch.me](https://sturdy-backbone.glitch.me).

Another good tool is [http://xray.p3k.io](http://xray.p3k.io), which displays the results in JSON.

### Usage

Once activated the plugin will provide the following functionality:

* It will listen to the create events of objects and, if the object's description contains URLs, it will attempt to send a webmention to it.
* It exposes a webmention endpoint on http://yoursite.url/webmention/ (setting the appropriate header values). 

### OpenWeb Icons

We use [OpenWeb Icons](http://pfefferle.github.io/openwebicons/) to make some of the menu items look pretty.

Use this code snippet to add icons to menus:

```php

<i class="openwebicons-indieweb" style="font-size: 16px;"></i>

<i class="openwebicons-feed-colored" style="font-size: 20px;"></i>

```

More examples on the [project's page](http://pfefferle.github.io/openwebicons/)

### Developer snippets

```php

/** Elgg\IndieWeb\Webmention\Client\WebmentionClient **/
$svc = elgg()->webmention;
$response = $svc->get($source);

/** Elgg\IndieWeb\Microsub\Client\MicrosubClient **/
$microsub_client = elgg()->microsub;
$microsub_client->sendNotification($webmention, $parsed);

/** \Elgg\IndieWeb\Cache\MediaCacher **/
$image = elgg()->mediacacher->saveImageFromUrl($author_value);

// Get the target guid
$target_guid = indieweb_get_guid($target);
$webmention->setMetadata('target_guid', $target_guid);
						
$target = indieweb_get_path($target);
$webmention->setMetadata('target', $target);

```












