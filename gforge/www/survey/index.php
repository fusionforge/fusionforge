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
require_once('vote_function.php');
require_once('www/survey/survey_utils.php');

if (!$group_id) {
	echo "<H1>For some reason, the Group ID or Survey ID did not make it to this page</H1>";
}

survey_header(array('title'=>'Survey','pagename'=>'survey','titlevals'=>array(group_getname($group_id))));

Function  ShowResultsGroupSurveys($result) {
	global $group_id;
	$rows  =  db_numrows($result);
	$cols  =  db_numfields($result);

	$title_arr=array();
	$title_arr[]='Survey ID';
	$title_arr[]='Survey Title';

	echo $GLOBALS['HTML']->listTableTop ($title_arr);

	for($j=0; $j<$rows; $j++)  {

		echo "<tr ". $GLOBALS['HTML']->boxGetAltRowStyle($j) .">\n";

		echo "<TD><A HREF=\"survey.php?group_id=$group_id&survey_id=".db_result($result,$j,"survey_id")."\">".
			db_result($result,$j,"survey_id")."</TD>";

		for ($i=1; $i<$cols; $i++)  {
			printf("<TD>%s</TD>\n",db_result($result,$j,$i));
		}

		echo "</tr>";
	}

	echo $GLOBALS['HTML']->listTableBottom();

}

$sql="SELECT survey_id,survey_title FROM surveys WHERE group_id='$group_id' AND is_active='1'";

$result=db_query($sql);

if (!$result || db_numrows($result) < 1) {
	echo "<H2>This Group Has No Active Surveys</H2>";
	echo db_error();
} else {
	ShowResultsGroupSurveys($result);
}

survey_footer(array());

?>
