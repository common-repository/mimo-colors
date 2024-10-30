<?php
/**
 * Mimo Colors.
 *
 * @package   Mimo_Colors_Display
 * @author    Mimo <mail@mimo.studio>
 * @license   GPL-2.0+
 * @link      http://imo.media
 * @copyright 2015 Mimo
 */

/**
 * Plugin class. This class should ideally be used to work with the
 * administrative side of the WordPress site.
 *
 * If you're interested in introducing public-facing
 * functionality, then refer to `class-mimo-colors.php`
 *
 * 
 *
 * @package Mimo_Colors_Admin
 * @author  Mimo <mail@mimo.studio>
 */
class Mimo_Colors_Admin {

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Slug of the plugin screen.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_screen_hook_suffix = null;

	/**
	 * Initialize the plugin by loading admin scripts & styles and adding a
	 * settings page and menu.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {

		/*
		 *
		 * - Uncomment following lines if the admin class should only be available for super admins
		 */
		/* if( ! is_super_admin() ) {
		  return;
		  } */

		
		$plugin = Mimo_Colors_Display::get_instance();
		$this->plugin_slug = $plugin->get_plugin_slug();
		$this->plugin_prefix = $plugin->get_plugin_prefix();
		$this->plugin_name = $plugin->get_plugin_name();
		$this->version = $plugin->get_plugin_version();
		$this->cpts = $plugin->get_cpts();

		// Load admin style sheet and JavaScript.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
		// Load admin style in dashboard for the At glance widget
		add_action( 'admin_head-index.php', array( $this, 'enqueue_admin_styles' ) );

		
		// Add the options page and menu item.
		add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );
		//Add bubble notification for cpt pending
		add_action( 'admin_menu', array( $this, 'pending_cpt_bubble' ), 999 );

		// Add an action link pointing to the options page.
		$plugin_basename = plugin_basename( plugin_dir_path( realpath( dirname( __FILE__ ) ) ) . $this->plugin_slug . '.php' );
		add_filter( 'plugin_action_links_' . $plugin_basename, array( $this, 'add_action_links' ) );



		

		//Add the export settings method
		add_action( 'admin_init', array( $this, 'settings_export' ) );
		//Add the import settings method
		add_action( 'admin_init', array( $this, 'settings_import' ) );


		

		/*
		 * Load Wp_Contextual_Help for the help tabs
		 */
		add_filter( 'wp_contextual_help_docs_dir', array( $this, 'help_docs_dir' ) );
		add_filter( 'wp_contextual_help_docs_url', array( $this, 'help_docs_url' ) );
		if ( !class_exists( 'WP_Contextual_Help' ) ) {
			require_once( plugin_dir_path( __FILE__ ) . 'includes/WP-Contextual-Help/wp-contextual-help.php' );
		}
		add_action( 'init', array( $this, 'contextual_help' ) );

		/*
		 * Load Wp_Admin_Notice for the notices in the backend
		 * 
		 * First parameter the HTML, the second is the css class
		 */
		if ( !class_exists( 'WP_Admin_Notice' ) ) {
			require_once( plugin_dir_path( __FILE__ ) . 'includes/WP-Admin-Notice/WP_Admin_Notice.php' );
		}
		//new WP_Admin_Notice( __( 'Updated Messages' ), 'updated' );
		//new WP_Admin_Notice( __( 'Error Messages' ), 'error' );

		/*
		 * CMB 2 for metabox and many other cool things!
		 * https://github.com/WebDevStudios/CMB2
		 */
		require_once( plugin_dir_path( __FILE__ ) . '/includes/CMB2/init.php' );


		/*
		 * Load PointerPlus for the Wp Pointer
		 * 
		 * Unique paramter is the prefix
		 */
		if ( !class_exists( 'PointerPlus' ) ) {
			require_once( plugin_dir_path( __FILE__ ) . 'includes/PointerPlus/class-pointerplus.php' );
		}
		$pointerplus = new PointerPlus( array( 'prefix' => $this->plugin_slug ) );
		//With this you can reset all the pointer with your prefix
		//$pointerplus->reset_pointer();
		add_filter( 'pointerplus_list', array( $this, 'custom_initial_pointers' ), 10, 2 );

		

		
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		/*
		 * 
		 *
		 * - Uncomment following lines if the admin class should only be available for super admins
		 */
		/* if( ! is_super_admin() ) {
		  return;
		  } */

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_styles() {
		if ( !isset( $this->plugin_screen_hook_suffix ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( $this->plugin_screen_hook_suffix == $screen->id || strpos( $_SERVER[ 'REQUEST_URI' ], 'index.php' ) || strpos( $_SERVER[ 'REQUEST_URI' ], get_bloginfo( 'wpurl' ) . '/wp-admin/' ) ) {
			wp_enqueue_style( $this->plugin_slug . '_admin_styles', plugins_url( 'assets/css/mimo-colors-admin.min.css', __FILE__ ), array( 'dashicons' ), Mimo_Colors_Display::VERSION );
		}
		
	}

	/**
	 * Register and enqueue admin-specific JavaScript.
	 *
	 * 
	 *
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_scripts() {
		if ( !isset( $this->plugin_screen_hook_suffix ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( $this->plugin_screen_hook_suffix == $screen->id ) {
			wp_enqueue_script( $this->plugin_slug . '_admin_script', plugins_url( 'assets/js/mimo-colors-admin.min.js', __FILE__ ), array( 'jquery', 'jquery-ui-tabs' ), Mimo_Colors_Display::VERSION );
		}


	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_admin_menu() {

		/*
		 * Add a settings page for this plugin to the Settings menu.
		 *
		 * NOTE:  Alternative menu locations are available via WordPress administration menu functions.
		 *
		 *        Administration Menus: http://codex.wordpress.org/Administration_Menus
		 *
		 */
		$this->plugin_screen_hook_suffix = add_theme_page(
				__( 'Mimo Colors Settings', 'mimo-colors' ), $this->plugin_name, 'manage_options', 'mimo-colors-settings', array( $this, 'display_plugin_admin_page' )
		);
		/*
		 * Settings page in the menu
		 * 
		 */
		//$this->plugin_screen_hook_suffix = add_menu_page( __( 'Mimo Colors Settings', 'mimo-colors' ), $this->plugin_name, 'manage_options', 'mimo-colors-settings', array( $this, 'display_plugin_admin_page' ), 'dashicons-location', 81);
	}

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since    1.0.0
	 */
	public function display_plugin_admin_page() {
		include_once( 'views/admin.php' );
	}

	/**
	 * Add settings action link to the plugins page.
	 *
	 * @since    1.0.0
	 */
	public function add_action_links( $links ) {
		return array_merge(
				array(
			'settings' => '<a href="' . admin_url( 'options-general.php?page=' ) . '">' . __( 'Settings' ) . '</a>'
				), $links
		);
	}

	/**
	 * NOTE:     Actions are points in the execution of a page or process
	 *           lifecycle that WordPress fires.
	 *
	 *           Actions:    http://codex.wordpress.org/Plugin_API#Actions
	 *           Reference:  http://codex.wordpress.org/Plugin_API/Action_Reference
	 *
	 * @since    1.0.0
	 */
	public function action_method_name() {
		// Define your action hook callback here
	}

	/**
	 * NOTE:     Filters are points of execution in which WordPress modifies data
	 *           before saving it or sending it to the browser.
	 *
	 *           Filters: http://codex.wordpress.org/Plugin_API#Filters
	 *           Reference:  http://codex.wordpress.org/Plugin_API/Filter_Reference
	 *
	 * @since    1.0.0
	 */
	public function filter_method_name() {
		//  Define your filter hook callback here
	}

	

	/**
	 * Bubble Notification for pending cpt<br>
	 * NOTE: add in $post_types your cpts<br>
	 *
	 *        Reference:  http://wordpress.stackexchange.com/questions/89028/put-update-like-notification-bubble-on-multiple-cpts-menus-for-pending-items/95058
	 *
	 * @since    1.0.0
	 */
	function pending_cpt_bubble() {
		global $menu;

		$post_types = $this->cpts;
		foreach ( $post_types as $type ) {
			if ( !post_type_exists( $type ) ) {
				continue;
			}
			// Count posts
			$cpt_count = wp_count_posts( $type );

			if ( $cpt_count->pending ) {
				// Menu link suffix, Post is different from the rest
				$suffix = ( 'post' == $type ) ? '' : "?post_type=$type";

				// Locate the key of 
				$key = self::recursive_array_search_php( "edit.php$suffix", $menu );

				// Not found, just in case 
				if ( !$key ) {
					return;
				}

				// Modify menu item
				$menu[ $key ][ 0 ] .= sprintf(
						'<span class="update-plugins count-%1$s"><span class="plugin-count">%1$s</span></span>', $cpt_count->pending
				);
			}
		}
	}
	/**
	 * Gets a number of terms and displays them as options
	 * @param  string       $taxonomy Taxonomy terms to retrieve. Default is category.
	 * @param  string|array $args     Optional. get_terms optional arguments
	 * @return array                  An array of options that matches the CMB2 options array
	 */
	public static function get_term_options(  ) {

	    
	    

	    $term_options = array();
	    // $defaults = array( 'taxonomy' => 'category' );
	    $args1 = wp_parse_args( $args1, array( 'taxonomy' => 'mimo_colors_location_category' ) );

	    

	    $terms1 = (array) get_terms( 'mimo_colors_location_category', $args1 );

	    if ( ! empty( $terms1 ) ) {
	        foreach ( $terms1 as $term1 ) {
	            $term_options = array_merge($term_options,  array($term1->slug => $term1->name));
	        }
	    }

	    
	    
	    



	    return $term_options;
	}
	/**
	 * Required for the bubble notification<br>
	 *
	 *        Reference:  http://wordpress.stackexchange.com/questions/89028/put-update-like-notification-bubble-on-multiple-cpts-menus-for-pending-items/95058
	 *
	 * @since    1.0.0
	 */
	private function recursive_array_search_php( $needle, $haystack ) {
		foreach ( $haystack as $key => $value ) {
			$current_key = $key;
			if ( $needle === $value OR ( is_array( $value ) && self::recursive_array_search_php( $needle, $value ) !== false) ) {
				return $current_key;
			}
		}
		return false;
	}

	/**
	 * Process a settings export from config
	 * @since    1.0.0
	 */
	function settings_export() {

		if ( empty( $_POST[ 'pn_action' ] ) || 'export_settings' != $_POST[ 'pn_action' ] ) {
			return;
		}

		if ( !wp_verify_nonce( $_POST[ 'pn_export_nonce' ], 'pn_export_nonce' ) ) {
			return;
		}

		if ( !current_user_can( 'manage_options' ) ) {
			return;
		}
		$settings[ 0 ] = get_option( $this->plugin_slug . '_settings' );
		$settings[ 1 ] = get_option( $this->plugin_slug . '_settings_style' );

		ignore_user_abort( true );

		nocache_headers();
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=pn-settings-export-' . date( 'm-d-Y' ) . '.json' );
		header( "Expires: 0" );
		if ( version_compare( PHP_VERSION, '5.4.0', '>=' ) ) {
			echo json_encode( $settings, JSON_PRETTY_PRINT );
		} else {
			echo json_encode( $settings );
		}
		exit;
	}

	/**
	 * Process a settings import from a json file
	 * @since    1.0.0
	 */
	function settings_import() {

		if ( empty( $_POST[ 'pn_action' ] ) || 'import_settings' != $_POST[ 'pn_action' ] ) {
			return;
		}

		if ( !wp_verify_nonce( $_POST[ 'pn_import_nonce' ], 'pn_import_nonce' ) ) {
			return;
		}

		if ( !current_user_can( 'manage_options' ) ) {
			return;
		}
		$extension = end( explode( '.', $_FILES[ 'pn_import_file' ][ 'name' ] ) );

		if ( $extension != 'json' ) {
			wp_die( __( 'Please upload a valid .json file','mimo-colors') );
		}

		$import_file = $_FILES[ 'pn_import_file' ][ 'tmp_name' ];

		if ( empty( $import_file ) ) {
			wp_die( __( 'Please upload a file to import', 'mimo-colors' ) );
		}

		// Retrieve the settings from the file and convert the json object to an array.
		$settings = ( array ) json_decode( file_get_contents( $import_file ) );

		update_option( $this->plugin_slug . '_settings', get_object_vars( $settings[ 0 ] ) );
		update_option( $this->plugin_slug . '_settings_style', get_object_vars( $settings[ 1 ] ) );

		wp_safe_redirect( admin_url( 'options-general.php?page=mimo-colors-settings') );
		exit;
	}

	/**
	 * Filter for change the folder of Contextual Help
	 * 
	 * @since     1.0.0
	 *
	 * @return    string    the path
	 */
	public function help_docs_dir( $paths ) {
		$paths[] = plugin_dir_path( __FILE__ ) . '../help-docs/';
		return $paths;
	}

	/**
	 * Filter for change the folder image of Contextual Help
	 * 
	 * @since     1.0.0
	 *
	 * @return    string    the path
	 */
	public function help_docs_url( $paths ) {
		$paths[] = plugin_dir_path( __FILE__ ) . '../help-docs/img';
		return $paths;
	}

	/**
	 * Contextual Help, docs in /help-docs folter
	 * Documentation https://github.com/voceconnect/wp-contextual-help
	 * 
	 * @since    1.0.0 
	 */
	public function contextual_help() {
		if ( !class_exists( 'WP_Contextual_Help' ) ) {
			return;
		}

		// Only display on the pages - post.php and post-new.php, but only on the `demo` post_type
		WP_Contextual_Help::register_tab( 'mimo-colors-help-new-post', __( 'Mimo Colors Management', 'mimo-colors' ), array(
			'page' => array( 'post.php', 'post-new.php' ),
			'post_type' => array('product', 'post'),
			'wpautop' => true
		) );

		// Add to a custom plugin settings page
		WP_Contextual_Help::register_tab( 'mimo_colors_settings', __( 'Mimo Colors Settings', 'mimo-colors' ), array(
			'page' => $this->plugin_slug . '_settings',
			'wpautop' => true
		) );
	}

	/**
	 * TODO Add pointers.
	 * Check on https://github.com/Mte90/pointerplus/blob/master/pointerplus.php for examples
	 *
	 * @param $pointers
	 * @param $prefix for your pointers
	 *
	 * @return mixed
	 */
	function custom_initial_pointers( $pointers, $prefix ) {
		return array_merge( $pointers, array(
			$prefix . '_contextual_tab' => array(
				'selector' => '#contextual-help-link',
				'title' => __( 'Mimo Colors Help', 'mimo-colors' ),
				'text' => __( 'A pointer for help tab.<br>Go to Posts, Pages or Users for other pointers.', 'mimo-colors' ),
				'edge' => 'top',
				'align' => 'right',
				'icon_class' => 'dashicons-welcome-learn-more',
			)
				) );
	}

}
