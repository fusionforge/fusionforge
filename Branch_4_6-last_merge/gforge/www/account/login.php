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

require_once('pre.php');

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
    $login_button = $Language->getText('account_login', 'login_ssl');
} else {
    $login_button = $Language->getText('account_login', 'login'); 
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
			header ("Location: /my/");
			exit;
		}
	}
}

if ($session_hash) {
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
		$feedback = $Language->getText('account_login', 'pending_account', array(htmlspecialchars($form_loginname)));
	} else {
		if ($userstatus == "D") {
			$feedback .= '<br />'.$Language->getText('account_login', 'deleted_account', $GLOBALS['sys_name']);
		}
	}
	html_feedback_top($feedback);
}

?>
	
<p>
<span class="error"><?php echo $Language->getText('account_login', 'cookiewarn'); ?></span>
</p>
<form action="<?php echo getStringFromServer('PHP_SELF'); ?>" method="post">
<input type="hidden" name="form_key" value="<?php echo form_generate_key(); ?>"/>
<input type="hidden" name="return_to" value="<?php echo htmlspecialchars(stripslashes($return_to)); ?>" />
<p>
<?php echo $Language->getText('account_login', 'loginname'); ?>
<br /><input type="text" name="form_loginname" value="<?php echo htmlspecialchars(stripslashes($form_loginname)); ?>" />
</p>
<p>
<?php echo $Language->getText('account_login', 'passwd'); ?>
<br /><input type="password" name="form_pw" />
</p>
<p>
<input type="submit" name="login" value="<?php echo $login_button; ?>" />
</p>
</form>
<p><a href="lostpw.php"><?php echo $Language->getText('account_login', 'lostpw'); ?></a></p>
<p><a href="register.php"><?php echo $Language->getText('account_login', 'newaccount'); ?></a></p>
<p><a href="pending-resend.php"><?php echo $Language->getText('account_login','resend_pending'); ?></a></p>

<?php

$HTML->footer(array());

?>
