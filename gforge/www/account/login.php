<?php
/**
  * SourceForge login page
  *
  * This is main SF login page. It takes care of different account states
  * (by disallowing logging in with non-active account, with appropriate
  * notice).
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  */

Header( "Expires: Wed, 11 Nov 1998 11:11:11 GMT"); 
Header( "Cache-Control: no-cache"); 
Header( "Cache-Control: must-revalidate"); 

require_once('pre.php');

//
//	Validate return_to
//
if ($return_to) {
	$tmpreturn=explode('?',$return_to);
	if (!@is_file($sys_urlroot.$tmpreturn[0]) && !@is_dir($sys_urlroot.$tmpreturn[0]) && !(strpos($tmpreturn[0],'projects') == 1)) {
		$return_to='';
	}
}

if ($sys_use_ssl && !session_issecure()) {
	//force use of SSL for login
	header('Location: https://'.$HTTP_HOST.$REQUEST_URI);
}

// Decide login button based on session.
if (session_issecure()) {
    $login_button = $Language->getText('account_login', 'login_ssl');
} else {
    $login_button = $Language->getText('account_login', 'login'); 
}

// ###### first check for valid login, if so, redirect

if ($login) {
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

$HTML->header(array('title'=>'Login','pagename'=>'account_login'));

if ($login && !$success) {
		
	// Account Pending
	if ($userstatus == "P") {
		echo $Language->getText('account_login', 'pending_account', array($form_loginname));
	} else {
		echo '<h2 style="color:red">'. $feedback .'</h2>';
		if ($userstatus == "D") {
			echo $Language->getText('account_login', 'deleted_account', $GLOBALS[sys_name]);
		}
	} //end else

}

?>
	
<p>
<span style="color:red"><strong><?php echo $Language->getText('account_login', 'cookiewarn'); ?></strong></span>
</p>
<form action="<?php echo $PHP_SELF; ?>" method="post">
<input type="hidden" name="return_to" value="<?php echo $return_to; ?>" />
<p>
<?php echo $Language->getText('account_login', 'loginname'); ?>
<br /><input type="text" name="form_loginname" value="<?php echo $form_loginname; ?>" />
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
<p><a href="pending-resend.php"><?php echo $Language->getText('account_login','resend_pending'); ?></a>

<?php

$HTML->footer(array());

?>
