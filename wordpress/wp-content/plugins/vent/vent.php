<?php
/*
Plugin Name: Vent
Plugin URI: http://blog.clearskys.net/plugins/vent-events-system/
Description: The Vent plugin allows you to use the WordPress scheduled posts functionality to list and manage events
Author: Barry at clearskys.net
Version: 0.8
Author URI: http://blog.clearskys.net/
*/
/*  Copyright 2008 clearskys.net Ltd  (email : team@clearskys.net)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License version 2 as published by
    the Free Software Foundation .

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

// Core vent functions and variables
class vent_core {
	var $build = 2;
	var $op = array();
	var $site_uri = '';
	var $mylocation = '';
	var $base_uri = '';
	
	// For next event caching
	var $nextevent;
	var $onmu;
	
	// Core variables initialisation function
	function vent_core() {
		$this->site_uri = get_settings('siteurl');
		
		$directories = explode(DIRECTORY_SEPARATOR,dirname(__FILE__));
		$mydir = $directories[count($directories)-1];
		
		if('mu-plugins' == $mydir) {
			// We are running in mu_plugins 
			$this->base_uri = $this->site_uri . '/wp-content/' . $mydir . '/';
			$this->mylocation = basename(__FILE__); 
			$this->onmu = true;
		} else {
			// We are running in the standard plugins directory
			$this->base_uri = $this->site_uri . '/wp-content/plugins/' . $mydir . '/';
			$this->mylocation = $mydir . DIRECTORY_SEPARATOR . basename(__FILE__); 
			$this->onmu = false;
		}
		
		$this->op = $this->getpluginoptions();
	}
	
	// Global Plugin options function
	function getpluginoptions() {
		// Set up the plugins options
		if(empty($this->op)) {
			// Cache the options to reduce database reads
			$this->op = get_option('vent_config');
		}
		
		if(empty($this->op)) {
			// Set up default configuration values
			
			$this->op['build'] = $this->build;
			
			$this->op['ventenabled'] = 0;
			
			$this->op['identity_category'] = 0;
			$this->op['identity_tag'] = 0;
			$this->op['identity_subject'] = '';
			
			$this->op['highlight_next_event'] = 0;
			$this->op['highlight_next_event_using'] = 0;
			
			$this->op['make_pastevent_post'] = 0;
			
			$this->op['show_hcalender_onpost'] = 0;
			$this->op['hcalender_process_link'] = '';
			
			$this->op['highlight_next_event_style'] = "#post-%postid% {\n position: relative;\nmargin: 20px 0 10px; \n}\ndiv.nextevent {\n position: absolute;\n top:  -2px;\n right: -2px;\n background: transparent url(" . $this->base_uri . "ventincludes/images/nextevent.png) no-repeat;\n width: 89px;\n height: 89px;\n }\n";
			
			// Rewrite rules
			$this->op['eventrule_base'] = 'events';
			$this->op['eventrule_today'] = 'today';
			$this->op['eventrule_tomorrow'] = 'tomorrow';
			$this->op['eventrule_thisweek'] = 'thisweek';
			$this->op['eventrule_nextweek'] = 'nextweek';
			$this->op['eventrule_thismonth'] = 'thismonth';
			$this->op['eventrule_nextmonth'] = 'nextmonth';
			$this->op['eventrule_thisyear'] = 'thisyear';
			$this->op['eventrule_all'] = 'all';
			
			$this->op['options'] = array();
			foreach($this->op as $key => $value) {
				$this->op['options'][] = $key;
			}
			
			update_option("vent_config",$this->op);
			
		} elseif( $this->op['build'] < $this->build) {
			// The build is updated so add any extra bits
			
			$this->op['build'] = $this->build;
			
			if(empty($this->op['ventenabled'])) $this->op['ventenabled'] = 0;
			
			if(empty($this->op['identity_category'])) $this->op['identity_category'] = 0;
			if(empty($this->op['identity_tag'])) $this->op['identity_tag'] = 0;
			if(empty($this->op['identity_subject'])) $this->op['identity_subject'] = '';
			
			if(empty($this->op['highlight_next_event'])) $this->op['highlight_next_event'] = 0;
			if(empty($this->op['highlight_next_event_using'])) $this->op['highlight_next_event_using'] = 0;
			
			if(empty($this->op['make_pastevent_post'])) $this->op['make_pastevent_post'] = 0;
			
			if(empty($this->op['show_hcalender_onpost'])) $this->op['show_hcalender_onpost'] = 0;
			
			if(empty($this->op['hcalender_process_link'])) $this->op['hcalender_process_link'] = '';
			if(empty($this->op['highlight_next_event_style'])) $this->op['highlight_next_event_style'] = "#post-%postid% {\n position: relative;\nmargin: 20px 0 10px; \n}\ndiv.nextevent {\n position: absolute;\n top:  -2px;\n right: -2px;\n background: transparent url(" . $this->base_uri . "ventincludes/images/nextevent.png) no-repeat;\n width: 89px;\n height: 89px;\n }\n";
			
			// Rewrite rules
			if(empty($this->op['eventrule_base'])) $this->op['eventrule_base'] = 'events';
			if(empty($this->op['eventrule_today'])) $this->op['eventrule_today'] = 'today';
			if(empty($this->op['eventrule_tomorrow'])) $this->op['eventrule_tomorrow'] = 'tomorrow';
			if(empty($this->op['eventrule_thisweek'])) $this->op['eventrule_thisweek'] = 'thisweek';
			if(empty($this->op['eventrule_nextweek'])) $this->op['eventrule_nextweek'] = 'nextweek';
			if(empty($this->op['eventrule_thismonth'])) $this->op['eventrule_thismonth'] = 'thismonth';
			if(empty($this->op['eventrule_nextmonth'])) $this->op['eventrule_nextmonth'] = 'nextmonth';
			if(empty($this->op['eventrule_thisyear'])) $this->op['eventrule_thisyear'] = 'thisyear';
			if(empty($this->op['eventrule_all'])) $this->op['eventrule_all'] = 'all';
			
			$this->op['options'] = array();
			foreach($this->op as $key => $value) {
				$this->op['options'][] = $key;
			}
			
			update_option("vent_config",$this->op);
		}
		
		return $this->op;
		
	}
	
	function getnextevent() {
		// This function gets the details of the next event
		// It is in the core because it is used by the widgets and public classes.
		global $wpdb;
		
		if(!empty($this->nextevent)) {
			// The next event has already been retrieved so just return true
			return true;
		}
		
		$sql = "SELECT * FROM " . $wpdb->posts . " WHERE ";
		$sql .= "post_date > NOW() AND post_status = 'event' ";
		$sql .= "ORDER BY post_date ASC ";
		$sql .= "LIMIT 0, 1";
		
		$results = $wpdb->get_row($sql, OBJECT);
		
		if(empty($results)) {
			return false;
		} else {
			$this->nextevent = $results;
			return true;
		}
	}
	
	function getenddate($postid) {
		global $wpdb;
		
		$sql = "SELECT meta_value FROM " . $wpdb->postmeta . " WHERE ";
		$sql .= "post_id = " . $postid . " AND meta_key = 'event_enddate'";
		
		$result = $wpdb->get_var($sql);
		if(empty($result)) {
			return false;
		} else {
			return $result;
		}
	}
	
}

// vent functions for the administration interface
class vent_admin extends vent_core {
	
	function __construct() {
		
		// Initialise the core
		$this->vent_core();
		
		register_activation_hook(__FILE__, array(&$this, 'vent_install'));
		// Administrative hooks and filters
		add_action('init', array(&$this, 'initialise_wp'));
		add_action('init', array(&$this, 'handle_ajax'));
		
		add_action('admin_head', array(&$this,'add_admin_header'));
		add_action('admin_menu', array(&$this,"add_admin_pages"), 1);
		
		if(!empty($this->op['ventenabled']) && $this->op['ventenabled'] != 0) {
			// Only add the filters and actions is vent is actuall enabled
		
			add_action('save_post', array(&$this, 'post_handle_save'), 9 , 2);
			add_filter('post_stati',array(&$this,'admin_add_post_stati'));
			add_filter('posts_where', array(&$this,'admin_filter_where') );
			add_filter('the_posts', array(&$this,'admin_change_status'));
		}
		
	}
	
	function __destruct() {
		return true;
	}
	
	function vent_admin() {
		$this->__construct();
	}
	
	function vent_install() {
		global $wpdb;
		// Setup the things we want at install time such as the scheduler
		// Remove any already scheduled hooks
		wp_clear_scheduled_hook('vent_scheduled');
		// Add the scheduled event again
		wp_schedule_event(time(), 'hourly', 'vent_scheduled');
		
		// Make sure that the posts table has a varchar setting for posts status
		$sql = "SHOW COLUMNS FROM " . $wpdb->posts . " WHERE Field = 'post_status'";
		$coltest = $wpdb->get_results($sql, OBJECT);
		
		if(empty($coltest) || $coltest->Type != 'varchar(20)') {
			$sql = "ALTER TABLE " . $wpdb->posts . " CHANGE post_status post_status varchar(20) NOT NULL DEFAULT 'publish' ;";
			
			$result = $wpdb->query($sql);
		}
	}
	
	/* Administration functions */
	
	function initialise_wp() {
		// Enqueues our javascript if we are on the right page
		
		if(addslashes($_GET['page']) == $this->mylocation) {
			wp_enqueue_script('ventjs',$this->base_uri . 'ventincludes/js/ventadmin.js',array('jquery'));
		}
		
	}
	
	function handle_ajax() {
		
	}
	
	function admin_add_post_stati($stati) {
		// This function adds the "Event" type to the Post stati (status) so the extra link shows
		// up if there are posts with a post_status of 'event'
		
		$stati['event'] = array(__('Events','vent'),
								__('Manage Events','vent'),
								array(	__('Events (%s)','vent'),
										__('Events (%s)','vent')
										)
								); 
	
		return $stati;
	}
	
	function admin_filter_where($where) {
		// This function changes the Where clause in the manage posts administration panel
		// to list posts with a post_status of 'event' when we are viewing the events list.
	
		global $wpdb;
		
		if(function_exists('is_admin') && is_admin()) {
			if(addslashes($_GET['post_status']) == 'event') {
				$where .= " AND (" . $wpdb->posts . ".post_status = 'event')";
			}
		}
		return $where;
	}
	
	function admin_change_status($posts) {
		// This function changes the status of each post in the list from 'event' to 'published' so that
		// the correct status shows on the list (that of published), rather than just leave an empty
		// space in the table.
		
		if(!empty($posts)) {
			foreach($posts as $key=>$post) {
				if('event' == $post->post_status) {
					// Switch the status to show published
					$post->post_status = 'publish';
					// Add it back to the results
					$posts[$key] = $post;
				}
			}
		}
		
		return $posts;
	}
	
	function add_admin_header() {
		// This function includes our css if we are on the right page.
		
		if(addslashes($_GET['page']) == $this->mylocation) {
			echo "<link rel='stylesheet' href='" . $this->base_uri . "ventincludes/css/ventadmin.css?version=" . $this->build . "' type='text/css' />";
		}
		
	}
	
	function add_admin_pages() {
		// If the current user has the privs to change options then add our options panel.
		
		if (current_user_can('manage_options') ) {
			add_options_page('Vent options', 'Vent', 8,  $this->mylocation, array(&$this,'handle_options_panel'));
		}
	}
	
	function post_is_event($post) {
		// This function goes through the criteria and checks to see if the post is an event
		// If it is it returns true otherwise it returns false.
		global $wpdb;
		
		$subject = false;
		if(!empty($this->op['identity_subject'])) {
			if(strpos($post->post_title,$this->op['identity_subject']) === 0) {
				$subject = true;
			} else {
				$subject = false;
			}
		} else {
			// Subject check string is empty so the post passes by default
			$subject = true;
		}
		
		$category = false;
		if(!empty($this->op['identity_category']) && $this->op['identity_category'] != 0) {
			// Category is set so do a count of categories
			$sql = "SELECT COUNT(*) as categorynum FROM " . $wpdb->term_relationships . " AS tr ";
			$sql .= "INNER JOIN " . $wpdb->term_taxonomy . " AS tt ON tr.term_taxonomy_id = tt.term_taxonomy_id ";
			$sql .= "WHERE tt.term_id = " . mysql_real_escape_string($this->op['identity_category']) . " AND tt.taxonomy = 'category' ";
			$sql .= "AND tr.object_id = " . $post->ID;
			$categorynum = $wpdb->get_var($sql);
			
			if(!empty($categorynum) && $categorynum > 0 ) {
				$category = true;
			}
			
		} else {
			// Setting is any category so passes by default
			$category = true;
		}
		
		$tag = false;
		if(!empty($this->op['identity_tag']) && $this->op['identity_tag'] != 0) {
			// Tag is set so do a count of tags
			$sql = "SELECT COUNT(*) as tagnum FROM " . $wpdb->term_relationships . " AS tr ";
			$sql .= "INNER JOIN " . $wpdb->term_taxonomy . " AS tt ON tr.term_taxonomy_id = tt.term_taxonomy_id ";
			$sql .= "WHERE tt.term_id = " . mysql_real_escape_string($this->op['identity_tag']) . " AND tt.taxonomy = 'post_tag' ";
			$sql .= "AND tr.object_id = " . $post->ID;
			$tagnum = $wpdb->get_var($sql);
			
			if(!empty($tagnum) && $tagnum > 0 ) {
				$tag = true;
			}
		} else {
			// Setting is any tag so passes by default
			$tag = true;
		}
		
		if($subject && $category && $tag) {
			return true;
		} else {
			return false;
		}
	}
	
	function post_handle_save($post_id, $post) {
		global $wpdb;
		
		if('future' == $post->post_status || 'publish' == $post->post_status || 'event' == $post->post_status) {
			// If the post has a status of future (scheduled), publish (current) or event (ours)
			if($this->post_is_event($post)) {
				// Check if the post is an event
				$sql = "UPDATE " . $wpdb->posts . " SET post_status = 'event' ";
				$sql .= "WHERE id = " . $post_id;
				$result =  $wpdb->query($sql);
			}
						
		}
		
	}
	
	function post_is_event_sql($justcount = false, $justfuture = false) {
		global $wpdb;
		
		$sql ="";
		$join = "";
		$where = "";
		
		if($this->op['identity_category'] != 0) {
			// A category is required
			$join .= " INNER JOIN " . $wpdb->term_relationships . " AS trc ON " . $wpdb->posts . ".ID = trc.object_id INNER JOIN " . $wpdb->term_taxonomy . " AS ttc ON trc.term_taxonomy_id = ttc.term_taxonomy_id ";
			$where = "( ttc.term_id = '" . mysql_real_escape_string($this->op['identity_category']) . "' AND ttc.taxonomy = 'category' )";
		}
		if($this->op['identity_tag'] != 0) {
			// A tag is required
			$join .= " INNER JOIN " . $wpdb->term_relationships . " AS trt ON " . $wpdb->posts . ".ID = trt.object_id INNER JOIN " . $wpdb->term_taxonomy . " AS ttt ON trt.term_taxonomy_id = ttt.term_taxonomy_id ";
			if(!empty($where)) {
				$where .= " AND ";
			}
			$where .= "( ttt.term_id = '" . mysql_real_escape_string($this->op['identity_tag']) . "' AND ttt.taxonomy = 'post_tag' )";
		}
		
		if(!empty($this->op['identity_subject'])) {
			if(!empty($where)) {
				$where .= " AND ";
			}
			$where .= "(" . $wpdb->posts . ".post_title LIKE '" . mysql_real_escape_string($this->op['identity_subject']) . "%')";
		}
		
		$sql = "SELECT * FROM " . $wpdb->posts;
		
		return $sql . $join . " WHERE " . $where;
		
	}
	
	function event_count() {
		global $wpdb;
		
		$sql = "SELECT count(*) as events FROM " . $wpdb->posts . " ";
		$sql .= "WHERE post_status = 'event'";
		
		$result = $wpdb->get_var($sql);
		return $result;
	}
	
	function resetevents() {
		// A quick sql statement to return any events to either future or published status
		
		global $wpdb;
		
		$sql = "UPDATE " . $wpdb->posts . " SET post_status = 'future' ";
		$sql .= "WHERE post_status = 'event' AND post_date > NOW(); ";
		$results = $wpdb->query($sql);
		$sql = "UPDATE " . $wpdb->posts . " SET post_status = 'publish' ";
		$sql .= "WHERE post_status = 'event' AND post_date <= NOW(); ";
		$results = $wpdb->query($sql);
		
		
		
	}
		
	function handle_options_panel() {
		// This function shows and handles the upadtes to our options panel
		
		global $wpdb;
		
		$op = $this->getpluginoptions();
		
		if($wpdb->escape($_POST['_action']) == "update") {
			check_admin_referer('vent_options');
			
			if(isset($_POST['resetpostsbutton'])) {
				// Resest all the current events
				$this->resetevents();
				
				echo "<div id='message' class='updated fade'>";
				echo "<p><strong>" . __("All existing events have been set as posts.","vent") . "</strong></p>";
				echo "</div>";
			} else {
				// Standard update of options
				foreach($op['options'] as $value) {
					$option = $wpdb->escape($_POST[$value]);

					if(isset($option)) {
						$op[$value] = $option;
					}
				}
				
				if($op['ventenabled'] != 0 && !isset($op['runinstall'])) {
					// Make sure the install is run when the system is enabled
					// Sometimes MU doesn't run it properly.
					$this->vent_install();
					$op['runinstall'] = true;
				}

				update_option("vent_config",$op);

				echo "<div id='message' class='updated fade'>";
				echo "<p><strong>" . __("Settings saved.",'vent') . "</strong></p>";
				echo "</div>";
			}
			
			
		}
		//$this->post_is_event_sql();
		echo "<div class='wrap'>\n";
		echo "<h2>" . __('Vent settings','vent') . "</h2>\n";
		
		echo "<form action='' method='post'>\n";
		
		if ( function_exists('wp_nonce_field') )
			wp_nonce_field('vent_options');
		
		echo "<table class='form-table'>\n";
		echo "<tbody>\n";
		
		echo "<tr valign='top'>";
		echo "<th scope='row' colspan='2'>";
		echo __("Vent system status",'vent');
		echo "</th>";
		echo "<td>";
		_e('The Vent system is currently','vent');
		echo " <select name='ventenabled'>";
		echo "<option value='0'";
		if(0 == $op['ventenabled']) echo " selected='selected'";
		echo ">";
		echo __('Disabled','vent');
		echo "</option>";
		echo "<option value='1'";
		if(1 == $op['ventenabled']) echo " selected='selected'";
		echo ">";
		echo __('Enabled','vent');
		echo "</option>";
		echo "</select>";
		echo "</td>";
		echo "</tr>\n";
		
		echo "<tr valign='top'>";
		echo "<th scope='row' colspan='2'>";
		echo __("Event identifier",'vent');
		echo "</th>";
		echo "<td>";
		
		_e('A post is considered an <strong>event</strong>:','vent');
		
		echo "<ol>";
		echo "<li><span>";
		_e('if it is in the','vent');
		echo " <select name='identity_category'>";
		$cats = (array) get_categories('get=all');
		echo "<option value='0'";
		if(0 == $op['identity_category']) echo " selected='selected'";
		echo ">";
		echo __('Any','vent');
		echo "</option>";
		if(!empty($cats)) {
			foreach($cats as $key=>$cat) {
				echo "<option value='". $cat->term_id . "'";
				if($cat->term_id == $op['identity_category']) echo " selected='selected'";
				echo ">";
				echo $cat->cat_name;
				echo "</option>";
			}
		}
		echo "</select> ";
		_e('category','vent');
		echo "</span></li>";
		
		echo "<li><span>";
		_e('if it has been tagged','vent');
		echo " <select name='identity_tag'>";
		$tags = (array) get_tags('get=all');
		echo "<option value='0'";
		if(0 == $op['identity_tag']) echo " selected='selected'";
		echo ">";
		echo __('with anything','vent');
		echo "</option>";
		if(!empty($tags)) {
			foreach($tags as $key=>$tag) {
				echo "<option value='". $tag->term_id . "'";
				if($tag->term_id == $op['identity_tag']) echo " selected='selected'";
				echo ">";
				echo $tag->name;
				echo "</option>";
			}
		}
		echo "</select>";
		echo "</span></li>";
		
		echo "<li><span>";
		_e('if the post title starts with','vent');

		echo " <input type='text' name='identity_subject' id='identity_subject' value='";
		echo $op['identity_subject'];
		echo "' />";
		echo "</span></li>";
		echo "</ol>";
		
		echo "</td>";
		echo "</tr>\n";
		
		// Event display stuff
		
		echo "<tr valign='top'>";
		echo "<th scope='row' colspan='2'>";
		echo __("Highlight on Home page",'vent');
		echo "</th>";
		echo "<td>";
		_e('Showing the next event on the home page is currently','vent');
		echo " <select name='highlight_next_event'>";
		echo "<option value='0'";
		if(0 == $op['highlight_next_event']) echo " selected='selected'";
		echo ">";
		echo __('Disabled','vent');
		echo "</option>";
		echo "<option value='1'";
		if(1 == $op['highlight_next_event']) echo " selected='selected'";
		echo ">";
		echo __('Enabled','vent');
		echo "</option>";
		echo "</select> ";
		_e('and uses','vent');
		echo " <select name='highlight_next_event_using'>";
		echo "<option value='0'";
		if(0 == $op['highlight_next_event_using']) echo " selected='selected'";
		echo ">";
		echo __('Internal styles','vent');
		echo "</option>";
		echo "<option value='1'";
		if(1 == $op['highlight_next_event_using']) echo " selected='selected'";
		echo ">";
		echo __('My own styles','vent');
		echo "</option>";
		echo "</select> ";
		echo "</td>";
		echo "</tr>\n";
		
		// Styles
		echo "<tr valign='top'>";
		echo "<th scope='row' colspan='2'>";
		echo __("Internal highlight style",'vent');
		echo "</th>";
		echo "<td>";
		echo "<textarea name='highlight_next_event_style' id='highlight_next_event_style' cols='60' rows='5'>";
		echo $op['highlight_next_event_style'];
		echo "</textarea>";
		echo "</td>";
		echo "</tr>\n";
		
		echo "<tr valign='top'>";
		echo "<th scope='row' colspan='2'>";
		echo __("Make past events into posts",'vent');
		echo "</th>";
		echo "<td>";
		_e('Changing historical events back to posts is currently','vent');
		echo " <select name='make_pastevent_post'>";
		echo "<option value='0'";
		if(0 == $op['make_pastevent_post']) echo " selected='selected'";
		echo ">";
		echo __('Disabled','vent');
		echo "</option>";
		echo "<option value='1'";
		if(1 == $op['make_pastevent_post']) echo " selected='selected'";
		echo ">";
		echo __('Enabled','vent');
		echo "</option>";
		echo "</select>";
		echo "</td>";
		echo "</tr>\n";
		
		echo "<tr valign='top'>";
		echo "<th scope='row' colspan='2'>";
		echo __("hCalendar status",'vent');
		echo "</th>";
		echo "<td>";
		_e('The hCalendar added to an event post is','vent');
		echo " <select name='show_hcalender_onpost'>";
		echo "<option value='0'";
		if(0 == $op['show_hcalender_onpost']) echo " selected='selected'";
		echo ">";
		echo __('Hidden','vent');
		echo "</option>";
		echo "<option value='1'";
		if(1 == $op['show_hcalender_onpost']) echo " selected='selected'";
		echo ">";
		echo __('Complete','vent');
		echo "</option>";
		echo "<option value='2'";
		if(2 == $op['show_hcalender_onpost']) echo " selected='selected'";
		echo ">";
		echo __('Small','vent');
		echo "</option>";
		echo "</select>";
		echo "</td>";
		echo "</tr>\n";
		
		echo "<tr valign='top'>";
		echo "<th scope='row' colspan='2'>";
		echo __("hCalendar export link",'vent');
		echo "</th>";
		echo "<td>";
		echo "<input type='text' name='hcalender_process_link' id='hcalender_process_link' value='" . $op['hcalender_process_link'] . "' class='wide' />";
		echo "</td>";
		echo "</tr>\n";
		
		// Listing URI based data
		echo "<tr valign='top'>";
		echo "<th scope='row' colspan='2'>";
		echo __("Events base URL",'vent');
		echo "</th>";
		echo "<td>";
		echo "<strong>/</strong>&nbsp;";
		echo "<input type='text' name='eventrule_base' id='eventrule_base' value='" . $op['eventrule_base'] . "' />";
		echo "</td>";
		echo "</tr>";
		echo "<tr valign='top'>";
		echo "<th scope='row' colspan='2'>";
		echo __("Events sub URLs",'vent');
		echo "</th>";
		echo "<td>";
		echo "<strong>%base_url%&nbsp;/</strong>&nbsp;";
		echo "<input type='text' name='eventrule_all' id='eventrule_all' value='" . $op['eventrule_all'] . "' />";
		echo "&nbsp;-&nbsp;Will display all events<br/>";
		
		echo "<strong>%base_url%&nbsp;/</strong>&nbsp;";
		echo "<input type='text' name='eventrule_today' id='eventrule_today' value='" . $op['eventrule_today'] . "' />";
		echo "&nbsp;-&nbsp;Will display todays events<br/>";
		
		echo "<strong>%base_url%&nbsp;/</strong>&nbsp;";
		echo "<input type='text' name='eventrule_tomorrow' id='eventrule_tomorrow' value='" . $op['eventrule_tomorrow'] . "' />";
		echo "&nbsp;-&nbsp;Will display tomorrows events<br/>";
		
		echo "<strong>%base_url%&nbsp;/</strong>&nbsp;";
		echo "<input type='text' name='eventrule_thisweek' id='eventrule_thisweek' value='" . $op['eventrule_thisweek'] . "' />";
		echo "&nbsp;-&nbsp;Will display this weeks events<br/>";
		
		echo "<strong>%base_url%&nbsp;/</strong>&nbsp;";
		echo "<input type='text' name='eventrule_nextweek' id='eventrule_nextweek' value='" . $op['eventrule_nextweek'] . "' />";
		echo "&nbsp;-&nbsp;Will display next weeks events<br/>";
		
		echo "<strong>%base_url%&nbsp;/</strong>&nbsp;";
		echo "<input type='text' name='eventrule_thismonth' id='eventrule_thismonth' value='" . $op['eventrule_thismonth'] . "' />";
		echo "&nbsp;-&nbsp;Will display this months events<br/>";
		
		echo "<strong>%base_url%&nbsp;/</strong>&nbsp;";
		echo "<input type='text' name='eventrule_nextmonth' id='eventrule_nextmonth' value='" . $op['eventrule_nextmonth'] . "' />";
		echo "&nbsp;-&nbsp;Will display next months events<br/>";
		
		echo "<strong>%base_url%&nbsp;/</strong>&nbsp;";
		echo "<input type='text' name='eventrule_thisyear' id='eventrule_thisyear' value='" . $op['eventrule_thisyear'] . "' />";
		echo "&nbsp;-&nbsp;Will display this years events<br/>";
		
		echo "</td>";
		echo "</tr>\n";
		
		
		// Historical data
		
		echo "<tr valign='top'>";
		echo "<th scope='row' colspan='2'>";
		echo __("Current events",'vent');
		echo "</th>";
		echo "<td>";
		
		$numevents = $this->event_count();
		if($numevents > 1 || $numevents == 0) {
			$current = sprintf(__('There are currently <strong>%d</strong> posts marked as events. Click to reset them to posts.','vent'), $numevents);
		} else {
			$current = sprintf(__('There is currently <strong>%d</strong> post marked as an event. Click to reset it to a post.','vent'), $numevents);
		}
		
		echo $current . "&nbsp;";
		echo "<input type='submit' name='resetpostsbutton' id='resetpostsbutton' value='" . __('Reset Posts','vent') . "' class='button' />";
		
		
		echo "</td>";
		echo "</tr>\n";
		
		echo "</tbody>\n";
		echo "</table>\n";
		
		echo "<p>";
		echo "<input type='hidden' value='update' name='_action' />";
		echo "<input type='submit' value='" . __('Save Options','vent') . "' name='submit' class='button' />";
		echo "</p>";
		
		echo "</form>\n";
		
		echo "<table class='form-table'>";
		echo "<tbody>";
		echo "<tr valign='top'>";
		echo "<th scope='row'>";
		echo __("Support Vent",'vent');
		echo "</th>";
		echo "<td>";
		echo __('If you are using Vent and would like to support the future development of this system then please consider sending a donation by using the Donate button below. All donations sent via this method will be donated to our current charity (Oxfam and the Gurkha Welfare Trust).','vent');
		
		echo '<form action="https://www.paypal.com/cgi-bin/webscr" method="post">';
		echo '<input type="hidden" name="cmd" value="_donations">';
		echo '<input type="hidden" name="business" value="barry@clearskys.net">';
		echo '<input type="hidden" name="item_name" value="Vent donation">';
		echo '<input type="hidden" name="item_number" value="1">';
		echo '<input type="hidden" name="amount" value="">';
		echo '<input type="hidden" name="no_shipping" value="0">';
		echo '<input type="hidden" name="no_note" value="1">';
		echo '<input type="hidden" name="currency_code" value="GBP">';
		echo '<input type="hidden" name="tax" value="0">';
		echo '<input type="hidden" name="lc" value="US">';
		echo '<input type="hidden" name="bn" value="PP-DonationsBF">';
		echo '<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">';
		
		echo "</td>";
		echo "</tr>";
		echo "</tbody";
		echo "</table>";
		
		
		
		echo "</div>\n";
	}
	
	
}

// vent functions for the public interface
class vent_public extends vent_core {
	
	
		
	function __construct() {
		// Initialise the core
		$this->vent_core();
		
		if(!empty($this->op['ventenabled']) && $this->op['ventenabled'] != 0) {
			// Only add the filters and actions is vent is actually enabled
				
			// Rewrite rules - add checks for eventperiod and settings
			add_action('init', array(&$this, 'flush_rewrite'));
			add_action('generate_rewrite_rules', array(&$this, 'add_rewrite'));
		
			// Add the namespace and eventperiod queryvars to the list to look for
			add_filter('query_vars', array(&$this, 'add_vents_queryvars'));
		
			// Display based hooks and filters
			add_action('pre_get_posts', array(&$this, 'filter_vents_cats') );
			add_filter('the_content', array(&$this, 'filter_vents_content'));
			
			//$this->posts = apply_filters('posts_results', $this->posts);
			add_filter('posts_results', array(&$this, 'filter_post_results'));
		
			// Filter the query to return events when on the events page.
			add_filter('posts_where', array(&$this,'filter_vents_where') );
			add_filter('posts_orderby', array(&$this,'filter_vents_orderby') );
		
			// Filter the return posts if we are not on the events page to add the next
			// Event to the top of the page
			add_filter('the_posts', array(&$this,'add_next_vent_details'));
		
			add_action('wp_head', array(&$this,'add_site_header'));
		}
	}
	
	function vent_public() {
		$this->__construct();
	}
	
	function flush_rewrite() { 
		// This function clears the rewrite rules and forces them to be regenerated
		 	
		global $wp_rewrite;
	
		$wp_rewrite->flush_rules();
		
	}

	function add_vents_queryvars($vars) {
		// This function add the namespace (if it hasn't already been added) and the
		// eventperiod queryvars to the list that WordPress is looking for.
		// Note: Namespace provides a means to do a quick check to see if we should be doing anything
		
		if(!in_array('namespace',$vars)) $vars[] = 'namespace';
		$vars[] = 'eventperiod';
	
		return $vars;
	}
	
	function add_rewrite($wp_rewrite ) { 
	  	// This function adds in the Vent rewrite rules
		// Note the addition of the namespace variable so that we know these are vent based
		// calls
	
		$new_rules = array( 
							$this->op['eventrule_base'] . '/(.+)/page/?([0-9]{1,})/?$' => 'index.php?namespace=vent&eventperiod=' . $wp_rewrite->preg_index(1) . '&paged=' . $wp_rewrite->preg_index(2),
							$this->op['eventrule_base'] . '/(.+)/(feed|rdf|rss|rss2|atom|ical)/?$' => 'index.php?namespace=vent&eventperiod=' . $wp_rewrite->preg_index(1) . '&feed=' . $wp_rewrite->preg_index(2),
							$this->op['eventrule_base'] . '/(.+)' => 'index.php?namespace=vent&eventperiod=' . $wp_rewrite->preg_index(1),
							$this->op['eventrule_base'] . '/?$' => 'index.php?namespace=vent&eventperiod=all'
							);
					 
	  	$wp_rewrite->rules = $new_rules + $wp_rewrite->rules; 
	}
	
	/* Site side functions */
	
	function add_site_header() {
		echo "<link rel='stylesheet' href='" . $this->base_uri . "ventincludes/css/ventpublic.css?version=" . $this->build . "' type='text/css' />";
		if(isset($this->op['highlight_next_event']) && ($this->op['highlight_next_event'] != 0) && isset($this->op['highlight_next_event_using']) && ($this->op['highlight_next_event_using'] == 0)) {
			// We are set to show the next event and to use internal stylesheets
			if($this->getnextevent()) {
				// Check if there is actually a next event before outputting the styles
				echo "<style type='text/css' media='screen'>\n";
				
				echo str_replace('%postid%', $this->nextevent->ID, $this->op['highlight_next_event_style']);			
				
				echo "</style>\n";
			}			
		}
	}
	
	function filter_vents_cats() {
		
	}
	
	function filter_vents_content($thecontent) {
		// This function adds a vCalendar box to the bottom of the post content (or just above the <!--more--> tag if there is one)
		global $post, $wp_locale;
		
		$html = "";
		if($post->post_status == 'event') {
			
			switch($this->op['show_hcalender_onpost']) {
				case 0:
					$eventdiv = "style='display:none;'";
					$eventdivclass = "";
					break;
				case 1:
					// Do nothing
					break;
				case 2:
					$eventdivclass = "hidden";
					break;
			}
			
			$postdate = strtotime($post->post_date);
			$html .= "<br/>"; // For the feed
			$html .= "<div class='vevent $eventdivclass' $eventdiv>";
			if(!empty($this->op['hcalender_process_link'])) $html .= "<a href='" . $this->op['hcalender_process_link'] . urlencode(get_permalink($post->ID)) ."' title='" . __('Click to export event to your calendar','vent') . "'>";
			$html .= "<img src='" . $this->base_uri . "ventincludes/images/calendar.png' alt='calendar' border='0' /> ";
			if(!empty($this->op['hcalender_process_link'])) $html .= "</a>";
			$html .= "<a href='" . get_permalink($post->ID) . "' class='summary url'>" . $post->post_title . "</a> ";
			$html .= "<span class='datesblock'>";
			$html .= "<abbr class='dtstart' title='";
			$offset = get_option('gmt_offset');
			
			$html .= date("Y-m-d",$postdate) . "T" . date("H:i:s",$postdate);
			$offset = str_replace(":50", ":30", str_replace(":75", ":45",str_replace('.',':',sprintf("%+06.2f",$offset))));
			$html .= $offset;
			
			$html .= "'>" . $wp_locale->get_weekday_abbrev($wp_locale->get_weekday(date("w",$postdate))) . " " . date("j",$postdate) . " " . $wp_locale->get_month(date("m",$postdate)) . " " . date("Y H:i",$postdate) . "</abbr>";
			
			if(false !== $enddate = $this->getenddate($post->ID)) {
				$enddate = strtotime($enddate);
				$html .= " - <abbr class='dtend' title='";
				$html .= date("Y-m-d",$enddate) . "T" . date("H:i:s",$enddate);
				
				$html .= $offset;
				$html .= "'>" . $wp_locale->get_weekday_abbrev($wp_locale->get_weekday(date("w",$enddate))) . " " . date("j",$enddate) . " " . $wp_locale->get_month(date("m",$enddate)) . " " . date("Y H:i",$enddate) . "</abbr>";	
				
			}
			$html .= "</span>";
			$html .= "</div>";
			
			if($this->getnextevent()) {
				if($post->ID == $this->nextevent->ID) {
					$html .= "<div class='nextevent'></div>";
				}
			}
		}
				
		
		$thecontent .= $html;
		
		
		
		return $thecontent;
	}
	
	function filter_vents_orderby($orderby) {
		global $wp, $wp_query; 
		
		if(!empty($wp_query->query_vars['namespace']) && $wp_query->query_vars['namespace'] == 'vent') {
			$orderby = preg_replace("/(\w*)\.post_date\s*DESC/","\\1.post_date ASC", $orderby);
		}
		
		return $orderby;
	}
	
	function filter_vents_where($where) {
		// This function modifies the where clause so that only events are displayed when an event URI is passed
		// and adds date specifiers to limit the events to the specified date period.
		
		global $wp, $wp_query; 
		
		if(!empty($wp_query->query_vars['namespace']) && $wp_query->query_vars['namespace'] == 'vent') {
			$where = preg_replace("/\((\w*)\.post_status\s*=\s*'(.*)'\s*.*\)/","(\\1.post_status = 'event')", $where);
			
			switch($wp_query->query_vars['eventperiod']) {
					
				case $this->op['eventrule_today']:
							$where .= " AND (post_date >= CURDATE() AND post_date < DATE_ADD(CURDATE(), INTERVAL 1 day))";
							break;
				case $this->op['eventrule_tomorrow']:
							$where .= " AND (post_date >= DATE_ADD(CURDATE(), INTERVAL 1 day) AND post_date < DATE_ADD(CURDATE(), INTERVAL 2 day))";
							break;
				case $this->op['eventrule_thisweek']:
							$where .= " AND (post_date >= date_sub(curdate(), INTERVAL weekday(curdate()) day) AND post_date < date_add(curdate(), INTERVAL 7 - weekday(curdate()) day))";
							break;
				case $this->op['eventrule_nextweek']:
							$where .= " AND (post_date >= date_add(curdate(), INTERVAL 7-weekday(curdate()) day) AND post_date < date_add(curdate(), INTERVAL 14-weekday(curdate()) day) )";
							break;
				case $this->op['eventrule_thismonth']:
							$where .= " AND (post_date >= '" . date("Y-m-01") . "' AND post_date <= '" . date("Y-m-t") .  "')";
							break;
				case $this->op['eventrule_nextmonth']:
							$nextmonth = strtotime('+1 month', strtotime(date("Y-m-01")));
							$where .= " AND (post_date >= '" . date("Y-m-01",$nextmonth) . "' AND post_date <= '" . date("Y-m-t",$nextmonth) .  "')";
							break;
				case $this->op['eventrule_thisyear']:
							
							$where .= " AND (post_date >= '" . date("Y-01-01") . "' AND post_date <= '" . date("Y-12-31") .  "')";
							break;
				case $this->op['eventrule_all']:
				case 'all':
							// Do nothing as we want to show all of the results
							break;
				case '':
				default:
							// Need to decide what to display for an incorrect URL, at the moment
							// just display all the events as we have an event base_uri in place.
							break;
				
			}
		}
				
				
		return $where;
	}
	
	function filter_post_results($posts) {
		// This function changes the status of each post in the list from 'event' to 'published' so that
		// the correct status shows on the list (that of published), rather than just leave an empty
		// space in the table.
		
		if(!empty($posts)) {
			foreach($posts as $key=>$post) {
				if('event' == $post->post_status) {
					// Switch the status to show published
					$post->post_status = 'publish';
					// Add it back to the results
					$posts[$key] = $post;
				}
			}
		}
		
		return $posts;
	}
	
	function add_next_vent_details($posts) {
		
		global $wp_query;
		
		if(isset($this->op['highlight_next_event']) && $this->op['highlight_next_event'] != 0 && is_home() && !is_paged() && $wp_query->query_vars['namespace'] != 'vent') {
			if($this->getnextevent()) {
				$posts = array_merge(array($this->nextevent),$posts);
			}
		}
		
		return $posts;
	}
	
	
	
	
	
	
	
	
}

class vent_widgets extends vent_core {
	
	function __construct() {
		// Initialise the core
		$this->vent_core();
		
		// Widgets
		add_action('widgets_init', array(&$this,'register_widgets'));
	}
	
	function __destruct() {
		return true;
	}
	
	function vent_widgets() {
		$this->__construct();
	}
	
	
	/* Widgets code */
	
	function vent_next_event($args) {
		global $wp_locale;
		
		extract($args);
		$options = get_option('vent_next_event_widget');
		$title = empty($options['title']) ? __('Next Event','vent') : $options['title'];
		
		if($this->getnextevent()) {
			?>
			<?php echo $before_widget; ?>
			<?php echo $before_title . $title . $after_title; ?>
			
			<?php
			
			$post = $this->nextevent;
			$html = "";
			$postdate = strtotime($post->post_date);
			
			
			$html .= "<div class='vevent'";
			
			$html .= "<a href='" . get_permalink($post->ID) . "' class='summary url'>" . $post->post_title . "</a> ";
			$html .= "<span class='datesblock'>";
			$html .= "<strong>" . __('Starts','vent');
			$html .= ":</strong> <abbr class='dtstart' title='";
			$offset = get_option('gmt_offset');
			
			$html .= date("Y-m-d",$postdate) . "T" . date("H:i:s",$postdate);
			$offset = str_replace(":50", ":30", str_replace(":75", ":45",str_replace('.',':',sprintf("%+06.2f",$offset))));
			$html .= $offset;
			$html .= "'>" . $wp_locale->get_weekday_abbrev($wp_locale->get_weekday(date("w",$postdate))) . " " . date("j",$postdate) . " " . $wp_locale->get_month(date("m",$postdate)) . " " . date("Y",$postdate) . "</abbr>";
			$html .= "<br/><strong>" . __('Time','vent');
			$html .= ":</strong> " . date('H:i', $postdate);
			
			if(false !== $enddate = $this->getenddate($post->ID)) {
				$enddate = strtotime($enddate);
				$html .= "<br/><br/><strong>" . __('Ends','vent');
				$html .= ":</strong> <abbr class='dtend' title='";
				$html .= date("Y-m-d",$enddate) . "T" . date("H:i:s",$enddate);
				$html .= $offset;
				$html .= "'>" . $wp_locale->get_weekday_abbrev($wp_locale->get_weekday(date("w",$enddate))) . " " . date("j",$enddate) . " " . $wp_locale->get_month(date("m",$enddate)) . " " . date("Y",$enddate) . "</abbr>";	
				$html .= "<br/><strong>" . __('Time','vent');
				$html .= ":</strong> " . date('H:i', $enddate);
				
			}
			$html .= "</span>";
			if(!empty($this->op['hcalender_process_link'])) $html .= "<a href='" . $this->op['hcalender_process_link'] . urlencode(get_permalink($post->ID)) ."' title='" . __('Click to export event to your calendar','vent') . "'>";
			$html .= "<img src='" . $this->base_uri . "ventincludes/images/calendar.png' alt='calendar' border='0' /> ";
			if(!empty($this->op['hcalender_process_link'])) $html .= "</a>";
			$html .= "</div>";
			
			echo $html;
			
			?>
				
			<?php echo $after_widget; ?>
			<?php
		}
	}
	
	function vent_event_list($args) {
		global $wpdb, $wp_locale;
		
		if ( '%BEG_OF_TITLE%' != $args['before_title'] ) {
			if ( $output = wp_cache_get('vent_event_list', 'widget') )
				return print($output);
			ob_start();
		}

		extract($args);
		$options = get_option('vent_event_list_widget');
		$title = empty($options['title']) ? __('Upcoming Events','vent') : $options['title'];
		if ( !$number = (int) $options['number'] )
			$number = 10;
		else if ( $number < 1 )
			$number = 1;
		else if ( $number > 15 )
			$number = 15;

		$sql = "SELECT * FROM " . $wpdb->posts . " WHERE ";
		$sql .= "post_status = 'event' AND post_date >= NOW() ";
		$sql .= "ORDER BY post_date ASC ";
		$sql .= "LIMIT 0, $number";
		
		$r = $wpdb->get_results($sql, OBJECT);

		
		
		if (!empty($r)) :
	?>
			<?php echo $before_widget; ?>
				<?php echo $before_title . $title . $after_title; ?>
				<ul class='ventlist'>
				<?php  foreach ($r as $e) { ?>
				<li class='vevent'><?php
				if(!empty($this->op['hcalender_process_link'])) echo "<a href='" . $this->op['hcalender_process_link'] . urlencode(get_permalink($e->ID)) ."' title='" . __('Click to export event to your calendar','vent') . "'>";
				echo "<img src='" . $this->base_uri . "ventincludes/images/calendar.png' alt='calendar' border='0' /> ";
				if(!empty($this->op['hcalender_process_link'])) echo "</a>";
				?>
				<a class='summary url' href="<?php echo get_permalink($e->ID) ?>"><?php echo $e->post_title; ?> </a><br/>
				<?php
				echo "<span class='datesblock'>";
				echo "<abbr class='dtstart' title='";
				$offset = get_option('gmt_offset');
				$postdate = strtotime($e->post_date);
				echo date("Y-m-d",$postdate) . "T" . date("H:i:s",$postdate);
				$offset = str_replace(":50", ":30", str_replace(":75", ":45",str_replace('.',':',sprintf("%+06.2f",$offset))));
				echo $offset;
				echo "'>" . $wp_locale->get_weekday_abbrev($wp_locale->get_weekday(date("w",$postdate))) . " " . date("j",$postdate) . " " . $wp_locale->get_month(date("m",$postdate)) . " " . date('Y',$postdate) . "</abbr>";
				echo "</span>";
				?>
				</li>
				<?php } ?>
				</ul>
			<?php echo $after_widget; ?>
	<?php
		endif;

		if ( '%BEG_OF_TITLE%' != $args['before_title'] )
			wp_cache_add('vent_event_list', ob_get_flush(), 'widget');	
	}
	
	function vent_next_event_control() {
		$options = $newoptions = get_option('vent_next_event_widget');
		if ( $_POST["vent_next_event_submit"] ) {
			$newoptions['title'] = strip_tags(stripslashes($_POST["vent_next_event_title"]));
		}
		if ( $options != $newoptions ) {
			$options = $newoptions;
			update_option('vent_next_event_widget', $options);
		}
		$title = attribute_escape($options['title']);
		?>

					<p><label for="vent_next_event_title"><?php _e('Title:'); ?> <input class="widefat" id="vent_next_event_title" name="vent_next_event_title" type="text" value="<?php echo $title; ?>" /></label></p>
					<input type="hidden" id="vent_next_event_submit" name="vent_next_event_submit" value="1" />
		<?php
	}
	
	function vent_event_list_control() {
		$options = $newoptions = get_option('vent_event_list_widget');
		if ( $_POST["vent_event_list_submit"] ) {
			$newoptions['title'] = strip_tags(stripslashes($_POST["vent_event_list_title"]));
			$newoptions['number'] = (int) $_POST["vent_event_list_number"];
		}
		if ( $options != $newoptions ) {
			$options = $newoptions;
			update_option('vent_event_list_widget', $options);
			wp_flush_widget_recent_entries();
		}
		$title = attribute_escape($options['title']);
		if ( !$number = (int) $options['number'] )
			$number = 5;
	?>

				<p><label for="vent_event_list_title"><?php _e('Title:'); ?> <input class="widefat" id="vent_event_list_title" name="vent_event_list_title" type="text" value="<?php echo $title; ?>" /></label></p>
				<p>
					<label for="vent_event_list_number"><?php _e('Number of events to show:'); ?> <input style="width: 25px; text-align: center;" id="vent_event_list_number" name="vent_event_list_number" type="text" value="<?php echo $number; ?>" /></label>
					<br />
					<small><?php _e('(at most 15)'); ?></small>
				</p>
				<input type="hidden" id="vent_event_list_submit" name="vent_event_list_submit" value="1" />
	<?php
	}
	
	
	function register_widgets() {
		$widget_ops = array('classname' => 'widget_next_event', 'description' => __( "The next event") );
		wp_register_sidebar_widget('next_event', __('Next Event','vent'), array(&$this,'vent_next_event'), $widget_ops);
		wp_register_widget_control('next_event', __('Next Event','vent'),  array(&$this,'vent_next_event_control') );
		
		$widget_ops = array('classname' => 'widget_events_list', 'description' => __( "A list of the next events") );
		wp_register_sidebar_widget('events_list', __('Events List','vent'),  array(&$this,'vent_event_list'), $widget_ops);
		wp_register_widget_control('events_list', __('Events List','vent'),  array(&$this,'vent_event_list_control') );
	}
	
}

// External functions
function vent_scheduled() {
	// This function is run by the wp-cron scheduler
	global $vent, $wpdb;
	if(!empty($vent->op['ventenabled']) && $vent->op['ventenabled'] != 0 && $vent->op['make_pastevent_post'] != 0) {
		// Change any historical events back into posts
		$sql = "UPDATE " . $wpdb->posts . " SET ";
		$sql .= "post_status = 'publish' ";
		$sql .= "WHERE post_status = 'event' AND post_date < NOW()";
		$wpdb->query($sql);
	}
	return true;
}

// Load the relevant class depending on if we are in the admin area or the public website
// No need to load the one we are not using
if(is_admin()) {
	$vent =& new vent_admin();
} else {
	$vent =& new vent_public();
}

$ventwidget =& new vent_widgets();


?>