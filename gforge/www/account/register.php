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
		$email,$mail_site,$mail_va,$language_id,$timezone);
	if ($register) {
		echo $HTML->header(array('title'=>'Register Confirmation','pagename'=>'account_register'));
		?>
		<p>
		Congratulations. You have registered on <?php echo $GLOBALS['sys_name']; ?>.
		<p> 
		You are now being sent a confirmation email to verify your email 
		address. Visiting the link sent to you in this email will activate
		your account.
		<?php

		echo $HTML->footer(array());
		exit;
	} else {
		$feedback = $new_user->getErrorMessage();
	}
}


$HTML->header(array('title'=>'Register','pagename'=>'account_register'));

if (browser_is_windows() && browser_is_ie() && browser_get_version() < '5.1') {
	echo '<H2><FONT COLOR="RED">Internet Explorer users need to 
	upgrade to IE 5.01 or higher, preferably with 128-bit SSL or use Netscape 4.7 or higher</FONT></H2>';	
}
if (browser_is_ie() && browser_is_mac()) {
	echo '<H2><FONT COLOR="RED">Internet Explorer on the Macintosh
	is not supported currently. Use Netscape 4.7 or higher</FONT></H2>';
}


?>

<?php 
if ($feedback) {
	print "<p><FONT color=#FF0000>$feedback $register_error</FONT>";
} 
?>

<form action="https://<?php echo $HTTP_HOST.$PHP_SELF; ?>" method="post">
<p>
Login Name (do not use uppercase letters) *:<br>
<input type="text" name="unix_name" value="<?php print($unix_name); ?>">
<p>
Password (min. 6 chars) *:<br>
<input type="password" name="password1">
<p>
Password (repeat) *:<br>
<input type="password" name="password2">
<P>
Full/Real Name *:<BR>
<INPUT size=30 type="text" name="realname" value="<?php print($realname); ?>">
<P>
Language Choice:<BR>
<?php echo html_get_language_popup ($Language,'language_id',1); ?>
<P>
Timezone:<BR>
<?php echo html_get_timezone_popup('timezone', 'GMT'); ?>
<P>
Email Address *:
<BR><I>This email address will be verified before account activation.
It will not be displayed on the site. You will receive a mail forward
account at &lt;loginname@<?php echo $GLOBALS['sys_users_host']; ?>&gt; that will forward to
this address.</I>
<BR><INPUT size=30 type="text" name="email" value="<?php print($email); ?>">
<P>
<INPUT type="checkbox" name="mail_site" value="1" checked>
Receive Email about Site Updates <I>(Very low traffic and includes
security notices. Highly Recommended.)</I>
<P>
<INPUT type="checkbox" name="mail_va" value="1">
Receive additional community mailings. <I>(Low traffic.)</I>
<p>
Fields marked with * are mandatory.
</p>
<p>
<input type="submit" name="submit" value="Register">
</form>

<?php

$HTML->footer(array());

?>
