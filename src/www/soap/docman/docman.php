<?php
/**
 * SOAP Documentation Include - this file contains wrapper functions for the SOAP interface
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
require_once $gfcommon.'docman/Document.class.php';
require_once $gfcommon.'docman/DocumentFactory.class.php';
require_once $gfcommon.'docman/DocumentGroup.class.php';
require_once $gfcommon.'docman/DocumentGroupFactory.class.php';




//
//	DocumentGroup
//
$server->wsdl->addComplexType(
	'DocumentGroup',
	'complexType',
	'struct',
	'sequence',
	'',
	array(
	'doc_group_id' => array('name'=>'doc_group_id', 'type' => 'xsd:int'),
	'parent_doc_group' => array('name'=>'parent_doc_group', 'type' => 'xsd:int'),
	'groupname' => array('name'=>'groupname', 'type' => 'xsd:string')
	)
);

//
// DocumentGroup Array
//

$server->wsdl->addComplexType(
	'ArrayOfDocumentGroup',
	'complexType',
	'array',
	'',
	'SOAP-ENC:Array',
	array(),
	array(array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'tns:DocumentGroup[]')),
	'tns:DocumentGroup');



//
//	Documents
//
$server->wsdl->addComplexType(
	'Document',
	'complexType',
	'struct',
	'sequence',
	'',
	array(
	'docid' => array('name'=>'docid', 'type' => 'xsd:int'),
	'doc_group' => array('name'=>'doc_group', 'type' => 'xsd:int'),
	'title' => array('name'=>'title', 'type' => 'xsd:string'),
	'description' => array('name'=>'description', 'type' => 'xsd:string'),
	'stateid' => array('name'=>'stateid', 'type' => 'xsd:int'),
	'language_id' => array('name'=>'language_id', 'type' => 'xsd:int'),
	'filesize' => array('name'=>'filesize', 'type' => 'xsd:int')
	)
);

//
// Document Array
//

$server->wsdl->addComplexType(
	'ArrayOfDocument',
	'complexType',
	'array',
	'',
	'SOAP-ENC:Array',
	array(),
	array(array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'tns:Document[]')),
	'tns:Document');

//
// DocumentFile
//
$server->wsdl->addComplexType(
	'DocumentFile',
	'complexType',
	'struct',
	'sequence',
	'',
	array(
	'filename' => array('name'=>'filename', 'type' => 'xsd:string'),
	'filetype' => array('name'=>'filetype', 'type' => 'xsd:string'),
	'data' => array('name'=>'data', 'type' => 'xsd:string')
	)
);

//
// DocumentFiles Array
//

$server->wsdl->addComplexType(
	'ArrayOfDocumentFile',
	'complexType',
	'array',
	'',
	'SOAP-ENC:Array',
	array(),
	array(array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'tns:DocumentFile[]')),
	'tns:DocumentFile');



//
// DocumentState
//
$server->wsdl->addComplexType(
	'DocumentState',
	'complexType',
	'struct',
	'sequence',
	'',
	array(
	'state_id' => array('name'=>'state_id', 'type' => 'xsd:int'),
	'description' => array('name'=>'description', 'type' => 'xsd:string')
	)
);

//
// DocumentState Array
//

$server->wsdl->addComplexType(
	'ArrayOfDocumentState',
	'complexType',
	'array',
	'',
	'SOAP-ENC:Array',
	array(),
	array(array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'tns:DocumentState[]')),
	'tns:DocumentState');



//
//getDocumentStates
//
$server->register(
	'getDocumentStates',
	array(
		'session_ser'=>'xsd:string'
		),
	array('getDocumentStatesResponse'=>'tns:ArrayOfDocumentState'),
	$uri,$uri.'#getDocumentStates','rpc','encoded');
//
//getDocumentStates
//
function &getDocumentStates($session_ser) {
	continue_session($session_ser);
	$return = array();

	$states = db_query_params ('select * from doc_states',
			array ());
	for ($row=0; $row<db_numrows($states); $row++) {
			$return[]=array(
				'state_id'=>db_result($states,$row,'stateid'),
				'description'=>db_result($states,$row,'name')
			);
		}
	return $return;
}

//
// validateState is used to validate that the state_id that is provided is valid.
//

function validateState($state_id){
	$res = db_query_params ('SELECT name FROM doc_states WHERE stateid=$1',
			array ($state_id));
	if(db_numrows($res)==1){
		return true;
	}else{
		return false;
	}
}



//
// DocumentLanguage
//
$server->wsdl->addComplexType(
	'DocumentLanguage',
	'complexType',
	'struct',
	'sequence',
	'',
	array(
	'language_id' => array('name'=>'language_id', 'type' => 'xsd:int'),
	'description' => array('name'=>'description', 'type' => 'xsd:string')

	)
);

//
// DocumentLanguage Array
//

$server->wsdl->addComplexType(
	'ArrayOfDocumentLanguage',
	'complexType',
	'array',
	'',
	'SOAP-ENC:Array',
	array(),
	array(array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'tns:DocumentLanguage[]')),
	'tns:DocumentLanguage');

//
//getDocumentLanguages
//
$server->register(
	'getDocumentLanguages',
	array(
		'session_ser'=>'xsd:string'
		),
	array('getDocumentLanguagesResponse'=>'tns:ArrayOfDocumentLanguage'),
	$uri,$uri.'#getDocumentLanguages','rpc','encoded');


//
//getDocumentLanguages
//
function &getDocumentLanguages($session_ser) {
	continue_session($session_ser);
	$return = array();

	$languages = db_query_params ('select language_id, classname from supported_languages',
			array ());
	for ($row=0; $row<db_numrows($languages); $row++) {
			$return[]=array(
				'language_id'=>db_result($languages,$row,'language_id'),
				'description'=>db_result($languages,$row,'classname')
			);
		}
	return $return;
}

//
// validateLanguage is used to validate that the language_id that is provided is valid.
//

function validateLanguage($language_id){
	$res = db_query_params ('SELECT classname FROM supported_languages WHERE language_id=$1',
			array ($language_id));
	if(db_numrows($res)==1){
		return true;
	}else{
		return false;
	}
}

//
//addDocument
//
$server->register(
	'addDocument',
	array(
		'session_ser'=>'xsd:string',
		'group_id'=>'xsd:int',
		'doc_group'=>'xsd:int',
		'title'=>'xsd:string',
		'description'=>'xsd:string',
		'language_id'=>'xsd:int',
		'base64_contents'=>'xsd:string',
		'filename'=>'xsd:string',
		'file_url'=>'xsd:string'
	),
	array('addDocumentResponse'=>'xsd:int'),
	$uri,$uri.'#addDocument','rpc','encoded'
);

function &addDocument($session_ser,$group_id,$doc_group,$title,$description,$language_id, $base64_contents,$filename,$file_url) {
	continue_session($session_ser);

	$g = group_get_object($group_id);
	if (!$g || !is_object($g)) {
		return new soap_fault ('','addDocument','Could Not Get Project','Could Not Get Project');
	} elseif ($g->isError()) {
		return new soap_fault ('','addDocument',$g->getErrorMessage(),$g->getErrorMessage());
	}

	$d = new Document($g);
	if (!$d || !is_object($d)) {
		return new soap_fault ('','addDocument','Could Not create Document','Could Not create Document');
	} elseif ($d->isError()) {
		return new soap_fault ('','addDocument',$d->getErrorMessage(),$d->getErrorMessage());
	}

	if(!validateLanguage($language_id)){
		return new soap_fault ('','addDocument','Invalid Language ID','Invalid Language ID');
	}

	if ($base64_contents) {
		$data = base64_decode($base64_contents);
		$file_url='';
		$uploaded_data_name=$filename;
	} elseif ($file_url) {
		$data = '';
		$uploaded_data_name=$file_url;
		$uploaded_data_type='URL';
	}

	if (!$d->create($uploaded_data_name,$uploaded_data_type,$data,$doc_group,$title,$language_id,$description)) {
		return new soap_fault ('','addDocument',$d->getErrorMessage(),$d->getErrorMessage());
	} else {
		return $d->getID();
	}

}

//
//updateDocument
//
$server->register(
	'updateDocument',
	array(
		'session_ser'=>'xsd:string',
		'group_id'=>'xsd:int',
		'doc_group'=>'xsd:int',
		'doc_id'=>'xsd:int',
		'title'=>'xsd:string',
		'description'=>'xsd:string',
		'language_id'=>'xsd:int',
		'base64_contents'=>'xsd:string',
		'filename'=>'xsd:string',
		'file_url'=>'xsd:string',
		'state_id'=>'xsd:int'
	),
	array('updateDocumentResponse'=>'xsd:boolean'),
	$uri,$uri.'#updateDocument','rpc','encoded'
);

//
//updateDocument
//
function &updateDocument($session_ser,$group_id,$doc_group,$doc_id,$title,$description,$language_id, $base64_contents,$filename,$file_url,$state_id) {
	continue_session($session_ser);

	$g = group_get_object($group_id);
	if (!$g || !is_object($g)) {
		return new soap_fault ('','updateDocument','Could Not Get Project','Could Not Get Project');
	} elseif ($g->isError()) {
		return new soap_fault ('','updateDocument',$g->getErrorMessage(),$g->getErrorMessage());
	}

	$d = new Document($g,$doc_id);
	if (!$d || !is_object($d)) {
		return new soap_fault ('','updateDocument','Could Not create Document','Could Not create Document');
	} elseif ($d->isError()) {
		return new soap_fault ('','updateDocument',$d->getErrorMessage(),$d->getErrorMessage());
	}


	if(($language_id)){
		if(!validateLanguage($language_id)){
			return new soap_fault ('','updateDocument','Invalid Language ID','Invalid Language ID');
		}
	}else{
		$language_id=$d->getLanguageID();
	}

	if($state_id){
		if(!validateState($state_id)){
			return new soap_fault ('','updateDocument','Invalid State ID','Invalid State ID');
		}
	}else{
		$state_id=$d->getStateID();
	}

	if(!$title){
		$title=$d->getName();
	}

	if(!$description){
		$description=$d->getDescription();
	}


	if((!$base64_contents) && (!$file_url)){
		if((!$base64_contents) && (!$d->isURL())){
			$data = $d->getFileData();
			$uploaded_data_name=$d->getFileName();
			$file_url='';
		}else{
			if((!$file_url) && ($d->isURL())){

				$data='';
				$uploaded_data_name=$d->getFileName();
				$uploaded_data_type='URL';
			}
		}
	}elseif($file_url){
		$data='';
		$uploaded_data_name=$file_url;
		$uploaded_data_type='URL';
	}elseif($base64_contents){
		$data = base64_decode($base64_contents);
		$file_url='';
		$uploaded_data_name=$filename;
	}


	if (!$d->update($uploaded_data_name,$uploaded_data_type,$data,$doc_group,$title,$language_id,$description,$state_id)) {
		return new soap_fault ('','updateDocument',$d->getErrorMessage(),$d->getErrorMessage());
	} else {
		return true;
	}

}




//
//addDocumentGroup
//
$server->register(
	'addDocumentGroup',
	array(
		'session_ser'=>'xsd:string',
		'group_id'=>'xsd:int',
		'groupname'=>'xsd:string',
		'parent_doc_group'=>'xsd:int'
	),
	array('addDocumentGroupResponse'=>'xsd:int'),
	$uri,$uri.'#addDocumentGroup','rpc','encoded'
);

//
// addDocumentGroup
//
function &addDocumentGroup($session_ser,$group_id,$groupname,$parent_doc_group) {
	continue_session($session_ser);

	$g = group_get_object($group_id);
	if (!$g || !is_object($g)) {
		return new soap_fault ('','addDocumentGroup','Could Not Get Project','Could Not Get Project');
	} elseif ($g->isError()) {
		return new soap_fault ('','addDocumentGroup',$g->getErrorMessage(),$g->getErrorMessage());
	}

	$dg = new DocumentGroup($g);
	if (!$dg || !is_object($dg)) {
		return new soap_fault ('','addDocumentGroup','Could Not get Document Group','Could Not Get Document Group');
	}elseif ($dg->isError()) {
		return new soap_fault ('','addDocumentGroup',$dg->getErrorMessage(),$dg->getErrorMessage());
	}
	if (!$dg->create($groupname, $parent_doc_group)) {
		return new soap_fault ('','addDocumentGroup','Could Not Create Document Group','Could Not Create Document Group');
		}else {
		return $dg->getID();
	}
}


//
//updateDocumentGroup
//
$server->register(
	'updateDocumentGroup',
	array(
		'session_ser'=>'xsd:string',
		'group_id'=>'xsd:int',
		'doc_group'=>'xsd:int',
		'new_groupname'=>'xsd:string',
		'new_parent_doc_group'=>'xsd:int'
	),
	array('updateDocumentGroupResponse'=>'xsd:boolean'),
	$uri,$uri.'#updateDocumentGroup','rpc','encoded'
);

//
// updateDocumentGroup
//
function &updateDocumentGroup($session_ser, $group_id, $doc_group, $new_groupname, $new_parent_doc_group) {
	continue_session($session_ser);

	$g = group_get_object($group_id);
	if (!$g || !is_object($g)) {
		return new soap_fault ('','updateDocumentGroup','Could Not Get Project','Could Not Get Project');
	} elseif ($g->isError()) {
		return new soap_fault ('','updateDocumentGroup',$g->getErrorMessage(),$g->getErrorMessage());
	}

	$dg = new DocumentGroup($g,$doc_group);
	if (!$dg || !is_object($dg)) {
		return new soap_fault ('','updateDocumentGroup','Could Not get Document Group','Could Not Get Document Group');
	}elseif ($dg->isError()) {
		return new soap_fault ('','updateDocumentGroup',$dg->getErrorMessage(),$dg->getErrorMessage());
	}

	if (!$dg->update($new_groupname, $new_parent_doc_group)) {
		return new soap_fault ('','updateDocumentGroup',$dg->getErrorMessage(),$dg->getErrorMessage());
		}else {
		return true;
	}
}




//
//getDocuments
//
$server->register(
	'getDocuments',
	array(
		'session_ser'=>'xsd:string',
		'group_id'=>'xsd:int',
		'doc_group'=>'xsd:int'
		),
	array('getDocumentsResponse'=>'tns:ArrayOfDocument'),
	$uri,$uri.'#getDocuments','rpc','encoded');

//
//	getDocuments
//

function &getDocuments($session_ser,$group_id,$doc_group_id) {
	continue_session($session_ser);
	$g = group_get_object($group_id);
	if (!$g || !is_object($g)) {
		return new soap_fault ('','getDocuments','Could Not Get Project','Could Not Get Project');
	} elseif ($g->isError()) {
		return new soap_fault ('','getDocuments',$g->getErrorMessage(),$g->getErrorMessage());
	}


	$df = new DocumentFactory($g);
	if (!$df || !is_object($df)) {
		return new soap_fault ('','getDocuments','Could Not Get Document Factory','Could Not Get Document Factory');
	} elseif ($df->isError()) {
		return new soap_fault ('','getDocuments',$df->getErrorMessage(),$df->getErrorMessage());
	}

	$df->setDocGroupID($doc_group_id);

	return documents_to_soap($df->getDocuments());

}


//
//	convert array of documents to soap data structure
//
function documents_to_soap($d_arr) {
	$return = array();
	for ($i=0; $i<count($d_arr); $i++) {
		if ($d_arr[$i]->isError()) {
			//skip if error
		} else {

	//***********
	// Retrieving the documents details


			if(count($d_arr[$i]) < 1) { continue; }

			$return[]=array(
				'docid'=>$d_arr[$i]->data_array['docid'],
				'doc_group'=>$d_arr[$i]->data_array['doc_group'],
				'title'=>$d_arr[$i]->data_array['title'],
				'description'=>$d_arr[$i]->data_array['description'],
				'stateid'=>$d_arr[$i]->data_array['stateid'],
				'language_id'=>$d_arr[$i]->data_array['language_id'],
				'filesize'=>$d_arr[$i]->data_array['filesize']
			);
		}
	}
	return $return;
}

//
//getDocumentGroups
//
$server->register(
	'getDocumentGroups',
	array(
		'session_ser'=>'xsd:string',
		'group_id'=>'xsd:int'
		),
	array('getDocumentGroupsResponse'=>'tns:ArrayOfDocumentGroup'),
	$uri,$uri.'#getDocumentGroups','rpc','encoded');

//
//getDocumentGroups
//
function &getDocumentGroups($session_ser,$group_id) {
	continue_session($session_ser);
	$g = group_get_object($group_id);
	if (!$g || !is_object($g)) {
		return new soap_fault ('','getDocumentGroups','Could Not Get Project','Could Not Get Project');
	} elseif ($g->isError()) {
		return new soap_fault ('','getDocumentGroups',$g->getErrorMessage(),$g->getErrorMessage());
	}


	$dgf = new DocumentGroupFactory($g);
	if (!$dgf || !is_object($dgf)) {
		return new soap_fault ('','getDocumentGroups','Could Not Get Document Group Factory','Could Not Get Document Group Factory');
	} elseif ($dgf->isError()) {
		return new soap_fault ('','getDocumentGroups',$dgf->getErrorMessage(),$dgf->getErrorMessage());
	}

	return documentsGroup_to_soap($dgf->getDocumentGroups());
}


//
//	convert array of document group to soap data structure
//
function documentsGroup_to_soap($dg_arr) {
	$return = array();
	if (is_array($dg_arr) && count($dg_arr) > 0) {
		for ($i=0; $i<count($dg_arr); $i++) {
			if ($dg_arr[$i]->isError()) {
					//skip if error
			} else {
				$return[]=array(
					'doc_group_id'=>$dg_arr[$i]->getID(),
					'parent_doc_group'=>$dg_arr[$i]->getParentID(),
					'groupname'=>$dg_arr[$i]->getName()
				);
			}
		}
	}
	return $return;
}






//
//getDocumentGroup
//
$server->register(
	'getDocumentGroup',
	array(
		'session_ser'=>'xsd:string',
		'group_id'=>'xsd:int',
		'doc_group'=>'xsd:int'
		),
	array('getDocumentGroupResponse'=>'tns:DocumentGroup'),
	$uri,$uri.'#getDocumentGroup','rpc','encoded');

//
//getDocumentGroup
//
function &getDocumentGroup($session_ser,$group_id,$doc_group) {
	continue_session($session_ser);
	$g = group_get_object($group_id);
	if (!$g || !is_object($g)) {
		return new soap_fault ('','getDocumentGroup','Could Not Get Project','Could Not Get Project');
	} elseif ($g->isError()) {
		return new soap_fault ('','getDocumentGroup',$g->getErrorMessage(),$g->getErrorMessage());
	}


	$dg = new DocumentGroup($g,$doc_group);
	if (!$dg || !is_object($dg)) {
		return new soap_fault ('','getDocumentGroup','Could Not Get Document Group Factory','Could Not Get Document Group Factory');
	} elseif ($dg->isError()) {
		return new soap_fault ('','getDocumentGroup',$dg->getErrorMessage(),$dg->getErrorMessage());
	}


	$documentGroup=array('doc_group_id'=>$dg->getID(),
									'parent_doc_group'=>$dg->getParentID(),
									'groupname'=>$dg->getName());


	return $documentGroup;
}



///
/// getDocumentFiles
///


$server->register(
	'getDocumentFiles',
	array(
		'session_ser'=>'xsd:string',
		'group_id'=>'xsd:int',
		'doc_id'=>'xsd:int'
		),
	array('getDocumentFilesResponse'=>'tns:ArrayOfDocumentFile'),
	$uri,$uri.'#getDocuments','rpc','encoded');

///
/// getDocumentFiles
///
function &getDocumentFiles($session_ser,$group_id,$doc_id) {
	continue_session($session_ser);
	$g = group_get_object($group_id);
	if (!$g || !is_object($g)) {
		return new soap_fault ('','GetDocumentFiles','Could Not Get Project','Could Not Get Project');
	} elseif ($g->isError()) {
		return new soap_fault ('','GetDocumentFiles',$g->getErrorMessage(),$g->getErrorMessage());
	}

	$d=new Document($g,$doc_id);
	if (!$d || !is_object($d)) {
		return new soap_fault ('','GetDocumentFiles','Could Not Get Document','Could Not Get Document');
	} elseif ($d->isError()) {
		return new soap_fault ('','GetDocumentFiles',$d->getErrorMessage(),$d->getErrorMessage());
	}

	$return = (documentfiles_to_soap($d));

	return $return;
}


//
// convert array of document files to soap data structure
//
function documentfiles_to_soap($document) {
			$return = array();

			$return[]=array(
			'filename'=>$document->getFileName(),
			'filetype'=>$document->getFileType(),
			'data'=>base64_encode($document->getFileData())
			);
	return $return;
}


//
//	Document Delete
//
$server->register(
	'documentDelete',
	array('session_ser'=>'xsd:string','group_id'=>'xsd:int','doc_id'=>'xsd:int'),
	array('documentDeleteResponse'=>'xsd:boolean'),
	$uri,$uri.'#documentDeleteResponse','rpc','encoded'
);

function documentDelete($session_ser,$group_id,$doc_id) {
	continue_session($session_ser);

	$g = group_get_object($group_id);
	if (!$g || !is_object($g)) {
		return new soap_fault ('','documentDelete','Could Not Get Project','Could Not Get Project');
	} elseif ($g->isError()) {
		return new soap_fault ('','documentDelete',$g->getErrorMessage(),$g->getErrorMessage());
	}

	$d= new Document($g,$doc_id);
	if (!$d || !is_object($d)) {
		return new soap_fault ('','documentDelete','Could Not Get Document','Could Not Get Document');
	} elseif ($d->isError()) {
		return new soap_fault ('','documentDelete',$d->getErrorMessage(),$d->getErrorMessage());
	}

	if (!$d->delete()) {
		return new soap_fault ('','documentDelete',$d->getErrorMessage(),$d->getErrorMessage());
	} else {
		return true;
	}
}

?>
