<?php

/**
 * Mimo Colors.
 *
 * @package   Mimo_Colors_Display
 * @author    Mimo <mail@mimo.studio>
 * @license   GPL-2.0+
 * @link      http://mimo.studio
 * @copyright 2015 Mimo
 */

/**
 *
 * @package Mimo_Colors_Display
 * @author  Mimo <mail@mimo.studio>
 */
class Mimo_Colors_Display {

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since   1.0.0
	 *
	 * @var     string
	 */
	const VERSION = '1.0.0';

	/**
	 	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected static $plugin_slug = 'mimo-colors';
	protected static $plugin_prefix = 'mimo_colors';

	/**
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected static $plugin_name = 'Mimo Colors';

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Array of cpts of the plugin
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected $cpts = array( 'mimo_colors_location' );

	/**
	 * Array of capabilities by roles
	 * 
	 * @since 1.0.0
	 * 
	 * @var array
	 */


	protected static $plugin_roles = array(
		'editor' => array(
			'edit_demo' => true,
			'edit_others_demo' => true,
		),
		'author' => array(
			'edit_demo' => true,
			'edit_others_demo' => false,
		),
		'subscriber' => array(
			'edit_demo' => false,
			'edit_others_demo' => false,
		),
	);

	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {
		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		// Activate plugin when new blog is added
		add_action( 'wpmu_new_blog', array( $this, 'activate_new_site' ) );

		
		
		
		// Load public-facing style sheet and JavaScript.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_js_vars' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'display_color'  ) );
		
	}

	/**
	 * Return the plugin slug.
	 *
	 * @since    1.0.0
	 *
	 * @return    Plugin slug variable.
	 */
	public function get_plugin_slug() {
		return self::$plugin_slug;
	}

	public function get_plugin_prefix() {
		return self::$plugin_prefix;
	}

	/**
	 * Return the plugin name.
	 *
	 * @since    1.0.0
	 *
	 * @return    Plugin name variable.
	 */
	public function get_plugin_name() {
		return self::$plugin_name;
	}

	/**
	 * Return the version
	 *
	 * @since    1.0.0
	 *
	 * @return    Version const.
	 */
	public function get_plugin_version() {
		return self::VERSION;
	}

	/**
	 * Return the cpts
	 *
	 * @since    1.0.0
	 *
	 * @return    Cpts array
	 */
	public function get_cpts() {
		return $this->cpts;
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	
	/**
	 * Fired when the plugin is activated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Activate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       activated on an individual blog.
	 */
	public static function activate( $network_wide ) {
		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( $network_wide ) {

				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {

					switch_to_blog( $blog_id );
					self::single_activate();

					restore_current_blog();
				}
			} else {
				self::single_activate();
			}
		} else {
			self::single_activate();
		}
	}

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Deactivate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       deactivated on an individual blog.
	 */
	public static function deactivate( $network_wide ) {
		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( $network_wide ) {

				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {

					switch_to_blog( $blog_id );
					self::single_deactivate();

					restore_current_blog();
				}
			} else {
				self::single_deactivate();
			}
		} else {
			self::single_deactivate();
		}
	}

	/**
	 * Fired when a new site is activated with a WPMU environment.
	 *
	 * @since    1.0.0
	 *
	 * @param    int    $blog_id    ID of the new blog.
	 */
	public function activate_new_site( $blog_id ) {
		if ( 1 !== did_action( 'wpmu_new_blog' ) ) {
			return;
		}

		switch_to_blog( $blog_id );
		self::single_activate();
		restore_current_blog();
	}

	/**
	 * Fired when a new site is activated to see if it is licensed.
	 *
	 * @since    1.0.0
	 *
	 * @param    int    $blog_id    ID of the new blog.
	 */
	public function is_licensed(  ) {
		$general_settings = get_option( self::$plugin_prefix . '_settings' );
    	
    	$mimo_colors_settings_api_key = $general_settings[self::$plugin_prefix . '_settings_mimo_key'];
    	if($mimo_colors_settings_api_key) { $is_licensed =  true ; } else {$is_licensed =  true ;};
    	return $is_licensed;
	}
	
	/**
	 * Add support for custom CPT on the search box
	 *
	 * @since    1.0.0
	 *
	 * @param    object    $query   
	 */
	public function filter_search( $query ) {
		//if ( $query->is_search ) {
			//Mantain support for post
			//$this->cpts[] = 'post';
			//$query->set( 'post_type', $this->cpts );
		//}
		//return $query;
	}

	/**
	 * Get all blog ids of blogs in the current network that are:
	 * - not archived
	 * - not spam
	 * - not deleted
	 *
	 * @since    1.0.0
	 *
	 * @return   array|false    The blog ids, false if no matches.
	 */
	private static function get_blog_ids() {

		global $wpdb;

		// get an array of blog ids
		$sql = "SELECT blog_id FROM $wpdb->blogs
			WHERE archived = '0' AND spam = '0'
			AND deleted = '0'";

		return $wpdb->get_col( $sql );
	}

	/**
	 * Fired for each blog when the plugin is activated.
	 *
	 * @since    1.0.0
	 */
	private static function single_activate() {
		//Requirements Detection System - read the doc/example in the library file
		require_once( plugin_dir_path( __FILE__ ) . 'includes/requirements.php' );
		new Wpcolors_Requirements( self::$plugin_name, self::$plugin_slug, array(
			'WP' => new WordPress_Requirement( '4.1.0' )
				) );

		//Define activation functionality here
		
		global $wp_roles;
		if ( !isset( $wp_roles ) ) {
			$wp_roles = new WP_Roles;
		}

		foreach ( $wp_roles->role_names as $role => $label ) {
			//if the role is a standard role, color the default caps, otherwise, color as a subscriber
			$caps = ( array_key_exists( $role, self::$plugin_roles ) ) ? self::$plugin_roles[ $role ] : self::$plugin_roles[ 'subscriber' ];

			//loop and assign
			foreach ( $caps as $cap => $grant ) {
				//check to see if the user already has this capability, if so, don't re-add as that would override grant
				if ( !isset( $wp_roles->roles[ $role ][ 'capabilities' ][ $cap ] ) ) {
					$wp_roles->add_cap( $role, $cap, $grant );
				}
			}
		}
		//Clear the permalinks
		flush_rewrite_rules();
	}

	/**
	 * Fired for each blog when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 */
	private static function single_deactivate() {
		//Clear the permalinks
		flush_rewrite_rules();
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {
		$domain = 'mimo-colors';
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, FALSE, basename( plugin_dir_path( dirname( __FILE__ ) ) ) . '/languages/' );
	}

	/**
	 * Register and enqueue public-facing style sheet.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_prefix . '-style', plugins_url( 'assets/css/mimo-colors-public.min.css', __FILE__ ), array(), self::VERSION );
		

	}

	/**
	 * Register and enqueues public-facing JavaScript files.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		
		 wp_enqueue_script( self::$plugin_prefix, plugins_url( 'assets/js/mimo-colors.min.js', __FILE__ ), array( 'jquery' ), self::VERSION );
	    
		
	}

	/**
	 * Single View for CPT Colors
	 *
	 * @since    1.0.0
	 */
	/* Filter the single_template with our custom function*/
	public static function search($id, $array) {
		$i = 0;
		   foreach ( $array as $key => $val) {
		       if ($key === $id) {
		           return $val;
		       }
		   }
		   return null;
		   $i++;
		}


	

	

	

	

	/**
	 * Add class in the body on the frontend
	 *
	 * @since    1.0.0
	 */
	public function add_pn_class( $classes ) {
		$classes[] = $this->get_plugin_slug();
		return $classes;
	}

	//TODO Set default values in function to be overwritten by developers
	public static function display_color(  ) {
		
		$mimo_colors_css = '';
		//Get Settings
   		
   		$general_settings = get_option( self::$plugin_prefix . '_settings' );
   		if (isset($general_settings[self::$plugin_prefix . '_all_colors' ] ) ) $all_colors = $general_settings[self::$plugin_prefix . '_all_colors' ];
    	if(isset($all_colors)) :

			
			foreach( (array) $all_colors as $key => $entry){

				
		    	if ( isset( $entry[self::$plugin_prefix . '_bg_color'] ) ) {	$mimo_colors_bg_color = esc_html( $entry[self::$plugin_prefix . '_bg_color'] ) ;	} else {  	$mimo_colors_bg_color = '#606060' ; };
		    	if ( isset( $entry[self::$plugin_prefix . '_text_color'] ) ) {	$mimo_colors_text_color = esc_html( $entry[self::$plugin_prefix . '_text_color']  ) ;	} else {  	$mimo_colors_text_color = '#ffffff' ; };
		    	if ( isset( $entry[self::$plugin_prefix . '_class'] ) ) {	$mimo_colors_class = esc_html( $entry[self::$plugin_prefix . '_class']  ) ;	} else {  	$mimo_colors_class = '' ; };

		    	

		    	
				
				$mimo_colors_css .= "

					{$mimo_colors_class}
					{
						background:{$mimo_colors_bg_color} ;
						color:{$mimo_colors_text_color};
					}

					

					

					
					
					";
			

			}

		endif;


	    

		
		wp_enqueue_style( self::$plugin_prefix . '_plugin_mimo_colors_styles', plugins_url( 'assets/css/mimo-colors-custom-styles.css', __FILE__ ), array(), self::VERSION );
		$mimo_colors_css .= '';
		wp_add_inline_style( self::$plugin_prefix . '_plugin_mimo_colors_styles', $mimo_colors_css );

		
		

		

}	
	
	public function enqueue_js_vars() {
		
	}
	

	/**
	 * NOTE:  Actions are points in the execution of a page or process
	 *        lifecycle that WordPress fires.
	 *
	 *        Actions:    http://codex.wordpress.org/Plugin_API#Actions
	 *        Reference:  http://codex.wordpress.org/Plugin_API/Action_Reference
	 *
	 * @since    1.0.0
	 */


	public function action_method_name() {
		// Define your action hook callback here
	}

	/**
	 * NOTE:  Filters are points of execution in which WordPress modifies data
	 *        before saving it or sending it to the browser.
	 *
	 *        Filters: http://codex.wordpress.org/Plugin_API#Filters
	 *        Reference:  http://codex.wordpress.org/Plugin_API/Filter_Reference
	 *
	 * @since    1.0.0
	 */


	public function filter_method_name() {
		// Define your filter hook callback here
	}


	

	/**
	 *
	 *        Reference:  http://codex.wordpress.org/Shortcode_API
	 *
	 * @since    1.0.0
	 */


	

}
