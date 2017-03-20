<?php
/**
 * FusionForge Project Management Facility
 *
 * Copyright 1999/2000, Tim Perdue - Sourceforge
 * Copyright 2002 GForge, LLC
 * Copyright 2010, FusionForge Team
 * Copyright 2014, 2015 Franck Villaume - TrivialDev
 * http://fusionforge.org
 *
 * This file is part of FusionForge. FusionForge is free software;
 * you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option)
 * any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with FusionForge; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

require_once $gfcommon.'include/FFError.class.php';
require_once $gfcommon.'pm/ProjectTask.class.php';

class ProjectTaskHTML extends ProjectTask {

	function __construct(&$ProjectGroup, $project_task_id=false, $arr=false) {
		parent::__construct($ProjectGroup, $project_task_id, $arr);
	}

	function multipleDependBox ($name='dependent_on[]') {
		$result=$this->getOtherTasks();
		//get the data so we can mark items as SELECTED
		$arr2 = array_keys($this->getDependentOn());
		return html_build_multiple_select_box ($result,$name,$arr2,8,false);
	}

	function multipleAssignedBox ($name='assigned_to[]') {
		$engine = RBACEngine::getInstance();
		$techs = $engine->getUsersByAllowedAction ('pm', $this->ProjectGroup->getID(), 'tech') ;

		$tech_id_arr = array();
		$tech_name_arr = array();

		foreach ($techs as $tech) {
			$tech_id_arr[] = $tech->getID() ;
			$tech_name_arr[] = $tech->getRealName() ;
		}

		//get the data so we can mark items as SELECTED
		$arr2 = $this->getAssignedTo();
		return html_build_multiple_select_box_from_arrays($tech_id_arr,$tech_name_arr,$name,$arr2);
	}

	function showDependentTasks () {
		global $HTML;
		$result=db_query_params ('SELECT project_task.project_task_id,project_task.summary
			FROM project_task,project_dependencies
			WHERE project_task.project_task_id=project_dependencies.project_task_id
			AND project_dependencies.is_dependent_on_task_id=$1',
			array($this->getID()));
		$rows=db_numrows($result);
		if ($rows > 0) {
			echo html_e('h3', array(), _('Tasks That Depend on This Task'));

			$title_arr=array();
			$title_arr[]=_('Task Id');
			$title_arr[]=_('Task Summary');

			echo $HTML->listTableTop($title_arr);

			for ($i = 0; $i < $rows; $i++) {
				$cells = array();
				$cells[][] = util_make_link('/pm/task.php?func=detailtask&project_task_id='. db_result($result, $i, 'project_task_id'). '&group_id='. $this->ProjectGroup->Group->getID() . '&group_project_id='. $this->ProjectGroup->getID(), db_result($result, $i, 'project_task_id'));
				$cells[][] = db_result($result, $i, 'summary');
				echo $HTML->multiTableRow(array('class' => $HTML->boxGetAltRowStyle($i, true)), $cells);
			}
			echo $HTML->listTableBottom();
		} else {
			echo html_e('p', array(), _('No Tasks are Dependent on This Task'));
		}
	}

	function showRelatedArtifacts() {
		global $HTML;
		$res=$this->getRelatedArtifacts();

		$rows=db_numrows($res);
		if ($rows > 0) {
			if (forge_check_perm ('pm_admin', $this->ProjectGroup->Group->getID())) {
				$is_admin=false;
			} else {
				$is_admin=true;
			}

			echo html_e('h3', array(), _('Related Tracker Items'));

			$title_arr=array();
			$title_arr[]=_('Artifact Summary');
			$title_arr[]=_('Tracker');
			$title_arr[]=_('Status');
			$title_arr[]=_('Open Date');
			(($is_admin) ? $title_arr[]=_('Remove Relation') : '');

			echo $HTML->listTableTop($title_arr);

			for ($i = 0; $i < $rows; $i++) {
				$cells = array();
				$cells[][] = util_make_link('/tracker/?func=detail&aid='.db_result($res,$i,'artifact_id').'&group_id='.db_result($res,$i,'group_id').'&atid='.db_result($res,$i,'group_artifact_id'), db_result($res,$i,'summary'));
				$cells[][] = db_result($res,$i,'name');
				$cells[][] = db_result($res,$i,'status_name');
				$cells[][] = date(_('Y-m-d H:i'),db_result($res,$i,'open_date'));
				if ($is_admin) {
					$cells[][] = '<input type="checkbox" name="rem_artifact_id[]" value="'.db_result($res,$i,'artifact_id').'" />';
				}
				echo $HTML->multiTableRow(array('class' => $HTML->boxGetAltRowStyle($i, true)), $cells);
			}
			echo $HTML->listTableBottom();
		} else {
			echo html_e('p', array(), _('No Related Tracker Items Have Been Added'));
		}
	}

	function showMessages($asc = true, $whereto = '/') {
		global $HTML;
		/*
			Show the details rows from task_history
		*/
		$result=$this->getMessages($asc);
		$rows=db_numrows($result);

		if ($rows > 0) {
			$title = _('Comments')._(': ');

			if ($asc) {
				$title .= util_make_link($whereto.'&commentsort=anti', _('Sort comments antichronologically'));
			} else {
				$title .= util_make_link($whereto.'&commentsort=chrono', _('Sort comments chronologically'));
			}
			echo html_e('h3', array(), $title);

			$title_arr = array();
			$title_arr[] = _('Comment');
			$title_arr[] = _('Date');
			$title_arr[] = _('By');

			echo $HTML->listTableTop($title_arr);

			for ($i = 0; $i < $rows; $i++) {
				$cells = array();
				$sanitizer = new TextSanitizer();
				$body = $sanitizer->SanitizeHtml(db_result($result, $i, 'body'));
				if (strpos($body,'<') === false) {
					$cells[][] = nl2br(db_result($result, $i, 'body'));
				} else {
					$cells[][] = $body;
				}
				$cells[][] = date(_('Y-m-d H:i'),db_result($result, $i, 'postdate'));
				$cells[][] = db_result($result, $i, 'user_name');
				echo $HTML->multiTableRow(array('class' => $HTML->boxGetAltRowStyle($i, true)), $cells);
			}
			echo $HTML->listTableBottom();
		} else {
			echo html_e('p', array(), _('No Comments Have Been Posted'));
		}
	}

	function showHistory() {
		global $HTML;
		/*
			show the project_history rows that are
			relevant to this project_task_id, excluding details
		*/
		$result=$this->getHistory();
		$rows=db_numrows($result);

		if ($rows > 0) {

			echo html_e('h3', array(), _('Task Change History'));

			$title_arr=array();
			$title_arr[]=_('Field');
			$title_arr[]=_('Old Value');
			$title_arr[]=_('Date');
			$title_arr[]=_('By');

			echo $HTML->listTableTop($title_arr);

			for ($i = 0; $i < $rows; $i++) {
				$field = db_result($result, $i, 'field_name');
				$cells = array();
				$cells[][] = $field;
				$content = '';
				if ($field == 'status_id') {
//tdP - convert to actual status name
					$content .= db_result($result, $i, 'old_value');
				} elseif ($field == 'category_id') {
//tdP convert to actual category_name
					$content .= db_result($result, $i, 'old_value');
				} elseif ($field == 'start_date') {
					$content .= date('Y-m-d', db_result($result, $i, 'old_value'));
				} elseif ($field == 'end_date') {
					$content .= date('Y-m-d', db_result($result, $i, 'old_value'));
				} else {
					$content .= db_result($result, $i, 'old_value');
				}
				$cells[][] = $content;
				$cells[][] = date(_('Y-m-d H:i'),db_result($result, $i, 'mod_date'));
				$cells[][] = db_result($result, $i, 'user_name');
				echo $HTML->multiTableRow(array('class' => $HTML->boxGetAltRowStyle($i, true)), $cells);
			}
			echo $HTML->listTableBottom();
		} else {
			echo html_e('p', array(), _('No Changes Have Been Made'));
		}
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
