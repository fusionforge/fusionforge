<?php
//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net
//
// $Id$

require('pre.php');
require('../forum/forum_utils.php');

if (user_isloggedin()) {
	/*
		User obviously has to be logged in to monitor
		a thread
	*/

	if ($forum_id) {
		/*
			First check to see if they are already monitoring
			this thread. If they are, say so and quit.
			If they are NOT, then insert a row into the db
		*/

		$sql="SELECT * FROM forum_monitored_forums WHERE user_id='".user_getid()."' AND forum_id='$forum_id';";

		$result = db_query($sql);

		if (!$result || db_numrows($result) < 1) {
			/*
				User is not already monitoring thread, so 
				insert a row so monitoring can begin
			*/
			$sql="INSERT INTO forum_monitored_forums (forum_id,user_id) VALUES ('$forum_id','".user_getid()."')";

			$result = db_query($sql);

			if (!$result) {
				exit_error("ERROR","ERROR - could not insert into database");
			} else {
				header ("Location: /forum/forum.php?forum_id=$forum_id&feedback=".urlencode("Forum Is Now Being Monitored"));
			}

		} else {

			$sql="DELETE FROM forum_monitored_forums WHERE user_id='".user_getid()."' AND forum_id='$forum_id';";
			$result = db_query($sql);
			header ("Location: /forum/forum.php?forum_id=$forum_id&feedback=".urlencode("Forum Monitoring Deactivated"));
		}
		forum_footer(array());
	} else {
		forum_header(array('title'=>'Choose a forum First'));
		echo '
			<H1>Error - Choose a forum First</H1>';
		forum_footer(array());
	} 

} else {
	exit_not_logged_in();
}
?>
