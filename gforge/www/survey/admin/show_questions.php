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

$HTML->header(array('title'=>'Survey Questions','pagename'=>'survey_admin_show_questions'));

if (!session_loggedin() || !user_ismember($group_id,'A')) {
	echo "<h1>Permission Denied</h1>";
	survey_footer(array());
	exit;
}

?>

<p>You may use any of these questions on your surveys.</p>

<p><strong><span style="red">NOTE: use these question_id's when you create a new survey.</span></strong></p>
<p>&nbsp;</p>
<?php

Function  ShowResultsEditQuestion($result) {
	global $group_id;
	$rows  =  db_numrows($result);
	$cols  =  db_numfields($result);
	echo "<h3>$rows Found</h3>";

	echo /*"<table bgcolor=\"NAVY\"><tr><td bgcolor=\"NAVY\">*/ "<table border=\"0\">\n";
	/*  Create  the  headers  */
	echo "<tr style=\"background-color:$GLOBALS[COLOR_MENUBARBACK]\">\n";
	for($i=0; $i<$cols; $i++)  {
		printf( "<th><span style=\"color:white\"><strong>%s</strong></span></th>\n",  db_fieldname($result,$i));
	}
	echo( "</tr>");
	for($j  =  0;  $j  <  $rows;  $j++)  {

		echo( "<tr ". $GLOBALS['HTML']->boxGetAltRowStyle($j) .">\n");

		echo "<td><a href=\"edit_question.php?group_id=$group_id&amp;question_id=".db_result($result,$j,"question_id")."\">".db_result($result,$j,"question_id")."</a></td>\n";

		for($i  =  1;  $i  <  $cols;  $i++)  {
			printf("<td>%s</td>\n",db_result($result,$j,$i));
		}

		echo( "</tr>");
	}
	echo "</table>"; //</td></tr></TABLE>");
}

/*
	Select this survey from the database
*/

$sql="SELECT survey_questions.question_id,survey_questions.question,survey_question_types.type ".
	"FROM survey_questions,survey_question_types ".
	"WHERE survey_question_types.id=survey_questions.question_type AND survey_questions.group_id='$group_id' ORDER BY survey_questions.question_id DESC";

$result=db_query($sql);

ShowResultsEditQuestion($result);

$HTML->footer(array());

?>
