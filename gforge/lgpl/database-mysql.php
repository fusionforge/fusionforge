<?php

/**
 *	DATABASE ABSTRACTION LAYER.
 *
 *	Originally written by Tim Perdue, December 1998 
 *	Simply replaces calls to mysql_* with db_*
 *	To achieve basic database portability.
 */
function db_connect() {
	global $sys_dbhost,$sys_dbuser,$sys_dbpasswd, $conn;

	$conn = @mysql_pconnect($sys_dbhost,$sys_dbuser,$sys_dbpasswd);
}

function db_query($qstring,$limit='-1',$offset=0) {
	global $sys_dbname,$conn;

	if ($limit > 0) {
		if (!$offset || $offset < 0) {
			$offset=0;
		}
		$qstring=$qstring." LIMIT $offset,$limit";
	}

	return @mysql_db_query($sys_dbname,$qstring,$conn);
}

function db_begin() {
	return true;
}

function db_commit() {
	return true;
}

function db_rollback() {
	return true;
}

function db_numrows($qhandle) {
	// return only if qhandle exists, otherwise 0
	if ($qhandle) {
		return @mysql_numrows($qhandle);
	} else {
		return 0;
	}
}

function db_free_result($qhandle) {
	return @mysql_free_result($qhandle);
}

function db_reset_result($qhandle,$row=0) {
	return mysql_data_seek($qhandle,$row);
}

function db_result($qhandle,$row,$field) {
	return @mysql_result($qhandle,$row,$field);
}

function db_numfields($lhandle) {
	return @mysql_numfields($lhandle);
}

function db_fieldname($lhandle,$fnumber) {
	return @mysql_fieldname($lhandle,$fnumber);
}

function db_affected_rows($qhandle) {
	return @mysql_affected_rows();
}

function db_fetch_array($qhandle) {
	return @mysql_fetch_array($qhandle);
}

function db_insertid($qhandle,$table_name,$pkey_field_name) {
	return @mysql_insert_id();
}

function db_error() {
	return @mysql_error();
}

?>
