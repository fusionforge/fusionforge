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
	echo "<h1>".$Language->getText('survey_index','for_some_reason')."</h1>";
}

survey_header(array('title'=>$Language->getText('survey_index','title'),'pagename'=>'survey','titlevals'=>array(group_getname($group_id))));

Function  ShowResultsGroupSurveys($result) {
	global $group_id;
	global $Language;
	$rows  =  db_numrows($result);
	$cols  =  db_numfields($result);

	$title_arr=array();
	$title_arr[]=$Language->getText('survey_index','survey_id');
	$title_arr[]=$Language->getText('survey_index','survey_title');

	echo $GLOBALS['HTML']->listTableTop ($title_arr);

	for($j=0; $j<$rows; $j++)  {

		echo "<tr ". $GLOBALS['HTML']->boxGetAltRowStyle($j) .">\n";

		echo "<td><a href=\"survey.php?group_id=$group_id&amp;survey_id=".db_result($result,$j,"survey_id")."\">".
			db_result($result,$j,"survey_id")."</a></td>";

		for ($i=1; $i<$cols; $i++)  {
			printf("<td>%s</td>\n",db_result($result,$j,$i));
		}

		echo "</tr>";
	}

	echo $GLOBALS['HTML']->listTableBottom();

}

$sql="SELECT survey_id,survey_title FROM surveys WHERE group_id='$group_id' AND is_active='1'";

$result=db_query($sql);

if (!$result || db_numrows($result) < 1) {
	echo "<h2>".$Language->getText('survey_index','this_group_has')."</h2>";
	echo db_error();
} else {
	ShowResultsGroupSurveys($result);
}

survey_footer(array());

?>
