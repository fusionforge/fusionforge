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

$HTML->header(array('title'=>'Voting'));

if (!session_loggedin()) {
	echo "<h2>You must be logged in to vote</h2>";
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
			$feedback .= " ERROR ";
			echo "<h1>Error in insert</h1>";
			echo db_error();
		} else {
			$feedback .= " Vote registered ";
			echo "<h2>Vote Registered</h2>";
			echo "<a href=\"javascript:history.back()\"><strong>Click to return to previous page</strong></a>".
				"<p>If you vote again, your old vote will be erased.</p>";
		}
	} else {
		echo "<h1>ERROR!!! MISSING PARAMS</h1>";
	}
}
$HTML->footer(array());
?>
