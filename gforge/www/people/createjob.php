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

if ($group_id && (user_ismember($group_id, 'A'))) {

	/*
		Fill in the info to create a job
	*/
	people_header(array('title'=>'Create a job for your project','pagename'=>'people_createjob'));

	echo '
		<p>Start by filling in the fields below. When you click continue, you
		will be shown a list of skills and experience levels that this job requires.</p>
		<p>
		<form action="/people/editjob.php" method="post">
		<input type="hidden" name="group_id" value="'.$group_id.'" />
		<strong>Category:</strong>'.utils_requiredField().'<br /></p>
		'. people_job_category_box('category_id') .'
		<p>
		<strong>Short Description:</strong>'.utils_requiredField().'<br />
		<input type="text" name="title" value="" size="40" maxlength="60" /></p>
		<p>
		<strong>Long Description:</strong>'.utils_requiredField().'<br />
		<textarea name="description" rows="10" cols="60" wrap="soft"></textarea></p>
		<p>
		<input type="submit" name="add_job" value="Continue >>" />
		</form></p>';

	people_footer(array());

} else {
	/*
		Not logged in or insufficient privileges
	*/
	if (!$group_id) {
		exit_no_group();
	} else {
		exit_permission_denied();
	}
}
?>
