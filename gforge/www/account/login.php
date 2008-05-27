<?php
/**
 * GForge login page
 *
 * This is main login page. It takes care of different account states
 * (by disallowing logging in with non-active account, with appropriate
 * notice).
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

Header( "Expires: Wed, 11 Nov 1998 11:11:11 GMT"); 
Header( "Cache-Control: no-cache"); 
Header( "Cache-Control: must-revalidate"); 

require_once('../env.inc.php');
require_once $gfwww.'include/pre.php';

$return_to = getStringFromRequest('return_to');
$login = getStringFromRequest('login');
$form_loginname = getStringFromRequest('form_loginname');
$form_pw = getStringFromRequest('form_pw');

//
//	Validate return_to
//
if ($return_to) {
	$tmpreturn=explode('?',$return_to);
	if (!@is_file($sys_urlroot.$tmpreturn[0]) && !@is_dir($sys_urlroot.$tmpreturn[0]) && !(strpos($tmpreturn[0],'projects') == 1) && !(strpos($tmpreturn[0],'mediawiki') == 1)) {
		$return_to='';
	}
}

if ($sys_use_ssl && !session_issecure()) {
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
			header ("Location: " . $return_to);
			exit;
		} else {
			header ("Location: ".util_make_url ("/my/"));
			exit;
		}
	}
}

if (isset($session_hash)) {
	//nuke their old session
	session_logout();
}

//echo "\n\n$session_hash";
//echo "\n\nlogged in: ".session_loggedin();

$HTML->header(array('title'=>'Login'));

if ($login && !$success) {
	form_release_key(getStringFromRequest('form_key'));	
	// Account Pending
	if ($userstatus == "P") {
		$feedback = sprintf(_('<P>Your account is currently pending your email confirmation.		Visiting the link sent to you in this email will activate your account.		<P>If you need this email resent, please click below and a confirmation		email will be sent to the email address you provided in registration.		<P><A href="%1$s">[Resend Confirmation Email]</A>		<br><hr>		<p>'), util_make_url ("pending-resend.php?form_user=".htmlspecialchars($form_loginname)));
	} else {
		if ($userstatus == "D") {
			$feedback .= '<br />'.sprintf(_('<p>Your %1$s account has been removed by %1$s staff. This may occur for two reasons, either 1) you requested that your account be removed; or 2) some action has been performed using your account which has been seen as objectionable (i.e. you have breached the terms of service for use of your account) and your account has been revoked for administrative reasons. Should you have questions or concerns regarding this matter, please log a <a href="%2$s">support request</a>.</p><p>Thank you, <br><br>%1$s Staff</p>'), $GLOBALS['sys_name'], util_make_url ("/support/?group_id=1"));
		}
	}
	html_feedback_top($feedback);
}

?>
	
<p>
<span class="error"><?php echo _('Cookies must be enabled past this point.'); ?></span>
</p>
<form action="<?php echo getStringFromServer('PHP_SELF'); ?>" method="post">
<input type="hidden" name="form_key" value="<?php echo form_generate_key(); ?>"/>
<input type="hidden" name="return_to" value="<?php echo htmlspecialchars(stripslashes($return_to)); ?>" />
<p>
<?php echo _('Login name:'); ?>
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
<p><a href="register.php"><?php echo _('[New Account]'); ?></a></p>
<p><a href="pending-resend.php"><?php echo _('[Resend confirmation email to a pending account]'); ?></a></p>

<?php

$HTML->footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
