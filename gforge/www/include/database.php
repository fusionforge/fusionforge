<?php
//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net
//
// $Id: database.php,v 1.57 2000/12/07 18:31:26 tperdue Exp $
//

/*

	This is the PostgreSQL version of our 
	database connection/querying layer

*/

//$conn - database connection handle
$sys_db_row_pointer=array(); //current row for each result set


/**
 *
 *  Connect to the database
 *  Notice the global vars that must be set up
 *  Sets up a global $conn variable which is used 
 *  in other functions in this library
 *
 */

function db_connect() {
	global $sys_dbhost,$sys_dbuser,$sys_dbpasswd,$conn,$sys_dbname;
	$conn = @pg_pconnect("user=$sys_dbuser dbname=$sys_dbname host=$sys_dbhost password=$sys_dbpasswd"); 
	#return $conn;
}

/**
 *
 *  Query the database
 *
 *  @param qstring - SQL statement
 *  @param limit - how many rows do you want returned
 *  @param offset - of matching rows, return only rows starting here
 *
 */

function db_query($qstring,$limit='-1',$offset=0) {
	global $QUERY_COUNT;
	$QUERY_COUNT++;

	if ($limit > 0) {
		if (!$offset || $offset < 0) {
			$offset=0;
		}
		$qstring=$qstring." LIMIT $limit OFFSET $offset";
	}

	if ($GLOBALS['IS_DEBUG']) 
		$GLOBALS['G_DEBUGQUERY'] .= $qstring . "<P><BR>\n";
	global $conn;
	return @pg_exec($conn,$qstring);
}

/**
 *      db_begin()
 *
 *      begin a transaction
 */
function db_begin() {
	return db_query("BEGIN WORK");
}

/**
 *      db_commit()
 *
 *      commit a transaction
 */
function db_commit() {
	return db_query("COMMIT");
}

/**
 *      db_rollback()
 *
 *      rollback a transaction
 */
function db_rollback() {
	return db_query("ROLLBACK");
}

/**
 *	db_numrows()
 *
 *	Returns the number of rows in this result set
 *	@param qhandle query result set handle
 */

function db_numrows($qhandle) {
	return @pg_numrows($qhandle);
}

/**
 *
 *  Frees a database result properly 
 *
 *  @param qhandle query result set handle
 *
 */

function db_free_result($qhandle) {
	return @pg_freeresult($qhandle);
}

/**
 *
 *  Reset is useful for db_fetch_array
 *  sometimes you need to start over
 *
 *  @param qhandle query result set handle
 *  @param row - integer row number
 *
 */

function db_reset_result($qhandle,$row=0) {
	global $sys_db_row_pointer;
	return $sys_db_row_pointer[$qhandle]=$row;
}

/**
 *
 *  Returns a field from a result set
 *
 *  @param qhandle query result set handle
 *  @param row - integer row number
 *  @param field - text field name
 *
 */

function db_result($qhandle,$row,$field) {
	return @pg_result($qhandle,$row,$field);
}

/**
 *
 *  Returns the number of fields in this result set
 *
 *  @param qhandle query result set handle
 *
 */

function db_numfields($lhandle) {
	return @pg_numfields($lhandle);
}

/**
 *
 *  Returns the number of rows changed in the last query
 *
 *  @param qhandle - query result set handle
 *  @param fnumber - column number
 *
 */

function db_fieldname($lhandle,$fnumber) {
	return @pg_fieldname($lhandle,$fnumber);
}

/**
 *
 *  Returns the number of rows changed in the last query
 *
 *  @param qhandle query result set handle
 *
 */

function db_affected_rows($qhandle) {
	return @pg_cmdtuples($qhandle);
}

/**
 *
 *  Returns an associative array from 
 *  the current row of this database result
 *  Use db_reset_result to seek a particular row
 *
 *  @param qhandle query result set handle
 *
 */

function db_fetch_array($qhandle) {
	global $sys_db_row_pointer;
	$sys_db_row_pointer[$qhandle]++;
	return @pg_fetch_array($qhandle,($sys_db_row_pointer[$qhandle]-1));
}

/**
 *
 *  Returns the last primary key from an insert
 *
 *  @param qhandle query result set handle
 *  @param table_name is the name of the table you inserted into
 *  @param pkey_field_name is the field name of the primary key
 *
 */

function db_insertid($qhandle,$table_name,$pkey_field_name) {
	$oid=@pg_getlastoid($qhandle);
	if ($oid) {
		$sql="SELECT $pkey_field_name AS id FROM $table_name WHERE oid='$oid'";
		//echo $sql;
		$res=db_query($sql);
		if (db_numrows($res) >0) {
			return db_result($res,0,'id');
		} else {
		//	echo "No Rows Matched";
		//	echo db_error();
			return 0;
		}
	} else {
//		echo "No OID";
//		echo db_error();
		return 0;
	}
}

/**
 *
 *  Returns the last error from the database
 *
 */

function db_error() {
	global $conn;
	return @pg_errormessage($conn);
}

function db_drop_table_if_exists ($tn) {
  $sql = "SELECT COUNT(*) FROM pg_class WHERE relname='$tn';";
  $rel = db_query($sql);
  echo db_error();
  $count = db_result($rel,0,0);
  if ($count != 0) {
    $sql = "DROP TABLE $tn;";
    $rel = db_query ($sql);
    echo db_error();
  }
}
?>
