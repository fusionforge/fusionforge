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
			$Language->getText('general','error'),
			$Language->getText('account_change_pw','old_password_incorrect')
		);
	}
	
	if (strlen($passwd)<6) {
		exit_error(
			$Language->getText('general','error'),
			$Language->getText('account_change_pw','not_valid_password')
		);
	}
	
	if ($passwd != $passwd2) {
		exit_error(
			$Language->getText('general','error'),
			$Language->getText('account_change_pw','passwords_dont_match')
		);
	}

        if (!$u->setPasswd($passwd)) {
		exit_error(
			$Language->getText('general','error'),
			'Could not change password: '.$u->getErrorMessage()
		);
        }

	site_user_header(array(title=>$Language->getText('account_change_pw_changed','title'),'pagename'=>'account_change_pw'));
	?>

	<?php echo $Language->getText('account_change_pw', 'confirmation', $GLOBALS[sys_name]); ?>

	<p>
	<?php echo $Language->getText('account_change_pw_changed','return_to',array('<a href="/account/">','</a>')) ?>
	</p>
	
	<?php
} else { 
	// Show change form
	site_user_header(array(title=>$Language->getText('account_change_pw','title'),'pagename'=>'account_change_pw'));
	?>

	<form action="<?php echo $PHP_SELF; ?>" method="post">
	<p><?php echo $Language->getText('account_change_pw','old_password') ?>:
	<br /><input type="password" name="old_passwd" /></p>
	<p><?php echo $Language->getText('account_change_pw','new_password') ?>:
	<br /><input type="password" name="passwd" /></p>
	<p><?php echo $Language->getText('account_change_pw','new_password_repeat') ?>:
	<br /><input type="password" name="passwd2" /></p>
	<p><input type="submit" name="submit" value="<?php echo $Language->getText('account_change_pw','update') ?>" /></p>
	</form>
	<?php
}

site_user_footer(array());

?>
