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
	exit_error(
		$Language->getText('global','error'),
		$Language->getText('account_lostlogin','severalconfirm')
	);
}
if (db_numrows($res_user) < 1) {
	exit_error(
		$Language->getText('global','error'),
		$Language->getText('account_lostlogin','invalidconfirm')
	);
}
$u =& user_get_object(db_result($res_user, 0, 'user_id'), $res_user);
exit_assert_object($u, 'User');

if ($submit) {

        if (strlen($passwd)<6) {
	        exit_error(
			$Language->getText('global','error'),
			$Language->getText('account_lostlogin','sixchars')
	        );
	}

	if ($passwd != $passwd2) {
	        exit_error(
			$Language->getText('global','error'),
			$Language->getText('account_lostlogin','notmatch')
		);
	}

        if ($u->setPasswd($passwd)) {
	
		// Invalidate confirm hash
		$u->setNewEmailAndHash('', 0);
		
		$HTML->header(array('title'=>"Password changed"));
		echo $Language->getText('account_lostlogin','passwdchanged');
		$HTML->footer(array());
		exit();
        }

	$feedback = $Language->getText('global','error').': '.$u->getErrorMessage();
}

$HTML->header(array('title'=>"Lost Password Login"));
echo $Language->getText('account_lostlogin','welcome',$u->getUnixName());
?>

<FORM action="<?php echo $PHP_SELF; ?>" method="POST">
<p><?php echo $Language->getText('account_lostlogin','newpasswd'); ?>:
<br><input type="password" name="passwd">
<p><?php echo $Language->getText('account_lostlogin','newpasswd2'); ?>:
<br><input type="password" name="passwd2">
<input type="hidden" name="confirm_hash" value="<?php print $confirm_hash; ?>">
<p><input type="submit" name="submit" value="<?php echo $Language->getText('account_lostlogin','update'); ?>">
</form>

<?php

$HTML->footer(array());

?>
