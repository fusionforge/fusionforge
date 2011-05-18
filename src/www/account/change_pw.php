<?php
/**
 * Change user's password
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2010 (c) Franck Villaume - Capgemini
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
require_once $gfcommon.'include/account.php';

session_require_login () ;

$u =& user_get_object(user_getid());
if (!$u || !is_object($u)) {
	exit_error(_('Could Not Get User'),'my');
} elseif ($u->isError()) {
	exit_error($u->getErrorMessage(),'my');
}

if (getStringFromRequest('submit')) {
	if (!form_key_is_valid(getStringFromRequest('form_key'))) {
		exit_form_double_submit('my');
	}

	$old_passwd = getStringFromRequest('old_passwd');
	$passwd = getStringFromRequest('passwd');
	$passwd2 = getStringFromRequest('passwd2');

	if ($u->getMD5Passwd() != md5($old_passwd)) {
		form_release_key(getStringFromRequest('form_key'));
		exit_error(_('Old password is incorrect'),'my');
	}
	
	if (strlen($passwd)<6) {
		form_release_key(getStringFromRequest('form_key'));
		exit_error(_('You must supply valid password (at least 6 chars)'),'my');
	}
	
	if ($passwd != $passwd2) {
		form_release_key(getStringFromRequest('form_key'));
		exit_error(_('New passwords do not match.'),'my');
	}

	if (!$u->setPasswd($passwd)) {
		form_release_key(getStringFromRequest('form_key'));
		exit_error(_('Could not change password: ').$u->getErrorMessage(),'my');
	}
	//plugin webcal change user password
	else {
		plugin_hook('change_cal_password',user_getid());
	}

	site_user_header(array('title'=>_('Successfully Changed Password')));
	?>

	<?php
	print '<h2>';
	printf(_('%1$s Password Change Confirmation'), forge_get_config ('forge_name'));
	print '</h2>';

	print '<div class="feedback">';
	print _('Congratulations. You have changed your password.');
	print '</div>';
	?>

	<p>
		 <?php printf(_('You should now <a href="%1$s">Return to User Prefs</a>.'),
			      util_make_url('/account/')) ?>
	</p>
	
	<?php
} else { 
	// Show change form
	site_user_header(array('title'=>_('Change Password')));
	?>

	<form action="<?php echo util_make_url('/account/change_pw.php'); ?>" method="post">
	<input type="hidden" name="form_key" value="<?php echo form_generate_key(); ?>"/>
	<p><?php echo _('Old Password') ?>:
	<br /><input type="password" name="old_passwd" /></p>
	<p><?php echo _('New Password (at least 6 chars)') ?>:
	<br /><input type="password" name="passwd" /></p>
	<p><?php echo _('New Password (repeat)') ?>:
	<br /><input type="password" name="passwd2" /></p>
	<p><input type="submit" name="submit" value="<?php echo _('Update password') ?>" /></p>
	</form>
	<?php
}

site_user_footer(array());

?>
