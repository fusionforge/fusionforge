<?php
/**
 * Registration verification page
 *
 * This page is accessed with the link sent in account confirmation
 * email.
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2010, Franck Villaume - Capgemini
 * Copyright 2012,2014, Franck Villaume - TrivialDev
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

$confirm_hash = getStringFromRequest('confirm_hash');

if (getStringFromRequest('submit')) {
	$loginname = getStringFromRequest('loginname');
	$passwd = getStringFromRequest('passwd');

	if (!$loginname) {
		exit_missing_param('',array(_('User Name')),'my');
	}
	$loginname = strtolower($loginname);

	$u = user_get_object_by_name($loginname);
	if (!$u && forge_get_config('require_unique_email')) {
		$u = user_get_object_by_email ($loginname);
	}
	$confirm_hash = html_clean_hash_string($confirm_hash);
	if (!$u || !is_object($u)) {
		$error_msg = _('Invalid Password Or User Name');
	} elseif ($u->isError()) {
		$error_msg = $u->getErrorMessage();
	} elseif ($u->getStatus()=='A'){
		$error_msg = _('Account already active.');
	} elseif (($u->getStatus() == 'S') || ($u->getStatus() == 'D')) {
		$error_msg = _('Account is suspended or deleted');
	} elseif ($confirm_hash != $u->getConfirmHash()) {
		$error_msg = _('Cannot confirm account identity - invalid confirmation hash (or login name)');
	} elseif (!session_login_valid($loginname, $passwd, 1)) {
		$warning_msg = _('Credentials you entered do not correspond to valid account.');
	} elseif (!$u->setStatus('A')) {
		$error_msg = _('Error while activating account')._(': ').$u->getErrorMessage();
	} else {
		if (forge_get_config('user_notification_on_activation')) {
			$u->setAdminNotification();
		}
		session_redirect("/account/first.php");
	}
}

$HTML->header(array('title'=>_('Verify')));

echo html_e('p', array(), _('In order to complete your registration, login now. Your account will then be activated for normal logins.'));
echo $HTML->openForm(array('action' => '/account/verify.php?confirm_hash='.$confirm_hash, 'method' => 'post'));
if (forge_get_config('require_unique_email')) {
	$content = _('Login name or email address')._(':');
} else {
	$content = _('Login Name')._(':');
}
echo html_e('p', array(), $content.html_e('br').html_e('label', array('for' => 'loginname'),
							html_e('input', array('id' => 'loginname', 'type' => 'text', 'name' => 'loginname'))));
echo html_e('p', array(), _('Password')._(':').html_e('br').html_e('label', array('for' => 'passwd'),
							html_e('input', array('id' => 'passwd', 'type' => 'password', 'name' => 'passwd'))));
echo html_e('input', array('type' => 'hidden', 'name' => 'confirm_hash', 'value' => htmlentities($confirm_hash)));
echo html_e('p', array(), html_e('input', array('type' => 'submit', 'name' => 'submit', 'value' => _('Login'))));
echo $HTML->closeForm();
$HTML->footer();
