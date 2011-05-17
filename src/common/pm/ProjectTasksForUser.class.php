<?php
/**
 * FusionForge project manager
 *
 * Copyright 1999-2000, Tim Perdue/Sourceforge
 * Copyright 2002, Tim Perdue/GForge, LLC
 * Copyright 2009, Roland Mas
 *
 * This file is part of FusionForge. FusionForge is free software;
 * you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or (at your option)
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

require_once $gfcommon.'pm/ProjectGroup.class.php';
require_once $gfcommon.'pm/ProjectTask.class.php';
require_once $gfcommon.'include/Group.class.php';
require_once $gfcommon.'include/Error.class.php';

/**
*	A class that manages the project tasks for a specific user
*/
class ProjectTasksForUser extends Error {

	/**
	* The User to whom the tasks belong
	*/
	var $User;

	/**
	* Creates a new ProjectTasksForUser object
	*
	* @param	user	the User object
	*/
	function ProjectTasksForUser(&$user) {
		$this->User =& $user;
		return true;
	}

	/**
	* Gets a list of tasks for this user
	*
	* @param the SQL query to use to fetch the tasks
	*	@return	an array of ProjectTask objects
	*/
	function &getTasksFromSQLwithParams ($sql, $params) {
		$tasks = array();
		$result = db_query_params ($sql, $params);
		$rows=db_numrows($result);
		for ($i=0; $i < $rows; $i++) {
			$project_task_id = db_result($result,$i,'project_task_id');
			$arr = db_fetch_array($result);
			$task =& projecttask_get_object($project_task_id,$arr);
			$tasks[] =& $task;
		}
		return $tasks;
	}

	/**
	*	Gets a list of tasks by group project name
	*
	* @return an array of ProjectTask objects
	*/
	function &getTasksByGroupProjectName () {
		return $this->getTasksFromSQLwithParams ('SELECT ptv.*,g.group_name,pgl.project_name 
			FROM project_task_vw ptv,
				project_assigned_to pat,
				groups g,
				project_group_list pgl
			WHERE ptv.project_task_id=pat.project_task_id
				AND pgl.group_id=g.group_id
				AND pgl.group_project_id=ptv.group_project_id
				AND ptv.status_id=1
				AND pat.assigned_to_id=$1
			ORDER BY group_name,project_name',
							 array ($this->User->getID())) ;
	}
	
	function &getOpenTasksForDate($date) {
		return $this->getTasksFromSQLwithParams ('SELECT ptv.*,g.group_name,pgl.project_name 
			FROM project_task_vw ptv,
				project_assigned_to pat,
				groups g,
				project_group_list pgl
			WHERE ptv.project_task_id=pat.project_task_id
				AND pgl.group_id=g.group_id
				AND pgl.group_project_id=ptv.group_project_id
				AND ptv.start_date < $1
				AND ptv.status_id=1
				AND pat.assigned_to_id=$2
			ORDER BY group_name,project_name',
							 array ($date,
								$this->User->getID())) ;
	}

	function &getTasksForToday() {
		$now = getdate();
		$today = mktime (18, 00, 00, $now['mon'], $now['mday'], $now['year']);
		return $this->getOpenTasksForDate($today);
	}

	function &getTasksForThisWeek() {
		$now = getdate();
		$thisweek = mktime (18, 00, 00, $now['mon'], $now['mday'], $now['year'])+7*24*3600;
		return $this->getOpenTasksForDate($thisweek);
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
