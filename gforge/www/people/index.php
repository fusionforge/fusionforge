<?php
//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net
//
// $Id$

require('pre.php');
require('../people/people_utils.php');

people_header(array('title'=>'Help Wanted System'));

if ($group_id) {

	echo '<H3>Project Help Wanted for '. group_getname($group_id) .'</H3>
	<P>
	Here is a list of positions available for this project.
	<P>';

	echo people_show_project_jobs($group_id);
	
} else if ($category_id) {

	echo '<H3>Projects looking for '. people_get_category_name($category_id) .'</H3>
		<P>
		Click job titles for more detailed descriptions.
		<P>';
	echo people_show_category_jobs($category_id);

} else {

	echo '
	<H3>Projects Needing Help</H3>
	The SourceForge Project Help Wanted board is for non-commercial, project
	volunteer openings. Commercial use is prohibited.
	<P>
	Project listings remain live for two weeks, or until closed by the
	poster, whichever comes first. (Project administrators may always
	re-post expired openings.)
	<P>
	Browse through the category menu to find projects looking for your help.
	<P>
	If you\'re a project admin, log in and submit help wanted requests through
	your project administration page.
	<P>
	To suggest new job categories, submit a request via the support manager.
		<P>';
	echo people_show_category_table();

        echo '<h4>Last posts</h4>';

	$sql="SELECT people_job.group_id,people_job.job_id,groups.group_name,groups.unix_group_name,people_job.title,people_job.date,people_job_category.name AS category_name ".
		"FROM people_job,people_job_category,groups ".
		"WHERE people_job.group_id=groups.group_id ".
		"AND people_job.category_id=people_job_category.category_id ".
		"AND people_job.status_id=1 ".
                "ORDER BY date DESC";
	$result=db_query($sql,5);
        echo people_show_job_list($result);
        echo '<p><a href="helpwanted-latest.php">[more latest posts]</a></p>';

}

people_footer(array());

?>
