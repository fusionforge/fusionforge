<?php
/**
 * Help Wanted
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2002-2004 (c) GForge Team
 * http://fusionforge.org/
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
require_once $gfwww.'people/people_utils.php';

if (!forge_get_config('use_people')) {
	exit_disabled('home');
}

$group_id = getIntFromRequest('group_id');
$job_id = getIntFromRequest('job_id');

if ($group_id && $job_id) {

	/*
		Fill in the info to create a job
	*/

	//for security, include group_id
	$result=db_query_params("SELECT groups.group_name,people_job_category.name AS category_name,
people_job_status.name AS status_name,people_job.title,
people_job.description,people_job.post_date,users.user_name,users.user_id
FROM people_job,groups,people_job_status,people_job_category,users
WHERE people_job_category.category_id=people_job.category_id
AND people_job_status.status_id=people_job.status_id
AND users.user_id=people_job.created_by
AND groups.group_id=people_job.group_id
AND people_job.job_id=$1 AND people_job.group_id=$2",
array($job_id, $group_id));
	if (!$result || db_numrows($result) < 1) {
		$error_msg .= _('POSTING fetch FAILED: No such posting for this project :').db_error();
		people_header(array('title'=>_('View a Job')));
	} else {

		people_header(array('title'=>_('View a Job')));

//		<h2>'. db_result($result,0,'category_name') .' wanted for '. db_result($result,0,'group_name') .'</h2>
		echo '
		<p />
		<table border="0" width="100%">
                <tr><td colspan="2">
			<strong>'. db_result($result,0,'title') .'</strong>
		</td></tr>

		<tr><td>
			<strong>'._('Contact Info').'<br />
			'.util_make_link ('/sendmessage.php?touser='. db_result($result,0,'user_id') .'&amp;subject='. urlencode( 'RE: '.db_result($result,0,'title')), db_result($result,0,'user_name')) .'</strong>
		</td><td>
			<strong>'._('Status').'</strong><br />
			'. db_result($result,0,'status_name') .'
		</td></tr>

		<tr><td>
			<strong>'._('Open Date').'</strong><br />
			'. date(_('Y-m-d H:i'),db_result($result,0,'post_date')) .'
		</td><td>
			<strong>'._('For project').'<br />
			'.util_make_link ('/project/?group_id='. $group_id, db_result($result,0,'group_name')) .'</strong>
		</td></tr>

		<tr><td colspan="2">
			<strong>'._('Long Description').'</strong><p>
			'. nl2br(db_result($result,0,'description')) .'</p>
		</td></tr>
		<tr><td colspan="2">
		<h2>'._('Required Skills').'</h2>';

		//now show the list of desired skills
		echo people_show_job_inventory($job_id).'</td></tr></table>';
	}

	people_footer(array());

} else {
	/*
		Not logged in or insufficient privileges
	*/
	if (!$group_id) {
		exit_no_group();
	} else {
		exit_error(_('Posting ID not found'),'home');
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
