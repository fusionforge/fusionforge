<?php
/**
  *
  * SourceForge Developer's Page
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

if (session_loggedin()) {

	$me = session_get_user();
	if (!$me->usesRatings()) {
		exit_error(
			$Language->getText('developer_rate','turned_off_title'),
			$Language->getText('developer_rate','turned_off_body')
			);
	}

	$ruser = $me->getID();
	if ($rated_user != $ruser) {
		//how many questions can they be rated on?
		$count=count($USER_RATING_QUESTIONS);

		//now iterate and insert each response
		for ($i=1; $i<=$count; $i++) {
			$resp="Q_$i";
			$rating = $$resp;
			if ($rating==100) {
				//unrated on this criteria
			} else {
				//ratings can only be between +3 and -3
				if ($rating > 3 || $rating < -3) {
					$feedback .= $Language->getText('developer_rate','invalid_rate_value');
				} else {
					if ($rating) {
						// get rid of 0.1 thing
						$rating = (int)$rating;
						//user did answer this question, so insert into db
						$res=db_query("SELECT * FROM user_ratings ".
							"WHERE rated_by='$ruser' AND user_id='$rated_user' AND rate_field='$i'");
						if ($res && db_numrows($res) > 0) {
							$res=db_query("DELETE FROM user_ratings ".
								"WHERE rated_by='$ruser' AND user_id='$rated_user' AND rate_field='$i'");
						}
						$res=db_query("INSERT INTO user_ratings (rated_by,user_id,rate_field,rating) ".
							"VALUES ('$ruser','$rated_user','$i','$rating')");
						echo db_error();
					}
				}
			}
		}
	} else {
		global $G_SESSION;
		exit_error($Language->getText('general','error'),$Language->getText('developer_rate','cannot_rate_yourself'));
	}

	echo $HTML->header(array('title'=>$Language->getText('developer_rate','title')));

	echo '
	<h3>'.$Language->getText('developer_rate','ratings_recorded').'</h3>
	<p>'.$Language->getText('developer_rate','ratings_recorded_body').'.</p>
	<p>&nbsp;</p>';

	echo $HTML->footer(array());

} else {
	exit_not_logged_in();
}

?>
