<?php
$error = isset($_GET['sta_error']) ? urldecode($_GET['sta_error']) : '';
?>
<div class="sta-portal-login-form">
    <h2>WELCOME BACK EXCLUSIVE MEMBER</h2>
    <div style="text-align:center;margin-bottom:10px;font-size:1.04rem;color:#808080;">LOG IN TO CONTINUE</div>
    <?php if ($error): ?>
        <div class="sta-portal-error"><?php echo esc_html($error); ?></div>
    <?php endif; ?>
    <button type="button" class="sta-social-btn" style="margin-bottom:18px;">
        <img src="https://www.svgrepo.com/show/475656/google-color.svg" alt="Google" width="20" height="20" style="vertical-align:middle;"> Sign up with Google
    </button>
    <div style="text-align:center;font-size:0.98rem;color:#b2b2b2;margin-bottom:10px;">Or use Email</div>
    <form method="post" autocomplete="off">
        <?php wp_nonce_field('sta_portal_login', 'sta_portal_login_nonce'); ?>
        <label for="sta-login-email">Email</label>
        <input id="sta-login-email" type="email" name="sta_login_email" required />
        <label for="sta-login-password">Password</label>
        <input id="sta-login-password" type="password" name="sta_login_password" required />
        <button type="submit">Login</button>
    </form>
    <div class="sta-bottom" style="margin-top:14px;">
        <span>Having issues with your <a href="<?php echo wp_lostpassword_url(); ?>" class="sta-link">Password?</a></span>
    </div>
    <div class="sta-bottom" style="margin-top:6px;">
        <span>Not a member yet? <a href="<?php echo site_url('/signup/'); ?>" class="sta-link"><b>JOIN NOW</b></a></span>
    </div>
</div>
