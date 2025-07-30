<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class STA_Portal_Shortcodes {

    public function __construct() {
        add_shortcode( 'portal_login_form', array( $this, 'login_form_shortcode' ) );
        add_shortcode( 'portal_signup_form', array( $this, 'signup_form_shortcode' ) );
    }

    public function login_form_shortcode( $atts ) {
        ob_start();
        include( STA_PORTAL_PATH . 'templates/login-form.php' );
        return ob_get_clean();
    }

    public function signup_form_shortcode( $atts ) {
        ob_start();
        include( STA_PORTAL_PATH . 'templates/signup-form.php' );
        return ob_get_clean();
    }
}
