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

$HTML->header(array('title'=>$Language->getText('survey_rating_resp','title')));

if (!session_loggedin()) {
	echo "<h2>".$Language->getText('survey_rating_resp','you_must_be_logged_in')."</h2>";
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
			$feedback .= $Language->getText('survey_rating_resp','error');
			echo "<h1>".$Language->getText('survey_rating_resp','error_in_insert')."</h1>";
			echo db_error();
		} else {
			$feedback .= $Language->getText('survey_rating_resp','vote_reg');
			echo "<h2>".$Language->getText('survey_rating_resp','vote_regsitered')."</h2>";
			echo "<a href=\"javascript:history.back()\"><strong>".$Language->getText('survey_rating_resp','click_to_resturn')."</strong></a>".
				"<p>".$Language->getText('survey_rating_resp','if_you_vote_again')."</p>";
		}
	} else {
		echo "<h1>".$Language->getText('survey_rating_resp','error_missing')."</h1>";
	}
}
$HTML->footer(array());
?>
