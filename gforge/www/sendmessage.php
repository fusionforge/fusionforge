<?php
/**
 * Send an Email Message Page
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * The rest Copyright 2002-2004 (c) GForge Team
 * http://gforge.org/
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

if (!$toaddress && !$touser) {
	exit_error($Language->getText('general','error'),$Language->getText('sendmessage','error_variables'));
}

if ($touser) {
	/*
		check to see if that user even exists
		Get their name and email if it does
	*/
	$result=db_query("SELECT email,user_name FROM users WHERE user_id='$touser'");
	if (!$result || db_numrows($result) < 1) {
		exit_error($Language->getText('general','error'),$Language->getText('sendmessage','error_user_not_exist'));
	}
}

if ($toaddress && !eregi($GLOBALS['sys_default_domain'],$toaddress)) {
	exit_error($Language->getText('general','error'),$Language->getText('sendmessage','email_only_to')." @".$GLOBALS['sys_default_domain']);
}


if ($send_mail) {
	if (!$subject || !$body || !$name || !$email) {
		/*
			force them to enter all vars
		*/
		exit_missing_param();
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
		$to=eregi_replace('_maillink_','@',$toaddress);
		$to = util_remove_CRLF($to);
		util_send_message($to,stripslashes($subject),stripslashes($body),$email,'',$name);
		$HTML->header(array('title'=>$GLOBALS['sys_name'].' ' .$Language->getText('sendmessage','contact')   ,'pagename'=>'sendmessage','titlevals'=>array($to)));
		echo '<p>'.$Language->getText('sendmessage','message_sent').'.</p>';
		$HTML->footer(array());
		exit;
	} else if ($touser) {
		/*
			figure out the user's email and send it there
		*/
		$to=db_result($result,0,'email');
		$to = util_remove_CRLF($to);
		util_send_message($to,stripslashes($subject),stripslashes($body),$email,'',$name);
		$HTML->header(array('title'=>$GLOBALS['sys_name'].' '.$Language->getText('sendmessage','contact'),'pagename'=>'sendmessage','titlevals'=>array($touser)));
		echo '<p>'.$Language->getText('sendmessage','message_sent').'</p>';
		$HTML->footer(array());
		exit;
	}
}

if ($toaddress) {
	$titleaddress = $toaddress;
} else {
	$titleaddress = db_result($result,0,'user_name');
}

$HTML->header(array('title'=>$GLOBALS['sys_name'].' Staff','pagename'=>'sendmessage','titlevals'=>array($titleaddress)));

?>

<p />
<?php echo $Language->getText('sendmessage', 'about_blurb'); ?>
<p />
<form action="<?php echo $PHP_SELF; ?>" method="post">
<input type="hidden" name="toaddress" value="<?php echo $toaddress; ?>" />
<input type="hidden" name="touser" value="<?php echo $touser; ?>" />

<strong><?php echo $Language->getText('sendmessage','email') ?>:</strong><br />
<input type="text" name="email" size="30" maxlength="40" value="" />
<p />
<strong>Your Name:</strong><br />
<input type="text" name="name" size="30" maxlength="40" value="" />
<p />
<strong>Subject:</strong><br />
<input type="text" name="subject" size="30" maxlength="40" value="<?php echo $subject; ?>" />
<p />
<strong>Message:</strong><br />
<textarea name="body" rows="15" cols="60"></textarea>
<p />
<div align="center">
<input type="submit" name="send_mail" value="<?php echo $Language->getText('sendmessage','send') ?>" />
</div>
</form>
<?php
$HTML->footer(array());

?>

