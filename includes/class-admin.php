<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * STA_Portal_Admin
 * Handles admin menu and settings pages for STA Portal plugin.
 */
class STA_Portal_Admin {

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
    }

    /**
     * Add STA Portal main menu and Social Login submenu in WP admin
     */
    public function add_admin_menu() {
        add_menu_page(
            'STA Portal',
            'STA Portal',
            'manage_options',
            'sta-portal',
            array( $this, 'render_dashboard_page' ),
            'dashicons-groups'
        );

        add_submenu_page(
            'sta-portal',
            'Social Login',
            'Social Login',
            'manage_options',
            'sta-portal-social-login',
            array( $this, 'render_social_login_page' )
        );

        // Users listing (under STA Portal)
        add_submenu_page(
    'sta-portal',                     // parent slug (use the same one you used for STA Portal top-level)
    'Users',                          // page title
    'Users',                          // menu title
    'list_users',                     // capability (admins have it; shows only to roles that can list users)
    'sta-portal-users',               // menu slug
    array($this, 'render_users_page') // callback
     );

    }

    /**
     * Register social login settings for Google (expandable for LinkedIn)
     */
    public function register_settings() {
        // Google
        register_setting( 'sta_portal_social_login', 'sta_portal_google_enable' );
        register_setting( 'sta_portal_social_login', 'sta_portal_google_client_id' );
        register_setting( 'sta_portal_social_login', 'sta_portal_google_client_secret' );
        register_setting( 'sta_portal_social_login', 'sta_portal_google_callback_url' );
        // Microsoft 365 (Entra ID)
        register_setting( 'sta_portal_social_login', 'sta_portal_ms_enable' );
register_setting( 'sta_portal_social_login', 'sta_portal_ms_client_id' );
register_setting( 'sta_portal_social_login', 'sta_portal_ms_client_secret' );
register_setting( 'sta_portal_social_login', 'sta_portal_ms_callback_url' );
register_setting( 'sta_portal_social_login', 'sta_portal_ms_tenant' ); // default: organizations

    }

    /**
     * Dummy dashboard page (optional)
     */
    public function render_dashboard_page() {
        echo '<div class="wrap"><h1>STA Portal Dashboard</h1><p>Welcome to your portal settings.</p></div>';
    }

    /**
     * Render the Social Login settings page
     */
    public function render_social_login_page() {
        ?>
        <div class="wrap">
            <h1>STA Portal: Social Login Settings</h1>
            <form method="post" action="options.php">
                <?php settings_fields( 'sta_portal_social_login' ); ?>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row">Enable Google Login</th>
                        <td>
                            <input type="checkbox" name="sta_portal_google_enable" value="1" <?php checked( get_option('sta_portal_google_enable'), 1 ); ?> />
                            <label for="sta_portal_google_enable">Show "Sign in with Google" on login and signup</label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Google Client ID</th>
                        <td>
                            <input type="text" name="sta_portal_google_client_id" value="<?php echo esc_attr(get_option('sta_portal_google_client_id')); ?>" class="regular-text" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Google Client Secret</th>
                        <td>
                            <input type="password" name="sta_portal_google_client_secret" value="<?php echo esc_attr(get_option('sta_portal_google_client_secret')); ?>" class="regular-text" autocomplete="new-password" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Google Callback URL</th>
                        <td>
                            <input type="text" name="sta_portal_google_callback_url" value="<?php echo esc_attr(get_option('sta_portal_google_callback_url')); ?>" class="regular-text" />
                            <p class="description">Copy this URL into your Google Cloud Console OAuth settings. Example: <code>https://yourdomain.com/google-login-callback/</code></p>
                        </td>
                    </tr>
                    <tr>
  <th colspan="2"><h2>Microsoft 365 (Entra ID) Login</h2></th>
</tr>
<tr>
  <th scope="row">Enable Microsoft Login</th>
  <td>
    <input type="checkbox" name="sta_portal_ms_enable" value="1" <?php checked( get_option('sta_portal_ms_enable'), 1 ); ?> />
    <label>Show "Sign in with Microsoft" on login and signup</label>
  </td>
</tr>
<tr>
  <th scope="row">Client ID (Application ID)</th>
  <td>
    <input type="text" name="sta_portal_ms_client_id" value="<?php echo esc_attr(get_option('sta_portal_ms_client_id')); ?>" class="regular-text" />
  </td>
</tr>
<tr>
  <th scope="row">Client Secret</th>
  <td>
    <input type="password" name="sta_portal_ms_client_secret" value="<?php echo esc_attr(get_option('sta_portal_ms_client_secret')); ?>" class="regular-text" autocomplete="new-password" />
  </td>
</tr>
<tr>
  <th scope="row">Callback URL</th>
  <td>
    <input type="text" name="sta_portal_ms_callback_url" value="<?php echo esc_attr(get_option('sta_portal_ms_callback_url')); ?>" class="regular-text" />
    <p class="description">Use this in Azure: e.g. <code>https://yourdomain.com/microsoft-login-callback/</code></p>
  </td>
</tr>
<tr>
  <th scope="row">Tenant</th>
  <td>
    <?php $tenant = get_option('sta_portal_ms_tenant', 'organizations'); ?>
    <select name="sta_portal_ms_tenant">
      <option value="organizations" <?php selected($tenant, 'organizations'); ?>>organizations (work/school only)</option>
      <option value="common" <?php selected($tenant, 'common'); ?>>common (work/school + personal)</option>
      <option value="consumers" <?php selected($tenant, 'consumers'); ?>>consumers (personal only)</option>
    </select>
    <p class="description">You asked for orgs only → keep as <b>organizations</b>.</p>
  </td>
</tr>

                </table>
                <?php submit_button(); ?>
            </form>
            <!-- LinkedIn section can be added here later -->
        </div>
        <?php
    }

    public function render_users_page() {
    if ( ! current_user_can('list_users') ) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    // Pagination & search
    $base_url = admin_url('admin.php?page=sta-portal-users');
    $paged    = max(1, intval($_GET['paged'] ?? 1));
    $per_page = 20;
    $search_q = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';

    $args = array(
        'number' => $per_page,
        'offset' => ($paged - 1) * $per_page,
        'orderby' => 'registered',
        'order'   => 'DESC',
        'fields'  => array('ID', 'display_name', 'user_email'),
    );

    if ($search_q !== '') {
        $args['search']         = '*'. $search_q .'*';
        $args['search_columns'] = array('user_login','user_email','display_name');
    }

    $query = new WP_User_Query($args);
    $users = $query->get_results();
    $total = intval($query->get_total());
    $total_pages = max(1, ceil($total / $per_page));

    // Small styles (scoped to this page)
    echo '<div class="wrap"><h1 class="wp-heading-inline">STA Portal — Users</h1>';
    echo '<hr class="wp-header-end" />';

    // Search form
    echo '<form method="get" style="margin:12px 0;">';
    echo '<input type="hidden" name="page" value="sta-portal-users" />';
    echo '<p class="search-box">';
    echo '<label class="screen-reader-text" for="user-search-input">Search Users:</label>';
    echo '<input type="search" id="user-search-input" name="s" value="'. esc_attr($search_q) .'" />';
    echo '<input type="submit" id="search-submit" class="button" value="Search Users" />';
    echo '</p>';
    echo '</form>';

    // Table
    echo '<style>
        .sta-users-table{width:100%; border-collapse:collapse; background:#fff; }
        .sta-users-table th, .sta-users-table td{border-bottom:1px solid #e7ebf2; padding:10px; text-align:left;}
        .sta-users-table th{background:#f7f9fd; font-weight:600;}
        .sta-badge{display:inline-block; padding:2px 8px; border-radius:999px; border:1px solid #e7eaf2; background:#f5f7fb; font-size:12px;}
        .sta-badge--ok{background:#f6ffed; border-color:#b7eb8f; color:#237804;}
        .sta-badge--no{background:#fff1f0; border-color:#ffccc7; color:#a8071a;}
    </style>';

    echo '<table class="widefat fixed striped sta-users-table">';
    echo '<thead><tr>';
    echo '<th>Name</th>';
    echo '<th>Email</th>';
    echo '<th>Organization</th>';
    echo '<th>Country</th>';
    echo '<th>Sign up via</th>';
    echo '<th>Email Verified</th>';
    echo '</tr></thead><tbody>';

    if ($users) {
        foreach ($users as $u) {
            $org      = get_user_meta($u->ID, 'sta_org', true);
            $country  = get_user_meta($u->ID, 'sta_addr_country', true);
            $provider = get_user_meta($u->ID, 'sta_auth_provider', true);
            $verified = intval(get_user_meta($u->ID, 'sta_email_verified', true));

            switch ($provider) {
                case 'google':    $provider_label = 'Google'; break;
                case 'microsoft': $provider_label = 'Microsoft 365'; break;
                default:          $provider_label = 'Email';
            }
            $social_verified = ( $verified === 1 || in_array($provider, array('google','microsoft'), true) ) ? 1 : 0;
            $v_badge = $social_verified === 1
                ? '<span class="sta-badge sta-badge--ok">Yes</span>'
                : '<span class="sta-badge sta-badge--no">No</span>';

            echo '<tr>';
            echo '<td>'. esc_html($u->display_name ?: '-') .'</td>';
            echo '<td><a href="mailto:'. esc_attr($u->user_email) .'">'. esc_html($u->user_email) .'</a></td>';
            echo '<td>'. esc_html($org ?: '-') .'</td>';
            echo '<td>'. esc_html($country ?: '-') .'</td>';
            echo '<td><span class="sta-badge">'. esc_html($provider_label) .'</span></td>';
            echo '<td>'. $v_badge .'</td>';
            echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="6">No users found.</td></tr>';
    }

    echo '</tbody></table>';

    // Pagination
    if ($total_pages > 1) {
        echo '<div class="tablenav"><div class="tablenav-pages" style="margin-top:10px;">';
        $page_links = paginate_links( array(
            'base'      => add_query_arg('paged', '%#%', $base_url . ($search_q ? '&s=' . urlencode($search_q) : '')),
            'format'    => '',
            'prev_text' => '&laquo;',
            'next_text' => '&raquo;',
            'total'     => $total_pages,
            'current'   => $paged,
        ) );
        echo $page_links ? $page_links : '';
        echo '</div></div>';
    }

    echo '</div>'; // .wrap
}

}
