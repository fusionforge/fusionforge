<?php
/**
 * SOAP Group Include - this file contains wrapper functions for the SOAP interface
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

require_once('common/include/Error.class.php');
require_once('common/include/Group.class.php');
require_once('common/include/GForge.class.php');

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
	'register_time' => array('name'=>'register_time', 'type' => 'xsd:int') ) );

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

function &getGroups($session_ser,$group_ids) {
	global $feedback;
	continue_session($session_ser);

	$inputArgs = $session_ser;
	for ($i=0; $i<count($group_ids); $i++) {
		$inputArgs = $inputArgs.':'.$group_ids[i];
	}

	$grps =& group_get_objects($group_ids);
	if (!$grps) {
		return new soap_fault ('2001','group','Could Not Get Groups by Id'.$inputArgs,$feedback);
	}

	return groups_to_soap($grps);
}

function &getGroupsByName($session_ser,$group_names) {
	session_continue($session_ser);
	$grps =& group_get_objects_by_name($group_names);
	if (!$grps) {
		return new soap_fault ('2002','group','Could Not Get Groups by Name','Could Not Get Groups by Name');
	}

	return groups_to_soap($grps);
}


// use as a way to get group names for use in getGroupsByName
function &getPublicProjectNames($session_ser) {
	continue_session($session_ser);
	$gforge = new GForge();
	$result = $gforge->getPublicProjectNames();
	if ($gforge->isError()) {
		$errMsg = 'Could Not Get Public Group Names: '.$gforge->getErrorMessage();
		return new soap_fault ('2003','group',$errMsg,$errMsg);
	}
	return $result;
}

/*
	Converts an array of Group objects to soap data
*/
function &groups_to_soap($grps) {
	$return = array();

	for ($i=0; $i<count($grps); $i++) {
		if ($grps[$i]->isError()) {
			//skip it if it had an error
		} else {
			//build an array of just the fields we want
			$return[] = array('group_id'=>$grps[$i]->data_array['group_id'], 
			'group_name'=>$grps[$i]->data_array['group_name'],
			'homepage'=>$grps[$i]->data_array['homepage'],
			'is_public'=>$grps[$i]->data_array['is_public'],
			'status'=>$grps[$i]->data_array['status'],
			'unix_group_name'=>$grps[$i]->data_array['unix_group_name'],
			'short_description'=>$grps[$i]->data_array['short_description'],
			'scm_box'=>$grps[$i]->data_array['scm_box'],
			'register_time'=>$grps[$i]->data_array['register_time']);
		}
	}
	// changed to not return soapval which is not necessary
	// since we have wsdl to describe return value
	return $return;
}


?>
