<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class STA_Portal_Profile {

    public function __construct() {
        // Save profile (front-end form posts to admin-post.php)
        add_action('admin_post_sta_profile_save',        array($this, 'handle_profile_save'));
        add_action('admin_post_nopriv_sta_profile_save', array($this, 'redirect_login'));

        // Avatar upload (AJAX, logged-in)
        add_action('wp_ajax_sta_portal_save_avatar', array($this, 'ajax_save_avatar'));
        
        // Change Password on Manage Profile
        add_action('admin_post_sta_change_password', array($this, 'handle_change_password'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_profile_assets'));
        
        add_action('wp_ajax_sta_remove_avatar', [$this, 'ajax_remove_avatar']);
    }
    
    public function ajax_remove_avatar() {
    if ( ! is_user_logged_in() ) {
        wp_send_json_error(['message' => 'Not logged in'], 401);
    }
    if ( empty($_POST['nonce']) || ! wp_verify_nonce($_POST['nonce'], 'sta_remove_avatar') ) {
        wp_send_json_error(['message' => 'Invalid request'], 403);
    }

    $user_id = get_current_user_id();
    delete_user_meta($user_id, 'sta_avatar_id'); // do NOT delete the media file
    wp_send_json_success(['message' => 'Avatar removed']);
}


    public function redirect_login() {
        wp_safe_redirect( site_url('/login/') );
        exit;
    }

public function handle_profile_save() {
    $back = wp_get_referer() ?: site_url('/manage-profile/');

    if ( empty($_POST['sta_profile_nonce']) || ! wp_verify_nonce($_POST['sta_profile_nonce'], 'sta_profile_save') ) {
        wp_safe_redirect( add_query_arg('sta_error', urlencode('Security check failed.'), $back) );
        exit;
    }

    $user_id = get_current_user_id();
    if ( ! $user_id ) { $this->redirect_login(); }

    $errors = [];

    /* ---------------- First / Last name (NEW) ---------------- */
    $first = sanitize_text_field( $_POST['sta_first_name'] ?? '' );
    $last  = sanitize_text_field( $_POST['sta_last_name']  ?? '' );

   $NAME_RE = "/^[A-Za-z]+(?:[ '\-][A-Za-z]+)*$/"; // letters with optional internal space/'/-

if ( $first === '' || !preg_match($NAME_RE, $first) ) {
    $errors[] = 'First name: use English letters and spaces only.';
}
if ( $last === '' || !preg_match($NAME_RE, $last) ) {
    $errors[] = 'Last name: use English letters and spaces only.';
}

    $display = trim($first . ' ' . $last);
    /* --------------------------------------------------------- */

    // Email + uniqueness
    $email = sanitize_email($_POST['sta_email'] ?? '');
    if ( ! is_email($email) ) {
        $errors[] = 'Please enter a valid email address.';
    } else {
        $existing = get_user_by('email', $email);
        if ( $existing && intval($existing->ID) !== intval($user_id) ) {
            $errors[] = 'This email is already used by another account.';
        }
    }

    // Phone (E.164)
    $phone = trim($_POST['sta_phone'] ?? '');
    if ( $phone !== '' && !preg_match('/^\+[1-9]\d{7,14}$/', $phone) ) {
        $errors[] = 'Phone must include country code, e.g. +14155551212.';
    }

    if ( $errors ) {
        wp_safe_redirect( add_query_arg('sta_error', urlencode(implode(' ', $errors)), $back) );
        exit;
    }

    // Track email change for re-verification
    $old_email = $user_id ? get_userdata($user_id)->user_email : '';
    $changing_email = ( strtolower(trim($old_email)) !== strtolower(trim($email)) );

    /* ------------ Core fields: display_name + email ---------- */
    wp_update_user([
        'ID'           => $user_id,
        'display_name' => $display ?: $email,  // keep a sensible fallback
        'user_email'   => $email,
    ]);

    // Store first/last in native WP meta
    update_user_meta($user_id, 'first_name', $first);
    update_user_meta($user_id, 'last_name',  $last);
    /* --------------------------------------------------------- */

    // If email changed → mark unverified and send a new verification email
    if ( $changing_email ) {
        update_user_meta($user_id, 'sta_email_verified', 0);
        if ( class_exists('STA_Portal_Email_Verification') ) {
            STA_Portal_Email_Verification::send_verification_email( $user_id, $email );
        }
    }

    // Other meta (kept as-is)
    update_user_meta($user_id, 'sta_job_title',    sanitize_text_field($_POST['sta_job_title'] ?? ''));
    update_user_meta($user_id, 'sta_org',          sanitize_text_field($_POST['sta_org'] ?? ''));
    update_user_meta($user_id, 'sta_phone',        $phone);
    update_user_meta($user_id, 'sta_addr_street',  sanitize_text_field($_POST['sta_addr_street'] ?? ''));
    update_user_meta($user_id, 'sta_addr_city',    sanitize_text_field($_POST['sta_addr_city'] ?? ''));
    update_user_meta($user_id, 'sta_addr_state',   sanitize_text_field($_POST['sta_addr_state'] ?? ''));
    update_user_meta($user_id, 'sta_addr_country', sanitize_text_field($_POST['sta_addr_country'] ?? ''));
    update_user_meta($user_id, 'sta_addr_postal',  sanitize_text_field($_POST['sta_addr_postal'] ?? ''));

    wp_safe_redirect( add_query_arg('sta_success', urlencode('Profile updated successfully.'), $back) );
    exit;
}


    public function ajax_save_avatar() {
        if ( ! isset($_POST['nonce']) || ! wp_verify_nonce($_POST['nonce'], 'sta_profile_avatar') ) {
            wp_send_json_error('Invalid request.');
        }
        $user_id = get_current_user_id();
        if (!$user_id) wp_send_json_error('Not logged in.');

        // Ensure upload capability (subscribers need this)
        if ( ! current_user_can('upload_files') ) {
            wp_send_json_error('You are not allowed to upload files.');
        }

        $att_id = isset($_POST['attachment_id']) ? intval($_POST['attachment_id']) : 0;
        if ($att_id <= 0) wp_send_json_error('Invalid attachment.');

        $mime = get_post_mime_type($att_id);
        if (strpos($mime, 'image/') !== 0) wp_send_json_error('Please select an image.');

        update_user_meta($user_id, 'sta_avatar_id', $att_id);
        $url = wp_get_attachment_image_url($att_id, 'thumbnail');
        wp_send_json_success(['url' => $url]);
    }
    
    public function enqueue_profile_assets() {
    if ( function_exists('is_page') && is_page('manage-profile') ) {
        // JS already used on signup/reset; reuse it for the ticker here too
        wp_enqueue_script('sta-portal-js', STA_PORTAL_URL . 'assets/js/sta-portal.js', array(), '1.0.0', true);
        // If your password checklist styles live in sta-portal.css, enqueue that too
        wp_enqueue_style('sta-portal-css', STA_PORTAL_URL . 'assets/css/sta-portal.css', array(), '1.0.0');
    }
}


    public function handle_change_password() {
    // Only logged-in users may change their password
    if ( ! is_user_logged_in() ) {
        wp_safe_redirect( add_query_arg('sta_error', urlencode('You must be logged in.'), site_url('/login/')) );
        exit;
    }

    // Nonce
    if ( empty($_POST['sta_change_pass_nonce']) || ! wp_verify_nonce($_POST['sta_change_pass_nonce'], 'sta_change_password') ) {
        wp_safe_redirect( add_query_arg('sta_error', urlencode('Security check failed.'), site_url('/manage-profile/')) );
        exit;
    }

    $uid     = get_current_user_id();
    $user    = wp_get_current_user();
    $back    = site_url('/manage-profile/');

    // Read inputs
    $current = (string) trim( wp_unslash( $_POST['sta_pass_current'] ?? '' ) );
    $new1    = (string) trim( wp_unslash( $_POST['sta_pass_new1']   ?? '' ) );
    $new2    = (string) trim( wp_unslash( $_POST['sta_pass_new2']   ?? '' ) );

    // Validate current password
    if ( ! wp_check_password( $current, $user->data->user_pass, $uid ) ) {
        wp_safe_redirect( add_query_arg('sta_error', urlencode('Current password is incorrect.'), $back) );
        exit;
    }

    // Match
    if ( $new1 === '' || $new2 === '' || $new1 !== $new2 ) {
        wp_safe_redirect( add_query_arg('sta_error', urlencode('New passwords do not match.'), $back) );
        exit;
    }

    // Complexity (same as your reset/signup rules)
    $okLen   = strlen($new1) >= 8;
    $okUpper = (bool) preg_match('/[A-Z]/', $new1);
    $okLower = (bool) preg_match('/[a-z]/', $new1);
    $okDigit = (bool) preg_match('/\d/',    $new1);
    $okSym   = (bool) preg_match('/[^A-Za-z0-9]/', $new1) && !preg_match('/[<>]/', $new1);

    if ( !($okLen && $okUpper && $okLower && $okDigit && $okSym) ) {
        $missing = array();
        if (!$okLen)   $missing[] = '8+ characters';
        if (!$okUpper) $missing[] = 'an uppercase letter';
        if (!$okLower) $missing[] = 'a lowercase letter';
        if (!$okDigit) $missing[] = 'a number';
        if (!$okSym)   $missing[] = 'a special character (not < or >)';
        $msg = 'Password does not meet the requirements: missing ' . implode(', ', $missing) . '.';
        wp_safe_redirect( add_query_arg('sta_error', urlencode($msg), $back) );
        exit;
    }

    // Optional: prevent reusing current password
    if ( wp_check_password( $new1, $user->data->user_pass, $uid ) ) {
        wp_safe_redirect( add_query_arg('sta_error', urlencode('New password must be different from the current password.'), $back) );
        exit;
    }

    // Change password (logs out sessions)
    wp_set_password( $new1, $uid );

    // Re-auth so the user stays on the page (session cookie, secure)
    wp_set_current_user( $uid );
    wp_set_auth_cookie( $uid, false, true );

    update_user_meta( $uid, 'sta_last_password_change', current_time('timestamp') );

    wp_safe_redirect( add_query_arg('sta_success', urlencode('Password updated successfully.'), $back) );
    exit;
}


}
