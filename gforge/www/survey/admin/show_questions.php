<?php
/**
 * GForge Survey Facility
 *
 * Portions Copyright 1999-2001 (c) VA Linux Systems
 * The rest Copyright 2002-2004 (c) GForge Team
 * http://gforge.org/
 *
 * @version   $Id$
 *
 * This file is part of GForge.
 *
 * GForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

require_once('pre.php');
require_once('www/survey/survey_utils.php');

$is_admin_page='y';
$group_id = getIntFromRequest('group_id');
$survey_id = getIntFromRequest('survey_id');
survey_header(array('title'=>$Language->getText('survey_show_questions','title')));

if (!session_loggedin() || !user_ismember($group_id,'A')) {
	echo "<h1>".$Language->getText('survey_show_questions','permission_denied')."</h1>";
	survey_footer(array());
	exit;
}

?>

<p><?php echo $Language->getText('survey_show_questions','you_may_use_any_of_these'); ?>.</p>

<p><strong><span style="red"><?php echo $Language->getText('survey_show_questions','note_use_these_questions_id'); ?>.</span></strong></p>
<p>&nbsp;</p>
<?php

Function  ShowResultsEditQuestion($result) {
	global $group_id;
	global $Language;
	$rows  =  db_numrows($result);
	$cols  =  db_numfields($result);
	echo "<h3>" .$Language->getText('survey_show_questions','found',array($rows))."</h3>";

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
