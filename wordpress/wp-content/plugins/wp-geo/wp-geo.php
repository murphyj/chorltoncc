<?php



/*
Plugin Name: WP Geo
Plugin URI: http://www.wpgeo.com/
Description: Adds geocoding to WordPress.
Version: 3.0.9.1
Author: Ben Huson
Author URI: http://www.wpgeo.com/
Minimum WordPress Version Required: 2.5
Tested up to: 2.8.2
*/



// Pre-2.6 compatibility
if ( !defined( 'WP_CONTENT_URL' ) )
	define( 'WP_CONTENT_URL', get_option( 'siteurl' ) . '/wp-content' );
if ( !defined( 'WP_CONTENT_DIR' ) )
	define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
if ( !defined( 'WP_PLUGIN_URL' ) )
	define( 'WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins' );
if ( !defined( 'WP_PLUGIN_DIR' ) )
	define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );



/**
 * The WP Geo class
 */
class WPGeo
{
	
	
	
	/**
	 * Properties
	 */
	 
	var $version = '3.0.9.1';
	var $markers;
	var $show_maps_external = false;
	var $plugin_message = '';
	var $maps;
	var $editor;
	var $feeds;
	
	
	/**
	 * Constructor
	 */
	function WPGeo()
	{
		
		$this->maps = array();
		$this->markers = new WPGeoMarkers();
		$this->feeds = new WPGeo_Feeds();
		
	}
	
	

	/**
	 * Register Activation
	 */
	function register_activation()
	{
		
		global $wpgeo;
		
		$options = array(
			'google_api_key' => '', 
			'google_map_type' => 'G_NORMAL_MAP', 
			'show_post_map' => 'TOP', 
			'default_map_width' => '100%', 
			'default_map_height' => '300px',
			'default_map_zoom' => '5',
			'default_map_control' => 'GLargeMapControl',
			'show_map_type_normal' => 'Y',
			'show_map_type_satellite' => 'Y',
			'show_map_type_hybrid' => 'Y',
			'show_map_type_physical' => 'Y',
			'show_map_scale' => 'N',
			'show_map_overview' => 'N',
			'show_polylines' => 'Y',
			'polyline_colour' => '#FFFFFF',
			'show_maps_on_home' => 'Y',
			'show_maps_on_pages' => 'Y',
			'show_maps_on_posts' => 'Y',
			'show_maps_in_datearchives' => 'Y',
			'show_maps_in_categoryarchives' => 'Y',
			'show_maps_in_searchresults' => 'N',
			'add_geo_information_to_rss' => 'Y'
		);
		add_option('wp_geo_options', $options);
		$wp_geo_options = get_option('wp_geo_options');
		foreach ($options as $key => $val)
		{
			if (!isset($wp_geo_options[$key]))
			{
				$wp_geo_options[$key] = $options[$key];
			}
		}
		update_option('wp_geo_options', $wp_geo_options);
		
		// Files
		$this->markers->register_activation();
		
	}
	
	
	
	/**
	 * Shortcode: [wpgeo_latitude]
	 */
	function shortcode_wpgeo_latitude($atts, $content = null)
	{
	
		global $post;
		$lat = get_post_meta($post->ID, '_wp_geo_latitude', true);
		return $lat;
		
	}
	
	
	
	/**
	 * Shortcode: [wpgeo_longitude]
	 */
	function shortcode_wpgeo_longitude($atts, $content = null)
	{
	
		global $post;
		$long = get_post_meta($post->ID, '_wp_geo_longitude', true);
		return $long;
		
	}
	
	
	
	/**
	 * Shortcode: [wp_geo_map type="G_NORMAL_MAP"]
	 */
	function shortcode_wpgeo_map($atts, $content = null)
	{
	
		global $post, $wpgeo;
		
		$id = $post->ID;
		
		$wp_geo_options = get_option('wp_geo_options');
		
		if ($wpgeo->show_maps() && $wp_geo_options['show_post_map'] == 'HIDE' && $this->checkGoogleAPIKey())
		{
			$map_atts = array(
				'width' => null,
				'height' => null,
				'lat' => null,
				'long' => null,
				'type' => 'G_NORMAL_MAP',
				'escape' => false
			);
			extract(shortcode_atts($map_atts, $atts));
			
			// Escape?
			if ($escape == "true")
			{
				return '[wp_geo_map]';
			}
		
			$map_width = $wp_geo_options['default_map_width'];
			$map_height = $wp_geo_options['default_map_height'];
			
			if ( $atts['width'] != null) {
				$map_width = $atts['width'];
				if ( is_numeric($map_width) ) {
					$map_width = $map_width . 'px';
				}
			}
			if ( $atts['height'] != null) {
				$map_height = $atts['height'];
				if ( is_numeric($map_height) ) {
					$map_height = $map_height . 'px';
				}
			}
		
			// To Do: Add in lon/lat check and output map if needed
			
			return '<div class="wp_geo_map" id="wp_geo_map_' . $id . '" style="width:' . $map_width . '; height:' . $map_height . ';">' . $content . '</div>';
		}
		else
		{
			return '';
		}
		
	}
	
	
	
	/**
	 * is_wpgeo_feed
	 */
	function is_wpgeo_feed()
	{
		
		if ( is_feed() && $_GET['wpgeo'] == 'true' )
		{
			return true;
		}
		return false;
		
	}
	
	
	
	/**
	 * post_limits
	 */
	function post_limits( $limit )
	{
	
		global $wpgeo;
		
		if ( $wpgeo->is_wpgeo_feed() )
		{
			if ( isset($_GET['limit']) && is_numeric($_GET['limit']) )
			{
				return 'LIMIT 0, ' . $_GET['limit'];
			}
		}
		return $limit;
		
	}
	
	
	
	/**
	 * posts_join
	 */
	function posts_join( $join )
	{
	
		global $wpdb, $wpgeo;
		
		if ( $wpgeo->is_wpgeo_feed() )
		{
			$join .= " LEFT JOIN wp_postmeta ON (" . $wpdb->posts . ".ID = wp_postmeta.post_id)";
		}
		return $join;
		
	}
	
	
	
	/**
	 * posts_where
	 */
	function posts_where( $where )
	{
	
		global $wpgeo;
		
		if ( $wpgeo->is_wpgeo_feed() )
		{
			$where .= " AND (wp_postmeta.meta_key = '_wp_geo_latitude' OR wp_postmeta.meta_key = '_wp_geo_longitude')";
		}
		return $where;
	
	}
	
	
	
	/**
	 * Check Google API Key
	 */
	function checkGoogleAPIKey()
	{
		
		$wp_geo_options = get_option('wp_geo_options');
		if (empty($wp_geo_options['google_api_key']) || !isset($wp_geo_options['google_api_key']))
		{
			return false;
		}
		return true;
		
	}
	


	/**
	 * Category Map
	 */
	function categoryMap($args = '')
	{
		
		global $posts;
		
		$wp_geo_options = get_option('wp_geo_options');
		
		$showmap = false;
		
		// Extract args
		$allowed_args = array(
			'width' => null,
			'height' => null
		);
		$args = wp_parse_args($args, $allowed_args);
		
		for ($i = 0; $i < count($posts); $i++)
		{
			$post = $posts[$i];
			$latitude = get_post_meta($post->ID, '_wp_geo_latitude', true);
			$longitude = get_post_meta($post->ID, '_wp_geo_longitude', true);
			
			if (is_numeric($latitude) && is_numeric($longitude))
			{
				$showmap = true;
			}
			
		}
		
		$map_width = $wp_geo_options['default_map_width'];
		$map_height = $wp_geo_options['default_map_height'];
		
		if ( $args['width'] != null) {
			$map_width = $args['width'];
			if ( is_numeric($map_width) ) {
				$map_width = $map_width . 'px';
			}
		}
		if ( $args['height'] != null) {
			$map_height = $args['height'];
			if ( is_numeric($map_height) ) {
				$map_height = $map_height . 'px';
			}
		}
		
		if ($showmap && $this->checkGoogleAPIKey())
		{
			echo '<div class="wp_geo_map" id="wp_geo_map_visible" style="width:' . $map_width . '; height:' . $map_height . ';"></div>';
		}
		
	}
	
	
	/**
	 * meta_tags
	 */
	function meta_tags()
	{
		
		if (is_single())
		{
			
			global $post;
			
			$lat =  get_post_meta($post->ID, '_wp_geo_latitude', true);
			$long =  get_post_meta($post->ID, '_wp_geo_longitude', true);
			$nl = "\n";
			
			if (is_numeric($lat) && is_numeric($long))
			{
				echo '<meta name="geo.position" content="' . $lat . ';' . $long . '" />' . $nl; // Geo-Tag: Latitude and longitude
				//echo '<meta name="geo.region" content="DE-BY" />' . $nl;               // Geo-Tag: Country code (ISO 3166-1) and regional code (ISO 3166-2)
				//echo '<meta name="geo.placename" content="M�nchen" />' . $nl;          // Geo-Tag: City or the nearest town
				//echo '<meta name="DC.title" content="Geo Tag Validator" />' . $nl;     // Dublin Core Meta Tag Title (used by some geo databases)
				echo '<meta name="ICBM" content="' . $lat . ', ' . $long . '" />' . $nl;        // ICBM Tag (prior existing equivalent to the geo.position)
			}
		}
		
	}
	


	/**
	 * Hook: wp_head
	 */
	function wp_head()
	{
		
		global $wpgeo;
		
		$this->meta_tags();
		
		// WP Geo Default Settings
		$wp_geo_options = get_option('wp_geo_options');
		
		$controltypes = array();
		if ($wp_geo_options['show_map_type_normal'] == 'Y')
			$controltypes[] = 'G_NORMAL_MAP';
		if ($wp_geo_options['show_map_type_satellite'] == 'Y')
			$controltypes[] = 'G_SATELLITE_MAP';
		if ($wp_geo_options['show_map_type_hybrid'] == 'Y')
			$controltypes[] = 'G_HYBRID_MAP';
		if ($wp_geo_options['show_map_type_physical'] == 'Y')
			$controltypes[] = 'G_PHYSICAL_MAP';
		
		echo '
		
			<script type="text/javascript">
			//<![CDATA[
			
			// WP Geo default settings
			var wpgeo_w = \'' . $wp_geo_options['default_map_width'] . '\';
			var wpgeo_h = \'' . $wp_geo_options['default_map_height'] . '\';
			var wpgeo_type = \'' . $wp_geo_options['google_map_type'] . '\';
			var wpgeo_zoom = ' . $wp_geo_options['default_map_zoom'] . ';
			var wpgeo_controls = \'' . $wp_geo_options['default_map_control'] . '\';
			var wpgeo_controltypes = \'' . implode(",", $controltypes) . '\';
			var wpgeo_scale = \'' . $wp_geo_options['show_map_scale'] . '\';
			var wpgeo_overview = \'' . $wp_geo_options['show_map_overview'] . '\';
			
			//]]>
			</script>
			
			';
		
		// CSS
		echo '<link rel="stylesheet" href="' . WP_CONTENT_URL . '/plugins/wp-geo/wp-geo.css" type="text/css" />';
		
		if ($wpgeo->show_maps() || $wpgeo->widget_is_active())
		{
		
			global $posts;
			
			$this->markers->wp_head();
			
			$wp_geo_options = get_option('wp_geo_options');
			$maptype = empty($wp_geo_options['google_map_type']) ? 'G_NORMAL_MAP' : $wp_geo_options['google_map_type'];
			
			// Coords to show on map?
			$coords = array();
			for ($i = 0; $i < count($posts); $i++)
			{
				$post = $posts[$i];
				$latitude = get_post_meta($post->ID, '_wp_geo_latitude', true);
				$longitude = get_post_meta($post->ID, '_wp_geo_longitude', true);
				
				if (is_numeric($latitude) && is_numeric($longitude))
				{
					$push = array(
						'id' => $post->ID,
						'latitude' => $latitude,
						'longitude' => $longitude,
						'title' => $post->post_title,
						'link' => $post->guid
					);
					array_push($coords, $push);
					
					
					// ----------- Start - Create maps for visible posts and pages -----------
					
					$map = new WPGeoMap($post->ID);										// Create map
					
					// Add point
					$map->addPoint($latitude, $longitude, 'wpgeo_icon_large', $post->post_title, $post->guid);
					
					$map->setMapZoom($wp_geo_options['default_map_zoom']);				// Set zoom
					$map->setMapType($maptype);											// Set map type
					
					if ($wp_geo_options['show_map_type_physical'] == 'Y')
						$map->addMapType('G_PHYSICAL_MAP');								// Show PHYSICAL map?
					if ($wp_geo_options['show_map_type_normal'] == 'Y')
						$map->addMapType('G_NORMAL_MAP');								// Show NORMAL map?
					if ($wp_geo_options['show_map_type_satellite'] == 'Y')
						$map->addMapType('G_SATELLITE_MAP');							// Show SATELLITE map?
					if ($wp_geo_options['show_map_type_hybrid'] == 'Y')
						$map->addMapType('G_HYBRID_MAP');								// Show HYBRID map?
					
					if ($wp_geo_options['show_map_scale'] == 'Y')
						$map->showMapScale(true);										// Show map scale
					if ($wp_geo_options['show_map_overview'] == 'Y')
						$map->showMapOverview(true);									// Show map overview
					
					$map->setMapControl($wp_geo_options['default_map_control']);		// Set map control
					array_push($this->maps, $map);										// Add map to maps array
					
					// ----------- End - Create maps for visible posts and pages -----------
					
					
				}
				
			}
			
			// Need a map?
			if (count($coords) > 0)
			{
			
			
				// ----------- Start - Create map for visible posts and pages -----------
				
				$map = new WPGeoMap('visible');
				$map->show_polyline = true;
				
				// Add points
				for ($j = 0; $j < count($coords); $j++)
				{
					$map->addPoint($coords[$j]['latitude'], $coords[$j]['longitude'], 'wpgeo_icon_small', $coords[$j]['title'], $coords[$j]['link']);
				}
				
				$map->setMapZoom($wp_geo_options['default_map_zoom']);			// Set zoom
				$map->setMapType($maptype);										// Set map type
				
				if ($wp_geo_options['show_map_type_physical'] == 'Y')			// Show PHYSICAL map?
					$map->addMapType('G_PHYSICAL_MAP');
				if ($wp_geo_options['show_map_type_normal'] == 'Y')				// Show NORMAL map?
					$map->addMapType('G_NORMAL_MAP');
				if ($wp_geo_options['show_map_type_satellite'] == 'Y')			// Show SATELLITE map?
					$map->addMapType('G_SATELLITE_MAP');
				if ($wp_geo_options['show_map_type_hybrid'] == 'Y')				// Show HYBRID map?
					$map->addMapType('G_HYBRID_MAP');
				
				if ($wp_geo_options['show_map_scale'] == 'Y')
					$map->showMapScale(true);									// Show map scale
				if ($wp_geo_options['show_map_overview'] == 'Y')
					$map->showMapOverview(true);								// Show map overview
					
				$map->setMapControl($wp_geo_options['default_map_control']);	// Set map control
				array_push($this->maps, $map);									// Add map to maps array
				
				// ----------- End - Create map for visible posts and pages -----------
				
					
				$google_maps_api_key = $wp_geo_options['google_api_key'];
				$zoom = $wp_geo_options['default_map_zoom'];
				
				// Loop through maps to get Javascript
				$js_map_writes = '';
				foreach ($this->maps as $map)
				{
					$js_map_writes .= $map->renderMapJS();
				}
						
				// Script
				$wpgeo->includeGoogleMapsJavaScriptAPI();
				$html_content .= '
				<script type="text/javascript">
				//<![CDATA[
				
				var map = null; ' . $js_map_inits . '
				var marker = null; ' . $js_marker_inits . '
				
				function init_wp_geo_map()
				{
					if (GBrowserIsCompatible())
					{
						' . $js_map_writes . '
					}
				}
				if (document.all&&window.attachEvent) { // IE-Win
					window.attachEvent("onload", function () { init_wp_geo_map(); });
					window.attachEvent("onunload", GUnload);
				} else if (window.addEventListener) { // Others
					window.addEventListener("load", function () { init_wp_geo_map(); }, false);
					window.addEventListener("unload", GUnload, false);
				}
				//]]>
				</script>';
				
				echo $html_content;
				
			}
	
			// Check if plugin head needed
			// Check for Google API key
			// Write Javascripts and CSS
		
		}
		
	}



	/**
	 * Hook: Init
	 */
	function init()
	{
	
		// Only show admin things if Google API Key valid
		if ($this->checkGoogleAPIKey())
		{
		
			// Use the admin_menu action to define the custom boxes
			add_action('admin_menu', array($this, 'add_custom_boxes'));
			
			// Use the save_post action to do something with the data entered
			add_action('save_post', array($this, 'wpgeo_location_save_postdata'));
			
		}
		
	}
	
	

	/**
	 * Hook: admin_init
	 */
	function admin_init()
	{
		
		// Register Settings
		if (function_exists('register_setting'))
		{
			register_setting('wp-geo-options', 'wp_geo_options', '');
		}
		
		// Only show editor if Google API Key valid
		if ( $this->checkGoogleAPIKey() )
		{
			if ( class_exists( 'WPGeo_Editor' ) ) {
				$this->editor = new WPGeo_Editor();
				$this->editor->add_buttons();
			}
		}
		
	}



	/**
	 * Hook: admin_head
	 */
	function admin_head()
	{
	
		global $wpgeo, $post_ID;
		
		echo '<link rel="stylesheet" href="' . WP_CONTENT_URL . '/plugins/wp-geo/wp-geo.css" type="text/css" />';
		
		// Only load if on a post or page
		if ($wpgeo->show_maps())
		{
			
			// Get post location
			$latitude = get_post_meta($post_ID, '_wp_geo_latitude', true);
			$longitude = get_post_meta($post_ID, '_wp_geo_longitude', true);
			$default_latitude = $latitude;
			$default_longitude = $longitude;
			$default_zoom = 13;
			
			$panel_open = false;
			$hide_marker = false;
			
			if (!$wpgeo->show_maps_external)
			{
				echo $wpgeo->mapScriptsInit($default_latitude, $default_longitude, $default_zoom, $panel_open, $hide_marker);
			}
		
		}
		
	}
	
	
	
	/**
	 * Include Google Maps JavaScript API
	 */
	function includeGoogleMapsJavaScriptAPI()
	{
		
		global $wpgeo;
		$wp_geo_options = get_option('wp_geo_options');
		
		// Google AJAX API
		// Loads on all pages unless via proxy domain
		if ($wpgeo->check_domain() && $wpgeo->checkGoogleAPIKey())
		{
			wp_register_script('google_jsapi', 'http://www.google.com/jsapi?key=' . $wp_geo_options['google_api_key'], false, '1.0');
			wp_enqueue_script('google_jsapi');
		}
		
		if (($wpgeo->show_maps() || $wpgeo->widget_is_active()) && $wpgeo->checkGoogleAPIKey())
		{
			
			wp_register_script('googlemaps', 'http://maps.google.com/maps?file=api&v=2.118&key=' . $wp_geo_options['google_api_key'] . '&sensor=false', false, '2.118');
			wp_register_script('wpgeo', WP_CONTENT_URL . '/plugins/wp-geo/js/wp-geo.js', array('googlemaps', 'wpgeotooltip'), '1.0');
			wp_register_script('wpgeotooltip', WP_CONTENT_URL . '/plugins/wp-geo/js/tooltip.js', array('googlemaps', 'jquery'), '1.0');
			//wp_register_script('jquerywpgeo', WP_CONTENT_URL . '/plugins/wp-geo/js/jquery.wp-geo.js', array('jquery', 'googlemaps'), '1.0');
			
			wp_enqueue_script('jquery');
			wp_enqueue_script('googlemaps');
			wp_enqueue_script('wpgeo');
			wp_enqueue_script('wpgeotooltip');
			//wp_enqueue_script('jquerywpgeo');
			
			return '';
		}
		
	}
	
	
	
	/**
	 * Map Scripts Init
	 */
	function mapScriptsInit($latitude, $longitude, $zoom = 5, $panel_open = false, $hide_marker = false)
	{
		
		global $wpgeo;
		
		$wp_geo_options = get_option('wp_geo_options');
		$maptype = empty($wp_geo_options['google_map_type']) ? 'G_NORMAL_MAP' : $wp_geo_options['google_map_type'];	
		
		if (!is_numeric($latitude) || !is_numeric($longitude))
		{
			// Centre on London
			$latitude = 51.492526418807465;
			$longitude = -0.15754222869873047;
			$zoom = $wp_geo_options['default_map_zoom']; // Default 5;
			$panel_open = true;
			$hide_marker = true;
		}
		
		// Vars
		$google_maps_api_key = $wp_geo_options['google_api_key'];
		$panel_open ? $panel_open = 'jQuery(\'#wpgeolocationdiv.postbox h3\').click();' : $panel_open = '';
		$hide_marker ? $hide_marker = 'marker.hide();' : $hide_marker = '';
		
		// Script
		$wpgeo->includeGoogleMapsJavaScriptAPI();
		$html_content .= '
			<script type="text/javascript">
			//<![CDATA[
			
			var map = null;
			var geocoder = null;
			var marker = null;
			
			jQuery(document).ready(function() {
			
				// Admin - Latitude filed updated
				jQuery("#wp_geo_latitude").keyup(function() {
					updatedLatLngFields();
				});
				
				// Admin - Longitude filed updated
				jQuery("#wp_geo_longitude").keyup(function() {
					updatedLatLngFields();
				});
			
			});
			
			function updatedLatLngFields()
			{
				var latField = document.getElementById("wp_geo_latitude");
				var lngField = document.getElementById("wp_geo_longitude");
				if (latField.value == \'\' && lngField.value == \'\')
				{
					marker.hide();
				}
				else
				{
					var point = new GLatLng(latField.value, lngField.value);
					map.setCenter(point);
					marker.setPoint(point);
					marker.show();
				}
			}
			
			function clearLatLngFields()
			{
				var searchField = document.getElementById("wp_geo_search");
				var latField = document.getElementById("wp_geo_latitude");
				var lngField = document.getElementById("wp_geo_longitude");
				searchField.value = \'\';
				latField.value = \'\';
				lngField.value = \'\';
				marker.hide();
			}
			
			function wp_geo_showAddress()
			{
				var searchField = document.getElementById("wp_geo_search");
				var latField = document.getElementById("wp_geo_latitude");
				var lngField = document.getElementById("wp_geo_longitude");
				var address = searchField.value;
				if (geocoder)
				{
					geocoder.getLatLng(
						address,
						function(point)
						{
							if (!point)
							{
								alert(address + " not found");
							}
							else
							{
								map.setCenter(point);
								marker.setPoint(point);
								marker.show();
								latField.value = point.lat();
								lngField.value = point.lng();
							}
						}
					);
				}
			}
			
			function init_wp_geo_map_admin()
			{
				if (GBrowserIsCompatible() && document.getElementById("wp_geo_map"))
				{
					map = new GMap2(document.getElementById("wp_geo_map"));
					var center = new GLatLng(' . $latitude . ', ' . $longitude . ');
					map.setCenter(center, ' . $zoom . ');
					map.addMapType(G_PHYSICAL_MAP);
					
					// Map Controls
					var mapTypeControl = new GMapTypeControl();
					map.addControl(new GLargeMapControl());
					map.addControl(mapTypeControl);
					
					map.setMapType(' . $maptype . ');
					
					geocoder = new GClientGeocoder();
					 
					GEvent.addListener(map, "click", function(overlay, latlng) {
						var latField = document.getElementById("wp_geo_latitude");
						var lngField = document.getElementById("wp_geo_longitude");
						latField.value = latlng.lat();
						lngField.value = latlng.lng();
						marker.setPoint(latlng);
						marker.show();
					});
					
					GEvent.addListener(map, "zoomend", function(oldLevel, newLevel) {
						map.setCenter(marker.getLatLng());
					});
					
					marker = new GMarker(center, {draggable: true});
					
					GEvent.addListener(marker, "dragstart", function() {
						map.closeInfoWindow();
					});
					
					GEvent.addListener(marker, "dragend", function() {
						var coords = marker.getLatLng();
						var latField = document.getElementById("wp_geo_latitude");
						var lngField = document.getElementById("wp_geo_longitude");
						latField.value = coords.lat();
						lngField.value = coords.lng();
					});
					
					map.addOverlay(marker);
					
					' . $panel_open . '
					
					var latField = document.getElementById("wp_geo_latitude");
					var lngField = document.getElementById("wp_geo_longitude");
					
					' . $hide_marker . '
					
				}
			}
			if (document.all&&window.attachEvent) { // IE-Win
				window.attachEvent("onload", function () { init_wp_geo_map_admin(); });
				window.attachEvent("onunload", GUnload);
			} else if (window.addEventListener) { // Others
				window.addEventListener("load", function () { init_wp_geo_map_admin(); }, false);
				window.addEventListener("unload", GUnload, false);
			}
			//]]>
			</script>';
			
		return $html_content;
		
	}
	


	/**
	 * Hook: the_content
	 */
	function the_content($content = '')
	{
	
		global $wpgeo;
		
		if ($wpgeo->show_maps())
		{
		
			global $posts, $post;
			
			$wp_geo_options = get_option('wp_geo_options');
			
			// Get the post
			$id = $post->ID;
		
			// Get latitude and longitude
			$latitude = get_post_meta($post->ID, '_wp_geo_latitude', true);
			$longitude = get_post_meta($post->ID, '_wp_geo_longitude', true);
			
			// Need a map?
			if (is_numeric($latitude) && is_numeric($longitude))
			{
				if ($wp_geo_options['show_post_map'] == 'TOP')
				{
					// Show at top of post
					return '<div class="wp_geo_map" id="wp_geo_map_' . $id . '" style="width:' . $wp_geo_options['default_map_width'] . '; height:' . $wp_geo_options['default_map_height'] . ';"></div>' . $content;
				}
				elseif ($wp_geo_options['show_post_map'] == 'BOTTOM')
				{
					// Show at bottom of post
					return $content . '<div class="wp_geo_map" id="wp_geo_map_' . $id . '" style="width:' . $wp_geo_options['default_map_width'] . '; height:' . $wp_geo_options['default_map_height'] . ';"></div>';
				}
			}
		
		}
		
		return $content;
		
	}

	
	
	/**
	 * Hook: admin_menu
	 */
	function admin_menu()
	{
		
		global $wpgeo;
		
		if (function_exists('add_options_page'))
		{
			add_options_page('WP Geo Options', 'WP Geo', 8, __FILE__, array($wpgeo, 'options_page'));
		}
		
	}

	
	
	/**
	 * Widget Is Active
	 */
	function widget_is_active()
	{
		
		return is_active_widget(array('WPGeoWidget', 'map_widget'));
		
	}
	
	
	
	/**
	 * Show Maps
	 */
	function show_maps()
	{
	
		global $post_ID, $pagenow;
		
		$wp_geo_options = get_option('wp_geo_options');
		
		// Check if domain is correct
		if (!$this->check_domain())
		{
			return false;
		}
		
		// Widget active
		//if (is_active_widget(array('WPGeoWidget', 'map_widget'))) return true;
		
		// Check settings
		if (is_home() && $wp_geo_options['show_maps_on_home'] == 'Y')					return true;
		if (is_single() && $wp_geo_options['show_maps_on_posts'] == 'Y')				return true;
		if (is_page() && $wp_geo_options['show_maps_on_pages'] == 'Y')					return true;
		if (is_date() && $wp_geo_options['show_maps_in_datearchives'] == 'Y')			return true;
		if (is_category() && $wp_geo_options['show_maps_in_categoryarchives'] == 'Y')	return true;
		if (is_search() && $wp_geo_options['show_maps_in_searchresults'] == 'Y')		return true;
		if (is_feed() && $wp_geo_options['add_geo_information_to_rss'] == 'Y')			return true;

		// Activate maps in admin...
		if (is_admin())
		{
			// If editing a post or page...
			if (is_numeric($post_ID) && $post_ID > 0)
			{
				return true;
			}
			// If writing a new post or page...
			if ($pagenow == 'post-new.php' || $pagenow == 'page-new.php')
			{
				return true;
			}
		}
		
		// Do Action
		if ($this->show_maps_external)
		{
			return true;
		}
		
		return false;
		
	}
	
	
	
	/**
	 * ---------- Check Domain ----------
	 * This function checks that the domainname of the page matches the blog site url.
	 * If it doesn't match we can prevent maps from showing as the Google API Key will not be valid.
	 * This prevent warnings if the site is accessed through Google cache.
	 */
	function check_domain()
	{
	
		$host = 'http://' . rtrim($_SERVER["HTTP_HOST"], "/");
		$blog = preg_replace("/(http:\/\/[^\/]*).*/", "$1", get_bloginfo('siteurl')); // Blog might not be in site root so strip to domain
		
		$match = $host == $blog ? true : false;
		
		return $match;
		
	}



	/**
	 * Options Checkbox
	 */
	function options_checkbox($id, $val, $checked)
	{
	
		$is_checked = '';
		if ($val == $checked)
		{
			$is_checked = 'checked="checked" ';
		}
		return '<input name="' . $id . '" type="checkbox" id="' . $id . '" value="' . $val . '" ' . $is_checked . '/>';
	
	}



	/**
	 * Options Page
	 */
	function options_page()
	{
		
		global $wpgeo;
		
		$wp_geo_options = get_option('wp_geo_options');
		
		// Process option updates
		if (isset($_POST['action']) && $_POST['action'] == 'update')
		{
		
			$wp_geo_options['google_api_key'] = $_POST['google_api_key'];
			$wp_geo_options['google_map_type'] = $_POST['google_map_type'];
			$wp_geo_options['show_post_map'] = $_POST['show_post_map'];
			$wp_geo_options['default_map_width'] = $wpgeo->numberPercentOrPx($_POST['default_map_width']);
			$wp_geo_options['default_map_height'] = $wpgeo->numberPercentOrPx($_POST['default_map_height']);
			$wp_geo_options['default_map_zoom'] = $_POST['default_map_zoom'];
			
			$wp_geo_options['default_map_control'] = $_POST['default_map_control'];
			$wp_geo_options['show_map_type_normal'] = $_POST['show_map_type_normal'] == 'Y' ? 'Y' : 'N';
			$wp_geo_options['show_map_type_satellite'] = $_POST['show_map_type_satellite'] == 'Y' ? 'Y' : 'N';
			$wp_geo_options['show_map_type_hybrid'] = $_POST['show_map_type_hybrid'] == 'Y' ? 'Y' : 'N';
			$wp_geo_options['show_map_type_physical'] = $_POST['show_map_type_physical'] == 'Y' ? 'Y' : 'N';
			$wp_geo_options['show_map_scale'] = $_POST['show_map_scale'] == 'Y' ? 'Y' : 'N';
			$wp_geo_options['show_map_overview'] = $_POST['show_map_overview'] == 'Y' ? 'Y' : 'N';
			
			$wp_geo_options['show_polylines'] = $_POST['show_polylines'] == 'Y' ? 'Y' : 'N';
			$wp_geo_options['polyline_colour'] = $_POST['polyline_colour'];
			
			$wp_geo_options['show_maps_on_home'] = $_POST['show_maps_on_home'] == 'Y' ? 'Y' : 'N';
			$wp_geo_options['show_maps_on_pages'] = $_POST['show_maps_on_pages'] == 'Y' ? 'Y' : 'N';
			$wp_geo_options['show_maps_on_posts'] = $_POST['show_maps_on_posts'] == 'Y' ? 'Y' : 'N';
			$wp_geo_options['show_maps_in_datearchives'] = $_POST['show_maps_in_datearchives'] == 'Y' ? 'Y' : 'N';
			$wp_geo_options['show_maps_in_categoryarchives'] = $_POST['show_maps_in_categoryarchives'] == 'Y' ? 'Y' : 'N';
			$wp_geo_options['show_maps_in_searchresults'] = $_POST['show_maps_in_searchresults'] == 'Y' ? 'Y' : 'N';
			
			$wp_geo_options['add_geo_information_to_rss'] = $_POST['add_geo_information_to_rss'] == 'Y' ? 'Y' : 'N';
			
			update_option('wp_geo_options', $wp_geo_options);
			echo '<div class="updated"><p>' . __('WP Geo settings updated', 'wp-geo') . '</p></div>';
			
		}

		// Markers
		$markers = array();
		$markers['large'] = $this->markers->get_marker_meta('large');
		$markers['small'] = $this->markers->get_marker_meta('small');
		$markers['dot'] = $this->markers->get_marker_meta('dot');
		
		// Write the form
		echo '
		<div class="wrap">
			<h2>' . __('WP Geo Settings', 'wp-geo') . '</h2>
			<form method="post">
				<img style="float:right; padding:0 20px 0 0; margin:0 0 20px 20px;" src="' . WP_CONTENT_URL . '/plugins/wp-geo/img/logo/wp-geo.png" />
				<h3>' . __('General Settings', 'wp-geo') . '</h3>
				<p>'
				. sprintf(__("For more information and documentation about this plugin please visit the <a %s>WP Geo Plugin</a> home page.", 'wp-geo'), 'href="http://www.benhuson.co.uk/wordpress-plugins/wp-geo/"') . '<br />'
				. sprintf(__("If you experience any problems/bugs with the plugin, please <a %s>log it here</a>.", 'wp-geo'), 'href="http://code.google.com/p/wp-geo/issues/list"') . 
				'</p>';
		if (!$this->checkGoogleAPIKey())
		{
			echo '<div class="error"><p>Before you can use Wp Geo you must acquire a <a href="http://code.google.com/apis/maps/signup.html">Google API Key</a> for your blog - the plugin will not function without it!</p></div>';
		}
		echo '<table class="form-table">
					<tr valign="top">
						<th scope="row">' . __('Google API Key', 'wp-geo') . '</th>
						<td><input name="google_api_key" type="text" id="google_api_key" value="' . $wp_geo_options['google_api_key'] . '" size="50" /></td>
					</tr>
					<tr valign="top">
						<th scope="row">' . __('Map Type', 'wp-geo') . '</th>
						<td>' . $wpgeo->google_map_types('menu', $wp_geo_options['google_map_type']) . '</td>
					</tr>
					<tr valign="top">
						<th scope="row">' . __('Show Post Map', 'wp-geo') . '</th>
						<td>' . $wpgeo->post_map_menu('menu', $wp_geo_options['show_post_map']) . '</td>
					</tr>
					<tr valign="top">
						<th scope="row">' . __('Default Map Width', 'wp-geo') . '</th>
						<td><input name="default_map_width" type="text" id="default_map_width" value="' . $wp_geo_options['default_map_width'] . '" size="10" /></td>
					</tr>
					<tr valign="top">
						<th scope="row">' . __('Default Map Height', 'wp-geo') . '</th>
						<td><input name="default_map_height" type="text" id="default_map_height" value="' . $wp_geo_options['default_map_height'] . '" size="10" /></td>
					</tr>
					<tr valign="top">
						<th scope="row">' . __('Default Map Zoom', 'wp-geo') . '</th>
						<td>' . $wpgeo->selectMapZoom('menu', $wp_geo_options['default_map_zoom']) . '</td>
					</tr>
					<tr valign="top">
						<th scope="row">' . __('Default Map Controls', 'wp-geo') . '</th>
						<td>
							' . $wpgeo->selectMapControl('menu', $wp_geo_options['default_map_control']). '<br />
							<p style="margin:1em 0 0 0;"><strong>' . __('Map Type Controls', 'wp-geo') . '</strong></p>
							<p style="margin:0;">' . __('You must select at least 2 map types for the control to show.', 'wp-geo') . '</p>
							' . $wpgeo->options_checkbox('show_map_type_normal', 'Y', $wp_geo_options['show_map_type_normal']) . ' ' . __('Normal map', 'wp-geo') . '<br />
							' . $wpgeo->options_checkbox('show_map_type_satellite', 'Y', $wp_geo_options['show_map_type_satellite']) . ' ' . __('Satellite (photographic map)', 'wp-geo') . '<br />
							' . $wpgeo->options_checkbox('show_map_type_hybrid', 'Y', $wp_geo_options['show_map_type_hybrid']) . ' ' . __('Hybrid (photographic map with normal features)', 'wp-geo') . '<br />
							' . $wpgeo->options_checkbox('show_map_type_physical', 'Y', $wp_geo_options['show_map_type_physical']) . ' ' . __('Physical (terrain map)', 'wp-geo') . '<br />
							<p style="margin:1em 0 0 0;"><strong>' . __('Other Controls', 'wp-geo') . '</strong></p>
							' . $wpgeo->options_checkbox('show_map_scale', 'Y', $wp_geo_options['show_map_scale']) . ' ' . __('Show map scale', 'wp-geo') . '<br />
							' . $wpgeo->options_checkbox('show_map_overview', 'Y', $wp_geo_options['show_map_overview']) . ' ' . __('Show collapsible overview map (in the corner of the map)', 'wp-geo') . '
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">' . __('Polylines', 'wp-geo') . '</th>
						<td>' . $wpgeo->options_checkbox('show_polylines', 'Y', $wp_geo_options['show_polylines']) . ' ' . __('Show polylines (to connect multiple points on a single map)', 'wp-geo') . '</td>
					</tr>
					<tr valign="top">
						<th scope="row">' . __('Polyline Colour', 'wp-geo') . '</th>
						<td><input name="polyline_colour" type="text" id="polyline_colour" value="' . $wp_geo_options['polyline_colour'] . '" size="7" /></td>
					</tr>
					<tr valign="top">
						<th scope="row">' . __('Show Maps On', 'wp-geo') . '</th>
						<td>
							' . $wpgeo->options_checkbox('show_maps_on_pages', 'Y', $wp_geo_options['show_maps_on_pages']) . ' ' . __('Pages', 'wp-geo') . '<br />
							' . $wpgeo->options_checkbox('show_maps_on_posts', 'Y', $wp_geo_options['show_maps_on_posts']) . ' ' . __('Posts (single posts)', 'wp-geo') . '<br />
							' . $wpgeo->options_checkbox('show_maps_on_home', 'Y', $wp_geo_options['show_maps_on_home']) . ' ' . __('Posts home page', 'wp-geo') . '<br />
							' . $wpgeo->options_checkbox('show_maps_in_datearchives', 'Y', $wp_geo_options['show_maps_in_datearchives']) . ' ' . __('Posts in date archives', 'wp-geo') . '<br />
							' . $wpgeo->options_checkbox('show_maps_in_categoryarchives', 'Y', $wp_geo_options['show_maps_in_categoryarchives']) . ' ' . __('Posts in category archives', 'wp-geo') . '<br />
							' . $wpgeo->options_checkbox('show_maps_in_searchresults', 'Y', $wp_geo_options['show_maps_in_searchresults']) . ' ' . __('Search Results', 'wp-geo') . '
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">' . __('Feeds', 'wp-geo') . '</th>
						<td>' . $wpgeo->options_checkbox('add_geo_information_to_rss', 'Y', $wp_geo_options['add_geo_information_to_rss']) . ' ' . __('Add geographic information', 'wp-geo') . '</td>
					</tr>
				</table>
				<p class="submit">
					<input type="submit" name="Submit" value="' . __('Save Changes', 'wp-geo') . '" />
					<input type="hidden" name="action" value="update" />
					<input type="hidden" name="option_fields" value="google_api_key,google_map_type,show_post_map" />
				</p>
				<h2 style="margin-top:30px;">' . __('Marker Settings', 'wp-geo') . '</h2>'
				. __('<p>Custom marker images are automatically created in your WordPress uploads folder and used by WP Geo.<br />A copy of these images will remain in the WP Geo folder in case you need to revert to them at any time.<br />You may edit these marker icons if you wish - they must be PNG files. Each marker consist of a marker image and a shadow image. If you do not wish to show a marker shadow you should use a transparent PNG for the shadow file.</p><p>Currently you must update these images manually and the anchor point must be the same - looking to provide more control in future versions.</p>', 'wp-geo') . '
				<table class="form-table">
					<tr valign="top">
						<th scope="row">' . __('Large Marker', 'wp-geo') . '</th>
						<td>
							<p style="margin:0px; background-image:url(' . $markers['large']['shadow'] . '); background-repeat:no-repeat;"><img src="' . $markers['large']['image'] . '" /></p>
							<p style="margin:10px 0 0 0;">
								' . __('This is the default marker used to indicate a location on most maps.', 'wp-geo') . '<br />
								{ width:' . $markers['large']['width'] . ', height:' . $markers['large']['height'] . ', anchorX:' . $markers['large']['anchorX'] . ', anchorY:' . $markers['large']['anchorY'] . ' }
							</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">' . __('Small Marker', 'wp-geo') . '</th>
						<td>
							<p style="margin:0px; background-image:url(' . $markers['small']['shadow'] . '); background-repeat:no-repeat;"><img src="' . $markers['small']['image'] . '" /></p>
							<p style="margin:10px 0 0 0;">
								' . __('This is the default marker used for the WP Geo sidebar widget.', 'wp-geo') . '<br />
								{ width:' . $markers['small']['width'] . ', height:' . $markers['small']['height'] . ', anchorX:' . $markers['small']['anchorX'] . ', anchorY:' . $markers['small']['anchorY'] . ' }
							</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">' . __('Dot Marker', 'wp-geo') . '</th>
						<td>
							<p style="margin:0px; background-image:url(' . $markers['dot']['shadow'] . '); background-repeat:no-repeat;"><img src="' . $markers['dot']['image'] . '" /></p>
							<p style="margin:10px 0 0 0;">
								' . __('This marker image is not currently used but it is anticipated that it will be used to indicate less important locations in a future versions of WP Geo.', 'wp-geo') . '<br />
								{ width:' . $markers['dot']['width'] . ', height:' . $markers['dot']['height'] . ', anchorX:' . $markers['dot']['anchorX'] . ', anchorY:' . $markers['dot']['anchorY'] . ' }
							</p>
						</td>
					</tr>
				</table>';
		if (function_exists('register_setting') && function_exists('settings_fields'))
		{
			settings_fields('wp-geo-options'); 
		}	
		echo '</form>
			<h2 style="margin-top:30px;">' . __('Documentation', 'wp-geo') . '</h2>'
			. __('<p>If you set the Show Post Map setting to &quot;Manual&quot;, you can use the Shortcode <code>[wp_geo_map]</code> in a post to display a map (if a location has been set for the post). You can only include the Shortcode once within a post. If you select another Show Post Map option then the Shortcode will be ignored and the map will be positioned automatically.</p>', 'wp-geo')
			. '<h2 style="margin-top:30px;">' . __('Feedback', 'wp-geo') . '</h2>'
			. sprintf(__("<p>If you experience any problems or bugs with the plugin, or want to suggest an improvement, please visit the <a %s>WP Geo Google Code page</a> to log your issue. If you would like to feedback or comment on the plugin please visit the <a %s>WP Geo plugin</a> page.</p>", 'wp-geo'), 'href="http://code.google.com/p/wp-geo/issues/list"', 'href="http://www.benhuson.co.uk/wordpress-plugins/wp-geo/"')
			. sprintf(__("<p>If you like WP Geo and would like to make a donation, please do so on the <a %s>WP Geo website</a>. Your contributions help to ensure that I can dedicate more time to the support and development of the plugin.</p>", 'wp-geo'), 'href="http://www.wpgeo.com/" target="_blank"') . '
		</div>';
		
	}
	
	
	
	/**
	 * Number Percent Or Px
	 */
	function numberPercentOrPx($str = false)
	{
	
		if (is_numeric($str))
		{
			$str .= 'px';
		}
		return $str;
	
	}



	/**
	 * Select Map Control
	 */
	function selectMapControl($return = 'array', $selected = '')
	{
		
		// Array
		$map_type_array = array(
			'GLargeMapControl' 	=> __('Large pan/zoom control', 'wp-geo'), 
			'GSmallMapControl' 	=> __('Smaller pan/zoom control', 'wp-geo'), 
			'GSmallZoomControl' => __('Small zoom control (no panning controls)', 'wp-geo'), 
			'' 					=> __('No pan/zoom controls', 'wp-geo')
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
			$menu = '<select name="default_map_control" id="default_map_control">' . $menu. '</select>';
			return $menu;
		}
		
		// Default return
		return $map_type_array;
		
	}
	
	
	
	/**
	 * Select Map Zoom
	 */
	function selectMapZoom($return = 'array', $selected = '')
	{
		
		// Array
		$map_type_array = array(
			'0' 	=> '0 - ' . __('Zoomed Out', 'wp-geo'), 
			'1' 	=> '1', 
			'2' 	=> '2', 
			'3' 	=> '3', 
			'4' 	=> '4', 
			'5' 	=> '5', 
			'6' 	=> '6', 
			'7' 	=> '7', 
			'8' 	=> '8', 
			'9' 	=> '9', 
			'10' 	=> '10', 
			'11' 	=> '11', 
			'12' 	=> '12', 
			'13' 	=> '13', 
			'14' 	=> '14', 
			'15' 	=> '15', 
			'16' 	=> '16', 
			'17' 	=> '17', 
			'18' 	=> '18', 
			'19' 	=> '19 - ' . __('Zoomed In', 'wp-geo'), 
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
			$menu = '<select name="default_map_zoom" id="default_map_zoom">' . $menu. '</select>';
			return $menu;
		}
		
		// Default return
		return $map_type_array;
		
	}
	


	/**
	 * Google Map Types
	 */
	function google_map_types($return = 'array', $selected = '')
	{
		
		// Array
		$map_type_array = array(
			'G_NORMAL_MAP' 		=> __('Normal', 'wp-geo'), 
			'G_SATELLITE_MAP' 	=> __('Satellite (photographic map)', 'wp-geo'), 
			'G_HYBRID_MAP' 		=> __('Hybrid (photographic map with normal features)', 'wp-geo'),
			'G_PHYSICAL_MAP' 	=> __('Physical (terrain map)', 'wp-geo')
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
			$menu = '<select name="google_map_type" id="google_map_type">' . $menu. '</select>';
			return $menu;
		}
		
		// Default return
		return $map_type_array;
		
	}



	/**
	 * Post Map Menu
	 */
	function post_map_menu($return = 'array', $selected = '')
	{
		
		// Array
		$map_type_array = array(
			'TOP' 		=> __('At top of post', 'wp-geo'), 
			'BOTTOM' 	=> __('At bottom of post', 'wp-geo'), 
			'HIDE' 		=> __('Manually', 'wp-geo')
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
			$menu = '<select name="show_post_map" id="show_post_map">' . $menu. '</select>';
			return $menu;
		}
		
		// Default return
		return $map_type_array;
		
	}
	
	
	
	/**
	 * Hook: after_plugin_row
	 */
	// This function can be used to insert text after the WP Geo plugin row on the plugins page.
	// Useful if you need to tell people something important before they upgrade.
	function after_plugin_row($plugin)
	{
		
		if ('wp-geo/wp-geo.php' == $plugin && !empty($this->plugin_message))
		{
			//echo '<td colspan="5" class="plugin-update" style="line-height:1.2em;">' . $this->plugin_message . '</td>';
			return;
		}
		
	}
	
	
	
	/**
	 * Hook: get_wpgeo_posts
	 */
	function get_wpgeo_posts($args = 'numberposts=5')
	{
		
		global $customFields;
		
		$default_args = array('numberposts' => 5);
		$arguments = wp_parse_args($args, $default_args);
		extract($arguments, EXTR_SKIP);
		
		$customFields = "'_wp_geo_longitude', '_wp_geo_latitude'";
		$customPosts = new WP_Query();
		
		add_filter('posts_join', array($this, 'get_custom_field_posts_join'));
		add_filter('posts_groupby', array($this, 'get_custom_field_posts_group'));
		
		$customPosts->query('showposts=' . $numberposts); // Uses same parameters as query_posts
		
		remove_filter('posts_join', array($this, 'get_custom_field_posts_join'));
		remove_filter('posts_groupby', array($this, 'get_custom_field_posts_group'));
		
		$points = array();
		
		while ($customPosts->have_posts()) : $customPosts->the_post();
			$id   = get_the_ID();
			$long = get_post_custom_values("_wp_geo_longitude");
			$lat  = get_post_custom_values("_wp_geo_latitude");
			$points[] = array('id' => $id, 'long' => $long, 'lat' => $lat);
		endwhile;
		
		return $points;
		
	}
	
	
	
	/**
	 * Get Custom Field Posts Join
	 */
	function get_custom_field_posts_join($join)
	{
	
		global $wpdb, $customFields;
		return $join . " JOIN $wpdb->postmeta postmeta ON (postmeta.post_id = $wpdb->posts.ID and postmeta.meta_key in ($customFields))";
	
	}
	
	
	
	/**
	 * Get Custom Field Posts Group
	 */
	function get_custom_field_posts_group($group)
	{
	
		global $wpdb;
		$group .= " $wpdb->posts.ID ";
		return $group;
		
	}
	
	
	
	/**
	 * GeoRSS Namespace
	 */
	function georss_namespace() 
	{
	
		global $wpgeo;
		
		if ($wpgeo->show_maps())
		{			
			echo 'xmlns:georss="http://www.georss.org/georss" xmlns:geo="http://www.w3.org/2003/01/geo/wgs84_pos#" xmlns:ymaps="http://api.maps.yahoo.com/Maps/V2/AnnotatedMaps.xsd"';
 		}
	
	}



	/**
	 * GeoRSS Tag
	 */
	function georss_item() 
	{
	
		global $wpgeo;
		
		if ($wpgeo->show_maps())
		{
			global $post;
			
			// Get the post
			$id = $post->ID;		
		
			// Get latitude and longitude
			$latitude = get_post_meta($post->ID, '_wp_geo_latitude', true);
			$longitude = get_post_meta($post->ID, '_wp_geo_longitude', true);
			
			// Need a map?
			if (is_numeric($latitude) && is_numeric($longitude))
			{
				echo '<georss:point>' . $latitude . ' ' . $longitude . '</georss:point>';
				echo '<geo:lat>' . $latitude . '</geo:lat>';
				echo '<geo:long>' . $longitude . '</geo:long>';
			}
		}
		
	}
	
	
	
	/* =============== Admin Edit Pages =============== */
	
	

	/**
	 * ---------- Add Custom Boxes ----------
	 * Adds a custom section to the "advanced" Post and Page edit screens
	 * using the admin_menu hook
	 */
	function add_custom_boxes()
	{
	
		if (function_exists( 'add_meta_box'))
		{
			add_meta_box('wpgeo_location', __('WP Geo Location', 'wpgeo'), array($this, 'wpgeo_location_inner_custom_box'), 'post', 'advanced');
			add_meta_box('wpgeo_location', __('WP Geo Location', 'wpgeo'), array($this, 'wpgeo_location_inner_custom_box'), 'page', 'advanced');
		}
		else
		{
			add_action('dbx_post_advanced', array($this, 'wpgeo_location_old_custom_box'));
			add_action('dbx_page_advanced', array($this, 'wpgeo_location_old_custom_box'));
		}
		
	}
	
	
	
	/**
	 * ---------- WP Geo Location: Inner Custom Box ----------
	 * Prints the inner fields for the custom post/page section.
	 */
	function wpgeo_location_inner_custom_box()
	{
		
		global $post;
		
		$latitude = get_post_meta($post->ID, '_wp_geo_latitude', true);
		$longitude = get_post_meta($post->ID, '_wp_geo_longitude', true);
		
		// Use nonce for verification
		echo '<input type="hidden" name="wpgeo_location_noncename" id="wpgeo_location_noncename" value="' . wp_create_nonce(plugin_basename(__FILE__)) . '" />';
		
		// The actual fields for data entry
		echo '<table cellpadding="3" cellspacing="5" class="form-table">
			<tr>
				<th scope="row">' . __('Search for location', 'wp-geo') . '<br /><span style="font-weight:normal;">(' . __('town, postcode or address', 'wp-geo') . ')</span></th>
				<td><input name="wp_geo_search" type="text" size="45" id="wp_geo_search" value="' . $search . '" /> <span class="submit"><input type="button" id="wp_geo_search_button" name="wp_geo_search_button" value="' . __('Search', 'wp-geo') . '" onclick="wp_geo_showAddress();" /></span></td>
			</tr>
			<tr>
				<td colspan="2">
				<div id="wp_geo_map" style="height:300px; width:100%; padding:0px; margin:0px;">
					Loading Google map...
				</div>
				</td>
			</tr>
			<tr>
				<th scope="row">' . __('Latitude', 'wp-geo') . ', ' . __('Longitude', 'wp-geo') . '<br /><a href="#" onclick="clearLatLngFields(); return false;">' . __('clear location', 'wp-geo') . '</a></th>
				<td><input name="wp_geo_latitude" type="text" size="25" id="wp_geo_latitude" value="' . $latitude . '" /> <input name="wp_geo_longitude" type="text" size="25" id="wp_geo_longitude" value="' . $longitude . '" /></td>
			</tr>
		</table>';
		
	}
	
	
	
	/**
	 * ---------- WP Geo Location: Old Custom Box ----------
	 * Prints the edit form for pre-WordPress 2.5 post/page.
	 */
	function wpgeo_location_old_custom_box()
	{
	
		echo '<div class="dbx-b-ox-wrapper">' . "\n";
		echo '<fieldset id="wpgeo_location_fieldsetid" class="dbx-box">' . "\n";
		echo '<div class="dbx-h-andle-wrapper"><h3 class="dbx-handle">' . __('WP Geo Location', 'wpgeo') . "</h3></div>";   
		echo '<div class="dbx-c-ontent-wrapper"><div class="dbx-content">';
		
		// output editing form
		wpgeo_location_inner_custom_box();
		
		echo "</div></div></fieldset></div>\n";
		
	}
	
	
	
	/**
	 * ---------- WP Geo Location: Save post data ----------
	 * When the post is saved, saves our custom data.
	 */
	function wpgeo_location_save_postdata($post_id)
	{
	
		// Verify this came from the our screen and with proper authorization,
		// because save_post can be triggered at other times
		if (!wp_verify_nonce($_POST['wpgeo_location_noncename'], plugin_basename(__FILE__)))
		{
			return $post_id;
		}
		
		// Authenticate user
		if ('page' == $_POST['post_type'])
		{
			if (!current_user_can('edit_page', $post_id))
				return $post_id;
		}
		else
		{
			if (!current_user_can('edit_post', $post_id))
				return $post_id;
		}
		
		// Find and save the data
		if (isset($_POST['wp_geo_latitude']) && isset($_POST['wp_geo_longitude']))
		{
			
			// Only delete post meta if isset (to avoid deletion in bulk/quick edit mode)
			delete_post_meta($post_id, '_wp_geo_latitude');
			delete_post_meta($post_id, '_wp_geo_longitude');
			
			if (is_numeric($_POST['wp_geo_latitude']) && is_numeric($_POST['wp_geo_longitude']))
			{
				add_post_meta($post_id, '_wp_geo_latitude', $_POST['wp_geo_latitude']);
				add_post_meta($post_id, '_wp_geo_longitude', $_POST['wp_geo_longitude']);
				
				$mydata = array('_wp_geo_latitude' => $_POST['wp_geo_latitude'], '_wp_geo_longitude' => $_POST['wp_geo_longitude']);
				return $mydata;
		
			}
			
		}
		
		return false;
	
	}
	
	
	
}



// Language
load_plugin_textdomain('wp-geo', PLUGINDIR . '/wp-geo/languages');

// Includes
include('wp-geo-markers.php');
include('wp-geo-map.php');
include( 'includes/class.feeds.php' );

// Admin Includes
if ( is_admin() )
{
	include_once( 'admin/class.editor.php' );
}

// Init.
$wpgeo = new WPGeo();

// Hooks
register_activation_hook(__FILE__, array($wpgeo, 'register_activation'));
add_shortcode('wp_geo_map', array($wpgeo, 'shortcode_wpgeo_map'));
add_shortcode('wpgeo_longitude', array($wpgeo, 'shortcode_wpgeo_longitude'));
add_shortcode('wpgeo_latitude', array($wpgeo, 'shortcode_wpgeo_latitude'));
add_action('wp_print_scripts', array($wpgeo, 'includeGoogleMapsJavaScriptAPI'));

// Frontend Hooks
add_action('wp_head', array($wpgeo, 'wp_head'));
add_filter('the_content', array($wpgeo, 'the_content'));

// Admin Hooks
add_action('init', array($wpgeo, 'init'));
add_action('admin_init', array($wpgeo, 'admin_init'));
add_action('admin_menu', array($wpgeo, 'admin_menu'));
add_action('admin_head', array($wpgeo, 'admin_head'));
add_action('after_plugin_row', array($wpgeo, 'after_plugin_row'));

add_filter('post_limits', array($wpgeo, 'post_limits'));
add_filter('posts_join', array($wpgeo, 'posts_join'));
add_filter('posts_where', array($wpgeo, 'posts_where'));

		
// More Includes
include('dashboard.php');
include('wp-geo-widget.php');
include('display.php');



?>