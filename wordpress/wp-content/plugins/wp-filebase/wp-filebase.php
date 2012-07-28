<?php
/*
Plugin Name: WP-Filebase
Plugin URI: http://fabi.me/wordpress-plugins/wp-filebase-file-download-manager/
Description: A powerful download manager supporting file categories, thumbnails, traffic/bit rate limits and more.
Author: Fabian Schlieper
Version: 0.1.3.4
Author URI: http://fabi.me/
*/

define('WPFB_VERSION', '0.1.3.4');

// db settings
if(isset($wpdb))
{
	$wpdb->wpfilebase_cats = $wpdb->prefix . 'wpfb_cats';
	$wpdb->wpfilebase_files = $wpdb->prefix . 'wpfb_files';
}

define('WPFB_PLUGIN_ROOT', str_replace('\\', '/', dirname(__FILE__)) . '/');
define('WPFB_PLUGIN_URI', str_replace(str_replace('\\', '/', ABSPATH), get_option('siteurl').'/', WPFB_PLUGIN_ROOT));
define('WPFB_OPT_NAME', 'wpfilebase');
define('WPFB_PLUGIN_NAME', 'WP-Filebase');
define('WPFB', 'wpfb');

define('WPFB_PERM_FILE', 666);
define('WPFB_PERM_DIR', 777);

if(!function_exists('wpfilebase_inclib')) {
	function wpfilebase_inclib($lib) {
		$res = @include_once(WPFB_PLUGIN_ROOT . 'lib/wpfb_'.$lib.'.lib.php');
		if($res === false) {
			echo("<p>WP-Filebase Error: Could not include library <b>'$lib'</b>!</p>");
			return false;
		}
		return true;
	}
}

wpfilebase_inclib('core');

if(!function_exists('wpfilebase_activate')) {
	function wpfilebase_activate() {
		wpfilebase_inclib('admin');
		wpfilebase_inclib('setup');
		
		wpfilebase_add_options();
		wpfilebase_setup_tables();
		wpfilebase_protect_upload_path();
		wpfilebase_reset_tpls();
		wpfilebase_flush_rewrite_rules();
	}
}
register_activation_hook(__FILE__, 'wpfilebase_activate');

/*
todo: 3 listtypen: flach, baum, ajax baum
archive lister
per-page download list
folder struktur (categorien, unterkategorien)

* upload by guests
* upload progress bar
* searchboxes
*/
?>