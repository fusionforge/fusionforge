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
require_once('HTML_Graphs.php');
require_once('www/survey/survey_utils.php');

$is_admin_page='y';
survey_header(array('title'=>'Survey Aggregate Results','pagename'=>'survey_admin_show_results_comments'));

if (!session_loggedin() || !user_ismember($group_id,'A')) {
	echo "<h1>Permission Denied</h1>";
	survey_footer(array());
	exit;
}

Function  ShowResultComments($result) {
	global $survey_id;

	$rows  =  db_numrows($result);
	$cols  =  db_numfields($result);
	echo "<h3>$rows Found</h3>";

	echo /*"<table bgcolor=\"NAVY\"><tr><td bgcolor=\"NAVY\">*/ "<table border=\"0\">\n";
	/*  Create  the  headers  */
	echo "<tr style=\"background-color:$GLOBALS[COLOR_MENUBARBACK]\">\n";

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

		for ($i = 0; $i < $cols; $i++) {
			printf("<td>%s</td>\n",db_result($result,$j,$i));
		}

		echo "</tr>";
	}
	echo "</table>"; //</td></tr></TABLE>";
}

$sql="SELECT question FROM survey_questions WHERE question_id='$question_id'";
$result=db_query($sql);
echo "<h2>Question: ".db_result($result,0,"question")."</h2>";
echo "<p>&nbsp;</p>";

$sql="SELECT DISTINCT response FROM survey_responses WHERE survey_id='$survey_id' AND question_id='$question_id' AND group_id='$group_id'";
$result=db_query($sql);
ShowResultComments($result);

survey_footer(array());

?>
