<?php
/**
 * Send an Email Message Page
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2002-2004 (c) GForge Team
 * Copyright 2010-2013, Franck Villaume - TrivialDev
 * http://fusionforge.org/
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

require_once './env.inc.php';
require_once $gfcommon.'include/pre.php';

$toaddress = getStringFromRequest('toaddress');
$touser = getStringFromRequest('touser');

if (!$toaddress && !$touser) {
	exit_missing_param('', array(_('toaddress'), _('touser')), 'home');
}

if ($touser) {
	/*
		check to see if that user even exists
		Get their name and email if it does
	*/
	$result = db_query_params('SELECT email,user_name FROM users WHERE user_id=$1',
			array($touser));

	if (!$result || db_numrows($result) < 1) {
		exit_error(_('That user does not exist.'), 'home');
	}
}

if ($toaddress && !preg_match('/'.forge_get_config('web_host').'/i',$toaddress)) {
	exit_error(sprintf(_('You can only send to addresses @<em>%s</em>.'),forge_get_config('web_host')),'home');
}

if (getStringFromRequest('send_mail')) {
	if (!form_key_is_valid(getStringFromRequest('form_key'))) {
		exit_form_double_submit('home');
	}

	$valide = 1;
	if (!session_loggedin()) {
		$params['valide'] =& $valide;
		$params['warning_msg'] =& $warning_msg;
		plugin_hook('captcha_check', $params);
	}

	$subject = getStringFromRequest('subject');
	$body = getStringFromRequest('body');
	$name = getStringFromRequest('name');
	$email = getStringFromRequest('email');

	if ($valide) {
		if (!$subject || !$body || !$name || !$email) {
			/*
				force them to enter all vars
			*/
			form_release_key(getStringFromRequest('form_key'));
			exit_missing_param('', array(_('Subject'), _('Body'), _('Name'), _('Email')), 'home');
		}

		// we remove the CRLF in all thoses vars. This is to make sure that there will be no CRLF Injection
		$name = util_remove_CRLF($name);
		// Really don't see what wrong could happen with CRLF in message body
		//$email = util_remove_CRLF($email);
		$subject = util_remove_CRLF($subject);

		if ($toaddress) {
			/*
				send it to the toaddress
			*/
			$to = preg_replace('/_maillink_/i', '@', $toaddress);
			$to = util_remove_CRLF($to);
			util_send_message($to, $subject, $body, $email, '', $name);
			$HTML->header(array('title' => forge_get_config('forge_name').' ' ._('Contact')));
			echo '<p>'._('Message has been sent').'.</p>';
			$HTML->footer(array());
			exit;
		} elseif ($touser) {
			/*
				figure out the user's email and send it there
			*/
			$to = db_result($result,0,'email');
			$to = util_remove_CRLF($to);
			util_send_message($to, $subject, $body, $email, '', $name);
			$HTML->header(array('title' => forge_get_config('forge_name').' '._('Contact')));
			echo '<p>'._('Message has been sent').'</p>';
			$HTML->footer(array());
			exit;
		}
	}
}

if ($toaddress) {
	$titleaddress = $toaddress;
} else {
	$titleaddress = db_result($result,0,'user_name');
}

if (session_loggedin()) {
	$user  =& session_get_user();
	$name  = $user->getRealName();
	$email = $user->getEmail();
	$is_logged = true;
} else {
	$is_logged = false;
	if (!isset($valide)) {
		$name  = '';
		$email = '';
	}
}

$subject = getStringFromRequest('subject');
$HTML->header(array('title' => forge_get_config('forge_name').' '._('Contact')));

?>

<p>
<?php echo _('Fill it out accurately and completely or the receiver may not be able to respond.'); ?>
</p>

<p class="important">
<?php echo _('<strong>IF YOU ARE WRITING FOR HELP:</strong> Did you read the site documentation? Did you include your <strong>user_id</strong> and <strong>user_name?</strong> If you are writing about a project, include your <strong>project id</strong> (<strong>group_id</strong>) and <strong>Project Name</strong>.'); ?>
</p>

<form action="<?php echo getStringFromServer('PHP_SELF'); ?>" method="post">

<p>
<input type="hidden" name="form_key" value="<?php echo form_generate_key(); ?>" />
<input type="hidden" name="toaddress" value="<?php echo $toaddress; ?>" />
<input type="hidden" name="touser" value="<?php echo $touser; ?>" />

<strong><?php echo _('Your Name').utils_requiredField()._(':'); ?></strong><br />
<?php
if ($is_logged) {
	echo '<input type="hidden" name="name" value="'.$name.'" />';
	echo '<input type="text" disabled="disabled" size="'.strlen($name).'" value="'.$name.'" />';
} else {
	echo '<input type="text" required="required" name="name" size="40" maxlength="40" value="'.$name.'" />';
}
?>
</p>
<p>
<strong><?php echo _('Your Email Address').utils_requiredField()._(':'); ?></strong><br />
<?php
if ($is_logged) {
	echo '<input type="hidden" name="email" value="'.$email.'" />';
	echo '<input type="text" disabled="disabled" size="'.strlen($email).'" value="'.$email.'" />';
} else {
	echo '<input type="email" required="required" name="email" size="40" maxlength="255" value="'.$email.'" />';
}
?>
</p>
<p>
<strong><?php echo _('Subject').utils_requiredField()._(':'); ?></strong><br />
<input type="text" required="required" name="subject" size="60" maxlength="255" value="<?php echo $subject; ?>" />
</p>
<p>
<strong><?php echo _('Message').utils_requiredField()._(':'); ?></strong><br />
<textarea name="body" required="required" rows="15" cols="60" >
<?php
if (isset($body)) {
	echo $body;
}
?>
</textarea>
</p>
<?php
if (!$is_logged) {
	plugin_hook('captcha_form');
}
?>
<p align="center">
<input type="submit" name="send_mail" value="<?php echo _('Send Message') ?>" />
</p>
</form>
<?php
$HTML->footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
