#!/usr/local/bin/php -q
<?php
/**
  *
  * Massmail backend cron script
  * This is mass mailing backend script which actually sends messages 
  * of the mailings scheduled via the web frontend. It does so by 
  * spooling messages directly to mail server via SMTP protocol.
  * Mailing types, for which this is applicable, have trailer
  * appended with individual URL for unsubscription from future
  * mailings.
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */

require ('squal_pre.php');

// SMTP server to connect to
$MAILSERVER = "sf-list1";
//$MAILSERVER = "localhost";
// Whether to feed batch on its entirety and ignore responces or
// *talk* with server. I decided against using pipelining.
$PIPELINE = 0;
// Number of users to mail during single run
$CHUNK = 2000;
// Size of SMTP batch (so many messages are sent in one connection)
$BATCH = 20;
// Pause between batches, sec
$SLEEP = 10;
// Dump batches to file instead sending them over socket
$TEST = 0;

// This tables maps mailing types to tables which required to perform it
$table_mapping = array(
  'ALL'	    => "users",
  'SITE'    => "users",
  'COMMNTY' => "users",
  'DVLPR'   => "users,user_group",
  'ADMIN'   => "users,user_group",
  'SFDVLPR' => "users,user_group",
);

// This tables maps mailing types to WHERE subclauses which select 
// appropriate users
$cond_mapping = array(
  'ALL'	    => "",
  'SITE'    => "AND mail_siteupdates=1",
  'COMMNTY' => "AND mail_va=1",
  'DVLPR'   => "AND users.user_id=user_group.user_id",
  'ADMIN'   => "AND users.user_id=user_group.user_id AND user_group.admin_flags='A'",
  'SFDVLPR' => "AND users.user_id=user_group.user_id AND user_group.group_id=1"
);

/*if (!strstr($REMOTE_ADDR,$sys_internal_network)) {
        exit_permission_denied();
}*/

$mail_res = db_query("
	SELECT *
	FROM massmail_queue
	WHERE finished_date=0
	ORDER BY queued_date
", 1);

/* If there was error, notify admins, but don't be pesky */
if (!$mail_res) {
	print "cannot execute quesry to select pending mailings\n";
	$hrs = time()/(60*60);
	// Send reminder every second day at 11am
	if (($hrs%24)==11 && (($hrs/24)%2)==1) {
	        global $sys_default_domain;
		util_send_message(
			"admin@$sys_default_domain",
			"ATT: Problems with massmail cron script",
			"This is automatically generated message from\n"
			."the mass mailing cron script of SourceForge\n"
			."installation. There was error querying massmail_queue\n"
			."database table. Please take appropriate actions.\n"
		);
	}

	exit(1);
}

// print "Got ".db_numrows($mail_res)." rows\n";

if (db_numrows($mail_res)<1) {
	// Nothing to send
	exit();
}

$type = db_result($mail_res, 0, 'type');
if (!$table_mapping[$type]) {
    print "Unknown mailing type\n";
    exit(1);
}

$subj = db_result($mail_res, 0, 'subject');
$mail_id = db_result($mail_res, 0, 'id');

//print "Got mail to send: ".$subj."\n";

$sql = "
	SELECT users.user_id,user_name,realname,email,confirm_hash
	FROM ".$table_mapping[$type]."
	WHERE users.user_id>".db_result($mail_res, 0, 'last_userid')."
	AND status='A'
	".$cond_mapping[$type]."
	ORDER BY users.user_id
";

//echo $sql;

// Get next chunk of users to mail
$users_res = db_query($sql, $CHUNK);

print "Mailing ".db_numrows($users_res)." users.\n";

// If no more users left, we've finished with this mailing
if ($users_res && db_numrows($users_res)==0) {
	db_query("
		UPDATE massmail_queue
		SET failed_date=0,finished_date='".time()."'
		WHERE id=$mail_id
	");
	exit();
}

$batch_no = 0;
$count = 0;
$last_userid = 0;

// These mailing types should include unsubscription info
if ($type=='SITE' || $type=='COMMNTY') {
	$tail = "\r\n==================================================================\r\n"
	       ."You receive this message because you subscribed to SourceForge\r\n"
	       ."site mailing(s). You may opt out from some of them selectively\r\n"
	       ."by logging in to SourceForge and visiting your Account Maintenance\r\n"
	       ."page (https://$sys_default_domain/account/), or disable them altogether\r\n"
	       ."by visiting following link:\r\n"
	       ."<https://$sys_default_domain/account/unsubscribe.php?ch=_%s>\r\n";
}
$body = db_result($mail_res, 0, 'message');
//$lines = explode("\n", $body);
//$crlf_body = implode("\r\n", $lines);

// Get SMTP response
function get_resp() {
        global $out;
	global $response;
	
	$response = fgets($out, 500);
//	print ">$response";
	return substr($response, 0, 3);
}

// Expect given response, fail with $diag otherwise
function expect($diag, $resp) {
	global $PIPELINE;
	global $response;
	
	if (!$PIPELINE) {
		if (get_resp()!=$resp) {
			print "Error: $diag: $response";
			exit(1);
		}
	}
}

// Start new batch
function start_batch() {
        global $out;
        global $batch_no;
	global $sys_default_domain;
	global $MAILSERVER;
	global $TEST;

	if ($TEST) {
		$out = fopen("!batch.$batch_no","wb");
	} else {
		$out = fsockopen($MAILSERVER, 25, $errno, $errstr);
	}
	
	if (!$out) {
		print "Error connecting to SMTP: $errstr\n";
		exit(1);
	}
	if (!$TEST) {
		$resp = fgets($out,200);
		if (substr($resp,0,3)!="220") {
			print "Server is not ready to receive messages\n";
			exit(1);
		}
	}
	fputs($out,"HELO $sys_default_domain\r\n");
	expect("HELO", "250");
}

// Finish new batch
function flush_batch() {
        global $out;
        global $count;
        global $last_userid;
	global $mail_id;
	global $TEST;

        if ($count) {
		fputs($out,"QUIT\r\n");
		if (!$TEST) {
//			fpassthru($out);
			while (!feof($out)) fgets($out, 200);
			fclose($out);
		} else {
			fclose($out);
		}
		$count = 0;

		$sql="
			UPDATE massmail_queue
			SET failed_date=0,
			    last_userid=$last_userid
			WHERE id=$mail_id
		";
//		print $sql;
		db_query($sql);

		sleep($SLEEP);
	}
}


// Actual mailing loop
while ($row = db_fetch_array($users_res)) {

        if (!$count) {
                $batch_no++;
		start_batch();
        }

//        print "Sending for: ".$row['user_id']."\n";

//	$row['email'] = 'test@email';

        fputs($out,"MAIL FROM: noreply@$sys_default_domain\r\n");
	expect("MAIL", "250");
	fputs($out,"RCPT TO: ".$row['email']."\r\n");
	expect("RCPT", "250");
	fputs($out,"DATA\r\n");
	expect("DATA", "354");
	fputs(
		$out,
		"From: Mailer <noreply@$sys_default_domain>\r\n"
		."To: \"".strtr($row['realname'],'"',"'")."\" <".$row['email'].">\r\n"
		."Subject: ".$subj."\r\n"
		."\r\n"
		.$body
		."\r\n"
		.sprintf($tail,$row['confirm_hash'] )
		."\r\n.\r\n"
	);
	expect("DATA end", "250");

        $last_userid = $row['user_id'];

	if (++$count == $BATCH) {
	        flush_batch();
	}
}

flush_batch();

// print "end\n";

?>
