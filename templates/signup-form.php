<?php
$error = isset($_GET['sta_error']) ? urldecode($_GET['sta_error']) : '';
if ( is_page('signup') ) {
    wp_enqueue_script('sta-portal-js', STA_PORTAL_URL . 'assets/js/sta-portal.js', [], '1.0.0', true);
}

?>
<?php
// --- Notices from query string ---
$allowed = array( 'a' => array( 'href' => array(), 'target' => array(), 'rel' => array() ) );

$sta_error   = isset($_GET['sta_error'])
    ? wp_kses( urldecode( (string) $_GET['sta_error'] ), $allowed )
    : '';

$sta_success = isset($_GET['sta_success'])
    ? wp_kses( urldecode( (string) $_GET['sta_success'] ), $allowed )
    : '';
?>

<?php if ( $sta_error ): ?>
  <div class="sta-portal-error"><?php echo $sta_error; ?></div>
<?php endif; ?>

<?php if ( $sta_success ): ?>
  <div class="sta-portal-error sta-portal-success"><?php echo $sta_success; ?></div>
<?php endif; ?>

<div class="sta-portal-signup-form">
    <h2>BECOME AN EXCLUSIVE MEMBER</h2>
    <div style="text-align:center;margin-bottom:10px;font-size:1.04rem;color:#808080;">SIGN UP AND JOIN THE PARTNERSHIP</div>
    <?php if ($error): ?>
        <div class="sta-portal-error"><?php echo esc_html($error); ?></div>
    <?php endif; ?>
    
    <?php if (get_option('sta_portal_google_enable')): ?>
    <a href="<?php echo site_url('/google-login/'); ?>" class="sta-social-btn" style="margin-bottom:18px;">
        <img src="https://portal.systemsthinkingalliance.org/wp-content/uploads/2025/08/google-icon.png" alt="Google" width="20" height="20" style="vertical-align:middle;">
        Sign up with Google
    </a>
    <?php endif; ?>
    
<?php if (get_option('sta_portal_ms_enable')): ?>
  <a href="<?php echo site_url('/microsoft-login/'); ?>" class="sta-social-btn" style="margin-bottom:18px;">
    <img src="https://portal.systemsthinkingalliance.org/wp-content/uploads/2025/08/O365-icon.png" alt="Microsoft" width="20" height="20" style="vertical-align:middle;">
    Sign up with Microsoft
  </a>
<?php endif; ?>



    
    <div style="text-align:center;font-size:0.98rem;color:#b2b2b2;margin-bottom:10px;">Or use Email</div>
    <form id="sta-signup-form" method="post" autocomplete="off">
        <?php wp_nonce_field('sta_portal_signup', 'sta_portal_signup_nonce'); ?>
        <label for="sta-signup-name">Name</label>
        <input id="sta-signup-name" type="text" name="sta_signup_name" required />
        <label for="sta-signup-email">Email</label>
        <input id="sta-signup-email" type="email" name="sta_signup_email" required />
        <label for="sta-signup-password">Password</label>
        <input id="sta-signup-password" type="password" name="sta_signup_password" required />
        <small class="sta-field-msg info" id="msg-password">Password must be at least 8 characters and include a letter, a number, and a symbol.</small>

        <button type="submit">Sign Up</button>
    </form>
    <div class="sta-bottom" style="margin-top:14px;">
        <span>Already have an account? <a href="<?php echo site_url('/login/'); ?>" class="sta-link"><b>LOG IN NOW</b></a></span>
    </div>
</div>
