<?php
// templates/dashboard/widgets/welcome.php
if ( ! defined('ABSPATH') ) exit;
$dn   = esc_html($data['display_name']);
$ms   = esc_html($data['member_since']);
$pid  = esc_html($data['portal_id']);
$mail = esc_html($data['email']);
$jt   = esc_html($data['job_title']);
$org  = esc_html($data['organization']);
$ava  = esc_url($data['avatar_url']);
$edit = esc_url($data['edit_profile_url']);
?>
<section class="sta-welcome-card" aria-labelledby="sta-welcome-heading">
  <div class="swc-inner">
    <div class="swc-left">
      <div class="swc-avatar">
        <?php if ($ava): ?>
          <img src="<?php echo $ava; ?>" alt="<?php echo $dn ? $dn . '\'s avatar' : 'User avatar'; ?>">
        <?php else: ?>
          <div class="swc-avatar-fallback" aria-hidden="true"></div>
        <?php endif; ?>
      </div>
    </div>

    <div class="swc-right">
      <p class="swc-meta">Member Since: <strong><?php echo $ms ?: '—'; ?></strong></p>

      <h2 id="sta-welcome-heading" class="swc-title">Welcome back, <?php echo $dn ?: 'Member'; ?>!</h2>

      <p class="swc-row">
        <span>Member ID: <strong><?php echo $pid; ?></strong></span>
        <span class="swc-sep">|</span>
        <span>Email: <strong><?php echo $mail; ?></strong></span>
      </p>

      <p class="swc-row">
        <span>Title: <strong><?php echo $jt ?: 'Add your Job Title'; ?></strong></span>
        <span class="swc-sep">|</span>
        <span>Company Name: <strong><?php echo $org ?: 'Add your Organization'; ?></strong></span>
      </p>

      <p class="swc-actions">
        <a class="swc-edit" href="<?php echo $edit; ?>" aria-label="Edit your profile">Edit Profile</a>
      </p>
    </div>
  </div>

  <!-- Decorative gradient / image zone matches design -->
  <div class="swc-art" aria-hidden="true"></div>
</section>
