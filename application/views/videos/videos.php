<h2>Video Manager</h2>
<p><?php echo anchor('videos', 'View Video Lists'); ?> to see new videos and those marked to watch later.</p>
<p><?php echo anchor('videos/all', 'View All Subscriptions'); ?> to see those not listed.</p>
<p><?php echo anchor('videos/update', 'Update Subscriptions'); ?> to automatically add or remove subscriptions.</p>
<p>New videos will be updated in about <?php echo (90 - date('i')) % 60; ?> minutes.</p>

