<?php
//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net
//
// $Id$

require "pre.php";    

function account_verify($password,$user_name,$confirm_hash) {
	global $feedback;

	if (!$user_name) {
		$feedback .= ' Must Enter a User Name ';
		return false;
	}

	// first check just confirmation hash
	$res = db_query("SELECT confirm_hash,status FROM users 
		WHERE user_name='" . strtolower($user_name) . "'");

	if (db_numrows($res) < 1) {
		$feedback .= ' Invalid username ';
		return false;
	}
	$usr = db_fetch_array($res);

	if (strcmp($confirm_hash,$usr['confirm_hash'])) {
		$feedback .= ' Invalid confirmation hash ';
		return false;
	}

	// then check valid login	
	return (session_login_valid(strtolower($user_name),$password,1));
}

// ###### first check for valid login, if so, redirect

if ($Login){
	$success=account_verify($form_pw,$form_loginname,$confirm_hash);
	if ($success) {
		$res = db_query("UPDATE users SET status='A' 
			WHERE user_name='" . strtolower($form_loginname) . "'");
		session_redirect("/account/first.php");
	} else {
		exit_error('ERROR',$feedback);
	}
}

$HTML->header(array('title'=>'Login'));

?>
<p><b>SourceForge Account Verification</b>
<P>In order to complete your registration, login now. Your account will
then be activated for normal logins.
<?php 
if ($GLOBALS['error_msg']) {
	print '<P><FONT color="#FF0000">'.$GLOBALS['error_msg'].'</FONT>';
}
?>
<form action="verify.php" method="post">
<p>Login Name:
<br><input type="text" name="form_loginname">
<p>Password:
<br><input type="password" name="form_pw">
<INPUT type="hidden" name="confirm_hash" value="<?php print $confirm_hash; ?>">
<p><input type="submit" name="Login" value="Login">
</form>

<?php
$HTML->footer(array());

?>
