<?php
/**
 * Request recovery of the lost password
 *
 * This page sends confirmation email with link to reset password
 * for account.
 *
 * Portions Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2002-2004 (c) GForge Team
 * Copyright 2010 (c) Franck Villaume
 * http://fusionforge.org/
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

if (getStringFromRequest('submit')) {
	if (!form_key_is_valid(getStringFromRequest('form_key'))) {
		exit_form_double_submit('my');
	}

	$loginname = getStringFromRequest('loginname');

	$u = user_get_object_by_name($loginname);

	if (!$u || !is_object($u)){
		form_release_key(getStringFromRequest('form_key'));
		exit_error(_('That user does not exist.'),'my');
	}

	// First, we need to create new confirm hash

	$confirm_hash = md5(forge_get_config('session_key') . strval(time()) . strval(util_randbytes()));

	$u->setNewEmailAndHash($u->getEmail(), $confirm_hash);
	if ($u->isError()) {
		form_release_key(getStringFromRequest('form_key'));
		exit_error($u->getErrorMessage(),'my');
	} else {

		$message = sprintf(_('Someone (presumably you) on the %1$s site requested a
password change through email verification. If this was not you,
ignore this message and nothing will happen.

If you requested this verification, visit the following URL
to change your password:

<%2$s>

 -- the %1$s staff
'), forge_get_config ('forge_name'), util_make_url ("/account/lostlogin.php?ch=_".$confirm_hash));

		util_send_message($u->getEmail(),sprintf(_('%1$s Verification'), forge_get_config ('forge_name')),$message);

		$HTML->header(array('title'=>"Lost Password Confirmation"));

		echo '<p>'.printf(_('An email has been sent to the address you have on file. Follow the instructions in the email to change your account password.').'</p><p><a href="%1$s">'._("Home").'</a>', util_make_url ('/')).'</p>';

		$HTML->footer(array());
		exit();
	}
}

$HTML->header(array('title'=>"Lost Account Password"));

echo '<p>' . _('Hey... losing your password is serious business. It compromises the security of your account, your projects, and this site.') . '</p>';
echo '<p>' . _('Clicking "Send Lost PW Hash" below will email a URL to the email address we have on file for you. In this URL is a 128-bit confirmation hash for your account. Visiting the URL will allow you to change your password online and login.') . '</p>';
?>

<form action="<?php echo util_make_url('/account/lostpw.php'); ?>" method="post">
<input type="hidden" name="form_key" value="<?php echo form_generate_key(); ?>"/> <p>
<?php echo _('Login name:'); ?>
<br />
<input type="text" name="loginname" />
<br />
<br />
<input type="submit" name="submit" value="<?php echo _('Send Lost PW Hash'); ?>" />
</p>
</form>

	<p><?php echo util_make_link ("/", _('Return')); ?></p>

<?php

$HTML->footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
