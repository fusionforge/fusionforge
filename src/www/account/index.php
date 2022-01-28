<?php
/**
 * User account main page - show settings with means to change them
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2010-2011, Franck Villaume - Capgemini
 * Copyright 2011, Alain Peyrat - Alcatel-Lucent
 * Copyright 2012-2014,2016,2022, Franck Villaume - TrivialDev
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
if (file_exists(forge_get_config('source_path').'/common/account/actions/'.$action.'.php')) {
	include(forge_get_config('source_path').'/common/account/actions/'.$action.'.php');
}

$hookParams['user'] = $u;
if (getStringFromRequest('submit')) {
	if (!form_key_is_valid(getStringFromRequest('form_key'))) {
		exit_form_double_submit('my');
	}

	$firstname = getStringFromRequest('firstname');
	$lastname = getStringFromRequest('lastname');
	$language = getIntFromRequest('language');
	$timezone = getStringFromRequest('timezone');
	if (forge_get_config('use_user_theme')) {
		$theme_id = getIntFromRequest('theme_id');
	} else {
		$theme_id = getThemeIdFromName(forge_get_config('default_theme'));
	}
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
	$quicknav_mode = getIntFromRequest('quicknav_mode');

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
				$theme_id,$address,$address2,$phone,$fax,$title,$ccode,$use_tooltips)
				|| !$u->setPreference('quicknav_mode', $quicknav_mode)) {
			form_release_key(getStringFromRequest('form_key'));
			$error_msg = $u->getErrorMessage();
		} else {
			plugin_hook('userisactivecheckboxpost', $hookParams);
			$feedback = _('Updated');
		}

		if ($refresh) {
			session_redirect('/account/');
		}
	}
}

html_use_tablesorter();
$title = _('My Account');
site_user_header(array('title'=>$title));

echo $HTML->openForm(array('action' => '/account/', 'method' => 'post'));
echo html_e('input', array('type' => 'hidden', 'name' => 'form_key', 'value' => form_generate_key()));
echo $HTML->boxTop(_('Account options'));
echo html_e('p', array(), _('Welcome').html_e('strong', array(), $u->getRealName()));
echo html_e('p', array(), _('Account options')._(':'));
$elementLi[] = array('content' => util_make_link_u($u->getUnixName(), html_e('strong', array(), _('View My Profile'))));
if(forge_get_config('use_people')) {
	$elementLi[] = array('content' => util_make_link('/people/editprofile.php', html_e('strong', array(), _('Edit My Skills Profile'))));
}
echo $HTML->html_list($elementLi);
echo $HTML->listTableTop(array(), array(), 'infotable');

$cells = array();
$cells[][] = _('Member since')._(':');
$cells[][] = date(_('Y-m-d H:i'), $u->getAddDate());
echo $HTML->multiTableRow(array('class' => 'top'), $cells);

$cells = array();
$cells[][] = _('User Id')._(':');
$cells[][] = $u->getID();
echo $HTML->multiTableRow(array('class' => 'top'), $cells);

$cells = array();
$cells[][] = _('Login Name')._(':');
$cells[][] = $u->getUnixName().html_e('br').util_make_link('/account/change_pw.php', _('Change Password'));
echo $HTML->multiTableRow(array('class' => 'top'), $cells);

$cells = array();
$cells[][] = _('First Name').utils_requiredField()._(':');
$cells[][] = html_e('label', array('for' => 'firstname'), html_e('input', array('type' => 'text', 'id' => 'firstname', 'required' => 'required', 'name' => 'firstname', 'value' => $u->getFirstName())));
echo $HTML->multiTableRow(array('class' => 'top'), $cells);

$cells = array();
if ($u->isEditable('lastname')) {
	$cells[][] = _('Last Name').utils_requiredField()._(':');
	$cells[][] = html_e('label', array('for' => 'lastname'), html_e('input', array('type' => 'text', 'id' => 'lastname', 'required' => 'required', 'name' => 'lastname', 'value' => $u->getLastName())));
} else {
	$cells[][] = _('Last Name')._(':');
	$cells[][] = $u->getLastName();
}
echo $HTML->multiTableRow(array('class' => 'top'), $cells);

$cells = array();
$cells[][] = _('Language')._(':');
if ($u->isEditable('language')) {
	$cells[][] = html_get_language_popup('language',$u->getLanguage());
} else {
	$cells[][] = $u->getLanguage();
}
echo $HTML->multiTableRow(array('class' => 'top'), $cells);

$cells = array();
$cells[][] = _('Timezone')._(':');
if ($u->isEditable('timezone')) {
	$cells[][] = html_get_timezone_popup('timezone', $u->getTimeZone());
} else {
	$cells[][] = $u->getTimeZone();
}
echo $HTML->multiTableRow(array('class' => 'top'), $cells);

if (forge_get_config('use_user_theme')) {
	$cells = array();
	$cells[][] = _('Theme')._(':');
	$cells[][] = html_get_theme_popup('theme_id', $u->getThemeID());
	echo $HTML->multiTableRow(array('class' => 'top'), $cells);
}

$cells = array();
$cells[][] = _('Country')._(':');
if ($u->isEditable('ccode')) {
	$cells[][] = html_get_ccode_popup('ccode', $u->getCountryCode());
} else {
	$cells[][] = $u->getCountryCode();
}
echo $HTML->multiTableRow(array('class' => 'top'), $cells);

$cells = array();
$cells[][] = _('Email Address')._(':');
if ($u->isEditable('email')) {
	$cells[][] = $u->getEmail().html_e('br').util_make_link('/account/change_email.php', _('Change Email Address'));
} else {
	$cells[][] = $u->getEmail();
}
echo $HTML->multiTableRow(array('class' => 'top'), $cells);

$cells = array();
$cells[][] = _('Address')._(':');
if ($u->isEditable('address')) {
	$cells[][] = html_e('label', array('for' => 'address'), html_e('input', array('type' => 'text', 'id' => 'address', 'name' => 'address', 'value' => $u->getAddress(), 'size' => 80)));
} else {
	$cells[][] = $u->getAddress();
}
echo $HTML->multiTableRow(array('class' => 'top'), $cells);

$cells = array();
$cells[][] = _('Address (continued)')._(':');
if ($u->isEditable('address2')) {
	$cells[][] = html_e('label', array('for' => 'address2'), html_e('input', array('type' => 'text', 'id' => 'address2', 'name' => 'address2', 'value' => $u->getAddress2(), 'size' => 80)));
} else {
	$cells[][] = $u->getAddress2();
}
echo $HTML->multiTableRow(array('class' => 'top'), $cells);

$cells = array();
$cells[][] = _('Phone')._(':');
if ($u->isEditable('phone')) {
	$cells[][] = html_e('label', array('for' => 'phone'), html_e('input', array('type' => 'text', 'id' => 'phone', 'name' => 'phone', 'value' => $u->getPhone(), 'size' => 20)));
} else {
	$cells[][] = $u->getPhone();
}
echo $HTML->multiTableRow(array('class' => 'top'), $cells);

$cells = array();
$cells[][] = _('Fax')._(':');
if ($u->isEditable('fax')) {
	$cells[][] = html_e('label', array('for' => 'fax'), html_e('input', array('type' => 'text', 'id' => 'fax', 'name' => 'fax', 'value' => $u->getFax(), 'size' => 20)));
} else {
	$cells[][] = $u->getFax();
}
echo $HTML->multiTableRow(array('class' => 'top'), $cells);

$cells = array();
$cells[][] = _('Title')._(':');
if ($u->isEditable('title')) {
	$cells[][] = html_e('label', array('for' => 'title'), html_e('input', array('type' => 'text', 'id' => 'title', 'name' => 'title', 'value' => $u->getTitle(), 'size' => 10)));
} else {
	$cells[][] = $u->getTitle();
}
echo $HTML->multiTableRow(array('class' => 'top'), $cells);

echo $HTML->listTableBottom();
echo $HTML->boxBottom();
echo html_e('br');
// ############################# Preferences
echo $HTML->boxTop(_('Preferences'));

$htmlAttrs = array('id' => 'mail_site', 'type' => 'checkbox', 'name' => 'mail_site', 'value' => 1);
if ($u->getMailingsPrefs('site')) {
	$htmlAttrs['checked'] = 'checked';
}
echo html_e('p', array(), html_e('input', $htmlAttrs).html_e('label', array('for' => 'mail_site'), _('Receive Email about Site Updates <em>(Very low traffic and includes security notices. Highly Recommended.)</em>')));

$htmlAttrs = array('id' => 'mail_va', 'type' => 'checkbox', 'name' => 'mail_va', 'value' => 1);
if ($u->getMailingsPrefs('va')) {
	$htmlAttrs['checked'] = 'checked';
}
echo html_e('p', array(), html_e('input', $htmlAttrs).html_e('label', array('for' => 'mail_va'), _('Receive additional community mailings. <em>(Low traffic.)</em>')));

if (forge_get_config('use_ratings')) {
	$htmlAttrs = array('id' => 'use_ratings', 'type' => 'checkbox', 'name' => 'use_ratings', 'value' => 1);
	if ($u->usesRatings()) {
		$htmlAttrs['checked'] = 'checked';
	}
	echo html_e('p', array(), html_e('input', $htmlAttrs).
				html_e('label', array('for' => 'use_ratings'), sprintf(_('Participate in peer ratings. <em>(Allows you to rate other users using several criteria as well as to be rated by others. More information is available on your <a href="%s">user page</a> if you have chosen to participate in ratings.)</em>'), util_make_url_u($u->getUnixName()))));
}

$htmlAttrs = array('id' => 'use_tooltips', 'type' => 'checkbox', 'name' => 'use_tooltips', 'value' => 1);
if ($u->usesTooltips()) {
	$htmlAttrs['checked'] = 'checked';
}
echo html_e('p', array(), html_e('input', $htmlAttrs).html_e('label', array('for' => 'use_tooltips'), _('Enable tooltips. Small help texts displayed on mouse over links, images.')));

if (!forge_get_config('use_quicknav_default')) {
	$htmlAttrs = array('id' => 'quicknav_mode', 'type' => 'checkbox', 'name' => 'quicknav_mode', 'value' => 1);
	if ($u->getPreference('quicknav_mode')) {
		$htmlAttrs['checked'] = 'checked';
	}
	echo html_e('p', array(), html_e('input', $htmlAttrs).
				html_e('label', array('for' => 'quicknav_mode'), _('Use advanced quicknav menu based on your navigation history on this site. Quicknav will use your 5 more visited projects.')));
}

// displays a "Use xxxx Plugin" checkbox
plugin_hook("userisactivecheckbox", $hookParams);

echo $HTML->boxBottom();

// ############################### Shell Account

if (forge_get_config('use_shell')) {
	echo html_e('br');
	echo $HTML->boxTop(_('Shell Account Information')."");
	if ($u->getUnixStatus() == 'A') {
		echo html_e('br')._('Shell box')._(': ').html_e('strong', array(), forge_get_config('shell_host')).
			html_e('br')._('SSH Shared Authorized Keys')._(': ').'<strong>';
		$sshKeysArray = $u->getAuthorizedKeys();
		if (is_array($sshKeysArray) && count($sshKeysArray)) {
			$tabletop = array(_('Name'), _('Algorithm'), _('Fingerprint'), _('Uploaded'));
			$classth = array('', '', '', '');
			echo $HTML->listTableTop($tabletop, array(), 'sortable_sshkeys_listlinks', 'sortable', $classth);
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
		echo html_e('br').util_make_link('/account/editsshkeys.php', _('Edit Keys'));
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
