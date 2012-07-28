=== Vent ===
Contributors: clearskysnet
Donate link: http://blog.clearskys.net/donations/
Tags: events, scheduled posts, future, widgits
Requires at least: 2.2.0
Tested up to: 2.6.1
Stable tag: 0.8

The Vent plugin allows you to use the WordPress Scheduled posts functionality to enter,list and manage events.

== Description ==

The Vent events system allows you to use the standard WordPress scheduled posts functionality to enter, list and manage future events.

The administrator can setup the criteria that the Vent system uses to identify an Event. This enables you to continue to enter scheduled posts into WordPress (and more importantly, for them to function exactly as before) whilst still using the new Event functionality.

The simplest of criteria is to mark any events with a distinct category or tag, alternatively you can use any combination of the settings shown above, including looking for a string at the start of a post title (such as "Event:").

== Installation ==

The Vent system is compatible with standard WordPress and WordPress MU. The version of WordPress you have will determine where you install the plugins files.

Standard WordPress

   1. Download and unarchive the plugins Zip file. This will create a vent directory on your computer
   2. Upload the Vent directory (and it's contents) to the wp-content/plugins directory of your WordPress install.
   3. Log in to your WordPress administration system, go to the Plugins page and Activate the Vent plugin.

WordPress MU

   1. Download and unarchive the plugins Zip file. This will create a vent directory on your computer.
   2. Open the vent directory so that you can see the files it contains.
   3. Upload the vent.php and ventincludes directory (and it's contents) into the wp-content/mu-plugins directory of your WordPress MU install.
   4. Plugins placed in the mu-plugins directory are automatically activated.

== Enabling the Vent system ==

Once the plugin is activated, it will remain in an disabled state. This is because you have yet to tell the system how to identify an event from a post. Before enabling the system you need to make a  decision about how you would like to mark your future events.

The simplest option is to create a new category or tag called something like "Event" and mark every future event with that category or tag.

Let us, for arguments sake, assume that we have created a tag on our system called "event" and will be using that to identify all of our events.

Got the Settings page in your WordPress administration system and click on the Vent sub-menu.

On this page you will see a series of settings, for now we will deal with the main ones. Go through each of the settings listed below and switch them to your liking.

    * Event identifier - In our example we are using the tag "event", so I will select my tag in the second line and leave the other two lines (1 and 3) alone.
    * Highlight on Home page - The Vent system can automatically display the next (upcoming) event at the top of your blog/sites home page. If you would like the next event displayed on your page then selected "Enabled" here. The second option on this line concerns the styles that are applied to the event. This can help mark the next event out from the rest of your posts. You have the option of using the plugins own styles, or disabling the style generation and using those set up in your themes style sheet.
    * Internal highlight style - If you chose to use the plugins Internal styles then this is were you can change them. The most important part of these style is the post identifier (#post-%postid%). The Vent plugin will replace the %postid% value with the ID number of the next event so that it is only that post that the styles are applied to. If your theme doesn't use the #post-xx naming convention, then you will need to change this setting.
    * Make past events into posts - Setting this to "Enabled" will reset any historical events back into posts. This will ensure that the events remain with your blog/sites post hiearchy and show up in the correct historical position.
    * hCalendar status - The Vent system adds some hCalendar markup to each event. You can use this setting to decide how this information is displayed. hCalendar markup enables other sites and search engines to find and parse the event information on your blog/site.
    * hCalendar export link - This allows you to add a link to a hCalendar parsing website from your Events details. There are a number of these sites (including Technorati) that can parse the hCalendar information held on your page and return an iCal file that you can import into a calendar application (such as Google Calendar, iCal or Outlook).

Once you have set up these options, set the Vent system status option to "Enabled" and then save the options by clicking on the button at the bottom of the page.

Now you can start adding some events into your system.

== Frequently Asked Questions ==

= Can I continue to use the WordPress scheduled posts after I have enabled Vent =

Yes, Vent uses a criteria set up in it's option panel to identify Events from future posts. So as long as you keep to certain rules (defined by yourself), then Events and Scheduled posts will co-exist happily.
