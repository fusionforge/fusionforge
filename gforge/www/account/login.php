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

/*

if (!session_issecure()) {
	//force use of SSL for login
	header('Location: https://'.$HTTP_HOST.'/account/login.php');
}

*/

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
		
	if ($feedback == "Account Pending") {

		echo $Language->getText('account_login', 'pending_account', array($form_loginname));

	} else {
		
		echo '<h2><FONT COLOR="RED">'. $feedback .'</FONT></H2>';
		if (stristr($feedback, "deleted")) {
			echo $Language->getText('account_login', 'deleted_account', $GLOBALS[sys_name]);
		}
	} //end else

}

?>
	
<p>
<font color="red"><B><?php echo $Language->getText('account_login', 'cookiewarn'); ?></B></font>
<P>
<form action="<?php echo $PHP_SELF; ?>" method="post">
<INPUT TYPE="HIDDEN" NAME="return_to" VALUE="<?php echo $return_to; ?>">
<p>
<?php echo $Language->getText('account_login', 'loginname'); ?>
<br><input type="text" name="form_loginname" VALUE="<?php echo $form_loginname; ?>">
<p>
<?php echo $Language->getText('account_login', 'passwd'); ?>
<br><input type="password" name="form_pw">
<P>
</FONT></B>
<input type="submit" name="login" value="<?php echo $Language->getText('account_login', 'login'); ?>">
</form>
<P>
<A href="lostpw.php"><?php echo $Language->getText('account_login', 'lostpw'); ?></A>
<P>
<A HREF="register.php"><?php echo $Language->getText('account_login', 'newaccount'); ?></A>

<?php

$HTML->footer(array());

?>
