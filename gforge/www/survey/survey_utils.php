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
	global $group_id,$is_admin_page,$DOCUMENT_ROOT,$Language,$sys_use_survey;

	if (!$sys_use_survey) {
		exit_disabled();
	}

	$params['toptab']='surveys';
	$params['group']=$group_id;

	if ($project =& group_get_object($group_id)){
		if (!$project->usesSurvey()) {
			exit_error($Language->getText('general','error'), $Language->getText('survey_utils','error_this_group_has_turned_off'));
		}
		
		site_project_header($params);
		
		echo "<p><strong><a href=\"/survey/admin/?group_id=$group_id\">".$Language->getText('survey_utils','admin')."</a>";
		
		if ($is_admin_page && $group_id) {
			echo " | <a href=\"/survey/admin/add_survey.php?group_id=$group_id\">".$Language->getText('survey_utils','add_survey')."</a>";
			echo " | <a href=\"/survey/admin/edit_survey.php?group_id=$group_id\">".$Language->getText('survey_utils','edit_survey')."</a>";
			echo " | <a href=\"/survey/admin/add_question.php?group_id=$group_id\">".$Language->getText('survey_utils','add_question')."</a>";
			echo " | <a href=\"/survey/admin/show_questions.php?group_id=$group_id\">".$Language->getText('survey_utils','edit_questions')."</a>";
			echo " | <a href=\"/survey/admin/show_results.php?group_id=$group_id\">".$Language->getText('survey_utils','show_results')."</a></strong>";
		}
		
		echo "</p>";
	}// end if (valid group id)
}

function survey_footer($params) {
	site_project_footer($params);
}

?>
