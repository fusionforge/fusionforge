<?php
/**
 * DATABASE ABSTRACTION LAYER.
 *
 * Originally written by Tim Perdue, December 1998
 * Makes postgresql access API identical to mysql_* api
 * to achieve basic database portability.
 *
 * This file is part of GForge.
 *
 * GForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with GForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  US
 */

$sys_db_row_pointer=array(); //current row for each result set

function db_connect() {
	global $sys_dbhost,$sys_dbuser,$sys_dbpasswd,$conn, $sys_dbname;

	$conn = @pg_pconnect("user=$sys_dbuser dbname=$sys_dbname host=$sys_dbhost password=$sys_dbpasswd"); 
}

function db_query($qstring,$limit='-1',$offset=0) {
	global $conn;

	if ($limit > 0) {
		if (!$offset || $offset < 0) {
			$offset=0;
		}
		$qstring=$qstring." LIMIT $limit OFFSET $offset";
	}

	return @pg_exec($conn,$qstring);
}

function db_begin() {
	return db_query("BEGIN WORK");
}

function db_commit() {
	return db_query("COMMIT");
}

function db_rollback() {
	return db_query("ROLLBACK");
}

function db_numrows($qhandle) {
	return @pg_numrows($qhandle);
}

function db_free_result($qhandle) {
	return @pg_freeresult($qhandle);
}

function db_reset_result($qhandle,$row=0) {
	global $sys_db_row_pointer;
	return $sys_db_row_pointer[$qhandle]=$row;
}

function db_result($qhandle,$row,$field) {
	return @pg_result($qhandle,$row,$field);
}

function db_numfields($lhandle) {
	return @pg_numfields($lhandle);
}

function db_fieldname($lhandle,$fnumber) {
	return @pg_fieldname($lhandle,$fnumber);
}

function db_affected_rows($qhandle) {
	return @pg_cmdtuples($qhandle);
}

function db_fetch_array($qhandle) {
	global $sys_db_row_pointer;
	$sys_db_row_pointer[$qhandle]++;
	return @pg_fetch_array($qhandle,($sys_db_row_pointer[$qhandle]-1));
}

function db_insertid($qhandle,$table_name,$pkey_field_name) {
	$sql="SELECT max($pkey_field_name) AS id FROM $table_name";
	$res=db_query($sql);
	if (db_numrows($res) >0) {
		return db_result($res,0,'id');
	} else {
		return 0;
	}
}

function db_error() {
	global $conn;
	return @pg_errormessage($conn);
}

?>
