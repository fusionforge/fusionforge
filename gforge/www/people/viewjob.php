<?php
/**
  *
  * SourceForge Jobs (aka Help Wanted) Board
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */


require_once('pre.php');
require_once('www/people/people_utils.php');

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
		people_header(array('title'=>$Language->getText('people_viewjob','view_a_job'),'pagename'=>'people_viewjob'));
		echo db_error();
		$feedback .= $Language->getText('people_viewjob','fetch_failed');
		echo '<h2>'.$Language->getText('people_viewjob','no_such_posting').'</h2>';
	} else {

		people_header(array('title'=>$Language->getText('people_viewjob','view_a_job'),'pagename'=>'people_viewjob',
								'titlevals'=>array(db_result($result,0,'category_name'),db_result($result,0,'group_name')),
								'sectionvals'=>array(db_result($result,0,'group_name'))));

//		<h2>'. db_result($result,0,'category_name') .' wanted for '. db_result($result,0,'group_name') .'</h2>
		echo '
		<p />
		<table border="0" width="100%">
                <tr><td colspan="2">
			<strong>'. db_result($result,0,'title') .'</strong>
		</td></tr>

		<tr><td>
			<strong>'.$Language->getText('people_viewjob','contact_info').':<br />
			<a href="/sendmessage.php?touser='. db_result($result,0,'user_id') .'&amp;subject='. urlencode( 'RE: '.db_result($result,0,'title')) .'">'. db_result($result,0,'user_name') .'</a></strong>
		</td><td>
			<strong>'.$Language->getText('people','status').':</strong><br />
			'. db_result($result,0,'status_name') .'
		</td></tr>

		<tr><td>
			<strong>'.$Language->getText('people_viewjob','open_date').':</strong><br />
			'. date($sys_datefmt,db_result($result,0,'post_date')) .'
		</td><td>
			<strong>'.$Language->getText('people_viewjob','for_project').':<br />
			<a href="/project/?group_id='. $group_id .'">'. db_result($result,0,'group_name') .'</a></strong>
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
