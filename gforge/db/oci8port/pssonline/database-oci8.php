<?php
//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net
//
// $Id$
//

/*

	This is the Oracle 8 version of our
	database connection/querying layer.

	It has been seriously overhauled and tested to create
	this working version by	our friends at http://www.pssonline.com.

*/


$sys_db_oci_commit_mode=OCI_COMMIT_ON_SUCCESS;

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
	$conn = ocilogon($sys_dbuser,$sys_dbpasswd,$sys_dbname);
}

/**
 *
 * Does any preprocessing require on the sql to convert to ORACLE sql syntax
 *
 */

function db_query_preprocess($qstring) {
	$qstring = db_replace_words($qstring);
	$qstring = db_convert_outer_joins($qstring);
	$qstring = db_convert_nextvals($qstring);
	return $qstring;
}

/**
 *
 * Replaces oracle reserved words with sf_[reserved_word]
 *
 */

function db_replace_words($qstring) {
	/* replace oracle reserved words in $qstring with sf_[reserved_word] */
	$qstring = str_replace('session ','sf_session ',$qstring);
	$qstring = str_replace('session.','sf_session.',$qstring);
	$qstring = str_replace('session,','sf_session,',$qstring);
	$qstring = str_replace('.date','.sf_date',$qstring);			
	$qstring = str_replace(',date ',',sf_date ',$qstring);			
	$qstring = str_replace(',date,',',sf_date,',$qstring);			
	$qstring = str_replace(' date ',' sf_date ',$qstring);			
	$qstring = str_replace(';','',$qstring); // a semicolon at end of oci sql causes errors
	return $qstring;
}


/**
 *
 * Replaces "SELECT nexval('sequence_name')" with "SELECT sequence_name.nextval FROM dual"
 *
 */

function db_convert_nextvals($qstring) {
	if (stristr($qstring,'nextval')) {
		$seq_array = explode("'",$qstring);
		$qstring = "SELECT " . $seq_array[1] . ".nextval FROM dual";
	}
	return $qstring;
}

/*
How are ANSI SQL outer joins migrated to Oracle outer joins?
Outer joins may not be converted correctly.

Convert an ANSI SQL inner join to an Oracle join as illustrated in the following code example:
select * from a inner join b on a.col1=b.col2;
select * from a , b where a.col1=b.col2;
Convert a left join and right join in ANSI SQL to Oracle as indicated below:
select * from a left join b on a.col1=b.col2;
// remember the table to the right of left join and add (+) to it's columns.
select * from a, b where a.col1=b.col2(+);
select * from a right join b on a.col1=b.col2;
// remember the table to the left of right join and add (+) to it's columns.
select * from a, b where a.col1(+)=b.col2;

How can I convert full outer joins from ANSI SQL Server to Oracle?
There are several ways to express a full outer join within Oracle.
For example, in the following query the predicate a.col1 (+) = b.col1 (+)
is pseudo-Oracle notation for a full outer join although this predicate
is not currently supported in Oracle:

select a.col2 acol2, a.col1 acol1, b.col1 bcol1, b.col2 bcol2
from a,b where a.col1 (+) = b.col1 (+);
The most efficient way of executing this query is to use a UNION ALL of
a left outer join and a right outer join, with an additional predicate,
as illustrated below:
select a.col2 acol2, a.col1 acol1, b.col1 bcol1, b.col2 bcol2
from a,b where a.col1=b.col1(+)
union all
select a.col2 acol2, a.col1 acol1, b.col1 bcol1, b.col2 bcol2
from a,b where a.col1(+)=b.col1 and a.col1 is null;
*/

/*
	Right now, only "LEFT JOIN" and "RIGHT JOIN" syntax are converted.
	And it has only been tested on the sql statements found in
	SourceForge version 2.5.  It may need to be modified for future
	syntax variances.
*/

function db_convert_outer_joins($qstring) {
	if ( stristr($qstring,"SELECT") && stristr($qstring,"JOIN") ) {
		$select = substr($qstring,0,strpos(strtoupper($qstring),"FROM")-1);
		$from = stristr($qstring,"FROM");
		$from = substr($from,0,strpos(strtoupper($from),"WHERE")-1);
		if (stristr($qstring,"GROUP BY")) {
			$where = stristr($qstring,"WHERE");
			$where = substr($where,0,strpos(strtoupper($where),"GROUP BY")-1);
			$group_order = stristr($qstring,"GROUP BY");
		}
		else if (stristr($qstring,"ORDER BY")) {
			$where = stristr($qstring,"WHERE");
			$where = substr($where,0,strpos(strtoupper($where),"ORDER BY")-1);
			$group_order = stristr($qstring,"ORDER BY");
		}
		else {
			$where = stristr($qstring,"WHERE");
		}
		// loop through each TABLE [ALIAS] in the from clause
		$from_array = explode(",",substr($from,strlen("FROM ")));
		$from = "FROM ";
		for ($i=0; $i<count($from_array); $i++) {
		  if ( stristr($from_array[$i],"JOIN") ) {
		    if ( stristr($from_array[$i],"LEFT") ) {		
		      $join_str = "LEFT JOIN";
					$left_join = true;
		    }
		    else if ( stristr($from_array[$i],"RIGHT") ) {
		    	$join_str = "RIGHT JOIN";
		      $left_join = false;
		    }
		    $left_table = trim(substr($from_array[$i],0,strpos(strtoupper($from_array[$i]),$join_str)));
		    $right_table = substr(stristr($from_array[$i],$join_str),strlen($join_str));
		    $right_table = trim(substr($right_table,0,strpos(strtoupper($right_table),"USING")));
		    $from = $from . $left_table . ', ' . $right_table;		
		    $left_table_alias = strrchr(trim($left_table),' ');
		    $right_table_alias = strrchr(trim($right_table),' ');
		    if ( strlen($left_table_alias) == 0 ) {
		    	$left_table_alias = $left_table;
		    }
		    else {
			  	$left_table = strchr(trim($left_table),' ');
			  }		
		    if ( strlen($right_table_alias) == 0 ) {
		    	$right_table_alias = $right_table;
		    }
		    else {
			    $right_table = strrchr(trim($right_table),' ');
		    }
		    $using_column = trim(substr(stristr($from_array[$i],"USING"),strlen("USING")));
		    $using_column = substr($using_column,1,-1); // strip parens
		    if ( $left_join ) {	  		
			    $where = $where . "\nAND " .
			    		$left_table_alias . '.' . $using_column . ' = ' .
			    		$right_table_alias . '.' . $using_column . ' (+) ';
		    }
		    else {
			    $where = $where . "\nAND " .
			    		$left_table_alias . '.' . $using_column . ' (+) = ' .
			    		$right_table_alias . '.' . $using_column . ' ';
				}
		  }
		  else {
		    $from = $from . $from_array[$i] . ' ';
		  }
		}
		return $select.' '.$from.' '.$where.' '.$group_order;
	}
	else {	
		return $qstring;
	}

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
 *  NOTE - the oci version of this may be somewhat inefficient
 *  for large result sets (hundreds or thousands of rows selected)
 *  However - most queries are returning 25-50 rows
 *
 */

function db_query($qstring,$limit='-1',$offset=0) {
	
	global $QUERY_COUNT,$conn,$sys_db_oci_commit_mode,
			$sys_db_results,$sys_db_row_pointer,$sys_db_fieldnames;
		
	$QUERY_COUNT++;

	$qstring = db_query_preprocess($qstring);
	
	$stmt=ociparse($conn,$qstring);
	
	if (!$stmt) {
		echo $qstring.'<br>';
		return 0;
	} else {
		
		if ($limit > 0) {
			if (!$offset || $offset < 0) {
				$offset=0;
			}
		}

		$res = ociexecute($stmt,$sys_db_oci_commit_mode);
		
		if ( !$res ) {
			echo $qstring.'<br>';
			return 0;
		}
		else if ( strcmp(ocistatementtype($stmt),"SELECT")==0 ) {
		
			//store fieldnames for use in db_fieldname and db_numfields
			for ($i=0;$i<ocinumcols($stmt);$i++) {
				$sys_db_fieldnames[$stmt][$i] = ocicolumnname($stmt,$i+1);
			}
			
			//if offset, seek to starting point
			//potentially expensive if large offset
			//however there is no data_seek feature AFAICT			
			$more_data=true;
			if ($offset > 0) {
				for ($i=0; $i<$offset; $i++) {
					//burn them off
					if (!ocifetchinto($stmt,$res)) {
						//if no data be returned
						//get out of loop
						$more_data=false;
						break;
					}
				}
			}

			//store the data into $sys_db_results[$stmt]
			//this is needed since there is the possiblity of calling
			//db_result($qhandle,$row,$field) with any $row value
			//db_result and db_fetch_array could be rewritten to
			//eliminate the need for fetching all of the data up front
			$row=0;			
			while ($more_data) {
				$more = ocifetchinto($stmt,$res,OCI_RETURN_NULLS+OCI_RETURN_LOBS);
				
				//see if data is being returned && we are
				//still within the requested $limit
				if ( !$more || (($limit >= 0) && ($row >= $limit))) {
					$more_data=false;
				}
				else {
					//populate sys_db_results with an array that can be indexed
					//by field number or field name
					for ($col=0;$col<db_numfields($stmt);$col++) {
						$sys_db_results[$stmt][$row][$col] = $res[$col];
						$fieldname = strtolower($sys_db_fieldnames[$stmt][$col]);
						$sys_db_results[$stmt][$row][$fieldname] = $res[$col];
					}
				}
				$row++;
			}
			$sys_db_row_pointer[$stmt]=0;
			return $stmt;		
			
		}
		else {
			return $stmt;
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
	$sys_db_oci_commit_mode=OCI_DEFAULT;
}

/**
 *      db_commit()
 *
 *      commit a transaction
 */
function db_commit() {
	global $sys_db_oci_commit_mode,$conn;
	$sys_db_oci_commit_mode=OCI_COMMIT_ON_SUCCESS;
	return ocicommit($conn);
}

/**
 *      db_rollback()
 *
 *      rollback a transaction
 */
function db_rollback() {
	global $sys_db_oci_commit_mode,$conn;
	$sys_db_oci_commit_mode=OCI_COMMIT_ON_SUCCESS;
	return ocirollback($conn);
}

/**
 *
 *  Returns the number of rows in this result set
 *
 *  param qhandle query result set handle
 *
 */
function db_numrows($qhandle) {
	global $sys_db_results;
	// return only if qhandle exists, otherwise 0
	if ($qhandle) {
		return count($sys_db_results[$qhandle]);
	} else {
		return 0;
	}
}

/**
 *
 *  Frees a database result properly
 *
 *  param qhandle query result set handle
 *
 */

function db_free_result($qhandle) {
	global $sys_db_results;
	if ($qhandle) {
		unset($sys_db_results[$qhandle]);
		unset($sys_db_row_pointer[$qhandle]);
		unset($sys_db_fieldnames[$qhandle]);
		return ocifreestatement($qhandle);
	}
	else {
		return true;
	}
}

/**
 *
 *  Reset is useful for db_fetch_array
 *  sometimes you need to start over
 *
 *  param qhandle query result set handle
 *  param row - integer row number
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
 *  param qhandle query result set handle
 *  param row - integer row number
 *  param field - text field name
 *
 */

function db_result($qhandle,$row,$field) {
	global $sys_db_results;
	return $sys_db_results[$qhandle][$row][$field];
}

/**
 *
 *  Returns the number of fields in this result set
 *
 *  param qhandle query result set handle
 *
 */

function db_numfields($qhandle) {
	global $sys_db_fieldnames;
	// return only if qhandle exists, otherwise 0
	if ( $qhandle ) {
		return count($sys_db_fieldnames[$qhandle]);
	} else {
		return 0;
	}
}

/**
 *
 *  Returns the number of rows changed in the last query
 *
 *  param qhandle - query result set handle
 *  param fnumber - column number
 *
 */

function db_fieldname($lhandle,$fnumber) {
	global $sys_db_fieldnames;
	return $sys_db_fieldnames[$lhandle][$fnumber];
}

/**
 *
 *  Returns the number of rows changed in the last query
 *
 *  param qhandle query result set handle
 *
 */

function db_affected_rows($qhandle) {
	return ocirowcount($qhandle);
}

/**
 *
 *  Returns an associative array from
 *  the current row of this database result
 *  Use db_reset_result to seek a particular row
 *
 *  param qhandle query result set handle
 *
 */

function db_fetch_array($qhandle) {
	global $sys_db_results,$sys_db_row_pointer;
	if ($sys_db_row_pointer[$qhandle] < db_numrows($qhandle)) {
		$result = $sys_db_results[$qhandle][$sys_db_row_pointer[$qhandle]];		
  	$sys_db_row_pointer[$qhandle]++;
  	return $result;
	}
	else {
		return false;
 	}
}

/**
 *
 *  Returns the last primary key from an insert
 *
 *  param qhandle query result set handle
 *  param table_name is the name of the table you inserted into
 *  param pkey_field_name is the field name of the primary key
 *
 */

function db_insertid($qhandle,$table_name,$pkey_field_name) {
	$qstring = db_query_preprocess("SELECT max($pkey_field_name) AS id FROM $table_name");
	$res=db_query($qstring);
	if ($res && (db_numrows($res) > 0) ) {
		return db_result($res,0,0);
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
	global $conn,$stmt;
	if ($stmt) {
		$err= ocierror($stmt);
	}
	else if ($conn) {
		$err= ocierror($conn);
	}
	else {
		$err= ocierror();
	}
	if ($err) {
		return $err['message'];
	} else {
		return false;
	}
}

?>
