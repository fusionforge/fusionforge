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

if (session_loggedin()) {
	/*
		User obviously has to be logged in to monitor
	*/

	if ($diary_user) {
		/*
			First check to see if they are already monitoring
			If they are, unmonitor by deleting row.
			If they are NOT, then insert a row into the db
		*/

		$HTML->header (array('title'=>'Monitor a User'));

		echo '
			<h2>Monitor a User</h2>';

		$sql="SELECT * FROM user_diary_monitor WHERE user_id='".user_getid()."' AND monitored_user='$diary_user';";

		$result = db_query($sql);

		if (!$result || db_numrows($result) < 1) {
			/*
				User is not already monitoring thread, so 
				insert a row so monitoring can begin
			*/
			$sql="INSERT INTO user_diary_monitor (monitored_user,user_id) VALUES ('$diary_user','".user_getid()."')";

			$result = db_query($sql);

			if (!$result) {
				echo "<span style="color:red">Error inserting into user_diary_monitor</span>";
			} else {
				echo "<h3 style="color:red">User is now being monitored</h3>";
				echo "<p>You will now be emailed this user's diary entries.</p>";
				echo "<p>To turn off monitoring, simply click the <strong>Monitor user</strong> link again.</p>";
			}

		} else {

			$sql="DELETE FROM user_diary_monitor WHERE user_id='".user_getid()."' AND monitored_user='$diary_user';";
			$result = db_query($sql);
			echo "<h3 style="color:red">Monitoring has been turned off</h3>";
			echo "<p>You will not receive any more emails from this user.</p>";
		}
		$HTML->footer (array());
	} else {
		$HTML->header (array('title'=>'Choose a user First'));
		echo '
			<h1>Error - Choose a User To Monitor First</h1>';
		$HTML->footer (array());
	} 

} else {
	exit_not_logged_in();
}

?>
