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
	echo "<H2>You must be logged in to vote</H2>";
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
			echo "<H1>Error in insert</H1>";
			echo db_error();
		} else {
			$feedback .= " Vote registered ";
			echo "<H2>Vote Registered</H2>";
			echo "<A HREF=\"javascript:history.back()\"><B>Click to return to previous page</B></A>".
				"<P>If you vote again, your old vote will be erased.";
		}
	} else {
		echo "<H1>ERROR!!! MISSING PARAMS</H1>";
	}
}
$HTML->footer(array());
?>
