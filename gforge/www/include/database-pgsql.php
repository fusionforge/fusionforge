<?php
/**
 * PostgreSQL database connection/querying layer
 *
 * ALPHA VERSION - not debugged!!
 *
 * SourceForge: Breaking Down the Barriers to Open Source Development
 * Copyright 1999-2001 (c) VA Linux Systems
 * http://sourceforge.net
 *
 * @version   $Id$
 */

/**
 * Database connection handle
 *
 * Current row for each result set 
 *
 * @var	array	$sys_db_row_pointer
 */
$sys_db_row_pointer=array(); 


/**
 *  db_connect() - Connect to the database
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
 *  db_query() - Query the database
 *
 *  @param		string	SQL statement
 *  @param		int		How many rows do you want returned
 *  @param		int		Of matching rows, return only rows starting here
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
 * db_begin() - Begin a transaction
 */
function db_begin() {
	return db_query("BEGIN WORK");
}

/**
 * db_commit() - Commit a transaction
 */
function db_commit() {
	return db_query("COMMIT");
}

/**
 * db_rollback() - Rollback a transaction
 */
function db_rollback() {
	return db_query("ROLLBACK");
}

/**
 *	db_numrows() - Returns the number of rows in this result set
 *
 *	@param		string	Query result set handle
 */
function db_numrows($qhandle) {
	return @pg_numrows($qhandle);
}

/**
 * db_free_result() -  Frees a database result properly 
 *
 * @param		string	Query result set handle
 */
function db_free_result($qhandle) {
	return @pg_freeresult($qhandle);
}

/**
 * db_reset_result() - Reset a result set
 *
 * Reset is useful for db_fetch_array
 * sometimes you need to start over
 *
 * @param		string	Query result set handle
 * @param		int		Row number
 */
function db_reset_result($qhandle,$row=0) {
	global $sys_db_row_pointer;
	return $sys_db_row_pointer[$qhandle]=$row;
}

/**
 *  db_result() - Returns a field from a result set
 *
 *  @param		string	Query result set handle
 *  @param		int		Row number
 *  @param		string	Field name
 */
function db_result($qhandle,$row,$field) {
	return @pg_result($qhandle,$row,$field);
}

/**
 *  db_numfields() - Returns the number of fields in this result set
 *
 *  @param		string	Query result set handle
 */
function db_numfields($lhandle) {
	return @pg_numfields($lhandle);
}

/**
 *  db_fieldname() - Returns the number of rows changed in the last query
 *
 *  @param		string	Query result set handle
 *  @param		int		Column number
 */
function db_fieldname($lhandle,$fnumber) {
	return @pg_fieldname($lhandle,$fnumber);
}

/**
 *  db_affected_rows() - Returns the number of rows changed in the last query
 *
 *  @param		string	Query result set handle
 */
function db_affected_rows($qhandle) {
	return @pg_cmdtuples($qhandle);
}

/**
 *  db_fetch_array() - Fetch an array
 *
 *  Returns an associative array from 
 *  the current row of this database result
 *  Use db_reset_result to seek a particular row
 *
 *  @param		string	Query result set handle
 */
function db_fetch_array($qhandle) {
	global $sys_db_row_pointer;
	$sys_db_row_pointer[$qhandle]++;
	return @pg_fetch_array($qhandle,($sys_db_row_pointer[$qhandle]-1));
}

/**
 *  db_insertid() - Returns the last primary key from an insert
 *
 *  @param		string	Query result set handle
 *  @param		string	Is the name of the table you inserted into
 *  @param		string	Is the field name of the primary key
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
 *  db_error() - Returns the last error from the database
 */
function db_error() {
	global $conn;
	return @pg_errormessage($conn);
}

?>
