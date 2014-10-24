<?php
/**
* The Locations Post Type
*/
class SL_PostType {

	function __construct()
	{
		add_action( 'init', array( $this, 'register_post_type') );
		add_filter( 'manage_location_posts_columns', array($this,'locations_table_head'));
		add_action( 'manage_location_posts_custom_column', array($this, 'locations_table_columns'), 10, 2);
	}


	/**
	* Register the Location Post Type
	*/
	public function register_post_type()
	{
		if ( $this->check_post_type() ){
			$show_ui = false;
		} else {
			$show_ui = true;
		}

		$labels = array(
			'name' => __('Locations'),  
		    'singular_name' => __('Location'),
			'add_new_item'=> 'Add Location',
			'edit_item' => 'Edit Location',
			'view_item' => 'View Location'
		);
		$args = array(
		 	'labels' => $labels,
		    'public' => true,  
		    'show_ui' => $show_ui,
			'menu_position' => 5,
		    'capability_type' => 'post',  
		    'hierarchical' => false,  
		    'has_archive' => true,
		    'supports' => array('title','editor','thumbnail'),
		    'rewrite' => array('slug' => 'location', 'with_front' => false)
		);
		register_post_type( 'location' , $args );

	}


	/**
	* Check the post type option
	* If the option is not set to the location type, remove it from the menu
	*/
	public function check_post_type()
	{
		$pt = get_option('wpsl_post_type');
		if ( $pt !== 'location' ){
			return true;
		}
	}


	/**
	* Locations Admin Table Head
	*/	
	public function locations_table_head( $defaults )
	{
	    $defaults['address']  = 'Address';
	    $defaults['phone']    = 'Phone';
	    $defaults['website']  = 'Website';
	    return $defaults;
	}

	
	/**
	* Locations Admin Table Columns
	*/	
	public function locations_table_columns($column_name, $post_id)
	{
		if ( $column_name == 'address' ){
			$address = get_post_meta($post_id, 'wpsl_address', true);
			$city = get_post_meta($post_id, 'wpsl_city', true);
			$state = get_post_meta($post_id, 'wpsl_state', true);
			$zip = get_post_meta($post_id, 'wpsl_zip', true);
			echo $address . '<br />' . $city . ', ' . $state . ' ' . $zip;
		}
		if ( $column_name == 'phone' ){
			echo get_post_meta($post_id, 'wpsl_phone', true);
		}
		if ( $column_name == 'website' ){
			echo get_post_meta($post_id, 'wpsl_website', true);
		}
	}

}