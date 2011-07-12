#! /usr/bin/php
<?php

require_once dirname(__FILE__).'/../www/env.inc.php';
require_once $gfcommon.'include/pre.php';

if (!$gfconn) {
	print forge_get_config ('forge_name')." Could Not Connect to Database: ".db_error();
	exit;
}

// HERE BEGINS THE ACTUAL SCRIPT

if (count($argv) < 2) {
	echo "Usage: startpoint.php [version]\n";
	check_version();
} else {
	$date = 0;
	$version = $argv[1];
	if (!check_tables()) {
		echo "ERROR: Could not check tables\n";
		exit();
	} else {
		$date = check_version($version);

		if ($date != 0) {
			set_version($version, $date);
		}
	}
}

function check_tables() {
	db_begin();
	$res = db_query_params ('SELECT COUNT(*) AS exists FROM pg_class WHERE relname = $1 AND relkind = $2',
			array('database_startpoint',
			'r')) ;

	if (!$res) { // db error
		echo "DB-ERROR-1: ".db_error()."\n";
		db_rollback();
		return false;
	}
	if (db_result($res, 0, 'exists') == '0') {
		$res = db_query_params ('CREATE TABLE database_startpoint (db_version varchar(10), db_start_date int)',
			array()) ;

		if (!$res) { // db error
			echo "DB-ERROR-2: ".db_error()."\n";
			db_rollback();
			return false;
		}
	}
	$res = db_query_params ('SELECT COUNT(*) AS exists FROM pg_class WHERE relname = $1 AND relkind = $2',
			array('database_changes',
			'r')) ;

	if (!$res) { // db error
		echo "DB-ERROR-3: ".db_error()."\n";
		db_rollback();
		return false;
	}
	if (db_result($res, 0, 'exists') == '0') {
		$res = db_query_params ('CREATE TABLE database_changes (filename text)',
			array()) ;

		if (!$res) { // db error
			echo "DB-ERROR-4: ".db_error()."\n";
			db_rollback();
			return false;
		}
	}
	db_commit();
	return true;
}

function check_version($version = 0) {
	$version  = trim($version);
	$date = 0;
	$versions = array('2.5', 'sfee3.3', 'sfee3.0', '2.6','3.0pre5', '3.0pre6', '3.0pre7', '3.0pre8', '3.0pre9',
					'3.0b1', '3.0rc2', '3.0', '3.1', '3.21', '3.3', '4.0', '4.0.1', '4.0.2', '4.5.0.1','4.5.11',
					'4.6', '4.7', '4.7.3', '4.8rc2', '4.8', '4.8.1','4.8.2');
	$dates    = array('20001219', '20021001', '20021001', '20021001', '20021206', '20021210', '20021214', '20021230',
					'20030115', '20030304',	'20030510', '20040511', '20031026', '20040108', '20040326', '20041025',
					'20041107', '20041215','20050803','20050810',
					'20070924', '20070924', '20070924', '20090402', '20090402', '20090402','20090402');

	if (in_array($version, $versions)) {
		$date = $dates[array_search($version, $versions)];
	} else {
		if ($version != 0) {
			echo "\nInvalid version!";
		}
		echo "\nUse one of the following versions:\n";
		foreach ($versions as $nbr) {
			echo "$nbr\n";
		}
	}
	return $date;
}

function set_version($version, $date) {
	db_begin();
	$res = db_query_params ('TRUNCATE TABLE database_startpoint',
			array()) ;

	if (!$res) { // db error
		echo "DB-ERROR-5: ".db_error()."\n";
		db_rollback();
		return false;
	}
	$res = db_query_params ('INSERT INTO database_startpoint (db_version, db_start_date) VALUES ($1, $2)',
			array($version,
			$date)) ;

	if (!$res) { // db error
		echo "DB-ERROR-5: ".db_error()."\n";
		db_rollback();
		return false;
	}
	echo "FusionForge Database Version: $version ($date)\n";
	db_commit();
}
?>
