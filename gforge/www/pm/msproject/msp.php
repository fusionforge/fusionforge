<?php
/**
 * GForge MS Project Integration Facility
 *
 * Copyright 2004 GForge, LLC
 * http://gforge.org/
 *
 * @version   $Id$
 */

require_once('squal_pre.php');
require_once('common/pm/ProjectGroup.class');
require_once('common/pm/import_utils.php');
require_once('common/pm/ProjectTask.class');
require_once('common/pm/ProjectTaskFactory.class');
require_once('common/include/User.class');
require_once('common/include/session.php');

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
	global $feedback,$session_ser,$sys_database_type;

	$success=session_login_valid(strtolower($username),$password);
	if ($success) {
		$array['success']=true;
		$array['session_hash']=$session_ser;
	    if ( $sys_database_type == "mysql" ) {
			$sql="SELECT pgl.group_project_id, CONCAT(g.group_name, ': ', pgl.project_name) AS name";
	    } else {
			$sql="SELECT pgl.group_project_id, g.group_name || ': ' || pgl.project_name AS name";
		}
		$sql.="
			FROM groups g, project_group_list pgl 
			NATURAL JOIN project_perm pp
			WHERE pp.user_id='".user_getid()."' 
			AND g.group_id=pgl.group_id
			AND pp.perm_level > 0";
		$res=db_query($sql);
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
	$group_res = db_query("SELECT groups.group_id FROM groups NATURAL JOIN user_group WHERE user_id='".user_getid()."' AND project_flags='2'");
	$group_ids=&util_result_column_to_array($group_res,'group_id');
	$groups=&group_get_objects($group_ids);
	return $groups;
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
		$res['description']="No Such Group";
		return $res;
	} else {
		$perm =& $group->getPermission(session_get_user());
		if (!$perm || !is_object($perm)) {
			$res['code']="error";
			$res['description']="Could Not Get Perm Object";
			return $res;
		} elseif ($perm->isError()) {
			$res['code']="error";
			$res['description']="Error in Perm Object: ".$perm->getErrorMessage();
			return $res;
		} elseif (!$perm->isPMAdmin()) {
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
