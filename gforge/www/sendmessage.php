<?php
/**
  *
  * Send an Email Message Page
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */


require_once('pre.php');    

if (!$toaddress && !$touser) {
	exit_error('Error','Error - some variables were not provided');
}

if ($touser) {
	/*
		check to see if that user even exists
		Get their name and email if it does
	*/
	$result=db_query("SELECT email,user_name FROM users WHERE user_id='$touser'");
	if (!$result || db_numrows($result) < 1) {
		exit_error('Error','Error - That user does not exist.');
	}
}

if ($toaddress && !eregi($GLOBALS['sys_default_domain'],$toaddress)) {
	exit_error("error","You can only send to addresses @".$GLOBALS['sys_default_domain']);
}


if ($send_mail) {
	if (!$subject || !$body || !$name || !$email) {
		/*
			force them to enter all vars
		*/
		exit_missing_param();
	}

	if ($toaddress) {
		/*
			send it to the toaddress
		*/
		$to=eregi_replace('_maillink_','@',$toaddress);
		$from=$name .' <'. $email .'>';
		util_send_message($to, stripslashes($subject),stripslashes($body) ,$from);
		$HTML->header(array('title'=>$GLOBALS['sys_name'].' Contact','pagename'=>'sendmessage','titlevals'=>array($to)));
		echo '<p>Message has been sent.</p>';
		$HTML->footer(array());
		exit;
	} else if ($touser) {
		/*
			figure out the user's email and send it there
		*/
		$to=db_result($result,0,'email');
		$from=$name .' <'. $email .'>';
		util_send_message($to, stripslashes($subject), stripslashes($body),$from);
		$HTML->header(array('title'=>$GLOBALS['sys_name'].' Contact','pagename'=>'sendmessage','titlevals'=>array($touser)));
		echo '<p>Message has been sent.</p>';
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

<p>
<?php echo $Language->getText('sendmessage', 'about_blurb'); ?>
<p>
<form action="<?php echo $PHP_SELF; ?>" METHOD="POST">
<input type="hidden" name="toaddress" value="<?php echo $toaddress; ?>">
<input type="hidden" name="touser" value="<?php echo $touser; ?>">

<strong>Your Email Address:</strong><br />
<input type="TEXT" name="email" SIZE="30" MAXLENGTH="40" value="">
<p>
<strong>Your Name:</strong><br />
<input type="TEXT" name="name" SIZE="30" MAXLENGTH="40" value="">
<p>
<strong>Subject:</strong><br />
<input type="TEXT" name="subject" SIZE="30" MAXLENGTH="40" value="<?php echo $subject; ?>">
<p>
<strong>Message:</strong><br />
<textarea name="body" ROWS="15" COLS="60" WRAP="HARD"></textarea>
<p>
<CENTER>
<input type="SUBMIT" name="send_mail" value="Send Message">
</CENTER>
</form>
<?php
$HTML->footer(array());

?>
