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
require_once('www/project/admin/project_admin_utils.php');

if ($group_id && (user_ismember($group_id, 'A'))) {

	project_admin_header(array());

	/*
		Fill in the info to create a job
	*/
	echo '
		<p>'.$Language->getText('people_createjob','explains').'	</p>
		<p>
		<form action="/people/editjob.php" method="post">
		<input type="hidden" name="group_id" value="'.$group_id.'" />
		<strong>'.$Language->getText('people','category').':</strong>'.utils_requiredField().'<br /></p>
		'. people_job_category_box('category_id') .'
		<p>
		<strong>'.$Language->getText('people','short_description').':</strong>'.utils_requiredField().'<br />
		<input type="text" name="title" value="" size="40" maxlength="60" /></p>
		<p>
		<strong>'.$Language->getText('people','long_description').':</strong>'.utils_requiredField().'<br />
		<textarea name="description" rows="10" cols="60" wrap="soft"></textarea></p>
		<p>
		<input type="submit" name="add_job" value="'.$Language->getText('people_createjob','continue').'" />
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
