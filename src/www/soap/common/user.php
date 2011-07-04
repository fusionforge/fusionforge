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

//addUser (unix_name,firstname,lastname,password1,password2,email,
	//mail_site,mail_va,language_id,timezone,jabber_address,jabber_only,
	//theme_id,unix_box='shell',address='',address2='',phone='',fax='',
	//title='',ccode='US',send_mail)
$server->register(
    'addUser',
    array('unix_name'=>'xsd:string','firstname'=>'xsd:string',
	'lastname'=>'xsd:string','password1'=>'xsd:string',
	'password2'=>'xsd:string','email'=>'xsd:string',
	'mail_site'=>'xsd:string','mail_va'=>'xsd:string',
	'language_id'=>'xsd:int','timezone'=>'xsd:string',
	'jabber_address'=>'xsd:string','jabber_only'=>'xsd:int',
	'theme_id'=>'xsd:int','unix_box'=>'xsd:string',
	'address'=>'xsd:string','address2'=>'xsd:string',
	'phone'=>'xsd:string','fax'=>'xsd:string',
	'title'=>'xsd:string','ccode'=>'xsd:string'),
    array('addUserResonse'=>'xsd:int'),
    $uri,  
    $uri.'#addUser','rpc','encoded'
);

//updateUser  (session_ser,user_id,firstname,lastname,language_id,timezone,mail_site,mail_va,use_ratings,jabber_address,$jabber_only,theme_id,address,address2,phone,fax,title,ccode)
$server->register(
    'updateUser',
    array('session_ser'=>'xsd:string',
	'user_id'=>'xsd:string',
	'firstname'=>'xsd:string',
	'lastname'=>'xsd:string',
	'language_id'=>'xsd:int',
	'timezone'=>'xsd:string',
	'mail_site'=>'xsd:string',
	'mail_va'=>'xsd:string',
	'use_ratings'=>'xsd:string',	
	'jabber_address'=>'xsd:string',
	'jabber_only'=>'xsd:int',
	'theme_id'=>'xsd:int',
	'address'=>'xsd:string',
	'address2'=>'xsd:string',
	'phone'=>'xsd:string',
	'fax'=>'xsd:string',
	'title'=>'xsd:string',
	'ccode'=>'xsd:string'),
    array('updateUserResonse'=>'xsd:int'),
    $uri,  
    $uri.'#updateUser','rpc','encoded'
);

//deleteUser  (session_ser,user_id)
$server->register(
    'deleteUser',
    array('session_ser'=>'xsd:string','user_id'=>'xsd:string'),
    array('deleteUserResonse'=>'xsd:boolean'),
    $uri,  
    $uri.'#deleteUser','rpc','encoded'
);

//changeStatus  (session_ser,user_id,status)
$server->register(
    'changeStatus',
    array('session_ser'=>'xsd:string',
	'user_id'=>'xsd:string',
	'status'=>'xsd:string'),
    array('changeStatusResonse'=>'xsd:boolean'),
    $uri,  
    $uri.'#changeStatus','rpc','encoded'
);

//changePassword  (session_ser,user_id,password)
$server->register(
    'changePassword',
    array('session_ser'=>'xsd:string',
	'user_id'=>'xsd:string',
	'password'=>'xsd:string'),
    array('changePasswordResonse'=>'xsd:boolean'),
    $uri,  
    $uri.'#changePassword','rpc','encoded'
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

//add user object 
function addUser($unix_name,$firstname,$lastname,$password1,$password2,$email,
		$mail_site,$mail_va,$language_id,$timezone,$jabber_address,$jabber_only,$theme_id,$unix_box,$address,$address2,$phone,$fax,$title,$ccode){
	$new_user = new GFUser();

	$register = $new_user->create($unix_name,$firstname,$lastname,$password1,$password2,$email,$mail_site,$mail_va,$language_id,$timezone,$jabber_address,$jabber_only,$theme_id,'',$address,$address2,$phone,$fax,$title,$ccode);

	if (!$register){
		return new soap_fault ('3004','user','Could Not Add A New User','Could Not Add A New User');
	}

	return $new_user->getID();
}

//update user object 
function updateUser ($session_ser,$user_id,$firstname,$lastname,$language_id,$timezone,$mail_site,$mail_va,$use_ratings,$jabber_address,$jabber_only,$theme_id,$address,$address2,$phone,$fax,$title,$ccode){
	continue_session($session_ser);
	$user =& user_get_object($user_id);
	if (!$user || !is_object($user)) {
		return new soap_fault ('updateUser','Could Not Get User','Could Not Get User');
	}

	if (!$user->update($firstname,$lastname,$language_id,$timezone,$mail_site,$mail_va,$use_ratings,$jabber_address,$jabber_only,$theme_id,$address,$address2,$phone,$fax,$title,$ccode)){
	return new soap_fault ('updateUser',$user->getErrorMessage(),$user->getErrorMessage());
	}else{
		return $user->getID();	
	}
}

//delete user object 
function deleteUser ($session_ser,$user_id){
	continue_session($session_ser);
	$user =& user_get_object($user_id);
	if (!$user || !is_object($user)) {
		return new soap_fault ('deleteUser','Could Not Get User','Could Not Get User');
	}elseif ($user->isError()){
		return new soap_fault ('deleteUser',$user->getErrorMessage(),$user->getErrorMessage());
	}

	if (!$user->delete(true)){
		return new soap_fault ('deleteUser',$user->getErrorMessage(),$user->getErrorMessage());
	}else{
		return true;	
	}
}

//change status user object 
function changeStatus ($session_ser,$user_id,$status){
	continue_session($session_ser);
	$user =& user_get_object($user_id);
	if (!$user || !is_object($user)) {
		return new soap_fault ('changeStatus','Could Not Get User','Could Not Get User');
	}elseif ($user->isError()){
		return new soap_fault ('changeStatus',$user->getErrorMessage(),$user->getErrorMessage());
	}
	
	if (!$user->setStatus($status)){
		return new soap_fault ('changeStatus',$user->getErrorMessage(),$user->getErrorMessage());
	}else{
		return true;
	}
}

//change password user object 
function changePassword ($session_ser,$user_id,$password){
	continue_session($session_ser);
	$user =& user_get_object($user_id);
	if (!$user || !is_object($user)) {
		return new soap_fault ('changePassword','Could Not Get User','Could Not Get User');
	}elseif ($user->isError()){
		return new soap_fault ('changePassword',$user->getErrorMessage(),$user->getErrorMessage());
	}
	
	if (!$user->setPasswd($password)){
		return new soap_fault ('changePassword',$user->getErrorMessage(),$user->getErrorMessage());
	}else{
		return true;
	}
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
			'country_code'=>$usrs[$i]->data_array['ccode'],
			'add_date'=>$usrs[$i]->data_array['add_date'],
			'language_id'=>$usrs[$i]->data_array['language']
			);
		}

	}
	return $return;
}


?>
