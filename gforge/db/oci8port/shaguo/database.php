<?php
//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net
//
// $Id$
//

/*

This document summarize the changes I made to SourceForge 2.5 code that 
supports Oracle 8 (oci8) database replacing postgres. The change covers
the oracle layer, namely the script database-oci8.php which is an ALPHA
version as claimed in its header, the database schema that is converted
from the postgres schema (file SourceForge.sql), and some PHP code with
SQL statements that have wrong syntax, or with bugs.

Part 1: database-oci8.php
	
	The changes are made to correct the defects/bugs in this ALPHA 
	version of OCI8 support in SourceForge 2.5. I used PHP version 
	4 OCI functions and the working environment includes apache 
	1.3.14 and Sun Solaris 2.6

	Summary of changes:

	. Eliminated all @ in front of the OCI function calls;
	. Added ocicommit($conn) after ociexecute. OCI_COMMIT_ON_SUCCESS
	  should take care of that, but somehow certain INSERT/UPDATE
	  queries do not commit unless I call ocicommit.
	. I use column name instead of column index number to retrieve
	  column values here, though both should work due to the mode
	  OCI_ASSOC+OCI_NUM used in ocifetchinto. The reason I make
	  the change is that column names are used in most of PHP codes
	  of SourceForge (I recall that a few places use numbers though)
	  and I want to make it consistent. There is a defect here that
	  I did not fix: the db_query will return if the value of the
	  first column is null. This would cause problem if it happens
	  that the value is null, not because it is at the end of the
	  rows. The function could return less rows than actual result.
	  I do not have time to figure out a better way to detect if we
	  are at the end of the rows, considering that function db_query
	  applies to SELECT, INSERT, UPDATE, DELETE, all the queries.
	. The ALPHA code uses the returned value of ociexecute as the
	  array index of the returned result rows. Note that the returned
	  value is always 1 (if execution is successful) and if I have
 	  two db_query calls and the result of the second call will 
	  unexpectedly replace that of the first one. I use the returned
	  handle of ociparse as the index instead, since the handle will
	  be a different value for each db_query call. Then in all the 
	  code of calling db_query, it would be nice to free that handle
	  and the result array after the call. I guess it does not matter
	  much since each PHP script runs once as a CGI program and then
	  exit. If the PHP script is permanently loaded in web server
	  after the call, that would cause memory leak problem. I will 
	  try to clean the SourceForge PHP code that calls db_query later.

	. There was a problem with $sys_db_row_pointer, fixed that.

	. Oracle likes UPPER case names. If you try to SELECT rows from
	  an oracle table, most likely you will get a upper case column
	  name, which means you have to retrieve a column value like
	  $x[USER_ID] or $x['USER_ID'] or $x["USER_ID"], and $x[user_id]
	  or $x['user_id'] or $x["user_id"] will not work. I noticed that
	  almost all of the SourceForge codes use lower case names. 
	  A solution could be defining oracle tables like:

		create table users ("user_id" varchar2(20), ...)

	  In this case the field user_id is created in lower case in Oracle.
	  As a result $x["user_id"] is correct.

	  I personally does not like it due to that I have to do SELECT as:

		select "user_id" from users

	  Another solution is to make a copy of the returned rows and
	  replace the upper case names to lower case when copying.
	  Then make both result arrays available to the caller of db_query.
	  I prefer this generic approach even it costs a little bit of
	  memory and cpu. I have not done that though.

	  What I did here is accepting upper case column names and changed
	  all of the places in SourceForge that call db_query, or uses the
	  result arrays. Anyhow I want to find out the places and free the
	  arrays later when I have more time.

Part 2: Database Schema

	The schema enclosed in SF 2.5 is for postgres. I wrote a perl
	script that converts the postgres SQL statements to Oracle.

	The results are 3 separate files. File SourceForge_oci8.sql includes 
	all the tables, sequences, indices. Table session has been renamed 
	to session1, since it is a key word in Oracle DDL. Also all the 
	fields date has been renamed to date1. Corresponding changes need 
	to be made in places that refer to the table and/or fields.

	File Trigger_auto.sql are triggers that used to implement the auto 
	insertion of sequence numbers. Oracle does not allow 

		"bug_id" integer DEFAULT nextval('bug_pk_seq'::text) NOT NULL,

	So the triggers are necessary here. With these triggers, you can
	insert a record without specify the sequence number, and the
	trigger will get the next one and insert for you. If you do want
	to specify sequences in your INSERT/UPDATE queries, the trigger
	will take you number. 

	File Trigger_er.sql is integrity constraint triggers defining the 
	E-R among tables. I did not apply those in my case due to that I 
	would not be able to insert the default rows after that. I will 
	apply the constraints later.

	Many fields are defined as text in postgres and I had trouble 
	deciding what to do with it. There are a lot of limitation in 
	Oracle to LONG and LOB fields. I use varchar2() to replace text 
	even though the maximum bytes for varchar2() is 4000 (?). Most 
	likely we will not run that limit and if any case, I can simply 
	change it to LONG or LOB.

Part 3: Misc Changes Fixing SQL Syntax or Bugs (incomplete)

	File Name	Changes
	------------------------------------------------------------------

	account/login.php	

			added lines to set the hask cookie. Also added 
			the 3rd parameters to session_login_valid. 
			changed table name session to session1


	account/logout.php

			changed table name session to session1

	admin/lastlogins.php

			changed table name session to session1

	admin/search.php

			changed "distinctrow" to "unique"

	admin/userlist.php

			added select before insert statement

	developer/monitor.php

			changed variable user to user_id

	docman/doc_utils.php

			defined count(*) as cnt

	forum/forum.php

			changed table field date to date1

	forum/forum_utils.php

			changed table field date to date1. changed the
			SELECT nextval('forum_thread_seq') to 
			SELECT forum_thread_seq.nextval from dual

	include/User.class

			added select statement to get a user object

	include/cache.php

			comment out flock statements due to access rights.

	include/osdn.php

			image changed to /image. commented out some ads.

	include/session.php

			$allowingpending=0 changed to allowpending. added 
			UPDATE statement to activate pending user accounts.
			changed session to session1

	include/user_home.php

			changed user= to user_id=

	my/diary.php

			added a section to retrieve user object to set
			$G_SESSION
	
	news/news_utils.php

			changed date to date1. changed a few http to https.

	news/submit.php

			changed date to date1

	project/memberlist.php

			changed the join to outer join in query

	softwaremap/trove_list.php

			changed the LEFT JOIN to outer join in Oracle.


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
	global $sys_dbuser,$sys_dbpasswd,$conn,$sys_dbname;
	$conn = ocilogon($sys_dbuser,$sys_dbpasswd,$sys_dbname);
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

	global $conn,$QUERY_COUNT,$sys_db_results;
        global $sys_db_row_pointer,$sys_db_oci_commit_mode;

	$QUERY_COUNT++;

	$stmt=ociparse($conn,$qstring);

	if (!$stmt) {
		return 0;
	} else {
		if ($limit > 0) {
			if (!$offset || $offset < 0) {
				$offset=0;
			}
		}

		$res=ociexecute($stmt,$sys_db_oci_commit_mode);
                ocicommit($conn);

		if (!$res) {
			return 0;
		} else {
			//if offset, seek to starting point
			//potentially expensive if large offset
			//however there is no data_seek feature AFAICT
                        $col_name  = OCIColumnName($stmt,1);

			$more_data=true;
			if ($offset > 0) {
				for ($i=0; $i<$offset; $i++) {
					//burn them off
					ocifetchinto($stmt,&$x,OCI_ASSOC+OCI_NUM);
					if (!$x[$col_name]) {
						//if no data be returned
						//get out of loop
						$more_data=false;
						break;
					}
				}
			}

			$i=0;
			while ($more_data) {

                                unset($x);
				$ret = ocifetchinto($stmt,&$x,OCI_ASSOC+OCI_NUM);
				if (!$ret) {
                                  //if no data be returned
                                  //get out of loop
                                  $more_data=false;
                                  break;
                                }

				$i++;

				$sys_db_results[$stmt][$i-1]=$x;

				//see if data is being returned && we are 
				//still within the requested $limit
				if (count($x) < 1 || (($limit > 0) && 
                                   ($i >= $limit))) 
                                {
					$more_data=false;
				}
			}
			$sys_db_row_pointer[$stmt]=0;

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
	$sys_db_oci_commit_mode='OCI_DEFAULT';
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

		return count($sys_db_results[$qhandle]);
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
	return ocifreestatement($qhandle);
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
        $fieldu = strtoupper($field);

	return $sys_db_results[$qhandle][$row][$fieldu];
}

/**
 *
 *  Returns the number of fields in this result set
 *
 *  @param qhandle query result set handle
 *
 */

function db_numfields($lhandle) {
	return ocinumcols($lhandle);
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
	   return ocicolumnname($lhandle,$fnumber);
}

/**
 *
 *  Returns the number of rows changed in the last query
 *
 *  @param qhandle query result set handle
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
 *  @param qhandle query result set handle
 *
 */

function db_fetch_array($qhandle) {
	global $sys_db_results,$sys_db_row_pointer;
        $row = $sys_db_row_pointer[$qhandle];
        $sys_db_row_pointer[$qhandle] = $sys_db_row_pointer[$qhandle] + 1;
        //$sys_db_row_pointer = $sys_db_row_pointer + 1;
	return $sys_db_results[$qhandle][$row];
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
		return db_result($res,0,'id');
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
	$err= ocierror($conn);
	if ($err) {
		return $err['message'];
	} else {
		return false;
	}
}

?>
