<?php
$error = isset($_GET['sta_error']) ? urldecode($_GET['sta_error']) : '';
?>
<div class="sta-portal-signup-form">
    <h2>BECOME AN EXCLUSIVE MEMBER</h2>
    <div style="text-align:center;margin-bottom:10px;font-size:1.04rem;color:#808080;">SIGN UP AND JOIN THE PARTNERSHIP</div>
    <?php if ($error): ?>
        <div class="sta-portal-error"><?php echo esc_html($error); ?></div>
    <?php endif; ?>
    <?php if (get_option('sta_portal_google_enable')): ?>
    <a href="<?php echo site_url('/google-login/'); ?>" class="sta-social-btn" style="margin-bottom:18px;">
        <img src="https://www.svgrepo.com/show/475656/google-color.svg" alt="Google" width="20" height="20" style="vertical-align:middle;">
        Sign up with Google
    </a>
    <?php endif; ?>

    <div style="text-align:center;font-size:0.98rem;color:#b2b2b2;margin-bottom:10px;">Or use Email</div>
    <form method="post" autocomplete="off">
        <?php wp_nonce_field('sta_portal_signup', 'sta_portal_signup_nonce'); ?>
        <label for="sta-signup-name">Name</label>
        <input id="sta-signup-name" type="text" name="sta_signup_name" required />
        <label for="sta-signup-email">Email</label>
        <input id="sta-signup-email" type="email" name="sta_signup_email" required />
        <label for="sta-signup-password">Password</label>
        <input id="sta-signup-password" type="password" name="sta_signup_password" required />
        <button type="submit">Sign Up</button>
    </form>
    <div class="sta-bottom" style="margin-top:14px;">
        <span>Already have an account? <a href="<?php echo site_url('/login/'); ?>" class="sta-link"><b>LOG IN NOW</b></a></span>
    </div>
</div>