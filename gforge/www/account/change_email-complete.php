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

site_user_header(array('title'=>"Email Change Complete",'pagename'=>'account_change_email'));
?>

<p>
Welcome, <?php print $u->getUnixName(); ?>. Your email
change is complete. Your new email address on file is 
<strong>&lt;<?php print $u->getEmail(); ?>&gt;</strong>.

Mail sent to
&lt;<?php print $u->getUnixName(); ?>@<?php print $GLOBALS['sys_users_host']; ?>&gt; 
will now be forwarded to this account.
</p>

<p><a href="/"><?php echo $Language->getText('account_change_email', 'return'); ?></a></p>

<?php

site_user_footer(array());

?>
