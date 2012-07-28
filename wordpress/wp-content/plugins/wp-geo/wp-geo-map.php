<?php



/**
 * The WP Geo Map class
 */
class WPGeoMap
{
	
	
	
	/**
	 * Properties
	 */
	
	var $id;
	var $points;
	var $zoom = 5;
	var $maptype = 'G_NORMAL_MAP';
	var $maptypes;
	var $mapcontrol = 'GLargeMapControl';
	var $show_map_scale = false;
	var $show_map_overview = false;
	var $show_polyline = false;
	
	
	
	/**
	 * Constructor
	 */
	function WPGeoMap($id)
	{
		
		$this->id = $id;
		$this->maptypes = array();
		$this->points = array();
		
	}
	
	
	
	/**
	 * Render Map Javascript
	 */
	function renderMapJS($map_id = false)
	{
	
		$wp_geo_options = get_option('wp_geo_options');
		
		// ID of div for map output
		$map_id = $map_id ? $map_id : $this->id;
		$div = 'wp_geo_map_' . $map_id;
		
		// Map Types
		$maptypes = $this->maptypes;
		$maptypes[] = $this->maptype;
		$maptypes = array_unique($maptypes);
		$js_maptypes = '';
		if (in_array('G_PHYSICAL_MAP', $maptypes))
			$js_maptypes .= 'map_' . $map_id . '.addMapType(G_PHYSICAL_MAP);';
		if (!in_array('G_NORMAL_MAP', $maptypes))
			$js_maptypes .= 'map_' . $map_id . '.removeMapType(G_NORMAL_MAP);';
		if (!in_array('G_SATELLITE_MAP', $maptypes))
			$js_maptypes .= 'map_' . $map_id . '.removeMapType(G_SATELLITE_MAP);';
		if (!in_array('G_HYBRID_MAP', $maptypes))
			$js_maptypes .= 'map_' . $map_id . '.removeMapType(G_HYBRID_MAP);';
		
		// Markers
		$js_markers = '';
		if (count($this->points) > 0)
		{
			for ($i = 0; $i < count($this->points); $i++)
			{
				$js_markers .= 'var marker_' . $map_id .'_' . $i . ' = new wpgeo_createMarker2(map_' . $map_id . ', new GLatLng(' . $this->points[$i]['latitude'] . ', ' . $this->points[$i]['longitude'] . '), ' . $this->points[$i]['icon'] . ', \'' . addslashes($this->points[$i]['title']) . '\', \'' . $this->points[$i]['link'] . '\');' . "\n";
				$js_markers .= 'bounds.extend(new GLatLng(' . $this->points[$i]['latitude'] . ', ' . $this->points[$i]['longitude'] . '));';
			}
		}
		
		// Show Polyline
		$js_polyline = '';
		if ($wp_geo_options['show_polylines'] == 'Y')
		{
			if ($this->show_polyline)
			{
				if (count($this->points) > 1)
				{
					$polyline_coords = '';
					for ($i = 0; $i < count($this->points); $i++)
					{
						if ($i > 0)
						{
							$polyline_coords .= ',';
						}
						$polyline_coords .= 'new GLatLng(' . $this->points[$i]['latitude'] . ', ' . $this->points[$i]['longitude'] . ')' . "\n";
					}
					$js_polyline = 'var polyOptions = {geodesic:true};' . "\n";
					$js_polyline .= 'var polyline = new GPolyline([' . $polyline_coords . '], "' . $wp_geo_options['polyline_colour'] . '", 2, 0.5, polyOptions);' . "\n";
					$js_polyline .= 'map_' . $map_id . '.addOverlay(polyline);' . "\n";
				}
			}
		}
		
		
		
		// Zoom
		$js_zoom = '';
		if (count($this->points) > 1)
		{
			$js_zoom .= 'map_' . $map_id . '.setCenter(bounds.getCenter(), map_' . $map_id . '.getBoundsZoomLevel(bounds));';
		}
		if (count($this->points) == 1)
		{
			$js_zoom .= 'map_' . $map_id . '.setCenter(marker_' . $map_id . '_0.getLatLng());';
		}
		
		// Controls
		$js_controls = '';
		if ($this->show_map_scale)
			$js_controls .= 'map_' . $map_id . '.addControl(new GScaleControl());';
		if ($this->show_map_overview)
			$js_controls .= 'map_' . $map_id . '.addControl(new GOverviewMapControl());';
		
		// Map Javascript
		$js = '
			if (document.getElementById("' . $div . '"))
			{
				var bounds = new GLatLngBounds();
    
				map_' . $map_id . ' = new GMap2(document.getElementById("' . $div . '"));
				var center = new GLatLng(' . $this->points[0]['latitude'] . ', ' . $this->points[0]['longitude'] . ');
				map_' . $map_id . '.setCenter(center, ' . $this->zoom . ');
				
				' . $js_maptypes . '
				map_' . $map_id . '.setMapType(' . $this->maptype . ');
				
				var mapTypeControl = new GMapTypeControl();
				map_' . $map_id . '.addControl(mapTypeControl);';
		if ($this->mapcontrol != "")
		{
			$js .= 'map_' . $map_id . '.addControl(new ' . $this->mapcontrol . '());';
		}
		$js .= '
				var center_' . $map_id .' = new GLatLng(' . $this->points[0]['latitude'] . ', ' . $this->points[0]['longitude'] . ');
				
				' . $js_markers . '
				' . $js_polyline . '
    			' . $js_zoom . '
    			' . $js_controls . '
				
			}';
		
		return $js;
		
	}
	
	
	
	/**
	 * Add Point
	 */
	function addPoint($lat, $long, $icon = 'wpgeo_icon_large', $title = '', $link = '')
	{
	
		// Save point data
		$this->points[] = array(
			'latitude'  => $lat, 
			'longitude' => $long,
			'icon' => $icon,
			'title' => $title,
			'link' => $link,
		);
	
	}
	
	
	
	/**
	 * Show Polyline
	 */
	function showPolyline($bool = true)
	{
	
		$this->show_polyline = $bool;
		
	}
	
	
	
	/**
	 * Set Map Control
	 */
	function setMapControl($mapcontrol = 'GLargeMapControl')
	{
	
		$this->mapcontrol = $mapcontrol;
		
	}
	
	
	
	/**
	 * Set Map Type
	 */
	function setMapType($maptype = 'G_NORMAL_MAP')
	{
	
		$this->maptype = $maptype;
		
	}
	
	
	
	/**
	 * Add Map Type
	 */
	function addMapType($maptype)
	{
	
		$this->maptypes[] = $maptype;
		$this->maptypes = array_unique($this->maptypes);
		
	}
	
	
	
	/**
	 * Set Map Zoom
	 */
	function setMapZoom($zoom = 5)
	{
	
		$this->zoom = $zoom;
		
	}
	
	
	
	/**
	 * Show Map Scale
	 */
	function showMapScale($bool = true)
	{
	
		$this->show_map_scale = $bool;
		
	}
	
	
	
	/**
	 * Show Map Overview
	 */
	function showMapOverview($bool = true)
	{
	
		$this->show_map_overview = $bool;
		
	}
	
	

}



?>