<?php
/**
  *
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
  *
  */

Header( "Expires: Wed, 11 Nov 1998 11:11:11 GMT"); 
Header( "Cache-Control: no-cache"); 
Header( "Cache-Control: must-revalidate"); 

require_once('pre.php');

$CACHE_TIME = -1;

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

			// check for external redirection 
			//
			if (substr($return_to, 0, 4) == "http") {
				header ("Location: " . $return_to);
				exit;
			} else {
				header ("Location: http".$ssl_."://". $HTTP_HOST . $return_to);
				exit;
			}

		} else {
			header ("Location: http".$ssl_."://". $HTTP_HOST ."/my/");
			exit;
		}
	}
}

if ($session_hash) {
	//nuke their old session
	session_logout();
}

//echo "\n\n$session_hash";
//echo "\n\nlogged in: ".user_isloggedin();

$HTML->header(array('title'=>'Login','pagename'=>'account_login'));

if ($login && !$success) {
		
	if ($feedback == "Account Pending") {

		echo $Language->getText('account_login', 'pending_account', array($form_loginname));

	} else {
		
		echo '<h2><FONT COLOR="RED">'. $feedback .'</FONT></H2>';
		if (stristr($feedback, "deleted")) {
			echo $Language->getText('account_login', 'deleted_account');
		}
	} //end else

}

if (browser_is_windows() && browser_is_ie() && browser_get_version() < '5.6') {
	echo '<H2><FONT COLOR="RED">Internet Explorer users need to
	upgrade to IE 5.01 or higher, preferably with 128-bit SSL or use Netscape 4.7 or higher</FONT></H2>';
}

if (browser_is_ie() && browser_is_mac()) {
	echo '<H2><FONT COLOR="RED">Internet Explorer on the Macintosh 
	is not supported currently. Use Netscape 4.7 or higher</FONT></H2>';
}


?>
	
<p>
<font color="red"><B>Cookies must be enabled past this point.</B></font>
<P>
<form action="https://<?php echo $HTTP_HOST; ?>/account/login.php" method="post">
<INPUT TYPE="HIDDEN" NAME="return_to" VALUE="<?php echo $return_to; ?>">
<p>
Login Name:
<br><input type="text" name="form_loginname" VALUE="<?php echo $form_loginname; ?>">
<p>
Password:
<br><input type="password" name="form_pw">
<P>
<INPUT TYPE="CHECKBOX" NAME="stay_in_ssl" VALUE="1" <?php echo ((browser_is_ie() && browser_get_version() < '5.5')?'':'CHECKED') ?>> Stay in SSL mode after login
<p>
<B><FONT COLOR="RED">You will be connected with an SSL server when you submit this form and your password will not be visible to other users.
</FONT></B> 
<small style="font-size: x-small">
(If you wonder why very this page is not loaded via SSL, please read
next paragraph. Thank you.)
</small>

<P>
<B>Internet Explorer</B> users will have intermittent SSL problems, so they should leave SSL 
after login. Netscape users should stay in SSL mode permanently for maximum security.
Visit <A HREF="http://www.microsoft.com/">Microsoft</A> for more information about this known problem.
<P>
<input type="submit" name="login" value="Login With SSL">
</form>
<P>
<A href="lostpw.php">[Lost your password?]</A>
<P>
<A HREF="register.php">[New Account]</A>

<?php

$HTML->footer(array());

?>
