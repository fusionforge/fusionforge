<?php
/**
 * GForge Help Wanted 
 *
 * Copyright 1999-2001 (c) VA Linux Systems
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
require_once('www/people/people_utils.php');

if (!$sys_use_people) {
	exit_disabled();
}

$group_id = getIntFromRequest('group_id');
$job_id = getStringFromRequest('job_id');

if ($group_id && $job_id) {

	/*
		Fill in the info to create a job
	*/

	//for security, include group_id
	$sql="SELECT groups.group_name,people_job_category.name AS category_name,".
		"people_job_status.name AS status_name,people_job.title,".
		"people_job.description,people_job.post_date,users.user_name,users.user_id ".
		"FROM people_job,groups,people_job_status,people_job_category,users ".
		"WHERE people_job_category.category_id=people_job.category_id ".
		"AND people_job_status.status_id=people_job.status_id ".
		"AND users.user_id=people_job.created_by ".
		"AND groups.group_id=people_job.group_id ".
		"AND people_job.job_id='$job_id' AND people_job.group_id='$group_id'";
	$result=db_query($sql);
	if (!$result || db_numrows($result) < 1) {
		people_header(array('title'=>$Language->getText('people_viewjob','view_a_job')));
		echo db_error();
		$feedback .= $Language->getText('people_viewjob','fetch_failed');
		echo '<h2>'.$Language->getText('people_viewjob','no_such_posting').'</h2>';
	} else {

		people_header(array('title'=>$Language->getText('people_viewjob','view_a_job')));

//		<h2>'. db_result($result,0,'category_name') .' wanted for '. db_result($result,0,'group_name') .'</h2>
		echo '
		<p />
		<table border="0" width="100%">
                <tr><td colspan="2">
			<strong>'. db_result($result,0,'title') .'</strong>
		</td></tr>

		<tr><td>
			<strong>'.$Language->getText('people_viewjob','contact_info').':<br />
			<a href="'.$GLOBALS['sys_urlprefix'].'/sendmessage.php?touser='. db_result($result,0,'user_id') .'&amp;subject='. urlencode( 'RE: '.db_result($result,0,'title')) .'">'. db_result($result,0,'user_name') .'</a></strong>
		</td><td>
			<strong>'.$Language->getText('people','status').':</strong><br />
			'. db_result($result,0,'status_name') .'
		</td></tr>

		<tr><td>
			<strong>'.$Language->getText('people_viewjob','open_date').':</strong><br />
			'. date($sys_datefmt,db_result($result,0,'post_date')) .'
		</td><td>
			<strong>'.$Language->getText('people_viewjob','for_project').':<br />
			<a href="'.$GLOBALS['sys_urlprefix'].'/project/?group_id='. $group_id .'">'. db_result($result,0,'group_name') .'</a></strong>
		</td></tr>

		<tr><td colspan="2">
			<strong>'.$Language->getText('people','long_description').':</strong><p>
			'. nl2br(db_result($result,0,'description')) .'</p>
		</td></tr>
		<tr><td colspan="2">
		<h2>'.$Language->getText('people_viewjob','required_skills').':</h2>';

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
		exit_error($Language->getText('general','error'),$Language->getText('people_viewjob','posting_id_not_found'));
	}
}

?>
