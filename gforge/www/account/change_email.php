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
		exit_error('Error','Invalid email address');
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

	mail($newemail,$Language->getText('account_change_email', 'subject', $GLOBALS[sys_name]),$message,"From: noreply@$GLOBALS[HTTP_HOST]");

	site_user_header(array('title'=>"Email Change Confirmation",'pagename'=>'account_change_email'));
?>

<P>An email has been sent to the new address. Follow
the instructions in the email to complete the email change.

<P><A href="/">[ Home ]</A>

<?php
	site_user_footer(array());
	exit();
}


site_user_header(array('title'=>"Change Email Address",'pagename'=>'account_change_email'));
?>

<P>Changing your email address will require confirmation from your 
new email address, so that we can ensure we have a good email address
on file.

<P>We need to maintain an accurate email address for each user due
to the level of access we grant via this account. If we need to reach a user
for issues arriving from a shell or project account, it is important that
we be able to do so.

<P>Submitting the form below will mail a confirmation URL to the new
email address. Visiting this link will complete the email change.

<FORM action="<?php echo $PHP_SELF; ?>" method="POST">
New Email Address:
<INPUT type="text" name="newemail">
<INPUT type="submit" name="submit" value="Send Confirmation to New Address">
</FORM>

<P><A href="/"><?php echo $Language->getText('general', 'return'); ?></A>

<?php
site_user_footer(array());

?>
