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
<font color="red"><B><?php echo $Language->getText('account_login', 'cookiewarn'); ?></B></font>
<P>
<form action="https://<?php echo $HTTP_HOST; ?>/account/login.php" method="post">
<INPUT TYPE="HIDDEN" NAME="return_to" VALUE="<?php echo $return_to; ?>">
<p>
<?php echo $Language->getText('account_login', 'loginname'); ?>
<br><input type="text" name="form_loginname" VALUE="<?php echo $form_loginname; ?>">
<p>
<?php echo $Language->getText('account_login', 'passwd'); ?>
<br><input type="password" name="form_pw">
<P>
<INPUT TYPE="CHECKBOX" NAME="stay_in_ssl" VALUE="1" <?php echo ((browser_is_ie() && browser_get_version() < '5.5')?'':'CHECKED') ?>> <?php echo $Language->getText('account_login', 'usessl'); ?>
<?php echo $Language->getText('account_login', 'sslnotice'); ?>
<input type="submit" name="login" value="<?php echo $Language->getText('account_login', 'login'); ?>">
</form>
<P>
<A href="lostpw.php"><?php echo $Language->getText('account_login', 'lostpw'); ?></A>
<P>
<A HREF="register.php"><?php echo $Language->getText('account_login', 'newaccount'); ?></A>

<?php

$HTML->footer(array());

?>
