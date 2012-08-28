<div class="video">
  <img src="http://i3.ytimg.com/vi/<?php echo $video; ?>/default.jpg" alt="<?php echo $video; ?> thumbnail">
  <div class="subright">
    <p><?php echo anchor('http://www.youtube.com/watch?v=' . $video, $title); ?> &ndash; <?php echo $duration_hms; ?></p>
    <p class="update" title="<?php echo $published_date; ?>">Published <?php echo $published_ago; ?> ago</p>
  </div>
</div>
