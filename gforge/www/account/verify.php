<?php
/**
 * Registration verification page
 *
 * This page is accessed with the link sent in account confirmation
 * email.
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 *
 * @version   $Id$
 *
 * This file is part of GForge.
 *
 * GForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
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
	if (!$u || !is_object($u)) {
		exit_error('Error','Could Not Get User');
	} elseif ($u->isError()) {
		exit_error('Error',$u->getErrorMessage());
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

$HTML->header(array('title'=>'Verify'));

echo $Language->getText('account_verify', 'verify_blurb');

if ($GLOBALS['error_msg']) {
	print '<p><font color="#FF0000">'.$GLOBALS['error_msg'].'</font>';
}
?>

<form action="<?php echo $PHP_SELF; ?>" method="post">

<p><?php echo $Language->getText('account_verify', 'loginname'); ?>
<br /><input type="text" name="loginname" /></p>
<p><?php echo $Language->getText('account_verify', 'password'); ?>
<br /><input type="password" name="passwd" /></p>
<input type="hidden" name="confirm_hash" value="<?php print $confirm_hash; ?>" />
<p><input type="submit" name="submit" value="<?php echo $Language->getText('account_verify', 'login'); ?>" /></p>
</form>

<?php
$HTML->footer(array());

?>
