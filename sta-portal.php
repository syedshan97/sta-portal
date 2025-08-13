<?php
/**
 * Plugin Name: STA Portal
 * Description: Custom training portal for online learning. Login, registration, and user ID logic.
 * Version: 1.0.0
 * Author: Shan
 * Text Domain: sta-portal
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

define( 'STA_PORTAL_PATH', plugin_dir_path( __FILE__ ) );
define( 'STA_PORTAL_URL', plugin_dir_url( __FILE__ ) );



// Autoload only the needed includes
require_once( STA_PORTAL_PATH . 'includes/class-shortcodes.php' );
require_once( STA_PORTAL_PATH . 'includes/class-auth.php' );
require_once( STA_PORTAL_PATH . 'includes/class-hooks.php' );
require_once( STA_PORTAL_PATH . 'includes/class-admin.php' );
require_once( STA_PORTAL_PATH . 'includes/class-profile.php' );
require_once STA_PORTAL_PATH . 'includes/class-email-verification.php';


// Initialize plugin
add_action( 'plugins_loaded', function() {
    new STA_Portal_Shortcodes();
    new STA_Portal_Auth();
	new STA_Portal_Hooks();
    new STA_Portal_Admin();
    new STA_Portal_Profile();
    new STA_Portal_Email_Verification();

});

// Allow subscribers to upload (needed for front-end avatar)
register_activation_hook( __FILE__, function(){
    $role = get_role('subscriber');
    if ($role && ! $role->has_cap('upload_files')) {
        $role->add_cap('upload_files');
    }
});