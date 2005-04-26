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

require_once('pre.php');    
require_once('common/include/account.php');
require_once('common/include/timezones.php');

if ($sys_user_reg_restricted) {
	session_require(array('group'=>'1','admin_flags'=>'A'));
}

if (!$theme_id) {
	$theme_id=$HTML->getThemeIdFromName($sys_theme);
}

if ($submit) {
	/*

		Adding call to library rather than
		logic that used to be coded in this page

	*/
	$new_user = new User();
	$register = $new_user->create($unix_name,$firstname,$lastname,$password1,$password2,
		$email,$mail_site,$mail_va,$language_id,$timezone,$jabber_address,$jabber_only,$theme_id,'',
		$address,$address2,$phone,$fax,$title,$ccode);
	if ($register) {
		echo $HTML->header(array('title'=>'Register Confirmation'));

		echo $Language->getText('account_register','congrat', $sys_name);
		echo $HTML->footer(array());
		exit;
	} else {
		$feedback = $new_user->getErrorMessage();
	}
}

$HTML->header(array('title'=>'User Account Registration'));

if ($feedback) {
	print "<p><font color=\"#FF0000\">$feedback $register_error</font>";
} 
if (!isset($timezone) || empty($timezone)) {
	$timezone = (isset($sys_default_timezone) ? $sys_default_timezone : 'GMT');
}
if (!isset($ccode) || empty($ccode)) {
	$ccode = $sys_default_country_code;
}
?>

<form action="<?php echo $PHP_SELF; ?>" method="post">
<p>
<?php echo $Language->getText('account_register','loginname'); echo utils_requiredField(); ?><br />
<input type="text" name="unix_name" value="<?php print($unix_name); ?>" />
</p>
<p>
<?php echo $Language->getText('account_register','password'); echo utils_requiredField(); ?><br />
<input type="password" name="password1" />
</p>
<p>
<?php echo $Language->getText('account_register','password2'); echo utils_requiredField(); ?><br />
<input type="password" name="password2" />
</p
><p>
<?php echo $Language->getText('account_options', 'title2'); ?><br />
<input type="text" name="title" value="<?php echo $title; ?>" size="10" />
</p>
<p>
<?php echo $Language->getText('account_register','firstname'); echo utils_requiredField(); ?><br />
<input size="30" type="text" name="firstname" value="<?php print($firstname); ?>" />
</p>
<p>
<?php echo $Language->getText('account_register','lastname'); echo utils_requiredField(); ?><br />
<input size="30" type="text" name="lastname" value="<?php print($lastname); ?>" />
</p>
<p>
<?php echo $Language->getText('account_register','language'); ?><br />
<?php echo html_get_language_popup ($Language,'language_id',$Language->getLanguageId()); ?>
</p>
<p>
<?php echo $Language->getText('account_register','timezone'); ?><br />
<?php echo html_get_timezone_popup('timezone', $timezone); ?>
</p>
<p>
<?php echo $Language->getText('account_register','theme'); ?><br />
<?php echo html_get_theme_popup('theme_id', $theme_id); ?>
</p>
<p>
<?php echo $Language->getText('account_register','ccode'); ?><br />
<?php echo html_get_ccode_popup('ccode', $ccode); ?>
</p>
<p>
@<?php echo $Language->getText('account_register','emailaddr', $GLOBALS['sys_users_host']); ?>
<br /><input size="30" type="text" name="email" value="<?php print($email); ?>" />
</p>
<p>
<?php
if ($sys_use_jabber) {
	echo $Language->getText('account_register','jabberaddr').'<br />
	<input size="30" type="text" name="jabber_address" value="'. $jabber_address .'" /><br />
	<input type="checkbox" name="jabber_only" value="1" />
	'.$Language->getText('account_register','jabberonly').'.';
}
?>
</p>
<p>
<?php echo $Language->getText('account_options', 'address'); ?><br />
<input type="text" name="address" value="<?php echo $address; ?>" size="80" />
</p>
<p>
<?php echo $Language->getText('account_options', 'address'); ?><br />
<input type="text" name="address2" value="<?php echo $address2; ?>" size="80" />
</p>
<p>
<?php echo $Language->getText('account_options', 'phone'); ?><br />
<input type="text" name="phone" value="<?php echo $phone; ?>" size="20" />
</p>
<p>
<?php echo $Language->getText('account_options', 'fax'); ?><br />
<input type="text" name="fax" value="<?php echo $fax; ?>" size="20" />
</p>
<p>
<input type="checkbox" name="mail_site" value="1" checked="checked" />
<?php echo $Language->getText('account_register','siteupdate'); ?>
</p>
<p>
<input type="checkbox" name="mail_va" value="1" />
<?php echo $Language->getText('account_register','communitymail'); ?>
</p>
<p>
<?php echo $Language->getText('account_register','mandatory', utils_requiredField()); ?>
</p>
<p>
<input type="submit" name="submit" value="<?php echo $Language->getText('account_register','register'); ?>" />
</p>
</form>
<p><a href="pending-resend.php"><?php echo $Language->getText('account_register','resend_pending'); ?></a></p>

<?php $HTML->footer(array()); ?>
