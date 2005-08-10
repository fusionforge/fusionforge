<?php
/**
 * vote_function.php
 *
 * SourceForge: Breaking Down the Barriers to Open Source Development
 * Copyright 1999-2001 (c) VA Linux Systems
 * http://sourceforge.net
 *
 * @version   $Id$
 */

/**
 * vote_number_to_stars() - Turns vote results into *'s
 *
 * @param		int		Raw value
 */
function vote_number_to_stars($raw) {
	$raw=intval($raw*2);
	//	echo "\n\n<!-- $raw -->\n\n";
	if ($raw % 2 == 0) {
		$show_half=0;
	} else {
		$show_half=1;
	}
	$count=intval($raw/2);
	for ($i=0; $i<$count; $i++) {
		$return .= html_image("ic/check.png","15","16",array());
	}
	if ($show_half==1) {
		$return .= html_image("ic/halfcheck.png","15","16",array());
	}
	return $return;
}

/**
 * vote_show_thumbs() - Show vote stars
 *
 * @param		int		The survey ID
 * @param		string	The rating type
 */
function vote_show_thumbs($id,$flag) {
	/*
		$flag
		project - 1
		release - 2
		forum_message - 3
		user - 4
	*/
	$rating=vote_get_rating ($id,$flag);
	if ($rating==0) {
		return "<strong>(unrated)</strong>";
	} else {
		return vote_number_to_stars($rating).'('.$rating.')';
	}
}

/**
 * vote_get_rating() - Get a vote rating
 *
 * @param		int		The survey ID
 * @param		string	The rating type
 */
function vote_get_rating ($id,$flag) {
	$sql="SELECT response FROM survey_rating_aggregate WHERE type='$flag' AND id='$id'";
	$result=db_query($sql);
	if (!$result || (db_numrows($result) < 1) || (db_result($result,0,0)==0)) {
		return '0';
	} else {
		return db_result($result,0,0);
	}
}

/**
 * vote_show_release_radios() - Show release radio buttons
 *
 * @param		int		Survey ID
 * @param		string	The rating type
 */
function vote_show_release_radios ($vote_on_id,$flag) {
	/*
		$flag
		project - 1
		release - 2
		forum_message - 3
		user - 4
	*/

//html_blankimage($height,$width)
	$rating=vote_get_rating ($vote_on_id,$flag);
	if ($rating==0) {
		$rating='2.5';
	}
	$rating=((16*vote_get_rating ($vote_on_id,$flag))-15);

	?>
	<span style="font-size:smaller">
	<form action="/survey/rating_resp.php" method="post">
	<input type="radio" name="vote_on_id" value="<?php echo $vote_on_id; ?>" />
	<input type="radio" name="redirect_to" value="<?php echo urlencode(getStringFromServer('REQUEST_URI')); ?>" />
	<input type="radio" name="flag" value="<?php echo $flag; ?>" />
	<div align="center">
	<?php echo html_image("rateit.png","100","9",array()); ?>
	<br />
	<?php
		echo html_blankimage(1,$rating);
		echo html_image("ic/caret.png","9","6",array());
	?>
	<br />
	<input type="radio" name="response" value="1" />
	<input type="radio" name="response" value="2" />
	<input type="radio" name="response" value="3" />
	<input type="radio" name="response" value="4" />
	<input type="radio" name="response" value="5" />
	<br />
	<input type="submit" name="submit" value="Rate" />
	</div>
	</form>
	</span>
	<?php

}

/**
 * show_survey() - Select and show a specific survey from the database
 *
 * @param		int		The group ID
 * @param		int		The survey ID
 */
function show_survey ($group_id,$survey_id) {
  global $Language;

/*
	Select this survey from the database
*/

$sql="SELECT * FROM surveys WHERE survey_id='$survey_id' and group_id = '$group_id'";

$result=db_query($sql);

if (db_numrows($result) > 0) {
	echo '
		<h3>'.db_result($result, 0, 'survey_title').'</h3>
		<form action="/survey/survey_resp.php" method="post">
		<input type="hidden" name="group_id" value="'.$group_id.'" />
		<input type="hidden" name="survey_id" value="'.$survey_id.'" />';

	/*
		Select the questions for this survey
	*/

	$questions=db_result($result, 0, 'survey_questions');
	$quest_array=explode(',', $questions);
	$count=count($quest_array);
	echo '
		<table border="0">';
	$q_num=1;

	for ($i=0; $i<$count; $i++) {
		/*
			Build the questions on the HTML form
		*/

		$sql="SELECT * FROM survey_questions WHERE question_id='".$quest_array[$i]."'";
		$result=db_query($sql);
		$question_type=db_result($result, 0, 'question_type');

		if ($question_type == '4') {
			/*
				Don't show question number if it's just a comment
			*/

			echo '
				<tr><td valign="top">&nbsp;</td><td>';

		} else {
			echo '
				<tr><td valign="top"><strong>';
			/*
				If it's a 1-5 question box and first in series, move Quest
				number down a bit
			*/
			if (($question_type != $last_question_type) && (($question_type == '1') || ($question_type == '3'))) {
				echo '&nbsp;<br />';
			}

			echo $q_num.'&nbsp;&nbsp;&nbsp;&nbsp;<br /></td><td>';
			$q_num++;
		}

		if ($question_type == "1") {
			/*
				This is a radio-button question. Values 1-5.
			*/
			// Show the 1-5 markers only if this is the first in a series

			if ($question_type != $last_question_type) {
				echo '
					<strong>1</strong>'.$Language->getText('survey','low').'  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <strong>5</strong>' .$Language->getText('survey','high');
				echo '<br />';

			}

			for ($j=1; $j<=5; $j++) {
				echo '
					<input type="radio" name="_'.$quest_array[$i].'" value="'.$j.'" />';
			}

			echo '&nbsp; '.stripslashes(db_result($result, 0, 'question'));

		} else if ($question_type == '2') {
			/*
				This is a text-area question.
			*/

			echo stripslashes(db_result($result, 0, 'question')).'<br />';
			echo '
				<textarea name="_'.$quest_array[$i].'" rows="5" cols="60" wrap="soft"></textarea>';

		} else if ($question_type == '3') {
			/*
				This is a Yes/No question.
			*/

			//Show the Yes/No only if this is the first in a series

			if ($question_type != $last_question_type) {
				echo '<strong>Yes / No</strong><br />';
			}

			echo '
				<input type="radio" name="_'.$quest_array[$i].'" value="1" />';
			echo '
				<input type="radio" name="_'.$quest_array[$i].'" value="5" />';

			echo '&nbsp; '.stripslashes(db_result($result, 0, 'question'));

		} else if ($question_type == '4') {
			/*
				This is a comment only.
			*/

			echo '&nbsp;<br /><strong>'.util_make_links(stripslashes(db_result($result, 0, 'question'))).'</strong>';
			echo '
				<input type="hidden" name="_'.$quest_array[$i].'" value="-666" />';

		} else if ($question_type == '5') {
			/*
				This is a text-field question.
			*/

			echo stripslashes(db_result($result, 0, 'question')).'<br />';
			echo '
				<input type="text" name="_'.$quest_array[$i].'" size="20" maxlength="70" />';

		}
		else {
			// no answers, just show question
			echo stripslashes(db_result($result, 0, 'question')).'<br />';
		}

		echo '</td></tr>';

		$last_question_type=$question_type;
	}

	?>
	<tr><td align="center" colspan="2">

	<input type="submit" name="submit" value="<?php echo $Language->getText('general','submit') ?>" />
	<br />
	<a href="/survey/privacy.php"><?php echo $Language->getText('survey','survey_privacy') ?></a>
	</td></tr>
	</form>
	</table>
	<?php

} else {
	echo "<strong>".$Language->getText('survey','survey_not_found')."</strong>";
}

}

/**
 * Show a single question for the new user rating system
 *
 * @param		string	The question to show
 * @param		string	The array element
 */
function vote_show_a_question ($question,$element_name) {
	echo '
	<tr><td colspan="2" nowrap="nowrap">
	<input type="radio" name="Q_'. $element_name .'" value="-3">
	&nbsp; <input type="radio" name="Q_'. $element_name .'" value="-2" />
	&nbsp; <input type="radio" name="Q_'. $element_name .'" value="-1" />
	&nbsp; <input type="radio" name="Q_'. $element_name .'" value="0.1" />
	&nbsp; <input type="radio" name="Q_'. $element_name .'" value="1" />
	&nbsp; <input type="radio" name="Q_'. $element_name .'" value="2" />
	&nbsp; <input type="radio" name="Q_'. $element_name .'" value="3" />
	</td></tr>

	<tr><td colspan="2">'.$question.'
		<br />&nbsp;</td></tr>';

}

/*

	The ratings system is actually flexible enough
	to let you do N number of questions, but we are just going with 5
	that apply to everyone

*/

$USER_RATING_QUESTIONS=array();
//sorry - array starts at 1 so we can test for the questions on the receiving page
$USER_RATING_QUESTIONS[1]='Teamwork / Attitude';
$USER_RATING_QUESTIONS[2]='Code';
$USER_RATING_QUESTIONS[3]='Design / Architecture';
$USER_RATING_QUESTIONS[4]='Follow-Through / Reliability';
$USER_RATING_QUESTIONS[5]='Leadership / Management';

$USER_RATING_POPUP1[]='0 - Soloist';
$USER_RATING_POPUP1[]='1';
$USER_RATING_POPUP1[]='2';
$USER_RATING_POPUP1[]='3';
$USER_RATING_POPUP1[]='4';
$USER_RATING_POPUP1[]='5';
$USER_RATING_POPUP1[]='6 - Team Player';

$USER_RATING_POPUP2[]='0 - Beginner';
$USER_RATING_POPUP2[]='1';
$USER_RATING_POPUP2[]='2';
$USER_RATING_POPUP2[]='3';
$USER_RATING_POPUP2[]='4';
$USER_RATING_POPUP2[]='5';
$USER_RATING_POPUP2[]='6 - Master';

$USER_RATING_POPUP3[]='0 - Basic';
$USER_RATING_POPUP3[]='1';
$USER_RATING_POPUP3[]='2';
$USER_RATING_POPUP3[]='3';
$USER_RATING_POPUP3[]='4';
$USER_RATING_POPUP3[]='5';
$USER_RATING_POPUP3[]='6 - Elaborate';

$USER_RATING_POPUP4[]='0 - Unreliable';
$USER_RATING_POPUP4[]='1';
$USER_RATING_POPUP4[]='2';
$USER_RATING_POPUP4[]='3';
$USER_RATING_POPUP4[]='4';
$USER_RATING_POPUP4[]='5';
$USER_RATING_POPUP4[]='6 - Dependable';

$USER_RATING_POPUP5[]='0 - Weak';
$USER_RATING_POPUP5[]='1';
$USER_RATING_POPUP5[]='2';
$USER_RATING_POPUP5[]='3';
$USER_RATING_POPUP5[]='4';
$USER_RATING_POPUP5[]='5';
$USER_RATING_POPUP5[]='6 - Strong';

$USER_RATING_VALUES[]='-3';
$USER_RATING_VALUES[]='-2';
$USER_RATING_VALUES[]='-1';
$USER_RATING_VALUES[]='0.1';
$USER_RATING_VALUES[]='1';
$USER_RATING_VALUES[]='2';
$USER_RATING_VALUES[]='3';

/**
 * vote_show_user_rate_box() - Show user rating box
 *
 * @param		int		The user ID
 * @param		int		The user ID of the user who is rating $user_id
 */
function vote_show_user_rate_box ($user_id, $by_id=0) {
	if ($by_id) {
		$res = db_query("
			SELECT rate_field,rating FROM user_ratings
			WHERE rated_by='$by_id'
			AND user_id='$user_id'
		");
		$prev_vote = util_result_columns_to_assoc($res);
		while (list($k,$v) = each($prev_vote)) {
			if ($v == 0) {
				$prev_vote[$k] = 0.1;
			}
		}
	}

	global $USER_RATING_VALUES,$USER_RATING_QUESTIONS,$USER_RATING_POPUP1,$USER_RATING_POPUP2,$USER_RATING_POPUP3,$USER_RATING_POPUP4,$USER_RATING_POPUP5;
	echo '
	<table border="0">
		<form action="/developer/rate.php" method="post">
		<input type="hidden" name="rated_user" value="'.$user_id.'" />';

	for ($i=1; $i<=count($USER_RATING_QUESTIONS); $i++) {
		$popup="USER_RATING_POPUP$i";
		echo '<tr>
		<td colspan="2"><strong>'. $USER_RATING_QUESTIONS[$i] .':</strong><br /> '
		.html_build_select_box_from_arrays($USER_RATING_VALUES,$$popup,"Q_$i",$prev_vote[$i]/*'xzxz'*/,true,'Unrated').'</td></tr>';
	}

	echo '
		<tr><td colspan="2"><input type="submit" name="submit" value="Rate User" /></td></tr>
		</table>
	</form>';
}

/**
 * vote_show_user_rating() - Show a user rating
 *
 * @param		int		The user ID
 */
function vote_show_user_rating($user_id) {
	global $USER_RATING_QUESTIONS;
	$sql="SELECT rate_field,(avg(rating)+3) AS avg_rating,count(*) as count ".
		"FROM user_ratings ".
		"WHERE user_id='$user_id' ".
		"GROUP BY rate_field";
	$res=db_query($sql);
	$rows=db_numrows($res);
	if (!$res || $rows < 1) {

		echo '<tr><td colspan="2"><h4>Not Yet Rated</h4></td></tr>';

	} else {
		echo '<tr><td colspan="2">
			<h4>Current Ratings</h4>
			<p>
			Includes untrusted ratings.</p></td></tr>';
		for ($i=0; $i<$rows; $i++) {
			echo '
			<tr><td>'.$USER_RATING_QUESTIONS[db_result($res,$i,'rate_field')].'</td>
			<td>'.db_result($res,$i,'avg_rating').' (By '. db_result($res,$i,'count') .' Users)</td></tr>';
		}

		$res=db_query("SELECT ranking,metric,importance_factor FROM user_metric WHERE user_id='$user_id'");
		if ($res && db_numrows($res) > 0) {
			echo '<tr><td colspan="2"><strong>Trusted Overall Rating</strong></td></tr>';
			echo '<tr><td>Sitewide Ranking:</td><td><strong>'. db_result($res,0,'ranking') .'</strong></td></tr>
				<tr><td>Aggregate Score:</td><td><strong>'. number_format (db_result($res,0,'metric'),3) .'</strong></td></tr>
				<tr><td>Personal Importance:</td><td><strong>'. number_format (db_result($res,0,'importance_factor'),3) .'</strong></td></tr>';
		} else {
			echo '<tr><td colspan="2"><h4>Not Yet Included In Trusted Rankings</h4></td></tr>';
		}
	}
}

/**
 * vote_remove_all_ratings_by() - Remove all ratings by a particular user
 *
 * @param		int		The user ID
 */
function vote_remove_all_ratings_by($user_id) {
	db_query("
		DELETE FROM user_ratings
		WHERE rated_by='$user_id'
	");
}

?>
