<?php



/**
* WP Geo Widget
* @author Marco Alionso Ramirez, marco@onemarco.com, updated by Ben Huson, ben@thewhiteroom.net
* @version 1.2
* Adds a geocoding widget to WordPress (requires WP Geo plugin)
*/




/**
 * The WP Geo Widget class
 */
class WPGeoWidget
{



	/**
	 * Properties
	 */
	 
	var $version = '1.2';
	
	

	/**
	 * Initialize the map widget
	 */
	function init_map_widget()
	{
	
		// This registers the widget so it appears in the sidebar
		register_sidebar_widget('WP Geo', array('WPGeoWidget', 'map_widget'));
	
		// This registers the  widget control form
		register_widget_control('WP Geo', array('WPGeoWidget', 'map_widget_control'));
	
	}
	
	
	
	/**
	 * Widget to display a map in the sidebar
	 */
	function map_widget($args) 
	{	
	
		global $wpgeo;
		
		// If Google API Key...
		if ($wpgeo->checkGoogleAPIKey())
		{
		
			// Extract the widget options
			extract($args);
			$wp_geo_options = get_option('wp_geo_options');
			$options = get_option('map_widget');
	
			// Get the options for the widget
			$title 			= empty( $options['title'] ) ? '' : apply_filters('widget_title', __($options['title']));
			$width 			= empty( $options['width'] ) ? '' : $options['width'];
			$height 		= empty( $options['height'] ) ? '' : $options['height'];
			$maptype 		= empty( $options['maptype'] ) ? '' : $options['maptype'];
			$showpolylines 	= $wp_geo_options['show_polylines'] == 'Y' ? true : false;
			if ($options['show_polylines'] == 'Y' || $options['show_polylines'] == 'N')
			{
				$showpolylines = $options['show_polylines'] == 'Y' ? true : false;
			}
			
			// Start write widget
			$html_content = '';
			$map_content = WPGeoWidget::add_map($width, $height, $maptype, $showpolylines);
			
			if (!empty($map_content))
			{
				$html_content = $before_widget . $before_title . $title . $after_title . WPGeoWidget::add_map($width, $height, $maptype, $showpolylines);
				$html_content .= $after_widget;
			}
			
			echo $html_content;
		
		}
		
	}	
	
	
	
	/**
	 * Control panel for the map widget
	 */
	function map_widget_control() 
	{
		
		global $wpgeo;
		$options = $newoptions = get_option('map_widget');
		
		// Get the options
		if ($_POST['wpgeo-submit']) 
		{
			$newoptions['title'] 	= strip_tags(stripslashes($_POST['wpgeo-title']));
			$newoptions['width'] 	= strip_tags(stripslashes($_POST['wpgeo-width']));
			$newoptions['height'] 	= strip_tags(stripslashes($_POST['wpgeo-height']));
			$newoptions['maptype'] 	= strip_tags(stripslashes($_POST['google_map_type']));
			$newoptions['show_polylines'] 	= strip_tags(stripslashes($_POST['show_polylines']));
		}
		
		// Set the options when they differ
		if ($options != $newoptions)
		{
			$options = $newoptions;
			update_option('map_widget', $options);
		}
	
		// Clean up the options
		$title 			= attribute_escape($options['title']);
		$width 			= attribute_escape($options['width']);
		$height 		= attribute_escape($options['height']);
		$maptype 		= attribute_escape($options['maptype']);
		$show_polylines	= attribute_escape($options['show_polylines']);
		
		// Write the widget controls
		if (!$wpgeo->checkGoogleAPIKey())
		{
			// NOTE: Check if there is a 'less hard-coded' way to write link to settings page
			echo '<p class="wp_geo_error">' . __('WP Geo is not currently active as you have not entered a Google API Key', 'wp-geo') . '. <a href="' . get_bloginfo('wpurl') . '/wp-admin/options-general.php?page=wp-geo/wp-geo.php">' . __('Please update your WP Geo settings', 'wp-geo') . '</a>.</p>';
		}
		echo '
			<p><label for="wpgeo-title">' . __('Title', 'wp-geo') . ': <input class="widefat" id="wpgeo-title" name="wpgeo-title" type="text" value="' . $title . '" /></label></p>
			<p><label for="wpgeo-width">' . __('Width', 'wp-geo') . ': <input class="widefat" id="wpgeo-width" name="wpgeo-width" type="text" value="' . $width . '" /></label></p>
			<p><label for="wpgeo-height">' . __('Height', 'wp-geo') . ': <input class="widefat" id="wpgeo-height" name="wpgeo-height" type="text" value="' . $height . '" /></label></p>';
		echo '<p>' . $wpgeo->google_map_types('menu', $maptype) . '</p>';
		echo '<p>' . WPGeoWidget::show_polylines_options('menu', $show_polylines) . '</p>';
		echo '<input type="hidden" id="wpgeo-submit" name="wpgeo-submit" value="1" />';
	
	}
	


	/**
	 * Select: Show Polylines Options
	 */
	function show_polylines_options($return = 'array', $selected = '')
	{
		
		// Array
		$map_type_array = array(
			''	=> __('Default', 'wp-geo'), 
			'Y'	=> __('Show Polylines', 'wp-geo'), 
			'N'	=> __('Hide Polylines', 'wp-geo')
		);
		
		// Menu?
		if ($return = 'menu')
		{
			$menu = '';
			foreach ($map_type_array as $key => $val)
			{
				$is_selected = $selected == $key ? ' selected="selected"' : '';
				$menu .= '<option value="' . $key . '"' . $is_selected . '>' . $val . '</option>';
			}
			$menu = '<select name="show_polylines" id="show_polylines">' . $menu. '</select>';
			return $menu;
		}
		
		// Default return
		return $map_type_array;
		
	}	
	
	
	
	/**
	 * Add the map to the widget
	 * TODO: integrate the code better into the existing one
	 */
	function add_map($width = '100%', $height = 150, $maptype = '', $showpolylines = false) 
	{
	
		global $posts, $wpgeo;
		
		// If Google API Key...
		if ($wpgeo->checkGoogleAPIKey())
		{
		
			// Set default width and height
			if (empty($width))
			{
				$width = '100%';
			}
			if (empty($height))
			{
				$height = '150';
			}
			
			// Get the basic settings of wp geo
			$wp_geo_options = get_option('wp_geo_options');
			
			
			// Find the coordinates for the posts
			$coords = array();
			for ($i = 0; $i < count($posts); $i++)
			{
			
				$post 		= $posts[$i];
				$latitude 	= get_post_meta($post->ID, '_wp_geo_latitude', true);
				$longitude 	= get_post_meta($post->ID, '_wp_geo_longitude', true);
				$post_id 	= get_post($post->ID);
				$title 		= $post_id->post_title;
				
				if (is_numeric($latitude) && is_numeric($longitude))
				{
					$push = array(
						'id' 		=> $post->ID,
						'latitude' 	=> $latitude,
						'longitude' => $longitude,
						'title' 	=> $title
					);
					array_push($coords, $push);
				}
				
			}
			
			// Markers JS (output)
			$markers_js = '';
			
			// Only show map widget if there are coords to show
			if (count($coords) > 0)
			{
			
				$google_maps_api_key = $wp_geo_options['google_api_key'];
				$zoom = $wp_geo_options['default_map_zoom'];
				
				if (empty($maptype))
				{
					$maptype = empty($wp_geo_options['google_map_type']) ? 'G_NORMAL_MAP' : $wp_geo_options['google_map_type'];			
				}
				
				// Polyline JS
				$polyline_coords_js = '[';
				
				for ($i = 0; $i < count($coords); $i++)
				{
					$polyline_coords_js .= 'new GLatLng(' . $coords[$i]['latitude'] . ', ' . $coords[$i]['longitude'] . '),';
				}
				
				$polyline_coords_js .= ']';		
		
				for ($i = 0; $i < count($coords); $i++)
				{
					$markers_js .= 'marker' . $i . ' = wpgeo_createMarker(new GLatLng(' . $coords[$i]['latitude'] . ', ' . $coords[$i]['longitude'] . '), wpgeo_icon_small, "' . addslashes($coords[$i]['title']) . '", "' . get_permalink($coords[$i]['id']) . '");' . "\n";
				}
							
				// Html JS
				$wpgeo->includeGoogleMapsJavaScriptAPI();
				
				$small_marker = $wpgeo->markers->get_marker_meta('small');
				
				$html_js .= '
					<script type="text/javascript">
					//<![CDATA[
					
					
					
					/**
					* Define variables
					*/
					
					var map = "";
					var bounds = "";
					
					
					
					/**
					* Add events to load the map
					*/
				
					GEvent.addDomListener(window, "load", createMapWidget);
					GEvent.addDomListener(window, "unload", GUnload);
				
				
				
					/**
					* Create the map
					*/
					
					function createMapWidget()
					{
						if(GBrowserIsCompatible())
						{
							map = new GMap2(document.getElementById("wp_geo_map_widget"));
							map.addControl(new GSmallZoomControl());
							map.setCenter(new GLatLng(0, 0), 0);
							map.setMapType(' . $maptype . ');
									
							bounds = new GLatLngBounds();		
							
							// Add the markers	
							'.	$markers_js .'
											
							// draw the polygonal lines between points
							';
					
				if ($showpolylines)
				{
					$html_js .= 'map.addOverlay(wpgeo_createPolyline(' . $polyline_coords_js . ', "' . $wp_geo_options['polyline_colour'] . '", 2, 0.50));';
				}
				$html_js .='
							// Center the map to show all markers
							var center = bounds.getCenter();
							var zoom = map.getBoundsZoomLevel(bounds)
							if (zoom > ' . $zoom . ')
							{
								zoom = ' . $zoom . ';
							}
							map.setCenter(center, zoom);
						}
					}
					
					
					//]]>
					</script>';
				
				// Set width and height
				if (is_numeric($width))
					$width = $width . 'px';
				if (is_numeric($height))
					$height = $height . 'px';
				
				$html_js .= '<div class="wp_geo_map" id="wp_geo_map_widget" style="width:' . $width . '; height:' . $height . ';"></div>';
			
			}
			
			return $html_js;
		
		}
		
	}
	
	
		
}



// Widget Hooks
add_action('init', array('WPGeoWidget', 'init_map_widget'));



?>