    <div id="footer">
<?php if ($signedin): ?>
      <span>Signed in as <?php echo $username; ?></span>
      <span><?php echo anchor('auth/signout', 'Sign Out'); ?></span>
<?php else: ?>
      <span><?php echo anchor('auth/signin', 'Sign In'); ?></span>
      <span><?php echo anchor('auth/register', 'Register'); ?></span>
<?php endif; ?>
    </div>
  </body>
</html>
