<?php
/**
  *
  * Recover lost password page
  *
  * This page is accessed via confirmation URL in email
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

if (!$confirm_hash) {
        $confirm_hash = $ch;
}
// Remove noise from hash produced by buggy mail clients
$confirm_hash = html_clean_hash_string($confirm_hash);
		
$res_user = db_query("SELECT * FROM users WHERE confirm_hash='$confirm_hash'");
if (db_numrows($res_user) > 1) {
	exit_error("Error","This confirm hash exists more than once.");
}
if (db_numrows($res_user) < 1) {
	exit_error("Error","Invalid confirmation hash.");
}
$u =& user_get_object(db_result($res_user, 0, 'user_id'), $res_user);
exit_assert_object($u, 'User');

if ($submit) {

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

        if ($u->setPasswd($passwd)) {
	
		// Invalidate confirm hash
		$u->setNewEmailAndHash('', 0);
		
		$HTML->header(array('title'=>"Password changed"));
		?>

		<h2>Password changed</h2>
		<p>
		Congratulations, you have re-set your account password.
		You may <a href="/account/login.php">login</a> to the site
		now.
		</p>

		<?php
		$HTML->footer(array());
		exit();
        }

	$feedback = 'Error: '.$u->getErrorMessage();
}

$HTML->header(array('title'=>"Lost Password Login"));
?>

<h2>Lost Password Login</h2>

<P>Welcome, <?php echo $u->getUnixName(); ?>. You may now
change your password.

<FORM action="<?php echo $PHP_SELF; ?>" method="POST">
<p>New Password (min. 6 chars):
<br><input type="password" name="passwd">
<p>New Password (repeat):
<br><input type="password" name="passwd2">
<input type="hidden" name="confirm_hash" value="<?php print $confirm_hash; ?>">
<p><input type="submit" name="submit" value="Update">
</form>

<?php

$HTML->footer(array());

?>
