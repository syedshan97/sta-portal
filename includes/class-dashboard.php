<?php
// includes/class-dashboard.php
if ( ! defined('ABSPATH') ) exit;

class STA_Portal_Dashboard {

    public function __construct() {
        // Shortcode you’ll place on the Dashboard page
        add_shortcode('sta_portal_dashboard', array($this, 'render_dashboard_shortcode'));
    }

    /**
     * Renders the main dashboard template.
     */
    public function render_dashboard_shortcode($atts = []) {
        if ( ! is_user_logged_in() ) {
            // Safety (you also have a redirect elsewhere)
            return '<div class="sta-dash-login-needed">Please <a href="'. esc_url(site_url('/login/')) .'">log in</a> to view your dashboard.</div>';
        }

        // Enqueue dashboard CSS
        wp_enqueue_style('sta-dashboard-css', STA_PORTAL_URL . 'assets/css/sta-dashboard.css', [], '1.0.0');

        // Collect user data once and pass to template
        $user = wp_get_current_user();
        $data = $this->collect_user_data($user);

        ob_start();
        $tpl = STA_PORTAL_PATH . 'templates/dashboard/dashboard.php';
        if ( file_exists($tpl) ) {
            // $data is available inside template
            include $tpl;
        } else {
            echo '<p>Dashboard template is missing.</p>';
        }
        return ob_get_clean();
    }

    /**
     * Gather the fields needed by the Welcome Header.
     */
    private function collect_user_data(WP_User $user) {
        $user_id = $user->ID;

        $portal_id = get_user_meta($user_id, 'portal_user_id', true);
        $job_title = get_user_meta($user_id, 'sta_job_title', true);
        $org       = get_user_meta($user_id, 'sta_org', true);

        $avatar_id = intval( get_user_meta($user_id, 'sta_avatar_id', true) );
        $avatar    = $avatar_id ? wp_get_attachment_image_url($avatar_id, 'thumbnail') : get_avatar_url($user_id);

        $member_since = '';
        if ( ! empty($user->user_registered) ) {
            $ts = strtotime($user->user_registered);
            $member_since = $ts ? date_i18n(get_option('date_format'), $ts) : '';
        }

        return array(
            'display_name' => $user->display_name ?: $user->user_login,
            'email'        => $user->user_email,
            'member_since' => $member_since,
            'portal_id'    => $portal_id ?: '—',
            'job_title'    => $job_title ?: '',
            'organization' => $org ?: '',
            'avatar_url'   => $avatar,
            'edit_profile_url' => site_url('/manage-profile/'),
        );
    }
}
