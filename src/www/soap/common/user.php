<?php
/**
 * SOAP User Include - this file contains wrapper functions for the SOAP interface
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
require_once $gfcommon.'include/User.class.php';

// Add The definition of a user object
$server->wsdl->addComplexType(
	'User',
	'complexType',
	'struct',
	'sequence',
	'',
	array(
	'user_id' => array('name'=>'user_id', 'type' => 'xsd:int'),
	'user_name' => array('name'=>'user_name', 'type' => 'xsd:string'),
	'title' => array('name'=>'title', 'type' => 'xsd:string'),
	'firstname' => array('name'=>'firstname', 'type' => 'xsd:string'),
	'lastname' => array('name'=>'lastname', 'type' => 'xsd:string'),
	'address' => array('name'=>'address', 'type' => 'xsd:string'),
	'address2' => array('name'=>'address2', 'type' => 'xsd:string'),
	'phone' => array('name'=>'phone', 'type' => 'xsd:string'),
	'fax' => array('name'=>'fax', 'type' => 'xsd:string'),
	'status' => array('name'=>'status', 'type' => 'xsd:string'),
	'timezone' => array('name'=>'timezone', 'type' => 'xsd:string'),
	'country_code' => array('name'=>'country_code', 'type' => 'xsd:string'),
	'add_date' => array('name'=>'add_date', 'type' => 'xsd:int'), 
	'language_id' => array('name'=>'language_id', 'type' => 'xsd:int') 
	) );

// Array of users
$server->wsdl->addComplexType(
    'ArrayOfUser',
    'complexType',
    'array',
    '',
    'SOAP-ENC:Array',
    array(),
    array(array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'tns:User[]')),
    'tns:User');

//getUsers (id array)
$server->register(
    'getUsers',
    array('session_ser'=>'string','user_ids'=>'tns:ArrayOfint'),
    array('userResponse'=>'tns:ArrayOfUser'),
    $uri,
    $uri.'#getUsers','rpc','encoded'
);

//getUsersByName (unix_name array)
$server->register(
    'getUsersByName',
    array('session_ser'=>'string','user_ids'=>'tns:ArrayOfstring'),
    array('userResponse'=>'tns:ArrayOfUser'),
    $uri,
    $uri.'#getUsersByName','rpc','encoded'
);

//getGroups (id array)
$server->register(
    'userGetGroups',
    array('session_ser'=>'string','user_id'=>'xsd:int'),
    array('groupResponse'=>'tns:ArrayOfGroup'),
    $uri,
    $uri.'#userGetGroups','rpc','encoded'
);


//get user objects for array of user_ids
function &getUsers($session_ser,$user_ids) {
	continue_session($session_ser);
	$usrs =& user_get_objects($user_ids);
	if (!$usrs) {
		return new soap_fault ('3001','user','Could Not Get Users By Id','Could Not Get Users By Id');
	}

	return users_to_soap($usrs);
}

//get user objects for array of unix_names
function getUsersByName($session_ser,$user_names) {
	continue_session($session_ser);
	$usrs =& user_get_objects_by_name($user_names);
	if (!$usrs) {
		return new soap_fault ('3002','user','Could Not Get Users By Name','Could Not Get Users By Name');
	}

	return users_to_soap($usrs);
}

//get groups for user_id
function &userGetGroups($session_ser,$user_id) {
	continue_session($session_ser);
	$user =& user_get_object($user_id);
	if (!$user) {
		return new soap_fault ('3003','user','Could Not Get Users Projects','Could Not Get Users Projects');
	}
	return groups_to_soap($user->getGroups());
}

/*
	Converts an array of User objects to soap data
*/
function &users_to_soap($usrs) {
	$return = array();
	for ($i=0; $i<count($usrs); $i++) {
		if ($usrs[$i]->isError()) {
			return new soap_fault ('','User to soap',$usrs[$i]->getErrorMessage(),$usrs[$i]->getErrorMessage());
			//skip it if it had an error
		} else {
			//build an array of just the fields we want
			$return[] = array(
			'user_id'=>$usrs[$i]->data_array['user_id'], 
			'user_name'=>$usrs[$i]->data_array['user_name'],
			'title'=>$usrs[$i]->data_array['title'],
			'firstname'=>$usrs[$i]->data_array['firstname'],
			'lastname'=>$usrs[$i]->data_array['lastname'],
			'address'=>$usrs[$i]->data_array['address'],
			'address2'=>$usrs[$i]->data_array['address2'],
			'phone'=>$usrs[$i]->data_array['phone'],
			'fax'=>$usrs[$i]->data_array['fax'],
			'status'=>$usrs[$i]->data_array['status'],
			'timezone'=>$usrs[$i]->data_array['timezone'],
			'country_code'=>$usrs[$i]->data_array['country_code'],
			'add_date'=>$usrs[$i]->data_array['add_date'],
			'language_id'=>$usrs[$i]->data_array['language_id']
			);
		}

	}
	return $return;
}


?>
