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
		<P>
		Start by filling in the fields below. When you click continue, you 
		will be shown a list of skills and experience levels that this job requires.
		<P>
		<FORM ACTION="/people/editjob.php" METHOD="POST">
		<INPUT TYPE="HIDDEN" NAME="group_id" VALUE="'.$group_id.'">
		<B>Category:</B><BR>
		'. people_job_category_box('category_id') .'
		<P>
		<B>Short Description:</B><BR>
		<INPUT TYPE="TEXT" NAME="title" VALUE="" SIZE="40" MAXLENGTH="60">
		<P>
		<B>Long Description:</B><BR>
		<TEXTAREA NAME="description" ROWS="10" COLS="60" WRAP="SOFT"></TEXTAREA>
		<P>
		<INPUT TYPE="SUBMIT" NAME="add_job" VALUE="Continue >>">
		</FORM>';

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
