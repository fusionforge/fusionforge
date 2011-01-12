<?php
/**
 * SOAP Tracker Include - this file contains wrapper functions for the tracker query interface
 *
 * Copyright 2004 (c) GForge, LLC
 * http://gforge.org
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

require_once $gfcommon.'include/Error.class.php';
require_once $gfcommon.'tracker/ArtifactQuery.class.php';
require_once $gfcommon.'tracker/ArtifactQueryFactory.class.php';
// imports ArrayOfArtifactExtraFieldsData type
require_once $gfwww.'soap/tracker/tracker.php';


/**
 * artifactGetViews
 */
$server->wsdl->addComplexType(
	'ArtifactQueryExtraField',
	'complexType',
	'struct',
	'sequence',
	'',
	array(
		'extra_field_id' => array('name' => 'extra_field_id', 'type' => 'xsd:int'),
		'values' => array('name' => 'values', 'type' => 'tns:ArrayOfint')
	)
);

$server->wsdl->addComplexType(
	'ArrayOfArtifactQueryExtraField',
	'complexType',
	'array',
	'',
	'SOAP-ENC:Array',
	array(),
	array(
		array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'tns:ArtifactQueryExtraField[]')
	),
	'tns:ArtifactQueryExtraField'
);


$server->wsdl->addComplexType(
	'ArtifactQueryFields',
	'complexType',
	'struct',
	'sequence',
	'',
	array(
		'sortcol' => array('name' => 'sortcol', 'type' => 'xsd:string'),
		'sortord' => array('name' => 'sortord', 'type' => 'xsd:string'),
		'moddaterange' => array('name' => 'changed', 'type' => 'xsd:string'),
		'assignee' => array('name' => 'assignee', 'type' => 'tns:ArrayOfInteger'),
		'status' => array('name' => 'status', 'type' => 'xsd:int'),
		'extra_fields' => array('name' => 'extra_fields', 'type' => 'tns:ArrayOfArtifactQueryExtraField'),
		'opendaterange' => array('name' => 'changed', 'type' => 'xsd:string'),
		'closedaterange' => array('name' => 'changed', 'type' => 'xsd:string')
	)
);


$server->wsdl->addComplexType(
	'ArtifactQuery',
	'complexType',
	'struct',
	'sequence',
	'',
	array(
		'artifact_query_id' => array('name' => 'artifact_query_id', 'type' => 'xsd:int'),
		'name' => array('name' => 'query_name', 'type' => 'xsd:string'),
		'fields' => array('name' => 'fields', 'type' => 'tns:ArtifactQueryFields')
	)
);

$server->wsdl->addComplexType(
	'ArrayOfArtifactQuery',
	'complexType',
	'array',
	'',
	'SOAP-ENC:Array',
	array(),
	array(
		array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'tns:ArtifactQuery[]')
	),
	'tns:ArtifactQuery'
);

$server->register(
	'artifactGetViews',
	array(
		'session_ser'=>'xsd:string',
		'group_id'=>'xsd:int',
		'group_artifact_id'=>'xsd:int'
	),
	array('getArtifactTypesResponse'=>'tns:ArrayOfArtifactQuery'),
	$uri,
	$uri.'#artifactGetViews','rpc','encoded'
);

function artifactGetViews($session_ser, $group_id, $group_artifact_id) {
	continue_session($session_ser);
	$grp =& group_get_object($group_id);
	if (!$grp || !is_object($grp)) {
		return new soap_fault ('','artifactGetViews','Could Not Get Group','Could Not Get Group');
	} elseif ($grp->isError()) {
		return new soap_fault ('','artifactGetViews',$grp->getErrorMessage(),$grp->getErrorMessage());
	}
	
	$at = new ArtifactType($grp,$group_artifact_id);
	if (!$at || !is_object($at)) {
		return new soap_fault ('','artifactGetViews','Could Not Get ArtifactType','Could Not Get ArtifactType');
	} elseif ($at->isError()) {
		return new soap_fault ('','artifactGetViews',$at->getErrorMessage(),$at->getErrorMessage());
	}
	
	$aqf = new ArtifactQueryFactory($at);
	
	return queries_to_soap($aqf->getArtifactQueries());
}

function queries_to_soap($queries) {
	$result = array();
//	print_r($queries[1]->getExtraFields());
//	die();

	if (is_array($queries) && count($queries) > 0) {
		for ($i=0; $i < count($queries); $i++) {
			$artifactQuery =& $queries[$i];

			// transform the extra fields data
			$extra_fields = array();
			$queryExtraFields = $artifactQuery->getExtraFields();
			foreach ($queryExtraFields as $extra_field_id => $values) {
				// $value may be a int. We wrap it in an array.
				if (!is_array($values)) $values = array($values);
				$extra_fields[] = array(
									"extra_field_id"	=> $extra_field_id,
									"values"			=> $values
									);
			}

			$assignee = $artifactQuery->getAssignee();
			// this is a hack, ArtifactQuery::getAssignee sometimes returns an int and
			// sometimes it returns an array
			if (!is_array($assignee)) {
				if (is_numeric($assignee)) {	// a single ID
					$assignee = array($assignee);	// wrap in an array
				} else {
					$assignee = array();
				}
			}

			$result[] = array(
						"artifact_query_id"	=> $artifactQuery->getID(),
						"name"		=> $artifactQuery->getName(),
						"fields"	=> array(
										"sortcol"	=> $artifactQuery->getSortCol(),
										"sortord"	=> $artifactQuery->getSortOrd(),
										"moddaterange"	=> $artifactQuery->getModDateRange(),
										"assignee"	=> $assignee,
										"status"	=> $artifactQuery->getStatus(),
										"extra_fields"	=> $extra_fields,
										"opendaterange"	=> $artifactQuery->getOpenDateRange(),
										"closedaterange"	=> $artifactQuery->getCloseDateRange()
										),
						);
		}
	}

	return $result;
}


/**
 * artifactDeleteView
 */
$server->register(
	'artifactDeleteView',
	array(
		'session_ser'=>'xsd:string',
		'group_id'=>'xsd:int',
		'group_artifact_id'=>'xsd:int',
		'artifact_query_id' => 'xsd:int'
	),
	array('artifactDeleteViewResponse'=>'xsd:boolean'),
	$uri,
	$uri.'#artifactDeleteView','rpc','encoded'
);

function artifactDeleteView($session_ser, $group_id, $group_artifact_id, $artifact_query_id) {
	continue_session($session_ser);
	$grp =& group_get_object($group_id);
	if (!$grp || !is_object($grp)) {
		return new soap_fault ('','artifactDeleteView','Could Not Get Group','Could Not Get Group');
	} elseif ($grp->isError()) {
		return new soap_fault ('','artifactDeleteView',$grp->getErrorMessage(),$grp->getErrorMessage());
	}
	
	$at = new ArtifactType($grp,$group_artifact_id);
	if (!$at || !is_object($at)) {
		return new soap_fault ('','artifactDeleteView','Could Not Get ArtifactType','Could Not Get ArtifactType');
	} elseif ($at->isError()) {
		return new soap_fault ('','artifactDeleteView',$at->getErrorMessage(),$at->getErrorMessage());
	}
	
	$query = new ArtifactQuery($at, $artifact_query_id);
	if (!$query || !is_object($query)) {
		return new soap_fault ('','artifactDeleteView','Could Not Get Query','Could Not Get Query');
	} elseif ($query->isError()) {
		return new soap_fault ('','artifactDeleteView',$query->getErrorMessage(),$query->getErrorMessage());
	}
	
	$query->delete();
	return true;
}

/**
 * artifactSetView
 */
$server->register(
	'artifactSetView',
	array(
		'session_ser'=>'xsd:string',
		'group_id'=>'xsd:int',
		'group_artifact_id'=>'xsd:int',
		'artifact_query_id' => 'xsd:int'
	),
	array('artifactSetView'=>'xsd:boolean'),
	$uri,
	$uri.'#artifactSetView','rpc','encoded'
);

function artifactSetView($session_ser, $group_id, $group_artifact_id, $artifact_query_id) {
	continue_session($session_ser);
	$grp =& group_get_object($group_id);
	if (!$grp || !is_object($grp)) {
		return new soap_fault ('','artifactSetView','Could Not Get Group','Could Not Get Group');
	} elseif ($grp->isError()) {
		return new soap_fault ('','artifactSetView',$grp->getErrorMessage(),$grp->getErrorMessage());
	}
	
	$at = new ArtifactType($grp,$group_artifact_id);
	if (!$at || !is_object($at)) {
		return new soap_fault ('','artifactSetView','Could Not Get ArtifactType','Could Not Get ArtifactType');
	} elseif ($at->isError()) {
		return new soap_fault ('','artifactSetView',$at->getErrorMessage(),$at->getErrorMessage());
	}
	
	$query = new ArtifactQuery($at, $artifact_query_id);
	if (!$query || !is_object($query)) {
		return new soap_fault ('','artifactDeleteView','Could Not Get Query','Could Not Get Query');
	} elseif ($query->isError()) {
		return new soap_fault ('','artifactDeleteView',$query->getErrorMessage(),$query->getErrorMessage());
	}
	
	$query->makeDefault();
	return true;
}

/**
 * artifactCreateView
 */
$server->wsdl->addComplexType(
	'ArrayOfUserID',
	'complexType',
	'array',
	'',
	'SOAP-ENC:Array',
	array(),
	array(
		array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'tns:ArrayOfint')
	),
	'xsd:int'
); 
 
$server->register(
	'artifactCreateView',
	array(
		'session_ser'=>'xsd:string',
		'group_id'=>'xsd:int',
		'group_artifact_id'=>'xsd:int',
		'name'=>'xsd:string',
		'status'=>'xsd:int',
		'assignee'=>'tns:ArrayOfUserID',
		'moddaterange'=>'xsd:string',
		'sort_col'=>'xsd:string',
		'sort_ord'=>'xsd:string',
		'extra_fields'=>'tns:ArrayOfArtifactExtraFieldsData',
		'opendaterange'=>'xsd:string',
		'closedaterange'=>'xsd:string'
	),
	array('artifactCreateViewResponse'=>'xsd:int'),
	$uri,
	$uri.'#artifactCreateView','rpc','encoded'
);
function artifactCreateView($session_ser, $group_id, $group_artifact_id, $name, $status, $assignee, $moddaterange,
	$sort_col, $sort_ord, $extra_fields, $opendaterange, $closedaterange) {
		
	continue_session($session_ser);
	$grp =& group_get_object($group_id);
	if (!$grp || !is_object($grp)) {
		return new soap_fault ('','artifactCreateView','Could Not Get Group','Could Not Get Group');
	} elseif ($grp->isError()) {
		return new soap_fault ('','artifactCreateView',$grp->getErrorMessage(),$grp->getErrorMessage());
	}
	
	$at = new ArtifactType($grp,$group_artifact_id);
	if (!$at || !is_object($at)) {
		return new soap_fault ('','artifactCreateView','Could Not Get ArtifactType','Could Not Get ArtifactType');
	} elseif ($at->isError()) {
		return new soap_fault ('','artifactCreateView',$at->getErrorMessage(),$at->getErrorMessage());
	}
	
	//rearrange the extra fields
	$aef = $at->getExtraFields();
	$extra_fields = arrangeExtraFields($extra_fields, $aef);
	
	$query = new ArtifactQuery($at);
	if (!$query->create($name, $status, $assignee, $moddaterange, $sort_col, 
		$sort_ord, $extra_fields, $opendaterange, $closedaterange)) {
		return new soap_fault ('','artifactCreateView',$query->getErrorMessage(),$query->getErrorMessage());
	}
	
	return $query->getID();
}

/**
 * artifactUpdateView
 */
 
$server->register(
	'artifactUpdateView',
	array(
		'session_ser'=>'xsd:string',
		'group_id'=>'xsd:int',
		'group_artifact_id'=>'xsd:int',
		'query_id'=>'xsd:int',
		'name'=>'xsd:string',
		'status'=>'xsd:int',
		'assignee'=>'tns:ArrayOfUserID',
		'moddaterange'=>'xsd:string',
		'sort_col'=>'xsd:string',
		'sort_ord'=>'xsd:string',
		'extra_fields'=>'tns:ArrayOfArtifactExtraFieldsData',
		'opendaterange'=>'xsd:string',
		'closedaterange'=>'xsd:string'
	),
	array('artifactUpdateViewResponse'=>'xsd:int'),
	$uri,
	$uri.'#artifactUpdateView','rpc','encoded'
);

function artifactUpdateView($session_ser, $group_id, $group_artifact_id, $query_id, $name, $status, $assignee, $moddaterange,
	$sort_col, $sort_ord, $extra_fields, $opendaterange, $closedaterange) {
		
	continue_session($session_ser);
	$grp =& group_get_object($group_id);
	if (!$grp || !is_object($grp)) {
		return new soap_fault ('','artifactUpdateView','Could Not Get Group','Could Not Get Group');
	} elseif ($grp->isError()) {
		return new soap_fault ('','artifactCreateView',$grp->getErrorMessage(),$grp->getErrorMessage());
	}
	
	$at = new ArtifactType($grp,$group_artifact_id);
	if (!$at || !is_object($at)) {
		return new soap_fault ('','artifactUpdateView','Could Not Get ArtifactType','Could Not Get ArtifactType');
	} elseif ($at->isError()) {
		return new soap_fault ('','artifactUpdateView',$at->getErrorMessage(),$at->getErrorMessage());
	}
	
	//rearrange the extra fields
	$aef = $at->getExtraFields();
	$extra_fields = arrangeExtraFields($extra_fields, $aef);

	
	$query = new ArtifactQuery($at, $query_id);
	if (!$query || !is_object($query)) {
		return new soap_fault ('','artifactUpdateView','Could Not Get ArtifactType','Could Not Get ArtifactType');
	} elseif ($query->isError()) {
		return new soap_fault ('','artifactUpdateView',$query->getErrorMessage(),$query->getErrorMessage());
	}
	
	if (!$query->update($name, $status, $assignee, $moddaterange, $sort_col, $sort_ord, 
		$extra_fields, $opendaterange, $closedaterange)) {
		return new soap_fault ('','artifactUpdateView',$query->getErrorMessage(),$query->getErrorMessage());
	}
	
	return $query->getID();
}
?>
