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

$is_admin_page='y';
$group_id = getIntFromRequest('group_id');
$survey_id = getIntFromRequest('survey_id');
$customer_id = getIntFromRequest('customer_id');
survey_header(array('title'=>_('Results')));

if (!session_loggedin() || !user_ismember($group_id,'A')) {
        echo '<div class="error">'._('Permission denied').'</div>';
        survey_footer(array());
	exit;
}

?>

<form action="none">
<?php

/*
	Select this survey from the database
*/

	$result = db_query_params ('SELECT * FROM surveys WHERE survey_id=$1 AND group_id=$2',
				   array ($survey_id,
					  $group_id));

echo "\n<h2>".db_result($result, 0, "survey_title")."</h2><p>&nbsp;</p>";

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
	$result = db_query_params ('SELECT questions.question_type,questions.question,questions.question_id,responses.response FROM questions,responses WHERE questions.question_id=$1 AND questions.question_id=responses.question_id AND responses.customer_id=$2 AND responses.survey_id=$3',
				   array($quest_array[$i],
					 $customer_id,
					 $survey_id));

	if (!$result || db_numrows($result) < 1) {
		$result = db_query_params ('SELECT * FROM survey_questions WHERE question_id=$1',
					   array ($quest_array[$i]));
		$not_found=1;
	} else {
		$not_found=0;
	}

		//echo "\n\nnotfound: '$not_found'";

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

	if ($question_type == "1") {

		/*
			This is a radio-button question. Values 1-5.
		*/


		// Show the 1-5 markers only if this is the first in a series

		if ($question_type != $last_question_type) {
			echo "\n<strong>1 &nbsp; &nbsp;&nbsp;&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; 5</strong>\n";
                        echo "\n<br />";

		}

		for ($j=1; $j<=5; $j++) {
			echo "\n<input type=\"radio\" name=\"_".$quest_array[$i]."\" value=\"$j\"";
			/*
				add the checked statement if this was the response
			*/
			if (($not_found==0) && db_result($result, 0, "response")=="$j") { echo " checked=\"checked\""; }
			echo " />\n";
		}

		echo "&nbsp; ".db_result($result, 0, "question")."\n";

	} else if ($question_type == "2") {

		/*
			This is a text-area question.
		*/

		echo db_result($result, 0, "question")."<br />\n";
		echo "\n<textarea name=\"_".$quest_array[$i]."\" rows=\"5\" cols=\"60\">";

		/*
			Show the person's response if there was one
		*/

		if ($not_found==0) {
			echo db_result($result, 0, "response");
		}
		echo "</textarea>\n";

	} else if ($question_type == "3") {

                /*
                        This is a Yes/No question.
                */

		/*
			Show the Yes/No only if this is the first in a series
		*/

		if ($question_type != $last_question_type) {
	                echo "<strong>"._('Yes / No')."</strong><br />\n";
		}

		echo "\n<input type=\"radio\" name=\"_".$quest_array[$i]."\" value=\"1\"";

                /*
                	add the checked statement if this was the response
                */

		if (($not_found==0) && db_result($result, 0, "response")=="1") { echo " checked=\"checked\""; }
		echo " />";
                echo "\n<input type=\"radio\" name=\"_".$quest_array[$i]."\" value=\"5\"";

                /*
                        add the checked statement if this was the response
                */
                if (($not_found==0) && db_result($result, 0, "response")=="5") { echo " checked=\"checked\""; }

                echo " />";

		echo "&nbsp; ".db_result($result, 0, "question")."\n";

        } else if ($question_type == "4") {

		/*
			This is a comment only.
		*/

		echo "\n&nbsp;<p><strong>".db_result($result, 0, "question")."</strong></p>\n";
		echo "\n<input type=\"hidden\" name=\"_".$quest_array[$i]."\" value=\"-666\" />";

        } else if ($question_type == "5") {

                /*
                        This is a text-field question.
                */

		echo db_result($result, 0, "question")."<br />\n";
                echo "\n<input type=\"text\" name=\"_".$quest_array[$i]."\" size=\"20\" maxlength=\"70\" value=\"";

		/*
			Show the person's response if there was one
		*/
		if ($not_found==0) {
		 	echo db_result($result, 0, "response");
		}
		echo "\" />";

        }

	echo "</td></tr>";

	$last_question_type=$question_type;

}

echo "\n\n</table>";

?>
</form>

<?php

survey_footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
