<?php

function wpfilebase_admin_init() {
	wp_register_style('wpfb-admin', WPFB_PLUGIN_URI.'wp-filebase_admin.css', array(), WPFB_VERSION, 'all' );
	wp_register_script('wpfb-admin', WPFB_PLUGIN_URI.'wp-filebase_admin.js', array('jquery'), WPFB_VERSION);
	
	if(!empty($_GET['page']) && $_GET['page'] == 'wpfilebase') {
		wp_enqueue_script('jquery-ui-tabs');
		wp_enqueue_script('jquery-cookie');
		wp_enqueue_script('wpfb-admin');	
		wp_enqueue_script('wpfb');
		wp_enqueue_style('wpfb');		
		wp_enqueue_style ('wpfb-admin');
	}
		
	wpfilebase_version_update_check();
}
add_action('admin_init', 'wpfilebase_admin_init', 10);


function wpfilebase_admin_menu() {	
	add_options_page(WPFB_PLUGIN_NAME, WPFB_PLUGIN_NAME, 'manage_options', 'wpfilebase', '_wpfilebase_admin_options' );	
	add_management_page(WPFB_PLUGIN_NAME, WPFB_PLUGIN_NAME, 'manage_categories', 'wpfilebase', '_wpfilebase_admin_manage' );
}
add_action('admin_menu', 'wpfilebase_admin_menu');

function wpfilebase_mce_plugins($plugins) {
	$plugins['wpfilebase'] = WPFB_PLUGIN_URI . '/tinymce/editor_plugin.js';
	return $plugins;
}

function wpfilebase_mce_buttons($buttons) {
	array_push($buttons, 'separator', 'wpfbInsertTag');
	return $buttons;
}

function _wpfilebase_admin_options()
{
	wpfilebase_inclib('admin_gui_options');
	wpfilebase_admin_options();
}

function _wpfilebase_admin_manage()
{
	wpfilebase_inclib('admin_gui_manage');
	wpfilebase_admin_manage();
}

function _wpfilebase_widget_filelist_control()
{
	wpfilebase_load_lang();
	wpfilebase_inclib('widget');
	wpfilebase_widget_filelist_control();
}
function _wpfilebase_widget_catlist_control()
{
	wpfilebase_load_lang();
	wpfilebase_inclib('widget');
	wpfilebase_widget_catlist_control();
}
wp_register_widget_control(WPFB_PLUGIN_NAME, WPFB_PLUGIN_NAME .' '. __('File list'), '_wpfilebase_widget_filelist_control', array('description' => __('Lists the latest or most popular files', WPFB)));
wp_register_widget_control(WPFB_PLUGIN_NAME.'_cats', WPFB_PLUGIN_NAME.' ' . __('Category list'), '_wpfilebase_widget_catlist_control', array('description' => __('Simple listing of file categories', WPFB)));

function wpfilebase_version_update_check()
{
	$ver = wpfilebase_get_opt('version');
	if($ver != WPFB_VERSION) {
		wpfilebase_activate();
		echo '<!-- WPFilebase: version changed -->';
	}
}

?>