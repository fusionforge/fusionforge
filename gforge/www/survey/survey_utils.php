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
	global $group_id,$is_admin_page,$HTML,$DOCUMENT_ROOT,$Language,$sys_use_survey;

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
		
		if ($is_admin_page && $group_id) {
			echo ($HTML->subMenu(
				array(
					$Language->getText('group','short_survey'),
					$Language->getText('survey_utils','admin'),
					$Language->getText('survey_utils','add_survey'),
					$Language->getText('survey_utils','edit_survey'),
					$Language->getText('survey_utils','add_question'),
					$Language->getText('survey_utils','edit_questions'),
					$Language->getText('survey_utils','show_results')
				),
				array(
					'/survey/?group_id='.$group_id,
					'/survey/admin/?group_id='.$group_id,
					'/survey/admin/add_survey.php?group_id='.$group_id,
					'/survey/admin/edit_survey.php?group_id='.$group_id,
					'/survey/admin/add_question.php?group_id='.$group_id,
					'/survey/admin/show_questions.php?group_id='.$group_id,
					'/survey/admin/show_results.php?group_id='.$group_id
				)
			));
		} else {
			echo ($HTML->subMenu(
				array(
					$Language->getText('group','short_survey'),
					$Language->getText('survey_utils','admin')
				),
				array(
					'/survey/?group_id='.$group_id,
					'/survey/admin/?group_id='.$group_id
				)
			));
		}
	}// end if (valid group id)
}

function survey_footer($params) {
	site_project_footer($params);
}

?>
