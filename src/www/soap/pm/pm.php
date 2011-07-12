<?php
/**
 * SOAP Tasks Include - this file contains wrapper functions for the SOAP interface
 *
 * Copyright 2004 (c) GForge, LLC
 * http://gforge.org
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
require_once $gfcommon.'pm/ProjectGroup.class.php';
require_once $gfcommon.'pm/ProjectGroupFactory.class.php';
require_once $gfcommon.'pm/ProjectTaskFactory.class.php';
require_once $gfcommon.'pm/ProjectTask.class.php';
require_once $gfcommon.'pm/ProjectCategory.class.php';
//require_once('common/pm/ProjectMessage.class.php');

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
	'group_project_id' => array('name'=>'group_project_id', 'type' => 'xsd:int'),
	'group_id' => array('name'=>'group_id', 'type' => 'xsd:int'),
	'name' => array('name'=>'name', 'type' => 'xsd:string'),
	'description' => array('name'=>'description', 'type' => 'xsd:string'),
	'is_public' => array('name'=>'is_public', 'type' => 'xsd:int'),
	'send_all_posts_to' => array('name'=>'send_all_posts_to', 'type' => 'xsd:string')
	)
);

$server->wsdl->addComplexType(
	'ArrayOfProjectGroup',
	'complexType',
	'array',
	'',
	'SOAP-ENC:Array',
	array(),
	array(array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'tns:ProjectGroup[]')),
	'tns:ProjectGroup'
);

$server->register(
	'getProjectGroups',
	array('session_ser'=>'xsd:string','group_id'=>'xsd:int'),
	array('getProjectGroupsResponse'=>'tns:ArrayOfProjectGroup'),
	$uri,$uri.'#getProjectGroups','rpc','encoded'

);

$server->wsdl->addComplexType(
	'TaskDependency',
	'complexType',
	'struct',
	'sequence',
	'',
	array(
	'task_id' => array('name'=>'task_id', 'type' => 'xsd:int'),
	'link_type' => array('name'=>'link_type', 'type' => 'xsd:string')
	)
);

$server->wsdl->addComplexType(
	'ArrayOfTaskDependency',
	'complexType',
	'array',
	'',
	'SOAP-ENC:Array',
	array(),
	array(array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'tns:TaskDependency[]')),
	'tns:TaskDependency'
);


$server->wsdl->addComplexType(
	'TaskAssignee',
	'complexType',
	'struct',
	'sequence',
	'',
	array(
	'user_id' => array('name'=>'user_id', 'type' => 'xsd:int'),
	'percent_effort' => array('name'=>'link_type', 'type' => 'xsd:int')
	)
);

$server->wsdl->addComplexType(
	'ArrayOfTaskAssignee',
	'complexType',
	'array',
	'',
	'SOAP-ENC:Array',
	array(),
	array(array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'tns:TaskAssignee[]')),
	'tns:TaskAssignee'
);



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
	'project_task_id' => array('name'=>'project_task_id', 'type' => 'xsd:int'),
	'group_project_id' => array('name'=>'group_project_id', 'type' => 'xsd:int'),
	'summary' => array('name'=>'summary', 'type' => 'xsd:string'),
	'details' => array('name'=>'details', 'type' => 'xsd:string'),
	'percent_complete' => array('name'=>'percent_complete', 'type' => 'xsd:int'),
	'priority' => array('name'=>'priority', 'type' => 'xsd:int'),
	'hours' => array('name'=>'hours', 'type' => 'xsd:int'),
	'start_date' => array('name'=>'start_date', 'type' => 'xsd:int'),
	'end_date' => array('name'=>'end_date', 'type' => 'xsd:int'),
	'status_id' => array('name'=>'status_id', 'type' => 'xsd:int'),
	'category_id' => array('name'=>'category_id', 'type' => 'xsd:int'),
	'dependent_on' => array('name'=>'dependent_on', 'type' => 'tns:ArrayOfTaskDependency'),
	'assigned_to' => array('name'=>'assigned_to', 'type' => 'tns:ArrayOfTaskAssignee'),
	'duration' => array('name'=>'duration', 'type' => 'xsd:int'),
	'parent_id' => array('name'=>'parent_id', 'type' => 'xsd:int'),
	'sort_id' => array('name'=>'sort_id', 'type' => 'xsd:int'),
	)
);

$server->wsdl->addComplexType(
	'ArrayOfProjectTask',
	'complexType',
	'array',
	'',
	'SOAP-ENC:Array',
	array(),
	array(array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'tns:ProjectTask[]')),
	'tns:ProjectTask'
);

//getProjectTask
$server->register(
	'getProjectTasks',
	array(
		'session_ser'=>'xsd:string',
		'group_id'=>'xsd:int',
		'group_project_id'=>'xsd:int',
		'assigned_to'=>'xsd:int',
		'status'=>'xsd:int',
		'category'=>'xsd:int',
		'group'=>'xsd:int'),
	array('getProjectTasksResponse'=>'tns:ArrayOfProjectTask'),
		$uri,$uri.'#getProjectTasks','rpc','encoded'
	);

//addProjectTask
$server->register(
	'addProjectTask',
	array(
		'session_ser'=>'xsd:string',
		'group_id'=>'xsd:int',
		'group_project_id'=>'xsd:int',
		'summary'=>'xsd:string',
		'details'=>'xsd:string',
		'priority'=>'xsd:int',
		'hours'=>'xsd:int',
		'start_date'=>'xsd:int',
		'end_date'=>'xsd:int',
		'category_id'=>'xsd:int',
		'percent_complete'=>'xsd:int',
		'assigned_to'=>'tns:ArrayOfint',
		'dependent_on'=>'tns:ArrayOfint',
		'duration'=>'xsd:int',
		'parent_id'=>'xsd:int'
		),
		array('addProjectTaskResponse'=>'xsd:int'),
		$uri,$uri.'#addProjectTask','rpc','encoded'
);

//updateProjectTask
$server->register(
	'updateProjectTask',
	array(
		'session_ser'=>'xsd:string',
		'group_id'=>'xsd:int',
		'group_project_id'=>'xsd:int',
		'project_task_id'=>'xsd:int',
		'summary'=>'xsd:string',
		'details'=>'xsd:string',
		'priority'=>'xsd:int',
		'hours'=>'xsd:int',
		'start_date'=>'xsd:int',
		'end_date'=>'xsd:int',
		'status_id'=>'xsd:int',
		'category_id'=>'xsd:int',
		'percent_complete'=>'xsd:int',
		'assigned_to'=>'tns:ArrayOfint',
		'dependent_on'=>'tns:ArrayOfint',
		'new_group_project_id'=>'int',
		'duration'=>'xsd:int',
		'parent_id'=>'xsd:int'
		),
		array('updateProjectTaskResponse'=>'xsd:int'),
		$uri,$uri.'#updateProjectTask','rpc','encoded'
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
/*
	'id' => array('name'=>'id', 'type' => 'xsd:int'),
	'group_project_id' => array('name'=>'group_project_id', 'type' => 'xsd:int'),
	'category_name' => array('name'=>'category_name', 'type' => 'xsd:string')
*/
	'category_id' => array('name'=>'category_id', 'type' => 'xsd:int'),
	'category_name' => array('name'=>'category_name', 'type' => 'xsd:string')
	)
);

$server->wsdl->addComplexType(
	'ArrayOfProjectCategory',
	'complexType',
	'array',
	'',
	'SOAP-ENC:Array',
	array(),
	array(array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'tns:ProjectCategory[]')),
	'tns:ProjectCategory'
);

$server->register(
	'getProjectTaskCategories',
	array('session_ser'=>'xsd:string','group_id'=>'xsd:int','group_project_id'=>'xsd:int'),
	array('getProjectTaskCategoriesResponse'=>'tns:ArrayOfProjectCategory'),
	$uri,$uri.'#getProjectTaskCategories','rpc','encoded'
);

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
	'project_message_id' => array('name'=>'project_message_id', 'type' => 'xsd:int'),
	'project_task_id' => array('name'=>'project_task_id', 'type' => 'xsd:int'),
	'body' => array('name'=>'body', 'type' => 'xsd:string'),
	'postdate' => array('name'=>'postdate', 'type' => 'xsd:int'),
	'posted_by' => array('name'=>'posted_by', 'type' => 'xsd:int')
	)
);

$server->wsdl->addComplexType(
	'ArrayOfProjectMessage',
	'complexType',
	'array',
	'',
	'SOAP-ENC:Array',
	array(),
	array(array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'tns:ProjectMessage[]')),
	'tns:ProjectMessage'
);

$server->register(
	'getProjectMessages',
	array('session_ser'=>'xsd:string',
		'group_id'=>'xsd:int',
		'group_project_id'=>'xsd:int',
		'project_task_id'=>'xsd:int'
	),
	array('getProjectMessagesResponse'=>'tns:ArrayOfProjectMessage'),
	$uri,$uri.'#getProjectMessages','rpc','encoded'
);

//add
$server->register(
	'addProjectMessage',
	array('session_ser'=>'xsd:string',
		'group_id'=>'xsd:int',
		'group_project_id'=>'xsd:int',
		'project_task_id'=>'xsd:int',
		'body'=>'xsd:string'
	),
	array('addProjectMessageResponse'=>'xsd:int'),
	$uri,$uri.'#addProjectMessage','rpc','encoded'
);

//
//	ProjectTaskTechnician
//
//	Array of Users
//
$server->register(
	'getProjectTechnicians',
	array('session_ser'=>'xsd:string',
		'group_id'=>'xsd:int',
		'group_project_id'=>'xsd:int'),
	array('getProjectTechniciansResponse'=>'tns:ArrayOfUser'),
	$uri,$uri.'#getProjectTechnicians','rpc','encoded'
);


//
//	getProjectGroups
//
function &getProjectGroups($session_ser,$group_id) {
	continue_session($session_ser);
	$grp = group_get_object($group_id);
	if (!$grp || !is_object($grp)) {
		return new soap_fault ('','getProjectGroups','Could Not Get Project','Could Not Get Project');
	} elseif ($grp->isError()) {
		return new soap_fault ('','getProjectGroups',$grp->getErrorMessage(),$grp->getErrorMessage());
	}

	$atf = new ProjectGroupFactory($grp);
	if (!$atf || !is_object($atf)) {
		return new soap_fault ('','getProjectGroups','Could Not Get ProjectGroupFactory','Could Not Get ProjectGroupFactory');
	} elseif ($atf->isError()) {
		return new soap_fault ('','getProjectGroups',$atf->getErrorMessage(),$atf->getErrorMessage());
	}
	return projectgroups_to_soap($atf->getProjectGroups());
}

//
//  convert array of artifact types to soap data structure
//
function projectgroups_to_soap($pg_arr) {
	$return = array();
	for ($i=0; $i<count($pg_arr); $i++) {
		if (!is_a($pg_arr[$i], 'ProjectGroup') || $pg_arr[$i]->isError()) {
			//skip if error
		} else {
			$return[]=array(
				'group_project_id'=>$pg_arr[$i]->data_array['group_project_id'],
				'group_id'=>$pg_arr[$i]->data_array['group_id'],
				'name'=>$pg_arr[$i]->data_array['project_name'],
				'description'=>$pg_arr[$i]->data_array['description'],
				'is_public'=>$pg_arr[$i]->data_array['is_public'],
				'send_all_posts_to'=>$pg_arr[$i]->data_array['send_all_posts_to']
			);
		}
	}
	return $return;
}

//
//	addProjectTask
//
function &addProjectTask($session_ser,$group_id,$group_project_id,$summary,$details,$priority,
	$hours,$start_date,$end_date,$category_id,$percent_complete,$assigned_arr,$depend_arr,$duration,$parent_id) {
	continue_session($session_ser);
	$grp = group_get_object($group_id);
	if (!$grp || !is_object($grp)) {
		return new soap_fault ('','addProjectTask','Could Not Get Project','Could Not Get Project');
	} elseif ($grp->isError()) {
		return new soap_fault ('','addProjectTask',$grp->getErrorMessage(),$grp->getErrorMessage());
	}

	$at = new ProjectGroup($grp,$group_project_id);
	if (!$at || !is_object($at)) {
		return new soap_fault ('','addProjectTask','Could Not Get ProjectGroup','Could Not Get ProjectGroup');
	} elseif ($at->isError()) {
		return new soap_fault ('','addProjectTask',$at->getErrorMessage(),$at->getErrorMessage());
	}

	$a = new ProjectTask($at);
	if (!$a || !is_object($a)) {
		return new soap_fault ('','addProjectTask','Could Not Get ProjectTask','Could Not Get ProjectTask');
	} elseif ($a->isError()) {
		return new soap_fault ('','addProjectTask','$a->getErrorMessage()',$a->getErrorMessage());
	}

	// transform the $depend_arr (which is an array of ints) to include the link type
	$depend_map = array();
	foreach ($depend_arr as $depend_id) {
		$depend_map[$depend_id] = PM_LINK_DEFAULT;
	}

	if (!$a->create($summary,$details,$priority,$hours,$start_date,$end_date,
            $category_id,$percent_complete,$assigned_arr,$depend_map,$duration,$parent_id)) {
		return new soap_fault ('','addProjectTask',$a->getErrorMessage(),$a->getErrorMessage());
	} else {
		return $a->getID();
	}
}

//
//  Update ProjectTask
//
function &updateProjectTask($session_ser,$group_id,$group_project_id,$project_task_id,
	$summary,$details,$priority,$hours,$start_date,$end_date,$status_id,$category_id,
    $percent_complete,$assigned_arr,$depend_arr,$new_group_project_id,$duration,$parent_id) {
    continue_session($session_ser);
    $grp = group_get_object($group_id);
    if (!$grp || !is_object($grp)) {
        return new soap_fault ('','updateProjectTask','Could Not Get Project','Could Not Get Project');
    } elseif ($grp->isError()) {
        return new soap_fault ('','updateProjectTask',$grp->getErrorMessage(),$grp->getErrorMessage());
    }

    $at = new ProjectGroup($grp,$group_project_id);
    if (!$at || !is_object($at)) {
        return new soap_fault ('','updateProjectTask','Could Not Get ProjectGroup','Could Not Get ProjectGroup');
    } elseif ($at->isError()) {
        return new soap_fault ('','updateProjectTask',$at->getErrorMessage(),$at->getErrorMessage());
    }

    $a = new ProjectTask($at,$project_task_id);
    if (!$a || !is_object($a)) {
        return new soap_fault ('','updateProjectTask','Could Not Get ProjectTask','Could Not Get ProjectTask');
    } elseif ($a->isError()) {
        return new soap_fault ('','updateProjectTask',$a->getErrorMessage(),$a->getErrorMessage());
    }

    // transform the $depend_arr (which is an array of ints) to include the link type
	$depend_map = array();
	foreach ($depend_arr as $depend_id) {
		$depend_map[$depend_id] = PM_LINK_DEFAULT;
	}

    if (!$a->update($summary,$details,$priority,$hours,$start_date,$end_date,$status_id,$category_id,
		$percent_complete,$assigned_arr,$depend_map,$new_group_project_id,$duration,$parent_id)) {
        return new soap_fault ('','updateProjectTask',$a->getErrorMessage(),$a->getErrorMessage());
    } else {
        return $a->getID();
    }
}

//
//	getProjectTaskCategories
//
function &getProjectTaskCategories($session_ser,$group_id,$group_project_id) {
	continue_session($session_ser);
	$grp = group_get_object($group_id);
	if (!$grp || !is_object($grp)) {
		return new soap_fault ('','getProjectTaskCategories','Could Not Get Project','Could Not Get Project');
	} elseif ($grp->isError()) {
		return new soap_fault ('','getProjectTaskCategories',$grp->getErrorMessage(),$grp->getErrorMessage());
	}

	$at = new ProjectGroup($grp,$group_project_id);
	if (!$at || !is_object($at)) {
		return new soap_fault ('','getProjectTaskCategories','Could Not Get ProjectGroup','Could Not Get ProjectGroup');
	} elseif ($at->isError()) {
		return new soap_fault ('','getProjectTaskCategories',$at->getErrorMessage(),$at->getErrorMessage());
	}

	$cat_arr = $at->getCategoryObjects();

	return projectcategories_to_soap($cat_arr);
}

//
//  convert array of artifact categories to soap data structure
//
function projectcategories_to_soap($cat_arr) {
	$return = array();
	for ($i=0; $i<count($cat_arr); $i++) {
		if ($cat_arr[$i]->isError()) {
			//skip if error
		} else {
			$return[]=array(
				'category_id'=>$cat_arr[$i]->data_array['category_id'],
				'category_name'=>$cat_arr[$i]->data_array['category_name']
			);
		}
	}
	return $return;
}

//
//	getProjectTechnicians
//
function &getProjectTechnicians($session_ser,$group_id,$group_project_id) {
	continue_session($session_ser);
	$grp = group_get_object($group_id);
	if (!$grp || !is_object($grp)) {
		return new soap_fault ('','getProjectTechnicians','Could Not Get Project','Could Not Get Project');
	} elseif ($grp->isError()) {
		return new soap_fault ('','getProjectTechnicians',$grp->getErrorMessage(),$grp->getErrorMessage());
	}

	$pg = new ProjectGroup($grp,$group_project_id);
	if (!$pg || !is_object($pg)) {
		return new soap_fault ('','getProjectTechnicians','Could Not Get ProjectGroup','Could Not Get ProjectGroup');
	} elseif ($pg->isError()) {
		return new soap_fault ('','getProjectTechnicians',$pg->getErrorMessage(),$pg->getErrorMessage());
	}

	$engine = RBACEngine::getInstance () ;
	$techs = $engine->getUsersByAllowedAction ('pm', $pg->getID(), 'tech') ;

	return users_to_soap ($techs);
}

function &getProjectTasks($session_ser,$group_id,$group_project_id,$assigned_to,$status,$category,$group) {
	continue_session($session_ser);
	$grp = group_get_object($group_id);
	if (!$grp || !is_object($grp)) {
		return new soap_fault ('','getProjectTasks','Could Not Get Project','Could Not Get Project');
	} elseif ($grp->isError()) {
		return new soap_fault ('','getProjectTasks',$grp->getErrorMessage(),$grp->getErrorMessage());
	}

	$pg = new ProjectGroup($grp,$group_project_id);
	if (!$pg || !is_object($pg)) {
		return new soap_fault ('','getProjectTasks','Could Not Get ProjectGroup','Could Not Get ProjectGroup');
	} elseif ($pg->isError()) {
		return new soap_fault ('','getProjectTasks',$pg->getErrorMessage(),$pg->getErrorMessage());
	}

	$ptf = new ProjectTaskFactory($pg);
	if (!$ptf || !is_object($ptf)) {
		return new soap_fault ('','getProjectTasks','Could Not Get ProjectTaskFactory','Could Not Get ProjectTaskFactory');
	} elseif ($ptf->isError()) {
		return new soap_fault ('','getProjectTasks',$ptf->getErrorMessage(),$ptf->getErrorMessage());
	}

	$ptf->setup(0,0,0,0,$assigned_to,$status,$category,$group);
	$tasks_arr = $ptf->getTasks();
	return projecttasks_to_soap($tasks_arr);
}

//
//  convert array of projecttasks to soap data structure
//
function projecttasks_to_soap($pt_arr) {
	$return = array();

	for ($i=0; $i<count($pt_arr); $i++) {
		if ($pt_arr[$i]->isError()) {
			//skip if error
		} else {
			// create the dependent_on array
			$dependent_on = array();
			$dependent_on_tmp = $pt_arr[$i]->getDependentOn();
			foreach ($dependent_on_tmp as $dependent_on_id => $link_type) {
				$dependent_on[] = array("task_id" => $dependent_on_id,
										"link_type" => $link_type);
			}

			//build the assigned_to array
			$assigned_to = array();
			$assigned_ids = $pt_arr[$i]->getAssignedTo();
			foreach ($assigned_ids as $assigned_id) {
				$assigned_to[] = array("user_id" => $assigned_id,
										"percent_effort" => 0		// TODO: This should be implemented
									);
			}

			$sort_id = $pt_arr[$i]->getExternalID();
			if (!$sort_id) $sort_id=0;

			$return[]=array(
				'project_task_id'=>$pt_arr[$i]->data_array['project_task_id'],
				'group_project_id'=>$pt_arr[$i]->data_array['group_project_id'],
				'summary'=>$pt_arr[$i]->data_array['summary'],
				'details'=>$pt_arr[$i]->data_array['details'],
				'percent_complete'=>$pt_arr[$i]->data_array['percent_complete'],
				'priority'=>$pt_arr[$i]->data_array['priority'],
				'hours'=>$pt_arr[$i]->data_array['hours'],
				'start_date'=>$pt_arr[$i]->data_array['start_date'],
				'end_date'=>$pt_arr[$i]->data_array['end_date'],
				'status_id'=>$pt_arr[$i]->data_array['status_id'],
				'category_id'=>$pt_arr[$i]->data_array['category_id'],
				'dependent_on'=>$dependent_on,
				'assigned_to'=>$assigned_to,
				'duration'=>$pt_arr[$i]->getDuration(),
				'parent_id'=>$pt_arr[$i]->getParentID(),
				'sort_id'=>$sort_id
			);
		}
	}
	return $return;
}

//
//	getProjectMessages
//
function &getProjectMessages($session_ser,$group_id,$group_project_id,$project_task_id) {
	continue_session($session_ser);
	$grp = group_get_object($group_id);
	if (!$grp || !is_object($grp)) {
		return new soap_fault ('','getProjectMessages','Could Not Get Project','Could Not Get Project');
	} elseif ($grp->isError()) {
		return new soap_fault ('','getProjectMessages',$grp->getErrorMessage(),$grp->getErrorMessage());
	}

	$at = new ProjectGroup($grp,$group_project_id);
	if (!$at || !is_object($at)) {
		return new soap_fault ('','getProjectMessages','Could Not Get ProjectGroup','Could Not Get ProjectGroup');
	} elseif ($at->isError()) {
		return new soap_fault ('','getProjectMessages',$at->getErrorMessage(),$at->getErrorMessage());
	}

	$a = new ProjectTask($at,$project_task_id);
	if (!$a || !is_object($a)) {
		return new soap_fault ('','getProjectMessages','Could Not Get ProjectTask','Could Not Get ProjectTask');
	} elseif ($a->isError()) {
		return new soap_fault ('','getProjectMessages',$a->getErrorMessage(),$a->getErrorMessage());
	}
	return projectmessages_to_soap($a->getMessageObjects());
}

//
//  convert array of project messages to soap data structure
//
function projectmessages_to_soap($at_arr) {
	for ($i=0; $i<count($at_arr); $i++) {
		if ($at_arr[$i]->isError()) {
			//skip if error
		} else {
			$return[]=array(
				'project_message_id'=>$at_arr->data_array['project_message_id'],
				'project_task_id'=>$at_arr->data_array['project_task_id'],
				'body'=>$at_arr->data_array['body'],
				'postdate'=>$at_arr->data_array['postdate'],
				'posted_by'=>$at_arr->data_array['posted_by']
			);
		}
	}
	return $return;
}

//
//	addProjectMessage
//
function &addProjectMessage($session_ser,$group_id,$group_project_id,$project_task_id,$body) {
	continue_session($session_ser);
	$grp = group_get_object($group_id);
	if (!$grp || !is_object($grp)) {
		return new soap_fault ('','addProjectMessage','Could Not Get Project','Could Not Get Project');
	} elseif ($grp->isError()) {
		return new soap_fault ('','addProjectMessage',$grp->getErrorMessage(),$grp->getErrorMessage());
	}

	$at = new ProjectGroup($grp,$group_project_id);
	if (!$at || !is_object($at)) {
		return new soap_fault ('','addProjectMessage','Could Not Get ProjectGroup','Could Not Get ProjectGroup');
	} elseif ($at->isError()) {
		return new soap_fault ('','addProjectMessage',$at->getErrorMessage(),$at->getErrorMessage());
	}

	$a = new ProjectTask($at,$project_task_id);
	if (!$a || !is_object($a)) {
		return new soap_fault ('','addProjectMessage','Could Not Get ProjectTask','Could Not Get ProjectTask');
	} elseif ($a->isError()) {
		return new soap_fault ('','addProjectMessage',$a->getErrorMessage(),$a->getErrorMessage());
	}

	$am = new ProjectMessage($a);
	if (!$am || !is_object($am)) {
		return new soap_fault ('','addProjectMessage','Could Not Get ProjectMessage','Could Not Get ProjectMessage');
	} elseif ($am->isError()) {
		return new soap_fault ('','addProjectMessage',$am->getErrorMessage(),$am->getErrorMessage());
	}

	if (!$am->create($body)) {
		return new soap_fault ('','addProjectMessage',$am->getErrorMessage(),$am->getErrorMessage());
	} else {
		return $am->getID();
	}
}

?>
