<?php
/**
 * FusionForge Project Management Facility
 *
 * Copyright 1999/2000, Tim Perdue - Sourceforge
 * Copyright 2002 GForge, LLC
 * Copyright 2010, FusionForge Team
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

require_once $gfcommon.'include/Error.class.php';
require_once $gfcommon.'pm/ProjectTask.class.php';

class ProjectTaskHTML extends ProjectTask {

	function ProjectTaskHTML(&$ProjectGroup, $project_task_id=false, $arr=false) {
		return $this->ProjectTask($ProjectGroup,$project_task_id,$arr);
	}

	function multipleDependBox ($name='dependent_on[]') {
		$result=$this->getOtherTasks();
		//get the data so we can mark items as SELECTED
		$arr2 = array_keys($this->getDependentOn());
		return html_build_multiple_select_box ($result,$name,$arr2);
	}

	function multipleAssignedBox ($name='assigned_to[]') {
		$engine = RBACEngine::getInstance () ;
		$techs = $engine->getUsersByAllowedAction ('pm', $this->ProjectGroup->getID(), 'tech') ;

		$tech_id_arr = array () ;
		$tech_name_arr = array () ;
		
		foreach ($techs as $tech) {
			$tech_id_arr[] = $tech->getID() ;
			$tech_name_arr[] = $tech->getRealName() ;
		}
		
		//get the data so we can mark items as SELECTED
		$arr2 = $this->getAssignedTo();
		return html_build_multiple_select_box_from_arrays ($tech_id_arr,$tech_name_arr,$name,$arr2);
	}


	function showDependentTasks () {

		$result=db_query_params ('SELECT project_task.project_task_id,project_task.summary 
			FROM project_task,project_dependencies 
			WHERE project_task.project_task_id=project_dependencies.project_task_id 
			AND project_dependencies.is_dependent_on_task_id=$1',
			array($this->getID() ));
		$rows=db_numrows($result);

		if ($rows > 0) {
			echo '<h3>'._('Tasks That Depend on This Task').'</h3>';

			$title_arr=array();
			$title_arr[]=_('Task Id');
			$title_arr[]=_('Task Summary');

			echo $GLOBALS['HTML']->listTableTop ($title_arr);

			for ($i=0; $i < $rows; $i++) {
				echo '
				<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'>
					<td>'
					.util_make_link ('/pm/task.php?func=detailtask&project_task_id='. db_result($result, $i, 'project_task_id'). '&group_id='. $this->ProjectGroup->Group->getID() . '&group_project_id='. $this->ProjectGroup->getID(), db_result($result, $i, 'project_task_id')).'</td>
					<td>'.db_result($result, $i, 'summary').'</td></tr>';
			}

			echo $GLOBALS['HTML']->listTableBottom();

		} else {
			echo '<h3>'._('No Tasks are Dependent on This Task').'</h3>';
			echo db_error();
		}
	}

	function showRelatedArtifacts() {
		$res=$this->getRelatedArtifacts();

		$rows=db_numrows($res);
		if ($rows > 0) {
			if (forge_check_perm ('pm_admin', $this->ProjectGroup->Group->getID())) {
				$is_admin=false;
			} else {
				$is_admin=true;
			}

			echo '<h3>'._('Related Tracker Items').'</h3>';

			$title_arr=array();
			$title_arr[]=_('Artifact Summary');
			$title_arr[]=_('Tracker');
			$title_arr[]=_('Status');
			$title_arr[]=_('Open Date');
			(($is_admin) ? $title_arr[]=_('Remove Relation') : '');
		
			echo $GLOBALS['HTML']->listTableTop ($title_arr);

			for ($i=0; $i < $rows; $i++) {
				echo '
				<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'>
					<td>'.util_make_link ('/tracker/?func=detail&aid='.db_result($res,$i,'artifact_id').'&group_id='.db_result($res,$i,'group_id').'&atid='.db_result($res,$i,'group_artifact_id'), db_result($res,$i,'summary')).'</td>
					<td>'. db_result($res,$i,'name') .'</td>
+					<td>'. db_result($res,$i,'status_name') . '</td>
					<td>'. date(_('Y-m-d H:i'),db_result($res,$i,'open_date')) .'</td>'.
					(($is_admin) ? '<td><input type="checkbox" name="rem_artifact_id[]" value="'.db_result($res,$i,'artifact_id').'" /></td>' : '').
					'</tr>';
			}

			echo $GLOBALS['HTML']->listTableBottom();
		} else {
			echo '
			<h3>'._('No Related Tracker Items Have Been Added').'</h3>';
		}
	}

	function showMessages($asc=true,$whereto='/') {
		/*
			Show the details rows from task_history
		*/
		$result=$this->getMessages($asc);
		$rows=db_numrows($result);

		if ($rows > 0) {
			echo '<h3>'._('Followups: ');

			if ($asc) {
				echo '<a href="' .
					util_make_url($whereto . '&amp;commentsort=anti') .
					'">' . _('Sort comments antichronologically') . '</a>';
			} else {
				echo '<a href="' .
					util_make_url($whereto . '&amp;commentsort=chrono') .
					'">' . _('Sort comments chronologically') . '</a>';
			}
			echo "</h3>\n";

			$title_arr=array();
			$title_arr[]=_('Comment');
			$title_arr[]=_('Date');
			$title_arr[]=_('By');
		
			echo $GLOBALS['HTML']->listTableTop ($title_arr);

			for ($i=0; $i < $rows; $i++) {
				echo '
				<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'>
				<td>';
				$sanitizer = new TextSanitizer();
				$body = $sanitizer->SanitizeHtml(db_result($result, $i, 'body'));
				if (strpos($body,'<') === false) {
					echo nl2br(db_result($result, $i, 'body'));
				} else {
					echo $body;
				}

				echo '</td>
					<td valign="top">'.date(_('Y-m-d H:i'),db_result($result, $i, 'postdate')).'</td>
					<td valign="top">'.db_result($result, $i, 'user_name').'</td></tr>';
			}

			echo $GLOBALS['HTML']->listTableBottom();

		} else {
			echo '
			<h3>'._('No Comments Have Been Added').'</h3>';
		}
	
	}

	function showHistory() {
		/*
			show the project_history rows that are 
			relevant to this project_task_id, excluding details
		*/
		$result=$this->getHistory();
		$rows=db_numrows($result);

		if ($rows > 0) {

			echo '<h3>'._('Task Change History').'</h3>';

			$title_arr=array();
			$title_arr[]=_('Field');
			$title_arr[]=_('Old Value');
			$title_arr[]=_('Date');
			$title_arr[]=_('By');

			echo $GLOBALS['HTML']->listTableTop ($title_arr);

			for ($i=0; $i < $rows; $i++) {
				$field=db_result($result, $i, 'field_name');

				echo '
					<tr class="mod_task_field" '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'><td>'.$field.'</td><td>';

				if ($field == 'status_id') {
//tdP - convert to actual status name
					echo db_result($result, $i, 'old_value');

				} else if ($field == 'category_id') {
//tdP convert to actual category_name
					echo db_result($result, $i, 'old_value');

				} else if ($field == 'start_date') {

					echo date('Y-m-d',db_result($result, $i, 'old_value'));

				} else if ($field == 'end_date') {

					echo date('Y-m-d',db_result($result, $i, 'old_value'));

				} else {

					echo db_result($result, $i, 'old_value');

				}
				echo '</td>
					<td>'. date(_('Y-m-d H:i'),db_result($result, $i, 'mod_date')) .'</td>
					<td>'.db_result($result, $i, 'user_name').'</td></tr>';
			}

			echo $GLOBALS['HTML']->listTableBottom();

		} else {
			echo '
			<h3>'._('No Changes Have Been Made').'</h3>';
		}
	}

}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
