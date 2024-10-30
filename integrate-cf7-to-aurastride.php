<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://https://profiles.wordpress.org/vsourz1td/
 * @since             1.0.0
 * @package           Cf7_aurastride_crm
 *
 * @wordpress-plugin
 * Plugin Name:       Integrate CF7 to aurastride
 * Plugin URI:        https://https://profiles.wordpress.org/vsourz1td/
 * Description:       Integration of Contact form 7 to aurastride CRM for WordPress is the optimum way to manage leads in easiest way. It’s a plug & play tool which allows you to capture and store customer’s data by integrating the Contact Form and our plugin. No hand-written customization code required.
 * Version:           1.0.0
 * Author:            Vsourz Digital
 * Author URI:        https://https://profiles.wordpress.org/vsourz1td/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       integrate-cf7-to-aurastride
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */

define( 'CF7AU_VERSION', '1.0.0' );

//define all constants here

define( 'CF7AU_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'CF7AU_PLUGIN_URL', plugin_dir_url(__FILE__) );

//check ACF7DB active or not
/**
 * Detect addon. For use in Admin area only.
 */

if(!defined('CF7AU_ACF7DB_ACTIVE')){
	
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	if(is_plugin_active( 'advanced-cf7-db/advanced-cf7-db.php')){
		define( 'CF7AU_ACF7DB_ACTIVE',true);
	}
	else{
		define( 'CF7AU_ACF7DB_ACTIVE',false);	
	}
}

//define a constant for the API header authorization key name
if(!defined('CF7AU_AS_API_AUTH_KEY_NAME')){
	define( 'CF7AU_AS_API_AUTH_KEY_NAME','x-api-key');
}


//defined constants for enable direct API integration

// $cf7au_send_direct = get_option('cf72mot_send_direct');
$cf7au_send_direct = get_option('cf7au_send_direct');

if(!empty($cf7au_send_direct)){
	// define( 'CF72AUT_SEND_DIRECT',true);
	define( 'CF7AU_SEND_DIRECT',true);
}
else{
	// define( 'CF72AUT_SEND_DIRECT',false);
	define( 'CF7AU_SEND_DIRECT',false);
}

//Define the values that are field type and have defined values within Aurastride
$display_field_type_values = array("RB", "DD", "DM", "CK","BL");
define( 'cf7au_display_field_type_values',$display_field_type_values);
/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-cf7_aurastride_crm-activator.php
 */
function cf7au_activate_cf7_aurastride_crm() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-cf7_aurastride_crm-activator.php';
	Cf7au_aurastride_crm_Activator::cf7au_activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-cf7_aurastride_crm-deactivator.php
 */
function cf7au_deactivate_cf7_aurastride_crm() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-cf7_aurastride_crm-deactivator.php';
	Cf7au_aurastride_crm_Deactivator::cf7au_deactivate();
}

register_activation_hook( __FILE__, 'cf7au_activate_cf7_aurastride_crm' );
register_deactivation_hook( __FILE__, 'cf7au_deactivate_cf7_aurastride_crm' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-cf7_aurastride_crm.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function cf7au_run_cf7_aurastride_crm() {

	$plugin = new Cf7au_aurastride_crm();
	$plugin->cf7au_run();

}
cf7au_run_cf7_aurastride_crm();
