<?php
/**
  *
  * Register new acoount page
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */

require_once('pre.php');    
require_once('common/include/account.php');
require_once('common/include/timezones.php');

if ($submit) {
	/*

		Adding call to library rather than
		logic that used to be coded in this page

	*/
	$new_user = new User();
	$register = $new_user->create($unix_name,$realname,$password1,$password2,
		$email,$mail_site,$mail_va,$language_id,$timezone,$jabber_address,$jabber_only);
	if ($register) {
		echo $HTML->header(array('title'=>'Register Confirmation','pagename'=>'account_register'));

		echo $Language->getText('account_register','congrat', $GLOBALS[sys_name]);
		echo $HTML->footer(array());
		exit;
	} else {
		$feedback = $new_user->getErrorMessage();
	}
}


$HTML->header(array('title'=>'Gforge: Register','pagename'=>'account_register'));


?>

<?php 
if ($feedback) {
	print "<p><FONT color=#FF0000>$feedback $register_error</FONT>";
} 
?>

<form action="<?php echo $PHP_SELF; ?>" method="post">
<p>
<?php echo $Language->getText('account_register','loginname'); ?><br>
<input type="text" name="unix_name" value="<?php print($unix_name); ?>">
<p>
<?php echo $Language->getText('account_register','password'); ?><br>
<input type="password" name="password1">
<p>
<?php echo $Language->getText('account_register','password2'); ?><br>
<input type="password" name="password2">
<P>
<?php echo $Language->getText('account_register','realname'); ?><br>
<INPUT size=30 type="text" name="realname" value="<?php print($realname); ?>">
<P>
<?php echo $Language->getText('account_register','language'); ?><br>
<?php echo html_get_language_popup ($Language,'language_id',1); ?>
<P>
<?php echo $Language->getText('account_register','timezone'); ?><br>
<?php echo html_get_timezone_popup('timezone', 'GMT'); ?>
<P>
@<?php echo $Language->getText('account_register','emailaddr', $GLOBALS[sys_users_host]); ?>
<BR><INPUT size=30 type="text" name="email" value="<?php print($email); ?>">
<p>
<?php
if ($sys_use_jabber) {
	echo $Language->getText('account_register','jabberaddr').'<BR>
	<INPUT size=30 type="text" name="jabber_address" value="'. $jabber_address .'"><BR>
    <INPUT type="checkbox" name="jabber_only" value="1">
    '.$Language->getText('account_register','jabberonly').'.';
}
?>
<P>
<INPUT type="checkbox" name="mail_site" value="1" checked>
<?php echo $Language->getText('account_register','siteupdate'); ?>
<P>
<INPUT type="checkbox" name="mail_va" value="1">
<?php echo $Language->getText('account_register','communitymail'); ?>
<p>
<?php echo $Language->getText('account_register','mandatory'); ?>
</p>
<p>
<input type="submit" name="submit" value="<?php echo $Language->getText('account_register','register'); ?>">
</form>

<?php

$HTML->footer(array());

?>
