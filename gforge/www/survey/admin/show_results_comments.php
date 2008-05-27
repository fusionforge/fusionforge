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

require_once('../../env.inc.php');
require_once $gfwww.'include/pre.php';
require_once $gfwww.'include/HTML_Graphs.php';
require_once $gfwww.'survey/survey_utils.php';

$is_admin_page='y';
$group_id = getIntFromRequest('group_id');
$survey_id = getIntFromRequest('survey_id');
$question_id = getIntFromRequest('question_id');
survey_header(array('title'=>'Survey Aggregate Results'));

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
