<?php
/**
 * FusionForge Survey Facility
 *
 * Portions Copyright 1999-2001 (c) VA Linux Systems, Tim Perdue
 * Copyright 2002-2004 (c) GForge Team
 * Copyright 2010 (c) Franck Villaume
 * http://fusionforge.org/
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */


function survey_header($params) {
	global $group_id,$is_admin_page,$HTML;

	if (!forge_get_config('use_survey')) {
		exit_disabled();
	}

	$params['toptab']='surveys';
	$params['group']=$group_id;

	if ($project =& group_get_object($group_id)){
		if (!$project->usesSurvey()) {
			exit_disabled();
		}
		
		site_project_header($params);
		
		if ($is_admin_page && $group_id) {
			echo ($HTML->subMenu(
				array(
					_('Surveys'),
					_('Admin'),
					_('Add Survey'),
					_('Edit Survey'),
					_('Add Questions'),
					_('Edit Questions'),
					_('Show Results')
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
			if (forge_check_perm ('project_admin', $group_id)) {
				echo ($HTML->subMenu(
					      array(
						      _('Admin')
						      ),
					      array(
						      '/survey/admin/?group_id='.$group_id
						      )
					      ));
			}
		}
	}// end if (valid group id)
}

function survey_footer($params) {
	site_project_footer($params);
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
