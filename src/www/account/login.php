<?php
/**
 * FusionForge login page
 *
 * This is main login page. It takes care of different account states
 * (by disallowing logging in with non-active account, with appropriate
 * notice).
 *
 * Copyright 1999-2001 (c) VA Linux Systems
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

Header( "Expires: Wed, 11 Nov 1998 11:11:11 GMT"); 
Header( "Cache-Control: no-cache"); 
Header( "Cache-Control: must-revalidate"); 

require_once('../env.inc.php');
require_once $gfcommon.'include/pre.php';

$return_to = getStringFromRequest('return_to');
$login = getStringFromRequest('login');
$form_loginname = trim(getStringFromRequest('form_loginname'));
$form_pw = getStringFromRequest('form_pw');
$feedback = htmlspecialchars(getStringFromRequest('feedback'));
$warning_msg = htmlspecialchars(getStringFromRequest('warning_msg'));
$error_msg = htmlspecialchars(getStringFromRequest('error_msg'));
$triggered = getIntFromRequest('triggered');

//
//	Validate return_to
//
if ($return_to) {
	$tmpreturn=explode('?',$return_to);
	$rtpath = $tmpreturn[0] ;

	if (@is_file(forge_get_config('url_root').$rtpath)
	    || @is_dir(forge_get_config('url_root').$rtpath)
	    || (strpos($rtpath,'/projects') == 0)
	    || (strpos($rtpath,'/plugins/mediawiki') == 0)) {
		$newrt = $return_to ;
	} else {
		$newrt = '/' ;
	}
	$return_to = $newrt ;
}

if (forge_get_config('use_ssl') && !session_issecure()) {
	//force use of SSL for login
	header('Location: https://'.getStringFromServer('HTTP_HOST').getStringFromServer('REQUEST_URI'));
}

// Decide login button based on session.
if (session_issecure()) {
    $login_button = _('Login with SSL');
} else {
    $login_button = _('Login'); 
}

// ###### first check for valid login, if so, redirect

if ($login) {
	if (!form_key_is_valid(getStringFromRequest('form_key'))) {
		exit_form_double_submit();
	}
	$success=session_login_valid(strtolower($form_loginname),$form_pw);
	if ($success) {
		/*
			You can now optionally stay in SSL mode
		*/
		if ($return_to) {
			header ("Location: " . util_make_url($return_to));
			exit;
		} else {
			header ("Location: " . util_make_url("/my"));
			exit;
		}
	}
}

if (isset($session_hash)) {
	//nuke their old session
	session_logout();
}

if ($login && !$success) {
	form_release_key(getStringFromRequest('form_key'));	
	// Account Pending
	if (!isset($userstatus)) {
		if (isset ($form_loginname)) {
			$u = user_get_object_by_name($form_loginname) || 
				user_get_object_by_email($form_loginname) ;
			if (!$u) {
				$warning_msg .= '<br /><p>'. _('Your account does not exist.').'</p>';
			}
		}
	} else if ($userstatus == "P") {
		$warning_msg .= '<br />'. sprintf(_('<p>Your account is currently pending your email confirmation.		Visiting the link sent to you in this email will activate your account.		<p>If you need this email resent, please click below and a confirmation		email will be sent to the email address you provided in registration.		<p><a href="%1$s">[Resend Confirmation Email]</a>		<br><hr>		<p>'), util_make_url ("/account/pending-resend.php?form_user=".htmlspecialchars($form_loginname)));
	} else {
		if ($userstatus == "D") {
			$error_msg .= '<br />'.sprintf(_('<p>Your %1$s account has been removed by %1$s staff. This may occur for two reasons, either 1) you requested that your account be removed; or 2) some action has been performed using your account which has been seen as objectionable (i.e. you have breached the terms of service for use of your account) and your account has been revoked for administrative reasons. Should you have questions or concerns regarding this matter, please log a <a href="%2$s">support request</a>.</p><p>Thank you, <br><br>%1$s Staff</p>'), forge_get_config ('forge_name'), util_make_url ("/support/?group_id=1"));
		}
	}
}

$HTML->header(array('title'=>'Login'));

if ($triggered) {
	echo '<div class="warning">' ;
	echo _('You\'ve been redirected to this login page because you have tried accessing a page that was not available to you as an anonymous user.');
	echo '</div> ' ;
}

echo '<p>';
echo _('Cookies must be enabled past this point.');

?>
</p>
<form action="<?php echo util_make_url('/account/login.php'); ?>" method="post">
<input type="hidden" name="form_key" value="<?php echo form_generate_key(); ?>"/>
<input type="hidden" name="return_to" value="<?php echo htmlspecialchars(stripslashes($return_to)); ?>" />
<p>
<?php if (forge_get_config('require_unique_email')) {
	echo _('Login name or email address');
} else {
	echo _('Login name:');
} ?>
<br /><input type="text" name="form_loginname" value="<?php echo htmlspecialchars(stripslashes($form_loginname)); ?>" />
</p>
<p>
<?php echo _('Password:'); ?>
<br /><input type="password" name="form_pw" />
</p>
<p>
<input type="submit" name="login" value="<?php echo $login_button; ?>" />
</p>
</form>
<p><a href="lostpw.php"><?php echo _('[Lost your password?]'); ?></a></p>
<?php
// hide "new account" item if restricted to admin
if (!forge_get_config ('user_registration_restricted')) {
	echo '<p><a href="register.php">'._('[New Account]').'</a></p>';
}
?>
<p><a href="pending-resend.php"><?php echo _('[Resend confirmation email to a pending account]'); ?></a></p>

<?php

$HTML->footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
