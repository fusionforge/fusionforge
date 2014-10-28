<?php
/**
 * User account main page - show settings with means to change them
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2010-2011, Franck Villaume - Capgemini
 * Copyright 2011, Alain Peyrat - Alcatel-Lucent
 * Copyright 2012-2013, Franck Villaume - TrivialDev
 * Copyright 2013, French Ministry of National Education
 *
 * This file is part of FusionForge. FusionForge is free software;
 * you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or (at your option)
 * any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with FusionForge; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

require_once '../env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfcommon.'include/timezones.php';

session_require_login();

// get global users vars
$u = session_get_user();
if (!$u || !is_object($u)) {
	exit_error(_('Could Not Get User'));
} elseif ($u->isError()) {
	exit_error($u->getErrorMessage(),'my');
}

$action = getStringFromRequest('action');
switch ($action) {
	case "deletesshkey":
	case "addsshkey": {
		include ($gfcommon."account/actions/$action.php");
		break;
	}
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
	$mail_site = getStringFromRequest('mail_site');
	$mail_va = getStringFromRequest('mail_va');
	$remember_user = getStringFromRequest('remember_user');
	$use_ratings = getStringFromRequest('use_ratings');
	$use_tooltips = getIntFromRequest('use_tooltips');

	$check = true;
	if (!strlen(trim($firstname))) {
		$error_msg = _('You must supply a first name');
		$check = false;
	} elseif (!strlen(trim($lastname))) {
		$error_msg = _('You must supply a last name');
		$check = false;
	}

	if ($check) {
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
				'',0,$theme_id,$address,$address2,$phone,$fax,$title,$ccode,$use_tooltips)) {
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
}

$hookParams['user'] = user_get_object(user_getid());
if (getStringFromRequest('submit')) {//if this is set, then the user has issued an Update
	plugin_hook("userisactivecheckboxpost", $hookParams);
}

use_javascript('/js/sortable.js');
$title = _('My Account');
site_user_header(array('title'=>$title));

echo '<form action="'.util_make_url('/account/').'" method="post">';
echo '<input type="hidden" name="form_key" value="'.form_generate_key().'"/>';
echo $HTML->boxTop(_('Account options'));
?>

<p> <?php echo _('Welcome'); ?> <strong><?php print $u->getRealName(); ?></strong>. </p>
<p>

<?php echo _('Account options')._(':'); ?>
</p>
<ul>
	<li><?php echo util_make_link_u ($u->getUnixName(),$u->getId(),'<strong>'._('View My Profile').'</strong>'); ?></li>
<?php if(forge_get_config('use_people')) { ?>
	<li><?php echo util_make_link ('/people/editprofile.php','<strong>'._('Edit My Skills Profile').'</strong>'); ?></li>
<?php } ?>
</ul>

<table class="infotable">

<tr class="top">
<td><?php echo _('Member since')._(':'); ?> </td>
<td><?php print date(_('Y-m-d H:i'),$u->getAddDate()); ?></td>
</tr>
<tr class="top">
<td><?php echo _('User Id')._(':'); ?> </td>
<td><?php print $u->getID(); ?></td>
</tr>

<tr class="top">
<td><?php echo _('Login Name')._(':'); ?> </td>
<td><?php print $u->getUnixName(); ?>
<br /><a href="change_pw.php"><?php echo _('Change Password'); ?></a>
</td>
</tr>

<tr>
<td><?php echo _('First Name:').utils_requiredField(); ?></td>
<td>
    <label for="firstname">
        <input id="firstname" required="required" type="text" name="firstname" value="<?php print $u->getFirstName(); ?>"/>
    </label>
</td>
</tr>

<tr>
<td><?php echo _('Last Name:').utils_requiredField(); ?></td>
<td>
    <label for="lastname">
        <input id="lastname" required="required" type="text" name="lastname" value="<?php print $u->getLastName(); ?>"/>
    </label>
</td>
</tr>

<tr>
<td><?php echo _('Language')._(':'); ?> </td>
<td><?php echo html_get_language_popup ('language',$u->getLanguage()); ?>
</td>
</tr>

<tr>
<td><?php echo _('Timezone:'); ?> </td>
<td><?php echo html_get_timezone_popup('timezone', $u->getTimeZone()); ?>
</td>
</tr>

<tr>
<td><?php echo _('Theme')._(':'); ?> </td>
<td><?php echo html_get_theme_popup('theme_id', $u->getThemeID()); ?>
</td>
</tr>

<tr>
<td><?php echo _('Country:'); ?> </td>
<td><?php echo html_get_ccode_popup('ccode', $u->getCountryCode()); ?>
</td>
</tr>

<tr>
<td><?php echo _('Email Address') . _(': '); ?> </td>
<td><?php print $u->getEmail(); ?>
<br /><a href="change_email.php">[<?php echo _('Change Email Address'); ?>]</a>
</td>
</tr>

<tr>
<td><?php echo _('Address') . _(':'); ?></td>
<td>
    <label for="address">
        <input id="address" type="text" name="address" value="<?php echo $u->getAddress(); ?>" size="80"/>
    </label>
</td>
</tr>

<tr>
<td><?php echo _('Address (continued)') . _(':'); ?></td>
<td>
    <label for="address2">
        <input id="address2" type="text" name="address2" value="<?php echo $u->getAddress2(); ?>" size="80"/>
    </label>

</td>
</tr>

<tr>
<td><?php echo _('Phone')._(':'); ?></td>
<td>
    <label for="phone">
        <input id="phone" type="text" name="phone" value="<?php echo $u->getPhone(); ?>" size="20"/>
    </label>
</td>
</tr>

<tr>
<td><?php echo _('Fax')._(':'); ?></td>
<td>
    <label for="fax">
        <input id="fax" type="text" name="fax" value="<?php echo $u->getFax(); ?>" size="20"/>
    </label>
</td>
</tr>

<tr>
<td><?php echo _('Title')._(':'); ?></td>
<td>
    <label for="title">
        <input id="title" type="text" name="title" value="<?php echo $u->getTitle(); ?>" size="10"/>
    </label>
</td>
</tr>
</table>
<?php
echo $HTML->boxBottom();
// ############################# Preferences
echo $HTML->boxTop(_('Preferences'));
?>

<p>
    <label for="mail_site">
        <input id="mail_site" type="checkbox" name="mail_site" value="1"<?php
	if ($u->getMailingsPrefs('site')) print ' checked="checked"'; ?> />
    </label>
    <?php echo _('Receive Email about Site Updates <em>(Very low traffic and includes security notices. Highly Recommended.)</em>'); ?>
</p>

<p>
    <label for="mail_va">
        <input id="mail_va" type="checkbox" name="mail_va" value="1"<?php
    if ($u->getMailingsPrefs('va')) print ' checked="checked"'; ?> />
    </label>
    <?php echo _('Receive additional community mailings. <em>(Low traffic.)</em>'); ?>
</p>

<p>
<?php if (forge_get_config('use_ratings')) { ?>
    <label for="use_ratings">
        <input id="use_ratings" type="checkbox" name="use_ratings" value="1"<?php
        if ($u->usesRatings()) print ' checked="checked"'; ?> />
    </label>
    <?php printf(_('Participate in peer ratings. <em>(Allows you to rate other users using several criteria as well as to be rated by others. More information is available on your <a href="%s">user page</a> if you have chosen to participate in ratings.)</em>'),util_make_url_u ($u->getUnixName(),$u->getId()));
} ?>
</p>
<p>
    <label for="use_tooltips">
        <input id="use_tooltips" type="checkbox" name="use_tooltips" value="1"<?php
    if ($u->usesTooltips()) print ' checked="checked"'; ?> />
    </label>
    <?php echo _('Enable tooltips. Small help texts displayed on mouse over links, images.');
?>
</p>

<?php
// displays a "Use xxxx Plugin" checkbox
plugin_hook("userisactivecheckbox", $hookParams);

echo $HTML->boxBottom();

// ############################### Shell Account

if (forge_get_config('use_shell')) {
	echo $HTML->boxTop(_('Shell Account Information')."");
	if ($u->getUnixStatus() == 'A') {
		print '&nbsp;
	<br />'._('Shell box').': <strong>'.forge_get_config('shell_host').'</strong>
	<br />'._('SSH Shared Authorized Keys').': <strong>';
		global $HTML;
		$sshKeysArray = $u->getAuthorizedKeys();
		if (is_array($sshKeysArray) && count($sshKeysArray)) {
			$tabletop = array(_('Name'), _('Algorithm'), _('Fingerprint'), _('Uploaded'), _('Ready ?'));
			$classth = array('', '', '', '', '');
			echo $HTML->listTableTop($tabletop, false, 'sortable_sshkeys_listlinks', 'sortable', $classth);
			foreach($sshKeysArray as $sshKey) {
				echo '<tr>';
				echo '<td>'.$sshKey['name'].'</td>';
				echo '<td>'.$sshKey['algorithm'].'</td>';
				echo '<td>'.$sshKey['fingerprint'].'</td>';
				echo '<td>'.date(_('Y-m-d H:i'), $sshKey['upload']).'</td>';
				if ($sshKey['deploy']) {
					$image = html_image('docman/validate.png', 22, 22, array('alt'=>_('ssh key is deployed.'), 'class'=>'tabtitle', 'title'=>_('ssh key is deployed.')));
				} else {
					$image = html_image('waiting.png', 22, 22, array('alt'=>_('ssh key is not deployed yet.'), 'class'=>'tabtitle', 'title'=>_('ssh key is not deployed yet.')));
				}
				echo '<td>'.$image.'</td>';
				echo '</tr>';
			}
			echo $HTML->listTableBottom();
		} else {
			print '0';
		}
		print '</strong>';
		print '<br />' . util_make_link("account/editsshkeys.php",_('Edit Keys'));
	} else {
		echo '<div class="warning_msg">'._('Shell Account deactivated').'</div>';
	}
	echo $HTML->boxBottom();
}
?>

</td>
</tr>

</table>
<span><?php echo sprintf(_('%s Mandatory fields'), utils_requiredField())?></span>

<p class="align-center">
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
