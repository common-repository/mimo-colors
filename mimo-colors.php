<?php

/**
 *
 * @package   Mimo_Colors_Display
 * @author    Mimo <mail@mimo.studio>
 * @license   GPL-2.0+
 * @link      http://mimo.studio
 * @copyright 2015 Mimo
 *
 * @wordpress-plugin
 * Plugin Name:       Mimo Colors
 * Plugin URI:        http://mimo.studio
 * Description:       Add custom colors to elements with no coding knowledge.
 * Version:           1.0
 * Author:            mimothemes
 * Author URI:        http://mimo.studio
 * Text Domain:       mimo-colors
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 * WordPress-Plugin-Boilerplate-Powered: v1.1.2
 */



// If this file is called directly, abort.
if ( !defined( 'WPINC' ) ) {
	die;
}

/*
 * ------------------------------------------------------------------------------
 * Public-Facing Functionality
 * ------------------------------------------------------------------------------
 */


/*
 * Load Language wrapper function for WPML/Ceceppa Multilingua/Polylang
 */

require_once( plugin_dir_path( __FILE__ ) . 'includes/language.php' );
require_once( plugin_dir_path( __FILE__ ) . 'public/class-mimo-colors.php' );


/*
 * Register hooks that are fired when the plugin is activated or deactivated.
 * When the plugin is deleted, the uninstall.php file is loaded.
 *

 */

register_activation_hook( __FILE__, array( 'Mimo_Colors_Display', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Mimo_Colors_Display', 'deactivate' ) );



add_action( 'plugins_loaded', array( 'Mimo_Colors_Display', 'get_instance' ), 9999 );

/*
 * -----------------------------------------------------------------------------
 * Dashboard and Administrative Functionality
 * -----------------------------------------------------------------------------
*/



if ( is_admin() && (!defined( 'DOING_AJAX' ) || !DOING_AJAX ) ) {

	require_once( plugin_dir_path( __FILE__ ) . 'admin/class-mimo-colors-admin.php' );
	add_action( 'plugins_loaded', array( 'Mimo_Colors_Admin', 'get_instance' ) );
}
