<?php
/**
 * SOAP Task Manager Include - this file contains wrapper functions for the SOAP interface
 *
 * Copyright 2004 (c) GForge, LLC
 * http://gforge.org
 *
 * @version   $Id$
 *
 * This file is part of GForge.
 *
 * GForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  US
 */

require_once('common/include/Error.class');
require_once('common/pm/ProjectGroup.class');
require_once('common/pm/ProjectGroupFactory.class');
require_once('common/pm/ProjectTaskFactory.class');
require_once('common/pm/ProjectTask.class');
require_once('common/pm/ProjectCategory.class');
require_once('common/pm/ProjectMessage.class');

//
//	ProjectGroup
//
$server->wsdl->addComplexType(
	'ProjectGroup',
	'complexType',
	'struct',
	'sequence',
	'',
	array(
	'group_project_id' => array('name'=>'group_project_id', 'type' => 'xsd:integer'),
	'group_id' => array('name'=>'group_id', 'type' => 'xsd:integer'),
	'name' => array('name'=>'name', 'type' => 'xsd:string'),
	'description' => array('name'=>'description', 'type' => 'xsd:string'),
	'is_public' => array('name'=>'is_public', 'type' => 'xsd:integer'),
	'send_all_posts_to' => array('name'=>'send_all_posts_to', 'type' => 'xsd:string')));

$server->wsdl->addComplexType(
	'ArrayOfProjectGroup',
	'complexType',
	'array',
	'',
	'SOAP-ENC:Array',
	array(),
	array(array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'tns:ProjectGroup[]')),
	'tns:ProjectGroup');

$server->register(
	'getProjectGroups',
	array('session_ser'=>'xsd:string','group_id'=>'xsd:integer'),
	array('getProjectGroupsResponse'=>'tns:ArrayOfProjectGroup'),
	$uri);

//
//	ProjectTasks
//
$server->wsdl->addComplexType(
	'ProjectTask',
	'complexType',
	'struct',
	'sequence',
	'',
	array(
	'project_task_id' => array('name'=>'project_task_id', 'type' => 'xsd:integer'),
	'group_project_id' => array('name'=>'group_project_id', 'type' => 'xsd:integer'),
	'summary' => array('name'=>'summary', 'type' => 'xsd:string'),
	'details' => array('name'=>'details', 'type' => 'xsd:string'),
	'percent_complete' => array('name'=>'percent_complete', 'type' => 'xsd:integer'),
	'priority' => array('name'=>'priority', 'type' => 'xsd:integer'),
	'hours' => array('name'=>'hours', 'type' => 'xsd:integer'),
	'start_date' => array('name'=>'start_date', 'type' => 'xsd:integer'),
	'end_date' => array('name'=>'end_date', 'type' => 'xsd:integer'),
	'status_id' => array('name'=>'status_id', 'type' => 'xsd:integer'),
	'category_id' => array('name'=>'category_id', 'type' => 'xsd:integer'),
	'is_dependent_on_task_id' => array('name'=>'is_dependent_on_task_id', 'type' => 'ArrayOfInteger'),
	'assigned_to_id' => array('name'=>'assigned_to_id', 'type' => 'ArrayOfInteger')
));

$server->wsdl->addComplexType(
	'ArrayOfProjectTask',
	'complexType',
	'array',
	'',
	'SOAP-ENC:Array',
	array(),
	array(array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'tns:ProjectTask[]')),
	'tns:ProjectTask');

//getProjectTask
$server->register(
	'getProjectTasks',
	array(
		'session_ser'=>'xsd:string',
		'group_id'=>'xsd:integer',
		'group_project_id'=>'xsd:integer',
		'assigned_to'=>'xsd:integer',
		'status'=>'xsd:integer',
		'category'=>'xsd:integer',
		'group'=>'xsd:integer'),
		array('getProjectTasksResponse'=>'tns:ArrayOfProjectTask'),
		$uri
	);

//addProjectTask
$server->register(
	'addProjectTask',
	array(
		'session_ser'=>'xsd:string',
		'group_id'=>'xsd:integer',
		'group_project_id'=>'xsd:integer',
		'summary'=>'xsd:string',
		'details'=>'xsd:string'),
		'percent_complete'=>'xsd:integer',
		'hours'=>'xsd:integer',
		'priority'=>'xsd:integer',
		'status_id'=>'xsd:integer',
		'start_date'=>'xsd:integer',
		'end_date'=>'xsd:integer',
		'assigned_to'=>'ArrayOfInteger',
		'dependent_on'=>'ArrayOfInteger',
		array('addProjectTaskResponse'=>'xsd:integer'),
		$uri
	);

//
//	ProjectCategory
//
$server->wsdl->addComplexType(
	'ProjectCategory',
	'complexType',
	'struct',
	'sequence',
	'',
	array(
	'id' => array('name'=>'id', 'type' => 'xsd:integer'),
	'group_project_id' => array('name'=>'group_project_id', 'type' => 'xsd:integer'),
	'category_name' => array('name'=>'category_name', 'type' => 'xsd:string')
));

$server->wsdl->addComplexType(
	'ArrayOfProjectCategory',
	'complexType',
	'array',
	'',
	'SOAP-ENC:Array',
	array(),
	array(array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'tns:ProjectCategory[]')),
	'tns:ProjectCategory');

$server->register(
	'getProjectTaskCategories',
	array('session_ser'=>'xsd:string','group_id'=>'xsd:integer','group_project_id'=>'xsd:integer'),
	array('getProjectTaskCategoriesResponse'=>'tns:ArrayOfProjectCategory'),
	$uri);

//
//	ProjectMessage
//
$server->wsdl->addComplexType(
	'ProjectMessage',
	'complexType',
	'struct',
	'sequence',
	'',
	array(
	'project_message_id' => array('name'=>'project_message_id', 'type' => 'xsd:integer'),
	'project_task_id' => array('name'=>'project_task_id', 'type' => 'xsd:integer'),
	'body' => array('name'=>'body', 'type' => 'xsd:string'),
	'postdate' => array('name'=>'postdate', 'type' => 'xsd:integer'),
	'posted_by' => array('name'=>'posted_by', 'type' => 'xsd:integer')
));

$server->wsdl->addComplexType(
	'ArrayOfProjectMessage',
	'complexType',
	'array',
	'',
	'SOAP-ENC:Array',
	array(),
	array(array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'tns:ProjectMessage[]')),
	'tns:ProjectMessage');

$server->register(
	'getProjectMessages',
	array('session_ser'=>'xsd:string',
		'group_id'=>'xsd:integer',
		'group_project_id'=>'xsd:integer',
		'project_task_id'=>'xsd:integer'),
	array('getProjectMessagesResponse'=>'tns:ArrayOfProjectMessage'),
	$uri);

//add
$server->register(
	'addProjectMessage',
	array('session_ser'=>'xsd:string',
		'group_id'=>'xsd:integer',
		'group_project_id'=>'xsd:integer',
		'project_task_id'=>'xsd:integer',
		'body'=>'xsd:string'),
	array('addProjectMessageResponse'=>'xsd:integer'),
	$uri);

//
//	ProjectTaskTechnician
//
//	Array of Users
//
$server->register(
	'getProjectTechnicians',
	array('session_ser'=>'xsd:string',
		'group_id'=>'xsd:integer',
		'group_project_id'=>'xsd:integer'),
	array('getProjectTechniciansResponse'=>'tns:ArrayOfUser'),
	$uri);



//
//	getProjectGroups
//
function &getProjectGroups($session_ser,$group_id) {
	continue_session($session_ser);
	$grp =& group_get_object($group_id);
	if (!$grp || !is_object($grp) {
		return new soap_fault ('','getProjectGroups','Could Not Get Group','Could Not Get Group');
	} elseif ($grp->isError()) {
		return new soap_fault ('','getProjectGroups',$grp->getErrorMessage(),$grp->getErrorMessage());
	}

	$atf = new ProjectGroupFactory($grp);
	if (!$atf || !is_object($atf) {
		return new soap_fault ('','getProjectGroups','Could Not Get ProjectGroupFactory','Could Not Get ProjectGroupFactory');
	} elseif ($atf->isError()) {
		return new soap_fault ('','getProjectGroups',$atf->getErrorMessage(),$atf->getErrorMessage());
	}
	return projectgroup_to_soap($atf->getProjectGroups());
}

//
//  convert array of artifact types to soap data structure
//
function projectgroup_to_soap($at_arr) {
	for ($i=0; $i<count($at_arr); $i++) {
		if ($at_arr[$i]->isError()) {
			//skip if error
		} else {
			$return[]=array(
				'group_project_id'=>$at_arr->data_array['group_project_id'],
				'group_id'=>$at_arr->data_array['group_id'],
				'name'=>$at_arr->data_array['name'],
				'description'=>$at_arr->data_array['description'],
				'is_public'=>$at_arr->data_array['is_public'],
				'allow_anon'=>$at_arr->data_array['allow_anon'],
				'due_period'=>$at_arr->data_array['due_period'],
				'use_resolution'=>$at_arr->data_array['use_resolution'],
				'datatype'=>$at_arr->data_array['datatype'],
				'status_timeout'=>$at_arr->data_array['status_timeout']
			);
		}
	}
	return new soapval('tns:ArrayOfProjectGroup', 'ArrayOfProjectGroup', $return);
}

//
//	addProjectTask
//
function &addProjectTask($session_ser,$group_id,$group_project_id,$status_id,$category_id,
	$artifact_group_id,$resolution_id,$priority,$assigned_to,$summary,$details) {
	continue_session($session_ser);
	$grp =& group_get_object($group_id);
	if (!$grp || !is_object($grp) {
		return new soap_fault ('','addProjectTask','Could Not Get Group','Could Not Get Group');
	} elseif ($grp->isError()) {
		return new soap_fault ('','addProjectTask',$grp->getErrorMessage(),$grp->getErrorMessage());
	}

	$at = new ProjectGroup($grp,$group_project_id);
	if (!$at || !is_object($at) {
		return new soap_fault ('','addProjectTask','Could Not Get ProjectGroup','Could Not Get ProjectGroup');
	} elseif ($at->isError()) {
		return new soap_fault ('','addProjectTask',$at->getErrorMessage(),$at->getErrorMessage());
	}

	$a = new ProjectTask($at);
	if (!$a || !is_object($a) {
		return new soap_fault ('','addProjectTask','Could Not Get ProjectTask','Could Not Get ProjectTask');
	} elseif ($a->isError()) {
		return new soap_fault ('','addProjectTask','$a->getErrorMessage()',$a->getErrorMessage());
	}

	if (!$a->create($category_id,$artifact_group_id,$summary,$details,$assigned_to,$priority) {
		return new soap_fault ('','addProjectTask',$a->getErrorMessage(),$a->getErrorMessage());
	} else {
		soapval('xsd:integer', 'integer', $a->getID());
	}
}

//
//	getProjectTaskCategories
//
function &getProjectTaskCategories($session_ser,$group_id,$group_project_id) {
	continue_session($session_ser);
	$grp =& group_get_object($group_id);
	if (!$grp || !is_object($grp) {
		return new soap_fault ('','getProjectTaskCategories','Could Not Get Group','Could Not Get Group');
	} elseif ($grp->isError()) {
		return new soap_fault ('','getProjectTaskCategories',$grp->getErrorMessage(),$grp->getErrorMessage());
	}

	$at = new ProjectGroup($grp,$group_project_id);
	if (!$at || !is_object($at) {
		return new soap_fault ('','getProjectTaskCategories','Could Not Get ProjectGroup','Could Not Get ProjectGroup');
	} elseif ($at->isError()) {
		return new soap_fault ('','getProjectTaskCategories',$at->getErrorMessage(),$at->getErrorMessage());
	}

	return projectcategories_to_soap($at->getCategoryObjects());
}

//
//  convert array of artifact categories to soap data structure
//
function projectcategories_to_soap($at_arr) {
	for ($i=0; $i<count($at_arr); $i++) {
		if ($at_arr[$i]->isError()) {
			//skip if error
		} else {
			$return[]=array(
				'id'=>$at_arr->data_array['id'],
				'group_project_id'=>$at_arr->data_array['group_project_id'],
				'category_name'=>$at_arr->data_array['category_name'],
				'auto_assign_to'=>$at_arr->data_array['auto_assign_to']
			);
		}
	}
	return new soapval('tns:ArrayOfArtifactCategory', 'ArrayOfArtifactCategory', $return);
}

//
//	getProjectTechnicians
//
function &getProjectTechnicians($session_ser,$group_id,$group_project_id) {
	continue_session($session_ser);
	$grp =& group_get_object($group_id);
	if (!$grp || !is_object($grp) {
		return new soap_fault ('','getProjectTechnicians','Could Not Get Group','Could Not Get Group');
	} elseif ($grp->isError()) {
		return new soap_fault ('','getProjectTechnicians',$grp->getErrorMessage(),$grp->getErrorMessage());
	}

	$at = new ProjectGroup($grp,$group_project_id);
	if (!$at || !is_object($at) {
		return new soap_fault ('','getProjectTechnicians','Could Not Get ProjectGroup','Could Not Get ProjectGroup');
	} elseif ($at->isError()) {
		return new soap_fault ('','getProjectTechnicians',$at->getErrorMessage(),$at->getErrorMessage());
	}

	return users_to_soap($at->getTechnicianObjects());
}

function &getProjectTasks($session_ser,$group_id,$group_project_id,$assigned_to,$status,$category,$group) {
	continue_session($session_ser);
	$grp =& group_get_object($group_id);
	if (!$grp || !is_object($grp) {
		return new soap_fault ('','getProjectTasks','Could Not Get Group','Could Not Get Group');
	} elseif ($grp->isError()) {
		return new soap_fault ('','getProjectTasks',$grp->getErrorMessage(),$grp->getErrorMessage());
	}

	$at = new ProjectGroup($grp,$group_project_id);
	if (!$at || !is_object($at) {
		return new soap_fault ('','getProjectTasks','Could Not Get ProjectGroup','Could Not Get ProjectGroup');
	} elseif ($at->isError()) {
		return new soap_fault ('','getProjectTasks',$at->getErrorMessage(),$at->getErrorMessage());
	}

	$af = new ProjectTaskFactory($at);
	if (!$af || !is_object($af) {
		return new soap_fault ('','getProjectTasks','Could Not Get ProjectTaskFactory','Could Not Get ProjectTaskFactory');
	} elseif ($af->isError()) {
		return new soap_fault ('','getProjectTasks',$af->getErrorMessage(),$af->getErrorMessage());
	}

	$af->setup(0,0,0,0,$assigned_to,$status,$category,$group);
	return projecttasks_to_soap($af->getProjectTasks());
}

//
//  convert array of projecttasks to soap data structure
//
function projecttasks_to_soap($at_arr) {
	for ($i=0; $i<count($at_arr); $i++) {
		if ($at_arr[$i]->isError()) {
			//skip if error
		} else {
			$return[]=array(
				'project_task_id'=>$at_arr->data_array['project_task_id'],
				'group_project_id'=>$at_arr->data_array['group_project_id'],
				'status_id'=>$at_arr->data_array['status_id'],
				'category_id'=>$at_arr->data_array['category_id'],
				'artifact_group_id'=>$at_arr->data_array['artifact_group_id'],
				'resolution_id'=>$at_arr->data_array['resolution_id'],
				'priority'=>$at_arr->data_array['priority'],
				'submitted_by'=>$at_arr->data_array['submitted_by'],
				'assigned_to'=>$at_arr->data_array['assigned_to'],
				'open_date'=>$at_arr->data_array['open_date'],
				'close_date'=>$at_arr->data_array['close_date'],
				'summary'=>$at_arr->data_array['summary'],
				'details'=>$at_arr->data_array['details']
			);
		}
	}
	return new soapval('tns:ArrayOfArtifactFile', 'ArrayOfArtifactFile', $return);
}

//
//	getProjectMessages
//
function &getProjectMessages($session_ser,$group_id,$group_project_id,$project_task_id) {
	continue_session($session_ser);
	$grp =& group_get_object($group_id);
	if (!$grp || !is_object($grp) {
		return new soap_fault ('','getProjectMessages','Could Not Get Group','Could Not Get Group');
	} elseif ($grp->isError()) {
		return new soap_fault ('','getProjectMessages',$grp->getErrorMessage(),$grp->getErrorMessage());
	}

	$at = new ProjectGroup($grp,$group_project_id);
	if (!$at || !is_object($at) {
		return new soap_fault ('','getProjectMessages','Could Not Get ProjectGroup','Could Not Get ProjectGroup');
	} elseif ($at->isError()) {
		return new soap_fault ('','getProjectMessages',$at->getErrorMessage(),$at->getErrorMessage());
	}

	$a = new ProjectTask($at,$project_task_id);
	if (!$a || !is_object($a) {
		return new soap_fault ('','getProjectMessages','Could Not Get ProjectTask','Could Not Get ProjectTask');
	} elseif ($a->isError()) {
		return new soap_fault ('','getProjectMessages',$a->getErrorMessage(),$a->getErrorMessage());
	}
	return projecttaskmessages_to_soap($a->getMessageObjects());
}

//
//  convert array of artifact messages to soap data structure
//
function projecttaskmessages_to_soap($at_arr) {
	for ($i=0; $i<count($at_arr); $i++) {
		if ($at_arr[$i]->isError()) {
			//skip if error
		} else {
			$return[]=array(
				'id'=>$at_arr->data_array['id'],
				'project_task_id'=>$at_arr->data_array['project_task_id'],
				'body'=>$at_arr->data_array['body'],
				'adddate'=>$at_arr->data_array['adddate'],
				'user_id'=>$at_arr->data_array['user_id']
			);
		}
	}
	return new soapval('tns:ArrayOfArtifactMessage', 'ArrayOfArtifactMessage', $return);
}

//
//	addProjectMessage
//
function &addProjectMessage($session_ser,$group_id,$group_project_id,$project_task_id,$body) {
	continue_session($session_ser);
	$grp =& group_get_object($group_id);
	if (!$grp || !is_object($grp) {
		return new soap_fault ('','addProjectMessage','Could Not Get Group','Could Not Get Group');
	} elseif ($grp->isError()) {
		return new soap_fault ('','addProjectMessage',$grp->getErrorMessage(),$grp->getErrorMessage());
	}

	$at = new ProjectGroup($grp,$group_project_id);
	if (!$at || !is_object($at) {
		return new soap_fault ('','addProjectMessage','Could Not Get ProjectGroup','Could Not Get ProjectGroup');
	} elseif ($at->isError()) {
		return new soap_fault ('','addProjectMessage',$at->getErrorMessage(),$at->getErrorMessage());
	}

	$a = new ProjectTask($at,$project_task_id);
	if (!$a || !is_object($a) {
		return new soap_fault ('','addProjectMessage','Could Not Get ProjectTask','Could Not Get ProjectTask');
	} elseif ($a->isError()) {
		return new soap_fault ('','addProjectMessage',$a->getErrorMessage(),$a->getErrorMessage());
	}

	$am = new ProjectMessage($a);
	if (!$am || !is_object($am) {
		return new soap_fault ('','addProjectMessage','Could Not Get ProjectMessage','Could Not Get ProjectMessage');
	} elseif ($am->isError()) {
		return new soap_fault ('','addProjectMessage',$am->getErrorMessage(),$am->getErrorMessage());
	}

	if (!$am->create($body) {
		return new soap_fault ('','addProjectMessage',$am->getErrorMessage(),$am->getErrorMessage());
	} else {
		return new soap_value ('xsd:integer','integer',$am->getID());
	}
}

?>
