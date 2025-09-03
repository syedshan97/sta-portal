<?php
// templates/manage-profile.php
$user_id   = get_current_user_id();
$user      = wp_get_current_user();

$avatar_id = intval(get_user_meta($user_id, 'sta_avatar_id', true));
$avatar    = $avatar_id ? wp_get_attachment_image_url($avatar_id, 'thumbnail') : get_avatar_url($user_id);
$member_id = get_user_meta($user_id, 'portal_user_id', true);

// NEW: first/last name
$fn = get_user_meta($user_id, 'first_name', true);
$ln = get_user_meta($user_id, 'last_name',  true);

// provider label
$provider  = get_user_meta($user_id, 'sta_auth_provider', true) ?: 'local';
$provider_label = ($provider === 'google') ? 'Google' : (($provider === 'microsoft') ? 'Microsoft 365' : 'Email');

$job_title = get_user_meta($user_id, 'sta_job_title', true);
$org       = get_user_meta($user_id, 'sta_org', true);
$phone     = get_user_meta($user_id, 'sta_phone', true);

$addr_street = get_user_meta($user_id, 'sta_addr_street', true);
$addr_city   = get_user_meta($user_id, 'sta_addr_city', true);
$addr_state  = get_user_meta($user_id, 'sta_addr_state', true);
$addr_country= get_user_meta($user_id, 'sta_addr_country', true);
$addr_postal = get_user_meta($user_id, 'sta_addr_postal', true);

$error   = isset($_GET['sta_error']) ? urldecode($_GET['sta_error']) : '';
$success = isset($_GET['sta_success']) ? urldecode($_GET['sta_success']) : '';

$lock_identity = true;


?>

<?php
// Ensure JS + AJAX data are available on this page
wp_enqueue_script('sta-portal-js', STA_PORTAL_URL . 'assets/js/sta-portal.js', [], '1.0.0', true);
wp_localize_script('sta-portal-js', 'STA_PORTAL_AJAX', [
  'url'   => admin_url('admin-ajax.php'),
  'nonce' => wp_create_nonce('sta_remove_avatar'),
]);
?>

<div class="sta-profile sta-profile--wide">
  <?php if ($error): ?><div class="sta-alert sta-alert--error"><?php echo esc_html($error); ?></div><?php endif; ?>
  <?php if ($success): ?><div class="sta-alert sta-alert--success"><?php echo esc_html($success); ?></div><?php endif; ?>

  <div class="sta-profile__top">
    <!--<div class="sta-profile__avatar">-->
    <!--  <div class="sta-avatar-wrap">-->
    <!--    <img src="<?php echo esc_url($avatar); ?>" alt="Avatar" id="sta-avatar-preview">-->
    <!--  </div>-->
    <!--  <button type="button" class="sta-btn sta-btn-light" id="sta-change-avatar">Upload</button>-->
    <!--</div>-->
    <div class="sta-profile__avatar">
  <div
    class="sta-avatar-card"
    id="sta-avatar-card"
    data-has="<?php echo $avatar_id ? '1' : '0'; ?>"
    data-placeholder="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='200' height='200' viewBox='0 0 200 200'%3E%3Cdefs%3E%3CradialGradient id='g' cx='50%25' cy='45%25' r='70%25'%3E%3Cstop stop-color='%23eceff4' offset='0'/%3E%3Cstop stop-color='%23e5e7eb' offset='1'/%3E%3C/radialGradient%3E%3C/defs%3E%3Ccircle cx='100' cy='100' r='98' fill='url(%23g)'/%3E%3Ccircle cx='100' cy='80' r='28' fill='%23bfc7d3'/%3E%3Cpath d='M40 152c8-28 32-40 60-40s52 12 60 40' fill='%23cdd5df'/%3E%3C/svg%3E"
  >
    <img
      src="<?php echo esc_url($avatar); ?>"
      id="sta-avatar-preview"
      class="sta-avatar-img"
      alt="Profile photo"
      draggable="false"
    />
    <span class="sta-avatar-hint" aria-hidden="true">Change / Remove</span>
    <span class="sta-avatar-chip" id="sta-avatar-chip">Upload</span>
  </div>

  <!-- keep your hidden trigger so existing media flow works -->
  <button type="button" class="sr-only" id="sta-change-avatar">Upload</button>
</div>

<!-- Avatar action modal -->
<div class="sta-modal" id="sta-avatar-modal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="sam-title">
  <div class="sta-modal__backdrop" data-close></div>
  <div class="sta-modal__panel" role="document">
    <button type="button" class="sam-close" data-close aria-label="Close">&times;</button>
    <h3 id="sam-title">Profile Photo</h3>
    <p class="sam-desc">Choose an action for your profile picture.</p>
    <div class="sam-actions">
      <button type="button" class="sam-btn sam-primary" id="sam-change">Change profile picture</button>
      <button type="button" class="sam-btn sam-danger"  id="sam-remove">Remove profile picture</button>
    </div>
  </div>
</div>

    <div class="sta-profile__headtext">
      <div class="sta-profile__name"><?php echo esc_html($user->display_name ?: $user->user_login); ?></div>
      <div class="sta-profile__sub">
        <span>Member ID: <b><?php echo esc_html($member_id ?: '-'); ?></b></span>
        <span class="sep">•</span>
        <span>Signed up via: <b><?php echo esc_html($provider_label); ?></b></span>
      </div>
    </div>
  </div>

  <form class="sta-profile__form" method="post" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" autocomplete="off">
    <input type="hidden" name="action" value="sta_profile_save">
    <?php wp_nonce_field('sta_profile_save','sta_profile_nonce'); ?>

    <h3 class="sta-section-title">Personal Details</h3>

    <div class="sta-grid">
      <!-- First name (NEW with consistent wrap + pencil) -->
      <div class="sta-field half">
        <label for="sta-first-name">First name</label>
        <div class="sta-input-wrap">
<input style="cursor: not-allowed;" type="text" id="sta-first-name" name="sta_first_name" value="<?php echo esc_attr($fn); ?>" required pattern="^[A-Za-z]+(?:[ '\-][A-Za-z]+)*$" title="Use English letters and spaces (hyphen/apostrophe allowed)" <?php echo $lock_identity ? 'readonly aria-readonly="true" class="is-locked"' : ''; ?> <span class="sta-pencil"></span>
        </div>
      </div>

      <!-- Last name (NEW with consistent wrap + pencil) -->
      <div class="sta-field half">
        <label for="sta-last-name">Last name</label>
        <div class="sta-input-wrap">
         <!--<input type="text" id="sta-last-name" name="sta_last_name" value="<?php echo esc_attr($ln); ?>" required pattern="^[A-Za-z]+(?:[ '\-][A-Za-z]+)*$" title="Use English letters and spaces (hyphen/apostrophe allowed)">-->
         <input style="cursor: not-allowed;" type="text" id="sta-last-name" name="sta_last_name" value="<?php echo esc_attr($ln); ?>" required pattern="^[A-Za-z]+(?:[ '\-][A-Za-z]+)*$" title="Use English letters and spaces (hyphen/apostrophe allowed)" <?php echo $lock_identity ? 'readonly aria-readonly="true" class="is-locked"' : ''; ?> >
          <span class="sta-pencil"></span>
        </div>
      </div>

      <div class="sta-field">
        <label>Job Title</label>
        <div class="sta-input-wrap">
          <input type="text" name="sta_job_title" value="<?php echo esc_attr($job_title); ?>">
          <span class="sta-pencil"><i class="fa fa-pencil" aria-hidden="true"></i></span>
        </div>
      </div>

      <div class="sta-field">
        <label>Organization</label>
        <div class="sta-input-wrap">
          <input type="text" name="sta_org" value="<?php echo esc_attr($org); ?>">
          <span class="sta-pencil"><i class="fa fa-pencil" aria-hidden="true"></i></span>
        </div>
      </div>

      <div class="sta-field">
        <label>Email</label>
        <div class="sta-input-wrap">
          <!--<input type="email" name="sta_email" value="<?php echo esc_attr($user->user_email); ?>" required>-->
          <input style="cursor: not-allowed;" type="email" name="sta_email" value="<?php echo esc_attr($user->user_email); ?>" required <?php echo $lock_identity ? 'readonly aria-readonly="true" class="is-locked"' : ''; ?> >
          <span class="sta-pencil"></span>
        </div>
      </div>

      <div class="sta-field">
        <label>Phone Number</label>
        <div class="sta-input-wrap">
          <input type="tel"
                 name="sta_phone"
                 value="<?php echo esc_attr($phone); ?>"
                 placeholder="+14155551212"
                 pattern="^\+[1-9]\d{7,14}$"
                 title="Include country code, e.g. +14155551212">
          <span class="sta-pencil"><i class="fa fa-pencil" aria-hidden="true"></i></span>
        </div>
      </div>
    </div>
<hr style="border: 1px solid #d7d7d7;margin: 50px 0 35px;">

    <h3 class="sta-section-title">Address</h3>
    <div class="sta-grid">
      <div class="sta-field full">
        <label>Street Address</label>
        <div class="sta-input-wrap">
          <input type="text" name="sta_addr_street" value="<?php echo esc_attr($addr_street); ?>">
          <span class="sta-pencil"><i class="fa fa-pencil" aria-hidden="true"></i></span>
        </div>
      </div>

      <div class="sta-field">
        <label>City</label>
        <div class="sta-input-wrap">
          <input type="text" name="sta_addr_city" value="<?php echo esc_attr($addr_city); ?>">
          <span class="sta-pencil"><i class="fa fa-pencil" aria-hidden="true"></i></span>
        </div>
      </div>

      <div class="sta-field">
        <label>State/Province</label>
        <div class="sta-input-wrap">
          <input type="text" name="sta_addr_state" value="<?php echo esc_attr($addr_state); ?>">
          <span class="sta-pencil"><i class="fa fa-pencil" aria-hidden="true"></i></span>
        </div>
      </div>

      <div class="sta-field">
        <label>Country</label>
        <div class="sta-input-wrap">
          <input type="text" name="sta_addr_country" value="<?php echo esc_attr($addr_country); ?>">
          <span class="sta-pencil"><i class="fa fa-pencil" aria-hidden="true"></i></span>
        </div>
      </div>

      <div class="sta-field">
        <label>Postal Code</label>
        <div class="sta-input-wrap">
          <input type="text" name="sta_addr_postal" value="<?php echo esc_attr($addr_postal); ?>">
          <span class="sta-pencil"><i class="fa fa-pencil" aria-hidden="true"></i></span>
        </div>
      </div>
    </div>

    <!--<div class="sta-password-row">-->
    <!--  <a class="sta-link" href="<?php echo esc_url( site_url('/forgot-password/') ); ?>">Set / Change Password</a>-->
    <!--</div>-->
    
   


    <button type="submit" class="sta-primary-btn">Save the Changes</button>
  </form>
  <hr style="border: 1px solid #d7d7d7;margin: 30px 0 35px;">

   <!-- ===== Password Section (separate form) ===== -->
<h3 class="sta-section-title">Password</h3>

<form id="sta-change-pass-form" class="sta-profile__form" method="post" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" autocomplete="off">
  <input type="hidden" name="action" value="sta_change_password">
  <?php wp_nonce_field('sta_change_password','sta_change_pass_nonce'); ?>

  <div class="sta-grid">
    <div class="sta-field full" id="field-pass-current">
      <label for="sta-pass-current">Current password</label>
      <div class="sta-input-wrap">
        <input type="password" id="sta-pass-current" name="sta_pass_current" required>
      </div>
    </div>

    <div class="sta-field full" id="field-pass-new1">
      <label for="sta-pass-new1">New password</label>
      <div class="sta-input-wrap">
        <input type="password" id="sta-pass-new1" name="sta_pass_new1" required>
      </div>

      <!-- Password requirements (same style as signup/reset) -->
      <ul class="sta-req-list" id="pwd-req-profile" aria-live="polite">
        <li id="reqp-len">At least 8 characters</li>
        <li id="reqp-upper">Contains an uppercase letter</li>
        <li id="reqp-lower">Contains a lowercase letter</li>
        <li id="reqp-num">Contains a number</li>
        <li id="reqp-sym">Contains a special character (not &lt; or &gt;)</li>
      </ul>
    </div>

    <div class="sta-field full" id="field-pass-new2">
      <label for="sta-pass-new2">Confirm new password</label>
      <div class="sta-input-wrap">
        <input type="password" id="sta-pass-new2" name="sta_pass_new2" required>
      </div>
      <small class="sta-field-msg" id="msg-profile-confirm"></small>
    </div>
  </div>

  <!--<div class="sta-password-row" style="margin-top:8px;">-->
  <!--  <a class="sta-link" href="<?php echo esc_url( site_url('/forgot-password/') ); ?>">Forgot your password?</a>-->
  <!--</div>-->

  <button type="submit" class="sta-primary-btn">Update Password</button>
</form>
<!-- ===== /Password Section ===== -->
<hr style="border: 1px solid #d7d7d7;margin: 30px 0 35px;">

<h3 class="sta-section-title">Password Reset</h3>
<div class="sta-password-row" style="margin-top:8px;">
    <a style="color:#2DA8E0;" class="sta-link" href="<?php echo esc_url( site_url('/forgot-password/') ); ?>">Forgot your password? Click Here to Reset . . .</a>
  </div>

</div>