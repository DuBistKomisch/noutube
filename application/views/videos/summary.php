<h3><?php echo $display; ?></h3>
<div class="subscription">
  <img src="<?php echo $thumbnail; ?>" alt="<?php echo $thumbnail; ?> thumbnail">
  <p>Last Checked: <?php echo date('Y-m-d H:i:s P', $checked); ?></p>
  <p>New Videos: <?php echo $new; ?></p>
  <p>Watch Later: <?php echo $later; ?></p>
</div>

