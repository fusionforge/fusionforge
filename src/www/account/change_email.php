<?php
/**
 * Change user's email page
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2010,2014,2017, Franck Villaume - TrivialDev
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

global $HTML;

session_require_login () ;

if (getStringFromRequest('submit')) {
	if (!form_key_is_valid(getStringFromRequest('form_key'))) {
		exit_form_double_submit('my');
	}

	$newemail = getStringFromRequest('newemail');

	if (!validate_email($newemail)) {
		form_release_key(getStringFromRequest('form_key'));
		exit_error(_('Invalid Email Address'),'my');
	}

	$confirm_hash = substr(md5($GLOBALS['session_ser'].time()), 0, 16);

	$u = user_get_object(user_getid());
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

	$message = sprintf(_('You have requested a change of email address on %s.'), forge_get_config('forge_name'))
			. "\n\n"
			. _('Please visit the following URL to complete the email change')._(':')
			. "\n\n"
			.  util_make_url('/account/change_email-complete.php?ch='.$confirm_hash)
			. "\n\n"
			. sprintf(_('-- the %s staff'), forge_get_config('forge_name'));

	util_send_message($newemail, sprintf(_('%s Verification'), forge_get_config ('forge_name')), $message);

	site_user_header(array('title' => _('Email Change Confirmation')));

	echo html_e('p', array(), _('An email has been sent to the new address. Follow the instructions in the email to complete the email change.'));
	echo util_make_link('/account/', '[ '._('Home').' ]');
} else {
	//show form
	site_user_header(array('title'=>_('Email change')));
	echo html_e('p', array(), _('Changing your email address will require confirmation from your new email address, so that we can ensure we have a good email address on file.'));
	echo html_e('p', array(), _('We need to maintain an accurate email address for each user due to the level of access we grant via this account. If we need to reach a user for issues arriving from a shell or project account, it is important that we be able to do so.'));
	echo html_e('p', array(), _('Submitting the form below will mail a confirmation URL to the new email address. Visiting this link will complete the email change.'));
	echo $HTML->openForm(array('action' => '/account/change_email.php', 'method' => 'post'));
	echo html_e('p', array(), html_e('input', array('type' => 'hidden', 'name' => 'form_key', 'value' => form_generate_key())).
				_('New Email Address')._(':').utils_requiredField().
				html_e('label', array('for' => 'newemail'), html_e('input', array('id' => 'newemail', 'required' => 'required', 'type' => 'email', 'name' => 'newemail', 'maxlength' => 255))).
				html_e('input', array('type' => 'submit', 'name' => 'submit', 'value' => _('Send Confirmation to New Address'))));
	echo $HTML->closeForm();
	echo $HTML->addRequiredFieldsInfoBox();
	echo html_e('p', array(), util_make_link('/account/', _('Return')));
}
site_user_footer();

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
