<?php
/**
 * Send an Email Message Page
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2002-2004 (c) GForge Team
 * Copyright 2010 (c) Franck Villaume
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

require_once('./env.inc.php');
require_once $gfcommon.'include/pre.php';

$toaddress = getStringFromRequest('toaddress');
$touser = getStringFromRequest('touser');

if (!$toaddress && !$touser) {
	exit_missing_param('',array(_('toaddress'),_('touser')),'home');
}

if ($touser) {
	/*
		check to see if that user even exists
		Get their name and email if it does
	*/
	$result=db_query_params ('SELECT email,user_name FROM users WHERE user_id=$1',
			array($touser)) ;

	if (!$result || db_numrows($result) < 1) {
		exit_error(_('That user does not exist'),'home');
	}
}

if ($toaddress && !preg_match('/'.forge_get_config('web_host').'/i',$toaddress)) {
	exit_error(sprintf(_('You can only send to addresses @<em>%1$s</em>.'),forge_get_config('web_host')),'home');
}


if (getStringFromRequest('send_mail')) {
	if (!form_key_is_valid(getStringFromRequest('form_key'))) {
		exit_form_double_submit('home');
	}

	$subject = getStringFromRequest('subject');
	$body = getStringFromRequest('body');
	$name = getStringFromRequest('name');
	$email = getStringFromRequest('email');

	if (!$subject || !$body || !$name || !$email) {
		/*
			force them to enter all vars
		*/
		form_release_key(getStringFromRequest('form_key'));
		exit_missing_param('',array(_('Subject'),_('Body'),_('Name'),_('Email')),'home');
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
		$to=preg_replace('/_maillink_/i','@',$toaddress);
		$to = util_remove_CRLF($to);
		util_send_message($to,$subject,$body,$email,'',$name);
		$HTML->header(array('title'=>forge_get_config ('forge_name').' ' ._('Contact')   ));
		echo '<p>'._('Message has been sent').'.</p>';
		$HTML->footer(array());
		exit;
	} else if ($touser) {
		/*
			figure out the user's email and send it there
		*/
		$to=db_result($result,0,'email');
		$to = util_remove_CRLF($to);
		util_send_message($to,$subject,$body,$email,'',$name);
		$HTML->header(array('title'=>forge_get_config ('forge_name').' '._('Contact')));
		echo '<p>'._('Message has been sent').'</p>';
		$HTML->footer(array());
		exit;
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
} else {
	$name  = '';
	$email = '';
}
$subject = getStringFromRequest('subject');

$HTML->header(array('title'=>forge_get_config ('forge_name').' Staff'));

?>

<p>
<?php echo _('In an attempt to reduce spam, we are using this form to send email.'); ?>
</p>

<p>
<?php echo _('Fill it out accurately and completely or the receiver may not be able to respond.'); ?>
</p>

<p class="important">
<?php echo _('<b>IF YOU ARE WRITING FOR HELP:</b> Did you read the site documentation? Did you include your <b>user_id</b> and <b>user_name?</b> If you are writing about a project, include your <b>project id</b> (<b>group_id</b>) and <b>Project Name</b>.'); ?>
</p>

<form action="<?php echo getStringFromServer('PHP_SELF'); ?>" method="post">

<p>
<input type="hidden" name="form_key" value="<?php echo form_generate_key(); ?>" />
<input type="hidden" name="toaddress" value="<?php echo $toaddress; ?>" />
<input type="hidden" name="touser" value="<?php echo $touser; ?>" />

<strong><?php echo _('Your Name').utils_requiredField() ?> :</strong><br />
<input type="text" name="name" size="40" maxlength="40" value="<?php echo $name ?>" />
</p>
<p>
<strong><?php echo _('Your Email Address').utils_requiredField() ?> :</strong><br />
<input type="text" name="email" size="40" maxlength="255" value="<?php echo $email ?>" />
</p>
<p>
<strong><?php echo _('Subject').utils_requiredField() ?> :</strong><br />
<input type="text" name="subject" size="60" maxlength="255" value="<?php echo $subject; ?>" />
</p>
<p>
<strong><?php echo _('Message').utils_requiredField() ?> :</strong><br />
<textarea name="body" rows="15" cols="60"></textarea>
</p>
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

?>
