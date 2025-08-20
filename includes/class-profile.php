<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class STA_Portal_Profile {

    public function __construct() {
        // Save profile (front-end form posts to admin-post.php)
        add_action('admin_post_sta_profile_save',        array($this, 'handle_profile_save'));
        add_action('admin_post_nopriv_sta_profile_save', array($this, 'redirect_login'));

        // Avatar upload (AJAX, logged-in)
        add_action('wp_ajax_sta_portal_save_avatar', array($this, 'ajax_save_avatar'));
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
}
