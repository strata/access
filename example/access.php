<?php
/**
 * Plugin Name:     Strata / Access
 * Plugin URI:      http://www.studio24.net/
 * Description:     Lock down CMS access to specific IP's and email domains via a one-time password.
 * Author:          Studio 24
 * Author URI:      http://www.studio24.net/
 * Text Domain:     access
 * Domain Path:     /
 * Version:         0.0.1
 *
 * @package    Strata / Access
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    throw new Exception('You cannot access this file directly');
}

require __DIR__ . '/StrataAccessPlugin.php';

// Sets up activation hook

register_activation_hook(__FILE__, function () {
    if (!class_exists('Routes')) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(__('Timber "Routes" class not available to Responses plugin. Plugin can not boot. Ensure Timber plugin is installed and activated before proceeding.'),
            'Plugin dependency check', array('back_link' => true));
        exit;
    }
});

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */

// Starts plugin
add_action('init', function() {

    new StrataAccessPlugin();

});


