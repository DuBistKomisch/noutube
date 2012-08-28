<div class="video">
  <img src="http://i3.ytimg.com/vi/<?php echo $video; ?>/default.jpg" alt="<?php echo $video; ?> thumbnail">
  <div class="subright">
    <p><?php echo anchor('http://www.youtube.com/watch?v=' . $video, $title); ?> &ndash; <?php echo $duration_hms; ?></p>
    <p class="update" title="<?php echo $published_date; ?>">Published <?php echo $published_ago; ?> ago</p>
  </div>
<?php if ($form === 'new'): ?>
  <p><?php echo form_checkbox('new' . $video) . form_label('Watch later', 'new' . $video); ?></p>
<?php elseif ($form === 'later'): ?>
  <p><?php echo form_checkbox('later' . $video) . form_label('Watched', 'later' . $video); ?></p>
<?php endif; ?>
</div>
