<?php

wpfilebase_inclib('common');
wpfilebase_inclib('admin_lite');
include_once(WPFB_PLUGIN_ROOT . 'wp-filebase_item.php');

function wpfilebase_options()
{
	$multiple_entries_desc = __('One entry per line. Seperate the title and a short tag (not longer than 8 characters) with \'|\'.<br />All lines beginning with \'*\' are selected by default.', WPFB);
	$multiple_line_desc = __('One entry per line.', WPFB);
	$bitrate_desc = __('Limits the maximum tranfer rate for downloads. 0 = unlimited', WPFB);
	$traffic_desc = __('Limits the maximum data traffic. 0 = unlimited', WPFB);
	$dls_per_day = __('downloads per day', WPFB);
	$daily_limit_for = __('Daily limit for %s', WPFB);
	
	$upload_path_base = str_replace(ABSPATH, '', get_option('upload_path'));
	if($upload_path_base == '' || $upload_path_base == '/')
		$upload_path_base = 'wp-content/uploads';
	
	return array (
	
	// common
	'upload_path'			=> array('default' => $upload_path_base . '/filebase', 'title' => __('Upload Path', WPFB), 'type' => 'text', 'class' => 'code', 'size' => 65),
	'thumbnail_size'		=> array('default' => 120, 'title' => __('Thumbnail size'), 'type' => 'number', 'class' => 'num', 'size' => 8),
	
	// display
	'auto_attach_files' 	=> array('default' => true,'title' => __('Show attached files', WPFB), 'type' => 'checkbox', 'desc' => __('If enabled, all associated files are listed below an article', WPFB)),
	'filelist_sorting'		=> array('default' => 'file_display_name', 'title' => __('Default sorting', WPFB), 'type' => 'select', 'desc' => __('The file property lists are sorted by', WPFB), 'options' => wpfilebase_sorting_options()),
	'filelist_sorting_dir'	=> array('default' => 0, 'title' => __('Sorting direction', WPFB), 'type' => 'select', 'desc' => __('The sorting direction of file lists', WPFB), 'options' => array(0 => __('Ascending'), 1 => __('Descending'))),

	// limits
	'bitrate_unregistered'	=> array('default' => 0, 'title' => __('Bit rate limit for guests', WPFB), 'type' => 'number', 'unit' => 'KiB/Sec', 'desc' => &$bitrate_desc),
	'bitrate_registered'	=> array('default' => 0, 'title' => __('Bit rate limit for registered users', WPFB), 'type' => 'number', 'unit' => 'KiB/Sec', 'desc' => &$bitrate_desc),	
	'traffic_day'			=> array('default' => 0, 'title' => __('Daily traffic limit', WPFB), 'type' => 'number', 'unit' => 'MiB', 'desc' => &$traffic_desc),
	'traffic_month'			=> array('default' => 0, 'title' => __('Monthly traffic limit', WPFB), 'type' => 'number', 'unit' => 'GiB', 'desc' => &$traffic_desc),
	'traffic_exceeded_msg'	=> array('default' => __('Traffic limit exceeded! Please try again later.', WPFB), 'title' => __('Traffic exceeded message', WPFB), 'type' => 'text', 'size' => 65),
	'file_offline_msg'		=> array('default' => __('This file is currently offline.', WPFB), 'title' => __('File offline message', WPFB), 'type' => 'text', 'size' => 65),
		
	'daily_user_limits'		=> array('default' => false, 'title' => __('Daily user download limits', WPFB), 'type' => 'checkbox', 'desc' => __('If enabled, unregistered users cannot download any files. You can set different limits for each user role below.', WPFB)), 	
	'daily_limit_subscriber'	=> array('default' => 5, 'title' => sprintf($daily_limit_for, _x('Subscriber', 'User role')), 'type' => 'number', 'unit' => &$dls_per_day),
	'daily_limit_contributor'	=> array('default' => 10, 'title' => sprintf($daily_limit_for, _x('Contributor', 'User role')), 'type' => 'number', 'unit' => &$dls_per_day),
	'daily_limit_author'		=> array('default' => 15, 'title' => sprintf($daily_limit_for, _x('Author', 'User role')), 'type' => 'number', 'unit' => &$dls_per_day),
	'daily_limit_editor'		=> array('default' => 20, 'title' => sprintf($daily_limit_for, _x('Editor', 'User role')), 'type' => 'number', 'unit' => &$dls_per_day),
	'daily_limit_exceeded_msg'	=> array('default' => __('You can only download %d files per day.', WPFB), 'title' => __('Daily limit exceeded message', WPFB), 'type' => 'text', 'size' => 65),
	
	
	'disable_permalinks'	=> array('default' => false, 'title' => __('Disable download permalinks', WPFB), 'type' => 'checkbox', 'desc' => __('Enable this if you have problems with permalinks.', WPFB)),
	'download_base'			=> array('default' => 'download', 'title' => __('Download URL base', WPFB), 'type' => 'text', 'desc' => sprintf(__('The url prefix for file download links. Example: <code>%s</code> (Only used when Permalinks are enabled.)', WPFB), get_option('home').'/%value%/category/file.zip')),
	
	'file_browser_post_id'	=> array('default' => '', 'title' => __('Post ID of the file browser', WPFB), 'type' => 'number', 'unit' => '<a href="javascript:;" class="button" onclick="openPostBrowser(\'file_browser_post_id\')">' . __('Select') . '...</a>', 'desc' => __('Specify the ID of the post or page where the file browser should be placed. If you want to disable this feature leave the field blank.', WPFB)),
	'cat_drop_down'			=> array('default' => false, 'title' => __('Category drop down list', WPFB), 'type' => 'checkbox', 'desc' => __('Use category drop down list in the file browser instead of listing like files.', WPFB)),

	'force_download'		=> array('default' => false, 'title' => __('Always force download', WPFB), 'type' => 'checkbox', 'desc' => __('If enabled files that can be viewed in the browser (like images, PDF documents or videos) can only be downloaded (no streaming).', WPFB)),
	'ignore_admin_dls'		=> array('default' => true, 'title' => __('Ignore downloads by admins', WPFB), 'type' => 'checkbox'),
	'hide_inaccessible'		=> array('default' => true, 'title' => __('Hide inaccessible files and categories', WPFB), 'type' => 'checkbox', 'desc' => __('If enabled files tagged <i>For members only</i> will not be listed for guests or users whith insufficient rights.', WPFB)),
	'inaccessible_msg'		=> array('default' => __('You are not allowed to access this file!', WPFB), 'title' => __('Inaccessible file message', WPFB), 'type' => 'text', 'size' => 65, 'desc' => __('This message will be displayed if users try to download a file they cannot access', WPFB)),
	'inaccessible_redirect'	=> array('default' => false, 'title' => __('Redirect to login', WPFB), 'type' => 'checkbox', 'desc' => __('Guests trying to download inaccessible files are redirected to the login page if this option is enabled.', WPFB)),
	
	'parse_tags_rss'		=> array('default' => true, 'title' => __('Parse template tags in RSS feeds', WPFB), 'type' => 'checkbox', 'desc' => __('If enabled WP-Filebase content tags are parsed in RSS feeds.', WPFB)),
	
	'allow_srv_script_upload'	=> array('default' => false, 'title' => __('Allow script upload', WPFB), 'type' => 'checkbox', 'desc' => __('If you enable this, scripts like PHP or CGI can be uploaded. <b>WARNING:</b> Enabling script uploads is a <b>security risk</b>!', WPFB)),
	
	'accept_empty_referers'	=> array('default' => true, 'title' => __('Accept empty referers', WPFB), 'type' => 'checkbox', 'desc' => __('If enabled, direct-link-protected files can be downloaded when the referer is empty (i.e. user entered file url in address bar or browser does not send referers)', WPFB)),	
	'allowed_referers' 		=> array('default' => '', 'title' => __('Allowed referers', WPFB), 'type' => 'textarea', 'desc' => __('Sites with matching URLs can link to files directly.', WPFB).'<br />'.$multiple_line_desc),
	
	'decimal_size_format'	=> array('default' => false, 'title' => __('Decimal file size prefixes', WPFB), 'type' => 'checkbox', 'desc' => __('Enable this if you want decimal prefixes (1 MB = 1000 KB = 1 000 000 B) instead of binary (1 MiB = 1024 KiB = 1 048 576 B)', WPFB)),

	'languages'				=> array('default' => "English|en\nDeutsch|de", 'title' => __('Languages'), 'type' => 'textarea', 'desc' => &$multiple_entries_desc),
	'platforms'				=> array('default' => "Windows 95|win95\n*Windows 98|win98\n*Windows 2000|win2k\n*Windows XP|winxp\n*Windows Vista|vista\n*Windows 7|win7\nLinux|linux\nMac OS X|mac", 'title' => __('Platforms', WPFB), 'type' => 'textarea', 'desc' => &$multiple_entries_desc, 'nowrap' => true),	
	'licenses'				=> array('default' => "*Freeware|free\nShareware|share\nGNU General Public License|gpl\nGNU Lesser General Public License|lgpl\nGNU Affero General Public License|agpl", 'title' => __('Licenses', WPFB), 'type' => 'textarea', 'desc' => &$multiple_entries_desc, 'nowrap' => true),
	'requirements'			=> array('default' => ".NET Framework 2.0|.net2|http://www.microsoft.com/downloads/details.aspx?FamilyID=0856eacb-4362-4b0d-8edd-aab15c5e04f5\n.NET Framework 3.0|.net3|http://www.microsoft.com/downloads/details.aspx?FamilyID=10cc340b-f857-4a14-83f5-25634c3bf043\n.NET Framework 3.5|.net35|http://www.microsoft.com/downloads/details.aspx?FamilyID=333325fd-ae52-4e35-b531-508d977d32a6", 'title' => __('Requirements', WPFB), 'type' => 'textarea', 'desc' => $multiple_entries_desc . ' ' . __('You can optionally add |<i>URL</i> to each line to link to the required software/file.', WPFB), 'nowrap' => true),
	
	
	'template_file'			=> array('default' =>
<<<TPLFILE
<div class="wpfilebase-attachment">
 <div class="wpfilebase-fileicon"><a href="%file_url%" title="Download %file_display_name%"><img align="middle" src="%file_icon_url%" alt="%file_display_name%" /></a></div>
 <div class="wpfilebase-rightcol">
  <div class="wpfilebase-filetitle">
   <a href="%file_url%" title="Download %file_display_name%">%file_display_name%</a><br />
   %file_name%<br />
   <!-- IF %file_version% -->%'Version:'% %file_version%<br /><!-- ENDIF -->
   <!-- IF %file_post_id% AND get_the_ID() != %file_post_id% --><a href="%file_post_url%" class="wpfilebase-postlink">%'View post'%</a><!-- ENDIF -->
  </div>
  <div class="wpfilebase-filedetails" id="wpfilebase-filedetails%uid%" style="display: none;">
  <p>%file_description%</p>
  <table border="0">
   <!-- IF %file_languages% --><tr><th>%'Languages'%:</th><td>%file_languages%</td></tr><!-- ENDIF -->
   <!-- IF %file_author% --><tr><th>%'Author'%:</th><td>%file_author%</td></tr><!-- ENDIF -->
   <!-- IF %file_platforms% --><tr><th>%'Platforms'%:</th><td>%file_platforms%</td></tr><!-- ENDIF -->
   <!-- IF %file_requirements% --><tr><th>%'Requirements'%:</th><td>%file_requirements%</td></tr><!-- ENDIF -->
   <!-- IF %file_category% --><tr><th>%'Category:'%</th><td>%file_category%</td></tr><!-- ENDIF -->
   <!-- IF %file_license% --><tr><th>%'License'%:</th><td>%file_license%</td></tr><!-- ENDIF -->
   <tr><th>%'Date'%:</th><td>%file_date%</td></tr>
   <!-- <tr><th>%'MD5 Hash'%:</th><td><small>%file_hash%</small></td></tr> -->
  </table>
  </div>
 </div>
 <div class="wpfilebase-fileinfo">
  %file_size%<br />
  %file_hits% %'Downloads'%<br />
  <a href="#" onclick="return wpfilebase_filedetails(%uid%);">%'Details'%...</a>
 </div>
 <div style="clear: both;"></div>
</div>
TPLFILE
	, 'title' => __('Default File Template', WPFB), 'type' => 'textarea', 'desc' => (wpfilebase_template_fields_select('template_file') . '<br />' . __('The template for attachments', WPFB)), 'class' => 'code'),

	'template_cat'			=> array('default' =>
<<<TPLCAT
<div class="wpfilebase-attachment-cat">
 <div class="wpfilebase-fileicon"><a href="%cat_url%" title="Goto %cat_name%"><img align="middle" src="%cat_icon_url%" alt="%cat_name%" /></a></div>
 <div class="wpfilebase-rightcol">
  <div class="wpfilebase-filetitle">
   <p><a href="%cat_url%" title="Goto category %cat_name%">%cat_name%</a></p>
   %cat_num_files% <!-- IF %cat_num_files% == 1 -->file<!-- ELSE -->files<!-- ENDIF -->
  </div>
 </div>
 <div style="clear: both;"></div>
</div>
TPLCAT
	, 'title' => __('Category Template', WPFB), 'type' => 'textarea', 'desc' => (wpfilebase_template_fields_select('template_cat', false, true) . '<br />' . __('The template for category lists (used in the file browser)', WPFB)), 'class' => 'code'),

	'dlclick_js'			=> array('default' =>
<<<JS
if(typeof pageTracker == 'object') {
	pageTracker._trackPageview(file_url); // new google analytics tracker
} else if(typeof urchinTracker == 'function') {	
	urchinTracker(file_url); // old google analytics tracker
}
JS
	, 'title' => __('Download JavaScript', WPFB), 'type' => 'textarea', 'desc' => __('Here you can enter JavaScript Code which is executed when a user clicks on file download link. The following variables can be used: <i>file_id</i>: the ID of the file, <i>file_url</i>: the clicked download url', WPFB), 'class' => 'code'),

	//'max_dls_per_ip'			=> array('default' => 10, 'title' => __('Maximum downloads', WPFB), 'type' => 'number', 'unit' => 'per file, per IP Address', 'desc' => 'Maximum number of downloads of a file allowed for an IP Address. 0 = unlimited'),
	//'archive_lister'			=> array('default' => false, 'title' => __('Archive lister', WPFB), 'type' => 'checkbox', 'desc' => __('Uploaded files are scanned for archives', WPFB)),
	//'enable_ratings'			=> array('default' => false, 'title' => __('Ratings'), 'type' => 'checkbox', 'desc' => ''),
	);
}


function wpfilebase_template_fields_desc($for_cat=false)
{
	return ( $for_cat ?
	array(	
	'cat_name'				=> __('The category name', WPFB),
	'cat_description'		=> __('Short description', WPFB),
	
	'cat_url'				=> __('The category URL', WPFB),
	'cat_path'				=> __('Category path (e.g cat1/cat2/)', WPFB),
	'cat_folder'			=> __('Just the category folder name, not the path', WPFB),
	
	'cat_parent_name'		=> __('Name of the parent categories (empty if none)', WPFB),
	'cat_num_files'			=> __('Number of files in the category', WPFB),
	
	'cat_required_level'	=> __('The minimum user level to view this category (-1 = guest, 0 = Subscriber ...)', WPFB),
	
	'cat_id'				=> __('The category ID', WPFB),
	'uid'					=> __('A unique ID number to indetify elements within a template', WPFB),
	):
	array(	
	'file_name'				=> __('Name of the file', WPFB),
	'file_size'				=> __('Formatted file size', WPFB),
	'file_date'				=> __('Formatted file date', WPFB),
	'file_thumbnail'		=> __('Name of the thumbnail file', WPFB),
	'file_display_name'		=> __('Title', WPFB),
	'file_description'		=> __('Short description', WPFB),
	'file_version'			=> __('File version', WPFB),
	'file_author'			=> __('Author'),
	'file_languages'		=> __('Supported languages', WPFB),
	'file_platforms'		=> __('Supported platforms (operating systems)', WPFB),
	'file_requirements'		=> __('Requirements to use this file', WPFB),
	'file_license'			=> __('License', WPFB),
	'file_required_level'	=> __('The minimum user level to download this file (-1 = guest, 0 = Subscriber ...)', WPFB),
	'file_offline'			=> __('1 if file is offline, otherwise 0', WPFB),
	'file_direct_linking'	=> __('1 if direct linking is allowed, otherwise 0', WPFB),
	'file_category'			=> __('The category name', WPFB),
	//'file_update_of'		=>
	'file_post_id'			=> __('ID of the post/page this file belongs to', WPFB),
	'file_added_by'			=> __('User ID of the uploader', WPFB),
	'file_hits'				=> __('How many times this file has been downloaded.', WPFB),
	//'file_ratings'			=>
	//'file_rating_sum'		=>
	'file_last_dl_ip'		=> __('IP Address of the last downloader', WPFB),
	'file_last_dl_time'		=> __('Time of the last download', WPFB),
	
	'file_url'				=> __('Download URL', WPFB),
	'file_post_url'			=> __('URL of the post/page this file belongs to', WPFB),
	'file_icon_url'			=> __('URL of the thumbnail or icon', WPFB),
	'file_path'				=> __('Category path and file name (e.g cat1/cat2/file.ext)', WPFB),
	
	'file_id'				=> __('The file ID', WPFB),
	
	'uid'					=> __('A unique ID number to indetify elements within a template', WPFB),
	));
}

function wpfilebase_sorting_options()
{
	return array(	
	'file_name'				=> __('Name of the file', WPFB),
	'file_size'				=> __('Formatted file size', WPFB),
	'file_date'				=> __('Formatted file date', WPFB),
	'file_display_name'		=> __('Title', WPFB),
	'file_description'		=> __('Short description', WPFB),
	'file_version'			=> __('File version', WPFB),
	'file_author'			=> __('Author', WPFB),
	'file_license'			=> __('License', WPFB),
	'file_required_level'	=> __('The minimum user level to download this file (-1 = guest, 0 = Subscriber ...)', WPFB),
	'file_offline'			=> __('Offline > Online', WPFB),
	'file_direct_linking'	=> __('Direct linking > redirect to post', WPFB),
	'file_category'			=> __('Category', WPFB),
	'file_post_id'			=> __('ID of the post/page this file belongs to', WPFB),
	'file_added_by'			=> __('User ID of the uploader', WPFB),
	'file_hits'				=> __('How many times this file has been downloaded.', WPFB),
	'file_last_dl_time'		=> __('Time of the last download', WPFB),
	);
}

function wpfilebase_template_fields_select($input, $short=false, $for_cat=false)
{
	$out = __('Add template variable:', WPFB) . ' <select name="_wpfb_tpl_fields" onchange="wpfilebaseAddTplField(this, \'' . $input . '\')"><option value=""></option>';	
	foreach(wpfilebase_template_fields_desc($for_cat) as $tag => $desc)
	{
		$out .= '<option value="'.$tag.'" title="'.$desc.'">'.$tag.($short ? '' : ' ('.$desc.')').'</option>';
	}
	$out .= '</select>';
	return $out;
}


function wpfilebase_insert_category($catarr)
{
	global $wpdb;
	
	$catarr = wp_parse_args($catarr, array('cat_id' => 0, 'cat_name' => '', 'cat_description' => '', 'cat_parent' => 0, 'cat_folder' => ''));
	extract($catarr, EXTR_SKIP);

	$cat_id = intval($cat_id);
	$cat_parent = intval($cat_parent);
	
	// Are we updating or creating?
	$update = ($cat_id > 0);
	if ($update)
		$cat = WPFilebaseCategory::get_category($cat_id);
	else
		$cat = new WPFilebaseCategory(array('cat_id' => 0));
	
	$cat->cat_name = trim($cat_name);
	$cat->cat_description = trim($cat_description);
	$old_path = !empty($cat->cat_folder) ? $cat->get_path() : '';
	$cat->cat_folder = trim($cat_folder);
	$new_path = $cat->get_path();
	
	if (empty($cat->cat_name) && empty($cat->cat_folder))
		return array( 'error' => __('You must enter a category name or a folder name.', WPFB) );
		
	if (empty($cat->cat_name))
		$cat->cat_name = wpfilebase_filename2title($cat->cat_folder, false);
	elseif(empty($cat->cat_folder))
		$cat->cat_folder = strtolower(str_replace(' ', '_', $cat->cat_name));
	
	$cat->cat_folder = preg_replace('/\s/', ' ', $cat->cat_folder);
	if(!preg_match('/^[0-9a-z-_.+,\s]+$/i', $cat->cat_folder))
		return array( 'error' => __('The category folder name contains invalid characters.', WPFB) );
	
	// move existing dir
	if($update && !empty($old_path) && $old_path != $new_path && is_dir($old_path)) {
		//die($old_path.'=>'.$new_path);
		if (!wp_mkdir_p($new_path))
			return array( 'error' => sprintf( __('Unable to create directory %s. Is its parent directory writable by the server?'/*def*/), $new_path) );
		wpfilebase_inclib('file');
		wpfilebase_move_dir($old_path, $new_path);
	}
		
	// permission
	$cat_members_only = !empty($cat_members_only);
	$cat->cat_required_level = $cat_members_only ? (min(max(intval($cat_required_level), 0), 10) + 1) : 0;
	$cat_child_apply_perm = $update && !empty($cat_child_apply_perm);
	if($cat_child_apply_perm)
	{
		//WPFilebaseCategory::get_categories();
		// apply permissions to all child files
		$files = $cat->get_files();
		foreach($files as /* & PHP 4 compability */ $file)
		{
			$file->file_required_level = $cat->cat_required_level;
			$file->db_save();
		}
	}
	
		
	// handle parent cat
	if($cat_parent <= 0 || $cat_parent == $cat_id) {
		$cat_parent = 0;
		$pcat = null;
	} else {
		$pcat = WPFilebaseCategory::get_category($cat_parent);
		if($pcat == null || ($update && $cat->is_ancestor_of($pcat)))
			$cat_parent = 0;
	}
	
	// if create new and dir already exists, cancel
	if(!$update && empty($add_existing))
	{
		$prev_parent = $cat->cat_parent;
		$cat->cat_parent = $cat_parent;
		if( @file_exists($cat->get_path()))
		{			
			return array( 'error' => sprintf( __( 'The directory %s already exists!', WPFB), $cat->get_path() ) );
		}
		$cat->cat_parent = $prev_parent;
	} elseif($add_existing)
		$cat->cat_parent = intval($cat_parent);
	
	$result = $cat->change_category($cat_parent);
	if(!empty($result['error']))
		return $result;
		
		
	// icon
	if(!empty($cat_icon_delete)) {
		@unlink($cat->get_thumbnail_path());
		$cat->cat_icon = null;
	}
	if(!empty($cat_icon) && @is_uploaded_file($cat_icon['tmp_name']) && !empty($cat_icon['name'])) {
		$ext = strtolower(substr($cat_icon['name'], strrpos($cat_icon['name'], '.')+1));
		if($ext == 'jpg' || $ext == 'jpeg' || $ext == 'png' || $ext == 'gif') {
			if(!empty($cat->cat_icon))
				@unlink($cat->get_thumbnail_path());
			$cat->cat_icon = '_caticon.'.$ext;
			if(!@move_uploaded_file($cat_icon['tmp_name'], $cat->get_thumbnail_path()))
				return array( 'error' => __( 'Unable to move category icon!', WPFB));	
			@chmod($cat->get_thumbnail_path(), octdec(WPFB_PERM_FILE));
		}
	}
	
	// save into db
	$result = $cat->db_save();	
	if(!empty($result['error']))
		return $result;		
	$cat_id = (int)$result['cat_id'];	
	
	return array( 'error' => false, 'cat_id' => $cat_id);
}


function wpfilebase_insert_file($filedata)
{	
	extract($filedata, EXTR_SKIP);
	
	$file_id = isset($file_id) ? (int)$file_id : 0;
	
	// are we updating or creating?
	$update = ( !empty($file_id) && $file_id > 0 && (($file = WPFilebaseFile::get_file($file_id)) != null) );
	if(!$update)
		$file = new WPFilebaseFile(array('file_id' => 0));
		
	$add_existing = !empty($add_existing);
	
	// are we uploading a file?
	$upload = (!$add_existing && @is_uploaded_file($file_upload['tmp_name']) && !empty($file_upload['name']));
	// are we uploading a thumbnail?
	$upload_thumb = (!$add_existing && @is_uploaded_file($file_upload_thumb['tmp_name']) && file_is_valid_image($file_upload_thumb['tmp_name']));
	
	$file_src_path = $upload ? $file_upload['tmp_name'] : $file_path;
	
	// check extension
	if($upload || $add_existing) {
		if(!wpfilebase_extension_is_allowed($upload ? $file_upload['name'] : $file_path)) {
			@unlink($file_src_path);
			return array( 'error' => sprintf( __( 'The file extension of the file <b>%s</b> is forbidden!', WPFB), $upload ? $file_upload['name'] : $file_path ) );
		}
	}
	
	// handle category
	$file_category = intval($file_category);
	if ($file_category > 0 && WPFilebaseCategory::get_category($file_category) == null)
		$file_category = 0;
	if($update && $file->file_category != $file_category)
	{
		$result = $file->change_category($file_category);
		if(!empty($result['error']))
			return $result;
	}
	
	// delete thumbnail if user wants to
	if(!empty($file_delete_thumb))
		$file->delete_thumbnail();

	// if we update, delete the old file
	if ($update && $upload)
		$file->delete();
		
	// set the new filename & category
	if($upload || $add_existing)
		$file->file_name = basename($upload ? $file_upload['name'] : $file_path);
	if(!$update) {
		$result = $file->change_category($file_category);
		if(!empty($result['error']))
			return $result;
	}

	// if there is an uploaded file 
	if($upload) {
		// if create new and file already exists, cancel
		if(!$update && @file_exists($file->get_path()))	
			return array( 'error' => sprintf( __( 'File %s already exists. You have to delete it first!', WPFB), $file->get_path() ) );

		// move uploaded file to the right place
		if(!@move_uploaded_file($file_src_path, $file->get_path()) || !@file_exists($file->get_path()))
			return array( 'error' => sprintf( __( 'Unable to move file %s! Is the upload directory writeable?', WPFB), $file->file_name ) );		
	} elseif(!$add_existing && !$update) {
		return array( 'error' => __('No file was uploaded.', WPFB) );
	}
	
	if($upload || $add_existing) {
		// check if the file is an image and create thumbnail
		if(empty($file->file_thumbnail) && !$upload_thumb && ($file->get_extension() == '.bmp' || file_is_valid_image($file->get_path())))
			$file->create_thumbnail();	
	}
	
	// set permissions
	@chmod ($file->get_path(), octdec(WPFB_PERM_FILE));
	
	// get file info
	$file->file_size = (int)filesize($file->get_path());
	$file->file_hash = md5_file($file->get_path());
	$file->file_date = !empty($file_date) ? $file_date : gmdate('Y-m-d H:i:s', filemtime($file->get_path()));

	// set display name
	$file->file_display_name = $file_display_name;
	if (empty($file->file_display_name))
		$file->file_display_name = wpfilebase_filename2title($file->file_name);
	
	$file_language = $file_platform = $file_requirement = '';
	if(!empty($file_languages))
		$file_language = implode('|', $file_languages);
	if(!empty($file_platforms))
		$file_platform = implode('|', $file_platforms);
	if(!empty($file_requirements))
		$file_requirement = implode('|', $file_requirements);
		
	$file_offline = empty($file_offline) ? 0 : 1;
	
	// permission
	$file_members_only = !empty($file_members_only);
	$file_required_level = $file_members_only ? (min(max(intval($file_required_level), 0), 10) + 1) : 0;
	
	if(!isset($file_direct_linking))
	{
		// allow direct linking by default
		$file_direct_linking = 1;
	}
		
	$var_names = array('version', 'author', 'date', 'post_id', 'direct_linking', 'description', 'hits', 'language', 'platform', 'requirement', 'license', 'offline', 'required_level');
	for($i = 0; $i < count($var_names); $i++)
	{
		$vn = 'file_' . $var_names[$i];
		if(isset(${$vn}))
			$file->$vn = ${$vn};
	}
		

	// set the user id	
	$current_user = wp_get_current_user();
	if(empty($current_user->ID))
		return array( 'error' => __('Could not get user id!', WPFB) );	
	if(!$update)
		$file->file_added_by = $current_user->ID;	

	// if thumbnail was uploaded
	if($upload_thumb)
	{
		// delete the old thumbnail (if existing)
		$file->delete_thumbnail();
		
		$thumb_dest_path = dirname($file->get_path()) . '/thumb_' . $file_upload_thumb['name'];
				
		if(@move_uploaded_file($file_upload_thumb['tmp_name'], $thumb_dest_path))
		{
			$file->create_thumbnail($thumb_dest_path);
		}
	}
	
	
	// save into db
	$result = $file->db_save();	
	if(!empty($result['error']))
		return $result;		
	$file_id = (int)$result['file_id'];	
	
	if($upload)
	{
		// TODO?
		//$file->update_subfiles();
	}
	
	return array( 'error' => false, 'file_id' => $file_id);
}


function wpfilebase_sync($hash_sync=false)
{
	@set_time_limit(0);
	
	$result = array('not_found' => array(), 'changed' => array(), 'not_added' => array(), 'error' => array(), 'updated_categories' => array());
	$files = &WPFilebaseFile::get_files();
	
	$file_paths = array();
	foreach($files as $id => /* & PHP 4 compability */ $file)
	{
		$file_path = str_replace('\\', '/', $file->get_path());
		$file_paths[] = $file_path;
		if($file->get_thumbnail_path())
			$file_paths[] = str_replace('\\', '/', $file->get_thumbnail_path());
		
		if(!@is_file($file_path) || !@is_readable($file_path))
		{
			$result['not_found'][] = $file;
			continue;
		}
		
		if($hash_sync)
			$file_hash = @md5_file($file_path);
		$file_size = (int)@filesize($file_path);
		$file_time = filemtime($file_path);
		
		if( ($hash_sync && $file->file_hash != $file_hash) || $file->file_size != $file_size)
		{
			$file->file_size = $file_size;
			$file->file_hash = $hash_sync ? $file_hash : @md5_file($file_path);
			
			$result = $file->db_save();
			
			if(!empty($result['error']))
				$result['error'][] = $file;
			else
				$result['changed'][] = $file;
		}
	}
	
	// search for not added files
	$upload_dir = wpfilebase_upload_dir();	
	$uploaded_files = list_files($upload_dir);
	$upload_dir_len = strlen($upload_dir);
	for($i = 0; $i < count($uploaded_files); $i++) {
		$fn = str_replace('\\', '/', $uploaded_files[$i]);
		$fbn = basename($fn);
		if($fbn{0} == '.' || $fbn == '_wp-filebase.css' || strpos($fbn, '_caticon.') !== false)
			continue;
		if(!in_array($fn, $file_paths) && is_file($fn) && is_readable($fn)) {
			$res = wpfilebase_add_existing_file($fn);			
			if(empty($res['error']))
				$result['added'][] = substr($fn, $upload_dir_len);
			else
				$result['error'][] = $res['error'];
		}
	}
	
	// chmod
	@chmod ($upload_dir, octdec(WPFB_PERM_DIR));
	for($i = 0; $i < count($file_paths); $i++)
	{
		if(file_exists($file_paths[$i]))
		{
			@chmod ($file_paths[$i], octdec(WPFB_PERM_FILE));
			if(!is_writable($file_paths[$i]) && !is_writable(dirname($file_paths[$i])))
				$result['warnings'][] = sprintf(__('File <b>%s</b> is not writable!', WPFB), substr($file_paths[$i], $upload_dir_len));
		}
	}	
	
	// sync categories
	$result['updated_categories'] = WPFilebaseCategory::sync_categories();
		
	wpfilebase_protect_upload_path();
	
	return $result;
}

function wpfilebase_add_existing_file($file_path)
{
	$upload_dir = wpfilebase_upload_dir();
	$rel_path = trim(substr($file_path, strlen($upload_dir)), '/');
	$rel_dir = dirname($rel_path);
	
	$last_cat_id = 0;
	
	if(!empty($rel_dir) && $rel_dir != '.')
	{
		$dirs = explode('/', $rel_dir);
		foreach($dirs as $dir)
		{
			if(empty($dir) || $dir == '.')
				continue;
				
			if(is_object($cat = WPFilebaseCategory::get_category_by_folder($dir, $last_cat_id))) {
				$last_cat_id = $cat->cat_id;
			} else {
				$result = wpfilebase_insert_category(array('add_existing' => true, 'cat_parent' => $last_cat_id, 'cat_folder' => $dir));
				if(!empty($result['error']))
					return $result;
				elseif(empty($result['cat_id']))
					wp_die('Could not create category!');
				else
					$last_cat_id = intval($result['cat_id']);
			}
		}
	}
	
	return wpfilebase_insert_file(array('add_existing' => true, 'file_category' => $last_cat_id, 'file_path' => $file_path));
}

function wpfilebase_wpcache_reject_uri($add_uri, $remove_uri='')
{
	// changes the settings of wp cache
	
	global $cache_rejected_uri;
	
	$added = false;

	if(!isset($cache_rejected_uri))
		return false;

	// remove uri
	if(!empty($remove_uri))
	{
		$new_cache_rejected_uri = array();
			
		foreach($cache_rejected_uri as $i => $v)
		{
			if($v != $remove_uri)
				$new_cache_rejected_uri[$i] = $v;
		}
		
		$cache_rejected_uri = $new_cache_rejected_uri;
	}
	
	if(!in_array($add_uri, $cache_rejected_uri))
	{
		$cache_rejected_uri[] = $add_uri;
		$added = true;
	}
	
	return (wpfilebase_wpcache_save_rejected_uri() && $added);
}

function wpfilebase_wpcache_save_rejected_uri()
{
	global $cache_rejected_uri, $wp_cache_config_file;
	
	if(!isset($cache_rejected_uri) || empty($wp_cache_config_file) || !function_exists('wp_cache_replace_line'))
		return false;	
	
	$text = var_export($cache_rejected_uri, true);
	$text = preg_replace('/[\s]+/', ' ', $text);
	wp_cache_replace_line('^ *\$cache_rejected_uri', "\$cache_rejected_uri = $text;", $wp_cache_config_file);

	return true;
}

function wpfilebase_make_options_list($opt_name, $selected = null, $add_empty_opt = false)
{
	$options = wpfilebase_get_opt($opt_name);	
	$options = explode("\n", $options);
	$def_sel = (is_null($selected) && !is_string($selected));
	$list = $add_empty_opt ? ('<option value=""' . ( (is_string($selected) && $selected == '') ? ' selected="selected"' : '') . '>-</option>') : '';
	$selected = explode('|', $selected);
	
	foreach($options as $opt)
	{
		$opt = trim($opt);
		$tmp = explode('|', $opt);
		$list .= '<option value="' . esc_attr(trim($tmp[1])) . '"' . ( (($def_sel && $opt{0} == '*') || (!$def_sel && in_array($tmp[1], $selected)) ) ? ' selected="selected"' : '' ) . '>' . wp_specialchars(trim($tmp[0], '*')) . '</option>';
	}
	
	return $list;
}


function wpfilebase_admin_table_sort_link($order)
{
	$desc = (!empty($_GET['order']) && $order == $_GET['order'] && empty($_GET['desc']));
	$uri = add_query_arg(array('order' => $order, 'desc' => $desc ? '1' : '0'));
	return $uri;
}

function wpfilebase_protect_upload_path()
{
	$htaccess = wpfilebase_upload_dir() . '/.htaccess';
	@unlink($htaccess);
	if( is_writable(wpfilebase_upload_dir()) && ($fp = @fopen($htaccess, 'w')) )
	{
		@fwrite($fp, "Order deny,allow\n");
		@fwrite($fp, "Deny from all\n");
		@fclose($fp);
		return @chmod($htaccess, octdec(WPFB_PERM_FILE));
	}	
	return false;
}

function wpfilebase_extension_is_allowed($ext)
{
	static $srv_script_exts = array('php', 'php3', 'php4', 'php5', 'phtml', 'cgi', 'pl', 'asp', 'py', 'aspx', 'jsp', 'jhtml', 'jhtm');	
	
	if(wpfilebase_get_opt('allow_srv_script_upload'))
		return true;
	
	$ext = strtolower($ext);	
	$p = strrpos($ext, '.');
	if($p !== false)
		$ext = substr($ext, $p + 1);
	
	return !in_array($ext, $srv_script_exts);
}

function wpfilebase_uninstall()
{
	wpfilebase_inclib('setup');
	wpfilebase_remove_options();
	wpfilebase_drop_tables();
	// TODO: remove user opt
}

function wpfilebase_progress_bar($progress, $label)
{
	$progress = round(100 * $progress);
	echo "<div class='wpfilebase-progress'><div class='progress'><div class='bar' style='width: $progress%'></div></div><div class='label'><strong>$progress %</strong> ($label)</div></div>";
}

function wpfilebase_admin_form($name, $item=null, $exform=false)
{
	include(WPFB_PLUGIN_ROOT . 'lib/wpfb_form_' . $name . '.php');
}

function wpfilebase_mkdir($dir)
{
	$parent = trim(dirname($dir), '.');
	if(trim($parent,'/') != '' && !is_dir($parent)) {
		$result = wpfilebase_mkdir($parent);
		if($result['error'])
			return $result;
	}
	return array('error' => !(@mkdir($dir, octdec(WPFB_PERM_DIR)) && @chmod($dir, octdec(WPFB_PERM_DIR))), 'dir' => $dir, 'parent' => $parent);
}

function wpfilebase_max_upload_size() {
	$val = ini_get('upload_max_filesize');
    if (is_numeric($val))
        return $val;

	$val_len = strlen($val);
	$max_bytes = substr($val, 0, $val_len - 1);
	$unit = strtolower(substr($val, $val_len - 1));
	switch($unit) {
		case 'k':
			$max_bytes *= 1024;
			break;
		case 'm':
			$max_bytes *= 1048576;
			break;
		case 'g':
			$max_bytes *= 1073741824;
			break;
	}
	return $max_bytes;
}

function wpfilebase_parse_tpls() {
	$ptpls = array();
	$tpls = get_option(WPFB_OPT_NAME . '_tpls');
	
	if(!empty($tpls)) {
		wpfilebase_inclib('template');
		foreach($tpls as $tag => $src)
			$ptpls[$tag] = wpfilebase_parse_template($src);
	}	
	update_option(WPFB_OPT_NAME . '_tpls_parsed', $ptpls);
}

function wpfilebase_flush_rewrite_rules()
{
    global $wp_rewrite;
	$browser_post_id = intval(wpfilebase_get_opt('file_browser_post_id'));
	if($browser_post_id <= 0) {
		$redirect = '';
		$regex = '';
	} else {
		$is_page = (get_post_type($browser_post_id) == 'page');
		$redirect = 'index.php?' . ($is_page ? 'page_id' : 'p') . '=' . $browser_post_id . '&wpfb_cat_path=$matches[1]';
		$file_browser_base = trim(substr(get_permalink($browser_post_id), strlen(get_option('home'))), '/');
	}
	wpfilebase_update_opt('file_browser_redirect', $redirect);
	wpfilebase_update_opt('file_browser_base', $file_browser_base);
	
	if(is_object($wp_rewrite))
		$wp_rewrite->flush_rules();
}
?>