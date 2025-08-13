<?php
if ( ! defined('ABSPATH') ) exit;

class STA_Portal_Email_Verification {

    const TOKEN_TTL_SECS = 48 * 3600; // 48 hours

    public function __construct() {
        // Pretty endpoints handled like your other routes
        add_action('init', array($this, 'maybe_handle_verify'));
        add_action('init', array($this, 'maybe_handle_resend'));
    }

    /* ---------- PUBLIC HELPERS ---------- */

    // Send a verification email to a user (call on local signup or when needed)
    public static function send_verification_email( $user_id, $email ) {
        $token = self::generate_token( $user_id, $email );
        $verify_url = add_query_arg(array(
            'uid'   => $user_id,
            'token' => rawurlencode($token),
        ), site_url('/verify-email/'));

        $blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

        $subject = sprintf( '[%s] Confirm your email address', $blogname );

        $message  = '<p>Hi,</p>';
        $message .= '<p>Please confirm your email address to activate your account.</p>';
        $message .= '<p><a href="'. esc_url($verify_url) .'" style="display:inline-block;padding:10px 16px;background:#e30b41;color:#fff;border-radius:6px;text-decoration:none;">Verify Email</a></p>';
        $message .= '<p>Or copy & paste this link:<br>'. esc_html($verify_url) .'</p>';
        $message .= '<p>This link expires in 48 hours.</p>';
        $headers = array('Content-Type: text/html; charset=UTF-8');

        // record last sent time
        update_user_meta($user_id, 'sta_email_verification_sent', time());

        return wp_mail( $email, $subject, $message, $headers );
    }

    // A link you can show in “unverified” errors, etc.
    public static function get_resend_url( $user_id ) {
        $nonce = wp_create_nonce('sta_resend_' . $user_id);
        return add_query_arg(array(
            'uid'   => $user_id,
            'nonce' => $nonce,
        ), site_url('/resend-verification/'));
    }

    /* ---------- ROUTE HANDLERS ---------- */

    // /verify-email/?uid=..&token=..
    public function maybe_handle_verify() {
        if ( ! isset($_SERVER['REQUEST_URI']) ) return;
        if ( strpos($_SERVER['REQUEST_URI'], '/verify-email/') === false ) return;

        $uid   = isset($_GET['uid'])   ? intval($_GET['uid'])   : 0;
        $token = isset($_GET['token']) ? (string) $_GET['token'] : '';

        if ( $uid <= 0 || empty($token) ) {
            wp_safe_redirect( add_query_arg('sta_error', urlencode('Invalid verification link.'), site_url('/login/')) ); exit;
        }

        $user = get_user_by('id', $uid);
        if ( ! $user ) {
            wp_safe_redirect( add_query_arg('sta_error', urlencode('Account not found.'), site_url('/login/')) ); exit;
        }

        if ( self::validate_token( $token, $uid, $user->user_email ) ) {
            update_user_meta($uid, 'sta_email_verified', 1);
            update_user_meta($uid, 'sta_email_verified_at', time());
            wp_safe_redirect( add_query_arg('sta_success', urlencode('Email verified! You can sign in now.'), site_url('/login/')) ); exit;
        } else {
            wp_safe_redirect( add_query_arg('sta_error', urlencode('Verification link is invalid or expired.'), site_url('/login/')) ); exit;
        }
    }

    // /resend-verification/?uid=..&nonce=..
    public function maybe_handle_resend() {
        if ( ! isset($_SERVER['REQUEST_URI']) ) return;
        if ( strpos($_SERVER['REQUEST_URI'], '/resend-verification/') === false ) return;

        $uid   = isset($_GET['uid'])   ? intval($_GET['uid'])   : 0;
        $nonce = isset($_GET['nonce']) ? (string) $_GET['nonce'] : '';

        if ( $uid <= 0 || empty($nonce) || ! wp_verify_nonce($nonce, 'sta_resend_' . $uid) ) {
            wp_safe_redirect( add_query_arg('sta_error', urlencode('Invalid request.'), site_url('/login/')) ); exit;
        }

        $user = get_user_by('id', $uid);
        if ( ! $user ) {
            wp_safe_redirect( add_query_arg('sta_error', urlencode('Account not found.'), site_url('/login/')) ); exit;
        }

        // If already verified, no need to resend
        if ( intval(get_user_meta($uid, 'sta_email_verified', true)) === 1 ) {
            wp_safe_redirect( add_query_arg('sta_success', urlencode('Your email is already verified.'), site_url('/login/')) ); exit;
        }

        self::send_verification_email($uid, $user->user_email);
        wp_safe_redirect( add_query_arg('sta_success', urlencode('Verification email sent.'), site_url('/login/')) ); exit;
    }

    /* ---------- TOKEN UTILS ---------- */

    private static function generate_token( $user_id, $email ) {
        $ts   = time();
        $data = $user_id . '|' . $ts . '|' . strtolower(trim($email));
        $sig  = hash_hmac('sha256', $data, wp_salt('auth'));
        $raw  = $user_id . ':' . $ts . ':' . $sig;
        return rtrim(strtr(base64_encode($raw), '+/', '-_'), '=');
    }

    private static function validate_token( $token, $user_id, $email_current ) {
        $raw = base64_decode(strtr($token, '-_', '+/'));
        if (! $raw) return false;
        $parts = explode(':', $raw);
        if ( count($parts) !== 3 ) return false;

        list($uid, $ts, $sig) = $parts;
        if ( intval($uid) !== intval($user_id) ) return false;
        if ( (time() - intval($ts)) > self::TOKEN_TTL_SECS ) return false;

        $data = $user_id . '|' . intval($ts) . '|' . strtolower(trim($email_current));
        $calc = hash_hmac('sha256', $data, wp_salt('auth'));

        return hash_equals($sig, $calc);
    }
}
