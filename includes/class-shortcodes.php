<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class STA_Portal_Shortcodes {

    public function __construct() {
        add_shortcode( 'portal_login_form', array( $this, 'login_form_shortcode' ) );
        add_shortcode( 'portal_signup_form', array( $this, 'signup_form_shortcode' ) );
        add_shortcode( 'portal_lost_password_form', array( $this, 'lost_password_form_shortcode' ) );
        add_shortcode( 'portal_reset_password_form', array( $this, 'reset_password_form_shortcode' ) );
        add_shortcode('portal_logout_link', array($this, 'logout_link_shortcode'));
        add_shortcode('portal_user_menu', array($this, 'user_menu_shortcode'));



    }

    public function login_form_shortcode( $atts ) {
        ob_start();
        include( STA_PORTAL_PATH . 'templates/login-form.php' );
        return ob_get_clean();
    }

    public function signup_form_shortcode( $atts ) {
        ob_start();
        include( STA_PORTAL_PATH . 'templates/signup-form.php' );
        return ob_get_clean();
    }

    public function lost_password_form_shortcode( $atts ) {
    ob_start();
    include( STA_PORTAL_PATH . 'templates/lost-password-form.php' );
    return ob_get_clean();
    
   }

    public function reset_password_form_shortcode( $atts ) {
    ob_start();
    include( STA_PORTAL_PATH . 'templates/reset-password-form.php' );
    return ob_get_clean();

    }

    public function logout_link_shortcode($atts) {
    // Hide link for guests
    if ( ! is_user_logged_in() ) return '';

    $atts = shortcode_atts(array(
        'text'     => 'Logout',
        'class'    => 'sta-logout-link',
        // Leave empty to use default success message redirect to /login/
        'redirect' => '',
    ), $atts, 'portal_logout_link');

    // Default redirect: /login/?sta_success=You’ve been logged out.
    $default_redirect = add_query_arg(
        'sta_success',
        urlencode("You’ve been logged out."),
        site_url('/login/')
    );

    $target_redirect = $atts['redirect'] ? $atts['redirect'] : $default_redirect;

    // Secure, nonce-protected WordPress logout URL
    $url = wp_logout_url( $target_redirect );

    return '<a href="'. esc_url($url) .'" class="'. esc_attr($atts['class']) .'">'. esc_html($atts['text']) .'</a>';
}

public function user_menu_shortcode($atts, $content = null) {
    // Only enqueue + render when actually used
    wp_enqueue_style('sta-user-menu-css', STA_PORTAL_URL . 'assets/css/sta-user-menu.css', [], '1.0.0');
    wp_enqueue_script('sta-user-menu-js', STA_PORTAL_URL . 'assets/js/sta-user-menu.js', [], '1.0.0', true);

    $defaults = array(
        'align'      => 'right',    // left|right
        'class'      => '',         // extra class on wrapper
        'show_name'  => 'true',     // true|false
        'show_arrow' => 'true',     // true|false
        /**
         * Items format: "Label|URL|icon;Label|URL|icon;..."
         * Use URL = logout  → will auto-generate a secure logout URL.
         * Use icon slugs: edit, order, card, bell, logout, dashboard, profile, article, book, user
         * Use "divider" as a label to insert a separator: "divider|-|-"
         */
        'items'      => '',         // if empty, we’ll use sensible defaults (see below)
    );
    $atts = shortcode_atts($defaults, $atts, 'portal_user_menu');

    // Defaults
    $align      = in_array($atts['align'], ['left','right'], true) ? $atts['align'] : 'right';
    $extra_cls  = sanitize_html_class($atts['class']);
    $show_name  = filter_var($atts['show_name'], FILTER_VALIDATE_BOOLEAN);
    $show_arrow = filter_var($atts['show_arrow'], FILTER_VALIDATE_BOOLEAN);

    $is_logged_in = is_user_logged_in();
    $current_user = wp_get_current_user();
    $user_id      = $is_logged_in ? $current_user->ID : 0;

    // Avatar
    $avatar_id = $is_logged_in ? intval(get_user_meta($user_id, 'sta_avatar_id', true)) : 0;
    $avatar    = $avatar_id ? wp_get_attachment_image_url($avatar_id, 'thumbnail') : ($is_logged_in ? get_avatar_url($user_id) : '');

    // Name
    $display_name = $is_logged_in ? ($current_user->display_name ?: $current_user->user_login) : '';

    // Build items
    $items = [];
    if ($is_logged_in) {
        // Provided by shortcode?
        if (!empty($atts['items'])) {
            $items = $this->parse_menu_items($atts['items']);
        } else {
            // Sensible defaults matching your design
            $items = [
                ['Submit an Article/ Blog', site_url('/submit-article/'), 'edit'],
                ['Order History',            site_url('/order-history/'), 'order'],
                ['Payment Info',             site_url('/payment-info/'),  'card'],
                ['Alert',                    site_url('/alerts/'),        'bell'],
                ['divider',                  '-',                         '-'],
                ['Logout',                   'logout',                    'logout'],
            ];
        }

        // Allow programmatic override via filter
        $items = apply_filters('sta_portal_user_menu_items', $items, $current_user);
    } else {
        // Logged out view (you can change or hide)
        $items = [
            ['Login',    site_url('/login/'),  'user'],
            ['Sign Up',  site_url('/signup/'), 'profile'],
        ];
    }

    // Resolve URLs & icons
    $resolved = [];
    foreach ($items as $row) {
        $label = isset($row[0]) ? trim($row[0]) : '';
        $url   = isset($row[1]) ? trim($row[1]) : '#';
        $icon  = isset($row[2]) ? trim($row[2]) : '';

        if (strtolower($label) === 'divider') {
            $resolved[] = ['divider', '#', ''];
            continue;
        }

        // Special: logout → secure nonce URL to /login with success message
        if ($url === 'logout') {
            $redirect = add_query_arg('sta_success', urlencode("You’ve been logged out."), site_url('/login/'));
            $url = wp_logout_url($redirect);
        }

        $resolved[] = [
            $label,
            esc_url($url),
            $this->user_menu_icon($icon) // inline SVG
        ];
    }

    // Wrapper classes
    $wrap_classes = 'sta-user-menu sta-align-' . $align . ($extra_cls ? ' ' . $extra_cls : '');
    $aria_name = esc_attr($display_name);

    ob_start();
    ?>
    <div class="<?php echo esc_attr($wrap_classes); ?>" data-sta-user-menu>
        <button class="sum-trigger" type="button" aria-haspopup="true" aria-expanded="false">
            <?php if ($is_logged_in): ?>
                <span class="sum-avatar"><?php if ($avatar) { ?><img src="<?php echo esc_url($avatar); ?>" alt=""><?php } ?></span>
                <?php if ($show_name): ?>
                    <span class="sum-name" aria-label="<?php echo $aria_name; ?>"><?php echo esc_html($display_name); ?></span>
                <?php endif; ?>
            <?php else: ?>
                <span class="sum-name">Menu</span>
            <?php endif; ?>
            <?php if ($show_arrow): ?><span class="sum-caret" aria-hidden="true">▾</span><?php endif; ?>
        </button>

        <div class="sum-dropdown" role="menu">
            <?php foreach ($resolved as $r): ?>
                <?php if ($r[0] === 'divider'): ?>
                    <div class="sum-divider" role="separator"></div>
                    <?php continue; ?>
                <?php endif; ?>
                <a class="sum-item" role="menuitem" href="<?php echo $r[1]; ?>">
                    <span class="sum-icn"><?php echo $r[2]; ?></span>
                    <span class="sum-label"><?php echo esc_html($r[0]); ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

/** Parse "Label|URL|icon;..." into array */
private function parse_menu_items($str) {
    $out = [];
    $parts = array_filter(array_map('trim', explode(';', $str)));
    foreach ($parts as $p) {
        $cols = array_map('trim', explode('|', $p));
        $out[] = [
            $cols[0] ?? '',
            $cols[1] ?? '#',
            $cols[2] ?? ''
        ];
    }
    return $out;
}

/** Minimal inline SVG icon set */
private function user_menu_icon($slug) {
    $svg = '';
    switch ($slug) {
        case 'edit':
        case 'article':
            $svg = '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 113 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>'; break;
        case 'order':
            $svg = '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M3 7h18"/><path d="M6 3h12l1 4H5l1-4z"/><rect x="3" y="7" width="18" height="14" rx="2"/><path d="M8 12h8M8 16h5"/></svg>'; break;
        case 'card':
            $svg = '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.75"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M2 9h20"/></svg>'; break;
        case 'bell':
            $svg = '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M15 17h5l-1.4-1.4A2 2 0 0118 14V10a6 6 0 10-12 0v4a2 2 0 01-.6 1.4L4 17h5"/><path d="M9 17a3 3 0 006 0"/></svg>'; break;
        case 'logout':
            $svg = '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/></svg>'; break;
        case 'dashboard':
            $svg = '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M3 13h8V3H3v10zM13 21h8v-8h-8v8zM3 21h8v-6H3v6zM13 3v6h8V3h-8z"/></svg>'; break;
        case 'profile':
        case 'user':
            $svg = '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.75"><circle cx="12" cy="8" r="4"/><path d="M4 20a8 8 0 0116 0"/></svg>'; break;
        default:
            $svg = '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M4 7h16M4 12h16M4 17h16"/></svg>';
    }
    return $svg;
}



}
