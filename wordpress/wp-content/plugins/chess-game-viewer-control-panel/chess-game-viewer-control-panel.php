<?php
/*
Plugin Name: Chess Game Viewer Control Panel
Plugin URI: http://adrian3.com/projects/wordpress-plugins/chess-game-viewer/
Description: Chess Game Viewer is a premium Wordpress plugin that allows you to easily add interactive chess games to your blog posts and pages. The board is fully customizable with the ability to change the style, size, and color of the board and pieces. 
Version: 1.4
Author: Adrian Hanft
Author URI: http://adrian3.com/projects/wordpress-plugins/
*/

/*  Copyright 2010  Adrian Hanft

* Licensed under the Apache License, Version 2.0 (the "License")
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
*/

require_once('scripts/plugin_options.php');//this connects this file with the file that makes the options work
$pluginName = "Chessboard"; //No spaces allowed

/* Define the variables that are required by this plugin*/


/* Chessboard Piece Style */
$chessboard_piece_style = array('Name'=>'Chess Piece Style','Type'=>'Select','Alfonso X'=>'alfonso-x','Traditional'=>'alpha','Condal'=>'condal','Fantasy'=>'fantasy','Harlequin'=>'harlequin','Kingdom'=>'kingdom','Leipzig'=>'leipzig','Line'=>'line','Lucena'=>'lucena','Maganetic'=>'magnetic','Mark'=>'mark','Marroquin'=>'marroquin','Maya'=>'maya','Medieval'=>'medieval','Merida'=>'merida','Motif'=>'motif','Smart'=>'smart','Usual'=>'usual');
add_new_plugin_option('chessboard_piece_style', $chessboard_piece_style, 'alpha');

/* Chessboard Style */
$chessboard_board_style = array('Name'=>'Chessboard Style','Type'=>'Select','Burl'=>'burl','Bamboo'=>'bamboo','Coffee Bean'=>'coffee_bean','Classic'=>'wenge','Ebony Pine'=>'ebony_pine','Executive'=>'executive','Green Marble'=>'green_marble','Marble'=>'marble','Wood Classic'=>'wood_classic','None'=>'none');
add_new_plugin_option('chessboard_board_style', $chessboard_board_style, 'bamboo');

/* Chessboard Square Size */
$chessboard_squaresize = array('Name'=>'Chessboard Size','Type'=>'Select','Tiny'=>'20','Small'=>'32','Medium'=>'48','Large'=>'76','Extra Large'=>'102');
add_new_plugin_option('chessboard_squaresize', $chessboard_squaresize, '32');

/* Chessboard White Square Color */
/* (UNCOMMENT THE TWO LINES BELOW TO ACTIVATE THIS FEATURE. NOTE THAT this will cause the background images of the board to stop working. You should also delete the word "transperency" around line 1592 of the jschess-tame-viewer.php file found inside the "scripts" folder inside this plugin's folder. When you delete the word "transparency" be sure to leave the quotes so that line looks like this: 
this.opts['whiteSqColor'] = "";

$chessboard_white_color = array('Name'=>'White Square Color','Type'=>'Text');
add_new_plugin_option('chessboard_white_color', $chessboard_white_color, '#e2e2e2');
 */

/* Chessboard Black Square Color */
$chessboard_black_color = array('Name'=>'Black Square Color','Type'=>'Text');
add_new_plugin_option('chessboard_black_color', $chessboard_black_color, 'transparent');

/* Comment Color */
$chessboard_comment_color = array('Name'=>'Text Notation Color','Type'=>'Text');
add_new_plugin_option('chessboard_comment_color', $chessboard_comment_color, '#666666');

/* Show Moves Window By Default */
$chessboard_moveswindow = array('Name'=>'Moves Window Visibility','Type'=>'Select','Visible'=>'true','Hidden'=>'false');
add_new_plugin_option('chessboard_moveswindow', $chessboard_moveswindow, 'false');

/* Chessboard Border Size */
$chessboard_border_size = array('Name'=>'Chessboard Border Size','Type'=>'Select','None'=>'0','1 Pixel'=>'1','2 Pixels'=>'2','3 Pixels'=>'3');
add_new_plugin_option('chessboard_border_size', $chessboard_border_size, '1');

/* Chessboard Border Color */
$chessboard_border_color = array('Name'=>'Chessboard Border Color','Type'=>'Text');
add_new_plugin_option('chessboard_border_color', $chessboard_border_color, '#000000');

/* Show Last Move */
$chessboard_last_move = array('Name'=>'Last Move Highlighting','Type'=>'Select','yes'=>'true','no'=>'false');
add_new_plugin_option('chessboard_last_move', $chessboard_last_move, 'true');

/* Last Move Highlight Color */
$chessboard_highlight_color = array('Name'=>'Last Move Highlight Color','Type'=>'Text');
add_new_plugin_option('chessboard_highlight_color', $chessboard_highlight_color, '#a00000');

/* Button Styles */
$chessboard_button_style = array('Name'=>'Button Style','Type'=>'Select','Minimal'=>'minimal','Red'=>'red','Silver'=>'silver','Classic'=>'classic','Neutral'=>'neutral');
add_new_plugin_option('chessboard_button_style', $chessboard_button_style, 'minimal');

/* The following function is a slightly modified version of Toomas Römer's jspgnviewer Wordpress
Plugin Copyright 2006 which is available from http://code.google.com/p/jspgnviewer/ */

function chess_game_view_callback($str) {
	$siteurl = get_option("siteurl");
	$now = time()+mt_rand();
	// tinyMCE might have added <br /> and other tags
	$str = strip_tags($str[0]);
	// strip entities
	$str = str_replace(array('&#8220;', '&#8221;', '&#8243;'), '"', $str);
	// strip the ###pgn### and %%%pgn%%% placeholders
	$str = str_replace(array('###pgn###', '%%%pgn%%%'), '', $str);
	// replacing "..." with an entity behind the scenes might break something
	$str = str_replace(array('&#8230;'), '...', $str);
	// hidden div with the game information
	$outputchessboard = '<div id="'.$now.'" style="visibility:hidden;display:none">'.$str."</div>\n";
	// the div that will contain the graphical board
	$outputchessboard .= '<style type="text/css"><!--
#chessboard table tbody tr td table tbody tr td table tbody tr td { min-width: '
.getPluginOption('chessboard_squaresize').
'px; } 
td input { background-color: transparent;}
#chessboard table tbody tr td table tbody tr td table tbody tr td {
	background-color: '
.getPluginOption('chessboard_white_color').
';
} 
#chessboard table tbody tr td table tbody tr td table tbody tr td { color: '
.getPluginOption('chessboard_comment_color').
';}
--></style><div id="chessboard"><div id="'.$now.'_board"></div>';
	$outputchessboard .= '<script>var brd = new Board('.$now.',{
\'imagePrefix\':\''
.$siteurl.'/wp-content/plugins/chess-game-viewer-control-panel/images/pieces/'
.getPluginOption('chessboard_piece_style').
'/'
.getPluginOption('chessboard_squaresize').
'/\',
\'buttonPrefix\':\''
.$siteurl.'/wp-content/plugins/chess-game-viewer-control-panel/images/buttons/'
.getPluginOption('chessboard_button_style').
'/\',
\'showMovesPane\':'
.getPluginOption('chessboard_moveswindow').
',
\'commentFontSize\':\'10px\',
\'moveFontColor\':\''
.getPluginOption('chessboard_comment_color').
'\',
\'commentFontColor\':\''
.getPluginOption('chessboard_comment_color').
'\',
\'squareSize\':\''
.getPluginOption('chessboard_squaresize').
'px\',
\'markLastMove\':'
.getPluginOption('chessboard_last_move').
',
\'blackSqColor\':\''
.getPluginOption('chessboard_black_color').
'\',
\'lightSqColor\':\''
.getPluginOption('chessboard_white_color').
'\',
\'move_highlight_color\':\''
.getPluginOption('chessboard_highlight_color').
'\',
\'board_background_image\': \'url('
.$siteurl.'/wp-content/plugins/chess-game-viewer-control-panel/images/boards/'
.getPluginOption('chessboard_squaresize').
'/'
.getPluginOption('chessboard_board_style').
'.jpg)\',
\'squareBorder\':\''
.getPluginOption('chessboard_border_size').
'px solid '
.getPluginOption('chessboard_border_color').
'\',
\'moveBorder\':\'0px solid #cccccc\'

});brd.init()</script>';
	$outputchessboard .= '<noscript>You have JavaScript disabled and you are not seeing a graphical interactive chessboard!</noscript></div>';

	return $outputchessboard;
}


function chessboard_add_script_tags($_) {
	$siteurl = get_option("siteurl");
	echo "<script type=\"text/javascript\" src=\"${siteurl}/wp-content/plugins/chess-game-viewer-control-panel/scripts/jschess-game-viewer.php\"></script>";

}

function chess_game_view($content) {
	if (stristr($content, "<pgn>") === FALSE)
		return preg_replace_callback('/###pgn###((.|\n|\r)*?)%%%pgn%%%/', "chess_game_view_callback", $content);
	else
		return preg_replace_callback('/<pgn>((.|\n|\r)*?)<\/pgn>/', "chess_game_view_callback", $content);
}


add_filter('the_content', 'chess_game_view');
add_action('wp_head', 'chessboard_add_script_tags');




/* The following function is based on The MyQuicktags plugin by Thomas Norberg */

if ( !function_exists('plugins_dir_url') ) :
function plugins_dir_url($file) {

if ( !function_exists('plugins_url') )
	return trailingslashit(get_option('siteurl') . '/wp-content/plugins/' . plugin_basename($file));
	return trailingslashit(plugins_url(plugin_basename(dirname($file)))); }
endif;

function chess_quicktag() {
	wp_enqueue_script(
		'chess_quicktags',
		plugins_dir_url(__FILE__) . 'scripts/chess_quicktags.js',
		array('quicktags') ); }
add_action('admin_print_scripts', 'chess_quicktag');

 ?>