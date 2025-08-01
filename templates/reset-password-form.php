<?php
$error = isset($_GET['sta_error']) ? urldecode($_GET['sta_error']) : '';
?>
<div class="sta-portal-login-form">
    <h2>Set New Password</h2>
    <div class="sta-portal-desc">Enter your new password below.</div>
    <?php if ($error): ?>
        <div class="sta-portal-error"><?php echo esc_html($error); ?></div>
    <?php endif; ?>
    <form method="post" autocomplete="off">
        <?php wp_nonce_field('sta_portal_resetpass', 'sta_portal_resetpass_nonce'); ?>
        <input type="hidden" name="reset_key" value="<?php echo esc_attr($_GET['key']); ?>">
        <input type="hidden" name="reset_login" value="<?php echo esc_attr($_GET['login']); ?>">
        <label for="sta-new-pass1">New Password</label>
        <input id="sta-new-pass1" type="password" name="sta_new_pass1" required />
        <label for="sta-new-pass2">Repeat New Password</label>
        <input id="sta-new-pass2" type="password" name="sta_new_pass2" required />
        <button type="submit">Reset Password</button>
    </form>
    <div class="sta-bottom">
        <span>Back to <a href="<?php echo site_url('/login/'); ?>" class="sta-link"><b>LOGIN</b></a></span>
    </div>
</div>
