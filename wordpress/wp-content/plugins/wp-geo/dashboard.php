<?php



// ---------- WP Geo Dashboard ----------



if (!class_exists('WPGeoDashboard'))
{
	
	
	
	// WP Geo Dashboard Class
	class WPGeoDashboard
	{
		
		
		
		/**
		 * Constructor
		 */
		function WPGeoDashboard()
		{
		
			add_action('wp_dashboard_setup', array(&$this, 'register_widget'));
			add_filter('wp_dashboard_widgets', array(&$this, 'add_widget'));
		
		}
		
		
		
		/**
		 * Register Widget
		 */
		function register_widget()
		{
		
			wp_register_sidebar_widget('wpgeo_dashboard', 'WP Geo',
				array(&$this, 'widget'),
				array(
				'all_link' => 'http://www.wpgeo.com/',
				'feed_link' => 'http://www.wpgeo.com/feed/')
			);
		
		}
		
		
		
		/**
		 * Add Widget
		 */
		function add_widget($widgets)
		{
		
			global $wp_registered_widgets;
			
			if (!isset($wp_registered_widgets['wpgeo_dashboard']))
				return $widgets;
			array_splice($widgets, sizeof($widgets) - 1, 0, 'wpgeo_dashboard');
			
			return $widgets;
		
		}
		
		
		
		/**
		 * Widget
		 */
		function widget($args = array())
		{
		
			if (is_array($args))
				extract( $args, EXTR_SKIP );

			echo $before_widget . $before_title . $widget_name . $after_title;
			echo '<div style="background-image:url(' . WP_CONTENT_URL . '/plugins/wp-geo/img/logo/wp-geo.png); background-repeat:no-repeat; background-position:right top; padding-right:80px;">';
			
			// Include WordPress native RSS functions.
			include_once(ABSPATH . WPINC . '/rss.php');

			$rss = fetch_rss('http://feeds2.feedburner.com/wpgeo');
			$items = array_slice($rss->items, 0, 2);
			
			if (empty($items))
			{
				echo '<p>No items</p>';
			}
			else
			{
				foreach ($items as $item)
				{
					echo '<p><a style="font-size: 1.2em; font-weight:bold;" href="' . $item['link'] . '" title="' . $item['title'] . '">' . $item['title'] . '</a></p>';
					echo '<p style="color: #aaa;">' . date('l, jS F Y', strtotime($item['pubdate'])) .'</p>';
					echo '<p>' . $item['summary'] .'</p>';
				}
			}
			
			echo '<p><a href="http://www.wpgeo.com/">View all WP Geo news...</a></p>';
			echo '</div>';
			echo $after_widget;
			
		}
		
		
		
	}
	
	
	
	// Start the plugin
	global $wpgeoDashboard; $wpgeoDashboard = new WPGeoDashboard();
	


}



?>