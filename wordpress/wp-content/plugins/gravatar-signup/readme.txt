=== Gravatar Signup ===
Contributors: markjaquith
Donate link: http://txfx.net/wordpress-plugins/donate/
Tags: comments, gravatar, avatar
Requires at least: 2.8
Tested up to: 2.8.4
Stable tag: trunk

Inserts a Gravatar signup checkbox into the comment form, if the current commentator lacks one.

== Description ==

This plugin inserts a checkbox into the comment form for users who don't have a Gravatar (based on the e-mail they typed in). If they check the box and submit their comment, it will initiate the first step of signing up for Gravatar, on their behalf. They'll receive an e-mail directly from Gravatar and will have to follow the instructions there to complete the process.

== Installation ==

1. Upload the `gravatar-signup` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Verify that your comment form has the `comment_form` hook installed by inspecting `comments.php` in your theme's directory

== Frequently Asked Questions ==

= What if my site requires registration for comments? =

It should still work, pulling the e-mail address from that user's profile.

== Changelog ==

= 2.0.1 =
* Oops! Forgot to commit the jquery md5 plugin with 2.0

= 2.0 =
* Support the new Gravatar signup form
* Check via JS whether the currently typed email has a Gravatar
* Massive code rewrite