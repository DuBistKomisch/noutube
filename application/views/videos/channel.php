<div class="subscription">
  <img src="<?php echo $thumbnail; ?>" alt="<?php echo $display; ?> thumbnail">
  <div class="subright">
    <p><?php echo anchor('videos/channel/' . $username, $display); ?> &mdash; <?php echo $new; ?> new, <?php echo $later; ?> for later</p>
    <p class="update">Updated <?php echo date('Y-m-d H:i:s P', $checked); ?></p>
  </div>
</div>
