=== Better Tag Cloud ===
Contributors: nkuttler
Author URI: http://www.nkuttler.de/
Plugin URI: http://www.nkuttler.de/nktagcloud/
Donate link: http://www.nkuttler.de/wordpress/donations/
Tags: tag, tags, tag cloud, tagging, admin, plugin, widget, widgets, shortcode, i18n, internationalized, sidebar, custom taxonomy, custom taxonomies, multi widget, multiple widgets
Requires at least: 2.7
Tested up to: 3.0
Stable tag: 0.99.5

A better tag cloud. Lots of configuration options, a widget and a shortcode to add tag clouds to posts and pages.

== Description ==
I was pretty annoyed with the default tag cloud widget. It does a lot of things wrong, like inconsistent HTML markup and hardcoded font sizes. So I wanted to write my own. Digging through the code I found out that the wordpress `wp_tag_cloud()` function is pretty powerful. In fact, it could do almost everyhing I want. So I abandoned my plan to rewrite everything from scratch and added a nice admin interface to my tag cloud widget.

This plugin makes the options for wp_tag_cloud() available to multiple tag cloud widgets and shortcodes. This means that you can easily choose things like custom taxonomies, font sizes, HTML markup and ordering. See the [wp_tag_cloud() page](http://codex.wordpress.org/Function_Reference/wp_tag_cloud) for what the normal settings can do.

Additionally, it is possible to sort tags by count and alphabetically at the same time. It is also possible to add a counter to the tags, so that your visitors can see how many posts are associated with each tag. The plugin also adds a sensible CSS class to every tag.

= Support =
Visit the [plugin's home page](http://www.nkuttler.de/wordpress/nktagcloud/) to leave comments, ask questions, etc.


= Other plugins I wrote =

 * [Better Lorem Ipsum Generator](http://www.nkuttler.de/wordpress-plugin/wordpress-lorem-ipsum-generator-plugin/)
 * [Better Related Posts](http://www.nkuttler.de/wordpress-plugin/wordpress-related-posts-plugin/)
 * [Custom Avatars For Comments](http://www.nkuttler.de/wordpress-plugin/custom-avatars-for-comments/)
 * [Better Tag Cloud](http://www.nkuttler.de/wordpress-plugin/a-better-tag-cloud-widget/)
 * [Theme Switch](http://www.nkuttler.de/wordpress-plugin/theme-switch-and-preview-plugin/)
 * [MU fast backend switch](http://www.nkuttler.de/wordpress-plugin/wpmu-switch-backend/)
 * [Visitor Movies for WordPress](http://www.nkuttler.de/wordpress-plugin/record-movies-of-visitors/)
 * [Zero Conf Mail](http://www.nkuttler.de/wordpress-plugin/zero-conf-mail/)
 * [Move WordPress Comments](http://www.nkuttler.de/wordpress-plugin/nkmovecomments/)
 * [Delete Pending Comments](http://www.nkuttler.de/wordpress-plugin/delete-pending-comments)
 * [Snow and more](http://www.nkuttler.de/wordpress-plugin/nksnow/)

== Installation ==
1. Unzip
2. Upload to your plugin directory
3. Enable the plugin
4. Add the widget in your dashboard's Design section and configure it as you like.

The easiest way to use this plugin is to drag the new multiwidget into one of your sidebars.


= Add a tag cloud inside a post or page =
There is as well the shortcode [nktagcloud]. Examples:

1. [nktagcloud] This displays the tag cloud as configured on the options page.
1. [nktagcloud single=yes] This does the same but shows only the tags of the current post.
1. [nktagcloud post_id=1234] Displays the tags of the post identified by post_id.
1. [nktagcloud separator="" categories=no] Remove separator and categories.

= Using the tag cloud in your theme =
You can call the function `nktagcloud_shortcode()` from your theme to get the same output as from the shortcode. Example:

`<?php
	if ( function_exists( 'nktagcloud_shortcode' ) ) {
		echo nktagcloud_shortcode( null );
	}
`?>

There's also the `nk_wp_tag_cloud()` function. You can use it to generate a tag cloud with a different configuration than the plugin's one. It accepts the same parameters as [wp_tag_cloud()](http://codex.wordpress.org/Template_Tags/wp_tag_cloud). Example:

`<?php
	if ( function_exists( 'nk_wp_tag_cloud' ) ) {
		echo nk_wp_tag_cloud( 'single=yes&separator= - &categories=no' );
	}
`?>

Additonal parameters (all strings):

* single: Only tags of the current post ('Yes', 'No')
* categories: Inlude categories in the cloud ('Yes', 'No')
* replace: Convert blanks to non-breaking spaces ('Yes', 'No')
* post_id: Display only tags of the specified post (post ID)
* mincount: Show only tags that have been used at least so many times
* separator: Tag separator
* inject_count: Add a counter to the tag ('Yes', 'No')
* inject_count_outside: Add the counter outside of the tag hyperlink ('Yes', 'No')
* nofollow: Add the noffow attribute to the tags in the cloud ('Yes', 'No')


== Screenshots ==
1. The config options.
2. The tag cloud with some CSS. Live demo at the [plugin's page](http://www.nkuttler.de/nktagcloud/) on [my blog](http://www.nkuttler.de/).

== Frequently Asked Questions ==
Q: How do I remove the link to the plugin homepage?<br />
A: Please read the settings page, you can disable it there.

== Changelog ==
= 0.99.5 ( 2010-12-10 ) =
 * Fix a few small bugs, update docs
= 0.99.4 ( 2010-11-12 ) =
 * Bugfix for custom taxonomies, thanks to all who reported this!
= 0.99.3 ( 2010-07-25 ) =
 * Minor bugfix
 * Update italian translation
= 0.99.2 ( 2010-07-14 ) =
 * Update docs
= 0.99 ( 2010-07-13 ) =
 * Support custom taxonomies (only for WordPress >= 3.0)
 * Add multi-widget support
= 0.11.2 ( 2010-05-24 ) =
 * Make it possible to hide the widget header markup when there is no header text.
= 0.11.1 ( 2010-05-18 ) =
 * Make it possible to hide the last separator as suggested by Scott.
= 0.11.0 ( 2010-05-17 ) =
 * Add the nofollow attribute as a configurable option.
 * Add dutch translation.
= 0.10.0.3 ( 2010-05-13 ) =
 * Update the documentation.
= 0.10.0.2 ( 2010-03-09 ) =
 * Rename function to avoid potential conflicts.
= 0.10.0.1 ( 2010-02-01 ) =
 * Bugfix for minimum tag count, thanks to [Muad](http://www.pathofysiologie.nl/) for reporting this.
= 0.10.0 ( 2010-01-19 ) =
 * Modify the shortcode so that it can display a single post's tags. 
 * Use parameters for all function calls to make them more usable for theme developers.
 * Add the possibility to display the tags of a specific post outside the loop. This can be used in the sidebar for example.
= 0.9.0.1 ( 2010-01-15 ) =
 * I &#9825; the wordpress plugin versioning system through plugin.php readme.txt svn tag... I really need to script around that...
= 0.9.0 ( 2010-01-15 ) =
 * Fix for when WordPress is installed into a subdirectory. Thanks [Martin](http://ten-fingers-and-a-brain.com)!
 * Add belorusian translation sponsored by FatCow, thanks! Update various others.
= 0.8.13 =
 * Clean up counter and separator markup, thanks Carolin!
= 0.8.12.2 =
 * Really add the new CSS file, thanks Roman!
 * Update italian translation.
= 0.8.12.1 =
 * Bugfix, thanks [Steve](http://www.criticaldetroit.org/)!
= 0.8.12 =
 * Add colored CSS style.
= 0.8.11 =
 * Add french translation by [Lise](http://liseweb.fr/BLOG/), thank you!
 * Add a first FAQ entry.
= 0.8.10 =
 * Add danish translation by Georg S. Adamsen.
 * Make widget title translatable.
= 0.8.9 =
 * Bugfix for missing category links.
= 0.8.8 =
 * Hopefully fix all invalid HTML markup.
= 0.8.7 =
 * Add the option to put a separator string between tags.
= 0.8.6 =
 * Small fixes, optimizations and updates.
= 0.8.5 =
 * Add minimum count required to show a tag, thanks to [Kaspars](http://krampis.lv/blog) for the suggestion.
 * Add CSS class to every tag in the cloud: class="nktagcloud-$tagsize". $tagsize is something between the configured values for biggest and smallest font size.
 * Add icon by [famfamfam](http://www.famfamfam.com) to the [Admin Drop Down Menu](http://planetozh.com/blog/my-projects/wordpress-admin-menu-drop-down-css/).
 * Move CSS files.
= 0.8.4.1 =
 * Fix version number
= 0.8.4 =
 * Fix bug for new installs where parts of the plugin options get deleted. Before, a plugin reset was necessary after the first install.
= 0.8.3 =
 * Bugfix, put shortcode in the right place on the page
 * Add admin styles + info
 * Update german translation
 * Add italian translation, thanks to [Gianni](http://gidibao.net/index.php/2009/10/15/better-tag-cloud-widget-in-italiano/)!
= 0.8.2 =
 * Bugfix, thanks to cornel.
= 0.8.1.2 =
 * Fix invalid HTML in admin
= 0.8.1 =
 * I18N
 * Add german translation by [Nicolas Kuttler](http://www.nicolaskuttler.de)
 * Sorry for releasing 0.8.0alpha-1, it was intended as a (pretty stable) development version.
 * Add a shortcode. It is possible to insert a tag cloud into any page or post.
= 0.8.0alpha-1 =
 * Major rewrite
 * Made code more efficient
 * Better code documentation
 * Improved admin security
= 0.7.0 =
 * The categories can be included in the tag cloud.
= 0.6.4 =
 * Make it possible to have the tag count outside of the tag hyperlink. Requested by Maya.
= 0.6.3 =
 * Fix URL typo.
 * Fix bad upgrade bug that deletes settings.
= 0.6.2 =
 * Make font sizes text input fields for post decimal positions. This was suggested by Triton Bloom as well.
 * Admin CSS update.
= 0.6.1 =
 * Add include parameter as well.
= 0.6.0 =
 * Make the exclude parameter work.
 * Use new changelog format.
 * Fix some typos.
= 0.5.0 =
 * Rewrote the settings form and added an option to enable/disable tag link underlining.
= 0.4.0 =
 * Add post count feature. Thanks to Triton Bloom for the suggestion.
 * Add sorting by count and name at the same time feature.
 * Moved the configuration to a settings page.
= 0.3.1 =
 * Better description.
= 0.3.0 =
 * Add regexp for putting multi-word tags on one line, thanks to [Augen](http://www.augen-seite.de) for suggesting this.
 * CSS fix, doc and other minor minor updates.
= 0.2.3 =
 * Compatibility tests
= 0.2.2 =
 * Another bugfix.
= 0.2.1 =
 * Bugfix, thanks to [The Local Landing](http://www.thelocallanding.net/).
= 0.2.0 =
 * Add default CSS style for the list format.
= 0.1.1 =
 * Doc updates, fix typo.
= 0.1.0 =
 * Release

== Upgrade Notice ==
= 0.99 =
 * Add support for custom taxonomies and multiple widgets
= 0.10.0.2 =
 * Very low priority compatibility fix.
= 0.10.0 =
 * This might break things for you if you're calling the plugin functions directly from your theme. However, everything is now configurable through the function call which is an improvement. For most users there shouldn't be any noticable change.
= 0.9.0.1 =
 * This still is a maintenance release with one big internal (hence the version bump) but no big visible changes.
= 0.9.0 =
 * This is a maintenance release with one big internal (hence the version bump) but no big visible changes.
