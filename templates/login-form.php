<?php
$error = isset($_GET['sta_error']) ? urldecode($_GET['sta_error']) : '';
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

<div class="sta-portal-login-form">
    <!--<h2>WELCOME BACK EXCLUSIVE MEMBER</h2>-->
    <div style="font-weight:700;text-align:center;margin-bottom:10px;font-size:1.04rem;color:#808080;">LOG IN TO CONTINUE</div>
    

<?php if (get_option('sta_portal_google_enable')): ?>
    <a href="<?php echo site_url('/google-login/'); ?>" class="sta-social-btn" style="margin-bottom:18px;">
        <img src="https://portal.systemsthinkingalliance.org/wp-content/uploads/2025/08/google-icon.png" alt="Google" width="35" height="35" style="vertical-align:middle;">
        Sign in with Google
    </a>
<?php endif; ?>
<?php if (get_option('sta_portal_ms_enable')): ?>
  <a href="<?php echo site_url('/microsoft-login/'); ?>" class="sta-social-btn" style="margin-bottom:18px;">
    <img src="https://portal.systemsthinkingalliance.org/wp-content/uploads/2025/08/O365-icon.png" alt="Microsoft" width="35" height="35" style="vertical-align:middle;">
    Sign in with Microsoft
  </a>
<?php endif; ?>



    <!--<div style="text-align:center;font-size:0.98rem;color:#b2b2b2;margin-bottom:10px;">Or use Email</div>-->
    <form method="post" autocomplete="off">
        <?php wp_nonce_field('sta_portal_login', 'sta_portal_login_nonce'); ?>
        <label for="sta-login-email">Email</label>
        <input id="sta-login-email" type="email" name="sta_login_email" required />
        <label for="sta-login-password">Password</label>
        <!--<input id="sta-login-password" type="password" name="sta_login_password" required />-->
        <div class="sta-passwrap">
        <input id="sta-login-password" class="sta-pass" type="password" name="sta_login_password" required>
        <span class="sta-toggle-pass" role="button" aria-pressed="false" aria-controls="sta-login-password" tabindex="0">Show</span>
        </div>
        <button type="submit">Login</button>
    </form>
    <br>
    <div class="sta-bottom" style="margin-top:14px;">
        <span>Having issues with your <a href="<?php echo site_url('/forgot-password/'); ?>" class="sta-link">Password?</a></span>
    </div>
    <!--<div class="sta-bottom" style="margin-top:6px;">-->
    <!--    <span>Not a member yet? <a href="<?php echo site_url('/signup/'); ?>" class="sta-link"><b>JOIN NOW</b></a></span>-->
    <!--</div>-->
</div>

<?php
// Enqueue STA portal assets on the login template (for Show/Hide password, etc.)
if ( function_exists('wp_enqueue_script') ) {

    if ( ! wp_script_is('sta-portal-js', 'enqueued') ) {
        wp_enqueue_script('sta-portal-js', STA_PORTAL_URL . 'assets/js/sta-portal.js', [], '1.0.0', true);
    }
}
?>