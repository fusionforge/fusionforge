<?php
/**
 * GForge rate user page
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
require_once('pre.php');
require_once('vote_function.php');

if (!session_loggedin()) {

	exit_not_logged_in();

} else {

	$me =& session_get_user();
	if (!$me->usesRatings()) {
		exit_error(
			$Language->getText('developer_rate','turned_off_title'),
			$Language->getText('developer_rate','turned_off_body')
		);
	}

	$rated_user = getStringFromRequest('rated_user');
	$ruser = $me->getID();
	if ($rated_user != $ruser) {
		//how many questions can they be rated on?
		$count=count($USER_RATING_QUESTIONS);

		//now iterate and insert each response
		for ($i=1; $i<=$count; $i++) {
			$resp="Q_$i";
			$rating = getStringFromRequest($resp);
			if ($rating==100) {
				//unrated on this criteria
			} else {
				//ratings can only be between +3 and -3
				if ($rating > 3 || $rating < -3) {
					$feedback .= $Language->getText('developer_rate','invalid_rate_value');
				} else {
					if ($rating) {
						// get rid of 0.1 thing
						$rating = (int)$rating;
						//user did answer this question, so insert into db
						$res=db_query("SELECT * FROM user_ratings ".
							"WHERE rated_by='$ruser' AND user_id='$rated_user' AND rate_field='$i'");
						if ($res && db_numrows($res) > 0) {
							$res=db_query("DELETE FROM user_ratings ".
								"WHERE rated_by='$ruser' AND user_id='$rated_user' AND rate_field='$i'");
						}
						$res=db_query("INSERT INTO user_ratings (rated_by,user_id,rate_field,rating) ".
							"VALUES ('$ruser','$rated_user','$i','$rating')");
						echo db_error();
					}
				}
			}
		}
	} else {
		exit_error($Language->getText('general','error'),$Language->getText('developer_rate','cannot_rate_yourself'));
	}

	echo $HTML->header(array('title'=>$Language->getText('developer_rate','title')));

	echo '
	<h3>'.$Language->getText('developer_rate','ratings_recorded').'</h3>
	<p>'.$Language->getText('developer_rate','ratings_recorded_body').'.</p>
	<p>&nbsp;</p>';

	echo $HTML->footer(array());

}

?>
