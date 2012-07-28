<?php
wpfilebase_inclib('admin');
wpfilebase_inclib('output');

function wpfilebase_admin_options()
{
	global $wpdb;
		
	if(!current_user_can('manage_options'))
		wp_die(__('Cheatin&#8217; uh?'));
		
	wpfilebase_version_update_check();
	
	$action = ( !empty($_POST['action']) ? $_POST['action'] : ( !empty($_GET['action']) ? $_GET['action'] : '' ) );
	$messages = array();
	$errors = array();
	
	$options = get_option(WPFB_OPT_NAME);
	$option_fields = &wpfilebase_options();
	
	if(isset($_POST['reset']))
	{
		wpfilebase_inclib('setup');
		wpfilebase_reset_options();
		$messages[] = __('Settings reseted.', WPFB);		
		$options = get_option(WPFB_OPT_NAME);
	}
	elseif(isset($_POST['submit']))
	{		
		// cleanup
		foreach($option_fields as $opt_tag => $opt_data)
		{
			if(isset($_POST[$opt_tag]))
			{
				if(get_magic_quotes_gpc() == 1)
					$_POST[$opt_tag] = stripslashes($_POST[$opt_tag]);				
				$_POST[$opt_tag] = trim($_POST[$opt_tag]);
				
				if($opt_data['type'] == 'number')
					$_POST[$opt_tag] = intval($_POST[$opt_tag]);
			}
		}
		
		$_POST['upload_path'] = str_replace(ABSPATH, '', $_POST['upload_path']);
		$options['upload_path'] = str_replace(ABSPATH, '', $options['upload_path']);
		
		$_POST['download_base'] = trim($_POST['download_base'], '/');
		if(wpfilebase_wpcache_reject_uri($_POST['download_base'] . '/', $options['download_base'] . '/'))
			$messages[] = sprintf(__('/%s/ added to rejected URIs list of WP Super Cache.', WPFB), $_POST['download_base']);
		
		$tpl_file = stripslashes($_POST['template_file']);
		$tpl_cat = stripslashes($_POST['template_cat']);
		if(!empty($tpl_file) && (empty($options['template_file_parsed']) || $tpl_file != $options['template_file']))
		{
			wpfilebase_inclib('template');
			$tpl_file = wpfilebase_parse_template($tpl_file);
			$result = wpfilebase_check_template($tpl_file);
			
			if(!$result['error']) {
				$options['template_file_parsed'] = $tpl_file;
				$messages[] = __('File template successfully parsed.', WPFB);
			} else {
				$errors[] = sprintf(__('Could not parse template: error (%s) in line %s.', WPFB), $result['msg'], $result['line']);
			}
		}
		
		if(!empty($tpl_cat) && (empty($options['template_cat_parsed']) || $tpl_cat != $options['template_cat']))
		{
			wpfilebase_inclib('template');
			$tpl_cat = wpfilebase_parse_template($tpl_cat);
			$result = wpfilebase_check_template($tpl_cat);
			
			if(!$result['error']) {
				$options['template_cat_parsed'] = $tpl_cat;
				$messages[] = __('Category template successfully parsed.', WPFB);
			} else {
				$errors[] = sprintf(__('Could not parse template: error (%s) in line %s.', WPFB), $result['msg'], $result['line']);
			}
		}
		
		// save options
		foreach($option_fields as $opt_tag => $opt_data)
		{
			$val = isset($_POST[$opt_tag]) ? $_POST[$opt_tag] : '';
			$options[$opt_tag] = stripslashes(trim($val));
		}
		
		// make sure a short tag exists, if not append one
		$select_opts = array('languages', 'platforms', 'licenses', 'requirements');
		foreach($select_opts as $opt_tag) {
			$lines = explode("\n", $options[$opt_tag]);
			for($i = 0; $i < count($lines); $i++) {
				$lines[$i] = trim($lines[$i], "|\r");
				$pos = strpos($lines[$i], '|');
				if($pos <= 0)
					$lines[$i] .= '|'.str_replace(' ','',strtolower(substr($lines[$i], 0, min(8, strlen($lines[$i])))));
			}
			$options[$opt_tag] = implode("\n", $lines);
		}
		
		update_option(WPFB_OPT_NAME, $options);
		
		wpfilebase_protect_upload_path();
		
		if(count($errors) == 0)
			$messages[] = __('Settings updated.', WPFB);
	}
	
	if(wpfilebase_get_opt('allow_srv_script_upload'))
		$messages[] = __('WARNING: Script upload enabled!', WPFB);
		
	$upload_path = wpfilebase_get_opt('upload_path');
	if(substr($upload_path, 0, 1) == '/')
		$messages[] = __(sprintf('NOTICE: The upload path <code>%s</code> is rooted to the filesystem. You should remove the leading slash if you want to use a folder inside your Wordpress directory (i.e: <code>%s</code>)', $upload_path, trim($upload_path, '/')), WPFB);
	
	wpfilebase_flush_rewrite_rules();
	
	$action_uri = $_SERVER['PHP_SELF'] . '?page=' . $_GET['page'] . '&amp;updated=true';

	if (!empty($messages)) :
	$message = '';
	foreach($messages as $msg)
		$message .= '<p>' . $msg . '</p>';
?>
<div id="message" class="updated fade"><?php echo $message; ?></div>
<?php
	endif;

	if (!empty($errors)) : 
	$error = '';
	foreach($errors as $err)
		$error .= '<p>' . $err . '</p>';
?>
<div id="message" class="error fade"><?php echo $error; ?></div>
<?php endif; ?>

<div class="wrap">
<h2><?php echo WPFB_PLUGIN_NAME; echo ' '; _e("Settings"/*def*/); ?></h2>

<form method="post" action="<?php echo $action_uri; ?>" name="wpfilebase-options">
	<?php wp_nonce_field('update-options'); ?>
	<p class="submit">
	<input type="submit" name="submit" value="<?php _e('Save Changes'/*def*/) ?>" class="button-primary" />
	<input type="submit" id="deletepost" name="reset" value="<?php _e('Reset'/*def*/) ?>" onclick="return confirm('<?php _e('Are you sure you want to do that?'/*def*/); ?>')" class="button delete" />
	</p>
	<?php
	
	$option_categories = array(
		__('Common', WPFB)					=> array('upload_path', 'thumbnail_size', 'file_browser_post_id', 'cat_drop_down'),
		__('Display', WPFB)					=> array('auto_attach_files', 'filelist_sorting', 'filelist_sorting_dir', 'parse_tags_rss', 'decimal_size_format'),
		__('Download', WPFB)				=> array('disable_permalinks', 'download_base', 'force_download', 'ignore_admin_dls', 'accept_empty_referers','allowed_referers'),
		__('Form Presets', WPFB)			=> array('languages', 'platforms', 'licenses', 'requirements'),
		__('Limits', WPFB)					=> array('bitrate_unregistered', 'bitrate_registered', 'traffic_day', 'traffic_month', 'traffic_exceeded_msg', 'file_offline_msg', 'daily_user_limits', 'daily_limit_subscriber', 'daily_limit_contributor', 'daily_limit_author', 'daily_limit_editor', 'daily_limit_exceeded_msg'),
		__('Security', WPFB)				=> array('allow_srv_script_upload', 'hide_inaccessible', 'inaccessible_msg', 'inaccessible_redirect'),
		__('Templates and Scripts', WPFB)	=> array('template_file', 'template_cat', 'dlclick_js')
	);
	?>
	<div id="wpfilebaseopttabs">
		<ul class="wpfilebase-optmenu">
			<?php foreach ( $option_categories as $key => $val ) {
				echo '<li><a href="#'.sanitize_title($key).'">'.wp_specialchars($key).'</a></li>';
			} ?>
		</ul>
	<?php
	$page_option_list = '';	
	$n = 0;
	foreach($option_categories as $opt_cat => $opt_cat_fields) {
		//echo "\n".'<h3>'.$opt_cat.'</h3>';	
		echo "\n\n".'<div id="'. sanitize_title($opt_cat) .'" class="wpfilebase-opttab"><table class="form-table">';
		foreach($opt_cat_fields as $opt_tag)
		{
			
			$field_data = $option_fields[$opt_tag];
			$opt_val = $options[$opt_tag];
			echo "\n".'<tr valign="top">'."\n".'<th scope="row">' . $field_data['title']. '</th>'."\n".'<td>';
			$style_class = '';
			if(!empty($field_data['class']))
				$style_class .= ' class="'.$field_data['class'].'"';
			if(!empty($field_data['style']))
				$style_class .= ' style="'.$field_data['style'].'"';
			switch($field_data['type'])
			{
				case 'text':
				case 'number':
				case 'checkbox':
					echo '<input name="' . $opt_tag . '" type="' . $field_data['type'] . '" id="' . $opt_tag . '"';
					echo ((!empty($field_data['class'])) ? ' class="' . $field_data['class'] . '"' : '');
					if($field_data['type'] == 'checkbox') {
						echo ' value="1" ';
						checked('1', $opt_val);
					} elseif($field_data['type'] == 'number')
						echo ' value="' . intval($opt_val) . '" size="5" style="text-align: right"';
					else {
						echo ' value="' . esc_attr($opt_val) . '"';
						if(isset($field_data['size']))
							echo ' size="' . (int)$field_data['size'] . '"';
					}
					echo $style_class . ' />';
					break;
					
				case 'textarea':
					$code_edit = (strpos($opt_tag, 'template_') !== false || (isset($field_data['class']) && strpos($field_data['class'], 'code') !== false));
					$nowrap = !empty($field_data['nowrap']);
					echo '<textarea name="' . $opt_tag . '" id="' . $opt_tag . '"';
					if($nowrap || $code_edit) {
						echo ' cols="100" wrap="off" style="width: 100%;' . ($code_edit ?  'font-size: 9px;' : '') . '"';
					} else
						echo ' cols="50"';
					echo ' rows="' . ($code_edit ? 20 : 5) . '"';
					echo $style_class;
					echo '>';
					echo wp_specialchars($opt_val);
					echo '</textarea>';
					break;
				case 'select':
					echo '<select name="' . $opt_tag . '" id="' . $opt_tag . '">';
					foreach($field_data['options'] as $opt_v => $opt_n)
						echo '<option value="' . esc_attr($opt_v) . '"' . (($opt_v == $opt_val) ? ' selected="selected" ' : '') . $style_class . '>' . (!is_numeric($opt_v) ? (wp_specialchars($opt_v) . ': ') : '') . wp_specialchars($opt_n) . '</option>';
					echo '</select>';
					break;
			}
			
			if(!empty($field_data['unit']))
				echo ' ' . $field_data['unit'];
				
			if(!empty($field_data['desc']))
				echo "\n".'<br />' . str_replace('%value%', $opt_val, $field_data['desc']);
			echo "\n</td>\n</tr>";		
			$page_option_list .= $opt_tag . ',';
		}
		
		echo '</table></div>'."\n";
	}
	?>
</div> <!--wpfilebase-opttabs-->
	<input type="hidden" name="action" value="update" />
	<input type="hidden" name="page_options" value="<?php echo $page_option_list; ?>" />
	<p class="submit">
	<input type="submit" name="submit" value="<?php _e('Save Changes') ?>" class="button-primary" />
	<input type="submit" id="deletepost" name="reset" class="button delete" value="<?php _e('Reset') ?>" onclick="return confirm('<?php _e('Are you sure you want to do that?'/*def*/); ?>')" />
	</p>
</form>
</div>	<!-- wrap -->	
<?php
}
?>