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
		User obviously has to be logged in to save place 
	*/

	if ($forum_id) {
		/*
			First check to see if they already saved their place 
			If they have NOT, then insert a row into the db

			ELSE update the time()
		*/

		$sql="SELECT * FROM forum_saved_place WHERE user_id='".user_getid()."' AND forum_id='$forum_id'";

		$result = db_query($sql);

		if (!$result || db_numrows($result) < 1) {
			/*
				User is not already monitoring thread, so 
				insert a row so monitoring can begin
			*/
			$sql="INSERT INTO forum_saved_place (forum_id,user_id,save_date) VALUES ('$forum_id','".user_getid()."','".time()."')";

			$result = db_query($sql);

			if (!$result) {
				exit_error("ERROR","ERROR INSERTING PLACE");
			} else {
				header ("Location: /forum/forum.php?forum_id=$forum_id&feedback=".urlencode("Forum Position Saved. New messages will be highlighted when you return"));
			}

		} else {
			$sql="UPDATE forum_saved_place SET save_date='".time()."' WHERE user_id='".user_getid()."' AND forum_id='$forum_id'";
			$result = db_query($sql);

			if (!$result) {
				exit_error("ERROR","ERROR UPDATING PLACE");
			} else {
				header ("Location: /forum/forum.php?forum_id=$forum_id&feedback=".urlencode("Forum Position Saved. New messages will be highlighted when you return"));
			}
		} 
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
