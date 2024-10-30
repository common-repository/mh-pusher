<?php

/**
 *
 * @link              travisnguyen.me
 * @since             1.0.0
 * @package           Message_Hub
 *
 * @wordpress-plugin
 * Plugin Name:       Message Hub
 * Plugin URI:        message-hub.com
 * Description:       Message Hub is a message forwarder, keep you update with messages from your customer
 * Version:           1.0.0
 * Author:            Travis Nguyen
 * Author URI:        travisnguyen.me
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       message-hub
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'MESSAGE_HUB_VERSION', '1.0.0' );

define( 'MESSAGE_HUB_REMOTE_URL', 'https://message-hub.com/api' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-message-hub-activator.php
 */
function activate_message_hub() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-message-hub-activator.php';
	Message_Hub_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-message-hub-deactivator.php
 */
function deactivate_message_hub() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-message-hub-deactivator.php';
	Message_Hub_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_message_hub' );
register_deactivation_hook( __FILE__, 'deactivate_message_hub' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-message-hub.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_message_hub() {

	$plugin = new Message_Hub();
	$plugin->run();

}
run_message_hub();
