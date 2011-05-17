<?php
/**
 * Survey Facility
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2002-2004 (c) GForge Team
 * Copyright 2010 (c) FusionForge Team
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

$is_admin_page='y';
$group_id = getIntFromRequest('group_id');
$survey_id = getIntFromRequest('survey_id');
survey_header(array('title'=>_('Survey Questions')));

if (!session_loggedin() || !user_ismember($group_id,'A')) {
	echo '<div class="error">'._('Permission denied').'</div>';
	survey_footer(array());
	exit;
}

?>

<p><?php echo _('You may use any of these questions on your surveys'); ?>.</p>

<p><span class="important"><?php echo _('NOTE: use these question_id\'s when you create a new survey'); ?>.</span></p>
<p>&nbsp;</p>
<?php

function showResultsEditQuestion($result) {
	global $group_id;
	$rows  =  db_numrows($result);
	$cols  =  db_numfields($result);
	echo "<h3>" .sprintf(ngettext('%1$s question found', '%1$s questions found', $rows), $rows)."</h3>";

	echo  "<table border=\"0\">\n";
	/*  Create  the  headers  */
	echo "<tr class=\"tableheading\">\n";
	for($i=0; $i<$cols; $i++)  {
		echo "<th>".db_fieldname($result,$i)."</th>\n";
	}
	
	echo( "</tr>");
	for($j  =  0;  $j  <  $rows;  $j++)  {

		echo( "<tr ". $GLOBALS['HTML']->boxGetAltRowStyle($j) .">\n");

		echo "<td><a href=\"edit_question.php?group_id=$group_id&amp;question_id=".db_result($result,$j,"question_id")."\">".db_result($result,$j,"question_id")."</a></td>\n";

		for($i  =  1;  $i  <  $cols;  $i++)  {
			echo "<td>".db_result($result,$j,$i)."</td>\n";
		}

		echo( "</tr>");
	}
	echo "</table>"; //</td></tr></table>");
}

/*
	Select this survey from the database
*/

$result = db_query_params ('SELECT survey_questions.question_id,survey_questions.question,survey_question_types.type
FROM survey_questions,survey_question_types
WHERE survey_question_types.id=survey_questions.question_type AND survey_questions.group_id=$1 ORDER BY survey_questions.question_id DESC',
			   array ($group_id));

showResultsEditQuestion($result);

survey_footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
