<?php namespace SimpleLocator\Dependencies;
use SimpleLocator\Repositories\MapStyles;
use \SimpleLocator\Repositories\SettingsRepository;

/**
* Register & Enqueue Styles & Scripts
*/
class Dependencies {

	/**
	* Plugin Directory
	*/
	private $plugin_dir;

	/**
	* Map Styles Repository
	* @var object
	*/
	private $styles_repo;

	/**
	* Plugin Version
	*/
	private $version;

	/**
	* Post Type for Locations
	*/
	private $post_type;

	/**
	* Settings Repository
	*/
	private $settings_repo;


	public function __construct()
	{
		$this->styles_repo = new MapStyles;
		$this->settings_repo = new SettingsRepository();
		$this->setVersion();
		$this->setPostType();
		$this->plugin_dir = \SimpleLocator\Helpers::plugin_url();
		add_action( 'admin_enqueue_scripts', array( $this, 'adminStyles' ));
		add_action( 'admin_enqueue_scripts', array( $this, 'adminScripts' ));
		add_action( 'wp_enqueue_scripts', array( $this, 'styles' ));
		add_action( 'wp_enqueue_scripts', array( $this, 'scripts' ));
	}


	/**
	* Admin Styles
	*/
	public function adminStyles()
	{
		wp_enqueue_style(
			'simplelocator', 
			$this->plugin_dir . '/assets/css/simple-locator-admin.css', 
			array(), 
			$this->version
		);
	}

	/**
	* Set the Plugin Version for dependency versioning
	*/
	private function setVersion()
	{
		global $simple_locator_version;
		$this->version = $simple_locator_version;
	}

	/**
	* Set the Post Type from Options
	* @since 1.0.6
	*/
	private function setPostType()
	{
		$labels = get_option('wpsl_posttype_labels');
		$this->post_type = ( isset($labels['name']) ) ? $labels['name'] : 'location';
	}


	/**
	* Admin Scripts
	*/
	public function adminScripts()
	{
		$screen = get_current_screen();
		if ( ($screen->post_type == $this->post_type) || ($screen->id == 'settings_page_wp_simple_locator') ) {
			$this->addGoogleMaps();
			wp_enqueue_script('jquery-ui-core');
			wp_enqueue_style('jquery-ui-sortable');
			wp_enqueue_script('google-maps');
			wp_enqueue_script('media-upload');
			wp_enqueue_script('thickbox');
			wp_enqueue_style('thickbox');
			wp_enqueue_script(
				'simple-locator-admin', 
				$this->plugin_dir . '/assets/js/simple-locator-admin.js', 
				array('jquery', 'jquery-ui-sortable'), 
				$this->version
			);
			$data = array( 
				'locatorNonce' => wp_create_nonce( 'wpsl_locator-locator-nonce' ),
				'upload' => __('Upload', 'wpsimplelocator'),
				'remove' => __('Remove', 'wpsimplelocator'),
				'posttype' => $this->post_type
			);
			$data = $this->importVars($data);
			wp_localize_script( 
				'simple-locator-admin', 
				'wpsl_locator', 
				$data
			);
		}

		// Map Style Choices
		if ( ($screen->id == 'settings_page_wp_simple_locator') && (isset($_GET['tab'])) && ($_GET['tab'] == 'map') ){
			wp_enqueue_script(
				'simple-locator-admin-maps', 
				$this->plugin_dir . '/assets/js/simple-locator-admin-maps.js', 
				array('jquery'), 
				$this->version
			);
			wp_localize_script( 
				'simple-locator-admin-maps', 
				'wpsl_locator_mapstyles', 
				$this->mapStyleData()
			);
		}

		// Default Map
		if ( ($screen->id == 'settings_page_wp_simple_locator') && (isset($_GET['tab'])) && ($_GET['tab'] == 'defaultmap') ){
			wp_enqueue_script(
				'simple-locator-admin-defaultmap', 
				$this->plugin_dir . '/assets/js/simple-locator-admin-defaultmap.js', 
				array('jquery'), 
				$this->version
			);
			wp_localize_script( 
				'simple-locator-admin-defaultmap', 
				'wpsl_locator_defaultmap', 
				array(
					'enabled' => $this->settings_repo->showDefaultMap(),
					'latitude' => $this->settings_repo->defaultMap('latitude'),
					'longitude' => $this->settings_repo->defaultMap('longitude'),
					'zoom' => intval($this->settings_repo->defaultMap('zoom')),
					'searchtext' => __('Search for a location', 'wpsimplelocator'),
					'styles' => $this->styles_repo->getLocalizedStyles(),
					'mappin' => get_option('wpsl_map_pin')
				)
			);
		}
	}


	/**
	* Front End Styles
	*/
	public function styles()
	{
		if ( get_option('wpsl_output_css') == "true" ){
			wp_enqueue_style(
				'simple-locator', 
				$this->plugin_dir . '/assets/css/simple-locator.css', 
				'', 
				$this->version
			);
		}
	}


	/**
	* Front End Scripts
	*/
	public function scripts()
	{
		wp_enqueue_script('jquery');
		$this->addGoogleMaps();

		wp_register_script(
			'simple-locator', 
			$this->plugin_dir . '/assets/js/simple-locator.js', 
			'jquery', $this->version, 
			true
		);

		$localized_data = array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'locatorNonce' => wp_create_nonce( 'wpsl_locator-locator-nonce' ),
			'distance' => __( 'Distance', 'wpsimplelocator' ), 
			'website' => __('Website', 'wpsimplelocator'),
			'location' => __('location', 'wpsimplelocator'),
			'locations' => __('locations', 'wpsimplelocator'),
			'found_within' => __('found within', 'wpsimplelocator'),
			'phone' => __('Phone', 'wpsimplelocator'),
			'showonmap' => __('Show on Map', 'wpsimplelocator'),
			'viewlocation' => __('View Location', 'wpsimplelocator'),
			'notfounderror' => __('Address not found', 'wpsimplelocator'),
			'mappin' => get_option('wpsl_map_pin'),
			'showgeobutton'=> $this->settings_repo->showGeoButton('enabled'),
			'geobuttontext'=> $this->settings_repo->showGeoButton('text'),
			'yourlocation' => __('your location', 'wpsimplelocator'),
			'default_enabled' => $this->settings_repo->showDefaultMap(),
			'default_latitude' => $this->settings_repo->defaultMap('latitude'),
			'default_longitude' => $this->settings_repo->defaultMap('longitude'),
			'default_zoom' => intval($this->settings_repo->defaultMap('zoom')),
			'default_user_center' => $this->settings_repo->defaultMap('user_location')
		);
		$localized_data['mapstyles'] = $this->styles_repo->getLocalizedStyles();
		wp_localize_script( 
			'simple-locator', 
			'wpsl_locator', 
			$localized_data
		);
	}


	/**
	* Get Map Style Data
	* for use in settings page display of google maps
	*/
	private function mapStyleData()
	{
		return $this->styles_repo->getAllStyles();
	}


	/**
	* Register the Google Maps Script
	* Only Enqueue when needed
	* @param $override boolean - whether to check the option or not
	*/
	private function addGoogleMaps()
	{
		if ( !$this->settings_repo->outputGMaps() ) return;
		$maps_url = 'http://maps.google.com/maps/api/js?';
		$maps_url .= ( get_option('wpsl_google_api_key') ) ? 'key=' . get_option('wpsl_google_api_key') . '&' : '';
		$maps_url .= 'sensor=false';

		wp_register_script(
			'google-maps', 
			$maps_url
		);
	}


	/**
	* Add vars needed for importing process
	*/
	private function importVars($data)
	{
		if ( !isset($_GET['tab']) || $_GET['tab'] !== "import" || !isset($_GET['step']) ){
			$data['isimport'] = "false";
			return $data;
		} 

		$data['isimport'] = "true";
		$data['importstep'] = $_GET['step'];
		$data['Row'] = 'Showing Row';
		$data['pause'] = __('Pause', 'wpsimplelocator');
		$data['pause_continue'] = __('Continue', 'wpsimplelocator');
		return $data;
	}


}