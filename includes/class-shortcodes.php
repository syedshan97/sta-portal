<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class STA_Portal_Shortcodes {

    public function __construct() {
        add_shortcode( 'portal_login_form', array( $this, 'login_form_shortcode' ) );
        add_shortcode( 'portal_signup_form', array( $this, 'signup_form_shortcode' ) );
        add_shortcode( 'portal_lost_password_form', array( $this, 'lost_password_form_shortcode' ) );
        add_shortcode( 'portal_reset_password_form', array( $this, 'reset_password_form_shortcode' ) );
        add_shortcode('portal_logout_link', array($this, 'logout_link_shortcode'));


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

    public function lost_password_form_shortcode( $atts ) {
    ob_start();
    include( STA_PORTAL_PATH . 'templates/lost-password-form.php' );
    return ob_get_clean();
    
   }

    public function reset_password_form_shortcode( $atts ) {
    ob_start();
    include( STA_PORTAL_PATH . 'templates/reset-password-form.php' );
    return ob_get_clean();

    }

    public function logout_link_shortcode($atts) {
    // Hide link for guests
    if ( ! is_user_logged_in() ) return '';

    $atts = shortcode_atts(array(
        'text'     => 'Logout',
        'class'    => 'sta-logout-link',
        // Leave empty to use default success message redirect to /login/
        'redirect' => '',
    ), $atts, 'portal_logout_link');

    // Default redirect: /login/?sta_success=You’ve been logged out.
    $default_redirect = add_query_arg(
        'sta_success',
        urlencode("You’ve been logged out."),
        site_url('/login/')
    );

    $target_redirect = $atts['redirect'] ? $atts['redirect'] : $default_redirect;

    // Secure, nonce-protected WordPress logout URL
    $url = wp_logout_url( $target_redirect );

    return '<a href="'. esc_url($url) .'" class="'. esc_attr($atts['class']) .'">'. esc_html($atts['text']) .'</a>';
}


}
