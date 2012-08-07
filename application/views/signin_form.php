<?php echo validation_errors(); ?>
<?php echo form_open('auth/signin'); ?>
<p><?php echo form_label('Username:', 'username'); ?></p>
<p><?php echo form_input('username', set_value('username')); ?></p>
<p><?php echo form_label('Password:', 'password'); ?></p>
<p><?php echo form_password('password'); ?></p>
<p><?php echo form_submit('submit', 'Sign In'); ?></p>
<p><?php echo form_reset('reset', 'Reset'); ?></p>
<?php echo form_close(); ?>
