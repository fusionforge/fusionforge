<?php
/**
  *
  * Commit user's email change
  *
  * This page should be accessed with confirmation URL sent to user in email
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

if (!$u->setEmail($u->getNewEmail())) {
	exit_error(
		'Could Not Complete Operation',
		$u->getErrorMessage()
	);
}

site_user_header(array('title'=>$Language->getText('account_change_email-complete','title'),'pagename'=>'account_change_email'));
?>

<p>
<?php echo $Language->getText('account_change_email-complete','confirm', array($u->getUnixName(),$u->getEmail(),'&lt;'.$u->getUnixName().'@'.$GLOBALS['sys_users_host'].'&gt')) ?>
</p>

<p><a href="/"><?php echo $Language->getText('account_change_email', 'return'); ?></a></p>

<?php

site_user_footer(array());

?>
