<?php
/**
 * FusionForge login page
 *
 * This is main login page. It takes care of different account states
 * (by disallowing logging in with non-active account, with appropriate
 * notice).
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2011, Roland Mas
 * Copyright 2011, Franck Villaume - Capgemini
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

Header("Expires: Wed, 11 Nov 1998 11:11:11 GMT");
Header("Cache-Control: no-cache");
Header("Cache-Control: must-revalidate");

require_once('../../../www/env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once('../../../www/include/login-form.php');

/* because session_check_credentials_in_database is setting warning_msg */
global $warning_msg;

$plugin = plugin_get_object('authbuiltin');

$return_to = getStringFromRequest('return_to');
$login = getStringFromRequest('login');
$form_loginname = getStringFromRequest('form_loginname');
$form_pw = getStringFromRequest('form_pw');
$triggered = getIntFromRequest('triggered');

if (session_loggedin())
	session_redirect('/my');

//
//	Validate return_to
//
if ($return_to) {
	$tmpreturn = explode('?',$return_to);
	$rtpath = $tmpreturn[0] ;

	if (@is_file(forge_get_config('url_root').$rtpath)
	    || @is_dir(forge_get_config('url_root').$rtpath)
	    || (strpos($rtpath,'/projects') == 0)
	    || (strpos($rtpath,'/plugins/mediawiki') == 0)) {
		$newrt = $return_to;
	} else {
		$newrt = '/';
	}
	$return_to = $newrt;
}

if (forge_get_config('use_ssl') && !session_issecure()) {
	//force use of SSL for login
	header('Location: https://'.getStringFromServer('HTTP_HOST').getStringFromServer('REQUEST_URI'));
}

// ###### first check for valid login, if so, redirect

if ($login) {
	if (!form_key_is_valid(getStringFromRequest('form_key'))) {
		exit_form_double_submit();
	}
	if (session_check_credentials_in_database(strtolower($form_loginname), $form_pw, false)) {
		if ($plugin->isSufficient()) {
			$plugin->startSession($form_loginname);
		}
		if ($return_to) {
			session_redirect($return_to);
			exit;
		} else {
			session_redirect('/my');
			exit;
		}
	} else {
		if ($form_loginname && $form_pw) {
		} else {
			$warning_msg = _('Missing Password Or User Name');
		}
	}

	form_release_key(getStringFromRequest('form_key'));
	// Account Pending
	if (!isset($userstatus)) {
		if (!empty($form_loginname)) {
			$u = user_get_object_by_name($form_loginname) ||
				user_get_object_by_email($form_loginname) ;
			if (!$u) {
				$warning_msg .= '<br /><p>'. _('Your account does not exist.').'</p>';
			}
		}
	} else if ($userstatus == "P") {
		$warning_msg .= '<br />'. sprintf(_('<p>Your account is currently pending your email confirmation.<br/>Visiting the link sent to you in this email will activate your account.<br/>If you need this email resent, please click below and a confirmation email will be sent to the email address you provided in registration.</p><a href="%1$s">[Resend Confirmation Email]</a><br><hr>'), util_make_url ("/account/pending-resend.php?form_user=".htmlspecialchars($form_loginname)));
	} else {
		if ($userstatus == "D") {
			$error_msg = '<br />'.sprintf(_('<p>Your %1$s account has been removed by %1$s staff. This may occur for two reasons, either 1) you requested that your account be removed; or 2) some action has been performed using your account which has been seen as objectionable (i.e. you have breached the terms of service for use of your account) and your account has been revoked for administrative reasons. Should you have questions or concerns regarding this matter, please log a <a href="%2$s">support request</a>.</p><p>Thank you, <br><br>%1$s Staff</p>'), forge_get_config('forge_name'), util_make_url("/support/?group_id=1"));
		}
	}
}

$HTML->header(array('title'=>'Login'));

// Otherwise, display the login form again
display_login_form($return_to, $triggered);

$HTML->footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
