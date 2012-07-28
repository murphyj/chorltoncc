<?php

function wpfilebase_add_options()
{
	$default_opts = &wpfilebase_options();		
	$existing_opts = wpfilebase_get_opt();
	$new_opts = array();
	
	foreach($default_opts as $opt_name => $opt_data)
	{
		$new_opts[$opt_name] = $opt_data['default'];
	}		

	$new_opts['widget'] = array(
		'filelist_title' => 'Top Downloads',
		'filelist_order_by' => 'file_hits',
		'filelist_asc' => false,
		'filelist_limit' => 10,
		'filelist_template' => '<a href="%file_post_url%">%file_display_name%</a> (%file_hits%)',
		'filelist_template_parsed' => ''
	);

	
	$new_opts['templates'] = array();
	$new_opts['version'] = WPFB_VERSION;
	
	
	if($existing_opts === false)
		add_option(WPFB_OPT_NAME, $new_opts);
	else {
		$changed = false;
		foreach($new_opts as $opt_name => $opt_data)
		{
			// check if this option already exists, and if changed, take the existing value
			if($opt_name != 'version' && isset($existing_opts[$opt_name]) && $existing_opts[$opt_name] != $opt_data)
			{
				$new_opts[$opt_name] = $existing_opts[$opt_name];
				$changed = true;				
			}
		}
		if($changed)
			update_option(WPFB_OPT_NAME, $new_opts);
	}
	
	$default_tpls = array(
		'image_320' => '[caption id="file_%file_id%" align="alignnone" width="320" caption="<!-- IF %file_description% -->%file_description%<!-- ELSE -->%file_display_name%<!-- ENDIF -->"]<img class="size-full" title="%file_display_name%" src="%file_url%" alt="%file_display_name%" width="320" />[/caption]'."\n\n",
		'thumbnail' => '<div class="wpfilebase-fileicon"><a href="%file_url%" title="Download %file_display_name%"><img align="middle" src="%file_icon_url%" /></a></div>'."\n",
		'simple'	=> '<p><img align="absmiddle" src="%file_icon_url%" height="20" /> <a href="%file_url%" title="Download %file_display_name%">%file_display_name%</a> (%file_size%)</p>'
	);
	
	$tpls = get_option(WPFB_OPT_NAME . '_tpls');
	if(empty($tpls) || !is_array($tpls) || count($tpls) == 0) {
		$tpls = $default_tpls;
		update_option(WPFB_OPT_NAME . '_tpls', $tpls);
	}
}


function wpfilebase_remove_options()
{		
	delete_option(WPFB_OPT_NAME);
	
	// delete old options too
	$options = &wpfilebase_options();
	foreach($options as $opt_name => $opt_data)
		delete_option(WPFB_OPT_NAME . '_' . $opt_name);
}

function wpfilebase_reset_options()
{
	// keep stats
	$traffic = wpfilebase_get_opt('traffic_stats');
	wpfilebase_remove_options();
	wpfilebase_add_options();
	wpfilebase_update_opt('traffic_stats', $traffic);
	wpfilebase_reset_tpls();
}

function wpfilebase_setup_tables()
{
	global $wpdb;

	$queries = array();
	$tbl_cats = $wpdb->prefix . 'wpfb_cats';
	$tbl_files = $wpdb->prefix . 'wpfb_files';
	
	$queries[] = "CREATE TABLE IF NOT EXISTS `$tbl_cats` (
  `cat_id` int(8) unsigned NOT NULL auto_increment,
  `cat_name` varchar(255) NOT NULL default '',
  `cat_description` text,
  `cat_folder` varchar(255) NOT NULL,
  `cat_parent` int(8) unsigned NOT NULL default '0',
  `cat_files` bigint(20) unsigned NOT NULL default '0',
  `cat_required_level` tinyint(2) NOT NULL default '0',
  `cat_icon` varchar(255) default NULL,
  PRIMARY KEY  (`cat_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";
				
	
	$queries[] = "CREATE TABLE IF NOT EXISTS `$tbl_files` (
  `file_id` bigint(20) unsigned NOT NULL auto_increment,
  `file_name` varchar(255) NOT NULL default '',
  `file_size` bigint(20) unsigned NOT NULL default '0',
  `file_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `file_hash` char(32) NOT NULL,
  `file_thumbnail` varchar(255) default NULL,
  `file_display_name` varchar(255) NOT NULL default '',
  `file_description` text,
  `file_requirement` varchar(255) default NULL,
  `file_version` varchar(64) default NULL,
  `file_author` varchar(255) default NULL,
  `file_language` varchar(255) default NULL,
  `file_platform` varchar(255) default NULL,
  `file_license` varchar(255) NOT NULL default '',
  `file_required_level` tinyint(2) unsigned default NULL,
  `file_offline` enum('0','1') NOT NULL default '0',
  `file_direct_linking` enum('0','1') NOT NULL default '0',
  `file_category` int(8) unsigned NOT NULL default '0',
  `file_update_of` bigint(20) unsigned default NULL,
  `file_post_id` bigint(20) unsigned default NULL,
  `file_added_by` bigint(20) unsigned default NULL,
  `file_hits` bigint(20) unsigned NOT NULL default '0',
  `file_ratings` bigint(20) unsigned NOT NULL default '0',
  `file_rating_sum` bigint(20) unsigned NOT NULL default '0',
  `file_last_dl_ip` varchar(100) NOT NULL default '',
  `file_last_dl_time` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`file_id`),
  FULLTEXT KEY `FULLTEXT` (`file_description`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";


	$queries[] = "ALTER TABLE `$tbl_cats` DROP INDEX `FULLTEXT`";
	$queries[] = "ALTER TABLE `$tbl_cats` DROP INDEX `CAT_NAME`";
	$queries[] = "ALTER TABLE `$tbl_cats` DROP INDEX `CAT_FOLDER`";
	$queries[] = "ALTER TABLE `$tbl_cats` ADD UNIQUE `UNIQUE_FOLDER` ( `cat_folder` , `cat_parent` ) ";
	
	$queries[] = "ALTER TABLE `$tbl_files` ADD UNIQUE `UNIQUE_FILE` ( `file_name` , `file_category` )";
	
	// <= v0.1.2.2
	$queries[] = "ALTER TABLE `$tbl_cats` ADD `cat_icon` VARCHAR(255) NULL DEFAULT NULL";
	
	$queries[] = "OPTIMIZE TABLE `$tbl_cats`";
	$queries[] = "OPTIMIZE TABLE `$tbl_files`";

	foreach($queries as $sql)
		$wpdb->query($sql);
}

function wpfilebase_drop_tables()
{
	global $wpdb;
	
	$tables = array($wpdb->wpfilebase_files, $wpdb->wpfilebase_cats);	
	
	foreach($tables as $tbl)
		$wpdb->query("DROP TABLE IF EXISTS `" . $tbl . "`");
}

function wpfilebase_reset_tpls()
{
	wpfilebase_update_opt('template_file_parsed', '');	
	$widget = wpfilebase_get_opt('widget');	
	$widget['filelist_template_parsed'] = '';	
	wpfilebase_update_opt('widget', $widget);
	update_option(WPFB_OPT_NAME . '_tpls_parsed', '');
}
?>