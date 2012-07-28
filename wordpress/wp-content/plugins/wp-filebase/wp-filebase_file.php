<?php

require_once(WPFB_PLUGIN_ROOT . 'wp-filebase_item.php');

global $wpfb_file_cache;
$wpfb_file_cache = array(); // (PHP 4 compatibility)
global $wpfb_file_tpl_uid;
$wpfb_file_tpl_uid = 0;

class WPFilebaseFile extends WPFilebaseItem {

	var $file_id;
	var $file_name;
	var $file_size;
	var $file_date;
	var $file_hash;
	var $file_thumbnail;
	var $file_display_name;
	var $file_description;
	var $file_version;
	var $file_author;
	var $file_language;
	var $file_platform;
	var $file_requirement;
	var $file_license;
	var $file_required_level;
	var $file_offline;
	var $file_direct_linking;
	var $file_category;
	var $file_update_of; // TODO
	var $file_post_id;
	var $file_added_by;
	var $file_hits;
	var $file_ratings; // TODO
	var $file_rating_sum; // TODO
	var $file_last_dl_ip;
	var $file_last_dl_time;
	
	/* static private $_files = array(); (PHP 4 compatibility) */
	
		
	/*public static (PHP 4 compatibility) */ function get_files($extra_sql = '')
	{
		global $wpdb, $wpfb_file_cache;
		
		if(!is_array($wpfb_file_cache))
			$wpfb_file_cache = array();
		
		$files = array();
		
		$results = $wpdb->get_results('SELECT * FROM ' . $wpdb->wpfilebase_files . ' ' . $extra_sql);

		if(!empty($results) && count($results) > 0)
		{
			foreach($results as $file_row)
			{
				$file = new WPFilebaseFile($file_row);
				$id = (int)$file->file_id;
				
				$files[$id] = $file;
				$wpfb_file_cache[$id] = $file;
			}
		}
		
		return $files;
	}
	
	/*public static (PHP 4 compatibility) */ function get_file($id)
	{
		global $wpfb_file_cache;
		
		$id = (int)intval($id);
		
		if(isset($wpfb_file_cache[$id]))
			return $wpfb_file_cache[$id];
			
		$files = &WPFilebaseFile::get_files("WHERE file_id = $id");
		
		return $files[$id];
	}
	
	/*public static (PHP 4 compatibility) */ function get_file_by_path($path)
	{
		global $wpdb;
		
		$names = explode('/', $path);
		$n = count($names);
		if($n == 1) {
			$cat_folder = null;
			$file_name = $names[0];
		} else {
			$cat_folder = $names[$n-2];
			$file_name = $names[$n-1];
		}
		
		$cat_folder = trim($cat_folder, '/');
		$file_name = trim($file_name, '/');
		
		if(empty($file_name))
			return;
		
		$cat_id = 0;		
		// get parent cat id
		if(!empty($cat_folder))
		{
			if(!is_object($cat = &WPFilebaseCategory::get_category_by_folder($cat_folder)))
				return null;
			$cat_id = (int)$cat->cat_id;
		}
		
		$files = &WPFilebaseFile::get_files("WHERE file_name = '" . $wpdb->escape($file_name) . "' AND file_category = " . (int)$cat_id);
		
		if(empty($files))
			return null;
		else
			return reset($files);
	}
	
	/*public static (PHP 4 compatibility) */ function get_num_files()
	{
		global $wpdb;
		return $wpdb->get_var("SELECT COUNT(file_id) FROM $wpdb->wpfilebase_files WHERE 1"); 
	}

	
	// gets the extension of the file (including .)
	/*public (PHP 4 compatibility) */ function get_extension()
	{
		return strtolower(strrchr($this->file_name, '.'));
	}
	
	/*public (PHP 4 compatibility) */ function get_type()
	{
		$ext = substr($this->get_extension(), 1);
		if( ($type = wp_ext2type($ext)) )
			return $type;
		
		return $ext;
	}	
	
	/*public (PHP 4 compatibility) */ function get_thumbnail_path()
	{
		if(empty($this->file_thumbnail))
			return null;
			
		return  dirname($this->get_path()) . '/' . $this->file_thumbnail;
	}
	
	/*public (PHP 4 compatibility) */ function get_icon_url()
	{	
		if(!empty($this->file_thumbnail) && file_exists($this->get_thumbnail_path()))
		{
			return WPFB_PLUGIN_URI . 'wp-filebase_thumb.php?fid=' . $this->file_id;
		}
				
		$type = $this->get_type();
		$ext = substr($this->get_extension(), 1);
		
		$img_path = ABSPATH . WPINC . '/images/';
		$img_url = get_option('siteurl').'/'. WPINC .'/images/';
		$custom_folder = '/images/fileicons/';
		
		// check for custom icons
		if(file_exists(WP_CONTENT_DIR.$custom_folder.$ext.'.png'))
			return WP_CONTENT_URL.$custom_folder.$ext.'.png';		
		if(file_exists(WP_CONTENT_DIR.$custom_folder.$type.'.png'))
			return WP_CONTENT_URL.$custom_folder.$type.'.png';
		

		if(file_exists($img_path . 'crystal/' . $ext . '.png'))
			return $img_url . 'crystal/' . $ext . '.png';
		if(file_exists($img_path . 'crystal/' . $type . '.png'))
			return $img_url . 'crystal/' . $type . '.png';	
				
		if(file_exists($img_path . $ext . '.png'))
			return $img_url . $ext . '.png';
		if(file_exists($img_path . $type . '.png'))
			return $img_url . $type . '.png';
		
		// fallback to default
		if(file_exists($img_path . 'crystal/default.png'))
			return $img_url . 'crystal/default.png';		
		if(file_exists($img_path . 'default.png'))
			return $img_url . 'default.png';
		
		// fallback to blank :(
		return $img_url . 'blank.gif';
	}
	
	/*public (PHP 4 compatibility) */ function create_thumbnail($src_image='')
	{
		$src_set = !empty($src_image) && file_exists($src_image);
		if(!$src_set)
			$src_image = $this->get_path();
		
		if(!file_exists($src_image) || @filesize($src_image) < 3)
			return;
		
		$ext = trim($this->get_extension(), '.');
		
		if($ext != 'bmp' && !file_is_valid_image($src_image))
			return;
			
		$this->delete_thumbnail();
		
		$thumb = null;
		$thumb_size = (int)wpfilebase_get_opt('thumbnail_size');
		
		if(!function_exists('wp_create_thumbnail'))
			wp_die('Function wp_create_thumbnail does not exist!');
			
		
		if($ext != 'bmp')
		{
			$thumb = @wp_create_thumbnail($src_image, $thumb_size);
		} else {
			$extras_dir = WPFB_PLUGIN_ROOT . 'extras/';
			if(@file_exists($extras_dir . 'phpthumb.functions.php') && @file_exists($extras_dir . 'phpthumb.bmp.php'))
			{
				@include($extras_dir . 'phpthumb.functions.php');
				@include($extras_dir . 'phpthumb.bmp.php');
				
				if(class_exists('phpthumb_functions') && class_exists('phpthumb_bmp'))
				{
					$phpthumb_bmp = new phpthumb_bmp();
					
					$im = $phpthumb_bmp->phpthumb_bmpfile2gd($src_image);
					if($im) {
						$jpg_file = $src_image . '__.tmp.jpg';
						@imagejpeg($im, $jpg_file, 100);
						if(@file_exists($jpg_file) && @filesize($jpg_file) > 0)
						{
							$thumb = @wp_create_thumbnail($jpg_file, $thumb_size);
						}
						@unlink($jpg_file);
					}						
				}
			}				
		}
		
		
		
		if(!$src_set && (empty($thumb) || !is_string($thumb) || !file_exists($thumb))) {
			$this->file_thumbnail = null;
		} else {
			// fallback to source image
			if($src_set && (empty($thumb)  || !file_exists($thumb)))
				$thumb = $src_image;
			
			$this->file_thumbnail = basename($thumb);
			
			if(!@rename($thumb, $this->get_thumbnail_path()))
				$this->file_thumbnail = null;
			else
				@chmod($this->get_thumbnail_path(), octdec(WPFB_PERM_FILE));
		}
	}

	/*public (PHP 4 compatibility) */ function get_post_url()
	{
		if(empty($this->file_post_id))
			return null;
			
		return wpfilebase_get_post_url($this->file_post_id);
	}
	
	/*public (PHP 4 compatibility) */ function get_formatted_size()
	{
		return wpfilebase_format_filesize($this->file_size);
	}
	
	/*public (PHP 4 compatibility) */ function get_formatted_date()
	{
		return mysql2date(get_option('date_format'), $this->file_date);
	}
	
	/*public (PHP 4 compatibility) */ function delete()
	{
		$this->delete_thumbnail();
		
		if(@unlink($this->get_path()))
		{
			$this->file_name = null;
			$this->file_size = null;
			$this->file_date = null;		
			return true;
		}		
		return false;
	}
	
	
	/*public (PHP 4 compatibility) */ function delete_thumbnail()
	{
		$thumb = $this->get_thumbnail_path();
		if(!empty($thumb) && file_exists($thumb))
			@unlink($thumb);			
		$this->file_thumbnail = null;
	}
	

	/*public (PHP 4 compatibility) */ function remove()
	{	
		global $wpdb;

		if($this->file_category > 0 && ($parent = $this->get_parent()) != null)
			$parent->remove_file($this);
		
		// remove file entry
		$wpdb->query("DELETE FROM " . $wpdb->wpfilebase_files . " WHERE file_id = " . (int)$this->file_id);
		// remove all sub file entries TODO
		//$wpdb->query("DELETE FROM " . $wpdb->wpfilebase_subfiles . " WHERE subfile_parent_file = " . (int)$this->file_id);
			
		return $this->delete();
	}



	/*public (PHP 4 compatibility) */ function change_category($new_cat_id)
	{
		if(is_object($new_cat_id))
			$new_cat_id = $new_cat_id->get_id();
		$new_cat_id = (int)intval($new_cat_id);
			
		// get old paths
		$old_file_path = $this->get_path();
		$old_thumb_path = $this->get_thumbnail_path();
		
		// remove from current cat
		$parent = $this->get_parent();
		if($parent)
			$parent->remove_file($this);
		
		// add to current cat
		$this->file_category = (int)$new_cat_id;
		$parent = $this->get_parent();		
		if($parent)
			$parent->add_file($this);
			
		// create the directory if it doesnt exist
		if(!is_dir(dirname($this->get_path())))
		{
			if ( !wp_mkdir_p(dirname($this->get_path())) )
				return array( 'error' => sprintf( __( 'Unable to create directory %s. Is it\'s parent directory writable?'/*def*/), $this->get_path() ) );
		}
		
		// move file
		if(!empty($old_file_path) && @is_file($old_file_path))
		{
			if(!@rename($old_file_path, $this->get_path()))
				return array( 'error' => sprintf('Unable to move file %s!', $this->get_path()));
			@chmod($this->get_path(), octdec(WPFB_PERM_FILE));
		}
		
		// move thumb
		if(!empty($old_thumb_path) && @is_file($old_thumb_path))
		{
			if(!@rename($old_thumb_path, $this->get_thumbnail_path()))
				return array( 'error' =>'Unable to move thumbnail!');
			@chmod($this->get_thumbnail_path(), octdec(WPFB_PERM_FILE));
		}
		
		return array( 'error' => false);
	}
	
	/*public (PHP 4 compatibility) */ function generate_template($template='', $widget=false)
	{
		static $js_printed = false;
		global $wpfb_file_tpl_uid, $wpfb_load_js;
		
		if(!$widget && empty($wpfb_load_js))
			$wpfb_load_js = true;
		
		if(empty($template))
		{
			$template = wpfilebase_get_opt('template_file_parsed');
			if(empty($template))
			{
				$tpl = wpfilebase_get_opt('template_file');
				if(!empty($tpl))
				{
					wpfilebase_inclib('template');
					$template = wpfilebase_parse_template($tpl);
					wpfilebase_update_opt('template_file_parsed', $template); 
				}
			}
		}

		$wpfb_file_tpl_uid++;
		$f = &$this;
		$template = @eval('return (' . $template . ');');
		
		if(!$js_printed && !is_feed())
		{
			$js = wpfilebase_get_opt('dlclick_js');
			if(!empty($js))
			{
				// TODO: put this in a JS file
				$template .= <<<JS
<script type="text/javascript">
function wpfilebase_dlclick(file_id, file_url) {try{
{$js}
}catch(err){}}
</script>
JS;
			}
			$js_printed = true;
		}
		
		return $template;
	}
    
    function _get_tpl_var($name)
    {
		global $wpfb_file_tpl_uid;

		switch($name) {
			case 'file_url':			return $this->get_url();
			case 'file_url_rel':		return wpfilebase_get_opt('download_base') . '/' . str_replace('\\', '/', $this->get_rel_path());
			case 'file_post_url':		return is_null($url = $this->get_post_url()) ? $this->get_url() : $url;			
			case 'file_icon_url':		return $this->get_icon_url();
			case 'file_size':			return $this->get_formatted_size();
			case 'file_path':			return $this->get_rel_path();
			case 'file_category':		return is_object($parent = $this->get_parent()) ? $parent->cat_name : '';
			
			case 'file_languages':		return wpfilebase_parse_selected_options('languages', $this->file_language);
			case 'file_platforms':		return wpfilebase_parse_selected_options('platforms', $this->file_platform);
			case 'file_requirements':	return wpfilebase_parse_selected_options('requirements', $this->file_requirement, true);
			case 'file_license':		return wpfilebase_parse_selected_options('licenses', $this->file_license);
			
			case 'file_required_level':	return ($this->file_required_level - 1);
			
			case 'file_date':
			case 'file_last_dl_time':	return mysql2date(get_option('date_format'), $this->$name);
			
			case 'uid':					return $wpfb_file_tpl_uid;
		}
		return isset($this->$name) ? $this->$name : '';
    }
	
	function get_tpl_var($name) {
		static $no_esc = array('file_languages', 'file_platforms', 'file_requirements', 'file_license');
		return in_array($name, $no_esc) ? $this->_get_tpl_var($name) : htmlspecialchars($this->_get_tpl_var($name));
	}
	
	function download_denied($msg_id) {
		if(wpfilebase_get_opt('inaccessible_redirect') && !is_user_logged_in())
			auth_redirect();
		$msg = wpfilebase_get_opt($msg_id);
		if(!$msg) $msg = $msg_id;
		wp_die(empty($msg) ? __('Cheatin&#8217; uh?') : $msg);
		exit;
	}
	
	/*public (PHP 4 compatibility) */ function download()
	{
		global $wpdb, $current_user, $user_ID;
		
		@error_reporting(0);
		wpfilebase_inclib('download');
		$downloader_ip = preg_replace( '/[^0-9a-fA-F:., ]/', '', $_SERVER['REMOTE_ADDR']);
		get_currentuserinfo();
		$logged_in = (!empty($user_ID));
		$user_role = $logged_in ? array_shift($current_user->roles) : null; // get user's highest role (like in user-eidt.php)
		$is_admin = current_user_can('level_8') && $user_role == 'administrator'; 
		
		// check user level
		if(!$this->current_user_can_access())
			$this->download_denied('inaccessible_msg');
		
		// check offline
		if($this->file_offline)
			wp_die(wpfilebase_get_opt('file_offline_msg'));
		
		// check referrer
		if(!$this->file_direct_linking) {			
			// if referer check failed, redirect to the file post
			if(!wpfilebase_referer_check()) {
				wp_redirect(wpfilebase_get_post_url($this->file_post_id));
				exit;
			}
		}
		
		// check traffic
		if(!wpfilebase_check_traffic($this->file_size)) {
			header('HTTP/1.x 503 Service Unavailable');
			wp_die(wpfilebase_get_opt('traffic_exceeded_msg'));
		}

		// check daily user limit
		if(!$is_admin && wpfilebase_get_opt('daily_user_limits')) {
			if(!$logged_in)
				$this->download_denied('inaccessible_msg');
			
			$today = intval(date('z'));
			$usr_dls_today = intval(get_user_option(WPFB_OPT_NAME . '_dls_today'));
			$usr_last_dl_day = intval(date('z', intval(get_user_option(WPFB_OPT_NAME . '_last_dl'))));
			if($today != $usr_last_dl_day)
				$usr_dls_today = 0;
			
			// check for limit
			$dl_limit = intval(wpfilebase_get_opt('daily_limit_'.$user_role));
			if($usr_dls_today >= $dl_limit)
				$this->download_denied(($dl_limit > 0) ? sprintf(wpfilebase_get_opt('daily_limit_exceeded_msg'), $dl_limit) : 'inaccessible_msg');			
			
			$usr_dls_today++;
			update_user_option($user_ID, WPFB_OPT_NAME . '_dls_today', $usr_dls_today);
			update_user_option($user_ID, WPFB_OPT_NAME . '_last_dl', time());
		}			
		
		// count download
		if(!$is_admin || !wpfilebase_get_opt('ignore_admin_dls')) {
			$last_dl_time = mysql2date('U', $file->last_dl_time , false);
			if(empty($this->file_last_dl_ip) || $this->file_last_dl_ip != $downloader_ip || ((time() - $last_dl_time) > 86400))
				$wpdb->query("UPDATE " . $wpdb->wpfilebase_files . " SET file_hits = file_hits + 1, file_last_dl_ip = '" . $downloader_ip . "', file_last_dl_time = '" . current_time('mysql') . "' WHERE file_id = " . (int)$this->file_id);
		}
		
		wpfilebase_send_file($this->get_path(), wpfilebase_get_opt('bitrate_' . ($logged_in?'registered':'unregistered')), $this->file_hash);
		
		exit;
	}
	
	/*TODO?
	public function update_subfiles()
	{
		global $wpdb;
		
		// clear all subfiles
		$wpdb->query("DELETE FROM " . $wpdb->wpfilebase_subfiles . " WHERE subfile_parent_file = " . (int)$this->file_id);
		
		// check if the file is an archive and read it's files
		wpfilebase_inclib('file');
		$sub_files = wpfilebase_list_archive_files($full_upload_path);
		if(!empty($sub_files) && is_array($sub_files) && count($sub_files) > 0)
		{
			foreach($sub_files as $sb)
				$wpdb->insert( $wpdb->wpfilebase_subfiles, array('subfile_parent_file' => (int)$this->file_id, 'subfile_name' => $sb['name'], 'subfile_size' => (int)$sb['size']));
		}
	}
	*/
}

?>