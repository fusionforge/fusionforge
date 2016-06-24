<?php
/**
 * User account main page - show settings with means to change them
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2010-2011, Franck Villaume - Capgemini
 * Copyright 2011, Alain Peyrat - Alcatel-Lucent
 * Copyright 2012-2014, Franck Villaume - TrivialDev
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

global $HTML;
global $error_msg;
global $feedback;

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
		// Refresh page if language or theme changed
		$refresh = ($language != $u->getLanguage() || $theme_id != $u->getThemeID());

		if (!$u->update($firstname, $lastname, $language, $timezone, $mail_site, $mail_va, $use_ratings,
				'',0,$theme_id,$address,$address2,$phone,$fax,$title,$ccode,$use_tooltips)) {
			form_release_key(getStringFromRequest('form_key'));
			$error_msg = $u->getErrorMessage();
		} else {
			$feedback = _('Updated');
		}

		if ($refresh) {
			session_redirect('/account/');
		}
	}
}

$hookParams['user'] = user_get_object(user_getid());

if (getStringFromRequest('submit')) {//if this is set, then the user has issued an Update
	plugin_hook("userisactivecheckboxpost", $hookParams);
}

html_use_tablesorter();
$title = _('My Account');
site_user_header(array('title'=>$title));

echo $HTML->openForm(array('action' => util_make_uri('/account/'), 'method' => 'post'));
echo html_e('input', array('type' => 'hidden', 'name' => 'form_key', 'value' => form_generate_key()));
echo $HTML->boxTop(_('Account options'));
echo html_e('p', array(), _('Welcome').html_e('strong', array(), $u->getRealName()));
echo html_e('p', array(), _('Account options')._(':'));
echo html_ao('ul');
echo html_e('li', array(), util_make_link_u($u->getUnixName(),$u->getID(),html_e('strong', array(), _('View My Profile'))));
if(forge_get_config('use_people')) {
	echo html_e('li', array(), util_make_link('/people/editprofile.php', html_e('strong', array(), _('Edit My Skills Profile'))));
}
echo html_ac(html_ap() - 1);
echo $HTML->listTableTop(array(), array(), 'infotable');
?>

<tr class="top">
	<td><?php echo _('Member since')._(':'); ?></td>
	<td><?php print date(_('Y-m-d H:i'),$u->getAddDate()); ?></td>
</tr>
<tr class="top">
	<td><?php echo _('User Id')._(':'); ?></td>
	<td><?php print $u->getID(); ?></td>
</tr>

<tr class="top">
	<td><?php echo _('Login Name')._(':'); ?></td>
	<td><?php print $u->getUnixName(); ?>
	<br /><a href="change_pw.php"><?php echo _('Change Password'); ?></a>
	</td>
</tr>

<tr class="top">
	<td>
	<?php echo _('First Name').utils_requiredField()._(':'); ?>
	</td>
	<td>
		<label for="firstname">
			<input id="firstname" required="required" type="text" name="firstname" value="<?php print $u->getFirstName(); ?>"/>
		</label>
	</td>
</tr>

<tr class="top">
	<td>
	<?php echo _('Last Name').utils_requiredField()._(':'); ?>
	</td>
	<td>
		<label for="lastname">
		<input id="lastname" required="required" type="text" name="lastname" value="<?php print $u->getLastName(); ?>"/>
		</label>
	</td>
</tr>

<tr class="top">
	<td>
	<?php echo _('Language')._(':'); ?>
	</td>
	<td>
	<?php echo html_get_language_popup ('language',$u->getLanguage()); ?>
	</td>
</tr>

<tr class="top">
	<td><?php echo _('Timezone:'); ?></td>
	<td><?php echo html_get_timezone_popup('timezone', $u->getTimeZone()); ?>
	</td>
</tr>

<tr class="top">
	<td><?php echo _('Theme')._(':'); ?></td>
	<td><?php echo html_get_theme_popup('theme_id', $u->getThemeID()); ?>
	</td>
</tr>

<tr class="top">
	<td>
	<?php echo _('Country')._(':'); ?>
	</td>
	<td>
	<?php echo html_get_ccode_popup('ccode', $u->getCountryCode()); ?>
	</td>
</tr>

<tr class="top">
	<td>
	<?php echo _('Email Address')._(': '); ?>
	</td>
	<td><?php print $u->getEmail(); ?>
	<br /><a href="change_email.php">[<?php echo _('Change Email Address'); ?>]</a>
	</td>
</tr>

<tr class="top">
	<td>
	<?php echo _('Address')._(':'); ?>
	</td>
	<td>
		<label for="address">
			<input id="address" type="text" name="address" value="<?php echo $u->getAddress(); ?>" size="80"/>
		</label>
	</td>
</tr>

<tr class="top">
	<td>
	<?php echo _('Address (continued)')._(':'); ?>
	</td>
	<td>
		<label for="address2">
			<input id="address2" type="text" name="address2" value="<?php echo $u->getAddress2(); ?>" size="80"/>
		</label>
	</td>
</tr>

<tr class="top">
	<td>
	<?php echo _('Phone')._(':'); ?>
	</td>
	<td>
		<label for="phone">
			<input id="phone" type="text" name="phone" value="<?php echo $u->getPhone(); ?>" size="20"/>
		</label>
	</td>
</tr>

<tr class="top">
	<td>
	<?php echo _('Fax')._(':'); ?>
	</td>
	<td>
		<label for="fax">
			<input id="fax" type="text" name="fax" value="<?php echo $u->getFax(); ?>" size="20"/>
		</label>
	</td>
</tr>

<tr class="top">
	<td>
	<?php echo _('Title')._(':'); ?>
	</td>
	<td>
		<label for="title">
			<input id="title" type="text" name="title" value="<?php echo $u->getTitle(); ?>" size="10"/>
		</label>
	</td>
</tr>
<?php
echo $HTML->listTableBottom();
echo $HTML->boxBottom();
// ############################# Preferences
echo $HTML->boxTop(_('Preferences'));
?>

<p>
	<input id="mail_site" type="checkbox" name="mail_site" value="1"<?php
	if ($u->getMailingsPrefs('site')) print ' checked="checked"'; ?> />
	<label for="mail_site">
		<?php echo _('Receive Email about Site Updates <em>(Very low traffic and includes security notices. Highly Recommended.)</em>'); ?>
	</label>
</p>

<p>
	<input id="mail_va" type="checkbox" name="mail_va" value="1"<?php
	if ($u->getMailingsPrefs('va')) print ' checked="checked"'; ?> />
	<label for="mail_va">
		<?php echo _('Receive additional community mailings. <em>(Low traffic.)</em>'); ?>
	</label>
</p>

<p>
<?php if (forge_get_config('use_ratings')) { ?>
	<input id="use_ratings" type="checkbox" name="use_ratings" value="1"<?php
	if ($u->usesRatings()) print ' checked="checked"'; ?> />
	<label for="use_ratings">
		<?php printf(_('Participate in peer ratings. <em>(Allows you to rate other users using several criteria as well as to be rated by others. More information is available on your <a href="%s">user page</a> if you have chosen to participate in ratings.)</em>'), util_make_url_u($u->getUnixName(),$u->getID())); ?>
	</label>
<?php } ?>
</p>

<p>
	<input id="use_tooltips" type="checkbox" name="use_tooltips" value="1"<?php
	if ($u->usesTooltips()) print ' checked="checked"'; ?> />
		<label for="use_tooltips">
	<?php echo _('Enable tooltips. Small help texts displayed on mouse over links, images.'); ?>
	</label>
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
	<br />'._('Shell box')._(': ').'<strong>'.forge_get_config('shell_host').'</strong>
	<br />'._('SSH Shared Authorized Keys')._(': ').'<strong>';
		$sshKeysArray = $u->getAuthorizedKeys();
		if (is_array($sshKeysArray) && count($sshKeysArray)) {
			$tabletop = array(_('Name'), _('Algorithm'), _('Fingerprint'), _('Uploaded'));
			$classth = array('', '', '', '');
			echo $HTML->listTableTop($tabletop, false, 'sortable_sshkeys_listlinks', 'sortable', $classth);
			foreach($sshKeysArray as $sshKey) {
				$cells = array();
				$cells[][] = $sshKey['name'];
				$cells[][] = $sshKey['algorithm'];
				$cells[][] = $sshKey['fingerprint'];
				$cells[][] = date(_('Y-m-d H:i'), $sshKey['upload']);
				echo $HTML->multiTableRow(array(), $cells);
			}
			echo $HTML->listTableBottom();
		} else {
			print '0';
		}
		print '</strong>';
		print '<br />' . util_make_link('/account/editsshkeys.php', _('Edit Keys'));
	} else {
		echo $HTML->warning_msg(_('Shell Account deactivated'));
	}
	echo $HTML->boxBottom();
}
echo $HTML->addRequiredFieldsInfoBox();
echo html_e('p', array('class' => 'align-center'),
		html_e('input', array('type' => 'submit', 'name' => 'submit', 'value' => _('Update'))).
		html_e('input', array('type' => 'reset', 'name' => 'reset', 'value' => _('Reset Changes'))));
echo $HTML->closeForm();
site_user_footer();

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
