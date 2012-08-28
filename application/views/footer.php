    <div id="footer">
<?php if ($_signedin): ?>
      <p>Signed in as <?php echo $_username; ?></p>
      <p><?php echo anchor('auth/signout', 'Sign Out'); ?></p>
<?php else: ?>
      <p><?php echo anchor('auth/signin', 'Sign In'); ?> or <?php echo anchor('auth/register', 'Register'); ?></p>
<?php endif; ?>
      <p><?php echo anchor('home/privacy', 'Privacy Statement'); ?></p>
      <p>&copy; Copyright 2012 <?php echo anchor('http://www.jakebarnes.com.au', 'Jake Barnes'); ?></p>
    </div>
  </body>
</html>

