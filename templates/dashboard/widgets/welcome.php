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

// Fixed right-side image URL (provided)
$sideimg = 'https://portal.systemsthinkingalliance.org/wp-content/uploads/2025/08/dashboard-right-image.png';
?>
<section class="sta-welcome-card" aria-labelledby="sta-welcome-heading">
  <div class="swc-inner">
    <!-- Left + center content (has padding) -->
    <div class="swc-main">
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
      </div>
    </div>

    <!-- Right-side image (no padding, touches borders) -->
    <div class="swc-side" role="img" aria-label="Dashboard illustration"></div>
  </div>
</section>
