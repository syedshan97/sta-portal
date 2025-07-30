<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * STA_Portal_Hooks
 * Handles all action and filter hooks for the STA Portal plugin.
 */
class STA_Portal_Hooks {

    public function __construct() {
        // Protect the dashboard page for logged-in users only
        add_action('template_redirect', array($this, 'protect_dashboard_page'));

        // Hide WP admin bar for non-admin users on the frontend
        add_action('after_setup_theme', array($this, 'maybe_hide_admin_bar'));

        // Prevent non-admins from accessing /wp-admin/
        add_action('admin_init', array($this, 'maybe_disable_admin_dashboard'));

        // Enqueue portal styles only on login/signup pages
        add_action('wp_enqueue_scripts', array($this, 'enqueue_portal_styles'));
    }

    /**
     * Redirect non-logged-in users away from the dashboard page.
     */
    public function protect_dashboard_page() {
        if ( is_page('dashboard') && !is_user_logged_in() ) {
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

    /**
     * Redirect non-admin users away from the backend.
     */
    public function maybe_disable_admin_dashboard() {
        if ( ! current_user_can('administrator') && ! defined('DOING_AJAX') ) {
            wp_redirect( site_url('/dashboard/') );
            exit;
        }
    }

    /**
     * Enqueue portal CSS styles on login and signup pages only.
     */
    public function enqueue_portal_styles() {
        if ( is_page(['login', 'signup']) ) {
            wp_enqueue_style(
                'sta-portal-css',
                STA_PORTAL_URL . 'assets/css/sta-portal.css',
                [],
                '1.0.0'
            );
        }
    }
}
