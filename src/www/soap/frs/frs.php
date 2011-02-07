<?php
/**
 * SOAP File Release System Include - this file contains wrapper functions for the SOAP interface
 *
 * Copyright 2004 (c) GForge, LLC
 * http://gforge.org
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  US
 */

require_once $gfcommon.'include/Error.class.php';
require_once $gfcommon.'frs/FRSFile.class.php';
//require_once $gfcommon.'frs/FRSFileType.class.php';
//require_once $gfcommon.'frs/FRSFileProcessorType.class.php';
require_once $gfcommon.'frs/FRSPackage.class.php';
require_once $gfcommon.'frs/FRSRelease.class.php';

$server->wsdl->addComplexType(
	'FRSPackage',
	'complexType',
	'struct',
	'sequence',
	'',
	array(
	'package_id' => array('name'=>'package_id', 'type' => 'xsd:int'),
	'name' => array('name'=>'name', 'type' => 'xsd:string'),
	'status_id' => array('name'=>'description', 'type' => 'xsd:string'),
	'is_public' => array('name'=>'is_public', 'type' => 'xsd:int'),
	)
);

$server->wsdl->addComplexType(
	'ArrayOfFRSPackage',
	'complexType',
	'array',
	'',
	'SOAP-ENC:Array',
	array(),
	array(array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'tns:FRSPackage[]')),
	'tns:FRSPackage'
);

$server->register(
	'getPackages',
	array(
		'session_ser'=>'xsd:string',
		'group_id'=>'xsd:int'),
	array('getPackagesResponse'=>'tns:ArrayOfFRSPackage'),
		$uri,$uri.'#getPackages','rpc','encoded'
);

$server->wsdl->addComplexType(
	'FRSFileType',
	'complexType',
	'struct',
	'sequence',
	'',
	array(
	'type_id' => array('name'=>'type_id', 'type' => 'xsd:int'),
	'name' => array('name'=>'name', 'type' => 'xsd:string'),
	)
);
$server->wsdl->addComplexType(
	'ArrayOfFRSFileType',
	'complexType',
	'array',
	'',
	'SOAP-ENC:Array',
	array(),
	array(array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'tns:FRSFileType[]')),
	'tns:FRSFileType'
);

$server->register(
	'getFileTypes',
	array('session_ser'=>'xsd:string'),
	array('getFileTypeResponse'=>'tns:ArrayOfFRSFileType'),
		$uri,$uri.'#getFileTypes','rpc','encoded'
);

$server->wsdl->addComplexType(
	'FRSFileProcessorType',
	'complexType',
	'struct',
	'sequence',
	'',
	array(
	'processor_id' => array('name'=>'processor_id', 'type' => 'xsd:int'),
	'name' => array('name'=>'name', 'type' => 'xsd:string'),
	)
);
$server->wsdl->addComplexType(
	'ArrayOfFRSFileProcessorType',
	'complexType',
	'array',
	'',
	'SOAP-ENC:Array',
	array(),
	array(array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'tns:FRSFileProcessorType[]')),
	'tns:FRSFileProcessorType'
);
$server->register(
	'getFileProcessorTypes',
	array('session_ser'=>'xsd:string'),
	array('getFileProcessorTypeResponse'=>'tns:ArrayOfFRSFileProcessorType'),
		$uri,$uri.'#getFileProcessorTypes','rpc','encoded'
);

$server->register(
	'addPackage',
	array(
		'session_ser'=>'xsd:string',
		'group_id'=>'xsd:int',
		'package_name'=>'xsd:string',
		'is_public'=>'xsd:int'),
	array('addPackageResponse'=>'xsd:int'),
		$uri,$uri.'#addPackage','rpc','encoded'
);

$server->wsdl->addComplexType(
	'FRSRelease',
	'complexType',
	'struct',
	'sequence',
	'',
	array(
	'release_id' => array('name'=>'release_id', 'type' => 'xsd:int'),
	'name' => array('name'=>'name', 'type' => 'xsd:string'),
	'notes' => array('name'=>'notes', 'type' => 'xsd:string'),
	'changes' => array('name'=>'changes', 'type' => 'xsd:string'),
	'status_id' => array('name'=>'description', 'type' => 'xsd:string'),
	'release_date' => array('name'=>'release_date', 'type' => 'xsd:int'),
	)
);

$server->wsdl->addComplexType(
	'ArrayOfFRSRelease',
	'complexType',
	'array',
	'',
	'SOAP-ENC:Array',
	array(),
	array(array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'tns:FRSRelease[]')),
	'tns:FRSRelease'
);

$server->register(
	'getReleases',
	array(
		'session_ser'=>'xsd:string',
		'group_id'=>'xsd:int',
		'package_id'=>'xsd:int'),
	array('getPackagesResponse'=>'tns:ArrayOfFRSRelease'),
		$uri,$uri.'#getReleases','rpc','encoded'
);

$server->register(
	'addRelease',
	array(
		'session_ser'=>'xsd:string',
		'group_id'=>'xsd:int',
		'package_id'=>'xsd:int',
		'name'=>'xsd:string',
		'notes'=>'xsd:string',
		'changes'=>'xsd:string',
		'release_date'=>'xsd:int'),
	array('addRelease'=>'xsd:int'),
		$uri,$uri.'#addRelease','rpc','encoded'
);

$server->wsdl->addComplexType(
	'FRSFile',
	'complexType',
	'struct',
	'sequence',
	'',
	array(
		'file_id' => array('name'=>'file_id', 'type' => 'xsd:int'),
		'name' => array('name'=>'name', 'type' => 'xsd:string'),
		'size' => array('name'=>'size', 'type' => 'xsd:int'),
		'type' => array('name'=>'type', 'type' => 'xsd:string'),
		'processor' => array('name'=>'processor', 'type' => 'xsd:string'),
		'downloads' => array('name'=>'downloads', 'type' => 'xsd:int'),
		'release' => array('name'=>'release_time', 'type' => 'xsd:int'),
		'date' => array('name'=>'date', 'type' => 'xsd:int'),
	)
);


$server->wsdl->addComplexType(
	'ArrayOfFRSFile',
	'complexType',
	'array',
	'',
	'SOAP-ENC:Array',
	array(),
	array(array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'tns:FRSFile[]')),
	'tns:FRSFile'
);

$server->register(
	'getFiles',
	array(
		'session_ser'=>'xsd:string',
		'group_id'=>'xsd:int',
		'package_id'=>'xsd:int',
		'release_id'=>'xsd:int'),
	array('getFilesResponse'=>'tns:ArrayOfFRSFile'),
		$uri,$uri.'#getFiles','rpc','encoded'
);

$server->register(
	'getFile',
	array(
		'session_ser'=>'xsd:string',
		'group_id'=>'xsd:int',
		'package_id'=>'xsd:int',
		'release_id'=>'xsd:int',
		'file_id'=>'xsd:int'),
	array('getFileResponse'=>'xsd:string'),
		$uri,$uri.'#getFile','rpc','encoded'
);

$server->register(
	'addFile',
	array(
		'session_ser'=>'xsd:string',
		'group_id'=>'xsd:int',
		'package_id'=>'xsd:int',
		'release_id'=>'xsd:int',
		'name'=>'xsd:string',
		'base64_contents'=>'xsd:string',
		'type_id'=>'xsd:int',
		'processor_id'=>'xsd:int',
		'release_time'=>'xsd:int'
		),
	array('addFile'=>'xsd:string'),
		$uri,$uri.'#addFile','rpc','encoded'
);

$server->register(
        'addUploadedFile',
        array(
                'session_ser'=>'xsd:string',
                'group_id'=>'xsd:int',
                'package_id'=>'xsd:int',
                'release_id'=>'xsd:int',
                'file_name'=>'xsd:string',
                'type_id'=>'xsd:int',
                'processor_id'=>'xsd:int',
                'release_time'=>'xsd:int'
                ),
        array('addUploadedFile'=>'xsd:string'),
        $uri,$uri.'#addUploadedFile','rpc','encoded'
);

function getPackages($session_ser,$group_id) {
	continue_session($session_ser);
	$grp = group_get_object($group_id);
	if (!$grp || !is_object($grp)) {
		return new soap_fault ('','getPackages','Could Not Get Project','Could Not Get Project');
	} elseif ($grp->isError()) {
		return new soap_fault ('','getPackages',$grp->getErrorMessage(),$grp->getErrorMessage());
	}
	
	$pkg_arr = get_frs_packages($grp);
	return packages_to_soap($pkg_arr); 
}

function packages_to_soap(&$pkg_arr) {
	$return = array();

	if (is_array($pkg_arr) && count($pkg_arr) > 0) {
		for ($i=0; $i<count($pkg_arr); $i++) {
			if ($pkg_arr[$i]->isError()) {
				//skip if error
			} else {
				$return[]=array(
					'package_id' => $pkg_arr[$i]->getID(),
					'name' => $pkg_arr[$i]->getName(),
					'status_id' => $pkg_arr[$i]->getStatus(),
					'is_public' => $pkg_arr[$i]->isPublic()
				);
			}
		}
	}
	return $return;
}

function getFileTypes($session_ser) {
	continue_session($session_ser);
	$pkg_arr = get_frs_filetypes();
	return filetypes_to_soap($pkg_arr);
}

function getFileProcessorTypes($session_ser) {
	continue_session($session_ser);
	$pkg_arr = get_frs_fileprocessortypes();
	return fileprocessortypes_to_soap($pkg_arr);
}

function addPackage($session_ser,$group_id,$package_name,$is_public) {
	continue_session($session_ser);
	$grp = group_get_object($group_id);
	if (!$grp || !is_object($grp)) {
		return new soap_fault ('','addPackage','Could Not Get Project','Could Not Get Project');
	} elseif ($grp->isError()) {
		return new soap_fault ('','addPackage',$grp->getErrorMessage(),$grp->getErrorMessage());
	}
	
	$frsp = new FRSPackage($grp);
	if (!$frsp->create($package_name, $is_public)) {
		return new soap_fault('', 'addPackage', $frsp->getErrorMessage(), $frsp->getErrorMessage());
	} else {
		return $frsp->getID();
	}
}

function getReleases($session_ser,$group_id,$package_id) {
	continue_session($session_ser);
	$grp = group_get_object($group_id);
	if (!$grp || !is_object($grp)) {
		return new soap_fault ('','getReleases','Could Not Get Project','Could Not Get Project');
	} elseif ($grp->isError()) {
		return new soap_fault ('','getReleases',$grp->getErrorMessage(),$grp->getErrorMessage());
	}
	
	$frsp =& frspackage_get_object($package_id);
	if (!$frsp || !is_object($frsp)) {
		return new soap_fault ('','getReleases','Could Not Get Package','Could Not Get Package');
	} else if ($frsp->isError()) {
		return new soap_fault ('','getReleases',$frsp->getErrorMessage(),$frsp->getErrorMessage());
	}

	$release_arr =& $frsp->getReleases();
	
	return releases_to_soap($release_arr); 
}

function releases_to_soap(&$release_arr) {
	$return = array();

	if (is_array($release_arr) && count($release_arr) > 0) {
		for ($i=0; $i<count($release_arr); $i++) {
			if ($release_arr[$i]->isError()) {
				//skip if error
			} else {
				$return[]=array(
					'release_id' => $release_arr[$i]->getID(),
					'name' => $release_arr[$i]->getName(),
					'notes' => $release_arr[$i]->getNotes(),
					'changes' => $release_arr[$i]->getChanges(),
					'status_id' => $release_arr[$i]->getStatus(),
					'release_date' => $release_arr[$i]->getReleaseDate()
				);
			}
		}
	}
	
	return $return;
}

function addRelease($session_ser,$group_id,$package_id,$name,$notes,$changes,$release_date) {
	continue_session($session_ser);

	$grp = group_get_object($group_id);
	if (!$grp || !is_object($grp)) {
		return new soap_fault ('','getPackages','Could Not Get Project','Could Not Get Project');
	} elseif ($grp->isError()) {
		return new soap_fault ('','getPackages',$grp->getErrorMessage(),$grp->getErrorMessage());
	}
	
	$frsp =& frspackage_get_object($package_id);
	if (!$frsp || !is_object($frsp)) {
		return new soap_fault ('','getReleases','Could Not Get Package','Could Not Get Package');
	} else if ($frsp->isError()) {
		return new soap_fault ('','getReleases',$frsp->getErrorMessage(),$frsp->getErrorMessage());
	}
	
	$frsr = new FRSRelease($frsp);
	if (!$frsr->create($name,$notes,$changes,0,$release_date)) {
		return new soap_fault('', 'addRelease', $frsr->getErrorMessage(), $frsr->getErrorMessage());
	} else {
		return $frsr->getID();
	}
}

function getFiles($session_ser,$group_id,$package_id,$release_id) {
	continue_session($session_ser);
	
	$grp = group_get_object($group_id);
	if (!$grp || !is_object($grp)) {
		return new soap_fault ('','getFiles','Could Not Get Project','Could Not Get Project');
	} elseif ($grp->isError()) {
		return new soap_fault ('','getFiles',$grp->getErrorMessage(),$grp->getErrorMessage());
	}
	
	$frsp =& frspackage_get_object($package_id);
	if (!$frsp || !is_object($frsp)) {
		return new soap_fault ('','getFiles','Could Not Get Package','Could Not Get Package');
	} else if ($frsp->isError()) {
		return new soap_fault ('','getFiles',$frsp->getErrorMessage(),$frsp->getErrorMessage());
	}
	
	//TODO: Check that the release belongs to the package
	$frsr =& frsrelease_get_object($release_id);
	if (!$frsr || !is_object($frsr)) {
		return new soap_fault ('','getFiles','Could Not Get Release','Could Not Get Release');
	} else if ($frsr->isError()) {
		return new soap_fault ('','getFiles',$frsr->getErrorMessage(),$frsr->getErrorMessage());
	}
	
	$files_arr =& $frsr->getFiles();
	return files_to_soap($files_arr);
}

function files_to_soap($files_arr) {
	$return = array();

	if (is_array($files_arr) && count($files_arr) > 0) {
		for ($i=0; $i<count($files_arr); $i++) {
			if ($files_arr[$i]->isError()) {
				//skip if error
			} else {
				$return[]=array(
					'file_id' => $files_arr[$i]->getID(),
					'name' => $files_arr[$i]->getName(),
					'size' => $files_arr[$i]->getSize(),
					'type' => $files_arr[$i]->getFileType(),
					'processor' => $files_arr[$i]->getProcessor(),
					'downloads' => $files_arr[$i]->getDownloads(),
					'release' => $files_arr[$i]->getReleaseTime(),
					'date' => $files_arr[$i]->getPostDate(),
				);
			}
		}
	}
	
	return $return;
}

function filetypes_to_soap($files_arr) {
	$return = array();

	if (is_array($files_arr) && count($files_arr) > 0) {
		for ($i=0; $i<count($files_arr); $i++) {
			if ($files_arr[$i]->isError()) {
				//skip if error
			} else {
				$return[]=array(
					'type_id' => $files_arr[$i]->getID(),
					'name' => $files_arr[$i]->getName(),
				);
			}
		}
	}
	return $return;
}

function fileprocessortypes_to_soap($files_arr) {
	$return = array();

	if (is_array($files_arr) && count($files_arr) > 0) {
		for ($i=0; $i<count($files_arr); $i++) {
			if ($files_arr[$i]->isError()) {
				//skip if error
			} else {
				$return[]=array(
					'processor_id' => $files_arr[$i]->getID(),
					'name' => $files_arr[$i]->getName(),
				);
			}
		}
	}
	return $return;
}

function getFile($session_ser,$group_id,$package_id,$release_id,$file_id) {
	continue_session($session_ser);
	
	$grp = group_get_object($group_id);
	if (!$grp || !is_object($grp)) {
		return new soap_fault ('','getFile','Could Not Get Project','Could Not Get Project');
	} elseif ($grp->isError()) {
		return new soap_fault ('','getFile',$grp->getErrorMessage(),$grp->getErrorMessage());
	}
	
	$frsp =& frspackage_get_object($package_id);
	if (!$frsp || !is_object($frsp)) {
		return new soap_fault ('','getFile','Could Not Get Package','Could Not Get Package');
	} else if ($frsp->isError()) {
		return new soap_fault ('','getFile',$frsp->getErrorMessage(),$frsp->getErrorMessage());
	}
	
	$frsr =& frsrelease_get_object($release_id);
	if (!$frsr || !is_object($frsr)) {
		return new soap_fault ('','getFile','Could Not Get Release','Could Not Get Release');
	} else if ($frsr->isError()) {
		return new soap_fault ('','getFile',$frsr->getErrorMessage(),$frsr->getErrorMessage());
	}
	
	$frsf = new FRSFile($frsr, $file_id);
	if (!$frsf || !is_object($frsf)) {
		return new soap_fault ('','getFile','Could Not Get File','Could Not Get File');
	} else if ($frsf->isError()) {
		return new soap_fault ('','getFile',$frsf->getErrorMessage(),$frsf->getErrorMessage());
	}
	
	$file_location = forge_get_config('upload_dir').'/'.
				$frsf->FRSRelease->FRSPackage->Group->getUnixName().'/'.
				$frsf->FRSRelease->FRSPackage->getFileName().'/'.
				$frsf->FRSRelease->getFileName().'/'.
				$frsf->getName();
	if (!file_exists($file_location)) {
		return new soap_fault('','getFile','File doesn\'t exist in server','File doesn\'t exist in server');
	}
	
	$fh = fopen($file_location, "rb");
	$contents = fread($fh, filesize($file_location));
	fclose($fh);
	return base64_encode($contents);
}

function addFile($session_ser,$group_id,$package_id,$release_id,$name,$base64_contents,$type_id,$processor_id,$release_time) {
	continue_session($session_ser);

	$grp = group_get_object($group_id);
	if (!$grp || !is_object($grp)) {
		return new soap_fault ('','addFile','Could Not Get Project','Could Not Get Project');
	} elseif ($grp->isError()) {
		return new soap_fault ('','addFile',$grp->getErrorMessage(),$grp->getErrorMessage());
	}
	
	$frsp =& frspackage_get_object($package_id);
	if (!$frsp || !is_object($frsp)) {
		return new soap_fault ('','addFile','Could Not Get Package','Could Not Get Package');
	} else if ($frsp->isError()) {
		return new soap_fault ('','addFile',$frsp->getErrorMessage(),$frsp->getErrorMessage());
	}
	
	$frsr =& frsrelease_get_object($release_id);
	if (!$frsr || !is_object($frsr)) {
		return new soap_fault ('','addFile','Could Not Get Release','Could Not Get Release');
	} else if ($frsr->isError()) {
		return new soap_fault ('','addFile',$frsr->getErrorMessage(),$frsr->getErrorMessage());
	}
	
	$frsf = new FRSFile($frsr);
	if (!$frsf || !is_object($frsf)) {
		return new soap_fault ('','addFile','Could Not Get File','Could Not Get File');
	}
	
	$tmpname = tempnam("/tmp", "gforge_cli_frs");
	$fh = fopen($tmpname, "wb");
	if (!$fh) {
		return new soap_fault ('','addFile','Could not create temporary file in directory /tmp');
	}
	fwrite($fh, base64_decode($base64_contents));
	fclose($fh);
	
	if (!$frsf->create($name,$tmpname,$type_id,$processor_id,$release_time)) {
		@unlink($tmpname);
		return new soap_fault ('','addFile',$frsf->getErrorMessage(),$frsf->getErrorMessage());
	} else {
		@unlink($tmpname);
		return $frsf->getID();
	}
}
