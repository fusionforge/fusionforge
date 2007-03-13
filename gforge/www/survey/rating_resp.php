<?php
/**
 * GForge Survey Facility
 *
 * Portions Copyright 1999-2001 (c) VA Linux Systems
 * The rest Copyright 2002-2004 (c) GForge Team
 * http://gforge.org/
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
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */


require_once('../env.inc.php');
require_once('pre.php');

$HTML->header(array('title'=>_('Voting')));

if (!session_loggedin()) {
	echo "<h2>"._('You must be logged in to vote')."</h2>";
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

		$sql="DELETE FROM survey_rating_response WHERE user_id='".user_getid()."' AND type='$flag' AND id='$vote_on_id'";
		$toss=db_query($sql);

		$sql="INSERT INTO survey_rating_response (user_id,type,id,response,post_date) ".
			"VALUES ('".user_getid()."','$flag','$vote_on_id','$response','".time()."')";
		$result=db_query($sql);
		if (!$result) {
			$feedback .= _('ERROR');
			echo "<h1>"._('Error in insert')."</h1>";
			echo db_error();
		} else {
			$feedback .= _('Vote registered');
			echo "<h2>"._('Vote Registered')."</h2>";
			echo "<a href=\"javascript:history.back()\"><strong>"._('Click to return to previous page')."</strong></a>".
				"<p>"._('If you vote again, your old vote will be erased.')."</p>";
		}
	} else {
		echo "<h1>"._('ERROR!!! MISSING PARAMS')."</h1>";
	}
}
$HTML->footer(array());
?>
