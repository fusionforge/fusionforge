<?php
/**
 * Register new acoount page
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

require_once('../env.inc.php');
require_once('pre.php');    
require_once('common/include/account.php');
require_once('common/include/timezones.php');

if ($sys_user_reg_restricted) {
	session_require(array('group'=>'1','admin_flags'=>'A'));
}

$unix_name = getStringFromRequest('unix_name');
$firstname = getStringFromRequest('firstname');
$lastname = getStringFromRequest('lastname');
$password1 = getStringFromRequest('password1');
$password2 = getStringFromRequest('password2');
$email = getStringFromRequest('email');
$mail_site = getStringFromRequest('mail_site');
$mail_va = getStringFromRequest('mail_va');
$language_id = getIntFromRequest('language_id');
$timezone = getStringFromRequest('timezone');
$jabber_address = getStringFromRequest('jabber_address');
$jabber_only = getStringFromRequest('jabber_only');
$theme_id = getIntFromRequest('theme_id');
$address = getStringFromRequest('address');
$address2 = getStringFromRequest('address2');
$phone = getStringFromRequest('phone');
$fax = getStringFromRequest('fax');
$title = getStringFromRequest('title');
$ccode = getStringFromRequest('ccode');

if ($sys_use_ssl && !session_issecure()) {
	//force use of SSL for login
	header('Location: https://'.$HTTP_HOST.$REQUEST_URI);
}

if (!$theme_id || !is_numeric($theme_id)) {
	$theme_id=$HTML->getThemeIdFromName($sys_theme);
}

if (getStringFromRequest('submit')) {
	/*
		Adding call to library rather than
		logic that used to be coded in this page
	*/
	if (!form_key_is_valid(getStringFromRequest('form_key'))) {
		exit_form_double_submit();
	}	
	$new_user = new User();
	$register = $new_user->create($unix_name,$firstname,$lastname,$password1,$password2,
		$email,$mail_site,$mail_va,$language_id,$timezone,$jabber_address,$jabber_only,$theme_id,'',
		$address,$address2,$phone,$fax,$title,$ccode);
	if ($register) {
		echo $HTML->header(array('title'=>'Register Confirmation'));

		printf(_('<p>Congratulations. You have registered on %1$s.  <p> You are now being sent a confirmation email to verify your email address. Visiting the link sent to you in this email will activate your account.'), $sys_name);
		echo $HTML->footer(array());
		exit;
	} else {
		$feedback = $new_user->getErrorMessage();
	}
}

$HTML->header(array('title'=>'User Account Registration'));

if (isset($feedback)) {
	print "<p><span class=\"error\">$feedback $register_error</span>";
} 
if (!isset($timezone) || empty($timezone) || !eregi('^[-a-z0-9_/]*?$', $timezone)) {
	$timezone = (isset($sys_default_timezone) ? $sys_default_timezone : 'GMT');
}
if (!isset($ccode) || empty($ccode) || !eregi('^[a-z][a-z]$', $ccode)) {
	$ccode = $sys_default_country_code;
}
?>

<form action="<?php echo getStringFromServer('PHP_SELF'); ?>" method="post">
<input type="hidden" name="form_key" value="<?php echo form_generate_key(); ?>"/>
<p>
<?php echo _('Login Name (do not use uppercase letters):'); echo utils_requiredField(); ?><br />
<input type="text" name="unix_name" value="<?php print(htmlspecialchars(stripslashes($unix_name))); ?>" />
</p>
<p>
<?php echo _('Password (min. 6 chars):'); echo utils_requiredField(); ?><br />
<input type="password" name="password1" />
</p>
<p>
<?php echo _('Password (repeat):'); echo utils_requiredField(); ?><br />
<input type="password" name="password2" />
</p
><p>
<?php echo _('Title:'); ?><br />
<input type="text" name="title" value="<?php echo htmlspecialchars(stripslashes($title)); ?>" size="10" />
</p>
<p>
<?php echo _('First Name:'); echo utils_requiredField(); ?><br />
<input size="30" type="text" name="firstname" value="<?php print(htmlspecialchars(stripslashes($firstname))); ?>" />
</p>
<p>
<?php echo _('Last Name:'); echo utils_requiredField(); ?><br />
<input size="30" type="text" name="lastname" value="<?php print(htmlspecialchars(stripslashes($lastname))); ?>" />
</p>
<p>
<?php echo _('Language Choice:'); ?><br />
<?php echo html_get_language_popup ($Language,'language_id',$Language->getLanguageId()); ?>
</p>
<p>
<?php echo _('Timezone:'); ?><br />
<?php echo html_get_timezone_popup('timezone', $timezone); ?>
</p>
<p>
<?php echo _('Theme'); ?><br />
<?php echo html_get_theme_popup('theme_id', $theme_id); ?>
</p>
<p>
<?php echo _('Country:'); ?><br />
<?php echo html_get_ccode_popup('ccode', $ccode); ?>
</p>
<p>
@<?php printf(_('Email Address:<span class="important">*</span><br /><em>This email address will be verified before account activation. You will receive a mail forward account at &lt;loginname@%1$s&gt; that will forward to this address.</em>'), $GLOBALS['sys_users_host']); ?>
<br /><input size="30" type="text" name="email" value="<?php print(htmlspecialchars(stripslashes($email))); ?>" />
</p>
<p>
<?php
if ($sys_use_jabber) {
	echo _('Jabber Address:').'<br />
	<input size="30" type="text" name="jabber_address" value="'. 
	htmlspecialchars(stripslashes($jabber_address)) .'" /><br />
	<input type="checkbox" name="jabber_only" value="1" />
	'._('Send auto-generated notices only to my Jabber address').'.';
}
?>
</p>
<p>
<?php echo _('Address:'); ?><br />
<input type="text" name="address" value="<?php echo htmlspecialchars(stripslashes($address)); ?>" size="80" />
</p>
<p>
<?php echo _('Address:'); ?><br />
<input type="text" name="address2" value="<?php echo htmlspecialchars(stripslashes($address2)); ?>" size="80" />
</p>
<p>
<?php echo _('Phone:'); ?><br />
<input type="text" name="phone" value="<?php echo htmlspecialchars(stripslashes($phone)); ?>" size="20" />
</p>
<p>
<?php echo _('FAX:'); ?><br />
<input type="text" name="fax" value="<?php echo htmlspecialchars(stripslashes($fax)); ?>" size="20" />
</p>
<p>
<input type="checkbox" name="mail_site" value="1" checked="checked" />
<?php echo _('Receive Email about Site Updates <i>(Very low traffic and includes security notices. Highly Recommended.)</i>'); ?>
</p>
<p>
<input type="checkbox" name="mail_va" value="1" />
<?php echo _('Receive additional community mailings. <i>(Low traffic.)</i>'); ?>
</p>
<p>
<?php echo $Language->getText('account_register','mandatory', utils_requiredField()); ?>
</p>
<p>
<input type="submit" name="submit" value="<?php echo _('Register'); ?>" />
</p>
</form>
<p><a href="pending-resend.php"><?php echo _('[Resend confirmation email to a pending account]'); ?></a></p>

<?php $HTML->footer(array()); ?>
