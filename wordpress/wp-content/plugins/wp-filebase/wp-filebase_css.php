<?php
require('../../../wp-config.php');
wpfilebase_inclib('common');
wpfilebase_inclib('download');

$custom_file = wpfilebase_upload_dir() .'/_wp-filebase.css';
$default_file = WPFB_PLUGIN_ROOT . 'wp-filebase.css';
$custom = file_exists($custom_file);

wpfilebase_send_file($custom ? $custom_file : $default_file);

?>