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


require_once('../env.inc.php');
require_once $gfwww.'include/pre.php';

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

		$HTML->header (array('title'=>_('Monitor a User')));

		echo '
			<h2>'._('Monitor a User').'</h2>';

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
				echo "<span class=\"error\">"._('Error inserting into user_diary_monitor')."</span>";
			} else {
				echo "<span class=\"feedback\">"._('User is now being monitored')."</span>";
				echo _('<p>You will now be emailed this user\'s diary entries.</p><p>To turn off monitoring, simply click the <strong>Monitor user</strong> link again.</p>');
			}

		} else {

			$sql="DELETE FROM user_diary_monitor WHERE user_id='".user_getid()."' AND monitored_user='$diary_user';";
			$result = db_query($sql);
			echo "<span class=\"feedback\">"._('Monitoring has been turned off')."</span>";
			echo _('You will not receive any more emails from this user');
	
		}
		$HTML->footer (array());
	} else {
		$HTML->header (array('title'=>_('Choose a User first')));
		echo '
			<h1>'._('Error - Choose a User To Monitor First').'</h1>';
		$HTML->footer (array());
	} 

}

?>
