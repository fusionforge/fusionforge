<?php
/**
 * Resend account activation email with confirmation URL
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2010 (c), Franck Villaume
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
	$loginname = getStringFromRequest('loginname');

	$u = user_get_object_by_name($loginname);
	if (!$u && forge_get_config('require_unique_email')) {
		$u = user_get_object_by_email ($loginname);
	}
	if (!$u || !is_object($u)) {
		exit_error(_('Could Not Get User'),'home');
	} elseif ($u->isError()) {
		exit_error($u->getErrorMessage(),'home');
	}

	if ($u->getStatus() != 'P') {
		exit_error(_('Your account is already active.'),'my');
	}
	$u->sendRegistrationEmail();
	$HTML->header(array('title'=>"Account Pending Verification"));

	?>

	<h2><?php echo _('Pending Account')?></h2>
	<p>
	<?php echo _('Your email confirmation has been resent. Visit the link in this email to complete the registration process.');?>
	</p>

<?php
	exit;
}

$HTML->header(array('title'=>_('Resend confirmation email to a pending account')));

if (forge_get_config('require_unique_email')) {
	echo _('Fill in a user name or email address and click \'Submit\' to resend the confirmation email.');
} else {
	echo _('Fill in a user name and click \'Submit\' to resend the confirmation email.');
}
?>

<form action="<?php echo util_make_url('/account/pending-resend.php'); ?>" method="post">
<p><?php
if (forge_get_config('require_unique_email')) {
	echo _('Login name or email address:');
} else {
	echo _('Login name:');
}
?>
<br /><input type="text" name="loginname" /></p>
<p><input type="submit" name="submit" value="<?php echo _('Submit'); ?>" /></p>
</form>

<?php $HTML->footer(array()); ?>
