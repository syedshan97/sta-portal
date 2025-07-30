<?php
/**
 * Plugin Name: STA Portal
 * Description: Custom training portal for online learning. Login, registration, dashboard, profile management, admin tools, and more.
 * Version: 1.0.0
 * Author: Your Name
 * Text Domain: sta-portal
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Define plugin path/URL
define( 'STA_PORTAL_PATH', plugin_dir_path( __FILE__ ) );
define( 'STA_PORTAL_URL', plugin_dir_url( __FILE__ ) );

// Autoload includes
foreach ( glob( STA_PORTAL_PATH . 'includes/class-*.php' ) as $file ) {
    require_once $file;
}
require_once( STA_PORTAL_PATH . 'includes/helpers.php' );

// Initialize plugin hooks and shortcodes
add_action( 'plugins_loaded', function() {
    new STA_Portal_Shortcodes();
    new STA_Portal_Auth();
    new STA_Portal_Admin();
    new STA_Portal_Dashboard();
    new STA_Portal_Profile();
    new STA_Portal_Scripts();
    new STA_Portal_Hooks();
});
