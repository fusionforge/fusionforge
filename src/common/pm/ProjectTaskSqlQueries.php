<?php
/**
 * @author: Patrick Apel, tarent GmbH
 */


/**
 * Retrieve group_project_id and group_id for a specific project_task_id,
 * for URI construction and similar things.
 *
 * in:	int project_task_id
 * out:	false, or an associative array with
 *	- int project_task_id (copy)
 *	- int group_project_id
 *	- int group_id
 */
function getGroupProjectIdGroupId($project_task_id) {
	$res = db_query_params('SELECT project_task.group_project_id, project_group_list.group_id FROM project_task ' .
	    'INNER JOIN project_group_list ON project_task.group_project_id = project_group_list.group_project_id ' .
	    'WHERE project_task.project_task_id = $1',
	    array($project_task_id));

	if (!$res || db_numrows($res) != 1) {
		return false;
	}

	while ($arr = db_fetch_array($res)) {
		$arrResult = array(
		    'project_task_id' => (int)$project_task_id,
		    'group_project_id' => (int)$arr[0],
		    'group_id' => (int)$arr[1]
		);
	}

	return $arrResult;
}


/**
 * Check if the task behind project_task_id is considered public.
 *
 * in:	int project_task_id
 * out:	true, if it is; false otherwise
 */
function isProjectTaskInfoPublic($project_task_id) {
	$res = db_query_params('SELECT group_project_id FROM project_task WHERE project_task_id=$1',
			       array ($project_task_id)) ;

	if (!$res || db_numrows($res) < 1) {
		return false;
	}

	return RoleAnonymous::getInstance()->hasPermission('pm',
							   db_result ($res, 0, 'group_project_id'),
							   'read') ;
}


/**
 * Check whether the user has access to the project task by
 * means of common group membership.
 *
 * in:	int project_task_id
 *	str user_name (Unix user name)
 * out:	true, if he has; false otherwise
 */
function isUserAndTaskinSameGroup($project_task_id, $user_name) {
	$res = db_query_params('SELECT group_project_id FROM project_task WHERE project_task_id=$1',
			       array ($project_task_id)) ;

	if (!$res || db_numrows($res) < 1) {
		return false;
	}
	$arr = db_fetch_array($res) ;

	return forge_check_perm_for_user(user_get_object_by_name ($user_name), 'pm', $arr['group_project_id'], 'read') ;
}

/*-
 * Query for controlling the result. It gives back all user_names and
 * project_task_ids that matches the groups above:
 *
 * SELECT users.user_name, project_task.project_task_id FROM users
 * INNER JOIN user_group ON users.user_id = user_group.user_id
 * INNER JOIN project_group_list ON user_group.group_id = project_group_list.group_id
 * INNER JOIN project_task ON project_group_list.group_project_id = project_task.group_project_id;
 *
 * Query for controlling the result. It gives back all user_names that
 * does not match a group above:
 *
 * SELECT users.user_name, project_task.project_task_id FROM users
 * LEFT JOIN user_group ON users.user_id = user_group.user_id
 * LEFT JOIN project_group_list ON user_group.group_id = project_group_list.group_id
 * LEFT JOIN project_task ON project_group_list.group_project_id = project_task.group_project_id
 * WHERE project_task_id isNull;
 */


/**
 * Retrieve extended information about a project task.
 *
 * in:	int project_task_id
 * out:	false (if an error occured) or an associative array with
 *	- int project_task_id (copy)
 *	- int group_project_id (for URI construction)
 *	- int group_id (for URI construction)
 *	- str group_name
 *	- str summary (of the task)
 *	- int priority
 *	- int created_by (user ID)
 *	- str created_by_name (Unix user name)
 *	- int status_id
 *	- str status_name
 *	- int category_id
 *	- str category_name (of the per-group category the task is in)
 *	- str project_name (of the per-group subproject the task is in)
 */
function getAllFromProjectTask($project_task_id) {
	$res = db_query_params('SELECT ' .
	    'project_task.project_task_id, project_task.group_project_id, project_task.summary, project_task.priority, ' .
	    'project_task.created_by, project_task.status_id, project_task.category_id, ' .
	    'users.user_name, ' .
	    'project_category.category_name, ' .
	    'project_group_list.project_name, ' .
	    'groups.group_name, ' .
	    'groups.group_id, ' .
	    'project_status.status_name ' .
	    'FROM project_status ' .
	    'INNER JOIN project_task ON ' .
	    'project_task.status_id = project_status.status_id ' .
	    'INNER JOIN users ON ' .
	    'users.user_id = project_task.created_by ' .
	    'INNER JOIN project_category ON ' .
	    'project_category.category_id = project_task.category_id ' .
	    'INNER JOIN project_group_list ON ' .
	    'project_group_list.group_project_id = project_task.group_project_id ' .
	    'INNER JOIN groups ON ' .
	    'groups.group_id = project_group_list.group_id ' .
	    'WHERE project_task.project_task_id = $1',
	    array($project_task_id));

	if (!$res || db_numrows($res) != 1) {
		return false;
	}

	while ($arr = db_fetch_array($res)) {
		$arrResult = array(
		    'project_task_id' => (int)$arr[0],
		    'group_project_id' => (int)$arr[1],
		    'group_id' => (int)$arr[11],
		    'summary' => $arr[2],
		    'priority' => (int)$arr[3],
		    'created_by' => (int)$arr[4],
		    'status_id' => (int)$arr[5],
		    'category_id' => (int)$arr[6],
		    'created_by_name' => $arr[7],
		    'category_name' => $arr[8],
		    'project_name' => $arr[9],
		    'group_name' => $arr[10],
		    'status_name' => $arr[12]
		);
	}

	return $arrResult;
}

?>
