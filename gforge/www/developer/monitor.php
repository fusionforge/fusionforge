<?php
//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net
//
// $Id$

require('pre.php');

if (user_isloggedin()) {
	/*
		User obviously has to be logged in to monitor
	*/

	if ($user_id) {
		/*
			First check to see if they are already monitoring
			If they are, unmonitor by deleting row.
			If they are NOT, then insert a row into the db
		*/

		$HTML->header (array('title'=>'Monitor a User'));

		echo '
			<H2>Monitor a User</H2>';

		$sql="SELECT * FROM user_diary_monitor WHERE user_id='".user_getid()."' AND monitored_user='$user_id';";

		$result = db_query($sql);

		if (!$result || db_numrows($result) < 1) {
			/*
				User is not already monitoring thread, so 
				insert a row so monitoring can begin
			*/
			$sql="INSERT INTO user_diary_monitor (monitored_user,user_id) VALUES ('$user_id','".user_getid()."')";

			$result = db_query($sql);

			if (!$result) {
				echo "<FONT COLOR=\"RED\">Error inserting into user_diary_monitor</FONT>";
			} else {
				echo "<FONT COLOR=\"RED\"><H3>User is now being monitored</H3></FONT>";
				echo "<P>You will now be emailed this user's diary entries.";
				echo "<P>To turn off monitoring, simply click the <B>Monitor user</B> link again.";
			}

		} else {

			$sql="DELETE FROM user_diary_monitor WHERE user_id='".user_getid()."' AND monitored_user='$user_id';";
			$result = db_query($sql);
			echo "<FONT COLOR=\"RED\"><H3>Monitoring has been turned off</H3></FONT>";
			echo "<P>You will not receive any more emails from this user.";
		}
		$HTML->footer (array());
	} else {
		$HTML->header (array('title'=>'Choose a user First'));
		echo '
			<H1>Error - Choose a User To Monitor First</H1>';
		$HTML->footer (array());
	} 

} else {
	exit_not_logged_in();
}

?>
