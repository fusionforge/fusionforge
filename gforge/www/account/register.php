<?php
//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net
//
// $Id$

require ('pre.php');    
require ('account.php');
require ('timezones.php');

if ($submit) {
	/*

		Adding call to library rather than
		logic that used to be coded in this page

	*/
	$register=account_register_new(strtolower($unix_name),$realname,$password1,$password2,$email,$language,$timezone,$mail_site,$mail_va,$language_id,$timezone);
	if ($register) {
		echo $HTML->header(array('title'=>'Register Confirmation'));
		?>
		<p>
		<b>SourceForge: New Account Registration Confirmation</b>
		<?php
		echo $Language->ACCOUNTREGISTER_congrat;

		echo $HTML->footer(array());
		exit;
	}
}


$HTML->header(array('title'=>'SourceForge: Register'));

if (browser_is_windows() && browser_is_ie() && browser_get_version() < '5.1') {
	echo $Language->IEWARN;
}
if (browser_is_ie() && browser_is_mac()) {
	echo $Language->MACWARN;
}


?>
<p><b><?php echo $Language->ACCOUNTREGISTER_title; ?></b>
<?php 
if ($feedback) {
	print "<p><FONT color=#FF0000>$feedback $register_error</FONT>";
} 
?>
<form action="https://<?php echo $HTTP_HOST.$PHP_SELF; ?>" method="post">
<p>
<?php echo $Language->ACCOUNTREGISTER_loginname; ?><br>
<input type="text" name="unix_name" value="<?php print($unix_name); ?>">
<p>
<?php echo $Language->ACCOUNTREGISTER_password; ?><br>
<input type="password" name="password1" value="<?php print($password1); ?>">
<p>
<?php echo $Language->ACCOUNTREGISTER_password2; ?><br>
<input type="password" name="password2" value="<?php print($password2); ?>">
<P>
<?php echo $Language->ACCOUNTREGISTER_realname; ?><br>
<INPUT size=30 type="text" name="realname" value="<?php print($realname); ?>">
<P>
<?php echo $Language->ACCOUNTREGISTER_language; ?><br>
<?php echo html_get_language_popup ($Language,'language_id',1); ?>
<P>
<?php echo $Language->ACCOUNTREGISTER_timezone; ?><br>
<?php echo html_get_timezone_popup (); ?>
<P>
<?php echo $Language->ACCOUNTREGISTER_emailaddr.$GLOBALS[sys_default_domain].$Language->ACCOUNTREGISTER_emailaddr2; ?>
<BR><INPUT size=30 type="text" name="email" value="<?php print($email); ?>">
<P>
<INPUT type="checkbox" name="form_mail_site" value="1" checked>
<?php echo $Language->ACCOUNTREGISTER_siteupdate; ?>
<P>
<INPUT type="checkbox" name="form_mail_va" value="1">
<?php echo $Language->ACCOUNTREGISTER_communitymail; ?>
<p>
<input type="submit" name="submit" value="<?php echo $Language->ACCOUNTREGISTER_register; ?>">
</form>

<?php

$HTML->footer(array());

?>
