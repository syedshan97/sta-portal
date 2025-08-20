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
?>
<div class="sta-profile sta-profile--wide">
  <?php if ($error): ?><div class="sta-alert sta-alert--error"><?php echo esc_html($error); ?></div><?php endif; ?>
  <?php if ($success): ?><div class="sta-alert sta-alert--success"><?php echo esc_html($success); ?></div><?php endif; ?>

  <div class="sta-profile__top">
    <div class="sta-profile__avatar">
      <div class="sta-avatar-wrap">
        <img src="<?php echo esc_url($avatar); ?>" alt="Avatar" id="sta-avatar-preview">
      </div>
      <button type="button" class="sta-btn sta-btn-light" id="sta-change-avatar">Upload</button>
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
        <input type="text" id="sta-first-name" name="sta_first_name" value="<?php echo esc_attr($fn); ?>" required pattern="^[A-Za-z]+(?:[ '\-][A-Za-z]+)*$" title="Use English letters and spaces (hyphen/apostrophe allowed)">

          <span class="sta-pencil"></span>
        </div>
      </div>

      <!-- Last name (NEW with consistent wrap + pencil) -->
      <div class="sta-field half">
        <label for="sta-last-name">Last name</label>
        <div class="sta-input-wrap">
        <input type="text" id="sta-last-name" name="sta_last_name" value="<?php echo esc_attr($ln); ?>" required pattern="^[A-Za-z]+(?:[ '\-][A-Za-z]+)*$" title="Use English letters and spaces (hyphen/apostrophe allowed)">

          <span class="sta-pencil"></span>
        </div>
      </div>

      <div class="sta-field">
        <label>Job Title</label>
        <div class="sta-input-wrap">
          <input type="text" name="sta_job_title" value="<?php echo esc_attr($job_title); ?>">
          <span class="sta-pencil">✎</span>
        </div>
      </div>

      <div class="sta-field">
        <label>Organization</label>
        <div class="sta-input-wrap">
          <input type="text" name="sta_org" value="<?php echo esc_attr($org); ?>">
          <span class="sta-pencil">✎</span>
        </div>
      </div>

      <div class="sta-field">
        <label>Email</label>
        <div class="sta-input-wrap">
          <input disabled type="email" name="sta_email" value="<?php echo esc_attr($user->user_email); ?>" required>
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
          <span class="sta-pencil">✎</span>
        </div>
      </div>
    </div>

    <h3 class="sta-section-title">Address</h3>
    <div class="sta-grid">
      <div class="sta-field full">
        <label>Street Address</label>
        <div class="sta-input-wrap">
          <input type="text" name="sta_addr_street" value="<?php echo esc_attr($addr_street); ?>">
          <span class="sta-pencil">✎</span>
        </div>
      </div>

      <div class="sta-field">
        <label>City</label>
        <div class="sta-input-wrap">
          <input type="text" name="sta_addr_city" value="<?php echo esc_attr($addr_city); ?>">
          <span class="sta-pencil">✎</span>
        </div>
      </div>

      <div class="sta-field">
        <label>State/Province</label>
        <div class="sta-input-wrap">
          <input type="text" name="sta_addr_state" value="<?php echo esc_attr($addr_state); ?>">
          <span class="sta-pencil">✎</span>
        </div>
      </div>

      <div class="sta-field">
        <label>Country</label>
        <div class="sta-input-wrap">
          <input type="text" name="sta_addr_country" value="<?php echo esc_attr($addr_country); ?>">
          <span class="sta-pencil">✎</span>
        </div>
      </div>

      <div class="sta-field">
        <label>Postal Code</label>
        <div class="sta-input-wrap">
          <input type="text" name="sta_addr_postal" value="<?php echo esc_attr($addr_postal); ?>">
          <span class="sta-pencil">✎</span>
        </div>
      </div>
    </div>

    <div class="sta-password-row">
      <a class="sta-link" href="<?php echo esc_url( site_url('/forgot-password/') ); ?>">Set / Change Password</a>
    </div>

    <button type="submit" class="sta-primary-btn">Save the Changes</button>
  </form>
</div>
