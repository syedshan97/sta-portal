<?php
// templates/lost-password-form.php

// Allow simple links in notices
$allowed = array(
  'a' => array(
    'href' => array(),
    'target' => array(),
    'rel' => array(),
  ),
);

$sta_error   = isset($_GET['sta_error'])   ? wp_kses( urldecode((string) $_GET['sta_error']),   $allowed ) : '';
$sta_success = isset($_GET['sta_success']) ? wp_kses( urldecode((string) $_GET['sta_success']), $allowed ) : '';
?>
<div class="sta-portal-login-form">
  <h2>Forgot Your Password?</h2>
  <div class="sta-portal-desc">Enter your email address to reset your password.</div>

  <?php if ( $sta_error ): ?>
    <div class="sta-portal-error" role="alert" aria-live="polite"><?php echo $sta_error; ?></div>
  <?php endif; ?>

  <?php if ( $sta_success ): ?>
    <div class="sta-portal-error sta-portal-success" role="status" aria-live="polite"><?php echo $sta_success; ?></div>
  <?php endif; ?>

  <form id="sta-lostpass-form" method="post" autocomplete="off">
    <?php wp_nonce_field('sta_portal_lostpass', 'sta_portal_lostpass_nonce'); ?>

    <label for="sta-lostpass-email">Email</label>
    <input
      id="sta-lostpass-email"
      type="email"
      name="sta_lostpass_email"
      required
      autocomplete="email"
      inputmode="email"
      placeholder="name@example.com"
    />

    <button type="submit">Send Reset Link</button>
  </form>

  <div class="sta-bottom">
    <span>Back to <a href="<?php echo esc_url( site_url('/login/') ); ?>" class="sta-link"><b>LOGIN</b></a></span>
  </div>
</div>