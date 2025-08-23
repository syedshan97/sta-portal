<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * STA_Portal_Hooks
 * Handles all action and filter hooks for the STA Portal plugin.
 */
class STA_Portal_Hooks {

    public function __construct() {

        add_action( 'template_redirect', array( 'STA_Portal_Hooks', 'redirect_site_root_to_portal' ) );

        // Protect the dashboard page for logged-in users only
        add_action('template_redirect', array($this, 'protect_dashboard_page'));

        // Hide WP admin bar for non-admin users on the frontend
        add_action('after_setup_theme', array($this, 'maybe_hide_admin_bar'));

        // Prevent non-admins from accessing /wp-admin/
       // add_action('admin_init', array($this, 'maybe_disable_admin_dashboard'));
        add_action('admin_init', array($this, 'restrict_admin_for_non_admins'));

        // Enqueue portal styles only on login/signup pages
        add_action('wp_enqueue_scripts', array($this, 'enqueue_portal_styles'));
        
        add_action('init', array($this, 'handle_google_login_redirect'));
        
        add_action('init', array($this, 'handle_google_callback'));
        
        add_action('init', array($this, 'handle_ms_login_redirect'));
        
        add_action('init', array($this, 'handle_ms_callback'));
        
        add_filter('ajax_query_attachments_args', array($this, 'restrict_media_to_author'));
        add_filter('rest_attachment_query',       array($this, 'restrict_media_to_author_rest'), 10, 2);
        add_action('add_attachment',              array($this, 'ensure_attachment_author'));
        add_action('init', function () { $role = get_role('subscriber'); if ( $role && ! $role->has_cap('upload_files') ) { $role->add_cap('upload_files'); } });
        add_action('admin_init', function () {
    if ( current_user_can('administrator') ) return;

    // Allow AJAX and upload endpoints
    $pagenow = isset($GLOBALS['pagenow']) ? $GLOBALS['pagenow'] : '';
    if ( wp_doing_ajax() ) return;
    if ( in_array( $pagenow, array('admin-ajax.php','async-upload.php','admin-post.php'), true ) ) return;

    // Everything else in wp-admin: redirect
    wp_redirect( site_url('/dashboard/') );
    exit;
});
        
        add_action('wp_login', function($user_login, $user){
    // store as timestamp for easy formatting later
    update_user_meta($user->ID, 'sta_last_login', current_time('timestamp'));
}, 10, 2);


        
    }

     // Redirect site root "/" to Login (guest) or Dashboard (logged-in)
    public static function redirect_site_root_to_portal() {
    // Only front-end
    if ( is_admin() || wp_doing_ajax() ) return;
    if ( defined('REST_REQUEST') && REST_REQUEST ) return;
    if ( defined('DOING_CRON') && DOING_CRON ) return;

    // Only when the request is exactly the root path
    $req_path = parse_url( $_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH );
    if ( $req_path !== '/' ) return;

    // Avoid loops if root already points to one of these (defensive)
    $current = trailingslashit( home_url( $req_path ) );
    $login   = trailingslashit( site_url('/login/') );
    $dash    = trailingslashit( site_url('/dashboard/') );
    if ( $current === $login || $current === $dash ) return;

    if ( is_user_logged_in() ) {
        wp_redirect( $dash );
    } else {
        wp_redirect( $login );
    }
    exit;
    }

    /**
     * Redirect non-logged-in users away from the dashboard page.
     */
    public function protect_dashboard_page() {
        if ( is_page( array('dashboard', 'manage-profile') ) && !is_user_logged_in() ) {
            wp_redirect(site_url('/login/'));
            exit;
        }
    }

    /**
     * Hide the WordPress admin bar for non-admin users.
     */
    public function maybe_hide_admin_bar() {
        if ( ! current_user_can('administrator') && ! is_admin() ) {
            show_admin_bar(false);
        }
    }


    public function restrict_admin_for_non_admins() {
    // Allow admins
    if ( current_user_can('administrator') ) {
        return;
    }

    // Let these core endpoints through (needed for front-end forms & uploads)
    $script = isset($_SERVER['PHP_SELF']) ? basename($_SERVER['PHP_SELF']) : '';
    $whitelist = array('admin-post.php', 'admin-ajax.php', 'async-upload.php');
    if ( in_array($script, $whitelist, true) ) {
        return;
    }

    // Also allow REST and CRON
    if ( defined('REST_REQUEST') && REST_REQUEST ) return;
    if ( defined('DOING_CRON') && DOING_CRON ) return;

    // Block the rest of wp-admin for non-admins
    if ( is_admin() ) {
        wp_safe_redirect( site_url('/dashboard/') );
        exit;
    }
}


    /**
     * Enqueue portal CSS styles on login and signup pages only.
     */
   public function enqueue_portal_styles() {
    // shared styles for auth/profile pages
    if ( is_page( array('login','signup','forgot-password','reset-password','manage-profile') ) ) {
        wp_enqueue_style('sta-portal-css', STA_PORTAL_URL.'assets/css/sta-portal.css', [], '1.0.0');
    }
    // manage profile only
    if ( is_page('manage-profile') && is_user_logged_in() ) {
        wp_enqueue_style('sta-profile-css', STA_PORTAL_URL.'assets/css/sta-profile.css', [], '1.0.0');
        wp_enqueue_media();
        wp_enqueue_script('sta-profile-js', STA_PORTAL_URL.'assets/js/sta-profile.js', ['jquery'], '1.0.0', true);
        wp_localize_script('sta-profile-js', 'staProfile', [
            'nonce'   => wp_create_nonce('sta_profile_avatar'),
            'ajaxurl' => admin_url('admin-ajax.php'),
        ]);
    }
}
    
    public function handle_google_login_redirect() {
    if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/google-login/') !== false) {
        $google_enabled = get_option('sta_portal_google_enable');
        if (!$google_enabled) wp_die('Google Login is disabled.');
        $client_id = get_option('sta_portal_google_client_id');
        $callback = get_option('sta_portal_google_callback_url');
        $state = wp_create_nonce('sta_portal_google_login');
        $scope = 'email profile';
        $google_oauth_url = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
            'response_type' => 'code',
            'client_id' => $client_id,
            'redirect_uri' => $callback,
            'scope' => $scope,
            'state' => $state,
            'access_type' => 'online',
            'prompt' => 'select_account'
        ]);
        wp_redirect($google_oauth_url);
        exit;
    }
    }
    
    public function handle_google_callback() {
    // Adjust the path to match your callback URL slug
    if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/google-login-callback/') !== false) {
        // Security: Verify state (optional)
        if (!isset($_GET['state']) || !wp_verify_nonce($_GET['state'], 'sta_portal_google_login')) {
            wp_die('Invalid state/nonce. Please try again.');
        }

        // Check for error or code in callback
        if (isset($_GET['error'])) {
            wp_die('Google login error: ' . esc_html($_GET['error']));
        }
        if (empty($_GET['code'])) {
            wp_die('Missing Google auth code.');
        }

        // Get tokens from Google
        $client_id = get_option('sta_portal_google_client_id');
        $client_secret = get_option('sta_portal_google_client_secret');
        $callback = get_option('sta_portal_google_callback_url');
        $token_url = 'https://oauth2.googleapis.com/token';

        $response = wp_remote_post($token_url, [
            'body' => [
                'code' => $_GET['code'],
                'client_id' => $client_id,
                'client_secret' => $client_secret,
                'redirect_uri' => $callback,
                'grant_type' => 'authorization_code'
            ]
        ]);
        if (is_wp_error($response)) wp_die('Token request failed.');

        $token_data = json_decode(wp_remote_retrieve_body($response), true);
        if (empty($token_data['access_token'])) wp_die('Failed to get access token.');

        // Get user info from Google
        $userinfo = wp_remote_get('https://www.googleapis.com/oauth2/v2/userinfo', [
            'headers' => ['Authorization' => 'Bearer ' . $token_data['access_token']]
        ]);
        if (is_wp_error($userinfo)) wp_die('Failed to get user info.');
        $user_data = json_decode(wp_remote_retrieve_body($userinfo), true);

        if (empty($user_data['email'])) wp_die('No email received from Google.');
        $email = sanitize_email($user_data['email']);
        $name  = sanitize_text_field($user_data['name'] ?? 'Google User');

        // Try to find user by email
        $user = get_user_by('email', $email);
        

        if (!$user) {
            // Register new user with unique portal_user_id
            $random_pass = wp_generate_password(12, true);
            $last_id = get_option('sta_portal_last_user_id', 4500);
            $next_id = intval($last_id) + 1;
            $user_id = wp_create_user($email, $random_pass, $email);
            if (is_wp_error($user_id)) wp_die('Could not create user: ' . $user_id->get_error_message());
            wp_update_user(['ID' => $user_id, 'display_name' => $name]);
            update_user_meta($user_id, 'portal_user_id', $next_id);
            update_option('sta_portal_last_user_id', $next_id);
            $user = get_user_by('id', $user_id);
        }
        
        update_user_meta($user->ID, 'sta_auth_provider', 'google');
        if (!empty($user_data['id'])) {
            update_user_meta($user->ID, 'sta_google_sub', sanitize_text_field($user_data['id']));
        }

        // Log the user in
        wp_set_current_user($user->ID);
        wp_set_auth_cookie($user->ID);
        wp_redirect(site_url('/dashboard/'));
        exit;
    }
    }
    
    public function handle_ms_login_redirect() {
    if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/microsoft-login/') !== false) {
        if (!get_option('sta_portal_ms_enable')) wp_die('Microsoft login is disabled.');

        $client_id = trim(get_option('sta_portal_ms_client_id'));
        $callback  = trim(get_option('sta_portal_ms_callback_url'));
        $tenant    = trim(get_option('sta_portal_ms_tenant', 'organizations')); // you want orgs only
        if (!$client_id || !$callback) wp_die('Microsoft login is not configured.');

        $state = wp_create_nonce('sta_portal_ms_login');
        $authorize = "https://login.microsoftonline.com/{$tenant}/oauth2/v2.0/authorize";

        $params = [
            'client_id'     => $client_id,
            'response_type' => 'code',
            'redirect_uri'  => $callback,
            'response_mode' => 'query',
            'scope'         => 'openid profile email User.Read',
            'state'         => $state,
            // 'prompt' => 'select_account', // uncomment if you want to force account picker
        ];

        wp_redirect($authorize . '?' . http_build_query($params));
        exit;
    }
}

public function handle_ms_callback() {
    if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/microsoft-login-callback/') !== false) {
        // Verify state
        if (!isset($_GET['state']) || !wp_verify_nonce($_GET['state'], 'sta_portal_ms_login')) {
            wp_die('Invalid state. Please try again.');
        }
        if (isset($_GET['error'])) {
            wp_die('Microsoft login error: ' . esc_html($_GET['error_description'] ?? $_GET['error']));
        }
        if (empty($_GET['code'])) {
            wp_die('Missing authorization code.');
        }

        $client_id     = trim(get_option('sta_portal_ms_client_id'));
        $client_secret = trim(get_option('sta_portal_ms_client_secret'));
        $callback      = trim(get_option('sta_portal_ms_callback_url'));
        $tenant        = trim(get_option('sta_portal_ms_tenant', 'organizations'));
        if (!$client_id || !$client_secret || !$callback) wp_die('Microsoft login is not configured.');

        $token_endpoint = "https://login.microsoftonline.com/{$tenant}/oauth2/v2.0/token";

        // Exchange code for tokens
        $response = wp_remote_post($token_endpoint, [
            'body' => [
                'client_id'     => $client_id,
                'client_secret' => $client_secret,
                'grant_type'    => 'authorization_code',
                'code'          => $_GET['code'],
                'redirect_uri'  => $callback,
                'scope'         => 'openid profile email User.Read',
            ],
            'timeout' => 20,
        ]);
        if (is_wp_error($response)) wp_die('Token request failed.');
        $token_data = json_decode(wp_remote_retrieve_body($response), true);
        if (empty($token_data['access_token'])) wp_die('Failed to obtain access token.');

        $access_token = $token_data['access_token'];

        // Get user via Microsoft Graph
        $me = wp_remote_get('https://graph.microsoft.com/v1.0/me', [
            'headers' => ['Authorization' => 'Bearer ' . $access_token],
            'timeout' => 20,
        ]);
        if (is_wp_error($me)) wp_die('Failed to fetch user from Graph.');
        $me_data = json_decode(wp_remote_retrieve_body($me), true);

        // Prefer 'mail', fallback to 'userPrincipalName'
        $email = '';
        if (!empty($me_data['mail'])) {
            $email = sanitize_email($me_data['mail']);
        } elseif (!empty($me_data['userPrincipalName'])) {
            $email = sanitize_email($me_data['userPrincipalName']);
        }
        if (!$email) wp_die('No email available on Microsoft account.');

        $name = !empty($me_data['displayName']) ? sanitize_text_field($me_data['displayName']) : 'Microsoft User';

        // Find or create WP user
        $user = get_user_by('email', $email);
        if (!$user) {
            $random_pass = wp_generate_password(12, true);
            $last_id = get_option('sta_portal_last_user_id', 4500);
            $next_id = intval($last_id) + 1;

            $user_id = wp_create_user($email, $random_pass, $email);
            if (is_wp_error($user_id)) wp_die('Could not create user: ' . $user_id->get_error_message());
            wp_update_user(['ID' => $user_id, 'display_name' => $name]);

            update_user_meta($user_id, 'portal_user_id', $next_id);
            update_option('sta_portal_last_user_id', $next_id);

            $user = get_user_by('id', $user_id);
        }
        
        update_user_meta($user->ID, 'sta_auth_provider', 'microsoft');
        if (!empty($me_data['id'])) {
            update_user_meta($user->ID, 'sta_ms_id', sanitize_text_field($me_data['id']));
        }

        // Log in + redirect
        wp_set_current_user($user->ID);
        wp_set_auth_cookie($user->ID);
        wp_redirect(site_url('/dashboard/'));
        exit;
    }
}

// // Limit the media modal (admin-ajax query-attachments) to current user's uploads
// public function restrict_media_to_author( $args ) {
//     // Let admins see everything
//     if ( current_user_can('manage_options') ) {
//         return $args;
//     }
//     $args['author'] = get_current_user_id();
//     return $args;
// }

// Limit the media modal (admin-ajax query-attachments) to current user's uploads
public function restrict_media_to_author( $args ) {
    // Let admins see everything
    if ( current_user_can('manage_options') ) {
        return $args;
    }

    // If the user can't upload, don't interfere
    if ( ! current_user_can('upload_files') ) {
        return $args;
    }

    // Force author to current user
    $args['author'] = get_current_user_id();

    // Attachments are usually 'inherit' status; keep query robust
    if ( empty( $args['post_status'] ) ) {
        $args['post_status'] = array( 'inherit', 'private' );
    }

    return $args;
}


// // Limit the REST /wp/v2/media listing (some WP versions / contexts use REST)
// public function restrict_media_to_author_rest( $args, $request ) {
//     if ( current_user_can('manage_options') ) {
//         return $args;
//     }
//     $args['author'] = get_current_user_id();
//     return $args;
// }

// Limit the REST /wp/v2/media listing (some WP versions / contexts use REST)
public function restrict_media_to_author_rest( $args, $request ) {
    if ( current_user_can('manage_options') ) {
        return $args;
    }

    if ( ! current_user_can('upload_files') ) {
        return $args;
    }

    // Only target media list routes
    $route = method_exists( $request, 'get_route' ) ? $request->get_route() : '';
    if ( strpos( $route, '/wp/v2/media' ) === false ) {
        return $args;
    }

    $args['author'] = get_current_user_id();

    if ( empty( $args['status'] ) ) {
        $args['status'] = array( 'inherit', 'private' );
    }

    return $args;
}


// // Make sure new uploads are owned by the uploader (important for subscribers)
// public function ensure_attachment_author( $attachment_id ) {
//     if ( ! is_user_logged_in() ) return;
//     if ( current_user_can('manage_options') ) return;

//     $post = get_post( $attachment_id );
//     if ( $post && (int) $post->post_author === 0 ) {
//         wp_update_post( array(
//             'ID' => $attachment_id,
//             'post_author' => get_current_user_id(),
//         ) );
//     }
// }

// Make sure new uploads are owned by the uploader (important for subscribers)
public function ensure_attachment_author( $attachment_id ) {
    if ( ! is_user_logged_in() ) return;
    if ( current_user_can('manage_options') ) return;
    if ( ! current_user_can('upload_files') ) return;

    $post = get_post( $attachment_id );
    if ( ! $post || $post->post_type !== 'attachment' ) return;

    $current = get_current_user_id();

    // If attachment isn't attributed, or attributed to someone else, assign to current user
    if ( intval( $post->post_author ) !== $current ) {
        // Guard against recursive hooks
        remove_action( 'add_attachment', array( $this, 'ensure_attachment_author' ) );
        wp_update_post( array(
            'ID'          => $attachment_id,
            'post_author' => $current,
        ) );
        add_action( 'add_attachment', array( $this, 'ensure_attachment_author' ) );
    }
}



}