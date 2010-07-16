<?php
/**
 * GForge Survey Facility
 *
 * Portions Copyright 1999-2001 (c) VA Linux Systems
 * The rest Copyright 2002-2004 (c) GForge Team
 * http://gforge.org/
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
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */


require_once('../env.inc.php');
require_once $gfcommon.'include/pre.php';

$HTML->header(array('title'=>_('Voting')));

if (!session_loggedin()) {
	echo '<div class="error">'._('You must be logged in to vote')."</div>";
} else {
	$vote_on_id = getIntFromRequest('vote_on_id');
	$response = getStringFromRequest('response');
	$flag = getStringFromRequest('flag');

	if ($vote_on_id && $response && $flag) {
		/*
			$flag
			1=project
			2=release
		*/
		$toss = db_query_params ('DELETE FROM survey_rating_response WHERE user_id=$1 AND type=$2 AND id=$3',
					 array(user_getid(),
					       $flag,
					       $vote_on_id));

		$result = db_query_params ('INSERT INTO survey_rating_response (user_id,type,id,response,post_date) VALUES ($1,$2,$3,$4,$5)',
					   array(user_getid(),
						 $flag,
						 $vote_on_id,
						 $response,
						 time()));
		if (!$result) {
			$feedback .= _('ERROR');
			echo "<h1>"._('Error in insert')."</h1>";
			echo db_error();
		} else {
			$feedback .= _('Vote registered');
			echo "<h2>"._('Vote registered')."</h2>";
			echo "<a href=\"javascript:history.back()\"><strong>"._('Click to return to previous page')."</strong></a>
<p>"._('If you vote again, your old vote will be erased.')."</p>";
		}
	} else {
		echo "<h1>"._('ERROR!!! MISSING PARAMS')."</h1>";
	}
}
$HTML->footer(array());
?>
