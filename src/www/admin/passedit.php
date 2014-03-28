<?php
/**
 * Site Admin user password editing page
 *
 * Copyright © 2010
 *	Thorsten “mirabilos” Glaser <t.glaser@tarent.de>
 * All rights reserved.
 * Copyright 2013-2014, Franck Villaume - TrivialDev
 *
 * Based on other FusionForge code.
 *
 * This file is part of FusionForge. FusionForge is free software;
 * you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option)
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
require_once $gfwww.'include/pre.php';
require_once $gfcommon.'include/account.php';
require_once $gfwww.'admin/admin_utils.php';

global $HTML;

session_require_global_perm ('forge_admin');

$user_id = getIntFromRequest('user_id');
$u =& user_get_object($user_id);
if (!$u || !is_object($u)) {
	exit_error('Error','Could Not Get User');
} elseif ($u->isError()) {
	exit_error('Error',$u->getErrorMessage());
}

if (getStringFromRequest('submit')) {
	if (!form_key_is_valid(getStringFromRequest('form_key'))) {
		exit_form_double_submit();
	}

	$passwd = getStringFromRequest('passwd');
	$passwd2 = getStringFromRequest('passwd2');

	if (strlen($passwd)<6) {
		form_release_key(getStringFromRequest('form_key'));
		exit_error(
			_('Error'),
			_('You must supply valid password (at least 6 chars).')
		);
	}

	if ($passwd != $passwd2) {
		form_release_key(getStringFromRequest('form_key'));
		exit_error(
			_('Error'),
			_('New passwords do not match.')
		);
	}

	if (!$u->setPasswd($passwd)) {
		form_release_key(getStringFromRequest('form_key'));
		exit_error(
			_('Error'),
			_('Could not change password: ').$u->getErrorMessage()
		);
	}

	site_admin_header(array('title'=>_('Site Admin: Change User Password')));

	echo '<h2>';
	printf(_('%s Password Change Confirmation'), forge_get_config('forge_name'));
	echo '</h2>';
	echo $HTML->feedback(sprintf(_('You have changed successfully the password of %1$s (%2$s).'), $u->getUnixName(), $u->getRealName()));
	printf('<p>'._('Go back to %s.').'</p>', util_make_link('/admin/userlist.php', _('the Full User List')));
} else {
	// Show change form
	site_admin_header(array('title'=>_('Site Admin: Change User Password')));
	?>

	<form action="<?php echo util_make_uri('/admin/passedit.php?user_id='.$user_id); ?>" method="post">
	<input type="hidden" name="form_key" value="<?php echo form_generate_key(); ?>"/>
	<p><?php printf(_('Changing password for user #%1$s "%2$s" (%3$s).'), $user_id, $u->getUnixName(), $u->getRealName()); ?></p>
	<p><?php echo _('New Password (at least 6 chars)') ?>:
	<br /><input type="password" name="passwd" required="required" title="<?php echo _('At least 6 chars'); ?>" pattern=".{6,}"/></p>
	<p><?php echo _('New Password (repeat)') ?>:
	<br /><input type="password" name="passwd2" required="required" title="<?php echo _('At least 6 chars'); ?>" pattern=".{6,}" /></p>
	<p><input type="submit" name="submit" value="<?php echo _('Update password') ?>" /></p>
	</form>
	<?php
}

site_admin_footer();
