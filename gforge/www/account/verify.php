<?php
/**
  *
  * Registration verification page
  *
  * This page is accessed with the link sent in account confirmation
  * email.
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

	if (!$loginname) {
		exit_error(
			$Language->getText('account_verify','missingparam'),
			$Language->getText('account_verify','usermandatory')
		);
	}

	$u = user_get_object_by_name($loginname);

	if (!$u || !is_object($u)){
		exit_error(
			$Language->getText('account_verify','invaliduser'),
			$Language->getText('account_verify','nouser')
		);
	}

	if ($u->getStatus()=='A'){
		exit_error(
			$Language->getText('account_verify','invalidop'),
			$Language->getText('account_verify','accountactive')
		);
	}

	$confirm_hash = html_clean_hash_string($confirm_hash);

	if ($confirm_hash != $u->getConfirmHash()) {
		exit_error(
			$Language->getText('account_verify','invalidparam'),
			$Language->getText('account_verify','cannotconfirm')
		);
	}

	if (!session_login_valid($loginname, $passwd, 1)) {
		exit_error(
			$Language->getText('account_verify','accessdenied'),
			$Language->getText('account_verify','invalidcred')
		);
	}

	if (!$u->setStatus('A')) {
		exit_error(
			$Language->getText('account_verify','cannotactivate'),
			$Language->getText('account_verify','erroractivate').': '.$u->getErrorMessage()
		);
	}

	session_redirect("/account/first.php");
}

$HTML->header(array('title'=>'Login','pagename'=>'account_verify'));

echo $Language->getText('account_verify', 'verify_blurb');

if ($GLOBALS['error_msg']) {
	print '<P><FONT color="#FF0000">'.$GLOBALS['error_msg'].'</FONT>';
}
?>

<form action="<?php echo $PHP_SELF; ?>" method="POST">

<p><? echo $Language->getText('account_verify', 'loginname'); ?>
<br><input type="text" name="loginname">
<p><? echo $Language->getText('account_verify', 'password'); ?>
<br><input type="password" name="passwd">
<INPUT type="hidden" name="confirm_hash" value="<?php print $confirm_hash; ?>">
<p><input type="submit" name="submit" value="<? echo $Language->getText('account_verify', 'login'); ?>">
</form>

<?php
$HTML->footer(array());

?>
