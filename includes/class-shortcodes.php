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
    // Assets
    wp_enqueue_style('sta-user-menu-css', STA_PORTAL_URL . 'assets/css/sta-user-menu.css', [], '1.1.0');
    wp_enqueue_script('sta-user-menu-js', STA_PORTAL_URL . 'assets/js/sta-user-menu.js', [], '1.0.0', true);
    

    $defaults = array(
        'align'        => 'right',   // left|right
        'class'        => '',
        'show_name'    => 'true',    // true|false
        'show_arrow'   => 'true',    // true|false
        // New (optional): allow separate menus
        'items'        => '',        // fallback for both (Label|URL|fa classes;...)
        'items_mobile' => '',        // overrides on mobile
        'items_desktop'=> '',        // overrides on desktop
    );
    $atts = shortcode_atts($defaults, $atts, 'portal_user_menu');

    $align      = in_array($atts['align'], ['left','right'], true) ? $atts['align'] : 'right';
    $extra_cls  = sanitize_html_class($atts['class']);
    $show_name  = filter_var($atts['show_name'], FILTER_VALIDATE_BOOLEAN);
    $show_arrow = filter_var($atts['show_arrow'], FILTER_VALIDATE_BOOLEAN);

    $is_logged_in = is_user_logged_in();
    $current_user = wp_get_current_user();
    $user_id      = $is_logged_in ? $current_user->ID : 0;
    $is_mobile    = function_exists('wp_is_mobile') ? wp_is_mobile() : false;
    $context      = $is_mobile ? 'mobile' : 'desktop';

    // Avatar
    $avatar_id = $is_logged_in ? intval(get_user_meta($user_id, 'sta_avatar_id', true)) : 0;
    $avatar    = $avatar_id ? wp_get_attachment_image_url($avatar_id, 'thumbnail') : ($is_logged_in ? get_avatar_url($user_id) : '');

    // Name
    $display_name = $is_logged_in ? ($current_user->display_name ?: $current_user->user_login) : '';

    // Build items
    $items = [];
    if ($is_logged_in) {
        // Prefer explicit per-context attributes, then generic 'items'
        $raw = $is_mobile ? ($atts['items_mobile'] ?: $atts['items']) : ($atts['items_desktop'] ?: $atts['items']);

        if (!empty($raw)) {
            $items = $this->parse_menu_items($raw);
        } else {
            if ($is_mobile) {
                // Default MOBILE menu (your list)
                $items = [
                    ['Dashboard',      site_url('/dashboard/'),      'fa-solid fa-gauge'],
                    ['Manage profile', site_url('/manage-profile/'), 'fa-regular fa-user'],
                    ['Exams',          site_url('/exams/'),          'fa-solid fa-clipboard-check'],
                    ['Certifications', site_url('/certifications/'), 'fa-solid fa-certificate'],
                    ['Resources',      site_url('/resources/'),      'fa-solid fa-book'],
                    ['Alerts',         site_url('/alerts/'),         'fa-regular fa-bell'],
                    ['divider',        '-',                          '-'],
                    ['Logout',         'logout',                     'fa-solid fa-right-from-bracket'],
                ];
            } else {
                // Default DESKTOP menu
                $items = [
                    ['Manage profile', site_url('/manage-profile/'), 'fa-regular fa-user'],
                    ['divider',        '-',                          '-'],
                    ['Logout',         'logout',                    'fa-solid fa-right-from-bracket'],
                ];
            }
        }
        // Allow override via filter, provide context ('mobile'|'desktop')
        $items = apply_filters('sta_portal_user_menu_items', $items, $current_user, $context);
    } else {
        // Logged-out view
        $items = [
            ['Login',   site_url('/login/'),  'fa-regular fa-user'],
            ['Sign Up', site_url('/signup/'), 'fa-regular fa-id-card'],
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
            $this->user_menu_icon($icon) // returns <i class="..."></i>
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
            <?php if ($show_arrow): ?>
                <span class="sum-caret" aria-hidden="true"><i class="fa-solid fa-caret-down"></i></span>
            <?php endif; ?>
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


/** Parse "Label|URL|fa classes;..." into array */
private function parse_menu_items($str) {
    $out = [];
    $parts = array_filter(array_map('trim', explode(';', $str)));
    foreach ($parts as $p) {
        $cols = array_map('trim', explode('|', $p));
        $out[] = [
            $cols[0] ?? '',
            $cols[1] ?? '#',
            $cols[2] ?? ''   // Font Awesome class string, e.g. "fa-solid fa-user"
        ];
    }
    return $out;
}

/** Render a Font Awesome <i> from given classes */
private function user_menu_icon($classes) {
    $classes = (string) $classes;
    if ($classes === '') {
        $classes = 'fa-regular fa-circle';
    }
    // Sanitize to letters/numbers/dash/space only
    $classes = preg_replace('/[^a-zA-Z0-9\-\s]/', '', $classes);
    $classes = trim(preg_replace('/\s+/', ' ', $classes));
    return '<i class="'. esc_attr($classes) .'" aria-hidden="true"></i>';
}



}
