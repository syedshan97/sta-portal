<?php
// templates/reset-password.php (or your reset template)
$error = isset($_GET['sta_error']) ? urldecode($_GET['sta_error']) : '';

// somewhere you enqueue assets
if ( is_page('reset-password') ) {
    wp_enqueue_script('sta-portal-js', STA_PORTAL_URL . 'assets/js/sta-portal.js', [], '1.0.0', true);
}

?>
<div class="sta-portal-login-form sta-portal-reset-form">
  <h2>Set New Password</h2>
  <div class="sta-portal-desc">Enter your new password below.</div>

  <?php if ($error): ?>
    <div class="sta-portal-error"><?php echo esc_html($error); ?></div>
  <?php endif; ?>

  <form id="sta-reset-form" method="post" autocomplete="off">
    <?php wp_nonce_field('sta_portal_resetpass', 'sta_portal_resetpass_nonce'); ?>
    <input type="hidden" name="reset_key"   value="<?php echo esc_attr($_GET['key']   ?? ''); ?>">
    <input type="hidden" name="reset_login" value="<?php echo esc_attr($_GET['login'] ?? ''); ?>">

    <!-- New password -->
    <label for="sta-reset-pass1">New Password</label>
    <div class="sta-field" id="field-reset-pass1">
      <input id="sta-reset-pass1" type="password" name="sta_reset_pass1" autocomplete="new-password" required />

      <!-- Password Requirements Checklist (live) -->
      <div class="sta-pw-requirements" id="msg-password-reset" aria-live="polite">
        <div class="sta-pw-title">ðŸ”‘ Password Requirements</div>
        <ul class="sta-pw-list">
          <li data-rule="len">8 characters minimum</li>
          <li data-rule="upper">one uppercase letter</li>
          <li data-rule="lower">one lowercase letter</li>
          <li data-rule="num">one number</li>
          <li data-rule="sym">one special character (no &lt;&gt;)</li>
        </ul>
      </div>
    </div>

    <!-- Confirm password -->
    <label for="sta-reset-pass2">Confirm New Password</label>
    <div class="sta-field" id="field-reset-pass2">
      <input id="sta-reset-pass2" type="password" name="sta_reset_pass2" autocomplete="new-password" required />
      <small class="sta-field-msg" id="msg-reset-confirm"></small>
    </div>

    <button type="submit">Reset Password</button>
  </form>

  <div class="sta-bottom">
    <span>Back to <a href="<?php echo esc_url( site_url('/login/') ); ?>" class="sta-link"><b>LOGIN</b></a></span>
  </div>
</div>
