<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class STA_Portal_Auth {

    public function __construct() {
        add_action( 'init', array( $this, 'handle_login_form' ) );
        add_action( 'init', array( $this, 'handle_signup_form' ) );
        add_action( 'init', array( $this, 'handle_lost_password_form' ) );
        add_action( 'init', array( $this, 'handle_reset_password_form' ) );

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

    // public function handle_signup_form() {
    //     if ( isset( $_POST['sta_portal_signup_nonce'] ) && wp_verify_nonce( $_POST['sta_portal_signup_nonce'], 'sta_portal_signup' ) ) {
    //         $email    = sanitize_email( $_POST['sta_signup_email'] );
    //         $name     = sanitize_text_field( $_POST['sta_signup_name'] );
    //         $password = $_POST['sta_signup_password'];

    //         if ( email_exists( $email ) ) {
    //             wp_redirect( add_query_arg('sta_error', urlencode('Email already exists.'), wp_get_referer() ) );
    //             exit;
    //         }

    //         // Generate unique custom portal user ID
    //         $last_id = get_option('sta_portal_last_user_id', 4500);
    //         $next_id = intval($last_id) + 1;

    //         $user_id = wp_create_user( $email, $password, $email );
    //         if ( is_wp_error( $user_id ) ) {
    //             wp_redirect( add_query_arg('sta_error', urlencode($user_id->get_error_message()), wp_get_referer() ) );
    //             exit;
    //         }
    //         // Save display name and custom portal ID
    //         wp_update_user( array(
    //             'ID' => $user_id,
    //             'display_name' => $name
    //         ));
    //         update_user_meta( $user_id, 'portal_user_id', $next_id );
    //         update_option( 'sta_portal_last_user_id', $next_id );

    //         // Auto-login after registration
    //         wp_set_current_user( $user_id );
    //         wp_set_auth_cookie( $user_id );
    //         wp_redirect( site_url('/dashboard/') );
    //         exit;
    //     }
    // }

    public function handle_signup_form() {
    if ( isset($_POST['sta_portal_signup_nonce']) && wp_verify_nonce($_POST['sta_portal_signup_nonce'], 'sta_portal_signup') ) {
        $email    = sanitize_email( $_POST['sta_signup_email'] ?? '' );
        $name     = sanitize_text_field( $_POST['sta_signup_name'] ?? '' );
        $password = $_POST['sta_signup_password'] ?? '';

        // Basic validation
        if ( empty($email) || empty($name) || empty($password) ) {
            wp_safe_redirect( add_query_arg('sta_error', urlencode('Please fill all required fields.'), wp_get_referer() ?: site_url('/signup/')) );
            exit;
        }

        if ( email_exists( $email ) ) {
            wp_safe_redirect( add_query_arg('sta_error', urlencode('Email already exists.'), wp_get_referer() ?: site_url('/signup/')) );
            exit;
        }

        // Generate unique custom portal user ID
        $last_id = get_option('sta_portal_last_user_id', 4500);
        $next_id = intval($last_id) + 1;

        // Create user
        $user_id = wp_create_user( $email, $password, $email );
        if ( is_wp_error( $user_id ) ) {
            wp_safe_redirect( add_query_arg('sta_error', urlencode($user_id->get_error_message()), wp_get_referer() ?: site_url('/signup/')) );
            exit;
        }

        // Save display name and custom portal ID
        wp_update_user( array(
            'ID'           => $user_id,
            'display_name' => $name
        ) );
        update_user_meta( $user_id, 'portal_user_id', $next_id );
        update_option( 'sta_portal_last_user_id', $next_id );

        /* -------------------- POINT 3 START --------------------
           Mark local (email/password) signups as UNVERIFIED for admin records,
           and send a verification email with secure token.
        -------------------------------------------------------- */
        update_user_meta( $user_id, 'sta_auth_provider', 'local' );
        update_user_meta( $user_id, 'sta_email_verified', 0 );

        if ( class_exists('STA_Portal_Email_Verification') ) {
            STA_Portal_Email_Verification::send_verification_email( $user_id, $email );
        }
        /* --------------------- POINT 3 END --------------------- */

        /* -------------------- POINT 4 START --------------------
           Do NOT auto-login unverified users.
           Instead, redirect to Login with a success message telling
           them to verify their email. (Actual login blocking for
           unverified users is enforced in the login handler.)
        -------------------------------------------------------- */
        $msg = sprintf('Account created. We sent a verification link to %s. Please verify to sign in.', $email);
        wp_safe_redirect( add_query_arg('sta_success', urlencode($msg), site_url('/login/')) );
        exit;
        /* --------------------- POINT 4 END --------------------- */

        // (Old behavior removed)
        // wp_set_current_user( $user_id );
        // wp_set_auth_cookie( $user_id );
        // wp_redirect( site_url('/dashboard/') );
        // exit;
    }
}


    // Handle lost password form
public function handle_lost_password_form() {
    if ( isset( $_POST['sta_portal_lostpass_nonce'] ) && wp_verify_nonce( $_POST['sta_portal_lostpass_nonce'], 'sta_portal_lostpass' ) ) {
        $user_login = sanitize_text_field( $_POST['sta_lostpass_email'] );
        $user = get_user_by( 'email', $user_login );
        if ( ! $user ) {
            wp_redirect( add_query_arg('sta_error', urlencode('No account found with that email.'), wp_get_referer() ) );
            exit;
        }
        // Generate reset key and send email using WP's process
        $reset_key = get_password_reset_key( $user );
        $reset_url = site_url( '/reset-password/?key=' . $reset_key . '&login=' . rawurlencode( $user->user_login ) );
        // Use WP default email template (for simplicity) or build your own
        $message = "Someone requested a password reset for the following account: \r\n\r\n";
        $message .= "Username: " . $user->user_login . "\r\n";
        $message .= "If this was a mistake, just ignore this email.\r\n";
        $message .= "To reset your password, visit the following address: " . $reset_url;
        wp_mail( $user->user_email, 'Password Reset Request', $message );
        wp_redirect( add_query_arg('sta_success', urlencode('Check your email for the password reset link.'), wp_get_referer() ) );
        exit;
    }
}

// Handle reset password form
public function handle_reset_password_form() {
    if ( isset( $_POST['sta_portal_resetpass_nonce'] ) && wp_verify_nonce( $_POST['sta_portal_resetpass_nonce'], 'sta_portal_resetpass' ) ) {
        $key   = sanitize_text_field( $_POST['reset_key'] );
        $login = sanitize_text_field( $_POST['reset_login'] );
        $pass1 = $_POST['sta_new_pass1'];
        $pass2 = $_POST['sta_new_pass2'];
        if ( $pass1 !== $pass2 ) {
            wp_redirect( add_query_arg('sta_error', urlencode('Passwords do not match.'), wp_get_referer() ) );
            exit;
        }
        $user = check_password_reset_key( $key, $login );
        if ( is_wp_error( $user ) ) {
            wp_redirect( add_query_arg('sta_error', urlencode('Invalid reset link. Please try again.'), site_url('/forgot-password/') ) );
            exit;
        }
        reset_password( $user, $pass1 );
        wp_redirect( add_query_arg('sta_success', urlencode('Your password has been reset. Please log in.'), site_url('/login/') ) );
        exit;
    }
}

}
