<?php
/**
 * Change user's email page
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

session_require(array('isloggedin'=>1));


if (getStringFromRequest('submit')) {
	if (!form_key_is_valid(getStringFromRequest('form_key'))) {
		exit_form_double_submit();
	}

	$newemail = getStringFromRequest('newemail');

	if (!validate_email($newemail)) {
		form_release_key(getStringFromRequest('form_key'));
		exit_error(_('Error'),_('Error'));
	}

	$confirm_hash = substr(md5($session_hash . time()),0,16);

	$u =& user_get_object(user_getid());
	if (!$u || !is_object($u)) {
   		form_release_key(getStringFromRequest('form_key'));
   		exit_error('Error','Could Not Get User');
	} elseif ($u->isError()) {
		form_release_key(getStringFromRequest('form_key'));
		exit_error('Error',$u->getErrorMessage());
	}

	if (!$u->setNewEmailAndHash($newemail, $confirm_hash)) {
		form_release_key(getStringFromRequest('form_key'));
		exit_error(
			'Could Not Complete Operation',
			$u->getErrorMessage()
		);
	}

	$message = stripcslashes(sprintf(_('You have requested a change of email address on %1$s.
Please visit the following URL to complete the email change:

<http://%1$s/account/change_email-complete.php?ch=_%2$s>

 -- the %1$s staff'), getStringFromServer('HTTP_HOST'), $confirm_hash, $GLOBALS['sys_name']));

	util_send_message($newemail,sprintf(_('%1$s Verification'), $GLOBALS['sys_name']),$message);

	site_user_header(array('title'=>_('Email Change Confirmation')));

	echo _('<p>An email has been sent to the new address. Follow the instructions in the email to complete the email change. </p><a href="/">[ Home ]</a>');

	site_user_footer(array());
	exit();
}


site_user_header(array('title'=>_('Email change')));

echo _('<p>Changing your email address will require confirmation from your new email address, so that we can ensure we have a good email address on file.</p><p>We need to maintain an accurate email address for each user due to the level of access we grant via this account. If we need to reach a user for issues arriving from a shell or project account, it is important that we be able to do so.</p>  <p>Submitting the form below will mail a confirmation URL to the new email address. Visiting this link will complete the email change.</p>');
?>

<form action="<?php echo getStringFromServer('PHP_SELF'); ?>" method="post">
<input type="hidden" name="form_key" value="<?php echo form_generate_key(); ?>"/>
<?php echo _('New Email Address:') ?>
<input type="text" name="newemail" maxlength="255" />
<input type="submit" name="submit" value="<?php echo _('Send Confirmation to New Address') ?>" />
</form>

<p><a href="<?php echo $GLOBALS['sys_urlprefix']; ?>/"><?php printf(_('Return'), $sys_name); ?></a></p>

<?php
site_user_footer(array());

?>
