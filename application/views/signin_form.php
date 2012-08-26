<h2>Sign In</h2>
<?php echo form_open('auth/signin'); ?>
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
    <td colspan="3"><?php echo form_submit('submit', 'Sign In'); ?></td>
  </tr>
</table>
<?php echo form_close(); ?>
