<?php
//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net
//
// $Id$
//

/*

	This is the MySQL version of our 
	database connection/querying layer

*/


/**
 *
 *  Connect to the database
 *  Notice the global vars that must be set up
 *  Sets up a global $conn variable which is used 
 *  in other functions in this library
 *
 */

$sys_database_type='mysql';

function db_connect() {
	global $sys_dbhost,$sys_dbuser,$sys_dbpasswd,
		$conn,$conn_update,$sys_db_use_replication,$sys_dbreadhost;

	if ($sys_db_use_replication) {
		//
		//  if configured for replication, $conn is the read-only host
		//  we do not connect to update server until needed
		//
		$conn = @mysql_pconnect($sys_dbreadhost,$sys_dbuser,$sys_dbpasswd);
		$conn_update=@mysql_pconnect($sys_dbhost,$sys_dbuser,$sys_dbpasswd);
	} else {
		$conn = @mysql_pconnect($sys_dbhost,$sys_dbuser,$sys_dbpasswd);
	}
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
	global $QUERY_COUNT,$sys_db_use_replication,$sys_db_is_dirty,
		$sys_dbname,$conn,$conn_update,$sys_dbhost,$sys_dbuser,$sys_dbpasswd;
	$QUERY_COUNT++;

	if ($limit > 0) {
		if (!$offset || $offset < 0) {
			$offset=0;
		}
		$qstring=$qstring." LIMIT $offset,$limit";
	}
	if ($GLOBALS['IS_DEBUG'])
		$GLOBALS['G_DEBUGQUERY'] .= $qstring . "<P><BR>\n";

	//
	//are we configured to try to use replication?
	//
	if ($sys_db_use_replication) {
		//
		//if we haven't yet done an insert/update, 
		//read from the read-only db
		//
		if (!$sys_db_is_dirty && eregi("^( )*(select)",$qstring)) {
			if ($QUERY_COUNT%3==0) {
				// 1/3rd of read queries go to master for now
				return @mysql_db_query($sys_dbname,$qstring,$conn_update);
			} else {
				return @mysql_db_query($sys_dbname,$qstring,$conn);
			}
		} else {
			//must be an update/insert/delete query - go to master server
			$sys_db_is_dirty=true;
			return @mysql_db_query($sys_dbname,$qstring,$conn_update);
		}
	} else {
		return @mysql_db_query($sys_dbname,$qstring,$conn);
	}
}

/**
 *	db_begin()
 *
 *	begin a transaction for databases that support them
 *	may cause unexpected behavior in databases that don't
 */
function db_begin() {
	return db_query("BEGIN WORK");
}

/**
 *      db_commit()
 *
 *      commit a transaction for databases that support them
 *      may cause unexpected behavior in databases that don't
 */
function db_commit() {
	return db_query("COMMIT");
}

/**
 *      db_rollback()
 *
 *      rollback a transaction for databases that support them
 *      may cause unexpected behavior in databases that don't
 */
function db_rollback() {
	return db_query("ROLLBACK");
}

/**
 *
 *  Returns the number of rows in this result set
 *
 *  @param qhandle query result set handle
 *
 */

function db_numrows($qhandle) {
	// return only if qhandle exists, otherwise 0
	if ($qhandle) {
		return @mysql_numrows($qhandle);
	} else {
		return 0;
	}
}

/**
 *
 *  Frees a database result properly 
 *
 *  @param qhandle query result set handle
 *
 */

function db_free_result($qhandle) {
	return @mysql_free_result($qhandle);
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
	return mysql_data_seek($qhandle,$row);
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
	return @mysql_result($qhandle,$row,$field);
}

/**
 *
 *  Returns the number of fields in this result set
 *
 *  @param qhandle query result set handle
 *
 */

function db_numfields($lhandle) {
	return @mysql_numfields($lhandle);
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
	   return @mysql_fieldname($lhandle,$fnumber);
}

/**
 *
 *  Returns the number of rows changed in the last query
 *
 *  @param qhandle query result set handle
 *
 */

function db_affected_rows($qhandle) {
	return @mysql_affected_rows();
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
	return @mysql_fetch_array($qhandle);
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
	return @mysql_insert_id();
}

/**
 *
 *  Returns the last error from the database
 *
 */

function db_error() {
	return @mysql_error();
}

?>
