<?php
/**
 * SOAP Tracker Include - this file contains wrapper functions for the SOAP interface
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
require_once('common/tracker/ArtifactType.class');
require_once('common/tracker/Artifact.class');
require_once('common/tracker/ArtifactFactory.class');
require_once('common/tracker/ArtifactTypeFactory.class');
require_once('common/tracker/Artifacts.class');
require_once('common/tracker/ArtifactResolution.class');
require_once('common/tracker/ArtifactCategory.class');
require_once('common/tracker/ArtifactGroup.class');
require_once('common/tracker/ArtifactFile.class');
require_once('common/tracker/ArtifactMessage.class');

//
//	ArtifactType
//
$server->wsdl->addComplexType(
	'ArtifactType',
	'complexType',
	'struct',
	'sequence',
	'',
	array(
	'group_artifact_id' => array('name'=>'group_artifact_id', 'type' => 'xsd:integer'),
	'group_id' => array('name'=>'group_id', 'type' => 'xsd:integer'),
	'name' => array('name'=>'name', 'type' => 'xsd:string'),
	'description' => array('name'=>'description', 'type' => 'xsd:string'),
	'is_public' => array('name'=>'is_public', 'type' => 'xsd:integer'),
	'allow_anon' => array('name'=>'allow_anon', 'type' => 'xsd:integer'),
	'due_period' => array('name'=>'due_period', 'type' => 'xsd:integer'),
	'use_resolution' => array('name'=>'use_resolution', 'type' => 'xsd:integer'),
	'datatype' => array('name'=>'datatype', 'type' => 'xsd:integer'),
	'status_timeout' => array('name'=>'status_timeout', 'type' => 'xsd:integer')));

$server->wsdl->addComplexType(
    'ArrayOfArtifactType',
    'complexType',
    'array',
    '',
    'SOAP-ENC:Array',
    array(),
    array(array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'tns:ArtifactType[]')),
    'tns:ArtifactType');

$server->register(
    'getArtifactTypes',
    array('session_ser'=>'xsd:string','group_id'=>'xsd:integer'),
    array('getArtifactTypesResponse'=>'tns:ArrayOfArtifactType'),
    $uri);
//
//	Artifacts
//
$server->wsdl->addComplexType(
	'Artifact',
	'complexType',
	'struct',
	'sequence',
	'',
	array(
	'artifact_id' => array('name'=>'artifact_id', 'type' => 'xsd:integer'),
	'group_artifact_id' => array('name'=>'group_artifact_id', 'type' => 'xsd:integer'),
	'status_id' => array('name'=>'status_id', 'type' => 'xsd:integer'),
	'category_id' => array('name'=>'category_id', 'type' => 'xsd:integer'),
	'artifact_group_id' => array('name'=>'artifact_group_id', 'type' => 'xsd:integer'),
	'resolution_id' => array('name'=>'resolution_id', 'type' => 'xsd:integer'),
	'priority' => array('name'=>'priority', 'type' => 'xsd:integer'),
	'submitted_by' => array('name'=>'submitted_by', 'type' => 'xsd:integer'),
	'assigned_to' => array('name'=>'assigned_to', 'type' => 'xsd:integer'),
	'open_date' => array('name'=>'open_date', 'type' => 'xsd:integer'),
	'close_date' => array('name'=>'close_date', 'type' => 'xsd:integer'),
	'summary' => array('name'=>'summary', 'type' => 'xsd:string'),
	'details' => array('name'=>'details', 'type' => 'xsd:string')));

$server->wsdl->addComplexType(
    'ArrayOfArtifact',
    'complexType',
    'array',
    '',
    'SOAP-ENC:Array',
    array(),
    array(array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'tns:Artifact[]')),
    'tns:Artifact');

//getArtifact
$server->register(
    'getArtifacts',
    array('session_ser'=>'xsd:string','group_id'=>'xsd:integer','group_artifact_id'=>'xsd:integer','assigned_to'=>'xsd:integer','status'=>'xsd:integer','category'=>'xsd:integer','group'=>'xsd:integer'),
    array('getArtifactsResponse'=>'tns:ArrayOfArtifact'),
    $uri);

//addArtifact
$server->register(
    'addArtifact',
    array('session_ser'=>'xsd:string','group_id'=>'xsd:integer','group_artifact_id'=>'xsd:integer',
		'status_id'=>'xsd:integer','category_id'=>'xsd:integer','artifact_group_id'=>'xsd:integer',
		'resolution_id'=>'xsd:integer','priority'=>'xsd:integer','assigned_to'=>'xsd:integer',
		'summary'=>'xsd:string','details'=>'xsd:string'),
    array('addArtifactResponse'=>'xsd:integer'),
    $uri);

//
//	ArtifactCategory
//
$server->wsdl->addComplexType(
	'ArtifactCategory',
	'complexType',
	'struct',
	'sequence',
	'',
	array(
	'id' => array('name'=>'id', 'type' => 'xsd:integer'),
	'group_artifact_id' => array('name'=>'group_artifact_id', 'type' => 'xsd:integer'),
	'category_name' => array('name'=>'category_name', 'type' => 'xsd:string'),
	'auto_assign_to' => array('name'=>'auto_assign_to', 'type' => 'xsd:integer')));

$server->wsdl->addComplexType(
    'ArrayOfArtifactCategory',
    'complexType',
    'array',
    '',
    'SOAP-ENC:Array',
    array(),
    array(array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'tns:ArtifactCategory[]')),
    'tns:ArtifactCategory');

$server->register(
    'getArtifactCategories',
    array('session_ser'=>'xsd:string','group_id'=>'xsd:integer','group_artifact_id'=>'xsd:integer'),
    array('getArtifactCategoriesResponse'=>'tns:ArrayOfArtifactCategory'),
    $uri);

//
//	ArtifactGroup
//
$server->wsdl->addComplexType(
	'ArtifactGroup',
	'complexType',
	'struct',
	'sequence',
	'',
	array(
	'id' => array('name'=>'id', 'type' => 'xsd:integer'),
	'group_artifact_id' => array('name'=>'group_artifact_id', 'type' => 'xsd:integer'),
	'group_name' => array('name'=>'group_name', 'type' => 'xsd:string')));

$server->wsdl->addComplexType(
    'ArrayOfArtifactGroup',
    'complexType',
    'array',
    '',
    'SOAP-ENC:Array',
    array(),
    array(array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'tns:ArtifactGroup[]')),
    'tns:ArtifactGroup');

$server->register(
    'getArtifactGroups',
    array('session_ser'=>'xsd:string','group_id'=>'xsd:integer','group_artifact_id'=>'xsd:integer'),
    array('getArtifactGroupsResponse'=>'tns:ArrayOfArtifactGroup'),
    $uri);

//
//	ArtifactResolution
//
$server->wsdl->addComplexType(
	'ArtifactResolution',
	'complexType',
	'struct',
	'sequence',
	'',
	array(
	'id' => array('name'=>'id', 'type' => 'xsd:integer'),
	'group_artifact_id' => array('name'=>'group_artifact_id', 'type' => 'xsd:integer'),
	'resolution_name' => array('name'=>'resolution_name', 'type' => 'xsd:string')));

$server->wsdl->addComplexType(
    'ArrayOfArtifactResolution',
    'complexType',
    'array',
    '',
    'SOAP-ENC:Array',
    array(),
    array(array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'tns:ArtifactResolution[]')),
    'tns:ArtifactResolution');

$server->register(
    'getArtifactResolutions',
    array('session_ser'=>'xsd:string','group_id'=>'xsd:integer','group_artifact_id'=>'xsd:integer'),
    array('getArtifactResolutionsResponse'=>'tns:ArrayOfArtifactResolution'),
    $uri);

//
//	ArtifactFile
//
$server->wsdl->addComplexType(
	'ArtifactFile',
	'complexType',
	'struct',
	'sequence',
	'',
	array(
	'id' => array('name'=>'id', 'type' => 'xsd:integer'),
	'artifact_id' => array('name'=>'artifact_id', 'type' => 'xsd:integer'),
	'description' => array('name'=>'description', 'type' => 'xsd:string'),
	'filesize' => array('name'=>'filesize', 'type' => 'xsd:integer'),
	'filetype' => array('name'=>'filetype', 'type' => 'xsd:string'),
	'adddate' => array('name'=>'adddate', 'type' => 'xsd:integer'),
	'submitted_by' => array('name'=>'submitted_by', 'type' => 'xsd:integer')));

$server->wsdl->addComplexType(
    'ArrayOfArtifactFile',
    'complexType',
    'array',
    '',
    'SOAP-ENC:Array',
    array(),
    array(array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'tns:ArtifactFile[]')),
    'tns:ArtifactFile');

$server->register(
    'getArtifactFiles',
    array('session_ser'=>'xsd:string','group_id'=>'xsd:integer','group_artifact_id'=>'xsd:integer','artifact_id'=>'xsd:integer'),
    array('getArtifactFilesResponse'=>'tns:ArrayOfArtifactFile'),
    $uri);

//TODO - FINISH ADD FILE
$server->register(
    'addArtifactFile',
    array('session_ser'=>'xsd:string','group_id'=>'xsd:integer','group_artifact_id'=>'xsd:integer','artifact_id'=>'xsd:integer','file_path'=>'xsd:string','description'=>'xsd:string','filename'=>'xsd:string','filetype'=>'xsd:string'),
    array('addArtifactFileResponse'=>'xsd:integer'),
    $uri);


//
//	ArtifactMessage
//
$server->wsdl->addComplexType(
	'ArtifactMessage',
	'complexType',
	'struct',
	'sequence',
	'',
	array(
	'id' => array('name'=>'id', 'type' => 'xsd:integer'),
	'artifact_id' => array('name'=>'artifact_id', 'type' => 'xsd:integer'),
	'body' => array('name'=>'body', 'type' => 'xsd:string'),
	'adddate' => array('name'=>'adddate', 'type' => 'xsd:integer'),
	'user_id' => array('name'=>'user_id', 'type' => 'xsd:integer')));

$server->wsdl->addComplexType(
    'ArrayOfArtifactMessage',
    'complexType',
    'array',
    '',
    'SOAP-ENC:Array',
    array(),
    array(array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'tns:ArtifactMessage[]')),
    'tns:ArtifactMessage');

$server->register(
    'getArtifactMessages',
    array('session_ser'=>'xsd:string','group_id'=>'xsd:integer','group_artifact_id'=>'xsd:integer','artifact_id'=>'xsd:integer'),
    array('getArtifactMessagesResponse'=>'tns:ArrayOfArtifactMessage'),
    $uri);

//add
$server->register(
    'addArtifactMessage',
    array('session_ser'=>'xsd:string','group_id'=>'xsd:integer','group_artifact_id'=>'xsd:integer','artifact_id'=>'xsd:integer','body'=>'xsd:string'),
    array('addArtifactMessageResponse'=>'xsd:integer'),
    $uri);

//
//	ArtifactTechnician
//
//	Array of Users
//
$server->register(
    'getArtifactTechnicians',
    array('session_ser'=>'xsd:string','group_id'=>'xsd:integer','group_artifact_id'=>'xsd:integer'),
    array('getArtifactTechniciansResponse'=>'tns:ArrayOfUser'),
    $uri);



//
//	getArtifactTypes
//
function &getArtifactTypes($session_ser,$group_id) {
	continue_session($session_ser);
	$grp =& group_get_object($group_id);
	if (!$grp || !is_object($grp) {
		return new soap_fault ('','getArtifactTypes','Could Not Get Group','Could Not Get Group');
	} elseif ($grp->isError()) {
		return new soap_fault ('','getArtifactTypes','$grp->getErrorMessage()',$grp->getErrorMessage());
	}

	$atf = new ArtifactTypeFactory($grp);
	if (!$atf || !is_object($atf) {
		return new soap_fault ('','getArtifactTypes','Could Not Get ArtifactTypeFactory','Could Not Get ArtifactTypeFactory');
	} elseif ($atf->isError()) {
		return new soap_fault ('','getArtifactTypes',$atf->getErrorMessage(),$atf->getErrorMessage());
	}

	return $atf->getArtifactTypes();
}

//
//	addArtifact
//
function &addArtifact($session_ser,$group_id,$group_artifact_id,$status_id,$category_id,
	$artifact_group_id,$resolution_id,$priority,$assigned_to,$summary,$details) {
	continue_session($session_ser);
	$grp =& group_get_object($group_id);
	if (!$grp || !is_object($grp) {
		return new soap_fault ('','addArtifact','Could Not Get Group','Could Not Get Group');
	} elseif ($grp->isError()) {
		return new soap_fault ('','addArtifact',$grp->getErrorMessage(),$grp->getErrorMessage());
	}

	$at = new ArtifactType($grp,$group_artifact_id);
	if (!$at || !is_object($at) {
		return new soap_fault ('','addArtifact','Could Not Get ArtifactType','Could Not Get ArtifactType');
	} elseif ($at->isError()) {
		return new soap_fault ('','addArtifact',$at->getErrorMessage(),$at->getErrorMessage());
	}

	$a = new Artifact($at);
	if (!$a || !is_object($a) {
		return new soap_fault ('','addArtifact','Could Not Get Artifact','Could Not Get Artifact');
	} elseif ($a->isError()) {
		return new soap_fault ('','addArtifact','$a->getErrorMessage()',$a->getErrorMessage());
	}

	if (!$a->create($category_id,$artifact_group_id,$summary,$details,$assigned_to,$priority) {
		return new soap_fault ('','addArtifact',$a->getErrorMessage(),$a->getErrorMessage());
	} else {
		soapval('xsd:integer', 'integer', $a->getID());
	}
}

//
//	getArtifactCategories
//
function &getArtifactCategories($session_ser,$group_id,$group_artifact_id) {
	continue_session($session_ser);
	$grp =& group_get_object($group_id);
	if (!$grp || !is_object($grp) {
		return new soap_fault ('','getArtifactCategories','Could Not Get Group','Could Not Get Group');
	} elseif ($grp->isError()) {
		return new soap_fault ('','getArtifactCategories',$grp->getErrorMessage(),$grp->getErrorMessage());
	}

	$at = new ArtifactType($grp,$group_artifact_id);
	if (!$at || !is_object($at) {
		return new soap_fault ('','getArtifactCategories','Could Not Get ArtifactType','Could Not Get ArtifactType');
	} elseif ($at->isError()) {
		return new soap_fault ('','getArtifactCategories',$at->getErrorMessage(),$at->getErrorMessage());
	}

	return $at->getCategoryObjects();
}

//
//	getArtifactGroups
//
function &getArtifactGroups($session_ser,$group_id,$group_artifact_id) {
	continue_session($session_ser);
	$grp =& group_get_object($group_id);
	if (!$grp || !is_object($grp) {
		return new soap_fault ('','getArtifactGroups','Could Not Get Group','Could Not Get Group');
	} elseif ($grp->isError()) {
		return new soap_fault ('','getArtifactGroups',$grp->getErrorMessage(),$grp->getErrorMessage());
	}

	$at = new ArtifactType($grp,$group_artifact_id);
	if (!$at || !is_object($at) {
		return new soap_fault ('','getArtifactGroups','Could Not Get ArtifactType','Could Not Get ArtifactType');
	} elseif ($at->isError()) {
		return new soap_fault ('','getArtifactGroups',$at->getErrorMessage(),$at->getErrorMessage());
	}

	return $at->getGroupObjects();
}

//
//	getArtifactResolutions
//
function &getArtifactResolutions($session_ser,$group_id,$group_artifact_id) {
	continue_session($session_ser);
	$grp =& group_get_object($group_id);
	if (!$grp || !is_object($grp) {
		return new soap_fault ('','getArtifactResolutions','Could Not Get Group','Could Not Get Group');
	} elseif ($grp->isError()) {
		return new soap_fault ('','getArtifactResolutions',$grp->getErrorMessage(),$grp->getErrorMessage());
	}

	$at = new ArtifactType($grp,$group_artifact_id);
	if (!$at || !is_object($at) {
		return new soap_fault ('','getArtifactResolutions','Could Not Get ArtifactType','Could Not Get ArtifactType');
	} elseif ($at->isError()) {
		return new soap_fault ('','getArtifactResolutions',$at->getErrorMessage(),$at->getErrorMessage());
	}

	return $at->getResolutionObjects();
}

//
//	getArtifactTechnicians
//
function &getArtifactTechnicians($session_ser,$group_id,$group_artifact_id) {
	continue_session($session_ser);
	$grp =& group_get_object($group_id);
	if (!$grp || !is_object($grp) {
		return new soap_fault ('','getArtifactTechnicians','Could Not Get Group','Could Not Get Group');
	} elseif ($grp->isError()) {
		return new soap_fault ('','getArtifactTechnicians',$grp->getErrorMessage(),$grp->getErrorMessage());
	}

	$at = new ArtifactType($grp,$group_artifact_id);
	if (!$at || !is_object($at) {
		return new soap_fault ('','getArtifactTechnicians','Could Not Get ArtifactType','Could Not Get ArtifactType');
	} elseif ($at->isError()) {
		return new soap_fault ('','getArtifactTechnicians',$at->getErrorMessage(),$at->getErrorMessage());
	}

	return $at->getTechnicianObjects();
}

//
//	getArtifactFiles
//
function &getArtifactFiles($session_ser,$group_id,$group_artifact_id,$artifact_id) {
	continue_session($session_ser);
	$grp =& group_get_object($group_id);
	if (!$grp || !is_object($grp) {
		return new soap_fault ('','getArtifactFiles','Could Not Get Group','Could Not Get Group');
	} elseif ($grp->isError()) {
		return new soap_fault ('','getArtifactFiles',$grp->getErrorMessage(),$grp->getErrorMessage());
	}

	$at = new ArtifactType($grp,$group_artifact_id);
	if (!$at || !is_object($at) {
		return new soap_fault ('','getArtifactFiles','Could Not Get ArtifactType','Could Not Get ArtifactType');
	} elseif ($at->isError()) {
		return new soap_fault ('','getArtifactFiles',$at->getErrorMessage(),$at->getErrorMessage());
	}

	$a = new Artifact($at,$artifact_id);
	if (!$a || !is_object($a) {
		return new soap_fault ('','getArtifactFiles','Could Not Get Artifact','Could Not Get Artifact');
	} elseif ($a->isError()) {
		return new soap_fault ('','getArtifactFiles',$a->getErrorMessage(),$a->getErrorMessage());
	}

	return $a->getFiles();
}

//
//	addArtifactFile
// 
/*
function &addArtifactFile($session_ser,$group_id,$group_artifact_id,$artifact_id) {
	continue_session($session_ser);
	$grp =& group_get_object($group_id);
	if (!$grp || !is_object($grp) {
		return new soap_fault ('','addArtifactFile','Could Not Get Group','Could Not Get Group');
	} elseif ($grp->isError()) {
		return new soap_fault ('','addArtifactFile',$grp->getErrorMessage(),$grp->getErrorMessage());
	}

	$at = new ArtifactType($grp,$group_artifact_id);
	if (!$at || !is_object($at) {
		return new soap_fault ('','addArtifactFile','Could Not Get ArtifactType','Could Not Get ArtifactType');
	} elseif ($at->isError()) {
		return new soap_fault ('','addArtifactFile',$at->getErrorMessage(),$at->getErrorMessage());
	}

	$a = new Artifact($at,$artifact_id);
	if (!$a || !is_object($a) {
		return new soap_fault ('','addArtifactFile','Could Not Get Artifact','Could Not Get Artifact');
	} elseif ($a->isError()) {
		return new soap_fault ('','addArtifactFile',$a->getErrorMessage(),$a->getErrorMessage());
	}

	return $a->getFiles();
}
*/
//
//
//	getArtifactMessages
//
function &getArtifactMessages($session_ser,$group_id,$group_artifact_id,$artifact_id) {
	continue_session($session_ser);
	$grp =& group_get_object($group_id);
	if (!$grp || !is_object($grp) {
		return new soap_fault ('','getArtifactMessages','Could Not Get Group','Could Not Get Group');
	} elseif ($grp->isError()) {
		return new soap_fault ('','getArtifactMessages',$grp->getErrorMessage(),$grp->getErrorMessage());
	}

	$at = new ArtifactType($grp,$group_artifact_id);
	if (!$at || !is_object($at) {
		return new soap_fault ('','getArtifactMessages','Could Not Get ArtifactType','Could Not Get ArtifactType');
	} elseif ($at->isError()) {
		return new soap_fault ('','getArtifactMessages',$at->getErrorMessage(),$at->getErrorMessage());
	}

	$a = new Artifact($at,$artifact_id);
	if (!$a || !is_object($a) {
		return new soap_fault ('','getArtifactMessages','Could Not Get Artifact','Could Not Get Artifact');
	} elseif ($a->isError()) {
		return new soap_fault ('','getArtifactMessages',$a->getErrorMessage(),$a->getErrorMessage());
	}

	return $a->getMessageObjects();
}

//
//	addArtifactMessage
//
function &addArtifactMessage($session_ser,$group_id,$group_artifact_id,$artifact_id,$body) {
	continue_session($session_ser);
	$grp =& group_get_object($group_id);
	if (!$grp || !is_object($grp) {
		return new soap_fault ('','addArtifactMessage','Could Not Get Group','Could Not Get Group');
	} elseif ($grp->isError()) {
		return new soap_fault ('','addArtifactMessage',$grp->getErrorMessage(),$grp->getErrorMessage());
	}

	$at = new ArtifactType($grp,$group_artifact_id);
	if (!$at || !is_object($at) {
		return new soap_fault ('','addArtifactMessage','Could Not Get ArtifactType','Could Not Get ArtifactType');
	} elseif ($at->isError()) {
		return new soap_fault ('','addArtifactMessage',$at->getErrorMessage(),$at->getErrorMessage());
	}

	$a = new Artifact($at,$artifact_id);
	if (!$a || !is_object($a) {
		return new soap_fault ('','addArtifactMessage','Could Not Get Artifact','Could Not Get Artifact');
	} elseif ($a->isError()) {
		return new soap_fault ('','addArtifactMessage',$a->getErrorMessage(),$a->getErrorMessage());
	}

	$am = new ArtifactMessage($a);
	if (!$am || !is_object($am) {
		return new soap_fault ('','addArtifactMessage','Could Not Get ArtifactMessage','Could Not Get ArtifactMessage');
	} elseif ($am->isError()) {
		return new soap_fault ('','addArtifactMessage',$am->getErrorMessage(),$am->getErrorMessage());
	}

	if (!$am->create($body) {
		return new soap_fault ('','addArtifactMessage',$am->getErrorMessage(),$am->getErrorMessage());
	} else {
		return new soap_value ('xsd:integer','integer',$am->getID());
	}
}

?>
