<?php



/**
 * The WP Geo Display class
 */
class WPGeoDisplay
{
	
	
	
	/**
	 * Properties
	 */
	
	var $maps;
	var $n = 0;
	
	
	
	/**
	 * Constructor
	 */
	function WPGeoDisplay()
	{
		
		$this->maps = array();
		
	}
	
	
	
	/**
	 * Get ID
	 */
	function getID()
	{
		
		$this->n++;
		return $this->n;
		
	}
	
	
	
	/**
	 * Add Map
	 */
	function addMap($args)
	{
		
		$this->maps[] = $args;
		
	}
	
	
	
	/**
	 * Render
	 */
	function render()
	{
		
		if ( count( $this->maps ) > 0 ) {
		
			echo '
				<script type="text/javascript">
				
				function renderWPGeo() {
					if (GBrowserIsCompatible()) {
					';
			foreach ( $this->maps as $map )
			{
				echo '
					map = new GMap2(document.getElementById("wpgeo-' . $map['id'] . '"));
					map.setCenter(new GLatLng(41.875696,-87.624207), 3);
					geoXml = new GGeoXml("' . $map['rss'] . '");
					GEvent.addListener(geoXml, "load", function() {
						geoXml.gotoDefaultViewport(map);
					});
					map.addOverlay(geoXml);
					';
			}
			echo '}
				}
			
				if (document.all&&window.attachEvent) { // IE-Win
					window.attachEvent("onload", function () { renderWPGeo(); });
					window.attachEvent("onunload", GUnload);
				} else if (window.addEventListener) { // Others
					window.addEventListener("load", function () { renderWPGeo(); }, false);
					window.addEventListener("unload", GUnload, false);
				}
				
				</script>
				';
		
		}
		
	}
	
	
	
	/**
	 * [wpgeo] Shortcode
	 */
	function shortcode_wpgeo($atts, $content = null)
	{
	
		$allowed_atts = array(
			'rss' => null,
			'kml' => null
		);
		extract(shortcode_atts($allowed_atts, $atts));
		
		if ($kml != null)
		{
			$rss = $kml;
		}
		
		if ($rss != null)
		{
			$id = $this->getID();
			$map = array(
				'id' => $id,
				'rss' => $rss
			);
			$this->addMap($map);
			$wp_geo_options = get_option('wp_geo_options');
			return '<div id="wpgeo-' . $id . '" class="wpgeo wpgeo-rss" style="width:' . $wp_geo_options['default_map_width'] . '; height:' . $wp_geo_options['default_map_height'] . ';">' . $rss . '</div>';
		}
		
		return '';
		
	}
	
	
	
}



$WPGeoDisplay = new WPGeoDisplay();



// Hooks
add_action('wp_footer', array($WPGeoDisplay, 'render'));
add_shortcode('wpgeo', array($WPGeoDisplay, 'shortcode_wpgeo'));



?>