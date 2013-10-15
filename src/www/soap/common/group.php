<?php
/**
 * SOAP Group Include - this file contains wrapper functions for the SOAP interface
 *
 * Copyright 2004 (c) GForge, LLC
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

require_once $gfcommon.'include/Error.class.php';
require_once $gfcommon.'include/Group.class.php';
require_once $gfcommon.'include/FusionForge.class.php';

require_once $gfcommon.'include/GroupJoinRequest.class.php';
require_once $gfcommon.'include/User.class.php';
require_once $gfcommon.'include/Role.class.php';

// Add The definition of a group object
$server->wsdl->addComplexType(
	'Group',
	'complexType',
	'struct',
	'all',
	'',
	array(
	'group_id' => array('name'=>'group_id', 'type' => 'xsd:int'),
	'group_name' => array('name'=>'group_name', 'type' => 'xsd:string'),
	'homepage' => array('name'=>'homepage', 'type' => 'xsd:string'),
	'is_public' => array('name'=>'is_public', 'type' => 'xsd:int'),
	'status' => array('name'=>'status', 'type' => 'xsd:string'),
	'unix_group_name' => array('name'=>'unix_group_name', 'type' => 'xsd:string'),
	'short_description' => array('name'=>'short_description', 'type' => 'xsd:string'),
	'scm_box' => array('name'=>'scm_box', 'type' => 'xsd:string'),
	'register_time' => array('name'=>'register_time', 'type' => 'xsd:int'),
	'use_mail' => array('name'=>'use_mail', 'type' => 'xsd:string'),
	'use_survey' => array('name'=>'use_survey', 'type' => 'xsd:string'),
	'use_forum' => array('name'=>'use_forum', 'type' => 'xsd:string'),
	'use_pm' => array('name'=>'use_pm', 'type' => 'xsd:string'),
	'use_pm_depend_box' => array('name'=>'use_pm_depend_box', 'type' => 'xsd:string'),
	'use_scm' => array('name'=>'use_scm', 'type' => 'xsd:string'),
	'use_news' => array('name'=>'use_news', 'type' => 'xsd:string'),
	'use_docman' => array('name'=>'use_docman', 'type' => 'xsd:string'),
	'new_doc_address' => array('name'=>'new_doc_address', 'type' => 'xsd:string'),
	'send_all_docs' => array('name'=>'send_all_docs', 'type' => 'xsd:string'),
	'use_ftp' => array('name'=>'use_ftp', 'type' => 'xsd:string'),
	'use_tracker' => array('name'=>'use_tracker', 'type' => 'xsd:string'),
	'use_frs' => array('name'=>'use_frs', 'type' => 'xsd:string'),
	'use_stats' => array('name'=>'use_stats', 'type' => 'xsd:string'),
	'tags' => array('name'=>'tags', 'type' => 'xsd:string')) );

// Array of groups
$server->wsdl->addComplexType(
    'ArrayOfGroup',
    'complexType',
    'array',
    '',
    'SOAP-ENC:Array',
    array(),
    array(array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'tns:Group[]')),
    'tns:Group');

// [Yosu] Add The definition of a role object
$server->wsdl->addComplexType(
	'Role',
	'complexType',
	'struct',
	'all',
	'',
	array(
	'role_id' => array('name'=>'role_id', 'type' => 'xsd:int'),
	'role_name' => array('name'=>'role_name', 'type' => 'xsd:string'),
	'project_read' => array('name'=>'project_read', 'type' => 'xsd:int'),
	'project_admin' => array('name'=>'project_admin', 'type' => 'xsd:int'),
	'frs' => array('name'=>'frs', 'type' => 'xsd:int'),
	'scm' => array('name'=>'scm', 'type' => 'xsd:int'),
	'docman' => array('name'=>'docman', 'type' => 'xsd:int'),
	'tracker_admin' => array('name'=>'tracker_admin', 'type' => 'xsd:int'),
	'new_tracker' => array('name'=>'new_tracker', 'type' => 'xsd:int'),
	'forum_admin' => array('name'=>'forum_admin', 'type' => 'xsd:int'),
	'new_forum' => array('name'=>'new_forum', 'type' => 'xsd:int'),
	'pm_admin' => array('name'=>'pm_admin', 'type' => 'xsd:int'),
	'new_pm' => array('name'=>'new_pm', 'type' => 'xsd:int')) );

//[Yosu] Array of roles
$server->wsdl->addComplexType(
    'ArrayOfRole',
    'complexType',
    'array',
    '',
    'SOAP-ENC:Array',
    array(),
    array(array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'tns:Role[]')),
    'tns:Role');

//getGroups (id array)
$server->register(
    'getGroups',
    array('session_ser'=>'xsd:string','group_ids'=>'tns:ArrayOfint'),
    array('return'=>'tns:ArrayOfGroup'),
    $uri,
    $uri.'#getGroups','rpc','encoded'
);

//getGroupsByName (unix_name array)
$server->register(
    'getGroupsByName',
    array('session_ser'=>'xsd:string','group_names'=>'tns:ArrayOfstring'),
    array('return'=>'tns:ArrayOfGroup'),
    $uri,
    $uri.'#getGroupsByName','rpc','encoded'
);

//getPublicProjectNames ()
$server->register(
    'getPublicProjectNames',
    array('session_ser'=>'xsd:string'),
    array('return'=>'tns:ArrayOfstring'),
    $uri,
    $uri.'#getPublicProjectNames','rpc','encoded'
);

// getGroupByStatus
$server->register(
    'getGroupByStatus',
    array('session_ser'=>'xsd:string',
	'status'=>'xsd:string'),
    array('return'=>'tns:ArrayOfGroup'),
    $uri,
    $uri.'#getGroupByStatus','rpc','encoded'
);

// approveGroup
$server->register(
    'approveGroup',
    array('session_ser'=>'xsd:string',
	'group_id'=>'xsd:int'),
    array('return'=>'xsd:boolean'),
    $uri,
    $uri.'#approveGroup','rpc','encoded'
);

//addGroup ($user, $group_name, $unix_name, $description, $purpose, $unix_box = 'shell1', $scm_box = 'cvs1', $is_public = 1, $send_mail = true, $built_from_template = 0)
$server->register(
    'addGroup',
    array('session_ser'=>'xsd:string',
	'group_name'=>'xsd:string',
	'unix_name'=>'xsd:string',
	'description'=>'xsd:string',
	'purpose'=>'xsd:string',
	'unix_box'=>'xsd:string',
	'scm_box'=>'xsd:string',
	'is_public'=>'xsd:int',
	'send_mail'=>'xsd:boolean',
	'built_from_template'=>'xsd:int'),
    array('return'=>'xsd:boolean'),
    $uri,
    $uri.'#addGroup','rpc','encoded'
);

//updateGroup ($group_id, $is_public, $is_template, $status, $group_type, $unix_box, $http_domain, $scm_box)
$server->register(
    'updateGroup',
    array('session_ser'=>'xsd:string',
	'group_id'=>'xsd:int',
	'is_public'=>'xsd:int',
	'is_template'=>'xsd:int',
	'status'=>'xsd:string',
	'group_type'=>'xsd:string',
	'unix_box'=>'xsd:string',
	'http_domain'=>'xsd:string',
	'scm_box'=>'xsd:string'),
    array('return'=>'xsd:boolean'),
    $uri,
    $uri.'#updateGroup','rpc','encoded'
);

//function updateGroup($session_ser, $group_id, $form_group_name, $form_homepage, $form_shortdesc, $use_mail, $use_survey, $use_forum, $use_pm,
//	$use_scm, $use_news, $use_docman, $new_doc_address, $send_all_docs, $use_ftp, $use_tracker, $use_frs, $use_stats, $tags)
$server->register(
    'updateGroup2',
    array('session_ser'=>'xsd:string',
	'group_id'=>'xsd:int',
        'form_group_name'=>'xsd:string',
        'form_homepage'=>'xsd:string',
        'form_shortdesc'=>'xsd:string',
        'use_mail'=>'xsd:string',
        'use_survey'=>'xsd:string',
        'use_forum'=>'xsd:string',
        'use_pm'=>'xsd:string',
        'use_scm'=>'xsd:string',
        'use_news'=>'xsd:string',
        'use_docman'=>'xsd:string',
        'new_doc_address'=>'xsd:string',
        'send_all_docs'=>'xsd:string',
        'use_ftp'=>'xsd:string',
        'use_tracker'=>'xsd:string',
        'use_frs'=>'xsd:string',
        'use_stats'=>'xsd:string',
        'tags'=>'xsd:string'),
    array('return'=>'xsd:boolean'),
    $uri,
    $uri.'#updateGroup2','rpc','encoded'
);

//deleteGroup ($group_id)
$server->register(
    'deleteGroup',
    array('session_ser'=>'xsd:string',
	'group_id'=>'xsd:int'),
    array('return'=>'xsd:boolean'),
    $uri,
    $uri.'#deleteGroup','rpc','encoded'
);

//[Yosu] addUserToGroup (session_ser, user_unix_name, group_id, role_id)
$server->register(
    'addUserToGroup',
    array('session_ser'=>'xsd:string',
	 'user_unix_name'=>'xsd:string',
         'group_id'=>'xsd:int',
         'role_id'=>'xsd:int'),
    array('addUserToGroupResonse'=>'xsd:boolean'),
    $uri,
    $uri.'#addUserToGroup','rpc','encoded'
);

//[Yosu] removeUserFromGroup (session_ser, user_id, role_id)
$server->register(
    'removeUserFromGroup',
    array('session_ser'=>'xsd:string',
         'user_id'=>'xsd:string',
         'role_id'=>'xsd:int'),
    array('removeUserFromGroupResonse'=>'xsd:boolean'),
    $uri,
    $uri.'#removeUserFromGroup','rpc','encoded'
);

//[Yosu] getGroupRoles (session_ser, group_id)
$server->register(
    'getGroupRoles',
    array('session_ser'=>'xsd:string',
	 'group_id'=>'xsd:int'),
    array('return'=>'tns:ArrayOfRole'),
    $uri,
    $uri.'#getGroupRoles','rpc','encoded'
);

//[Yosu] getUserRolesForGroup (session_ser, group_id, user_id)
$server->register(
    'getUserRolesForGroup',
    array('session_ser'=>'xsd:string',
	 'group_id'=>'xsd:int',
	 'user_id'=>'xsd:int'),
    array('return'=>'tns:ArrayOfRole'),
    $uri,
    $uri.'#getUserRolesForGroup','rpc','encoded'
);

//[Yosu] addGroupRole ($session_ser, $group_id, $role_name, $project_read, $project_admin, $frs, $scm, $docman,
//		$tracker_admin, $new_tracker, $forum_admin, $new_forum, $pm_admin, $new_pm)
$server->register(
	'addGroupRole',
	array('session_ser'=>'xsd:string',
			'group_id'=>'xsd:int',
			'role_name'=>'xsd:string',
			'project_read'=>'xsd:int',
			'project_admin'=>'xsd:int',
			'frs'=>'xsd:int',
			'scm'=>'xsd:int',
			'docman'=>'xsd:int',
			'tracker_admin'=>'xsd:int',
			'new_tracker'=>'xsd:int',
			'forum_admin'=>'xsd:int',
			'new_forum'=>'xsd:int',
			'pm_admin'=>'xsd:int',
			'new_pm'=>'xsd:int'),
	array('return'=>'xsd:int'),
	$uri,
	$uri.'#addGroupRole','rpc','encoded'
);

//[Yosu] updateGroupRole ($session_ser, $group_id, $role_id, $role_name, $project_read, $project_admin, $frs, $scm, $docman,
//		$tracker_admin, $new_tracker, $forum_admin, $new_forum, $pm_admin, $new_pm)
$server->register(
	'updateGroupRole',
	array('session_ser'=>'xsd:string',
			'group_id'=>'xsd:int',
			'role_id'=>'xsd:int',
			'role_name'=>'xsd:string',
			'project_read'=>'xsd:int',
			'project_admin'=>'xsd:int',
			'frs'=>'xsd:int',
			'scm'=>'xsd:int',
			'docman'=>'xsd:int',
			'tracker_admin'=>'xsd:int',
			'new_tracker'=>'xsd:int',
			'forum_admin'=>'xsd:int',
			'new_forum'=>'xsd:int',
			'pm_admin'=>'xsd:int',
			'new_pm'=>'xsd:int'),
	array('return'=>'xsd:int'),
	$uri,
	$uri.'#updateGroupRole','rpc','encoded'
);

//[Yosu] deleteGroupRole ($session_ser, $group_id, $role_id)
$server->register(
	'deleteGroupRole',
	array('session_ser'=>'xsd:string',
			'group_id'=>'xsd:int',
			'role_id'=>'xsd:int'),
	array('return'=>'xsd:int'),
	$uri,
	$uri.'#deleteGroupRole','rpc','encoded'
);

function &getGroups($session_ser,$group_ids) {
	global $feedback;
	continue_session($session_ser);

	$inputArgs = $session_ser;
	for ($i=0; $i<count($group_ids); $i++) {
		$inputArgs = $inputArgs.':'.$group_ids[$i];
	}

	$grps = group_get_objects($group_ids);
	if (!$grps) {
		return new soap_fault ('2001','group','Could Not Get Projects by Id'.$inputArgs,$feedback);
	}

	return groups_to_soap($grps);
}

function &getGroupsByName($session_ser,$group_names) {
	session_continue($session_ser);
	$grps = group_get_objects_by_name($group_names);
	if (!$grps) {
		return new soap_fault ('2002','group','Could Not Get Projects by Name','Could Not Get Projects by Name');
	}

	return groups_to_soap($grps);
}

// use as a way to get group names for use in getGroupsByName
function &getPublicProjectNames($session_ser) {
	continue_session($session_ser);
	$forge = new FusionForge();
	$result = $forge->getPublicProjectNames();
	if ($forge->isError()) {
		$errMsg = 'Could Not Get Public Projects Names: '.$forge->getErrorMessage();
		return new soap_fault ('2003','group',$errMsg,$errMsg);
	}
	return $result;
}

//group_get_status
function getGroupByStatus($session_ser, $status) {
	continue_session($session_ser);

	$res = db_query_params('SELECT group_id FROM groups WHERE status=$1', array($status));
	$grps = group_get_objects(util_result_column_to_array($res,0));

	if ($grps < 0) {
		return new soap_fault ('2004','group','Could Not Get Projects by Status','Could Not Get Projects by Status');
	}

	return groups_to_soap($grps);
}

// update a group
function approveGroup($session_ser, $group_id) {
	continue_session($session_ser);
	$group = group_get_object($group_id);
	$result = $group->setStatus(session_get_user(), 'A');

	if (!$result) {
		$errMsg = 'Could Not Approve The Project: '.$group->getErrorMessage();
		return new soap_fault ('2005','group',$errMsg,$errMsg);
	}

	return $result;
}

// add a group
function addGroup($session_ser, $group_name, $unix_name, $description, $purpose, $unix_box, $scm_box, $is_public, $send_mail, $built_from_template) {
	continue_session($session_ser);
	$group = new Group();
	$u = session_get_user();
	$result = $group->create($u, $group_name, $unix_name, $description, $purpose, $unix_box, $scm_box, $is_public, $send_mail, $built_from_template);

	if (!$result){
		$errMsg = 'Could Not Add A New Project: '.$group->getErrorMessage();
		return new soap_fault ('2006','group',$errMsg,$errMsg);
	}
	return $result;
}

function updateGroup($session_ser, $group_id, $is_public, $is_template, $status, $group_type, $unix_box, $http_domain, $scm_box) {
	continue_session($session_ser);
	$group = group_get_object($group_id);
	$error_msg = '';

	if (!$group->setStatus(session_get_user(), $status)) {
		$error_msg .= $group->getErrorMessage();
	}

	if (!$group->updateAdmin(session_get_user(), $is_public, $group_type, $unix_box, $http_domain)) {
		$error_msg .= $group->getErrorMessage();
	}

	if (!$group->setAsTemplate($is_template)) {
		$error_msg .= $group->getErrorMessage();
	}

	if($group->usesSCM() && !$group->setSCMBox($scm_box)) {
		$error_msg .= $group->getErrorMessage();
	}

	if ($error_msg){
		$errMsg = 'Could Not Update A Project: '.$error_msg;
		return new soap_fault ('2007','group',$errMsg,$errMsg);
	}
	return true;
}

// update a group
function updateGroup2($session_ser, $group_id, $form_group_name, $form_homepage, $form_shortdesc, $use_mail, $use_survey, $use_forum, $use_pm,
	$use_scm, $use_news, $use_docman, $new_doc_address, $send_all_docs, $use_ftp, $use_tracker, $use_frs, $use_stats, $tags) {
	continue_session($session_ser);
	$group = group_get_object($group_id);

	$res = $group->update(
		session_get_user(),
		$form_group_name,
		$form_homepage,
		$form_shortdesc,
		$use_mail,
		$use_survey,
		$use_forum,
		$use_pm,
		1,
		$use_scm,
		$use_news,
		$use_docman,
		$new_doc_address,
		$send_all_docs,
		100,
		$use_ftp,
		$use_tracker,
		$use_frs,
		$use_stats,
		$tags,
		0
	);

	if (!$res) {
		$errMsg = 'Could Not Update A Project: '.$group->getErrorMessage();
		return new soap_fault ('2007','group',$errMsg,$errMsg);
	}

	return true;
}

// delete a group
function deleteGroup($session_ser, $group_id) {
	continue_session($session_ser);
	$group = group_get_object($group_id);

	$result = $group->delete(true, true, true);
	if (!$result){
		$errMsg = 'Could Not Delete A Project: '.$group->getErrorMessage();
		return new soap_fault ('2008','group',$errMsg,$errMsg);
	}
	return $result;
}

//[Yosu] addUserToGroup (session_ser, user_unix_name, group_id, role_id)
function addUserToGroup ($session_ser,$user_unix_name, $group_id, $role_id){
	continue_session($session_ser);

	$user_object = user_get_object_by_name($user_unix_name);
	if ($user_object == false) {
		return new soap_fault ('addUserToGroup','Could Not Get User','Could Not Get User');
	}
	$user_id = $user_object->getID();

	$group = group_get_object($group_id);

	if (!$group || !is_object($group)) {
    		$errMsg = 'Could not get group: '.$group->getErrorMessage();
		return new soap_fault ('2004','group',$errMsg,$errMsg);
	} elseif ($group->isError()) {
		$errMsg = 'Could not get group: '.$group->getErrorMessage();
		return new soap_fault ('2004','group',$errMsg,$errMsg);
	}

	if (!$group->addUser($user_unix_name,$role_id)) {
		return new soap_fault ('addUserToGroup',$group->getErrorMessage(),$group->getErrorMessage());
	} else {
		//if the user have requested to join this group
		//we should remove him from the request list
		//since it has already been added
		$gjr=new GroupJoinRequest($group, $user_id);
		if ($gjr || is_object($gjr) || !$gjr->isError()) {
			$gjr->delete(true);
		}
		return true;
	}
}

//[Yosu] removeUserFromGroup (user_id, role_id)
function removeUserFromGroup ($session_ser,$user_id, $role_id){
	continue_session($session_ser);

	$role = RBACEngine::getInstance()->getRoleById($role_id) ;

	if (!$role->removeUser (user_get_object ($user_id))) {
		return new soap_fault ('removeUserFromGroup',$role->getErrorMessage(),$role->getErrorMessage());
	} else {
		return true;
	}
}

//[Yosu] getGroupRoles (session_ser, group_id)
function &getGroupRoles($session_ser,$group_id) {
	continue_session($session_ser);

	$group = group_get_object($group_id);

	if (!$group || !is_object($group)) {
    		$errMsg = 'Could not get group: '.$group->getErrorMessage();
		return new soap_fault ('2005','group',$errMsg,$errMsg);
	} elseif ($group->isError()) {
		$errMsg = 'Could not get group: '.$group->getErrorMessage();
		return new soap_fault ('2005','group',$errMsg,$errMsg);
	}
	$roles = $group->getRoles();
	if (!$roles) {
		$errMsg = 'Could not get any roles';
		return new soap_fault ('2005','group',$errMsg,$errMsg);
	}

	return roles_to_soap($roles,$group_id);
}

//[Yosu] getUserRolesForGroup (session_ser, $group_id, $user_id)
function getUserRolesForGroup($session_ser, $group_id, $user_id) {
	continue_session($session_ser);

	$roles = array () ;
	$user = user_get_object($user_id);
	if ($user == false) {
		$errMsg = 'Could not get user: '.$user->getErrorMessage();
		return new soap_fault ('2006','user',$errMsg,$errMsg);
	}

	$group = group_get_object($group_id);
	if (!$group || !is_object($group)) {
    		$errMsg = 'Could not get group: '.$group->getErrorMessage();
		return new soap_fault ('2006','group',$errMsg,$errMsg);
	} elseif ($group->isError()) {
		$errMsg = 'Could not get group: '.$group->getErrorMessage();
		return new soap_fault ('2006','group',$errMsg,$errMsg);
	}

	foreach (RBACEngine::getInstance()->getAvailableRolesForUser ($user) as $role) {
		if ($role->getHomeProject() && $role->getHomeProject()->getID() == $group->getID()) {
			$roles[] = $role ;
		}
	}
	sortRoleList ($roles) ;

	return roles_to_soap($roles,$group_id);
}

//[Yosu]add group role object
function addGroupRole ($session_ser, $group_id, $role_name, $project_read, $project_admin, $frs, $scm, $docman,
		$tracker_admin, $new_tracker, $forum_admin, $new_forum, $pm_admin, $new_pm){

	continue_session($session_ser);

	$group = group_get_object($group_id);
	$role = new Role ($group) ;

	if (!$role || !is_object($role)) {
		return new soap_fault ('addGroupRole','Could Not Get GroupRole','Could Not Get GroupRole');
	} elseif ($role->isError()) {
		return new soap_fault ('addGroupRole',$role->getErrorMessage(),$role->getErrorMessage());
	}

	$data = array ();
	$data['project_read'][$group_id] = $project_read;
	$data['project_admin'][$group_id] = $project_admin;
	$data['frs'][$group_id] = $frs;
	$data['scm'][$group_id] = $scm;
	$data['docman'][$group_id] = $docman;
	$data['tracker_admin'][$group_id] = $tracker_admin;
	$data['new_tracker'][$group_id] = $new_tracker;
	$data['forum_admin'][$group_id] = $forum_admin;
	$data['new_forum'][$group_id] = $new_forum;
	$data['pm_admin'][$group_id] = $pm_admin;
	$data['new_pm'][$group_id] = $new_pm;

	$role_id = $role->create($role_name, $data);

	if (!$role_id) {
		return new soap_fault ('addGroupRole',$role->getErrorMessage(),$role->getErrorMessage());
	}

	return $role_id;
}

//update role object
function updateGroupRole ($session_ser, $group_id, $role_id, $role_name, $project_read, $project_admin, $frs, $scm, $docman,
		$tracker_admin, $new_tracker, $forum_admin, $new_forum, $pm_admin, $new_pm){

	continue_session($session_ser);

	$role = RBACEngine::getInstance()->getRoleById($role_id) ;

	if (!$role || !is_object($role)) {
		return new soap_fault ('updateGroupRole','Could Not Get Role','Could Not Get Role');
	} elseif ($role->isError()) {
		return new soap_fault ('updateGroupRole',$role->getErrorMessage(),$role->getErrorMessage());
	}

	$data = array ();
	$data['project_read'][$group_id] = $project_read;
	$data['project_admin'][$group_id] = $project_admin;
	$data['frs'][$group_id] = $frs;
	$data['scm'][$group_id] = $scm;
	$data['docman'][$group_id] = $docman;
	$data['tracker_admin'][$group_id] = $tracker_admin;
	$data['new_tracker'][$group_id] = $new_tracker;
	$data['forum_admin'][$group_id] = $forum_admin;
	$data['new_forum'][$group_id] = $new_forum;
	$data['pm_admin'][$group_id] = $pm_admin;
	$data['new_pm'][$group_id] = $new_pm;

	$role_id = $role->update($role_name, $data, false);

	if (!$role_id) {
		return new soap_fault ('updateRole',$role->getErrorMessage(),$role->getErrorMessage());
	}

	return $role_id;

}

//delete role object
function deleteGroupRole ($session_ser, $group_id, $role_id){

	continue_session($session_ser);

	$role = RBACEngine::getInstance()->getRoleById($role_id) ;

	if (!$role || !is_object($role)) {
		return new soap_fault ('deleteGroupRole','Could Not Get Role','Could Not Get Role');
	} elseif ($role->isError()) {
		return new soap_fault ('deleteGroupRole',$role->getErrorMessage(),$role->getErrorMessage());
	}

	if ($role->getHomeProject() == NULL) {
		return new soap_fault ('deleteGroupRole', "You can't delete a global role from here.", "You can't delete a global role from here.");
	}

	if ($role->getHomeProject()->getID() != $group_id) {
		return new soap_fault ('deleteGroupRole', "You can't delete a role belonging to another project.", "You can't delete a role belonging to another project.");
	}

	if (!$role->delete()) {
		return new soap_fault ('deleteGroupRole',$role->getErrorMessage(),$role->getErrorMessage());
	}else{
		return true;
	}
}

/*
	Converts an array of Group objects to soap data
*/
function &groups_to_soap($grps) {
	$return = array();
	$ra = RoleAnonymous::getInstance() ;

	for ($i=0; $i<count($grps); $i++) {
		if ($grps[$i]->isError()) {
			//skip it if it had an error
		} else {
			$gid = $grps[$i]->data_array['group_id'];
			if ($ra->hasPermission('project_read', $gid)) {
				$is_public = 1;
			} else {
				$is_public = 0;
			}
			//build an array of just the fields we want
			$return[] = array('group_id'=>$grps[$i]->data_array['group_id'],
			'group_name'=>$grps[$i]->data_array['group_name'],
			'homepage'=>$grps[$i]->data_array['homepage'],
			'is_public'=>$is_public,
			'status'=>$grps[$i]->data_array['status'],
			'unix_group_name'=>$grps[$i]->data_array['unix_group_name'],
			'short_description'=>$grps[$i]->data_array['short_description'],
			'scm_box'=>$grps[$i]->data_array['scm_box'],
			'register_time'=>$grps[$i]->data_array['register_time'],
			'use_mail'=>$grps[$i]->data_array['use_mail'],
			'use_survey'=>$grps[$i]->data_array['use_survey'],
			'use_forum'=>$grps[$i]->data_array['use_forum'],
			'use_pm'=>$grps[$i]->data_array['use_pm'],
			'use_pm_depend_box'=>$grps[$i]->data_array['use_pm_depend_box'],
			'use_scm'=>$grps[$i]->data_array['use_scm'],
			'use_news'=>$grps[$i]->data_array['use_news'],
			'use_docman'=>$grps[$i]->data_array['use_docman'],
			'new_doc_address'=>$grps[$i]->data_array['new_doc_address'],
			'send_all_docs'=>$grps[$i]->data_array['send_all_docs'],
			'use_ftp'=>$grps[$i]->data_array['use_ftp'],
			'use_tracker'=>$grps[$i]->data_array['use_tracker'],
			'use_frs'=>$grps[$i]->data_array['use_frs'],
			'use_stats'=>$grps[$i]->data_array['use_stats'],
			'tags'=>$grps[$i]->getTags());
		}
	}
	// changed to not return soapval which is not necessary
	// since we have wsdl to describe return value
	return $return;
}

/*
	[Yosu]	Converts an array of Role objects to soap data
*/
function &roles_to_soap($roles, $group_id) {
	$return = array();

	for ($i=0; $i<count($roles); $i++) {
		if ($roles[$i]->isError()) {
			//skip it if it had an error
		} else {
			//build an array of just the fields we want

			$return[] = array('role_id'=>$roles[$i]->data_array['role_id'],
			'role_name'=>$roles[$i]->data_array['role_name'],
			'project_read'=>$roles[$i]->getVal('project_read',$group_id),
			'project_admin'=>$roles[$i]->getVal('project_admin',$group_id),
			'frs'=>$roles[$i]->getVal('frs',$group_id),
			'scm'=>$roles[$i]->getVal('scm',$group_id),
			'docman'=>$roles[$i]->getVal('docman',$group_id),
			'tracker_admin'=>$roles[$i]->getVal('tracker_admin',$group_id),
			'new_tracker'=>$roles[$i]->getVal('new_tracker',$group_id),
			'forum_admin'=>$roles[$i]->getVal('forum_admin',$group_id),
			'new_forum'=>$roles[$i]->getVal('new_forum',$group_id),
			'pm_admin'=>$roles[$i]->getVal('pm_admin',$group_id),
			'new_pm'=>$roles[$i]->getVal('new_pm',$group_id));
		}
	}
	// changed to not return soapval which is not necessary
	// since we have wsdl to describe return value
	return $return;
}
