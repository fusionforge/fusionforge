#! /usr/bin/php
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
 * Copyright 2010, Roland Mas
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

require dirname(__FILE__).'/../www/env.inc.php';
require_once $gfcommon.'include/pre.php';
require $gfcommon.'include/cron_utils.php';

$err='';

if (!cron_create_lock(__FILE__)) {
	$err = "Massmail already running...exiting";
		if (!cron_entry(6,$err)) {
			// rely on crond to report the error
			echo "cron_entry error: ".db_error()."\n";
		}
	exit();
}

// Pause between messages, sec
$SLEEP = 1;

$all_users = user_get_active_users () ;
sortUserList ($all_users, 'id') ;

$mail_res = db_query_params ('SELECT *
	FROM massmail_queue
	WHERE finished_date=0
	ORDER BY queued_date',
			array()) ;


/* If there was error, notify admins, but don't be pesky */
if (!$mail_res) {
	$err .= "cannot execute query to select pending mailings: ".db_error()."\n";
	$hrs = time()/(60*60);
	// Send reminder every second day at 11am
	if (($hrs%24)==11 && (($hrs/24)%2)==1) {

		util_send_message(
			forge_get_config('admin_email'),
			"ATT: Problems with massmail cron script",
			"This is automatically generated message from\n
the mass mailing cron script of ".forge_get_config ('forge_name')."\n
installation. There was error querying massmail_queue\n
database table. Please take appropriate actions.\n"
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
if (!$type) {
	$err .= "Unknown mailing type\n";
	m_exit();
}

$subj = db_result($mail_res, 0, 'subject');
$mail_id = db_result($mail_res, 0, 'id');
$body =  db_result($mail_res, 0, 'message');
//$err .= "Got mail to send: ".$subj."\n";

$filtered_users = array () ;

foreach ($all_users as $user) {
	$process = false ;
	switch ($type) {
	case 'ALL':
		$process = true ;
		break;
	case 'SITE':
		$process = $user->getMailingPrefs('site') ;
		break;
	case 'COMMNTY':
		$process = $user->getMailingPrefs('va') ;
		break;
	case 'DVLPR':
		$process = count ($user->getGroups()) ;
		break;
	case 'ADMIN':
		foreach ($user->getGroups(false) as $g) {
			if (forge_check_perm_for_user ($user,'project_admin',$g->getID())) {
				$process = true ;
				break ;
			}
		}
		break;
	case 'SFDLVPR':
		$process = forge_check_global_perm_for_user ($user,'forge_admin') ;
		break;
	}
	if ($process) {
		$filtered_users[] = $user ;
	}
}

$err .= "Mailing ".count($filtered_users)." users.\n";

// If no more users left, we've finished with this mailing
if (count ($filtered_users)==0) {
	db_query_params ('UPDATE massmail_queue SET failed_date=0,finished_date=$1 WHERE id=$2',
			 array(time(),
			       $mail_id));
	m_exit();
}

// Actual mailing loop
$compt = 0;
foreach ($filtered_users as $user) {
	$compt++;
	if ($type=='SITE' || $type=='COMMNTY') {
		$tail = "\r\n==================================================================\r\n" ;
		$tail .= sprintf (_('You receive this message because you subscribed to %1$s
site mailing(s). You may opt out from some of them selectively
by logging in to %1$s and visiting your Account Maintenance
page (%2$s), or disable them altogether
by visiting following link:
<%3$s>
'), 
				  forge_get_config ('forge_name'), 
				  util_make_url('/account/'),
				  util_make_url('/account/unsubscribe.php?ch=_'.$user->getConfirmHash())) ;
	} else {
		$tail = "" ;
	}
	util_send_message($user->getEmail(),$subj, $body."\r\n".$tail,'noreply@'.forge_get_config('web_host'));
	$last_userid = $user->getID();

	sleep($SLEEP);
}

db_query_params ('UPDATE massmail_queue SET failed_date=0, last_userid=$1, finished_date=$2 WHERE id=$3',
		 array($last_userid,
		       time (),
		       $mail_id));

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
		// rely on crond to report the error
		echo "cron_entry error: ".db_error()."\n";
	}
	exit;
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
