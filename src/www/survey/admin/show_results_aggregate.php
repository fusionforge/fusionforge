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
survey_header(array('title'=>_('Survey Aggregate Results')));

if (!session_loggedin() || !user_ismember($group_id,'A')) {
	echo '<div class="error">'._('Permission denied').'</div>';
	survey_footer(array());
	exit;
}

/*
	Select this survey from the database
*/

$result = db_query_params ('SELECT * FROM surveys WHERE survey_id=$1 AND group_id=$2',
			   array ($survey_id,
				  $group_id));

echo "<h2>".db_result($result, 0, "survey_title")."</h2><p>&nbsp;</p>";

/*
echo "<h3><a href=\"show_results_csv.php?survey_id=$survey_id&amp;group_id=$group_id\">.CSV File</a></h3><p>";
*/

/*
	Select the questions for this survey
*/

$questions=db_result($result, 0, "survey_questions");
$questions=str_replace(" ", "", $questions);
$quest_array=explode(',', $questions);
$count=count($quest_array);

echo "\n\n<table>";

$q_num=1;

for ($i=0; $i<$count; $i++) {

	/*
		Build the questions on the HTML form
	*/

	$result = db_query_params ('SELECT question_type,question,question_id FROM survey_questions WHERE question_id=$1 AND group_id=$2',
				   array ($quest_array[$i],
					  $group_id));

	$question_type=db_result($result, 0, "question_type");

	if ($question_type == "4") {
		/*
			Don't show question number if it's just a comment
		*/

		echo "\n<tr><td valign=\"top\">&nbsp;</td>\n<td>";

	} else {

		echo "\n<tr><td valign=\"top\"><strong>";

		/*
			If it's a 1-5 question box and first in series, move Quest
			number down a bit
		*/

		if (($question_type != $last_question_type) && (($question_type == "1") || ($question_type == "3"))) {
			echo "&nbsp;<p>&nbsp;</p>";
		}

		echo $q_num."&nbsp;&nbsp;&nbsp;&nbsp;<br /></td>\n<td>";
		$q_num++;

	}

	if ($question_type == "1") { // This is a radio-button question. Values 1-5.
		// Show the 1-5 markers only if this is the first in a series

		if ($question_type != $last_question_type) {
			echo "\n<strong>1 &nbsp; &nbsp;&nbsp;&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; 5</strong>\n";
			echo "<br />";

		}

		// Select the number of responses to this question

		$result2 = db_query_params ('SELECT count(*) AS count FROM survey_responses WHERE survey_id=$1 AND question_id=$2 AND response IN (1,2,3,4,5) AND group_id=$3',
			array ($survey_id,
				$quest_array[$i],
				$group_id));
		if (!$result2 || db_numrows($result2) < 1) {
			echo _('error');
			echo db_error();
		} else {
			$response_count = db_result($result2, 0, 'count');
			echo "<strong>" . $response_count . "</strong>" ._('Responses')."<br />";
		}

		//	average
		if ($response_count > 0){
			$result2 = db_query_params ('SELECT avg(response::int) AS avg FROM survey_responses WHERE survey_id=$1 AND question_id=$2 AND group_id=$3 AND response IN (1,2,3,4,5)',
						    array ($survey_id,
							   $quest_array[$i],
							   $group_id));
			if (!$result2 || db_numrows($result2) < 1) {
				echo _('error');
				echo db_error();
			} else {
				echo "<strong>". number_format(db_result($result2, 0, 'avg'),2) ."</strong>"._('Average');
			}

			$result2 = db_query_params ('SELECT response,count(*) AS count FROM survey_responses WHERE survey_id=$1 AND question_id=$2 AND group_id=$3 AND response IN (1,2,3,4,5) GROUP BY response',
						    array ($survey_id,
							   $quest_array[$i],
							   $group_id));
			if (!$result2 || db_numrows($result2) < 1) {
				echo _('error');
				echo db_error();
			} else {
				GraphResult($result2,stripslashes(db_result($result, 0, "question")));
			}
		}// end if (responses to survey question present)
	} else if ($question_type == "2") { // This is a text-area question.
		echo db_result($result, 0, "question")."<br />\n";
		echo "<a href=\"show_results_comments.php?survey_id=$survey_id&amp;question_id=$quest_array[$i]&amp;group_id=$group_id\">"._('View All Comments')."</a>";

	} else if ($question_type == "3") { // 	This is a Yes/No question.
	  //	Show the Yes/No only if this is the first in a series
		if ($question_type != $last_question_type) {
			echo "<strong>"._('Yes / No')."</strong><br />\n";
		}

		// Select the count and average of responses to this question
		$result2 = db_query_params ('SELECT count(*) AS count FROM survey_responses WHERE survey_id=$1 AND question_id=$2 AND group_id=$3 AND response IN (1,5)',
					    array ($survey_id,
						   $quest_array[$i],
						   $group_id));
		if (!$result2 || db_numrows($result2) < 1) {
			echo _('error');
			echo db_error();
		} else {
			echo "<strong>".db_result($result2, 0, 0)."</strong> "._('Responses')."<br />";
		}

		// average
		$result2 = db_query_params ('SELECT avg(response::int) AS avg FROM survey_responses WHERE survey_id=$1 AND question_id=$2 AND group_id=$3  and response != $4',
					    array ($survey_id,
						   $quest_array[$i],
						   $group_id,
						   ''));

		if (!$result2 || db_numrows($result2) < 1) {
			echo _('error');
			echo db_error();
		} else {
			echo "<strong>".number_format(db_result($result2, 0, 0),2)."</strong>"._('Average');
		}

		// Get the YES responses
		$result2 = db_query_params ('SELECT count(*) AS count FROM survey_responses WHERE survey_id=$1 AND question_id=$2 AND group_id=$3 AND response=1',
					    array ($survey_id,
						   $quest_array[$i],
						   $group_id));

		$name_array[0]=_('Yes');

		if (!$result2 || db_numrows($result2) < 1) {
			$value_array[0]=0;
		} else {
			$value_array[0]=db_result($result2, 0, "count");
		}

		// Get the NO responses
		$result2 = db_query_params ('SELECT count(*) AS count FROM survey_responses WHERE survey_id=$1 AND question_id=$2 AND group_id=$3 AND response=5',
					    array ($survey_id,
						   $quest_array[$i],
						   $group_id));

		$name_array[1]=_('No');

		if (!$result2 || db_numrows($result2) < 1) {
			$value_array[1]=0;
		} else {
			$value_array[1]=db_result($result2, 0, "count");
		}

		GraphIt($name_array,$value_array,stripslashes(db_result($result, 0, "question")));

	} else if ($question_type == "4") {

		echo "&nbsp;<p><strong>".db_result($result, 0, "question")."</strong></p>\n";
		echo "<input type=\"hidden\" name=\"_".$quest_array[$i]."\" value=\"-666\" />";

	} else if ($question_type == "5") { // This is a text-field question.
		echo db_result($result, 0, "question")."<br />\n";

		echo "<a href=\"show_results_comments.php?survey_id=$survey_id&amp;question_id=$quest_array[$i]&amp;group_id=$group_id\">"._('View All Comments')."</a>";

	}

	echo "</td></tr>";

	$last_question_type=$question_type;

}

echo "\n\n</table>";

survey_footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
