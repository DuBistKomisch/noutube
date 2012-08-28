<div class="subscription">
  <img src="<?php echo $thumbnail; ?>" alt="<?php echo $display; ?> thumbnail">
  <div class="subright">
    <p><?php echo anchor('videos/channel/' . $username, $display); ?> &mdash; <?php echo $new; ?> new, <?php echo $later; ?> to watch later</p>
    <p class="update" title="<?php echo $checked_date; ?>">Updated <?php echo $checked_ago; ?> ago</p>
  </div>
</div>
