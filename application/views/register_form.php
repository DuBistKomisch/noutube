<h2>Register</h2>
<?php echo form_open('auth/register'); ?>
<table>
  <tr>
    <td><?php echo form_label('Username:', 'username'); ?></td>
    <td><?php echo form_input('username', set_value('username')); ?></td>
    <td><?php echo form_error('username'); ?></td>
  </tr>
  <tr>
    <td><?php echo form_label('Password:', 'password'); ?></td>
    <td><?php echo form_password('password'); ?></td>
    <td><?php echo form_error('password'); ?></td>
  </tr>
  <tr>
    <td><?php echo form_label('Verify:', 'password_verify'); ?></td>
    <td><?php echo form_password('password_verify'); ?></td>
    <td><?php echo form_error('password_verify'); ?></td>
  </tr>
  <tr>
    <td colspan="3"><?php echo form_submit('submit', 'Register'); ?></td>
  </tr>
</table>
<?php echo form_close(); ?>
