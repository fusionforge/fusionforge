<?php
/**
 * FusionForge rate user page
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2010 (c) Franck Villaume
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
require_once $gfwww.'include/vote_function.php';

if (!session_loggedin()) {
	exit_not_logged_in();
} else {

	$me =& session_get_user();
	if (!$me->usesRatings()) {
		exit_error(_('Ratings turned off. You chose not to participate in the peer rating system'),'my');
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
					$warning_msg .= _('Invalid rate value');
				} else {
					if ($rating) {
						// get rid of 0.1 thing
						$rating = (int)$rating;
						//user did answer this question, so insert into db
						$res=db_query_params ('SELECT * FROM user_ratings WHERE rated_by=$1 AND user_id=$2 AND rate_field=$3',
								array($ruser,
									$rated_user,
									$i)) ;

						if ($res && db_numrows($res) > 0) {
							$res=db_query_params ('DELETE FROM user_ratings WHERE rated_by=$1 AND user_id=$2 AND rate_field=$3',
									array($ruser,
										$rated_user,
										$i)) ;

						}
						$res=db_query_params ('INSERT INTO user_ratings (rated_by,user_id,rate_field,rating) VALUES ($1,$2,$3,$4)',
									array($ruser,
										$rated_user,
										$i,
										$rating)) ;

						if(db_error()) {
							exit_error(db_error(),'my');
						}
					}
				}
			}
		}
	} else {
		exit_error(_('You can\'t rate yourself'),'my');
	}

	echo $HTML->header(array('title'=>_('User Ratings Page')));

	echo '
	<h3>'._('Ratings Recorded').'</h3>
	<p>'._('You can re-rate this person by simply returning to their ratings page and re-submitting the info.').'.</p>
	<p>&nbsp;</p>';

	echo $HTML->footer(array());
}

?>
