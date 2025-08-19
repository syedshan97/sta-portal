<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class STA_Portal_Auth {

    public function __construct() {
        add_action( 'init', array( $this, 'handle_login_form' ) );
        add_action( 'init', array( $this, 'handle_signup_form' ) );
        add_action( 'init', array( $this, 'handle_lost_password_form' ) );
        add_action( 'init', array( $this, 'handle_reset_password_form' ) );
        add_filter('authenticate', array($this, 'block_unverified_local_on_auth'), 30, 3);


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
    if ( isset($_POST['sta_portal_signup_nonce']) && wp_verify_nonce($_POST['sta_portal_signup_nonce'], 'sta_portal_signup') ) {

        // --- FIRST/LAST NAME UPGRADE: collect inputs ---
        $first    = sanitize_text_field( $_POST['sta_signup_first'] ?? '' );
        $last     = sanitize_text_field( $_POST['sta_signup_last'] ?? '' );
        $email    = sanitize_email( $_POST['sta_signup_email'] ?? '' );
        $password = $_POST['sta_signup_password'] ?? '';

        // --- SIMPLE VALIDATION (server-side) ---
        $errors = [];

        // First name: required, English letters only
        if ( $first === '' || !preg_match('/^[A-Za-z]+$/', $first) ) {
            $errors[] = 'First name is required (English letters only).';
        }

        // Last name: required, English letters only
        if ( $last === '' || !preg_match('/^[A-Za-z]+$/', $last) ) {
            $errors[] = 'Last name is required (English letters only).';
        }

        // Email: required + valid format
        if ( $email === '' || !is_email($email) ) {
            $errors[] = 'Please enter a valid email address (e.g., name@example.com).';
        }

        // Password: ≥8, at least one letter, one digit, one symbol
        $has_len   = strlen($password) >= 8;
        $has_alpha = preg_match('/[A-Za-z]/', $password);
        $has_digit = preg_match('/\d/', $password);
        $has_sym   = preg_match('/[^A-Za-z0-9]/', $password);

        if ( !($has_len && $has_alpha && $has_digit && $has_sym) ) {
            $errors[] = 'Password must be at least 8 characters and include a letter, a number, and a symbol.';
        }

        if ( !empty($errors) ) {
            wp_safe_redirect( add_query_arg('sta_error', urlencode(implode(' ', $errors)), wp_get_referer() ?: site_url('/signup/')) );
            exit;
        }
        // --- END SIMPLE VALIDATION ---

        // Email already exists (keep AFTER format checks)
        if ( email_exists( $email ) ) {
            wp_safe_redirect( add_query_arg('sta_error', urlencode('Email already exists.'), wp_get_referer() ?: site_url('/signup/')) );
            exit;
        }

        // Generate unique custom portal user ID
        $last_id = get_option('sta_portal_last_user_id', 4500);
        $next_id = intval($last_id) + 1;

        // Create user (use email as username)
        $user_id = wp_create_user( $email, $password, $email );
        if ( is_wp_error( $user_id ) ) {
            wp_safe_redirect( add_query_arg('sta_error', urlencode($user_id->get_error_message()), wp_get_referer() ?: site_url('/signup/')) );
            exit;
        }

        // --- FIRST/LAST NAME UPGRADE: save names + display_name ---
        $display = trim($first . ' ' . $last);
        update_user_meta( $user_id, 'first_name', $first );
        update_user_meta( $user_id, 'last_name',  $last );
        wp_update_user( array(
            'ID'           => $user_id,
            'display_name' => $display ?: $email,
        ) );

        // Save custom portal ID
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

/**
 * Block email/password login for unverified "local" users.
 * Works globally (wp-login.php, custom forms, wp_signon()).
 *
 * @param WP_User|WP_Error|null $user
 * @param string $username
 * @param string $password
 * @return WP_User|WP_Error|null
 */
public function block_unverified_local_on_auth( $user, $username, $password ) {
    // If another auth step already failed or user not resolved yet, do nothing.
    if ( is_wp_error($user) || ! $user instanceof WP_User ) {
        return $user;
    }

    // Social logins are trusted → allow
    $provider = get_user_meta($user->ID, 'sta_auth_provider', true);
    if ( in_array($provider, array('google','microsoft'), true) ) {
        return $user;
    }

    // Treat missing provider as local (legacy accounts)
    if ( $provider === '' ) {
        $provider = 'local';
        // Optional: backfill for future clarity
        // update_user_meta($user->ID, 'sta_auth_provider', 'local');
    }

    if ( $provider === 'local' ) {
        $verified = intval( get_user_meta($user->ID, 'sta_email_verified', true) );
        if ( $verified !== 1 ) {
            // Build a resend link if the class exists
            $msg = 'Please verify your email to continue.';
            if ( class_exists('STA_Portal_Email_Verification') ) {
                $resend_url = STA_Portal_Email_Verification::get_resend_url( $user->ID );
                $msg .= ' <a href="'. esc_url($resend_url) .'">Resend verification email</a>.';
            }
            return new WP_Error( 'email_not_verified', $msg );
        }
    }

    return $user;
}


}
