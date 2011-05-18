<?php
/**
 * Survey Facility
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2002-2004 (c) GForge Team
 * http://fusionforge.org/
 *
 * This file is part of FusionForge. FusionForge is free software;
 * you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or (at your option)
 * any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with FusionForge; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

require_once('../../env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'include/HTML_Graphs.php';

$is_admin_page='y';
$group_id = getIntFromRequest('group_id');
$survey_id = getIntFromRequest('survey_id');
$question_id = getIntFromRequest('question_id');
survey_header(array('title'=>'Survey Aggregate Results'));

if (!session_loggedin() || !user_ismember($group_id,'A')) {
	echo '<div class="error">'._('Permission denied').'</div>';
	survey_footer(array());
	exit;
}

Function  showResultComments($result) {
	global $survey_id;

	$rows  =  db_numrows($result);
	$cols  =  db_numfields($result);
	echo "<h3>$rows Found</h3>";

	echo  "<table border=\"0\">\n";
	/*  Create  the  headers  */
	echo "<tr class=\"tableheading\">\n";

	for($i  =  0;  $i  <  $cols;  $i++)  {
		echo "<th>".db_fieldname($result,$i)."</th>\n";
	}
	echo "</tr>";

	for($j  =  0;  $j  <  $rows;  $j++)  {
			echo "<tr class=\"".$HTML->boxGetAltRowStyle($j)."\">\n";

		for ($i = 0; $i < $cols; $i++) {
			echo "<td>".db_result($result,$j,$i)."</td>\n";
		}

		echo "</tr>";
	}
	echo "</table>"; //</td></tr></table>";
}

$result=db_query_params ('SELECT question FROM survey_questions WHERE question_id=$1',
			 array($question_id));
echo "<h2>Question: ".db_result($result,0,"question")."</h2>";
echo "<p>&nbsp;</p>";

$result=db_query_params ('SELECT DISTINCT response FROM survey_responses WHERE survey_id=$1 AND question_id=$2 AND group_id=$3',
			 array($survey_id,
			       $question_id,
			       $group_id));
showResultComments($result);

survey_footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
