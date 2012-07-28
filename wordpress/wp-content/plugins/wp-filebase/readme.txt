=== WP-Filebase ===
Contributors: fabifott
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=money%40fabi-s%2ede&item_name=WP-Filebase&no_shipping=0&no_note=1&tax=0&currency_code=USD&lc=US&bn=PP%2dDonationsBF&charset=UTF%2d8
Tags: filebase, filemanager, file, files, manager, upload, download, downloads, downloadmanager, traffic, widget, filelist, list, thumb, thumbnail, attachment, attachments, category, categories, media, template, ftp, http
Requires at least: 2.2.0
Tested up to: 2.9.1
Stable tag: 0.1.3.4

Adds a powerful download manager supporting file categories, thumbnails, traffic/bandwidth limits and more to your WordPress blog.

== Description ==

WP-Filebase is a powerful download manager supporting file categories, thumbnails and more.
Uploaded files can be associated with a post or page so the download URL, thumbnail and other file information are appended automatically to the content.
Additionally the downloadmanager offers options to limit traffic and download speed.

Some more features:

*   Powerful filemanger to arrange files in categories and sub-categories
*   Insert file lists in posts and pages (with Editor Button)
*   Flexible content tags
*   Automatically creates thumbnails of images (JPEG, PNG, GIF, BMP)
*   Category Icons
*   Upload files with your browser or FTP client
*   Powerful template engine (variables, IF-Blocks)
*   Associate files to posts and automatically attach them to the content
*   Customisable file list widget
*   Multiple custom templates for filelists
*   Access control for categories and files (e.g. to make files accessible for members only)
*   Hotlinking protection
*   Daily and monthly traffic limits
*   Download speed limiter for registered users and anonymous
*   Traffic and download limits
*   Range download (allows users to pause downloads and continue them later)
*   Works with permalink structure (nice download URIs)
*   Download counter which ignores multiple downloads from the same client
*   Many file properties like author, version, supported languages, platforms and license
*   Custom JavaScript code which is executed when a download link is clicked (e.g. to track downloads with Google Analytics)
*   Works with WP Super Cache

You can see a [live demo on my Website](http://fabi.me/downloads/ "WP-Filebase demo")

**Since Version 0.1.3.0 the plugin supports localization.** If you want to translate WP-Filebase in your language, open `wp-filebase/languages/template.po` with [Poedit](http://www.poedit.net/download.php) and save as `wpfb-xx_YY.po` (`xx` is your language code, `YY` your country). Poedit will create the file `wpfb-xx_YY.po`. Put this file in `wp-filebase/languages` and share it if you like (attach it to an email or post it on my blog).

If you want to report a bug or have any problems with this Plugin please post your WordPress and PHP Version!

**Note:** If you only want to limit traffic or bandwidth of media files you should take a look at my [Traffic Limiter](http://wordpress.org/extend/plugins/traffic-limiter/ "Traffic Limiter").

== Installation ==

1. Upload the `wp-filebase` folder with all it's files to `wp-content/plugins/`
2. Create the directory `/wp-content/uploads/filebase` and make it writable (FTP command: `CHMOD 777 wp-content/uploads/filebase`)
3. Activate the Plugin and customize the settings under *Settings->WP-Filebase*

== Frequently Asked Questions ==

= How do I insert a file list into a post?  =

In the post editor click on the *WP-Filebase* button. In the appearing box click on *File list*, then select a category. Optionally you can select a custom template.

= How do I list a categories, sub categories and files?  =

To list all categories and files on your blog, create an empty page (e.g named *Downloads*). Then goto *WP-Filebase Settings* and select it in the post browser for the option *Post ID of the file browser*.
Now a file browser should be appended to the content of the page.

= How do I add files with FTP? =

Upload all files you want to add to the WP-Filebase upload directory (default is `wp-content/uploads/filebase`) with your FTP client. Then goto WP-Admin -> Tools -> WP-Filebase and click *Sync Filebase*. All your uploaded files are added to the database now. Categories are created automatically if files are in sub folders.

= How do I customize the appearance of filelists and attached files? =

You can change the HTML template under WP-Admin -> Settings -> WP-Filebase. To edit the stylesheet goto WP-Admin -> Tools -> WP-Filebase and click *Edit Stylesheet*.
Since Version 0.1.2.0 you can create your custom templates for individual file lists. You can manage the templates under WP-Admin -> Tools -> WP-Filebase -> Manage templates. When adding a tag to a post/page you can now select the template.

= How can I use custom file type/extension icons? =

WP-Filebase uses WordPress' default file type icons in `wp-includes/images/crystal` for files without a thumbnail. To use custom icons copy the icon files in PNG format named like `pdf.png` or `audio.png` to `wp-content/images/fileicons` (you have to create that folder first).
== Screenshots ==

1. Example of three auto-attached files
2. The WP-Filebase Widget
3. The Editor Button to insert tags for filelists and download urls

== Changelog ==

= 0.1.3.4 =
* Fixed blank tools page caused by empty Wordpress upload path
* Added notice if WP-Filebase upload path is rooted

= 0.1.3.3 =
* Brazillian Portuguese translation by [Jan Seidl](http://www.heavyworks.net/)
* French translation by [pidou](http://www.portableapps-blog.fr/)

= 0.1.3.2 =
* Added daily user download limits
* JavaScript errors caused by jQuery tabs function are suppressed if not supported by the browser
* Added support for custom file type icons. Copy your icons to `wp-content/images/fileicons` (see FAQ).

= 0.1.3.0 =
* Added option *Parse template tags in RSS feeds*
* New Widget: Category list
* Settings are organized in tabs now
* Conditional loading of WP-Filebase's JS
* Automatic login redirect
* Validated template output (**Note**: line breaks are not converted to HTML anymore, so please add &lt;br /&gt;'s or reset your settings to load the default template)
* Added localization support
* German translation
* Editor Button code changes
* Changed default file permissions from 777 to 666
* Fixed file date bug causing a reset of the date

= 0.1.2.4 =
* New option *Category drop down list* for the file browser
* Fixed sync bug

= 0.1.2.3 =
* Added support for custom Category Icons
* Fixed `file_url` in the download JavaScript for proper tracking
* Fixed a thumbnail upload bug

= 0.1.2.2 =
* Files and categories in the file browser are sorted now
* Category directories are now renamed when the folder name is changed
* Fixed file browser query arg
* Fixed Permalink bug

= 0.1.2.1 =
* New feature: category template for category listing
* New feature: added file browser which lists categories and files
* Added option to disable download permalinks
* New option *Decimal file size prefixes*
* Fixed a problem with download permalinks
* Fixed an issue with auto attaching files
* Fixed a SQL table index issue causing trouble with syncing
* Fixed a sync bug causing categories to be moved into others

= 0.1.2.0 =
* Added multiple templates support (you can now create custom templates for file lists)
* Added option *Hide inaccessible files* and *Inaccessible file message*
* When resetting WP-Filebase settings the traffic stats are retained now
* Fixed *Manage Categories* button
* Enhanced content tag parser
* Added support for HTTP `ETag` header (for caching)
* Improved template generation

= 0.1.1.5 =
* Added CSS Editor
* Added max upload size display
* Fixed settings error `Missing argument 1 for WPFilebaseItem::WPFilebaseItem()`
* Fixed widget control
* Fixed an issue with browser caching and hotlink protection

= 0.1.1.4 =
* Download charset HTTP header fixed
* Editor file list fixed
* New file list option `Uncategorized Files`

= 0.1.1.3 =
* Added FTP upload support (use `Sync Filebase` to add uploaded files)
* Code optimizations for less server load
* File requirements can include URLs now
* Fixed options checkbox bug
* Fixed an issue with the editor button
* Fixed form URL query issue
* Some fixes for Windows platform support

= 0.1.1.2 =
* Fixes - for PHP 4 only

= 0.1.1.1 =
* Now fully PHP 4 compatible (it is strongly recommended to update to PHP 5)
* Fixed a HTTP header bug causing trouble with PDF files and Adobe Acrobat Reader
* New option *Always force download*: if enabled files that can be viewed in the browser (images, videos...) can only be downloaded (no streaming)
* Attachement lists are sorted now
* The MD5 hash line in the file template is now commented out by default
* Fixed `Fatal error: Cannot redeclare wpfilebase_inclib()`

= 0.1.1.0 =
* Added simple upload form with less options which is shown by default
* Fixed editor button
* Changed editor tag box
* Selection fields in the file upload form are removed if there are no entries
* You can now enter custom JavaScript Code which is executed when a download link is clicked (e.g. to track downloads with Google Analytics)
* If no display name is entered it will be generated from the filename
* Removed the keyword `private` in class property declarations to make the plugin compatible with PHP 4
* Serveral small bug fixes
* CSS fixes
* Optimized code to decrease memory usage

= 0.1.0.3 =
* Added file list sorting options
* Rearranged options
* Fixed `Direct linking` label of upload form
* Added HTML link titles of the default template (to enable this change you must reset your options to defaults)

= 0.1.0.2 =
* Fixed a HTTP cache header
* Added support for HTTP If-Modified-Since header (better caching, lower traffic)

= 0.1.0.1 =
* Added download permissions, each file can have a minimum user level
* New Editor Tag `[filebase:attachments]` which lists all files associated with the current article
* Fixed missing `file_requirements` template field. You should reset your WP-Filebase settings if you want to use this.

= 0.1.0.0 =
* First version