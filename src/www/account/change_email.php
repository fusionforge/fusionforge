<?php
/**
 * Change user's email page
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

session_require_login () ;

if (getStringFromRequest('submit')) {
	if (!form_key_is_valid(getStringFromRequest('form_key'))) {
		exit_form_double_submit('my');
	}

	$newemail = getStringFromRequest('newemail');

	if (!validate_email($newemail)) {
		form_release_key(getStringFromRequest('form_key'));
		exit_error(_('Invalid email address.'),'my');
	}

	$confirm_hash = substr(md5($GLOBALS['session_ser'] . time()),0,16);

	$u =& user_get_object(user_getid());
	if (!$u || !is_object($u)) {
   		form_release_key(getStringFromRequest('form_key'));
   		exit_error(_('Could Not Get User'),'my');
	} elseif ($u->isError()) {
		form_release_key(getStringFromRequest('form_key'));
		exit_error($u->getErrorMessage(),'my');
	}

	if (!$u->setNewEmailAndHash($newemail, $confirm_hash)) {
		form_release_key(getStringFromRequest('form_key'));
		exit_error($u->getErrorMessage(),'my');
	}

	$message = sprintf(_('You have requested a change of email address on %1$s.
Please visit the following URL to complete the email change:

%2$s

 -- the %1$s staff'),
					 forge_get_config ('forge_name'),
					 util_make_url ('/account/change_email-complete.php?ch=_'.$confirm_hash));

	util_send_message($newemail,sprintf(_('%1$s Verification'), forge_get_config ('forge_name')),$message);

	site_user_header(array('title'=>_('Email Change Confirmation')));

	print '<p>' . _('An email has been sent to the new address. Follow the instructions in the email to complete the email change.') . '</p>';
    printf ('<a href="%1$s">[ Home ]</a>', util_make_url ('/'));

	site_user_footer(array());
	exit();
}


site_user_header(array('title'=>_('Email change')));

echo '<p>' . _('Changing your email address will require confirmation from your new email address, so that we can ensure we have a good email address on file.') . '</p>';
echo '<p>' . _('We need to maintain an accurate email address for each user due to the level of access we grant via this account. If we need to reach a user for issues arriving from a shell or project account, it is important that we be able to do so.') . '</p>';
echo '<p>' . _('Submitting the form below will mail a confirmation URL to the new email address. Visiting this link will complete the email change.') . '</p>';
?>

<form action="<?php echo util_make_url('/account/change_email.php'); ?>" method="post">
<p>
<input type="hidden" name="form_key" value="<?php echo form_generate_key(); ?>"/>
<?php echo _('New Email Address:') ?>
<input type="text" name="newemail" maxlength="255" />
<input type="submit" name="submit" value="<?php echo _('Send Confirmation to New Address') ?>" />
</p>
</form>

	<p><?php echo util_make_link('/', _('Return')); ?></p>

<?php
site_user_footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
