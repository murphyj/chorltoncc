=== WP Geo  ===
Contributors: Ben Huson
Donate link: http://www.wpgeo.com/donate
Tags: maps, map, geo, geocoding, google, location, georss
Requires at least: 2.5
Tested up to: 2.8.2
Stable tag: 3.0.9.1

Add location maps to your posts and pages.

== Description ==
When editing a post or page, you will be able to set a physically location for that post. You can select the location by:

1. Clicking on the map to position the point.
2. Searching for a location, town, city or address.
3. Entering the latitude and longitude. 

The WP Geo location selector is styled to fit seemlessly into the latest version of the WordPress admin.

More information can be found at http://www.wpgeo.com/.

= Features =

* NEW - Set width and height in shortcode and category map.
* NEW - Additional Geo Feed control.
* NEW - Load maps from GeoRSS or KML data.
* NEW - Geo Meta Tags
* Markers links to posts
* Settings for default controls
* Custom Markers
* Sidebar Widget
* GeoRSS points in feeds.
* Set default map zoom level.
* Show post maps on category and archive pages.
* Set default width and height for maps
* Shortcode [wp_geo_map] to insert map within your post
* Select your preferred map type
* Select wether to show your map at the top or bottom of posts (or not at all)
* Set a location by clicking on a map or
* Set a location by searching for a location, town, city or address or
* Set a location by entering the latitude and longitude

== Installation ==
1. Download the archive file and uncompress it.
2. Put the "wp_geo" folder in "wp-content/plugins"
3. Enable in WordPress by visiting the "Plugins" menu and activating it.
4. Go to the Settings page in the admin and enter your Google API Key and customise the settings.

(you can sign up for a Google API Key at http://code.google.com/apis/maps/signup.html)

WP Geo will appear on the edit post and edit page screens.
If you set a location, a Google map will automatically appear on your post or page (if your settings are set to).

You can add a map you your category pages to which will display the locations of any posts within that category.
Simply enter <?php $wpgeo->categoryMap(); ?> into your category template where you would like the map to appear.

Please note that from version 2.2 you should access any WPGeo methods using the $wpgeo instance, not using a static class such as <?php WPGeo::categoryMap(); ?>.
Being able to access methods in this way will be phased out in future versions so please change your code now if you need to.

= Upgrading =

If upgrading from a previous version of the plugin:

1. If you are not performing an automatic upgrade, deactivate and reactivate the plugin to ensure any new features are correctly installed.
2. Visit the settings page after installing the plugin to customise any new options.

== Screenshots ==

1. Example of a post with a map.
2. Admin panel shown when editing a post or page.
3. Admin Settings

== Changelog ==

= WP Geo 3.0.9.1 =

* Fix for GUnload() and GBrowserIsCompatible() being called when not available/required.
* Russian language added.

= WP Geo 3.0.9 =

* Added width and height attributes to shortcode.
* Added width and height attributes to category map.
* Danish language updated.

= WP Geo 3.0.8.1 =

* Fixed Google Javascript API loading via proxy issue.
* Tooltip.js filename now all lowercase.
* Added Changelog tab to read me file.

= WP Geo 3.0.8 =

* Additional Geo Feed control.
* Load maps from GeoRSS or KML data.
* Danish language added.
* Languages updated.

= WP Geo 3.0.7.1 =

* Firefox scrolling bug fixed.
* Added longitude and latitude shortcodes.
* Marker on maps in admin now update as you manually change longitude and latitude.
* Added setting to show maps on search result page.

= WP Geo 3.0.7 =

* Added map button in rich text editor.
* Added setting to turn on/off polylines.
* Added setting to set colour of polylines.
* Added setting to override polylines in Widget.
* Using v2.118 of Google Maps to prevent Javascript errors.
* Added WP Geo news feed widget on admin dashboard.
* Admin panels re-implemented using WordPress API.
* Widget map never zooms in more than default zoom setting.

= WP Geo 3.0.6.2 =

* 'Show Maps On' setting now works correctly when widget is active.
* Fixes paths if WordPress is installed in a subdirectory.

= WP Geo 3.0.6.1 =

* Include files removed (fix)

= WP Geo 3.0.6 =

* Marker Tooltip improved (can now but style via css)
* Spanish language added.

= WP Geo 3.0.5 =

* Added way to escape shortcode [wp_geo_map escape="true"]
* CSS Max-width image fixed added.
* Italian language added.
* German language updated.

= WP Geo 3.0.4 =

* Added French language support.

= WP Geo 3.0.3 =

* Added Geo Meta Tags on single post pages.
* Fixed issue when geo data was deleted in quick/bulk edit mode or when scheduled post when live.
* Fixed domain check to work with blogs in a subfolder of a domain.

= WP Geo 3.0.2 =

* Add language support.
* Various bug fixes.

= WP Geo 3.0.1 =

* Markers link to posts.
* Map scale and corner map settings now fixed.

= WP Geo 3.0 =

* Added more default control settings.
* Added custom marker images. 
* Added sidebar Widget.
* Improvements to Javascript loading including addition of external Javascript files.
* Loads jQuery to aid future plugin developments.
* No longer functions as a static class.

= WP Geo 2.1.2 =

* Added capability for feeds including georss points - for more information see http://www.georss.org. 

= WP Geo 2.1.1 =

* Adds external CSS stylesheet to fix image background colours on certain themes.
* Added 'wp_geo_map' class to map divs so they can be styled.

= WP Geo 2.1 =

* Added setting for default map zoom.
* Map in admin now defaults to preferred map type.
* Added screenshots.

= WP Geo 2.0 =

* Added options to display posts maps on category and archive pages.

= WP Geo 1.3 =

* Added options to set default width and height for maps.

= WP Geo 1.2 =

* Added [wp_geo_map] Shortcode to add map within post content.

= WP Geo 1.1 =

* Added option to set map type.
* Added option to set wether maps appear at the top or bottom of posts.

== Languages ==

WP Geo is currently available in the following languages:

* English (default)
* Danish (by <a href="http://wordpress.blogos.dk/s¿g-efter-downloads/?did=91">Georg</a>)
* French
* German
* Italian
* Russian (by <a href="http://www.fatcow.com/">Fat Cower</a>)
* Spanish
