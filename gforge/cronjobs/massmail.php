#! /usr/bin/php5 -f
<?php
/**
 * Massmail backend cron script
 * This is mass mailing backend script which actually sends messages 
 * of the mailings scheduled via the web frontend.
 * Mailing types, for which this is applicable, have trailer
 * appended with individual URL for unsubscription from future
 * mailings.
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2003 (c) GForge, LLC
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
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  US
 */

require ('squal_pre.php');
require ('common/include/cron_utils.php');

$err='';

if (!cron_create_lock(__FILE__)) {
	$err = "Massmail already running...exiting";
		if (!cron_entry(6,$err)) {
			# rely on crond to report the error
			echo "cron_entry error: ".db_error()."\n";
		}
	exit();
}

//bad hack to get around Roland's misuse of Language in utils.php
$Language = new BaseLanguage();
$Language->loadLanguage($sys_lang);

// Pause between messages, sec
$SLEEP = 1;

// This tables maps mailing types to tables which required to perform it
$table_mapping = array(
	'ALL'		=> "users",
	'SITE'	=> "users",
	'COMMNTY' => "users",
	'DVLPR'   => "users,user_group",
	'ADMIN'   => "users,user_group,groups",
	'SFDVLPR' => "users,user_group",
);

// This tables maps mailing types to WHERE subclauses which select 
// appropriate users
$cond_mapping = array(
	'ALL'		=> "",
	'SITE'	=> "AND mail_siteupdates=1",
	'COMMNTY' => "AND mail_va=1",
	'DVLPR'   => "AND users.user_id=user_group.user_id",
	'ADMIN'   => "AND users.user_id=user_group.user_id AND user_group.admin_flags='A' AND groups.status='A' AND groups.group_id=user_group.group_id",
	'SFDVLPR' => "AND users.user_id=user_group.user_id AND user_group.group_id=1"
);

$mail_res = db_query("SELECT *
	FROM massmail_queue
	WHERE finished_date=0
	ORDER BY queued_date");

/* If there was error, notify admins, but don't be pesky */
if (!$mail_res) {
	$err .= "cannot execute query to select pending mailings: ".db_error()."\n";
	$hrs = time()/(60*60);
	// Send reminder every second day at 11am
	if (($hrs%24)==11 && (($hrs/24)%2)==1) {
		global $sys_admin_email;
		util_send_message(
			"$sys_admin_email",
			"ATT: Problems with massmail cron script",
			"This is automatically generated message from\n"
			."the mass mailing cron script of $sys_name\n"
			."installation. There was error querying massmail_queue\n"
			."database table. Please take appropriate actions.\n"
		);
	}
	m_exit();
}

// $err .= "Got ".db_numrows($mail_res)." rows\n";

if (db_numrows($mail_res)<1) {
	// Nothing to send
	m_exit();
}

$type = db_result($mail_res, 0, 'type');
if (!$table_mapping[$type]) {
	$err .= "Unknown mailing type\n";
	m_exit();
}

$subj = db_result($mail_res, 0, 'subject');
$mail_id = db_result($mail_res, 0, 'id');

//$err .= "Got mail to send: ".$subj."\n";

$sql = "SELECT DISTINCT users.user_id,users.user_name,users.realname,users.email,users.confirm_hash
	FROM $table_mapping[$type]
	WHERE users.user_id>".db_result($mail_res, 0, 'last_userid')."
	AND users.status='A'
	".$cond_mapping[$type]."
	ORDER BY users.user_id";

//$err .= $sql;

// Get next chunk of users to mail
$users_res = db_query($sql);

$err .= "Mailing ".db_numrows($users_res)." users.\n";

// If no more users left, we've finished with this mailing
if ($users_res && db_numrows($users_res)==0) {
	db_query("UPDATE massmail_queue
		SET failed_date=0,finished_date='".time()."'
		WHERE id='$mail_id'");
	m_exit();
}

// These mailing types should include unsubscription info
if ($type=='SITE' || $type=='COMMNTY') {
	$tail = "\r\n==================================================================\r\n"
		   ."You receive this message because you subscribed to $sys_name\r\n"
		   ."site mailing(s). You may opt out from some of them selectively\r\n"
		   ."by logging in to $sys_name and visiting your Account Maintenance\r\n"
		   ."page (http://$sys_default_domain/account/), or disable them altogether\r\n"
		   ."by visiting following link:\r\n"
		   ."<http://$sys_default_domain/account/unsubscribe.php?ch=_%s>\r\n";
}
$body = db_result($mail_res, 0, 'message');

// Actual mailing loop
$compt = 0;
while ($row =& db_fetch_array($users_res)) {
	$compt++;
	util_send_message($row['email'],$subj,$body."\r\n".sprintf( $tail,$row['confirm_hash'] ),'noreply@'.$sys_default_domain );
	$last_userid = $row['user_id'];

	sleep($SLEEP);
}

$sql="UPDATE massmail_queue
		SET failed_date=0,
		last_userid='$last_userid',
		finished_date='".time()."'
		WHERE id='$mail_id'";

db_query($sql);

if (db_error()) {
	$err .= $sql.db_error();
}
$mess = "massmail $compt mails sent";
m_exit($mess);

function m_exit($mess = '') {
	global $err;
	
	if (!cron_remove_lock(__FILE__)) {
		$err .= "Could not remove lock\n";
	}
	if (!cron_entry(6,$mess.$err)) {
		# rely on crond to report the error
		echo "cron_entry error: ".db_error()."\n";
	}
	exit;
}

?>
