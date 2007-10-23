#! /usr/bin/php5 -f
<?php
$sys_localinc=getenv('sys_localinc');
if (is_file($sys_localinc)) {
	require($sys_localinc);
} else {
	if (is_file('/etc/gforge/local.inc')) {
		require ('/etc/gforge/local.inc');
	} else {
		if (is_file('../etc/gforge/local.inc')) {
			require('../etc/gforge/local.inc');
		}
	}
}
// database abstraction
require_once('common/include/database.php');

db_connect();

if (!$conn) {
	print "$sys_name Could Not Connect to Database: ".db_error();
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
	$res = db_query("SELECT COUNT(*) AS exists FROM pg_class WHERE relname = 'database_startpoint' AND relkind = 'r'");
	if (!$res) { // db error
		echo "DB-ERROR-1: ".db_error()."\n";
		db_rollback();
		return false;
	}
	if (db_result($res, 0, 'exists') == '0') {
		$res = db_query("CREATE TABLE database_startpoint (db_version varchar(10), db_start_date int)");
		if (!$res) { // db error
			echo "DB-ERROR-2: ".db_error()."\n";
			db_rollback();
			return false;
		}
	}
	$res = db_query("SELECT COUNT(*) AS exists FROM pg_class WHERE relname = 'database_changes' AND relkind = 'r'");
	if (!$res) { // db error
		echo "DB-ERROR-3: ".db_error()."\n";
		db_rollback();
		return false;
	}
	if (db_result($res, 0, 'exists') == '0') {
		$res = db_query("CREATE TABLE database_changes (filename text)");
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
					'3.0b1', '3.0rc2', '3.0', '3.1', '3.21', '3.3', '4.0', '4.0.1', '4.0.2');
	$dates    = array('20001219', '20021001', '20021001', '20021001', '20021206', '20021210', '20021214', '20021230',
					'20030115', '20030304',	'20030510', '20040511', '20031026', '20040108', '20040326', '20041025',
					'20041107', '20041215');

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
	$res = db_query("TRUNCATE TABLE database_startpoint");
	if (!$res) { // db error
		echo "DB-ERROR-5: ".db_error()."\n";
		db_rollback();
		return false;
	}
	$res = db_query("INSERT INTO database_startpoint (db_version, db_start_date) VALUES ('$version', '$date')");
	if (!$res) { // db error
		echo "DB-ERROR-5: ".db_error()."\n";
		db_rollback();
		return false;
	}
	echo "GForge Database Version: $version ($date)\n";
	db_commit();	
}
?>
