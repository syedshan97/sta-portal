<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class STA_Portal_Auth {

    public function __construct() {
        add_action( 'init', array( $this, 'handle_login_form' ) );
        add_action( 'init', array( $this, 'handle_signup_form' ) );
    }

    public function handle_login_form() {
        if ( isset( $_POST['sta_portal_login_nonce'] ) && wp_verify_nonce( $_POST['sta_portal_login_nonce'], 'sta_portal_login' ) ) {
            $email    = sanitize_email( $_POST['sta_login_email'] );
            $password = $_POST['sta_login_password'];
            $creds = array(
                'user_login'    => $email,
                'user_password' => $password,
                'remember'      => true
            );
            $user = wp_signon( $creds, false );
            if ( is_wp_error( $user ) ) {
                wp_redirect( add_query_arg('sta_error', urlencode($user->get_error_message()), wp_get_referer() ) );
                exit;
            } else {
                wp_redirect( site_url('/dashboard/') );
                exit;
            }
        }
    }

    public function handle_signup_form() {
        if ( isset( $_POST['sta_portal_signup_nonce'] ) && wp_verify_nonce( $_POST['sta_portal_signup_nonce'], 'sta_portal_signup' ) ) {
            $email    = sanitize_email( $_POST['sta_signup_email'] );
            $name     = sanitize_text_field( $_POST['sta_signup_name'] );
            $password = $_POST['sta_signup_password'];

            if ( email_exists( $email ) ) {
                wp_redirect( add_query_arg('sta_error', urlencode('Email already exists.'), wp_get_referer() ) );
                exit;
            }

            // Generate unique custom portal user ID
            $last_id = get_option('sta_portal_last_user_id', 4500);
            $next_id = intval($last_id) + 1;

            $user_id = wp_create_user( $email, $password, $email );
            if ( is_wp_error( $user_id ) ) {
                wp_redirect( add_query_arg('sta_error', urlencode($user_id->get_error_message()), wp_get_referer() ) );
                exit;
            }
            // Save display name and custom portal ID
            wp_update_user( array(
                'ID' => $user_id,
                'display_name' => $name
            ));
            update_user_meta( $user_id, 'portal_user_id', $next_id );
            update_option( 'sta_portal_last_user_id', $next_id );

            // Auto-login after registration
            wp_set_current_user( $user_id );
            wp_set_auth_cookie( $user_id );
            wp_redirect( site_url('/dashboard/') );
            exit;
        }
    }
}
