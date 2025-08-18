<?php
// templates/dashboard/dashboard.php
if ( ! defined('ABSPATH') ) exit;
/** @var array $data passed from class-dashboard.php */
?>
<div class="sta-dash">
  <?php
    // Permanent header widget
    $welcome_tpl = STA_PORTAL_PATH . 'templates/dashboard/widgets/welcome.php';
    if ( file_exists($welcome_tpl) ) {
        include $welcome_tpl;
    }
  ?>

  <!-- Future widgets area
  <div class="sta-dash-widgets">
      <?php // echo 'Other widgets will render here later…'; ?>
  </div>
  -->
</div>
