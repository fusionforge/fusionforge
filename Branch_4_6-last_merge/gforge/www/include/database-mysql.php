<?php
/**
 * MySQL database connection/querying layer
 *
 * SourceForge: Breaking Down the Barriers to Open Source Development
 * Copyright 1999-2001 (c) VA Linux Systems
 * http://sourceforge.net
 *
 * @version   $Id$
 */

/**
 * System-wide database type
 *
 * @var	constant		$sys_database_type
 */
$sys_database_type='mysql';

/**
 *  db_connect() -  Connect to the database
 *
 *  Notice the global vars that must be set up
 *  Sets up a global $conn variable which is used 
 *  in other functions in this library
 */
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
 *  db_query() - Query the database
 *
 *  @param		string	SQL statement
 *  @param		int		How many rows do you want returned
 *  @param		int		Of matching rows, return only rows starting here
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
		$GLOBALS['G_DEBUGQUERY'] .= $qstring . "<p><br />\n";

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
 *	db_begin() - Begin a transaction
 *
 *	Begin a transaction for databases that support them
 *	may cause unexpected behavior in databases that don't
 */
function db_begin() {
	return db_query("BEGIN WORK");
}

/**
 * db_commit() - Commit a transaction
 *
 * Commit a transaction for databases that support them
 * may cause unexpected behavior in databases that don't
 */
function db_commit() {
	return db_query("COMMIT");
}

/**
 * db_rollback() - Roll back a transaction
 *
 * Rollback a transaction for databases that support them
 * may cause unexpected behavior in databases that don't
 */
function db_rollback() {
	return db_query("ROLLBACK");
}

/**
 *  db_numrows() - Returns the number of rows in this result set
 *
 *  @param		string	Query result set handle
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
 *  db_free_result() - Frees a database result properly 
 *
 *  @param		string	Query result set handle
 */
function db_free_result($qhandle) {
	return @mysql_free_result($qhandle);
}

/**
 *  db_reset_result() - Reset a result set.
 *
 *  Reset is useful for db_fetch_array sometimes you need to start over
 *
 *  @param		string	Query result set handle
 *  @param		int		Row number
 */
function db_reset_result($qhandle,$row=0) {
	return mysql_data_seek($qhandle,$row);
}

/**
 *  db_result() - Returns a field from a result set
 *
 *  @param		string	Query result set handle
 *  @param		int		Row number
 *  @param		string	Field name
 */
function db_result($qhandle,$row,$field) {
	return @mysql_result($qhandle,$row,$field);
}

/**
 *  db_numfields() - Returns the number of fields in this result set
 *
 *  @param		string	Query result set handle
 */
function db_numfields($lhandle) {
	return @mysql_numfields($lhandle);
}

/**
 *  db_fieldname() - Returns the number of rows changed in the last query
 *
 *  @param		string	Query result set handle
 *  @param		int		Column number
 */
function db_fieldname($lhandle,$fnumber) {
	   return @mysql_fieldname($lhandle,$fnumber);
}

/**
 *  db_affected_rows() - Returns the number of rows changed in the last query
 *
 *  @param		string	Query result set handle
 */
function db_affected_rows($qhandle) {
	return @mysql_affected_rows();
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
	return @mysql_fetch_array($qhandle);
}

/**
 *  db_insertid() - Returns the last primary key from an insert
 *
 *  @param		string	Query result set handle
 *  @param		string	Is the name of the table you inserted into
 *  @param		string	Is the field name of the primary key
 */
function db_insertid($qhandle,$table_name,$pkey_field_name) {
	return @mysql_insert_id();
}

/**
 *  db_error() - Returns the last error from the database
 */
function db_error() {
	return @mysql_error();
}

?>
