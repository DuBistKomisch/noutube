<div class="channel">
  <img src="<?php echo $thumbnail; ?>" alt="<?php echo $display; ?> thumbnail">
  <div class="subright">
    <p><?php echo anchor('http://www.youtube.com/user/' . $username, $display); ?><?php if ($new > 0 || $later > 0) echo ' &mdash; '; ?><?php if ($new > 0) echo $new . ' new'; ?><?php if ($new > 0 && $later > 0) echo ', '; ?><?php if ($later > 0) echo $later . ' to watch later'; ?></p>
    <p class="update" title="<?php echo $checked_date; ?>">Updated <?php echo $checked_ago; ?> ago</p>
  </div>
</div>
