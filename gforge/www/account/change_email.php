<?php
/**
  *
  * Change user's email page
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */

require_once('pre.php');

session_require(array('isloggedin'=>1));


if ($newemail) {

	if (!validate_email($newemail)) {
		exit_error($Language->getText('general','error'),$Language->getText('account_change_email','invalid_email'));
	}

	$confirm_hash = substr(md5($session_hash . time()),0,16);

	$u =& user_get_object(user_getid());
	exit_assert_object($u, 'User');

	if (!$u->setNewEmailAndHash($newemail, $confirm_hash)) {
		exit_error(
			'Could Not Complete Operation',
			$u->getErrorMessage()
		);
	}

	$message = stripcslashes($Language->getText('account_change_email', 'message', array($GLOBALS[HTTP_HOST], $confirm_hash, $GLOBALS[sys_name])));

	util_send_message($newemail,$Language->getText('account_change_email', 'subject', $GLOBALS[sys_name]),$message);

	site_user_header(array('title'=>$Language->getText('account_change_email_confirm','title'),'pagename'=>'account_change_email'));

	echo $Language->getText('account_change_email', 'mailsent');

	site_user_footer(array());
	exit();
}


site_user_header(array('title'=>$Language->getText('account_change_email','title'),'pagename'=>'account_change_email'));

echo $Language->getText('account_change_email', 'desc');
?>

<form action="<?php echo $PHP_SELF; ?>" method="post">
<?php echo $Language->getText('account_change_email','new_address') ?>
<input type="text" name="newemail" maxlength="255" />
<input type="submit" name="submit" value="<?php echo $Language->getText('account_change_email','send_confirmation') ?>" />
</form>

<p><a href="/"><?php echo $Language->getText('general', 'return', $GLOBALS[sys_name]); ?></a></p>

<?php
site_user_footer(array());

?>
