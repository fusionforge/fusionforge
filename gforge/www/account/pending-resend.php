<?php
/**
  *
  * Resend account activation email with confirmation URL
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */

require_once('pre.php');

if ($submit) {
	$u = user_get_object_by_name($loginname);
	exit_assert_object($u, 'User');

	if ($u->getStatus() != 'P') {
		exit_error(
			'Invalid action',
			'Your account is already active.'
		);
	}
	$u->sendRegistrationEmail();
	$HTML->header(array(title=>"Account Pending Verification"));
	
	?>

	<h2>Pending Account</h2>
	<p>
	Your email confirmation has been resent. Visit the link
	in this email to complete the registration process.
	</p>

<?php
	exit;
}

$HTML->header(array('title'=>'Pending-resend'));
echo $Language->getText('account_login', 'resend_pending_directions');
?>

<form action="<?php echo $PHP_SELF; ?>" method="post">
<p><?php echo $Language->getText('account_verify', 'loginname'); ?>
<br /><input type="text" name="loginname" /></p>
<p><input type="submit" name="submit" value="<?php echo "Submit"; ?>" /></p>
</form>

<?php $HTML->footer(array()); ?>
