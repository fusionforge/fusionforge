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
require_once('../people/people_utils.php');

if ($group_id) {

	people_header(array('title'=>'Help Wanted System','pagename'=>'people_proj','titlevals'=>array(group_getname($group_id))));

	echo '
	<P>
	Here is a list of positions available for this project.
	<P>';

	echo people_show_project_jobs($group_id);
	
} else if ($category_id) {

	people_header(array('title'=>'Help Wanted System','pagename'=>'people_cat','titlevals'=>array(people_get_category_name($category_id))));

	echo '
		<P>
		Click job titles for more detailed descriptions.
		<P>';
	echo people_show_category_jobs($category_id);

} else {

	people_header(array('title'=>'Help Wanted System','pagename'=>'people'));

	echo $Language->getText('people','about_blurb');

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
