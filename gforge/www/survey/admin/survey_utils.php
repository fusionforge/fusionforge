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



Function  ShowResultsEditSurvey($result) {
	global $group_id;
	global $Language;
	$rows  =  db_NumRows($result);
	$cols  =  db_NumFields($result);
	echo "<h3>". $Language->getText('survey_admin_utils','found',array($rows))."</h3>";

	if ($rows > 0) {
		echo /*"<table bgcolor=\"NAVY\"><tr><td bgcolor=\"NAVY\">*/ "<table border=\"0\">\n";

		/*  Create  the  headers  */
		echo "<tr style=\"background-color:$GLOBALS[COLOR_MENUBARBACK]\">\n";
		for ($i  =  0;  $i  <  $cols;  $i++)  {
			printf( "<th><span><strong>%s</strong></span></th>\n",  $Language->getText('survey_admin_utils',db_fieldname($result,$i)));
		}
		echo "</tr>";
		for($j  =  0;  $j  <  $rows;  $j++)  {

			if ($j%2==0) {
				$row_bg="white";
			} else {
				$row_bg="$GLOBALS[COLOR_LTBACK1]";
			}

			echo "<tr style=\"background-color:$row_bg\">\n";
			echo "<td><a href=\"edit_survey.php?group_id=$group_id&amp;survey_id=".db_result($result,$j,0)."\">".db_result($result,$j,0)."</a></td>";
			for ($i = 1; $i < $cols; $i++)  {
				printf("<td>%s</td>\n",db_result($result,$j,$i));
			}

			echo "</tr>";
		}
		echo "</table>"; //</td></tr></TABLE>";
	}
}



?>
