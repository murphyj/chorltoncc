<?php

function wpfilebase_widget_filelist($args)
{
	wpfilebase_inclib('output');
	require_once(WPFB_PLUGIN_ROOT . 'wp-filebase_item.php');
	
	extract($args);
	
	$options = &wpfilebase_get_opt('widget');

	echo $before_widget;
	echo $before_title . $options['filelist_title'] . $after_title;
	
	// load all categories
	WPFilebaseCategory::get_categories();
	$files = WPFilebaseFile::get_files('ORDER BY ' . $options['filelist_order_by'] . ($options['filelist_asc'] ? ' ASC' : ' DESC') . ' LIMIT ' . (int)$options['filelist_limit']);
	
	// add url to template
	/*
	if(strpos($options['filelist_template'], '%file_display_name%') !== false)
		$options['filelist_template'] = str_replace('%file_display_name%', '<a href="%file_url%">%file_display_name%</a>', $options['filelist_template']);
	else
		$options['filelist_template'] = '<a href="%file_url%">' . $options['filelist_template'] . '</a>';
	*/
	
	if(empty($options['filelist_template_parsed']) && !empty($options['filelist_template']))
	{
		wpfilebase_inclib('template');
		$options['filelist_template_parsed'] = wpfilebase_parse_template($options['filelist_template']);
		wpfilebase_update_opt('widget', $options);
	}
	
	echo '<ul>';
	foreach($files as $file){
		if($file->current_user_can_access(true))
			echo '<li>' . $file->generate_template($options['filelist_template_parsed'], true) . '</li>';
	}
	echo '</ul>';
	
	echo $after_widget;     
}


function wpfilebase_widget_filelist_control()
{
	wpfilebase_inclib('admin');
	
	$options = wpfilebase_get_opt('widget');

	if ( !empty($_POST['wpfilebase-filelist-submit']) )
	{
		$options['filelist_title'] = strip_tags(stripslashes($_POST['wpfilebase-filelist-title']));
		$options['filelist_order_by'] = strip_tags(stripslashes($_POST['wpfilebase-filelist-order-by']));
		$options['filelist_asc'] = !empty($_POST['wpfilebase-filelist-asc']);
		$options['filelist_limit'] = max(1, (int)$_POST['wpfilebase-filelist-limit']);
		
		$options['filelist_template'] = stripslashes($_POST['wpfilebase-filelist-template']);
		if(strpos($options['filelist_template'], '<a ') === false)
			$options['filelist_template'] = '<a href="%file_url%">' . $options['filelist_template'] . '</a>';
		wpfilebase_inclib('template');
		$options['filelist_template_parsed'] = wpfilebase_parse_template($options['filelist_template']);
		wpfilebase_update_opt('widget', $options);
	}
	?>
	<div>
		<p><label for="wpfilebase-filelist-title"><?php _e('Title:'); ?>
			<input type="text" id="wpfilebase-filelist-title" name="wpfilebase-filelist-title" value="<?php echo esc_attr($options['filelist_title']); ?>" />
		</label></p>
		
		<p>
			<label for="wpfilebase-filelist-order-by"><?php _e('Sort by:'/*def*/); ?></label>
			<select type="text" id="wpfilebase-filelist-order-by" name="wpfilebase-filelist-order-by">
			<?php
				$order_by_options = array('file_id', 'file_name', 'file_size', 'file_date', 'file_display_name', 'file_hits', 'file_rating_sum', 'file_last_dl_time');
				$field_descs = &wpfilebase_template_fields_desc();
				foreach($order_by_options as $tag)
				{
					echo '<option value="' . esc_attr($tag) . '" title="' . esc_attr($field_descs[$tag]) . '"' . ( ($options['filelist_order_by'] == $tag) ? ' selected="selected"' : '' ) . '>' . $tag . '</option>';
				}
			?>
			</select><br />
			<label for="wpfilebase-filelist-asc0"><input type="radio" name="wpfilebase-filelist-asc" id="wpfilebase-filelist-asc0" value="0"<?php checked($options['filelist_asc'], false) ?>/><?php _e('Descending'); ?></label>
			<label for="wpfilebase-filelist-asc1"><input type="radio" name="wpfilebase-filelist-asc" id="wpfilebase-filelist-asc1" value="1"<?php checked($options['filelist_asc'], true) ?>/><?php _e('Ascending'); ?></label>
		</p>
		
		<p><label for="wpfilebase-filelist-limit"><?php _e('Limit:', WPFB); ?>
			<input type="text" id="wpfilebase-filelist-limit" name="wpfilebase-filelist-limit" size="4" maxlength="3" value="<?php echo $options['filelist_limit']; ?>" />
		</label></p>
		
		<p>
			<label for="wpfilebase-filelist-template"><?php _e('Template:', WPFB); ?><br /><input class="widefat" type="text" id="wpfilebase-filelist-template" name="wpfilebase-filelist-template" value="<?php echo esc_attr($options['filelist_template']); ?>" /></label>
			<br />
			<?php					
				echo wpfilebase_template_fields_select('wpfilebase-filelist-template', true);
			?>
		</p>
		<input type="hidden" name="wpfilebase-filelist-submit" id="wpfilebase-filelist-submit" value="1" />
	</div>
	<?php
}

function wpfilebase_widget_catlist($args)
{
	// if no filebrowser this widget doosnt work
	if(wpfilebase_get_opt('file_browser_post_id') <= 0)
		return;
		
	wpfilebase_inclib('output');
	require_once(WPFB_PLUGIN_ROOT . 'wp-filebase_item.php');
	
	extract($args);
	
	$options = &wpfilebase_get_opt('widget');

	echo $before_widget;
	echo $before_title . $options['catlist_title'] . $after_title;
	
	// load all categories
	$cats = WPFilebaseCategory::get_categories('ORDER BY ' . $options['catlist_order_by'] . ($options['catlist_asc'] ? ' ASC' : ' DESC') . ' LIMIT ' . (int)$options['catlist_limit']);
	
	echo '<ul>';
	foreach($cats as $cat){
		if($cat->current_user_can_access(true))
			echo '<li><a href="'.$cat->get_url().'">'.wp_specialchars($cat->cat_name).'</a></li>';
	}
	echo '</ul>';
	echo $after_widget;
}


function wpfilebase_widget_catlist_control()
{
	if(wpfilebase_get_opt('file_browser_post_id') <= 0) {
		echo '<div>';
		_e('Before you can use this widget, please set a Post ID for the file browser in WP-Filebase settings.', WPFB);
		echo '</div>';
		return;
	}
	
	wpfilebase_inclib('admin');
	
	$options = wpfilebase_get_opt('widget');

	if ( !empty($_POST['wpfilebase-catlist-submit']) )
	{
		$options['catlist_title'] = strip_tags(stripslashes($_POST['wpfilebase-catlist-title']));
		$options['catlist_order_by'] = strip_tags(stripslashes($_POST['wpfilebase-catlist-order-by']));
		$options['catlist_asc'] = !empty($_POST['wpfilebase-catlist-asc']);
		$options['catlist_limit'] = max(1, (int)$_POST['wpfilebase-catlist-limit']);
		wpfilebase_update_opt('widget', $options);
	}
	?>
	<div>
		<p><label for="wpfilebase-catlist-title"><?php _e('Title:'/*def*/); ?>
			<input type="text" id="wpfilebase-catlist-title" name="wpfilebase-catlist-title" value="<?php echo esc_attr($options['catlist_title']); ?>" />
		</label></p>
		
		<p>
			<label for="wpfilebase-catlist-order-by"><?php _e('Sort by:'/*def*/); ?></label>
			<select type="text" id="wpfilebase-catlist-order-by" name="wpfilebase-catlist-order-by">
			<?php
				$order_by_options = array('cat_id', 'cat_name', 'cat_folder', 'cat_files');
				$field_descs = &wpfilebase_template_fields_desc(true);
				foreach($order_by_options as $tag)
				{
					echo '<option value="' . esc_attr($tag) . '" title="' . esc_attr($field_descs[$tag]) . '"' . ( ($options['catlist_order_by'] == $tag) ? ' selected="selected"' : '' ) . '>' . $tag . '</option>';
				}
			?>
			</select><br />
			<label><input type="radio" name="wpfilebase-catlist-asc" value="0" <?php checked($options['catlist_asc'], false) ?>/><?php _e('Descending'); ?></label>
			<label><input type="radio" name="wpfilebase-catlist-asc" value="1" <?php checked($options['catlist_asc'], true) ?>/><?php _e('Ascending'); ?></label>
		</p>
		
		<p><label for="wpfilebase-catlist-limit"><?php _e('Limit:', WPFB); ?>
			<input type="text" id="wpfilebase-catlist-limit" name="wpfilebase-catlist-limit" size="4" maxlength="3" value="<?php echo $options['catlist_limit']; ?>" />
		</label></p>
		<input type="hidden" name="wpfilebase-catlist-submit" id="wpfilebase-catlist-submit" value="1" />
	</div>
	<?php
}
?>