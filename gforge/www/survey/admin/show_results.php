<?php
/**
  *
  * SourceForge Survey Facility
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */


require_once('pre.php');
require_once('www/survey/survey_utils.php');
$is_admin_page='y';
survey_header(array('title'=>'Survey Results','pagename'=>'survey_admin_show_results'));

if (!session_loggedin() || !user_ismember($group_id,'A')) {
	echo "<h1>Permission Denied</h1>";
	survey_footer(array());
	exit;
}

Function  ShowResultsSurvey($result) {
	global $group_id,$PHP_SELF;
	$rows  =  db_numrows($result);
	$cols  =  db_numfields($result);
	echo "<h3>$rows Found</h3>";

	echo /*"<table bgcolor=\"NAVY\"><tr><td bgcolor=\"NAVY\">*/ "<table border=\"0\">\n";
	/*  Create  the  headers  */
	echo "<tr style=\"background=color:$GLOBALS[COLOR_MENUBARBACK]\">\n";

	for($i  =  0;  $i  <  $cols;  $i++)  {
		printf( "<th><span style=\"color:white\"><strong>%s</strong></span></th>\n",  db_fieldname($result,$i));
	}
	echo "</tr>";

	for($j  =  0;  $j  <  $rows;  $j++)  {

		if ($j%2==0) {
			$row_bg="white";
		} else {
			$row_bg="$GLOBALS[COLOR_LTBACK1]";
		}

		echo "<tr style=\"background-color:$row_bg\">\n";

		echo "<td><a href=\"$PHP_SELF?group_id=$group_id&amp;survey_id=".db_result($result,$j,"survey_id")."\">".db_result($result,$j,"survey_id")."</a></td>\n";

		for($i  =  1;  $i  <  $cols;  $i++)  {
			printf("<td>%s</td>\n",db_result($result,$j,$i));
		}

		echo "</tr>";
	}
	echo "</table>"; //</td></tr></TABLE>";
}


Function  ShowResultsAggregate($result) {
	global $group_id;
	$rows  =  db_numrows($result);
	$cols  =  db_numfields($result);
	echo "<h3>$rows Found</h3>";

	echo /*"<table bgcolor=\"NAVY\"><tr><td bgcolor=\"NAVY\">*/ "<table border=\"0\">\n";
	/*  Create  the  headers  */
	echo "<tr style=\"background=color:$GLOBALS[COLOR_MENUBARBACK]\">\n";

	for($i  =  0;  $i  <  $cols;  $i++)  {
		printf( "<th><span style=\"color:white\"><strong>%s</strong></span></th>\n",  db_fieldname($result,$i));
	}
	echo "</tr>";

	for($j  =  0;  $j  <  $rows;  $j++)  {

		if ($j%2==0) {
			$row_bg="white";
		} else {
			$row_bg="$GLOBALS[COLOR_LTBACK1]";
		}

		echo "<tr style=\"background-color:$row_bg\">\n";

		echo "<td><a href=\"show_results_aggregate.php?group_id=$group_id&amp;survey_id=".db_result($result,$j,"survey_id")."\">".db_result($result,$j,"survey_id")."</a></td>\n";

		for($i  =  1;  $i  <  $cols;  $i++)  {
			printf("<td>%s</td>\n",db_result($result,$j,$i));
		}

		echo "</tr>";
	}
	echo "</table>"; //</td></tr></TABLE>";
}


Function  ShowResultsCustomer($result) {
	global $survey_id,$group_id;

	$rows  =  db_numrows($result);
	$cols  =  db_numfields($result);
	echo "<h3>$rows Found</h3>";

	echo /*"<table bgcolor=\"NAVY\"><tr><td bgcolor=\"NAVY\">*/ "<table border=\"0\">\n";
	/*  Create  the  headers  */
	echo "<tr style=\"background=color:$GLOBALS[COLOR_MENUBARBACK]\">\n";

	for($i  =  0;  $i  <  $cols;  $i++)  {
		printf( "<th><span style=\"color:white\"><strong>%s</strong></span></th>\n",  db_fieldname($result,$i));
	}
	echo "</tr>";

	for($j  =  0;  $j  <  $rows;  $j++)  {

		if ($j%2==0) {
			$row_bg="white";
		} else {
			$row_bg="$GLOBALS[COLOR_LTBACK1]";
		}

		echo "<tr style=\"background-color:$row_bg\">\n";

		echo "<td><a href=\"show_results_individual.php?group_id=$group_id&amp;survey_id=$survey_id&amp;customer_id=".db_result($result,$j,"cust_id")."\">".db_result($result,$j,"cust_id")."</a></td>\n";

		for($i  =  1;  $i  <  $cols;  $i++)  {
			printf("<td>%s</td>\n",db_result($result,$j,$i));
		}

		echo "</tr>";
	}
	echo "</table>"; //</td></tr></TABLE>";
}



if (!$survey_id) {

	/*
		Select a list of surveys, so they can click in and view a particular set of responses
	*/

	$sql="SELECT survey_id,survey_title FROM surveys WHERE group_id='$group_id'";

	$result=db_query($sql);

//	echo "\n<h2>View Individual Responses</h2>\n\n";
//	ShowResultsSurvey($result);

	echo "\n<h2>View Aggregate Responses</h2>\n\n";
	ShowResultsAggregate($result);

} /* else {

	/ *
		Pull up a list of customer IDs for people that responded to this survey
	* /

	$sql="select people.cust_id, people.first_name, people.last_name ".
		"FROM people,responses where responses.customer_id=people.cust_id AND responses.survey_id='$survey_id' ".
		"GROUP BY people.cust_id, people.first_name, people.last_name";

	$result=db_query($sql);

	ShowResultsCustomer($result);

} */

survey_footer(array());

?>
