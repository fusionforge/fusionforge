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
survey_header(array('title'=>$Language->getText('survey_show_results_individual','title'),'pagename'=>'survey_admin_show_results_individual'));

if (!session_loggedin() || !user_ismember($group_id,'A')) {
        echo "<h1>".Language->getText('survey_show_results_individual','permission_denied')."</h1>";
        survey_footer(array());
	exit;
}

?>

<form action="none">
<?php

/*
	Select this survey from the database
*/

$sql="SELECT * FROM surveys WHERE survey_id='$survey_id' AND group_id='$group_id'";
$result=db_query($sql);

echo "\n<h2>".db_result($result, 0, "survey_title")."</h2><p>&nbsp;</p>";

/*
	Select the questions for this survey
*/

$questions=db_result($result, 0, "survey_questions");
$questions=str_replace(" ", "", $questions);
$quest_array=explode(',', $questions);

$count=count($quest_array);

/*
	Display info for this customer
*/

/*
$sql="select * from people where cust_id='$customer_id'";

$result=db_query($sql);

echo "\n<strong>Name: </strong>".db_result($result, 0, "first_name")." ".db_result($result, 0, "last_name")."<br />";
echo "\n<strong>Email: </strong>".db_result($result, 0, "email")." / ".db_result($result, 0, "email2")."<br />";
echo "\n<strong>Phone: </strong>".db_result($result, 0, "phone")."<br />";
echo "\n<strong>Beeper: </strong>".db_result($result, 0, "beeper")."<br />";
echo "\n<strong>Cell: </strong>".db_result($result, 0, "cell")."<p>";
*/

echo "\n\n<table>";

$q_num=1;

for ($i=0; $i<$count; $i++) {

	/*
		Build the questions on the HTML form
	*/

	$sql="select questions.question_type,questions.question,questions.question_id,responses.response ".
		"from questions,responses where questions.question_id='".$quest_array[$i]."' and ".
		"questions.question_id=responses.question_id and responses.customer_id='$customer_id' AND responses.survey_id='$survey_id'";

	$result=db_query($sql);

/*
	See if there was a result. If not a result, join might have failed because of "open ended question".
	In that case, requery, and test again. If still no response, then this is a "comment only" question
*/
	if (!$result || db_numrows($result) < 1) {

		//$result=db_query("select * from responses where question_id='".$quest_array[$i]."' and survey_id='$survey_id' AND customer_id='$customer_id'");

		//echo "\n\n<!-- falling back 1 -->";
	
		//if (!$result || db_numrows($result) < 1) {
		//	echo "\n\n<!-- falling back 2 -->";
			$result=db_query("select * from survey_questions where question_id='".$quest_array[$i]."'");
			$not_found=1;
		//} else {
                //	$not_found=0;
		//}

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
			This is a rædio-button question. Values 1-5.	
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
		echo "\n<textarea name=\"_".$quest_array[$i]."\" rows=\"5\" cols=\"60\" wrap=\"soft\">";

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
	                echo "<strong>".$Language->getText('survey_show_results_individual','yes_no').Yes / No"</strong><br />\n";
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

?>
