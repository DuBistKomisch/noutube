<?php echo validation_errors(); ?>
<?php echo form_open('auth/register'); ?>
<p><?php echo form_label('Username:', 'username'); ?></p>
<p><?php echo form_input('username', set_value('username')); ?></p>
<p><?php echo form_label('Password:', 'password'); ?></p>
<p><?php echo form_password('password'); ?></p>
<p><?php echo form_label('Verify:', 'password_verify'); ?></p>
<p><?php echo form_password('password_verify'); ?></p>
<p><?php echo form_submit('submit', 'Register'); ?></p>
<p><?php echo form_reset('reset', 'Reset'); ?></p>
<?php echo form_close(); ?>
