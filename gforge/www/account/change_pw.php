<?php
/**
  *
  * Change user's password
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */

require_once('pre.php');
require_once('common/include/account.php');

session_require(array('isloggedin'=>1));
$u =& user_get_object(user_getid());
exit_assert_object($u, 'User');

if ($submit) {

	if ($u->getMD5Passwd() != md5($old_passwd)) {
		exit_error(
			'Error',
			'Old password is incorrect.'
		);
	}
	
	if (strlen($passwd)<6) {
		exit_error(
			'Error',
			'You must supply valid password (at least 6 chars).'
		);
	}
	
	if ($passwd != $passwd2) {
		exit_error(
			'Error',
			'New passwords do not match.'
		);
	}

        if (!$u->setPasswd($passwd)) {
		exit_error(
			'Error',
			'Could not change password: '.$u->getErrorMessage()
		);
        }

	site_user_header(array(title=>"Successfully Changed Password",'pagename'=>'account_change_pw'));
	?>

	<?php echo $Language->getText('account_change_pw', 'confirmation', $GLOBALS[sys_name]); ?>

	<p>
	You should now <a href="/account/">Return to UserPrefs</a>.
	</p>
	
	<?php
} else { 
	// Show change form
	site_user_header(array(title=>"Change Password",'pagename'=>'account_change_pw'));
	?>

	<form action="<?php echo $PHP_SELF; ?>" method="post">
	<p>Old Password:
	<br /><input type="password" name="old_passwd" /></p>
	<p>New Password (at least 6 chars):
	<br /><input type="password" name="passwd" /></p>
	<p>New Password (repeat):
	<br /><input type="password" name="passwd2" /></p>
	<p><input type="submit" name="submit" value="Update" /></p>
	</form>
	<?php
}

site_user_footer(array());

?>
