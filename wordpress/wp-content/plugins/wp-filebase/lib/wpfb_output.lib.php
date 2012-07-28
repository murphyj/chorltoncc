<?php

wpfilebase_inclib('common');
	require_once(WPFB_PLUGIN_ROOT . 'wp-filebase_item.php');

function wpfilebase_parse_content_tags(&$content)
{
/*
	static $replace_filters = array(
		'[filebase:attachments]' => 'wpfilebase_get_post_attachments',
		'[filebase:filelist]' => 'wpfilebase_filelist',
	);
	
	static $regexp_filters = array(
		'\[filebase:filelist:cat=?([0-9]+)\]' => "wpfilebase_filelist('\\1')",
		'\[filebase:fileurl:file=?([0-9]+)\]' => "wpfilebase_get_file_url('\\1')",
		'\[filebase:file:file=?([0-9]+)\]' 	=> "wpfilebase_file_generate_template('\\1')",
	);
	
	foreach($replace_filters as $tag => /* & PHP 4 compability *//* $callback)
	{
		if(strpos($content, $tag) !== false)
			$content = str_replace($tag, $callback(), $content);
	}
	
	foreach($regexp_filters as $pattern => $replace)
		$content = preg_replace("/$pattern/e", $replace, $content);
		*/
	// new tag parser, complex but fast & flexible
	$offset = 0;
	while(($tag_start = strpos($content, '[filebase:', $offset)) !== false)
	{
		$tag_end = strpos($content, ']', $tag_start + 10);  // len of '[filebase:'
		if($tag_end === false)
			break; // no more tag ends, break
		$tag_end++;
		$tag_len = $tag_end - $tag_start;
		$tag_content = '';
		$tag = explode(':', substr(substr($content, $tag_start, $tag_len), 10, -1));
		if(!empty($tag[0])) {
			$args = array();
			for($i = 1; $i < count($tag); ++$i) {
				$ta = $tag[$i];
				if($pos = strpos($ta, '='))
					$args[substr($ta, 0, $pos)] = substr($ta, $pos + 1);
				elseif(substr($ta, 0, 4) == 'file' && is_numeric($tmp = substr($ta, 4))) // support for old tags
					$args['file'] = intval($tmp);
				elseif(substr($ta, 0, 3) == 'cat' && is_numeric($tmp = substr($ta, 3)))
					$args['cat'] = intval($tmp);
			}
			switch($tag[0]) {
				case 'filelist':
					$tag_content = wpfilebase_filelist(isset($args['cat']) ? intval($args['cat']) : -1, !empty($args['tpl']) ? $args['tpl'] : null);
					break;

				case 'file':
					if(isset($args['file']) && is_object($file = WPFilebaseFile::get_file($args['file'])) && $file->current_user_can_access(true)) {
						if(empty($args['tpl']))
							$tag_content = $file->generate_template();
						else
							$tag_content = $file->generate_template(wpfilebase_get_parsed_tpl($args['tpl']));
					}
					break;
					
				case 'fileurl':
					if(isset($args['file']) && is_object($file = WPFilebaseFile::get_file($args['file'])))
						$tag_content = $file->get_url();
					break;
					
				case 'attachments':
					wpfilebase_get_post_attachments(&$tag_content, false, !empty($args['tpl']) ? $args['tpl'] : null);
					break;
			}
		}

		$content = (substr($content, 0, $tag_start) . $tag_content . substr($content, $tag_end));
		$offset += strlen($tag_content);
	}
}

function wpfilebase_get_parsed_tpl($tag)
{
	$ptpls = get_option(WPFB_OPT_NAME . '_tpls_parsed');
	if(empty($ptpls[$tag])) {
		$tpls = get_option(WPFB_OPT_NAME . '_tpls');
		if(empty($tpls[$tag]))
			return '';
		wpfilebase_inclib('template');
		$ptpls[$tag] = wpfilebase_parse_template($tpls[$tag]);
		update_option(WPFB_OPT_NAME . '_tpls_parsed', $ptpls);
	}
	return $ptpls[$tag];
}

function wpfilebase_get_file_url($file_id)
{
	$file = WPFilebaseFile::get_file($file_id);
	return is_object($file) ? $file->get_url() : '';
}

function wpfilebase_file_generate_template($file_id)
{
	$file = WPFilebaseFile::get_file($file_id);
	return is_object($file) ? $file->generate_template() : '';
}


function wpfilebase_get_post_attachments(&$content, $check_attached = false, $tpl_tag=null)
{
	static $attached = false;
	global $wpdb, $id;
	
	if(empty($id) || $id <= 0 || ($check_attached && $attached))
		return false;
	
	require_once(WPFB_PLUGIN_ROOT . 'wp-filebase_item.php');
	
	if(!empty($tpl_tag))
		$tpl = wpfilebase_get_parsed_tpl($tpl_tag);
	else
		$tpl = null;
	
	if(count($files = &WPFilebaseFile::get_files("WHERE file_post_id = $id " . wpfilebase_get_filelist_sorting_sql())) > 0) {
		$attached = true;
		foreach($files as $file)
		{
			if($file->current_user_can_access(true))
				$content .= $file->generate_template($tpl);
		}
		$content .= '<div style="clear:both;"></div>';
		return true;
	}
	return false;
}

function wpfilebase_filelist($cat_id=-1, $tpl_tag=null)
{
	global $wpdb;
	
	require_once(WPFB_PLUGIN_ROOT . 'wp-filebase_item.php');
	
	$cat_id = (int)$cat_id;
	$content = '';
	$extra_sql = '';
	if($cat_id > 0)
	{
		$cat = WPFilebaseCategory::get_category($cat_id);
		// check permission
		if($cat && !$cat->current_user_can_access(true))
			return '';
			
		$extra_sql .= 'WHERE file_category = ' . (int)$cat_id . ' ';
	} elseif($cat_id == 0) {
		$extra_sql .= 'WHERE file_category = 0 ';
	} else {
		// load all cats
		WPFilebaseCategory::get_categories();
	}
	
	$extra_sql .= wpfilebase_get_filelist_sorting_sql();
	
	if(!empty($tpl_tag))
		$tpl = wpfilebase_get_parsed_tpl($tpl_tag);
	else
		$tpl = null;
	
	$files = &WPFilebaseFile::get_files($extra_sql);
	foreach($files as /* & PHP 4 compability */ $file)
	{
		if($file->current_user_can_access(true))
			$content .= $file->generate_template($tpl);
	}
	
	return $content . '<div style="clear:both;"></div>';
}

function wpfilebase_file_browser(&$content)
{
	global $wp_query;

	//print_r($wp_query->query_vars);	
	$cat_id = !empty($_GET['wpfb_cat']) ? intval($_GET['wpfb_cat']) : (isset($wp_query->query_vars['wpfb_cat']) ? intval($wp_query->query_vars['wpfb_cat']) : 0);
	$cat_path = !empty($_GET['wpfb_cat_path']) ? $_GET['wpfb_cat_path'] : (isset($wp_query->query_vars['wpfb_cat_path']) ? $wp_query->query_vars['wpfb_cat_path'] : '');
	$parent_cat_id = 0;
	
	if($cat_id <= 0 && !empty($cat_path)) {
		$folders = explode('/', $cat_path);
		$cat_id = 0;
		for($i = 0; $i < count($folders); $i++) {
			$folder = trim($folders[$i], '/');
			if(empty($folder))
				continue;
			if(!is_object($cat = &WPFilebaseCategory::get_category_by_folder($folder, $cat_id))) {
				$cat_id = 0;
				break;
			}
			$parent_cat_id = $cat->cat_parent;
			$cat_id = $cat->cat_id;
		}
	} elseif($cat_id > 0){
		$cat = &WPFilebaseCategory::get_category($cat_id);
		$parent_cat_id = $cat->cat_parent;
	}
	
	$drop_down = (wpfilebase_get_opt('cat_drop_down') == true);
	
	if($drop_down) {
		$content .= wpfilebase_cat_url_table_js();
		$content .= '<form name="wpfb_cats" action="">';
		$content .= '<select name="wpfb_cat" id="wpfb_cat" class="wpfilebase-catselect" onchange="wpfilebase_catselect_changed(this)">'.wpfilebase_cat_selection_tree($cat_id).'</select>';
		$content .= '</form>';
	} else {
		if($cat_id > 0)
		{
			if($parent_cat_id < 0 || !is_object($parent_cat = &WPFilebaseCategory::get_category($parent_cat_id))) {
				$parent_cat = new WPFilebaseCategory();
				$parent_cat->cat_files = count(WPFilebaseFile::get_files("WHERE file_category = 0"));
			}
			$parent_cat->cat_name = __('Go back'/*def*/);
			$content .= $parent_cat->generate_template();
		}	
		$cats = &WPFilebaseCategory::get_categories("WHERE cat_parent = $cat_id ORDER BY cat_name " . (wpfilebase_get_opt('filelist_sorting_dir') ? 'DESC' : 'ASC'));	
		foreach($cats as /* & PHP 4 compability */ $cat)
		{
			if($cat->current_user_can_access(true))
				$content .= $cat->generate_template();
		}
	}
	
	$files = &WPFilebaseFile::get_files("WHERE file_category = $cat_id " . wpfilebase_get_filelist_sorting_sql());
	foreach($files as /* & PHP 4 compability */ $file)
	{
		if($file->current_user_can_access(true))
			$content .= $file->generate_template();
	}
	$content .= '<div style="clear:both;"></div>';
}

function wpfilebase_get_filelist_sorting_sql()
{
	global $wpdb;
	$sortby = wpfilebase_get_opt('filelist_sorting');
	if(empty($sortby))
		$sortby = 'file_display_name';
	$sortby = $wpdb->escape($sortby);
	$sortdir = wpfilebase_get_opt('filelist_sorting_dir') ? 'DESC' : 'ASC';	
	return "ORDER BY `$sortby` $sortdir";
}


function wpfilebase_parse_selected_options($opt_name, $sel_tags, $uris=false)
{
	$outarr = array();
	$opts = explode("\n", wpfilebase_get_opt($opt_name));	
	if(!is_array($sel_tags))
		$sel_tags = explode('|', $sel_tags);
	
	for($i = 0; $i < count($opts); $i++)
	{
		$opt = explode('|', trim($opts[$i]));
		if(in_array($opt[1], $sel_tags)) {
			$o = wp_specialchars(ltrim($opt[0], '*'));;
			if($uris && isset($opt[2]))
				$o = '<a href="' . esc_attr($opt[2]) . '" target="_blank">' . $o . '</a>';
			$outarr[] = $o;
		}
	}	
	return implode(', ', $outarr);
}

function wpfilebase_format_filesize($file_size) {
	global $wpfb_dec_size_format;
	if(!isset($wpfb_dec_size_format))
		$wpfb_dec_size_format = wpfilebase_get_opt('decimal_size_format');
	if($wpfb_dec_size_format) {
		if($file_size <= 1000) {
			$unit = 'B';
		} elseif($file_size < 1000000) {
			$file_size /= 1000;
			$unit = 'KB';
		} elseif($file_size < 1000000000) {
			$file_size /= 1000000;
			$unit = 'MB';
		} else {
			$file_size /= 1000000000;
			$unit = 'GB';
		}
	} else {
		if($file_size <= 1024) {
			$unit = 'B';
		} elseif($file_size < 1048576) {
			$file_size /= 1024;
			$unit = 'KiB';
		} elseif($file_size < 1073741824) {
			$file_size /= 1048576;
			$unit = 'MiB';
		} else {
			$file_size /= 1073741824;
			$unit = 'GiB';
		}
	}
	
	return sprintf('%01.1f %s', $file_size, $unit);
}

function wpfilebase_filename2title($ft, $remove_ext=true)
{
	if($remove_ext) {
		$p = strrpos($ft, '.');
		if($p !== false && $p != 0)
			$ft = substr($ft, 0, $p);
	}
	$ft = str_replace(array('.', '_'), ' ', $ft);
	$ft = ucwords($ft);
	return $ft;
}


function wpfilebase_cat_selection_tree($selected_id = 0, $exclude_id = 0, $cat_id = 0, $deepth = 0)
{
	$out = '';
	if($cat_id <= 0)
	{
		$out .= '<option value="0">' . __('None'/*def*/) . '</option>';
		$cats = &WPFilebaseCategory::get_categories();
		foreach($cats as $c) {
			if($c->cat_parent <= 0 && $c->cat_id != $exclude_id)
				$out .= wpfilebase_cat_selection_tree($selected_id, $exclude_id, $c->cat_id, 0);	
		}
	} else {
		$cat = &WPFilebaseCategory::get_category($cat_id);	
		$out .= '<option value="' . $cat_id . '"' . (($cat_id == $selected_id) ? ' selected="selected"' : '') . '>' . str_repeat('&nbsp;&nbsp; ', $deepth) . esc_attr($cat->cat_name) . '</option>';

		if(isset($cat->cat_childs)) {
			foreach($cat->cat_childs as $child_id) {
				if($child_id != $exclude_id)
					$out .= wpfilebase_cat_selection_tree($selected_id, $exclude_id, $child_id, $deepth + 1);
			}
		}
	}
	return $out;
}


function wpfilebase_cat_url_table_js() {
	$out = '<script type="text/javascript"> var wpfb_cat_urls = [';
	$nocat = new WPFilebaseCategory();
	$out .= "{id:0,url:'".$nocat->get_url()."'},";
	$cats = &WPFilebaseCategory::get_categories();
	foreach($cats as $c) {	
		$out .= "{id:$c->cat_id,url:'".$c->get_url()."'},";
	}
	
	$out .= ']; </script>';
	return $out;
}

?>