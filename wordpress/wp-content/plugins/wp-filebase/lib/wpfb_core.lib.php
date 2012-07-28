<?php

function wpfilebase_init() {
	wpfilebase_load_lang();
	
	wp_register_style('wpfb', WPFB_PLUGIN_URI.'wp-filebase_css.php', array(), WPFB_VERSION, 'all' );
	wp_register_script('wpfb', WPFB_PLUGIN_URI.'wp-filebase.js', array('jquery'), WPFB_VERSION);
	
	wp_enqueue_style('wpfb');
	// widgets
	wp_register_sidebar_widget(WPFB_PLUGIN_NAME, WPFB_PLUGIN_NAME .' '. __('File list', WPFB), '_wpfilebase_widget_filelist', array('description' => __('Lists the latest or most popular files', WPFB)));
	wp_register_sidebar_widget(WPFB_PLUGIN_NAME.'_cats', WPFB_PLUGIN_NAME.' ' . __('Category list', WPFB), '_wpfilebase_widget_catlist', array('description' => __('Simple listing of file categories', WPFB)));

	// for admin
	if (current_user_can('edit_posts') || current_user_can('edit_pages'))
		wpfilebase_mce_addbuttons();
}
add_action('init', 'wpfilebase_init');

function wpfilebase_load_lang() {
	static $loaded = false;
	if(!$loaded) {
		$lang_dir = basename(WPFB_PLUGIN_ROOT).'/languages';
		load_plugin_textdomain(WPFB, 'wp-content/plugins/'.$lang_dir, $lang_dir);
		$loaded = true;
	}
}

function wpfilebase_get_opt($name = null)
{
	$options = get_option(WPFB_OPT_NAME);		
	if(empty($name))
		return $options;
	else
		return isset($options[$name]) ? $options[$name] : null;
}

function _wpfilebase_widget_filelist($args) {
	wpfilebase_inclib('widget');
	return wpfilebase_widget_filelist($args);
}
function _wpfilebase_widget_catlist($args) {
	wpfilebase_inclib('widget');
	return wpfilebase_widget_catlist($args);
}

add_action('template_redirect',	'wpfilebase_redirect');
function wpfilebase_redirect()
{
	global $wpdb;
	$file = null;

	if(!empty($_GET['wpfb_dl'])) {
		require_once(WPFB_PLUGIN_ROOT . 'wp-filebase_item.php');
		$file = $file = WPFilebaseFile::get_file((int)$_GET['wpfb_dl']);
	} else {
		$dl_url = parse_url(get_option('home') . '/' . wpfilebase_get_opt('download_base') . '/');
		$dl_url_path = $dl_url['path'];
		$pos = strpos($_SERVER['REQUEST_URI'], $dl_url_path);
		if($pos !== false && $pos == 0) {
			$filepath = trim(urldecode(substr($_SERVER['REQUEST_URI'], strlen($dl_url_path))), '/');
			if(!empty($filepath)) {
				require_once(WPFB_PLUGIN_ROOT . 'wp-filebase_item.php');
				$file = WPFilebaseFile::get_file_by_path($filepath);
			}
		} else {	
			// no download, set site visited coockie to disable referer check
			if(empty($_COOKIE[WPFB_OPT_NAME])) {
				@setcookie(WPFB_OPT_NAME, '1');
				$_COOKIE[WPFB_OPT_NAME] = '1';
			}
			return;
		}
	}
	
	if(!empty($file) && is_object($file)) {
		wpfilebase_inclib('common');	
		$file->download();
		exit;
	}
}

add_filter('ext2type',		'wpfilebase_ext2type_filter');
function wpfilebase_ext2type_filter($arr) {
	$arr['interactive'][] = 'exe';
	$arr['interactive'][] = 'msi';
	return $arr;
}


/*
// conditionally loading
add_filter('the_posts', 'wpfilebase_posts_filter');
function wpfilebase_posts_filter($posts) {
	global $id, $wpfb_loaded_output;
	print_r($posts);
	if(!empty($wpfb_loaded_output) || empty($posts))
		return $posts;
	$fb_id = wpfilebase_get_opt('file_browser_post_id');
	if($id > 0 && $id == $fb_id) {
		wpfilebase_load_output_scripts();
	} else {		
		foreach($posts as $post) {
		if(strpos($post->post_content, '[filebase') !== false || $post->id == $fb_id) {
				wpfilebase_load_output_scripts();
				break;
			}
		}
	}
	return $posts;
} */


add_filter('the_content',	'wpfilebase_content_filter', 10); // must be lower than 11 (before do_shortcode) and after wpautop (>9)
add_filter('the_excerpt',	'wpfilebase_content_filter', 10);
add_filter('the_content_rss',	'wpfilebase_content_filter', 10);
add_filter('the_excerpt_rss ',	'wpfilebase_content_filter', 10);
function wpfilebase_content_filter($content)
{
	global $id;
	
	if(!wpfilebase_get_opt('parse_tags_rss') && is_feed())
		return $content;	
		
	// all tags start with '[filebase'
	if(strpos($content, '[filebase') !== false)
	{
		wpfilebase_inclib('output');
		wpfilebase_parse_content_tags($content);
	}	
	
	if(!empty($id) && $id > 0 && (is_single() || is_page()))
	{
		if($id == wpfilebase_get_opt('file_browser_post_id'))
		{
			wpfilebase_inclib('output');
			wpfilebase_file_browser($content);
		}
	
		if(wpfilebase_get_opt('auto_attach_files'))
		{
			wpfilebase_inclib('output');
			wpfilebase_get_post_attachments($content, true);
		}
	}

    return $content;
}

add_action('wp_footer', 'wpfilebase_footer');
function wpfilebase_footer() {
	global $wpfb_load_js;
	
	if($wpfb_load_js) {
		wp_print_scripts('wpfb');
	}
}

add_action('generate_rewrite_rules', 'wpfilebase_add_rewrite_rules');
function wpfilebase_add_rewrite_rules($wp_rewrite) {	
	$browser_base = wpfilebase_get_opt('file_browser_base');
	$redirect = wpfilebase_get_opt('file_browser_redirect');
	if(empty($browser_base) || empty($redirect))
		return;
    $new_rules = array('^' . $browser_base . '/(.+)$' => $redirect);
    $wp_rewrite->rules = $new_rules + $wp_rewrite->rules;
}

add_filter('query_vars', 'wpfilebase_queryvars' );
function wpfilebase_queryvars($qvars){
	$qvars[] = 'wpfb_cat_path';
	$qvars[] = 'wpfb_cat';
	$qvars[] = 'wpfb_dl';
    return $qvars;
}

function wpfilebase_mce_addbuttons() {
	wpfilebase_inclib('admin_lite');
	add_filter('mce_external_plugins', 'wpfilebase_mce_plugins');
	add_filter('mce_buttons', 'wpfilebase_mce_buttons');
}


if(is_admin()) {wpfilebase_inclib('admin_lite');}

?>