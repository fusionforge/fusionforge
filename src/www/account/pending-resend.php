<?php
/**
 * Resend account activation email with confirmation URL
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2010-2013, Franck Villaume - TrivialDev
 * Copyright 2013, French Ministry of National Education
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

require_once '../env.inc.php';
require_once $gfcommon.'include/pre.php';

if (getStringFromRequest('submit')) {
	$loginname = trim(getStringFromRequest('loginname'));
	if (!strlen($loginname)) {
		$error_msg = _('Missing Parameter. You must provide a login name or an email address.');
	} else {
		$u = user_get_object_by_name($loginname);
		if (!$u && forge_get_config('require_unique_email')) {
			$u = user_get_object_by_email($loginname);
		}
		if (!$u || !is_object($u)) {
			$error_msg = _('That user does not exist.');
		} elseif ($u->isError()) {
			$error_msg = $u->getErrorMessage();
		} elseif ($u->getStatus() != 'P') {
			$warning_msg = _('Your account is already active.');
		} else{
			$u->sendRegistrationEmail();
			$HTML->header(array('title'=>_('Pending Account')));
			echo '<p>'. _('Your email confirmation has been resent. Visit the link in this email to complete the registration process.'). '</p>';
			$HTML->footer(array());
			exit;
		}
	}
}

$HTML->header(array('title'=>_('Resend confirmation email to a pending account')));

if (forge_get_config('require_unique_email')) {
	echo _('Fill in a user name or email address and click “Submit” to resend the confirmation email.');
} else {
	echo _('Fill in a user name and click “Submit” to resend the confirmation email.');
}
?>

<form action="<?php echo util_make_url('/account/pending-resend.php'); ?>" method="post">
<p><?php
if (forge_get_config('require_unique_email')) {
	echo _('Login name or email address')._(':');
} else {
	echo _('Login Name')._(':');
}
?>
<br />
<label for="loginname">
	<input id="loginname" required="required" type="text" name="loginname"/>
</label>
</p>
<p><input type="submit" name="submit" value="<?php echo _('Submit'); ?>" /></p>
</form>

<?php $HTML->footer(array());
