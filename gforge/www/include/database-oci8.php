<?php
/**
 * Oracle 8i database connection/querying layer
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
 * An array of an array of associative arrays
 * containing row data from queries (3D array)
 *
 * Say that 50 times backwards while standing on your head and you
 * get a free SourceForge cookie!
 *
 * @var	array	$sys_db_oci_commit_mode
 */
$sys_db_oci_commit_mode='OCI_COMMIT_ON_SUCCESS';

/**
 *  db_connect() - Connect to the database
 *
 *  Notice the global vars that must be set up
 *  Sets up a global $conn variable which is used 
 *  in other functions in this library
 */
function db_connect() {
	global $sys_dbhost,$sys_dbuser,$sys_dbpasswd,$conn,$sys_dbname;
	$conn = @ocilogon($sys_dbuser,$sys_dbpasswd,$sys_dbname);
	#return $conn;
}

/**
 *  db_query() - Query the database
 *
 *  NOTE - the OCI version of this may be somewhat inefficient
 *  for large result sets (hundreds or thousands of rows selected)
 *  However - most queries are returning 25-50 rows
 *
 *  @param		string	SQL statement
 *  @param		int		How many rows do you want returned
 *  @param		int		Of matching rows, return only rows starting here
 */
function db_query($qstring,$limit='-1',$offset=0) {
	global $QUERY_COUNT,$sys_db_results,$sys_db_row_pointer,$sys_db_oci_commit_mode;
	$QUERY_COUNT++;

	$stmt=@ociparse($conn,$qstring);
	if (!$stmt) {
		return 0;
	} else {
		if ($limit > 0) {
			if (!$offset || $offset < 0) {
				$offset=0;
			}
		}

		$res=@ociexecute($stmt,$sys_db_oci_commit_mode);

		if (!$res) {
			return 0;
		} else {
			//if offset, seek to starting point
			//potentially expensive if large offset
			//however there is no data_seek feature AFAICT
			$more_data=true;
			if ($offset > 0) {
				for ($i=0; $i<$offset; $i++) {
					//burn them off
					@ocifetchinto($res,$x);
					if (!$x[1]) {
						//if no data be returned
						//get out of loop
						$more_data=false;
						break;
					}
				}
			}

			$i=0;
			while ($more_data) {
				$i++;
				@ocifetchinto($res,$x,'OCI_ASSOC+OCI_RETURN_NULLS+OCI_RETURN_LOBS');
				$sys_db_results[$res][$i-1]=$x;

				//see if data is being returned && we are 
				//still within the requested $limit
				if (count($x) < 1 || (($limit > 0) && ($i >= $limit))) {
					$more_data=false;
				}
			}
			$sys_db_row_pointer[$res]=0;
			return $res;
		}
	}
}

/**
 * db_begin() - Begin a transaction
 */
function db_begin() {
	global $sys_db_oci_commit_mode;
	$sys_db_oci_commit_mode='OCI_DEFAULT';
}

/**
 * db_commit() - Commit a transaction
 */
function db_commit() {
	global $sys_db_oci_commit_mode,$conn;
	$sys_db_oci_commit_mode='OCI_COMMIT_ON_SUCCESS';
	return ocicommit($conn);
}

/**
 * db_rollback() - Rollback a transaction
 */
function db_rollback() {
	global $sys_db_oci_commit_mode,$conn;
	$sys_db_oci_commit_mode='OCI_COMMIT_ON_SUCCESS';
	return ocirollback($conn);
}

/**
 *  db_numrows() - Returns the number of rows in this result set
 *
 *  @param		string	Query result set handle
 */
function db_numrows($qhandle) {
	global $sys_db_results;
	// return only if qhandle exists, otherwise 0
	if ($qhandle) {
		return @count($sys_db_results[$qhandle]);
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
	global $sys_db_results;
	unset($sys_db_results[$qhandle]);
	return @ocifreestatement($qhandle);
}

/**
 *  db_reset_result() - Reset a result set
 *
 *  Reset is useful for db_fetch_array
 *  sometimes you need to start over
 *
 *  @param		string	Query result set handle
 *  @param		int		Row number
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
	global $sys_db_results;
	return $sys_db_results[$qhandle][$row]["$field"];
}

/**
 *  db_numfields() - Returns the number of fields in this result set
 *
 *  @param		sting	Query result set handle
 */
function db_numfields($lhandle) {
	return @ocinumcols($lhandle);
}

/**
 *  db_fieldname() - Returns the number of rows changed in the last query
 *
 *  @param		string	Query result set handle
 *  @param		int		Column number
 */
function db_fieldname($lhandle,$fnumber) {
	   return @ocicolumnname($lhandle,$fnumber);
}

/**
 *  db_affected_rows() - Returns the number of rows changed in the last query
 *
 *  @param		string	Query result set handle
 */
function db_affected_rows($qhandle) {
	return @ocirowcount($qhandle);
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
	global $sys_db_results,$sys_db_row_pointer;
	return $sys_db_results[$qhandle][$sys_db_row_pointer++];
}

/**
 *  db_insertid() - Returns the last primary key from an insert
 *
 *  @param		string	Query result set handle
 *  @param		string	The name of the table you inserted into
 *  @param		string	The field name of the primary key
 */
function db_insertid($qhandle,$table_name,$pkey_field_name) {
	$res=db_query("SELECT max($pkey_field_name) AS id FROM $table_name");
	if ($res && db_numrows($res) > 0) {
		return @db_result($res,0,'id');
	} else {
		return 0;
	}
}

/**
 *  db_error() - Returns the last error from the database
 */
function db_error() {
	global $conn;
	$err= @ocierror($conn);
	if ($err) {
		return $err['message'];
	} else {
		return false;
	}
}

?>
