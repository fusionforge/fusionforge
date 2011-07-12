<?php
/**
 * Registration verification page
 *
 * This page is accessed with the link sent in account confirmation
 * email.
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2010 (c) Franck Villaume
 *
 * This file is part of FusionForge. FusionForge is free software;
 * you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or (at your option)
 * any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with FusionForge; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

require_once('../env.inc.php');
require_once $gfcommon.'include/pre.php';

$confirm_hash = getStringFromRequest('confirm_hash');

if (getStringFromRequest('submit')) {
	$loginname = getStringFromRequest('loginname');
	$passwd = getStringFromRequest('passwd');

	if (!$loginname) {
		exit_missing_param('',array(_('UserName')),'my');
	}

	$u = user_get_object_by_name($loginname);
	if (!$u && forge_get_config('require_unique_email')) {
		$u = user_get_object_by_email ($loginname);
	}
	if (!$u || !is_object($u)) {
		exit_error(_('Could Not Get User'),'home');
	} elseif ($u->isError()) {
		exit_error($u->getErrorMessage(),'my');
	}

	if ($u->getStatus()=='A'){
		exit_error(_('Account already active.'),'my');
	}

	$confirm_hash = html_clean_hash_string($confirm_hash);

	if ($confirm_hash != $u->getConfirmHash()) {
		exit_error(_('Cannot confirm account identity - invalid confirmation hash (or login name)'),'my');
	}

	if (!session_login_valid($loginname, $passwd, 1)) {
		exit_permission_denied(_('Credentials you entered do not correspond to valid account.'),'my');
	}

	if (!$u->setStatus('A')) {
		exit_error( _('Error while activiting account').': '.$u->getErrorMessage(),'my');
	}

	session_redirect("/account/first.php");
}

$HTML->header(array('title'=>'Verify'));

echo '<p>' . _('In order to complete your registration, login now. Your account will then be activated for normal logins.') . '</p>';

if (isset($GLOBALS['error_msg'])) {
	print '<p class="error">'.$GLOBALS['error_msg'].'</p>';
}
?>

<form action="<?php echo util_make_url('/account/verify.php'); ?>" method="post">

<p><?php
if (forge_get_config('require_unique_email')) {
	echo _('Login name or email address:');
} else {
	echo _('Login name:');
}
?>
<br /><input type="text" name="loginname" /></p>
<p><?php echo _('Password:'); ?>
<br /><input type="password" name="passwd" /></p>
<input type="hidden" name="confirm_hash" value="<?php print htmlentities($confirm_hash); ?>" />
<p><input type="submit" name="submit" value="<?php echo _('Login'); ?>" /></p>
</form>

<?php
$HTML->footer(array());

?>
