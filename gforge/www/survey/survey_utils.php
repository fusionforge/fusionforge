<?php
/**
  *
  * SourceForge Survey Facility
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */


/*
	Survey System
	By Tim Perdue, Sourceforge, 11/99
*/

function survey_header($params) {
	global $group_id,$is_admin_page,$DOCUMENT_ROOT;

	$params['toptab']='surveys';
	$params['group']=$group_id;

	if ($project =& group_get_object($group_id)){
		if (!$project->usesSurvey()) {
			exit_error('Error','This Group Has Turned Off Surveys');
		}
		
		site_project_header($params);
		
		echo "<p><strong><a href=\"/survey/admin/?group_id=$group_id\">Admin</a>";
		
		if ($is_admin_page && $group_id) {
			echo " | <a href=\"/survey/admin/add_survey.php?group_id=$group_id\">Add Surveys</a>";
			echo " | <a href=\"/survey/admin/edit_survey.php?group_id=$group_id\">Edit Surveys</a>";
			echo " | <a href=\"/survey/admin/add_question.php?group_id=$group_id\">Add Questions</a>";
			echo " | <a href=\"/survey/admin/show_questions.php?group_id=$group_id\">Edit Questions</a>";
			echo " | <a href=\"/survey/admin/show_results.php?group_id=$group_id\">Show Results</a></strong>";
		}
		
		echo "</p>";
	}// end if (valid group id)
}

function survey_footer($params) {
	site_project_footer($params);
}

?>
