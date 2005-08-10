<?php
/**
 * GForge Monitor Diary Page
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 *
 * @version   $Id$
 *
 * This file is part of GForge.
 *
 * GForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA
 */


require_once('pre.php');

if (!session_loggedin()) {

	exit_not_logged_in();

} else {

	/*
		User obviously has to be logged in to monitor
	*/

	$diary_user = getStringFromRequest('diary_user');
	if ($diary_user) {
		/*
			First check to see if they are already monitoring
			If they are, unmonitor by deleting row.
			If they are NOT, then insert a row into the db
		*/

		$HTML->header (array('title'=>$Language->getText('developer_monitor','title')));

		echo '
			<h2>'.$Language->getText('developer_monitor','title').'</h2>';

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
				echo "<span style=\"color:red\">".$Language->getText('developer_monitor','error_inserting')."</span>";
			} else {
				echo "<h3 style=\"color:red\">".$Language->getText('developer_monitor','monitoring_user')."</h3>";
				echo $Language->getText('developer_monitor','monitoring_user_expl');
			}

		} else {

			$sql="DELETE FROM user_diary_monitor WHERE user_id='".user_getid()."' AND monitored_user='$diary_user';";
			$result = db_query($sql);
			echo "<h3 style=\"color:red\">".$Language->getText('developer_monitor','monitoring_user_off')."</h3>";
			echo $Language->getText('developer_monitor','monitoring_user_off_expl');
	
		}
		$HTML->footer (array());
	} else {
		$HTML->header (array('title'=>$Language->getText('developer_monitor_choose_user','title')));
		echo '
			<h1>'.$Language->getText('developer_monitor_choose_user','body').'</h1>';
		$HTML->footer (array());
	} 

}

?>
