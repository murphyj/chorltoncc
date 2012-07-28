<?php



/**
 * @package WP Geo
 * @subpackage Includes > Feeds Class
 */



class WPGeo_Feeds
{
	
	
	
	/**
	 * @method       Constructor
	 * @description  Initialise the class.
	 */
	
	function WPGeo_Feeds()
	{
	
		$this->add_feed_hooks();
		
	}
	
	
	
	/**
	 * @method       Add Feed Hooks
	 * @description  Adds feed hooks to output GeoRSS info.
	 */
	
	function add_feed_hooks() 
	{
	
		add_action( 'rss2_ns', array( $this, 'georss_namespace' ) );
		add_action( 'atom_ns', array( $this, 'georss_namespace' ) );
		add_action( 'rdf_ns', array( $this, 'georss_namespace' ) );
		add_action( 'rss_item', array( $this, 'georss_item' ) );
		add_action( 'rss2_item', array( $this, 'georss_item' ) );
		add_action( 'atom_entry', array( $this, 'georss_item' ) );
		add_action( 'rdf_item', array( $this, 'georss_item' ) );
	
	}


	
	/**
	 * @method       GeoRSS Namespace
	 * @description  Adds the geo RSS namespace to the feed.
	 */
	
	function georss_namespace() 
	{
	
		global $wpgeo;
		
		if ( $wpgeo->show_maps() )
		{			
			echo 'xmlns:georss="http://www.georss.org/georss" xmlns:geo="http://www.w3.org/2003/01/geo/wgs84_pos#" xmlns:ymaps="http://api.maps.yahoo.com/Maps/V2/AnnotatedMaps.xsd"';
 		}
	
	}
	
	

	/**
	 * @method  GeoRSS Item
	 * @description  Adds geo RSS nodes to the feed item.
	 */
	
	function georss_item() 
	{
	
		global $wpgeo;
		
		if ( $wpgeo->show_maps() )
		{
		
			global $post;
			
			// Get the post
			$id = $post->ID;		
		
			// Get latitude and longitude
			$latitude  = get_post_meta( $post->ID, '_wp_geo_latitude', true );
			$longitude = get_post_meta( $post->ID, '_wp_geo_longitude', true );
			
			// Need a map?
			if ( is_numeric($latitude) && is_numeric($longitude) )
			{
				echo '<georss:point>' . $latitude . ' ' . $longitude . '</georss:point>';
				echo '<geo:lat>' . $latitude . '</geo:lat>';
				echo '<geo:long>' . $longitude . '</geo:long>';
			}
			
		}
		
	}
	
	
	
}



?>