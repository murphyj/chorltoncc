=== Chess Game Viewer ===
Contributors: Adrian Hanft
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=designer%40adrianhanft%2ecom&lc=US&item_name=Wordpress%20Plugin%20Development&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted
Tags: chess, game viewer, chessboard, chess game, pgn, chessboard customizer
Requires at least: 2.6
Tested up to: 2.9.2
Stable tag: trunk

The Chess Game Viewer Control Panel is the easiest way to add a customizable chess board to your blog. 

== Description ==
Chess Game Viewer is a premium Chess Wordpress plugin that allows you to easily add interactive chess games to your blog posts and pages. The board is fully customizable with the ability to change the style, size, and color of the board and pieces. Simply paste your game into the post panel using the "Chess Game" quicktag. If you would like to customize the appearance of your board you can adjust the settings from your Wordpress admin under the "settings/chessboard" tab. This screen gives you the ability to adjust your chessboard's size, style, color, and more. If you like this plugin, please take a look at other plugins by Adrian Hanft at <a href="http://adrian3.com/projects/wordpress-plugins/" title="adrian3.com">adrian3.com</a> including the free <a href="http://adrian3.com/projects/wordpress-plugins/daily-chess-puzzle-widget/" title="chess wordpress plugin">chess puzzle widget.</a>

This plugin is powered by jsPgnViewer which was created by <a href="http://tom.jabber.ee/chessblog/">Toomas Roomer</a> and is available at <a href="http://code.google.com/p/jspgnviewer/downloads/list">code.google.com/p/jspgnviewer/</a>

Changelog:
Version 1.4
- Updated the jsPgnViewer javascript to the latest version (0.6.7). 

Version 1.3
- Fixed error that caused preview not to show up within the Wordpress admin after changes were made.

Version 1.2
- Compatibility with WPMU improved. Made changes to how code is inserted into posts. Instead of <pgn>game</pgn> it now defaults to ###pgn### game %%%pgn%%% because this is a bit more compatible, especially with Wordpress MU. The <pgn> tags will still work, though. 
- Fixed bug that prevented the preview screen to show accurately in the admin panel.

Version 1.0  
- The first version of this plugin allows you to customize the style, size, and colors of the chess boards. 

== Installation ==

Installing the Chess Game Viewer is very easy and shouldn't require any template modification. Just follow these steps:

1. Upload the folder 'chess-game-viewer' to the '/wp-content/plugins/' directory (or install it directly through Wordpress) or install it directly from your Wordpress admin's "plugin" screen.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Under "settings" there will be a "Chessboard" option that will take you to a page where you can customize your board. Follow the instructions on this page and then click "update" to save your changes.
4. To add games to your blog posts or pages you can do one of two things. The easiest way is to click the "HTML" tab above the editor. You will see a "Chess Game" quick tag. Click on this button and paste your game in the box provided. Note that the game must be in PGN format. The other way to add a chess game to your blog post or page is to do it manually. All you have to do is paste your game between pgn tags like this: ###pgn### paste your game here %%%pgn%%%. Save your post or page and you are done. (Alternatively, you can use <pgn> </pgn> tags if that is easier to remember.) 



== Frequently Asked Questions ==

= Who do I contact for help with this plugin? =

If this plugin gives you any trouble, you can go to adrian3.com/contact to report any issues.

= How does this plugin work? =

The Chess Game Viewer is powered by javascript. It uses the jsPgnViewer library that was developed by Toomas Roomer, who was instrumental in the development of this plugin. More information about the jsPgnViewer can be found at http://code.google.com/p/jspgnviewer/


== Screenshots ==
1. This screenshot shows the control panel for the Chess Game Viewer plugin.