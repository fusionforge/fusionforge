<?php
/**
 * GForge Project Management Facility
 *
 * Copyright 2002 GForge, LLC
 * http://gforge.org/
 *
 * @version   $Id$
 */
/*

	Project/Task Manager
	By Tim Perdue, Sourceforge, 11/99
	Heavy rewrite by Tim Perdue April 2000

	Total rewrite in OO and GForge coding guidelines 12/2002 by Tim Perdue
*/

require_once('common/include/Error.class.php');
require_once('common/pm/ProjectTask.class.php');

class ProjectTaskHTML extends ProjectTask {

	function ProjectTaskHTML(&$ProjectGroup, $project_task_id=false, $arr=false) {
		return $this->ProjectTask($ProjectGroup,$project_task_id,$arr);
	}

	function multipleDependBox ($name='dependent_on[]') {
		$result=$this->getOtherTasks();
		//get the data so we can mark items as SELECTED
		$arr2 =& array_keys($this->getDependentOn());
		return html_build_multiple_select_box ($result,$name,$arr2);
	}

	function multipleAssignedBox ($name='assigned_to[]') {
		$result = $this->ProjectGroup->getTechnicians ();
		//get the data so we can mark items as SELECTED
		$arr2 =& $this->getAssignedTo();
		return html_build_multiple_select_box ($result,$name,$arr2);
	}


	function showDependentTasks () {
		global $Language;
		$sql="SELECT project_task.project_task_id,project_task.summary 
			FROM project_task,project_dependencies 
			WHERE project_task.project_task_id=project_dependencies.project_task_id 
			AND project_dependencies.is_dependent_on_task_id='". $this->getID() ."'";
		$result=db_query($sql);
		$rows=db_numrows($result);

		if ($rows > 0) {
			echo '
			<h3>'._('Tasks That Depend on This Task').'</h3>
			<p>';

			$title_arr=array();
			$title_arr[]=_('Task Id');
			$title_arr[]=_('Task Summary');

			echo $GLOBALS['HTML']->listTableTop ($title_arr);

			for ($i=0; $i < $rows; $i++) {
				echo '
				<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'>
					<td><a href="'.$GLOBALS['sys_urlprefix'].'/pm/task.php?func=detailtask&project_task_id='.
					db_result($result, $i, 'project_task_id').
					'&group_id='. $this->ProjectGroup->Group->getID() .
					'&group_project_id='. $this->ProjectGroup->getID() .'">'.
					db_result($result, $i, 'project_task_id').'</td>
					<td>'.db_result($result, $i, 'summary').'</td></tr>';
			}

			echo $GLOBALS['HTML']->listTableBottom();

		} else {
			echo '
				<h3>'._('No Tasks are Dependent on This Task').'</h3>';
			echo db_error();
		}
	}

	function showRelatedArtifacts() {
		global $Language;
		$res=$this->getRelatedArtifacts();

		$rows=db_numrows($res);
		if ($rows > 0) {
			$perm =& $this->ProjectGroup->Group->getPermission( session_get_user() );

			if (!$perm || !is_object($perm) || !$perm->isPMAdmin()) {
				$is_admin=false;
			} else {
				$is_admin=true;
			}

			echo '
			<h3>'._('Related Tracker Items').'</h3>
			<p>';

			$title_arr=array();
			$title_arr[]=_('Task Summary');
			$title_arr[]=_('Tracker');
			$title_arr[]=_('Open Date');
			(($is_admin) ? $title_arr[]=_('Remove Relation') : '');
		
			echo $GLOBALS['HTML']->listTableTop ($title_arr);

			for ($i=0; $i < $rows; $i++) {
				echo '
				<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'>
					<td><a href="'.$GLOBALS['sys_urlprefix'].'/tracker/?func=detail&aid='.db_result($res,$i,'artifact_id').'&group_id='.db_result($res,$i,'group_id').'&atid='.db_result($res,$i,'group_artifact_id').'">'.db_result($res,$i,'summary').'</a></td>
					<td>'. db_result($res,$i,'name') .'</td>
					<td>'. date(_('Y-m-d H:i'),db_result($res,$i,'open_date')) .'</td>'.
					(($is_admin) ? '<td><input type="checkbox" name="rem_artifact_id[]" value="'.db_result($res,$i,'artifact_id').'"></td>' : '').
					'</tr>';
			}

			echo $GLOBALS['HTML']->listTableBottom();
		} else {
			echo '
			<h3>'._('No Related Tracker Items Have Been Added').'</h3>';
		}
	}

	function showMessages() {
		/*
			Show the details rows from task_history
		*/
		global $Language;
	
		$result=$this->getMessages();
		$rows=db_numrows($result);

		if ($rows > 0) {
			echo '
			<h3>'._('Followups').'</h3>
			<p>';

			$title_arr=array();
			$title_arr[]=_('Comment');
			$title_arr[]=_('Date');
			$title_arr[]=_('By');
		
			echo $GLOBALS['HTML']->listTableTop ($title_arr);

			for ($i=0; $i < $rows; $i++) {
				echo '
				<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'>
					<td>'. nl2br(db_result($result, $i, 'body')).'</td>
					<td valign="TOP">'.date(_('Y-m-d H:i'),db_result($result, $i, 'postdate')).'</td>
					<td valign="TOP">'.db_result($result, $i, 'user_name').'</td></tr>';
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
		global $Language;

		$result=$this->getHistory();
		$rows=db_numrows($result);

		if ($rows > 0) {

			echo '
			<h3>'._('Task Change History').'</h3>
			<p>';

			$title_arr=array();
			$title_arr[]=_('Field');
			$title_arr[]=_('Old Value');
			$title_arr[]=_('Date');
			$title_arr[]=_('By');

			echo $GLOBALS['HTML']->listTableTop ($title_arr);

			for ($i=0; $i < $rows; $i++) {
				$field=db_result($result, $i, 'field_name');

				echo '
					<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'><td>'.$field.'</td><td>';

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

?>
