<?php
/**
 * Change user's password
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2010 (c) Franck Villaume - Capgemini
 * Copyright 2014, Franck Villaume - TrivialDev
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
require_once $gfcommon.'include/account.php';

global $HTML;

session_require_login () ;

$u = user_get_object(user_getid());
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

	if ($u->getUnixPasswd() !== crypt($old_passwd, $u->getUnixPasswd())) {
		form_release_key(getStringFromRequest('form_key'));
		exit_error(_('Old password is incorrect'),'my');
	}

	if ($passwd != $passwd2) {
		form_release_key(getStringFromRequest('form_key'));
		exit_error(_('New passwords do not match.'),'my');
	}

	if (!$u->setPasswd($passwd)) {
		form_release_key(getStringFromRequest('form_key'));
		exit_error(_('Could not change password: ').$u->getErrorMessage(),'my');
	}

	site_user_header(array('title'=>_('Successfully Changed Password')));
	echo html_e('h2', array(), sprintf(_('%s Password Change Confirmation'), forge_get_config ('forge_name')));
	echo $HTML->feedback(_('Congratulations. You have changed your password.'));
	echo html_e('p', array(), sprintf(_('You should now <a href="%s">Return to User Prefs</a>.'), util_make_uri('/account/')));
} else {
	// Show change form
	site_user_header(array('title'=>_('Change Password')));
	echo $HTML->openForm(array('action' => '/account/change_pw.php', 'method' => 'post'));
	echo html_e('input', array('type' => 'hidden', 'name' => 'form_key', 'value' => form_generate_key()));
	echo html_e('p', array(), _('Old Password')._(':').utils_requiredField().
				html_e('br').
				html_e('label', array('for' => 'old_passwd'), html_e('input',array('id' => 'old_passwd', 'type' => 'password', 'name' => 'old_passwd', 'required'=> 'required'))));
	echo html_e('p', array(), _('New Password')._(':').utils_requiredField().
				html_e('br').
				html_e('em', array(),
					_('Minimum 8 characters.').html_e('br').
					(forge_get_config('check_password_strength') ? _('Must contain at least one uppercase letter, one lowercase, one digit, one non-alphanumeric character.').html_e('br') : '')).
				html_e('label', array('for' => 'passwd'), html_e('input', array('id' => 'passwd', 'type' => 'password', 'name' => 'passwd', 'required' => 'required', 'pattern' => '.{8,}'))));
	echo html_e('p', array(), _('New Password (repeat)')._(':').utils_requiredField().
				html_e('br').
				html_e('label', array('for' => 'passwd2'), html_e('input', array('id' => 'passwd2', 'type' => 'password', 'name' => 'passwd2', 'required' => 'required', 'pattern' => '.{8,}'))));
	echo html_e('p', array(), html_e('input', array('type' => 'submit', 'name' => 'submit', 'value' => _('Update password'))));
	echo $HTML->closeForm();
	echo $HTML->addRequiredFieldsInfoBox();
}

site_user_footer();
