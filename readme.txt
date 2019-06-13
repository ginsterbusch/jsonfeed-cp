=== JSON Feed for ClassicPress (jsonfeed.org) ===
Contributors: fwolf
Tags: jsonfeed, json, feed, feeds, classicpress
Requires at least: 4.9
Tested up to: 5.2
Requires PHP: 5.6
Stable tag: 2.0
License: GPL-2.0+
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Adds feeds in JSON Feed format. Fork of the original `jsonfeed-wp` plugin. Partial rewrite for ClassicPress & compatible CMSes.

== Description ==

Adds a JSON Feed to your ClassicPress or WordPress site by adding `/feed/json` in the HTML header to any public URL.

The JSON Feed format is a pragmatic syndication format, like RSS and Atom, but with one big difference: it's JSON instead of XML. Learn more about it at [jsonfeed.org](http://jsonfeed.org/).

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/jsonfeed-cp` directory.
1. Activate the plugin through the 'Plugins' screen in ClassicPress

== Frequently Asked Questions ==

= What is JSONFeed? =

JSON Feed, a format similar to RSS and Atom but in JSON. JSON has become the developers’ choice for APIs, and that developers will often go out of their way to avoid XML.
JSON is simpler to read and write, and it’s less prone to bugs.

= Can I add other fields to the feed? =

Yes you can! There is a filter, `json_feed_item`, that allows you to modify the items in the feed just before they're inserted into the feed itself. For example, if you want to add a link to a post author's archive page to the respective item in the feed, you can use the following code:

```
function wp_custom_json_feed_fields( $feed_item, $post ){
    $feed_item['author']['archive_link'] = get_author_posts_url( $post->post_author );

    return $feed_item;
}
add_filter( 'json_feed_item', 'wp_custom_json_feed_fields', 10, 2);
```
= Can I adjust the output of the feed =

Yes, certainly. The original output files are being stored inside the subdirectory `templates` of the plugin directory. Just copy them into the directory of your current theme, adjust the copies in there - and off you go! The plugin will automatically detect your custom templates, as long as they are called the same way (ie. `feed-json-comments.php` for the comment feed, and `feed-json.php` for the regular feed), and use them in place of the default ones.


= Can I write information to my posts? =

This is a syndication format, which means it only represents your posts and comments as feed elements. This is read-only, similar to RSS or Atom. It is not an API.

== Changelog ==

= 2.0 =
* Forked from `jsonfeed-wp`
* Partial rewrite as classes / OOP and directory restructuring
* Added custom template option
* Added CORS Headers

= 1.3.0 =
* Add comments template
* JSONFeed icon now part of repo
* Allow for multiple attachments
* Respect summary setting
* Add support for extra feeds in header

= 1.2.0 =
* dshanske added as a contributor/maintainer
* Add featured image if set
* Add site icon if set
* home_page_url now actually returns the correct URL instead of always returning the homepage of the site
* Add avatar and URL to author
* Include site name in feed name in the discovery title
* Fix issue with timezone not reflecting on date

= 1.1.2 =

= 1.1.1 =

= 1.0 =
* Initial release
