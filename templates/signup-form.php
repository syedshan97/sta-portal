<?php
// templates/signup-form.php

// Read any error from query (legacy)
$error = isset($_GET['sta_error']) ? urldecode($_GET['sta_error']) : '';

// Enqueue signup JS only on this page
if ( is_page('signup') ) {
    wp_enqueue_script('sta-portal-js', STA_PORTAL_URL . 'assets/js/sta-portal.js', [], '1.0.0', true);
}

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

    <?php if ( get_option('sta_portal_google_enable') ): ?>
      <a href="<?php echo esc_url( site_url('/google-login/') ); ?>" class="sta-social-btn" style="margin-bottom:18px;">
        <img src="https://portal.systemsthinkingalliance.org/wp-content/uploads/2025/08/google-icon.png" alt="Google" width="20" height="20" style="vertical-align:middle;">
        Sign up with Google
      </a>
    <?php endif; ?>

    <?php if ( get_option('sta_portal_ms_enable') ): ?>
      <a href="<?php echo esc_url( site_url('/microsoft-login/') ); ?>" class="sta-social-btn" style="margin-bottom:18px;">
        <img src="https://portal.systemsthinkingalliance.org/wp-content/uploads/2025/08/O365-icon.png" alt="Microsoft" width="20" height="20" style="vertical-align:middle;">
        Sign up with Microsoft
      </a>
    <?php endif; ?>

    <div style="text-align:center;font-size:0.98rem;color:#b2b2b2;margin-bottom:10px;">Or use Email</div>

    <form id="sta-signup-form" method="post" action="" autocomplete="off">
        <?php wp_nonce_field('sta_portal_signup', 'sta_portal_signup_nonce'); ?>

        <!-- First Name -->
        <div class="sta-field" id="field-first">
          <label for="sta-signup-first">First name</label>
          <input type="text" id="sta-signup-first" name="sta_signup_first" autocomplete="given-name" required />
          <small class="sta-field-msg" id="msg-first"></small>
        </div>

        <!-- Last Name -->
        <div class="sta-field" id="field-last">
          <label for="sta-signup-last">Last name</label>
          <input type="text" id="sta-signup-last" name="sta_signup_last" autocomplete="family-name" required />
          <small class="sta-field-msg" id="msg-last"></small>
        </div>

        <!-- Email -->
        <label for="sta-signup-email">Email</label>
        <div class="sta-field" id="field-email">
          <input id="sta-signup-email" type="email" name="sta_signup_email" inputmode="email" autocomplete="email" required />
          <small class="sta-field-msg" id="msg-email"></small>
        </div>

                <!-- Password -->
        <label for="sta-signup-password">Password</label>
<div class="sta-field" id="field-password">
  <input id="sta-signup-password" type="password" name="sta_signup_password" autocomplete="new-password" required />

  <!-- Password Requirements Checklist -->
 <div class="sta-pw-requirements" id="msg-password" aria-live="polite">
  <div class="sta-pw-title">
    <span class="sta-ico-key" aria-hidden="true">🔑</span>
    Password Requirements
  </div>
  <ul class="sta-pw-list">
    <li data-rule="len">8 characters minimum</li>
    <li data-rule="upper">one uppercase letter</li>
    <li data-rule="lower">one lowercase letter</li>
    <li data-rule="num">one number</li>
    <li data-rule="sym">one special character (no &lt;&gt;)</li>
  </ul>
</div>
</div>

<!-- Consent note (before the button) -->

<small class="sta-consent-note">By clicking the download button below, you consent to allow Systems Thinking Alliance to store and process your information and send you communications, which you can unsubscribe from at any time.​
</small>
<small class="sta-consent-note">
  We are committed to protecting and respecting your privacy, please review our
  <a href="https://systemsthinkingalliance.org/privacy-policy/" target="_blank" rel="noopener">Privacy Policy</a>.
</small>
        

        <button type="submit">Sign Up</button>
    </form>

    <div class="sta-bottom" style="margin-top:14px;">
        <span>Already have an account? <a href="<?php echo esc_url( site_url('/login/') ); ?>" class="sta-link"><b>LOG IN NOW</b></a></span>
    </div>
</div>
