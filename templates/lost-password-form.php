<?php
$error   = isset($_GET['sta_error']) ? urldecode($_GET['sta_error']) : '';
$success = isset($_GET['sta_success']) ? urldecode($_GET['sta_success']) : '';
?>
<div class="sta-portal-login-form">
    <h2>Forgot Your Password?</h2>
    <div class="sta-portal-desc">Enter your email address to reset your password.</div>
    <?php if ($error): ?>
        <div class="sta-portal-error"><?php echo esc_html($error); ?></div>
    <?php elseif ($success): ?>
        <div class="sta-portal-error sta-portal-success"><?php echo esc_html($success); ?></div>
    <?php endif; ?>
    <form method="post" autocomplete="off">
        <?php wp_nonce_field('sta_portal_lostpass', 'sta_portal_lostpass_nonce'); ?>
        <label for="sta-lostpass-email">Email</label>
        <input id="sta-lostpass-email" type="email" name="sta_lostpass_email" required />
        <button type="submit">Send Reset Link</button>
    </form>
    <div class="sta-bottom">
        <span>Back to <a href="<?php echo site_url('/login/'); ?>" class="sta-link"><b>LOGIN</b></a></span>
    </div>
</div>
