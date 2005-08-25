<?php
/**
 * SOAP Documentation Include - this file contains wrapper functions for the SOAP interface
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
require_once('common/docman/Document.class');
require_once('common/docman/DocumentFactory.class');
require_once('common/docman/DocumentGroup.class');
require_once('common/docman/DocumentGroupFactory.class');




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
	'groupnamename' => array('name'=>'groupnamename', 'type' => 'xsd:string')
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
	'language_id' => array('name'=>'language_id', 'type' => 'xsd:int')
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
		'filetype'=>'xsd:string'		
	),
	array('addDocumentResponse'=>'xsd:int'),
	$uri,$uri.'#addDocument','rpc','encoded'
);

function &addDocument($session_ser,$group_id,$doc_group,$title,$description,$language_id, $base64_contents,$filename,$file_url) {
	continue_session($session_ser);

	$g =& group_get_object($group_id);
	if (!$g || !is_object($g)) {
		return new soap_fault ('','addDocument','Could Not Get Group','Could Not Get Group');
	} elseif ($g->isError()) {
		return new soap_fault ('','addDocument',$g->getErrorMessage(),$g->getErrorMessage());
	}

	$d = new Document($g);
	if (!$d || !is_object($d)) {
		return new soap_fault ('','addDocument','Could Not create Document','Could Not create Document');
	} elseif ($d->isError()) {
		return new soap_fault ('','addDocument',$d->getErrorMessage(),$d->getErrorMessage());
	}

	if ($base64_contents) {		
		$bin_data = base64_decode($base64_contents);
		$data = addslashes($bin_data);
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
	array('addDocumentGroupResponse'=>'xsd:boolean'),
	$uri,$uri.'#addDocumentGroup','rpc','encoded'
);

//
// addDocumentGroup
//
function &addDocumentGroup($session_ser,$group_id,$groupname,$parent_doc_group) {
	continue_session($session_ser);

	$g =& group_get_object($group_id);
	if (!$g || !is_object($g)) {
		return new soap_fault ('','addDocumentGroup','Could Not Get Group','Could Not Get Group');
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
	$g =& group_get_object($group_id);
	if (!$g || !is_object($g)) {
		return new soap_fault ('','getDocuments','Could Not Get Group','Could Not Get Group');
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
				'language_id'=>$d_arr[$i]->data_array['language_id']
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

function &getDocumentGroups($session_ser,$group_id) {
	continue_session($session_ser);
	$g =& group_get_object($group_id);
	if (!$g || !is_object($g)) {
		return new soap_fault ('','getDocumentGroups','Could Not Get Group','Could Not Get Group');
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
		
	for ($i=0; $i<count($dg_arr); $i++) {
		if ($dg_arr[$i]->isError()) {
				$return[]=array(
				'doc_group_id'=>2,
				'parent_doc_group'=>2,
				'groupnamename'=>'FOO');
		} else {	
			$return[]=array(
				'doc_group_id'=>$dg_arr[$i]->getID(),
				'parent_doc_group'=>$dg_arr[$i]->getParentID(),
				'groupnamename'=>$dg_arr[$i]->getName()
			);
		}
	}
	return $return;
}






///
/// getDocumentFiles
///


$server->register(
	'getDocumentFiles',
	array(
		'session_ser'=>'xsd:string',
		'group_id'=>'xsd:int',
		'doc_group'=>'xsd:int',
		'doc_id'=>'xsd:int'
		),
	array('getDocumentFilesResponse'=>'tns:ArrayOfDocumentFile'),
	$uri,$uri.'#getDocuments','rpc','encoded');

///
/// getDocumentFiles
///
function &getDocumentFiles($session_ser,$group_id,$doc_group_id,$doc_id) {
	continue_session($session_ser);
	$g =& group_get_object($group_id);
	if (!$g || !is_object($g)) {
		return new soap_fault ('','GetDocumentFiles','Could Not Get Group','Could Not Get Group');
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
	
	$g =& group_get_object($group_id);
	if (!$g || !is_object($g)) {
		return new soap_fault ('','documentDelete','Could Not Get Group','Could Not Get Group');
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
