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

require_once('common/include/Error.class');
require_once('common/include/Group.class');

// Add The definition of a group object
$server->wsdl->addComplexType(
	'Group',
	'complexType',
	'struct',
	'sequence',
	'',
	array(
	'group_id' => array('name'=>'group_id', 'type' => 'xsd:integer'),
	'group_name' => array('name'=>'group_name', 'type' => 'xsd:string'),
	'homepage' => array('name'=>'homepage', 'type' => 'xsd:string'),
	'is_public' => array('name'=>'is_public', 'type' => 'xsd:integer'),
	'status' => array('name'=>'status', 'type' => 'xsd:string'),
	'unix_group_name' => array('name'=>'unix_group_name', 'type' => 'xsd:string'),
	'short_description' => array('name'=>'short_description', 'type' => 'xsd:string'),
	'register_time' => array('name'=>'register_time', 'type' => 'xsd:integer') ) );

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
    array('session_ser'=>'string','group_ids'=>'xsd:integer[]'),
    array('groupResponse'=>'tns:ArrayOfGroup'),
    $uri);

//getGroupsByName (unix_name array)
$server->register(
    'getGroupsByName',
    array('session_ser'=>'string','group_ids'=>'xsd:string[]'),
    array('groupResponse'=>'tns:ArrayOfGroup'),
    $uri);


function &getGroups($session_ser,$group_ids) {
	continue_session($session_ser);
	$grps =& group_get_objects($group_ids);
	if (!$grps) {
		return new soap_fault ('','','','Could Not Get Groups');
	}

	return groups_to_soap($grps);
}

function &getGroupsByName($session_ser,$group_names) {
	continue_session($session_ser);
	$grps =& group_get_objects_by_name($group_names);
	if (!$grps) {
		return new soap_fault ('','','','Could Not Get Groups');
	}

	return groups_to_soap($grps);
}

/*
	Converts an array of Group objects to soap data
*/
function &groups_to_soap($grps) {
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
			'register_time'=>$grps[$i]->data_array['register_time']);
		}

	}
	return new soapval('tns:ArrayOfGroup', 'ArrayOfGroup', $return);
}


?>
