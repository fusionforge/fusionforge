<?php
/**
 * MySQL database connection/querying layer
 *
 * SourceForge: Breaking Down the Barriers to Open Source Development
 * Copyright 1999-2001 (c) VA Linux Systems
 * http://sourceforge.net
 *
 * @version   $Id: database-mysql.php 1423 2003-01-10 14:44:28Z bigdisk $
 */

/**
 * System-wide database type
 *
 * @var	constant		$sys_database_type
 */

/**
 *  db_connect() -  Connect to the database
 *
 *  Notice the global vars that must be set up
 *  Sets up a global $conn variable which is used 
 *  in other functions in this library
 */
function db_connect() {
	global $sys_dbhost, $sys_dbuser, $sys_dbpasswd, $conn;

	db_log_entry('db_connect', NULL);

	db_log_dbentry('mysqli_connect',"$sys_dbhost, $sys_dbuser, $sys_dbpasswd");
	$conn = @mysqli_connect($sys_dbhost, $sys_dbuser, $sys_dbpasswd);
	db_log_dbexit('mysqli_connect',"$conn");

	//
	//	Now map the physical database connections to the
	//	"virtual" list that is used to distribute load in db_query()
	// Define dummy values to eliminate log messages
	//
	define('SYS_DB_PRIMARY', 0);
	define('SYS_DB_STATS', 1);
	define('SYS_DB_TROVE', 2);
	define('SYS_DB_SEARCH', 3);

	db_log_exit('db_connect');

	#return $conn;
}

/**
 *  db_query() - Query the database
 *
 *  @param		string	SQL statement
 *  @param		int		How many rows do you want returned
 *  @param		int		Of matching rows, return only rows starting here
 */
function db_query($qstring, $limit = '-1', $offset = 0) {
	global $sys_dbname, $conn;

	db_log_entry('db_query',"$qstring, $limit, $offset");

	if ($limit > 0) {
		if (!$offset || $offset < 0) {
			$offset=0;
		}
		$qstring=$qstring." LIMIT $offset,$limit";
	}
//	if ($GLOBALS['IS_DEBUG'])
//		$GLOBALS['G_DEBUGQUERY'] .= $qstring . "<p><br />\n";

	db_log_dbentry('mysqli_select_db',"$conn, $sys_dbname");
	if (!mysqli_select_db($conn, $sys_dbname)) {
		db_log_dbexit('mysqli_select_db',false);
		db_log_exit('db_query');
		return NULL;
	}
	db_log_dbexit('mysqli_select_db',true);

	db_log_dbentry('mysqli_query',"$conn, $sys_dbname");
	$res = mysqli_query($conn, $qstring);
	db_log_dbexit('mysqli_query',$res);

	db_log_exit('db_query',"$res");
	return $res;
}

/**
 *  db_mquery() - Query the database supporting multi-statements
 *
 *  @param		string	SQL statement
 */
function db_mquery($qstring) {
	global $sys_dbname, $conn;

//	if ($GLOBALS['IS_DEBUG'])
//		$GLOBALS['G_DEBUGQUERY'] .= $qstring . "<p><br />\n";

	db_log_entry('db_mquery',"$qstring");

	db_log_dbentry('mysqli_select_db',"$conn, $sys_dbname");
	if (!mysqli_select_db($conn, $sys_dbname)) {
		db_log_dbexit('mysqli_select_db',false);
		$err = mysqli_error($conn);
		if ($err) {
			db_log('DB Error = '.$err);
		}
		db_log_exit('db_mquery',NULL);
		return NULL;
	}
	db_log_dbexit('mysqli_select_db', true);

	db_log_dbentry('mysqli_multi_query',"$conn, $qstring");
	if (!mysqli_multi_query($conn, $qstring)) {
		db_log_dbexit('mysqli_multi_query','false');
		$err = mysqli_error($conn);
		if ($err) {
			db_log('DB Error = '.$err);
		}
		db_log_exit('db_mquery');
		return NULL;
	}
	db_log_dbexit('mysqli_multi_query',true);

	db_log_dbentry('mysqli_store_result',"$conn");
	if ($res = mysqli_store_result($conn)) {
		db_log_dbexit('mysqli_store_result',"$res");
		db_log_exit('db_mquery',"$res");
		return $res;
	} else {
		$err = mysqli_error($conn);
		if ($err) {
			db_log('DB Error = '.$err);
		}
		db_log_dbexit('mysqli_store_result');
	}
	db_log_exit('db_mquery',true);
	return true;
}

/**
 *  db_next_result() - Get the next result from query with multiple statements.
 *
 *  @param		string	SQL statement
 *  @param		int		How many rows do you want returned
 *  @param		int		Of matching rows, return only rows starting here
 */
function db_next_result() {
	global $conn;

	db_log_entry('db_next_result',NULL);

	db_log_dbentry('mysqli_next_result',"$conn");
	$ret = mysqli_next_result($conn);
	db_log_dbexit('mysqli_next_result',"$ret");
	$err = mysqli_error($conn);
	if ($err) {
		db_log('DB Error = '.$err);
	}

	if ($ret) {
		db_log_dbentry('mysqli_store_result',"$conn");
		$res = mysqli_store_result($conn);
		db_log_dbexit('mysqli_store_result',"$res");
		if (!$res) {
			$res = 1;
		}
	} else {
		$err = mysqli_error($conn);
		if ($err) {
			db_log('DB Error = '.$err);
		}
		$res = NULL;
	}
	db_log_exit('db_next_result',$res);
	return $res;
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
		return @mysqli_num_rows($qhandle);
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
	db_log_entry('db_free_result',"$qhandle");
	if (!is_object($qhandle)) {
		db_log_exit('db_free_result');
		return;
	}
	db_log_dbentry('mysqli_free_result',"$qhandle");
	$res = mysqli_free_result($qhandle);
	db_log_dbexit('mysqli_free_result',"$res");
	db_log_exit('db_free_result');
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
	return mysqli_data_seek($qhandle,$row);
}

/**
 *  db_result() - Returns a field from a result set
 *
 *  @param		string	Query result set handle
 *  @param		int		Row number
 *  @param		string	Field name
 */
function db_result($qhandle,$row,$field) {

	if (!mysqli_data_seek($qhandle,$row)) {
		return NULL;
	}

	$row_data = mysqli_fetch_array($qhandle, MYSQLI_BOTH);
	if (!$row_data) {
		return NULL;
	}
	return $row_data[$field];
}

/**
 *  db_numfields() - Returns the number of fields in this result set
 *
 *  @param		string	Query result set handle
 */
function db_numfields($lhandle) {
	return mysqli_num_fields($lhandle);
}

/**
 *  db_fieldname() - Returns the name of a field in this result set
 *
 *  @param		string	Query result set handle
 *  @param		int		Column number
 */
function db_fieldname($lhandle,$fnumber) {
	$fieldinfo=mysqli_fetch_field_direct($lhandle,$fnumber);
	if ($fieldinfo) {
		return $fieldinfo->name;
	} else {
		return NULL;
	}
}

/**
 *  db_affected_rows() - Returns the number of rows changed in the last query
 *
 *  @param		string	Query result set handle
 */
function db_affected_rows() {
	global $conn;

	return mysqli_affected_rows($conn);
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
	return @mysqli_fetch_array($qhandle);
}

/**
 *  db_insertid() - Returns the last primary key from an insert
 *
 *  @param		string	Query result set handle
 *  @param		string	Is the name of the table you inserted into
 *  @param		string	Is the field name of the primary key
 */
function db_insertid($qhandle,$table_name,$pkey_field_name) {
	global $conn;

	return mysqli_insert_id($conn);
}

/**
 *  db_error() - Returns the last error from the database
 */
function db_error() {
	global $conn;

	return mysqli_error($conn);
}

/**
 *	system_cleanup() - In the future, we may wish to do a number 
 *	of cleanup functions at script termination.
 *
 *	For now, we just abort any in-process transaction.
 */
function system_cleanup() {
	global $_sys_db_transaction_level;
	if ($_sys_db_transaction_level > 0) {
		echo "Open transaction detected!!!";
		db_query("ROLLBACK");
	}
}

function db_drop_table_if_exists ($tn) {
	$sql = "DROP TABLE IF EXISTS $tn;";
	$rel = db_query ($sql);
	echo db_error();
}

function db_drop_sequence_if_exists ($tn) {
}

function db_log($message) {
	global $fhlog;

	if (!$fhlog) {
		$fhlog = fopen('/tmp/db.log', 'a');
	}

	if ($fhlog) {
		fwrite($fhlog, $message);
		fflush($fhlog);
	}
}

function db_log_entry($func, $args) {
	db_log("\nEntered ".$func.'('.$args.")\n");
}

function db_log_exit($func, $result = NULL) {
	db_log('Exited '.$func.' returned '.$result."\n");
}

function db_log_dbentry($func, $args) {
	db_log('Entered '.$func.'('.$args.")\n");
}

function db_log_dbexit($func, $result = NULL) {
	db_log('Exited '.$func.' returned '.$result."\n");
	if (!$result) {
		$err = db_error();
		if ($err) {
			db_log('Database error = '.$err."\n");
		}
	}
}

?>
