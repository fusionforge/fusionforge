<?php
//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net
//
// $Id$

Header( "Expires: Wed, 11 Nov 1998 11:11:11 GMT"); 
Header( "Cache-Control: no-cache"); 
Header( "Cache-Control: must-revalidate"); 

require ('pre.php');

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
		if ($stay_in_ssl) {
			$ssl_='s';
		} else {
			$ssl_='';
		}
		if ($return_to) {
			header ("Location: http".$ssl_."://". $HTTP_HOST . $return_to);
			exit;
		} else {
			header ("Location: http".$ssl_."://". $HTTP_HOST ."/my/");
			exit;
		}
	}
}

if ($session_hash) {
	//nuke their old session
	session_cookie('session_hash','');
	db_query("DELETE FROM session WHERE session_hash='$session_hash'");
}

//echo "\n\n$session_hash";
//echo "\n\nlogged in: ".user_isloggedin();

$HTML->header(array('title'=>'Login'));

if ($login && !$success) {
		
	if ($feedback == "Account Pending") {

		?>
		<P><B>Pending Account</B>

		<P>Your account is currently pending your email confirmation.
		Visiting the link sent to you in this email will activate your account.

		<P>If you need this email resent, please click below and a confirmation
		email will be sent to the email address you provided in registration.

		<P><A href="pending-resend.php?form_user=<?php print $form_loginname; ?>">[Resend Confirmation Email]</A>

		<br><hr>
		<p>


		<?php
	} else {
		
		echo '<h2><FONT COLOR="RED">'. $feedback .'</FONT></H2>';
	} //end else

}

if (browser_is_windows() && browser_is_ie() && browser_get_version() < '5.6') {
	echo $Language->IEWARN;
}

if (browser_is_ie() && browser_is_mac()) {
	echo $Language->MACWARN;
}


?>
	
<p>
<b><?php echo $Language->ACCOUNTLOGIN; ?></b>
<p>
<font color="red"><B><?php echo $Language->COOKIEWARN; ?></B></font>
<P>
<form action="https://<?php echo $HTTP_HOST; ?>/account/login.php" method="post">
<INPUT TYPE="HIDDEN" NAME="return_to" VALUE="<?php echo $return_to; ?>">
<p>
<?php echo $Language->LOGIN_NAME; ?>:
<br><input type="text" name="form_loginname" VALUE="<?php echo $form_loginname; ?>">
<p>
<?php echo $Language->PASSWORD; ?>:
<br><input type="password" name="form_pw">
<P>
<INPUT TYPE="CHECKBOX" NAME="stay_in_ssl" VALUE="1" <?php echo ((browser_is_ie() && browser_get_version() < '5.5')?'':'CHECKED') ?>><?php echo $Language->USESSL; ?> 
<?php echo $Language->SSLNOTICE; ?>
<input type="submit" name="login" value="<?php echo $Language->LOGIN; ?>">
</form>
<P>
<A href="lostpw.php">[<?php echo $Language->ACCOUNT_LOSTPW_title; ?>?]</A>
<P>
<A HREF="register.php">[<?php echo $Language->NEW_USER; ?>]</A>

<?php

$HTML->footer(array());

?>
