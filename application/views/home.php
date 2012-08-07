<?php if ($signedin): ?>
<p>In order to get your videos, you need to authorise us to access your data.</p>
<p><?php echo anchor(site_url('auth/token'), 'Authorise'); ?></p>
<?php else: ?>
<p>Welcome!</p>
<p>You need to be signed in to use this website.</p>
<?php endif; ?>
