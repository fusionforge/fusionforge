#!/usr/local/bin/php -q
<?php
/**
  *
  * kt_dump.php - Retreive the Kernel Traffic page 
  *
  * This script retrieves the main Kernel Traffic page and stores it
  * in the database.
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id: kt_dump.php,v 1.4 2001/06/13 18:44:09 pfalcon Exp $
  * @author: Darrell Brogdon <dbrogdon@valinux.com>
  *
  */

require ('squal_pre.php');
set_time_limit(60);

$url = 'http://kt.zork.net/kernel-traffic/latest.html';

// Retrieve the file and store the contents in an array.
$data =@ file($url);
if (!sizeof($data)){
	$errors = 'Could not open link: \'' . $url . "'\n";
}

$capt_flag = false;
// Walk through the array looking for elements that indicate the start
// of the page body.
foreach ($data as $key => $fileline) {
	// If this element is the start of the page body then 
	// Start saving data
	if( ereg("Kernel Traffic \#", $fileline) ) {
		$capt_flag = true;
	}

	if( eregi('<li><a href="latest_print.epl">Printer-Friendly Format</a>',$fileline) ) { 
		$data[$key] = '<li><a href="' . $url . 'latest_print.epl">Printer-Friendly Format</a>';
	}

	if( eregi('<li><a href="latest.epl#stats">Mailing List Stats For This Week</a>',$fileline) ) {
		$data[$key] = '<li><a href="#stats">Mailing List Stats For This Week</a>';
	}

	// Stop saving data
	if( eregi('<hr', $fileline) ) {
		$capt_flag = false;
	}

	if (!$capt_flag) {
	// Remove the current line from the array
		unset($data[$key]);
	} 
}

if(!count($data)) {
	$errors .= "Data size is too small.\n";
} else {
	db_begin();

	$sql = 'DELETE FROM kernel_traffic';
	db_query($sql);

	$sql = 'INSERT INTO kernel_traffic (kt_data) VALUES(\'' . addslashes(implode("", $data)) . '\')';
	$res = db_query($sql);

	if( !$res || db_affected_rows($res) < 1 ) {
		db_rollback();
		$errors .= 'SQL ERROR: ' . db_error();
	} else {
		db_commit();
	}
}

if ($errors) {
	$msg = "The following errors ocurred with kt_dump.php:\n     - " . $errors;
	util_send_mail('alexandria-staff@lists.sourceforge.net','kt_dump Failed',$msg);
}
?>
