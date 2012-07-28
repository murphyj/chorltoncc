<?php

require('../../../wp-config.php');

wpfilebase_inclib('common');
require_once(WPFB_PLUGIN_ROOT . 'wp-filebase_item.php');

$item = null;

if(isset($_GET['fid']))
	$item = WPFilebaseFile::get_file(intval($_GET['fid']));
elseif(isset($_GET['cid']))
	$item = WPFilebaseCategory::get_category(intval($_GET['cid']));
	
if($item == null || !$item->current_user_can_access(true))
	exit;
	
// if no thumbnail, redirect
if(empty($item->file_thumbnail) && empty($item->cat_icon))
{
	header('Location: ' . $item->get_icon_url());
	exit;
}

// send thumbnail
wpfilebase_inclib('download');
wpfilebase_send_file($item->get_thumbnail_path());

?>