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

$HTML->header(array('title'=>$Language->getText('survey_rating_resp','title')));

if (!session_loggedin()) {
	echo "<h2>".$Language->getText('survey_rating_resp','you_must_be_logged_in')."</h2>";
} else {
	if ($vote_on_id && $response && $flag) {
		/*
			$flag
			1=project
			2=release
		*/

		$sql="DELETE FROM survey_rating_response WHERE user_id='".user_getid()."' AND type='$flag' AND id='$vote_on_id'";
		$toss=db_query($sql);

		$sql="INSERT INTO survey_rating_response (user_id,type,id,response,date) ".
			"VALUES ('".user_getid()."','$flag','$vote_on_id','$response','".time()."')";
		$result=db_query($sql);
		if (!$result) {
			$feedback .= $Language->getText('survey_rating_resp','error');
			echo "<h1>".$Language->getText('survey_rating_resp','error_in_insert')."</h1>";
			echo db_error();
		} else {
			$feedback .= $Language->getText('survey_rating_resp','vote_reg');
			echo "<h2>".$Language->getText('survey_rating_resp','vote_regsitered')."</h2>";
			echo "<a href=\"javascript:history.back()\"><strong>".$Language->getText('survey_rating_resp','click_to_resturn')."</strong></a>".
				"<p>".$Language->getText->('survey_rating_resp','if_you_vote_again')."</p>";
		}
	} else {
		echo "<h1>".$Language->getText('survey_rating_resp','error_missing')."</h1>";
	}
}
$HTML->footer(array());
?>
