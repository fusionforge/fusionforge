<?php
/**
 * Register new account page
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2010 (c) FusionForge Team
 * Copyright (C) 2011 Alain Peyrat - Alcatel-Lucent
 * Copyright 2013-2014,2016, Franck Villaume - TrivialDev
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
require_once $gfcommon.'include/account.php';
require_once $gfcommon.'include/timezones.php';

global $HTML;

if (forge_get_config('user_registration_restricted')) {
	session_require_global_perm('forge_admin');
}

$unix_name = getStringFromRequest('unix_name');
$firstname = getStringFromRequest('firstname');
$lastname = getStringFromRequest('lastname');
$password1 = getStringFromRequest('password1');
$password2 = getStringFromRequest('password2');
$email = getStringFromRequest('email');
$mail_site = getIntFromRequest('mail_site');
$mail_va = getIntFromRequest('mail_va');

$language_id = getIntFromRequest('language_id');
$timezone = getStringFromRequest('timezone');
$theme_id = getIntFromRequest('theme_id');
$address = getStringFromRequest('address');
$address2 = getStringFromRequest('address2');
$phone = getStringFromRequest('phone');
$fax = getStringFromRequest('fax');
$title = getStringFromRequest('title');
$ccode = getStringFromRequest('ccode');
$accept_conditions = getIntFromRequest ('accept_conditions');

if (forge_get_config('use_ssl') && !session_issecure()) {
	//force use of SSL for login
	header('Location: https://'.getStringFromServer('HTTP_HOST').forge_get_config('url_prefix').getStringFromServer('REQUEST_URI'));
}

if (!$theme_id || !is_numeric($theme_id)) {
	$theme_id = getThemeIdFromName(forge_get_config('default_theme'));
}

if (getStringFromRequest('submit')) {
	/*
		Adding call to library rather than
		logic that used to be coded in this page
	*/
	if (!form_key_is_valid(getStringFromRequest('form_key'))) {
		exit_form_double_submit('my');
	}

	$valide = 1;
	if (forge_get_config('user_registration_accept_conditions') && ! $accept_conditions) {
		$warning_msg = _("You can't register an account unless you accept the terms of use.");
		$valide = 0;
	}
	if (!forge_check_global_perm('forge_admin')) {
		$params['valide'] =& $valide;
		$params['warning_msg'] =& $warning_msg;
		plugin_hook('captcha_check', $params);
	}

	if ($valide) {
		$activate_immediately = getIntFromRequest('activate_immediately');
		if (($activate_immediately == 1) &&
		    forge_check_global_perm ('forge_admin')) {
			$send_mail = false;
			$activate_immediately = true;
		} else {
			$send_mail = true;
			$activate_immediately = false;
		}

		$new_user = new FFUser();
		$register = $new_user->create($unix_name,$firstname,$lastname,$password1,$password2,
					      $email,$mail_site,$mail_va,$language_id,$timezone,'',0,$theme_id,'',
					      $address,$address2,$phone,$fax,$title,$ccode,$send_mail);
		if ($register) {
			site_header(array('title'=>_('Register Confirmation')));

			if ($activate_immediately) {
				if (!$new_user->setStatus('A')) {
					echo $HTML->error_msg(_('Error during user activation but after user registration (user is now in pending state and will not get a notification eMail!)'));
					print '<p>' . sprintf(_("Could not activate newly registered user's forge account: %s"), htmlspecialchars($new_user->getErrorMessage())) . '</p>';
					site_footer();
					exit;
				}
			}
			if ($send_mail) {
				echo '<p>';
				printf(_('You have registered the %1$s account on %2$s.'),
				       $new_user->getUnixName(),
				       forge_get_config ('forge_name'));
				echo '</p>';
				print '<p>' . _('A confirmation email is being sent to verify the submitted email address. Visiting the link sent in this email will activate the account.') . '</p>';
			} else {
				print '<p>' ;
				printf (_('You have registered and activated user %1$s on %2$s. They will not receive an eMail about this fact.'), $unix_name, forge_get_config('forge_name'));
				print '</p>' ;
			}
			site_footer();
			exit;
		} else {
			$error_msg = $new_user->getErrorMessage();
			if (isset($register_error)) {
				$error_msg .= ' '.$register_error;
			}
		}
	}
}

if (!isset($timezone) || empty($timezone) || !preg_match('/^[-a-zA-Z0-9_\/\.+]+$/', $timezone)) {
	$timezone = forge_get_config('default_timezone') ? forge_get_config('default_timezone') : 'GMT' ;
}
if (!isset($ccode) || empty($ccode) || !preg_match('/^[a-zA-Z]{2}$/', $ccode)) {
	$ccode = forge_get_config('default_country_code');
}

site_header(array('title'=>_('User Account Registration')));
echo $HTML->openForm(array('action' => '/account/register.php', 'method' => 'post'));
echo html_e('input', array('type' => 'hidden', 'name' => 'form_key', 'value' => form_generate_key()));
?>
<p>
<?php
if (forge_get_config('require_unique_email')) {
	echo _('Login Name (no uppercase letters; leave empty to have it generated automatically):');
} else {
	echo _('Login Name (do not use uppercase letters)').utils_requiredField()._(':');
} ?><br />
    <label for="unix_name">
        <input id="unix_name" type="text" required="required" name="unix_name" value="<?php print(htmlspecialchars($unix_name)); ?>"/>
    </label>
</p>
<p>
<?php echo _('Password').utils_requiredField()._(':'); ?><br />
<em>
<?php printf(_('Minimum 8 characters.')); ?><br/>
<?php
if (forge_get_config('check_password_strength')) {
	printf(_('Must contain at least one uppercase letter, one lowercase, one digit, one non-alphanumeric character.').'<br/>');
}
?>
</em>
    <label for="password1">
        <input id="password1" type="password" required="required" name="password1"/>
    </label>
</p>
<p>
<?php echo _('Password (repeat)').utils_requiredField()._(':'); ?><br />
    <label for="password2">
        <input id="password2" type="password" required="required" name="password2"/>
    </label>
</p
><p>
<?php echo _('Title')._(':'); ?><br />
    <label for="title">
        <input id="title" type="text" name="title" value="<?php echo htmlspecialchars($title); ?>" size="10"/>
    </label>
</p>
<p>
<?php echo _('First Name').utils_requiredField()._(':'); ?><br />
    <label for="firstname">
        <input id="firstname" required="required" size="30" type="text" name="firstname"
               value="<?php print(htmlspecialchars($firstname)); ?>"/>
    </label>
</p>
<p>
<?php echo _('Last Name').utils_requiredField()._(':'); ?><br />
    <label for="lastname">
        <input id="lastname" required="required" size="30" type="text" name="lastname"
               value="<?php print(htmlspecialchars($lastname)); ?>"/>
    </label>
</p>
<p>
<?php echo _('Language Choice:'); ?><br />
<?php echo html_get_language_popup ('language_id', language_name_to_lang_id (choose_language_from_context ())); ?>
</p>
<p>
<?php echo _('Timezone:'); ?><br />
<?php echo html_get_timezone_popup('timezone', $timezone); ?>
</p>
<?php
$toDisplay = html_get_theme_popup('theme_id', $theme_id);
if($toDisplay != "") {
?>
<p>
<?php echo _('Theme')._(':'); ?><br />
<?php echo $toDisplay; ?>
</p>
<?php } ?>
<p>
<?php echo _('Country:'); ?><br />
<?php echo html_get_ccode_popup('ccode', $ccode); ?>
</p>
<p>
<?php echo _('Email Address').utils_requiredField()._(':'); ?>
<br />
<em>
<?php printf(_('This email address will be verified before account activation. You will receive a mail forward account at &lt;loginname@%s&gt; that will forward to this address.'), forge_get_config('users_host')); ?>
</em>
<br />
<label for="email">
    <input id="email" size="40" type="text" name="email" required="required" value="<?php print(htmlspecialchars($email)); ?>"/>
</label>
</p>
<p>
<?php echo _('Address')._(':'); ?><br />
    <label for="address">
        <input id="address" type="text" name="address" value="<?php echo htmlspecialchars($address); ?>" size="80"/>
    </label>
</p>
<p>
<?php echo _('Address (continued)')._(':'); ?><br />
    <label for="address2">
        <input id="address2" type="text" name="address2" value="<?php echo htmlspecialchars($address2); ?>" size="80"/>
    </label>
</p>
<p>
<?php echo _('Phone')._(':'); ?><br />
    <label for="phone">
        <input id="phone" type="text" name="phone" value="<?php echo htmlspecialchars($phone); ?>" size="20"/>
    </label>
</p>
<p>
<?php echo _('Fax')._(':'); ?><br />
    <label for="fax">
        <input id="fax" type="text" name="fax" value="<?php echo htmlspecialchars($fax); ?>" size="20"/>
    </label>
</p>
<p>
    <label for="mail_site">
        <input id="mail_site" type="checkbox" name="mail_site" value="1" checked="checked"/>
    </label>
    <?php echo _('Receive Email about Site Updates <em>(Very low traffic and includes security notices. Highly Recommended.)</em>'); ?>
</p>
<p>
    <label for="mail_va">
        <input id="mail_va" type="checkbox" name="mail_va" value="1"/>
    </label>
    <?php echo _('Receive additional community mailings. <em>(Low traffic.)</em>'); ?>
</p>
<?php if (forge_get_config('user_registration_accept_conditions')) { ?>
	<p>
	<input id="accept_conditions" type="checkbox" name="accept_conditions" value="1" />
	<label for="accept_conditions">
		<?php printf (_('Do you accept the <a href="%s">terms of use</a> for this site?'), util_make_url('/terms.php')); ?>
	</label>
	</p>
<?php } ?>
<?php if (forge_check_global_perm('forge_admin')) { ?>
	<p>
		<input id="activate_immediately" type="checkbox" name="activate_immediately" value="1" />
		<label for="activate_immediately">
			<?php print _('Activate this user immediately') ; ?>
		</label>
	</p>
<?php } else {
	$html = '';
	plugin_hook_by_reference('captcha_form', $html);
	echo $html;
}
echo $HTML->addRequiredFieldsInfoBox();
echo html_e('p', array(), html_e('input', array('type' => 'submit', 'name' => 'submit', 'value' => _('Register'))));
echo $HTML->closeForm();
echo html_e('p', array(), util_make_link('/account/pending-resend.php', _('Resend confirmation email to a pending account')));
site_footer();

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
