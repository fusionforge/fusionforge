<?php
/**
 * User account main page - show settings with means to change them
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2010, Franck Villaume - Capgemini
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

require_once('../env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once $gfcommon.'include/timezones.php';

$feedback = htmlspecialchars(getStringFromRequest('feedback'));
$error_msg = htmlspecialchars(getStringFromRequest('error_msg'));

session_require_login();

// get global users vars
$u =& user_get_object(user_getid());
if (!$u || !is_object($u)) {
	exit_error(_('Could Not Get User'));
} elseif ($u->isError()) {
	exit_error($u->getErrorMessage(),'my');
}

if (getStringFromRequest('submit')) {
	if (!form_key_is_valid(getStringFromRequest('form_key'))) {
		exit_form_double_submit('my');
	}


	$firstname = getStringFromRequest('firstname');
	$lastname = getStringFromRequest('lastname');
	$language = getIntFromRequest('language');
	$timezone = getStringFromRequest('timezone');
	$theme_id = getIntFromRequest('theme_id');
	$ccode = getStringFromRequest('ccode');
	$address = getStringFromRequest('address');
	$address2 = getStringFromRequest('address2');
	$phone = getStringFromRequest('phone');
	$fax = getStringFromRequest('fax');
	$title = getStringFromRequest('title');
	$jabber_address = getStringFromRequest('jabber');
	$jabber_only = getStringFromRequest('jabber');
	$mail_site = getStringFromRequest('mail_site');
	$mail_va = getStringFromRequest('mail_va');
	$remember_user = getStringFromRequest('remember_user');
	$use_ratings = getStringFromRequest('use_ratings');

/*
//needs security audit
	if ($remember_user) {
		// set cookie, expire in 3 months
		setcookie("sf_user_hash",$u->getID().'_'.substr($u->getMD5Passwd(),0,16),time()+90*24*60*60,'/');
	} else {
		// remove cookie
		setcookie("sf_user_hash",'',0,'/');
	}
*/
	// Refresh page if language or theme changed
	$refresh = ($language != $u->getLanguage() || $theme_id != $u->getThemeID());

	if (!$u->update($firstname, $lastname, $language, $timezone, $mail_site, $mail_va, $use_ratings,
		$jabber_address,$jabber_only,$theme_id,$address,$address2,$phone,$fax,$title,$ccode)) {
		form_release_key(getStringFromRequest('form_key'));
		$error_msg = $u->getErrorMessage();
		$refresh_url = '/account/?error_msg='.urlencode($error_msg);
	} else {
		$feedback = _('Updated');
		$refresh_url = '/account/?feedback='.urlencode($feedback);
	}

	if ($refresh) {
		session_redirect($refresh_url);
	}
}

$hookParams['user']= user_get_object(user_getid());
if (getStringFromRequest('submit')) {//if this is set, then the user has issued an Update
	plugin_hook("userisactivecheckboxpost", $hookParams);
}

$title = _('Account Maintenance');
site_user_header(array('title'=>$title));
echo '<h1>' . $title . '</h1>';

echo '<form action="'.util_make_url('/account/').'" method="post">';
echo '<input type="hidden" name="form_key" value="'.form_generate_key().'"/>';
echo $HTML->boxTop(_('Account Maintenance'));

?>

<p> <?php echo _('Welcome'); ?> <strong><?php print $u->getRealName(); ?></strong>. </p>
<p>

<?php echo _('Account options:'); ?>
</p>
<ul>
	<li><?php echo util_make_link_u ($u->getUnixName(),$u->getId(),'<strong>'._('View My Profile').'</strong>'); ?></li>
<?php if(forge_get_config('use_people')) { ?>
	<li><?php echo util_make_link ('/people/editprofile.php','<strong>'._('Edit My Skills Profile').'</strong>'); ?></li>
<?php } ?>
</ul>
<?php echo $HTML->boxBottom(); ?>

&nbsp;<br />
<table width="100%" border="0">

<tr valign="top">
<td><?php echo _('Member since:'); ?> </td>
<td><strong><?php print date(_('Y-m-d H:i'),$u->getAddDate()); ?></strong></td>
</tr>
<tr valign="top">
<td><?php echo _('User ID:'); ?> </td>
<td><strong><?php print $u->getID(); ?></strong></td>
</tr>

<tr valign="top">
<td><?php echo _('Login name:'); ?> </td>
<td><strong><?php print $u->getUnixName(); ?></strong>
<br /><a href="change_pw.php">[<?php echo _('Change Password'); ?>]</a>
</td>
</tr>

<tr valign="top">
<td><?php echo _('First Name:'); ?></td>
<td>
<input type="text" name="firstname" value="<?php print $u->getFirstName(); ?>" />
</td>
</tr>

<tr valign="top">
<td><?php echo _('Last Name:'); ?></td>
<td>
<input type="text" name="lastname" value="<?php print $u->getLastName(); ?>" />
</td>
</tr>

<tr valign="top">
<td><?php echo _('Language:'); ?> </td>
<td><?php echo html_get_language_popup ('language',$u->getLanguage()); ?>
</td>
</tr>

<tr valign="top">
<td><?php echo _('Timezone:'); ?> </td>
<td><?php echo html_get_timezone_popup('timezone', $u->getTimeZone()); ?>
</td>
</tr>

<tr valign="top">
<td><?php echo _('Theme:'); ?> </td>
<td><?php echo html_get_theme_popup('theme_id', $u->getThemeID()); ?>
</td>
</tr>

<tr valign="top">
<td><?php echo _('Country:'); ?> </td>
<td><?php echo html_get_ccode_popup('ccode', $u->getCountryCode()); ?>
</td>
</tr>

<tr valign="top">
<td><?php echo _('Email Addr:'); ?> </td>
<td><strong><?php print $u->getEmail(); ?></strong>
<br /><a href="change_email.php">[<?php echo _('Change Email Addr'); ?>]</a>
</td>
</tr>

<tr valign="top">
<td><?php echo _('Address:'); ?></td>
<td>
<input type="text" name="address" value="<?php echo $u->getAddress(); ?>" size="80"/>
</td>
</tr>

<tr valign="top">
<td><?php echo _('Address:'); ?></td>
<td>
<input type="text" name="address2" value="<?php echo $u->getAddress2(); ?>" size="80"/>
</td>
</tr>

<tr valign="top">
<td><?php echo _('Phone:'); ?></td>
<td>
<input type="text" name="phone" value="<?php echo $u->getPhone(); ?>" size="20"/>
</td>
</tr>

<tr valign="top">
<td><?php echo _('FAX:'); ?></td>
<td>
<input type="text" name="fax" value="<?php echo $u->getFax(); ?>" size="20"/>
</td>
</tr>

<tr valign="top">
<td><?php echo _('Title:'); ?></td>
<td>
<input type="text" name="title" value="<?php echo $u->getTitle(); ?>" size="10"/>
</td>
</tr>

<?php
if (forge_get_config('use_jabber')) {
	echo '<tr valign="top">
<td>'. _('Jabber Address:') .'</td>
<td>
	<input size=30 type="text" name="jabber_address" value="'. $u->getJabberAddress() .'" /><p />
	<input type="checkbox" name="jabber_only" value="1" '.(($u->getJabberOnly()) ? 'checked="CHECKED"' : '' ).' />
	'._('Send auto-generated notices only to my Jabber address').'.
</td></tr>';

}
?>


<tr>
<td colspan="2">
<?php
// ############################# Preferences
echo $HTML->boxTop(_('Preferences')); ?>

<input type="checkbox" name="mail_site" value="1"<?php
	if ($u->getMailingsPrefs('site')) print " checked=\"checked\""; ?> />
	<?php echo _('Receive Email about Site Updates <i>(Very low traffic and includes security notices. Highly Recommended.)</i>'); ?>

<p /><input type="checkbox"  name="mail_va" value="1"<?php
	if ($u->getMailingsPrefs('va')) print " checked=\"checked\""; ?> />
	<?php echo _('Receive additional community mailings. <i>(Low traffic.)</i>'); ?>
<?php /*
<p /><input type="checkbox"  name="remember_user" value="1"<?php
	if ($sf_user_hash) print " checked=\"checked\""; ?> />
<?php printf(_('"Remember me". <i>(Allows to access your <a href="%s">personal page</a> without being logged in. You will still need to login explicitly before making any changes.)</i>'),util_make_url ('/my/'));
*/ ?>

<p />
<?php if (forge_get_config('use_ratings')) { ?>
<input type="checkbox"  name="use_ratings" value="1"<?php
	if ($u->usesRatings()) print ' checked="checked"'; ?> />
		<?php printf(_('Participate in peer ratings. <i>(Allows you to rate other users using several criteria as well as to be rated by others. More information is available on your <a href="%s">user page</a> if you have chosen to participate in ratings.)</i>'),util_make_url_u ($u->getUnixName(),$u->getId()));
} ?>
</td></tr>
<?php 
plugin_hook("userisactivecheckbox", $hookParams);
?>
<tr><td>

<?php echo $HTML->boxBottom();

// ############################### Shell Account

if (($u->getUnixStatus() == 'A') && (forge_get_config('use_shell'))) {
	echo $HTML->boxTop(_('Shell Account Information')."");
	print '&nbsp;
<br />'._('Shell box').': <strong>'.$u->getUnixBox().'</strong>
<br />'._('SSH Shared Authorized Keys').': <strong>';
	// get shared key count from db
	$expl_keys = explode("\n", $u->getAuthorizedKeys());
	if ($expl_keys[0]) {
		print (sizeof($expl_keys));
	} else {
		print '0';
	}
	print '</strong>';
	print '<br />' . util_make_link ("account/editsshkeys.php",_('Edit Keys')) ;
	echo $HTML->boxBottom();
}
?>

</td>
</tr>

</table>

<p style="text-align: center;">
<input type="submit" name="submit" value="<?php echo _('Update'); ?>" />
<input type="reset" name="reset" value="<?php echo _('Reset Changes'); ?>" />
</p>
</form>

<?php
site_user_footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
