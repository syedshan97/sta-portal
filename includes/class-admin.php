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
        // Later: LinkedIn settings
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
                </table>
                <?php submit_button(); ?>
            </form>
            <!-- LinkedIn section can be added here later -->
        </div>
        <?php
    }
}
