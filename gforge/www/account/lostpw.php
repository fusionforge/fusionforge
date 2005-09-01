<?php
/**
 * Request recovery of the lost password
 *
 * This page sends confirmation email with link to reset password
 * for account.
 *
 * Portions Copyright 1999-2001 (c) VA Linux Systems
 * The rest Copyright 2002-2004 (c) GForge Team
 * http://gforge.org/
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

if (getStringFromRequest('submit')) {
	if (!form_key_is_valid(getStringFromRequest('form_key'))) {
		exit_form_double_submit();
	}

	$loginname = getStringFromRequest('loginname');

	$u = user_get_object_by_name($loginname);

	if (!$u || !is_object($u)){
		form_release_key(getStringFromRequest('form_key'));
		exit_error($Language->getText('account_lostpw','invalid_user'),$Language->getText('account_lostpw','user_dont_exist'));
	}

	// First, we need to create new confirm hash

	$confirm_hash = md5($session_hash . strval(time()) . strval(rand()));

	$u->setNewEmailAndHash($u->getEmail(), $confirm_hash);
	if ($u->isError()) {
		form_release_key(getStringFromRequest('form_key'));
		exit_error('Error',$u->getErrorMessage());
	} else {

		$message = stripcslashes($Language->getText('account_lostpw', 'message', array(getStringFromServer('HTTP_HOST'), $confirm_hash, $GLOBALS['sys_name'])));

		util_send_message($u->getEmail(),$Language->getText('account_lostpw', 'subject', $GLOBALS['sys_name']),$message);

		$HTML->header(array('title'=>"Lost Password Confirmation"));

		echo $Language->getText('account_lostpw','notify');

		$HTML->footer(array());
		exit();
	}
}


$HTML->header(array('title'=>"Lost Account Password"));

echo $Language->getText('account_lostpw','warn');
?>

<form action="<?php echo getStringFromServer('PHP_SELF'); ?>" method="post">
<input type="hidden" name="form_key" value="<?php echo form_generate_key(); ?>">
<p>
<?php echo $Language->getText('account_login', 'loginname'); ?>
<br />
<input type="text" name="loginname" />
<br />
<br />
<input type="submit" name="submit" value="<?php echo $Language->getText('account_lostpw','sendhash'); ?>" />
</p>
</form>

<p><a href="/"><?php echo $Language->getText('general', 'return', $GLOBALS['sys_name']); ?></a></p>

<?php

$HTML->footer(array());

?>
