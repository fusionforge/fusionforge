<?php
/**
 * Resend account activation email with confirmation URL
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

require_once('../env.inc.php');
require_once('pre.php');

if (getStringFromRequest('submit')) {
	$loginname = getStringFromRequest('loginname');

	$u = user_get_object_by_name($loginname);
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

	<h2>Pending Account</h2>
	<p>
	Your email confirmation has been resent. Visit the link
	in this email to complete the registration process.
	</p>

<?php
	exit;
}

$HTML->header(array('title'=>'Pending-resend'));
echo _('Fill in a user name and click \'Submit\' to resend the confirmation email');
?>

<form action="<?php echo getStringFromServer('PHP_SELF'); ?>" method="post">
<p><?php echo _('Login name:'); ?>
<br /><input type="text" name="loginname" /></p>
<p><input type="submit" name="submit" value="<?php echo "Submit"; ?>" /></p>
</form>

<?php $HTML->footer(array()); ?>
