<?php
/**
 *  MS Project Integration Facility
 *
 * Copyright 2004 GForge, LLC
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

require_once $gfcommon.'include/pre.php';
require_once $gfcommon.'pm/ProjectGroup.class.php';
require_once $gfcommon.'pm/import_utils.php';
require_once $gfcommon.'pm/ProjectTask.class.php';
require_once $gfcommon.'pm/ProjectTaskFactory.class.php';
require_once $gfcommon.'include/User.class.php';
require_once $gfcommon.'include/session.php';

/**
return data:

	$array[success]=true;
	$array[session_hash]=jkjkjkjkjkjkjkj;
	$array[subprojects]=array(
						55=>'Subproject 1',
						77=>'Subproject2'
						);

**OR**

	$array[success]=false;
	$array[errormessage]='Bad Password';
 */
function &MSPLogin($username,$password) {
	global $feedback,$session_ser;

	$success=session_login_valid(strtolower($username),$password);
	if ($success) {
		$array['success']=true;
		$array['session_hash']=$session_ser;


		$result = db_query_params ('SELECT group_project_id FROM project_group_list',
					   array ()) ;

		$gids = array () ;
		while ($arr = db_fetch_array($result)) {
			if (forge_check_perm ('pm', $arr['group_project_id'], 'read')) {
				$gids[] = $arr['group_project_id'] ;
			}
		}

		$res = db_query_params ('SELECT pgl.group_project_id, g.group_name || $1 || pgl.project_name AS name
			FROM groups g, project_group_list pgl
			WHERE g.group_id=pgl.group_id
                        AND pgl_group_project_id = ANY ($2)',
					array(': ',
					      db_int_array_to_any_clause ($tids))) ;
		$rows=db_numrows($res);
		if (!$res || $rows<1) {
			$array['success']=false;
			$array['errormessage']='No Subprojects Found';
		} else {
			for ($i=0; $i<$rows; $i++) {
				$array['subprojects'][db_result($res,$i,'group_project_id')]=db_result($res,$i,'name');
			}
		}
	} else {
		$array['success']=false;
		$array['errormessage']=$feedback;
	}
	printr($array,'MSPLogin::return-array');
	return $array;
}

/**

return data:

	$array[success]=true;
	$array[tasks]=array of ProjectTask objects

**OR**

	$array[success]=false;
	$array[errormessage]='Invalid Subproject';

*/
function &MSPDownload($session_hash,$group_project_id) {
	if (!session_continue($session_hash)) {
		$array['success']=false;
		$array['errormessage']='Could Not Continue Session';
	}
	$pg =& projectgroup_get_object($group_project_id);
	if (!$pg || !is_object($pg)) {
		$array['success']=false;
		$array['errormessage']='Could Not Get ProjectGroup';
	} elseif ($pg->isError()) {
		$array['success']=false;
		$array['errormessage']='Could Not Get ProjectGroup: '.$pg->getErrorMessage();
	} else {
		$ptf =& new ProjectTaskFactory($pg);
		if (!$ptf || !is_object($ptf)) {
			$array['success']=false;
			$array['errormessage']='Could Not Get ProjectTaskFactory';
		} elseif ($ptf->isError()) {
			$array['success']=false;
			$array['errormessage']='Could Not Get ProjectTaskFactory: '.$ptf->getErrorMessage();
		} else {
			$ptf->order='external_id';
			$array['success']=true;
			$array['tasks'] =& $ptf->getTasks();
			if (!$array['tasks']) {
				$array['success']=false;
				$array['errormessage']='No Matching ProjectTasks Found: '.$ptf->getErrorMessage();
			}
		}
	}
	printr($array,'MSPDownload::return-array');
	return $array;
}

//same as download
//function MSPGetLatest($session_hash,$group_project_id) {
//}

/**

LINK TYPES -
				SS . Start to Start
				SF . Start to Finish
				FS . Finish to Start
				FF . Finish to Finish


	params:

	$session_hash
	$group_project_id (subproject_id)
	$tasks =
		array(
			id=>1,
			msproj_id=>p1,
			parent_id=>4,
			parent_msproj_id=>p5
			name=>'Task Name',
			duration=>5,
			work=>40,
			start_date=>'10/1/04',
			end_date=>'10/8/04',
			percent_complete=>'50',
			priority=>'medium',
			resources=>array(
				array(user_name=>'unix_name'),
				...
			),
			dependenton=>array(
				array( 'task_id'=>'55', 'msproj_id'=44, 'task_name'=>'Task Name', 'link_type'='SS' ),
				...
			)
			notes=>'notes'
		),

Return:

	$array[success]=true;

**OR**

	$array[success]=false;
	$array[errormessage]='Invalid Subproject';
	$array[resourcename]=array(
			'Michael',
			'Jon',
			...
		)
	$array[usernames]=array(
			array(user_id=>55,user_name='Jon Doe'),
			array(user_id=>87,user_name='Foo'),
			...
		)

*/
function &MSPCheckin($session_hash,$group_project_id,$tasks) {
	global $primap;
	printr($tasks,'MSPCheckin::in-tasks');
	if (!session_continue($session_hash)) {
		$array['success']=false;
		$array['errormessage']='Could Not Continue Session';
	}
	return pm_import_tasks($group_project_id,$tasks);
}

/**
* MSPGetProjects
* Return the projects by user.
*
* @author	Luis Hurtado	luis@gforgegroup.com
* @param	session_hash	User session
* @return	Groups		User groups
* @date		2005-01-19
*
*/
function &MSPGetProjects($session_hash) {
	if (!session_continue($session_hash)) {
		$array['success']=false;
		$array['errormessage']='Could Not Continue Session';
	}
	$projects = array () ;
	foreach (user_get_session()->getGroups() as $p) {
		if (forge_check_perm ('pm_admin', $p->getID())) {
			$projects[] = $p ;
		}
	}
	return $projects;
}

/**
* MSPCreateProject
* Create SubProjects
*
* @author	Luis Hurtado	luis@gforgegroup.com
* @param	groupid		ID Group
* @param	session_hash	User Session
* @param	name		Project name
* @param	ispublic	1 Public  0 Private
* @param	description	Project Description
* @return	ProjectGroup	Object ProjectGroup
* @date		2005-01-19
*
*/
function &MSPCreateProject($groupid,$session_hash,$name,$ispublic,$description) {
	if (!session_continue($session_hash)) {
		$array['success']=false;
		$array['errormessage']='Could Not Continue Session';
	}
	$group = group_get_object($groupid);
	if (!$group || !is_object($group)) {
		$res['code']="error";
		$res['description']="No Such Project";
		return $res;
	} else {
		if (!forge_check_perm ('pm_admin', $group_id)) {
			$res['code']="error";
			$res['description']="User must be Admin";
			return $res;
		} else {
			$pg = new ProjectGroup($group);
			if (!$pg || !is_object($pg)) {
				$res['code']="error";
				$res['description']="Could Not Get ProjectGroup";
				return $res;
			} else {
				if (!$pg->create($name,$description,$ispublic)) {
					$res['code']="error";
					$res['description']='Error Creating Subproject '.$pg->getErrorMessage();
					return $res;
				} else {
					return $pg;
				}
			}
		}
	}
}
?>
