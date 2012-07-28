<?php

// copy of wp's copy_dir, but moves everything
function wpfilebase_move_dir($from, $to)
{

	require_once(ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php');
	require_once(ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php');
	
	$wp_filesystem = new WP_Filesystem_Direct(null);
	
	$dirlist = $wp_filesystem->dirlist($from);

	$from = trailingslashit($from);
	$to = trailingslashit($to);

	foreach ( (array) $dirlist as $filename => $fileinfo ) {
		if ( 'f' == $fileinfo['type'] ) {
			if ( ! $wp_filesystem->move($from . $filename, $to . $filename, true) )
				return false;
			$wp_filesystem->chmod($to . $filename, octdec(WPFB_PERM_FILE));
		} elseif ( 'd' == $fileinfo['type'] ) {
			if ( !$wp_filesystem->mkdir($to . $filename, octdec(WPFB_PERM_DIR)) )
				return false;
			if(!wpfilebase_move_dir($from . $filename, $to . $filename))
				return false;
		}
	}
	
	// finally delete the from dir
	@rmdir($from);
	
	return true;
}

?>