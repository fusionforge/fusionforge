<?php
//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net
//
// $Id$
//

/*

	ALPHA VERSION - not debugged!!


	This is the Oracle 8 version of our 
	database connection/querying layer

	$sys_db_results is an array of an array of associative arrays
		containing row data from queries (3D array)

*/
$sys_db_oci_commit_mode='OCI_COMMIT_ON_SUCCESS';

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
	$conn = @ocilogon($sys_dbuser,$sys_dbpasswd,$sys_dbname);
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
 *
 *  NOTE - the OCI version of this may be somewhat inefficient
 *  for large result sets (hundreds or thousands of rows selected)
 *  However - most queries are returning 25-50 rows
 *
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
					$more_data=false
				}
			}
			$sys_db_row_pointer[$res]=0;
			return $res;
		}
	}
}

/**
 *      db_begin()
 *
 *      begin a transaction
 */
function db_begin() {
	global $sys_db_oci_commit_mode;
	$sys_db_oci_commit_mode='OCI_DEFAULT'
}

/**
 *      db_commit()
 *
 *      commit a transaction
 */
function db_commit() {
	global $sys_db_oci_commit_mode,$conn;
	$sys_db_oci_commit_mode='OCI_COMMIT_ON_SUCCESS';
	return ocicommit($conn);
}

/**
 *      db_rollback()
 *
 *      rollback a transaction
 */
function db_rollback() {
	global $sys_db_oci_commit_mode,$conn;
	$sys_db_oci_commit_mode='OCI_COMMIT_ON_SUCCESS';
	return ocirollback($conn);
}

/**
 *
 *  Returns the number of rows in this result set
 *
 *  @param qhandle query result set handle
 *
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
 *
 *  Frees a database result properly 
 *
 *  @param qhandle query result set handle
 *
 */

function db_free_result($qhandle) {
	global $sys_db_results;
	unset($sys_db_results[$qhandle]);
	return @ocifreestatement($qhandle);
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
	global $sys_db_results;
	return $sys_db_results[$qhandle][$row]["$field"];
}

/**
 *
 *  Returns the number of fields in this result set
 *
 *  @param qhandle query result set handle
 *
 */

function db_numfields($lhandle) {
	return @ocinumcols($lhandle);
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
	   return @ocicolumnname($lhandle,$fnumber);
}

/**
 *
 *  Returns the number of rows changed in the last query
 *
 *  @param qhandle query result set handle
 *
 */

function db_affected_rows($qhandle) {
	return @ocirowcount($qhandle);
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
	global $sys_db_results,$sys_db_row_pointer;
	return $sys_db_results[$qhandle][$sys_db_row_pointer++];
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
	$res=db_query("SELECT max($pkey_field_name) AS id FROM $table_name");
	if ($res && db_numrows($res) > 0) {
		return @db_result($res,0,'id');
	} else {
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
	$err= @ocierror($conn);
	if ($err) {
		return $err['message'];
	} else {
		return false;
	}
}

?>
