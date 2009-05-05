<?php
/**
 * Resend account activation email with confirmation URL
 *
 * Copyright 1999-2001 (c) VA Linux Systems
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

require_once('../env.inc.php');
require_once $gfwww.'include/pre.php';

if (getStringFromRequest('submit')) {
	$loginname = getStringFromRequest('loginname');

	$u = user_get_object_by_name($loginname);
	if ($GLOBALS['sys_require_unique_email']) {
		$u ||= user_get_object_by_email ($loginname);
	}
	if (!$u || !is_object($u)) {
		exit_error('Error','Could Not Get User');
	} elseif ($u->isError()) {
		exit_error('Error',$u->getErrorMessage());
	}

	if ($u->getStatus() != 'P') {
		exit_error(
			'Invalid action',
			'Your account is already active.'
		);
	}
	$u->sendRegistrationEmail();
	$HTML->header(array('title'=>"Account Pending Verification"));
	
	?>

	<h2><?php echo _('Pending Account')?></h2>
	<p>
	<?php echo _('Your email confirmation has been resent. Visit the link
	in this email to complete the registration process.');?>
	</p>

<?php
	exit;
}

$HTML->header(array('title'=>'Pending-resend'));
if ($GLOBALS['sys_require_unique_email']) {
	echo _('Fill in a user name or email address and click \'Submit\' to resend the confirmation email.');
} else {
	echo _('Fill in a user name and click \'Submit\' to resend the confirmation email.');
}
?>

<form action="<?php echo util_make_url('/account/pending-resend.php'); ?>" method="post">
<p><?php 
if ($GLOBALS['sys_require_unique_email']) {
	echo _('Login name or email address:');
} else {
	echo _('Login name:'); 
}
?>
<br /><input type="text" name="loginname" /></p>
<p><input type="submit" name="submit" value="<?php echo _('Submit'); ?>" /></p>
</form>

<?php $HTML->footer(array()); ?>
