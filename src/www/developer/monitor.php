<?php
/**
 * Monitor Diary Page
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 *
 * This file is part of FusionForge. FusionForge is free software;
 * you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or (at your option)
 * any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with FusionForge; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */


require_once('../env.inc.php');
require_once $gfcommon.'include/pre.php';

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

		site_user_header(array('title'=>_('Monitor a User')));

		$result = db_query_params ('SELECT * FROM user_diary_monitor WHERE user_id=$1 AND monitored_user=$2;',
					   array (user_getid(),
						  $diary_user));
		if (!$result || db_numrows($result) < 1) {
			/*
				User is not already monitoring thread, so 
				insert a row so monitoring can begin
			*/
			$result = db_query_params ('INSERT INTO user_diary_monitor (monitored_user,user_id) VALUES ($1,$2)',
						   array ($diary_user,
							  user_getid ()));

			if (!$result) {
				echo '<p class="error">' . _('Error inserting into user_diary_monitor') . '</p>';
			} else {
				echo '<p class="feedback">' . _('User is now being monitored') . '</p>';
				echo '<p>' . _("You will now be emailed this user's diary entries.") . '</p>';
				echo '<p>' . _('To turn off monitoring, simply click the <strong>Monitor user</strong> link again.') . '</p>';
			}

		} else {
			$result = db_query_params ('DELETE FROM user_diary_monitor WHERE user_id=$1 AND monitored_user=$2',
						   array (user_getid(),
							  $diary_user));
			echo '<p class="feedback">' . _('Monitoring has been turned off') . "</p>";
			echo _('You will not receive any more emails from this user');
	
		}
		$HTML->footer (array());
	} else {
		$HTML->header(array('title'=>_('Error - Choose a User To Monitor First')));
		$HTML->footer (array());
	} 

}

?>
