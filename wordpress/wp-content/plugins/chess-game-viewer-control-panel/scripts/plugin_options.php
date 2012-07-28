<?php           
  $create_plugin_options = '';

  function getPluginOption($optionName) {
    global $create_plugin_options;              
    
    if ($create_plugin_options == '')
        $create_plugin_options = getPluginOptions(); 
        
    return $create_plugin_options[$optionName];   
  }
  function getPluginOptions() {
    global $pluginName;
    return get_option($pluginName.'_customization_options');
  }                             
                    
  function add_new_plugin_option($optionName, $choices, $defaultValue) {
    global $pluginName;
    $optionValues = get_option($pluginName.'_customization_option_values');         
    if ($optionValues[$optionName] == '' || $optionValues[$optionName] == null) {  
        $optionValues[$optionName] = $choices;
        update_option($pluginName.'_customization_option_values', $optionValues);
        
        $options = get_option($pluginName.'_customization_options');
        $options[$optionName] = $defaultValue;  
        update_option($pluginName.'_customization_options', $options);
    }                                                             
  }
  
  function removePluginOption($optionName) {
    global $pluginName;
    $optionValues = get_option($pluginName.'_customization_option_values');
    unset($optionValues[$optionName]);
    update_option($pluginName.'_customization_option_values', $optionValues);
    
    $options = get_option($pluginName.'_customization_options');
    unset($options[$optionName]);       
    update_option($pluginName.'_customization_options', $options);
  }
  
  function plugins_options_options_panel() {
    global $pluginName;   
    $options = get_option($pluginName.'_customization_options');                                   
    $error = '';
        
    if (isset($_POST['info_update'])) {
        //Strip the slashes
        if (get_magic_quotes_gpc()) {
           function vsf_stripslashes_deep($value)
           {
               $value = is_array($value) ?
                           array_map('vsf_stripslashes_deep', $value) :
                           stripslashes($value);
               return $value;
           }

           $_POST = array_map('vsf_stripslashes_deep', $_POST);
           $_GET = array_map('vsf_stripslashes_deep', $_GET);
           $_COOKIE = array_map('vsf_stripslashes_deep', $_COOKIE);
        }                             
    
        foreach ($_POST as $key => $val) {
            $options[$key] = $val;
        }
            
        update_option($pluginName.'_customization_options', $options);
                                                         
        if ($error != '') 
            echo '<div class="error"><p style="color: red"><strong>' . $error . '</strong></p></div>';
        else
            echo '<div class="updated"><p><strong>Your settings have been saved.</strong></p></div>';
    }                  
    
    $optionValues = get_option($pluginName.'_customization_option_values');                                                        
    
    echo '<div class="wrap">';
            echo '<form method="post" action="">';
            echo '<input type="hidden" name="info_update" value="1" />';

    //General Options
    echo '<h2>Chess Game Viewer Options</h2>';
echo '<a href="http://adrian3.com/projects/wordpress-plugins/" title="adrian3.com"><img src="';
echo get_option("siteurl");
echo '/wp-content/plugins/chess-game-viewer-control-panel/images/Chess_Game_Viewer_Logo.jpg" width="550" height="227" alt="Chess Game Viewer Logo"></a>
<p>Thanks for using the Chess Game Viewer Plugin, the easiest way to add a customized chess game to your Wordpress blog. Use the options below to customize the appearance of your chessboard. If you like this plugin, please take a look at other plugins by Adrian Hanft at <a href="http://adrian3.com/projects/wordpress-plugins/" title="adrian3.com">adrian3.com</a>.</p>
';
    echo '<table width="500" cellspacing="15">';
    foreach ($optionValues as $key => $val) {            
        echo '<tr><td width="250">';
        echo $val['Name'] . ':</td><td valign="top">';
        switch (strtolower($val['Type'])) {
             Case 'text':
             Case 'textbox':
                echo '<input type="text" name="'. $key .'" value="' . $options[$key] . '"  style="'. $val['Style'] .'"/>';
                break;
             Case 'combo':
             Case 'combobox':
             Case 'select':              
                echo '<select name="'. $key .'" style="'. $val['Style'] .'">';
                foreach ($val as $subkey => $value) {  
                    if (strtolower($subkey) != 'name' && strtolower($subkey) != 'type' &&  strtolower($subkey) != 'style') {
                        echo '<option value="'.$value.'"';
                        if ($options[$key] == $value)
                            echo ' SELECTED';
                        echo '>'.$subkey.'</option>';
                        }
                }
                echo '</select>';
                break;       
             Case 'check':      
             Case 'checkbox':   
                echo '<input type="checkbox" name="'. $key .'" style="'. $val['Style'] .'" ';
                if ($options[$key] == 1)
                    echo "CHECKED ";
                echo '/>';
                break;
             Case 'textarea': 
                echo '<textarea name="'. $key .'" style="'. $val['Style'] .'">' . $options[$key] . '</textarea>';
                break;
        }
        echo '</td></tr>'; 
    }    
    echo '<tr><td><div class="submit" style="text-align: left"><input type="submit" value="Save Settings &raquo;" /></div></td></tr></table></form>
</div>';
  
echo '<h3>Update your settings and your changes<br /> will be seen in the board below.</h3>	<script type="text/javascript" src="';
echo get_option("siteurl");
echo '/wp-content/plugins/chess-game-viewer-control-panel/scripts/jschess-game-viewer.php">
</script><div id="1903260076" style="visibility:hidden;display:none">[Event "Sample Game"]
[Site "Yahoo! Chess"]
[Date "2010.02.20"]
[Round ""]
[White "Adrian Hanft"]
[Black "Anonymous"]
[Result "1-0"]
1. f4 e6 2. Nf3 { This odd looking opening gets referred to as the Polar Bear.
} Bc5 3. e3 h6 4. g4 g5 5. h4 gxf4 6. exf4 Qf6 7. d3 Ne7 8. Bd2 { This is a
trap exposing the b pawn to the queen. It looks like a bad move doesn&#8217;t it? }
Qxb2 9. Bc3 { Did white just win a rook? } Qb6 { Now white must resist taking
the rook because Bf2 could get nasty in a hurry. } 10. d4 Bb4 11. Bxb4 Qxb4+
12. Nbd2 Nd5 13. Rb1 Qa3 14. Bc4 Nc3 { This looks bad for white, but luckly
there is a queen swap option. } 15. Rb3 Nxd1 { Black accepts the trade. } 16.
Rxa3 Nb2 17. Be2 Nc6 18. O-O d5 19. Rb1 { Black\'s knight is in trouble and white
wins a pawn and a strong position. } Nc4 20. Nxc4 dxc4 21. Bxc4 a6 22. d5 exd5
23. Bxd5 Ne7 24. Bc4 b5 { This pawn is free for the taking thanks to a pin of
the knight. } 25. Bxb5+ c6 26. Bc4 Bxg4 27. Ne5 Bf5 28. Bxf7+ Kf8 29. Bh5 Bxc2
30. Rc1 Be4 31. Kf2 Rg8 32. Rg1 { Nd7+ probably would have been a better move,
but white plays Rg1 } Rxg1 33. Kxg1 Nf5 34. Kf2 Nxh4 35. Re3 Bd5 36. Nd7+ { Now
black is in trouble } Kg7 37. Re7+ Kh8 38. Nf6 Rf8 { Bg8 would have prolonged
things a little longer. } 39. Rh7# 1-0</div>
<style type="text/css"><!--
#chessboard table tbody tr td table tbody tr td table tbody tr td { min-width: ';
echo getPluginOption('chessboard_squaresize');
echo 'px; } 
#chessboard table tbody tr td table tbody tr td table tbody tr td {
	background-color: ';
echo getPluginOption('chessboard_white_color');
echo ';
} 

#chessboard table tbody tr td table tbody tr td table tbody tr td { color: #';
echo getPluginOption('chessboard_comment_color');
echo ';}
--></style><div id="chessboard"><div id="1903260076_board"></div><script>var brd = new Board(1903260076,{
\'imagePrefix\':\'';
echo get_option("siteurl");
echo '/wp-content/plugins/chess-game-viewer-control-panel/images/pieces/';
echo getPluginOption('chessboard_piece_style');
echo '/';
echo getPluginOption('chessboard_squaresize');
echo '/\',
\'buttonPrefix\':\'';
echo get_option("siteurl");
echo '/wp-content/plugins/chess-game-viewer-control-panel/images/buttons/';
echo getPluginOption('chessboard_button_style');
echo '/\',
\'showMovesPane\':';
echo getPluginOption('chessboard_moveswindow');
echo ',
\'commentFontSize\':\'10px\',
\'moveFontColor\':\'';
echo getPluginOption('chessboard_comment_color');
echo '\',
\'commentFontColor\':\'';
echo getPluginOption('chessboard_comment_color');
echo '\',
\'squareSize\':\'';
echo getPluginOption('chessboard_squaresize');
echo 'px\',
\'markLastMove\':';
echo getPluginOption('chessboard_last_move');
echo ',
\'blackSqColor\':\'';
echo getPluginOption('chessboard_black_color');
echo '\',
\'lightSqColor\':\'';
echo getPluginOption('chessboard_white_color');
echo '\',
\'move_highlight_color\':\'';
echo getPluginOption('chessboard_highlight_color');
echo '\',
\'board_background_image\': \'url(';
echo get_option("siteurl");
echo '/wp-content/plugins/chess-game-viewer-control-panel/images/boards/'
.getPluginOption('chessboard_squaresize').
'/'
.getPluginOption('chessboard_board_style').
'.jpg)\',
\'squareBorder\':\'';
echo getPluginOption('chessboard_border_size');
echo 'px solid ';
echo getPluginOption('chessboard_border_color');
echo '\',
\'moveBorder\':\'0px solid #cccccc\'

});brd.init()</script><noscript>You have JavaScript disabled and you are not seeing a graphical interactive chessboard!</noscript></div>
<h2>Help</h2>

<h4><em>How do the colors Work?</em></h4>
<p>The colors you enter must be valid HTML colors. This means that they can be a standard color like "pink" or "white" or "gray" or they can be hexadecimal colors like "#ffffff" or "#000000." Don\'t fortet to include the "#" in front of the hexidecimal colors. Do a quick internet search if you are unsure how to use web colors. Important: if you don\'t want to specify a color, I recommend you enter "transparent" in the box rather than leaving it blank.</p>

<h4><em>Why isn\'t there a setting to change the white squares?</em></h4>
<p>If you would rather use solid colors for your board instead of the graphic boards, you just need to make two simple edits to a coupld files in this plugin\'s folder. First, uncomment lines 48-49 of the chess-game-viewer.php file. Second, delete the word, "transparency" in "jschess-tame-viewer.php" which can be found inside the "scripts" folder</p>

<h4><em>Who do I contact for questions/help?</em></h4>
<p>If you need help please use the contact form at adrian3.com/contact</p>

<h4><em>Why do the pieces have white boxes around them in IE6?</em></h4>
<p>Because Internet Explorer 6 is an outdated browser and has trouble showing images in png format. (Png images are not to be confused with PGN format - png is an image format, pgn is the chess game notation format.) There are workarounds for getting IE6 to display pngs, so if you absolutely must support IE6, consider a Wordpress plugin like HITS-IE6 Png Fix which is available at <a href="http://wordpress.org/extend/plugins/hits-ie6-pngfix/" title="http://wordpress.org/extend/plugins/hits-ie6-pngfix/">http://wordpress.org/extend/plugins/hits-ie6-pngfix/</a>. But really, wouldn\'t it be better to just ditch IE6?</p>

<h4><em>About this plugin</em></h4>
<p>The Chess Game Viewer is powered by javascript. It uses the jsPgnViewer library that was developed by Toomas Roomer, who was instrumental in the development of this plugin. More information about the jsPgnViewer can be found at <a href="http://code.google.com/p/jspgnviewer/" title="http://code.google.com/p/jspgnviewer/">http://code.google.com/p/jspgnviewer/</a> </p>

';

  }      

    //=============================================
    // Adds the subpanel to the 
    // admin options panel
    //=============================================
    function plugins_options_add_options_subpanel() {
        if (function_exists('add_options_page')) {     
            global $pluginName;                                                                 
            add_options_page($pluginName . ' Options', $pluginName . '', 10, basename(__FILE__), 'plugins_options_options_panel');
        }
    }
    
    add_action('admin_menu', 'plugins_options_add_options_subpanel');
?>
