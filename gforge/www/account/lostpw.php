<?php
/**
  *
  * Request recovery of the lost password
  *
  * This page sends confirmation email with link to reset password
  * for account.
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */


require_once('pre.php');

if ($loginname) {

	$u = user_get_object_by_name($loginname);

	if (!$u || !is_object($u)){
		exit_error("Invalid user","That user does not exist.");
	}

	// First, we need to create new confirm hash

	$confirm_hash = md5($session_hash . strval(time()) . strval(rand()));

	$u->setNewEmailAndHash($u->getEmail(), $confirm_hash);
	if ($u->isError()) {
		exit_error('Error',$u->getErrorMessage());
	} else {

		$message = stripcslashes($Language->getText('account_lostpw', 'message', array($GLOBALS['HTTP_HOST'], $confirm_hash)));

		mail($u->getEmail(),$Language->getText('account_lostpw', 'subject'),$message,"From: noreply@$GLOBALS[HTTP_HOST]");

		$HTML->header(array('title'=>"Lost Password Confirmation",'pagename'=>'account_lostpw'));

		?>

		<P>An email has been sent to the address you have on file. Follow
		the instructions in the email to change your account password.

		<P><A href="/">[ Home ]</A>

		<?php
		$HTML->footer(array());
		exit();
	}
}


$HTML->header(array('title'=>"Lost Account Password",'pagename'=>'account_lostpw'));
?>

<P>Hey... losing your password is serious business. It compromises the
security of your account, your projects, and this site.

<P>Clicking "Send Lost PW Hash" below will email a URL to the email
address we have on file for you. In this URL is a 128-bit confirmation
hash for your account. Visiting the URL will allow you to change
your password online and login.

<FORM action="<?php echo $PHP_SELF; ?>" method="POST">
<P>
Login Name:
<INPUT type="text" name="loginname"><br>
<INPUT type="submit" name="submit" value="Send Lost PW Hash">
</FORM>

<P><A href="/"><?php echo $Language->getText('general', 'return'); ?></A>

<?php

$HTML->footer(array());

?>
