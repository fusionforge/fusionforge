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

people_header(array('title'=>$Language->getText('people_help_wanted_latest','title'),'pagename'=>'people_helpwanted_latest'));

{
        echo '<p>';

	$sql="SELECT people_job.group_id,people_job.job_id,groups.group_name,groups.unix_group_name,people_job.title,people_job.date,people_job_category.name AS category_name ".
		"FROM people_job,people_job_category,groups ".
		"WHERE people_job.group_id=groups.group_id ".
		"AND people_job.category_id=people_job_category.category_id ".
		"AND people_job.status_id=1 ".
                "ORDER BY date DESC";
	$result=db_query($sql,30);
        echo people_show_job_list($result) . '</p>';

}

people_footer(array());

?>
