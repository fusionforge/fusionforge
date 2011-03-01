<?php
require_once('../env.inc.php');
require_once $gfwww.'include/pre.php';
require_once($gfcommon.'tracker/ArtifactType.class.php');
require_once($gfcommon.'tracker/Artifact.class.php');
require_once($gfcommon.'include/User.class.php');
require_once($gfcommon.'tracker/ArtifactExtraField.class.php');
require_once($gfcommon.'tracker/ArtifactFile.class.php');
//require_once($gfcommon.'import/import_arrays.php');

define('TRACKER_IS_PUBLIC', 1);
define('TRACKER_ALLOW_ANON', 0);
define('TRACKER_BUGS', 1);
define('TRACKER_SUPPORT', 2);
define('TRACKER_PATCHES', 3);
define('TRACKER_FEATURES', 4);

static $NOT_EXTRA_FIELDS = array('assigned_to', 'attachments', 'class', 'comments', 'date', 'history', 'priority', 'status_id', 'submitter', 'summary', 'closed_at', 'description', 'type', 'type_of_search', 'id');//last 3 should not be there at all.

/**
 * findType - get the type of a field from its name, value, and vocabulary : default 0 (text box), otherwise 1 (select box) or 2 (multi choice field)
 * @param string	Name of the field
 * @param string|array	Value of the field
 * @param $vocabulary	Vocabulary of a tracker
 */
function findType($fieldName, $fieldValue, $vocabulary){
	if(is_array($fieldValue)){
		return ARTIFACT_EXTRAFIELDTYPE_MULTISELECT;
	}
	elseif(array_key_exists($fieldName, $vocabulary)){

		return ARTIFACT_EXTRAFIELDTYPE_SELECT;
	}
	else {
		return ARTIFACT_EXTRAFIELDTYPE_TEXT;
	}
	return true;
}

/**
 * createFieldElements - Add elements (choices) to an extra field
 * @param ArtifactExtraField	The artifact extra field where the choices should be added
 * @param array	The choices to be declared for the specified extra field
 * @return false if failed
 */
function createFieldElements($aef, $vocabulary){
	//TODO:Add each element to tracker extra field
	foreach($vocabulary as $element){
		if(!($element=='None')){
			$aefe = new ArtifactExtraFieldElement($aef);
			if (!$aefe->create(addslashes($element))) {
				db_rollback();
				return false;
			}
		}
	}
	return true;
}

/**
 * createFields - Create the custom fields for the specified ArtifactType
 * @param ArtifactType	The artifact type to be modified
 * @param array	Vocabulary and artifacts for the ArtifactType
 * @return false if failed
 */
function createFields($at, $data){
	global $NOT_EXTRA_FIELDS;
//new dBug($data);
	//TODO:Create ExtraFields	
	//include $GLOBALS['gfcommon'].'import/import_arrays.php';
	$artifactToCheck = $data["artifacts"][0];
	foreach($artifactToCheck as $fieldName => $fieldValue){
		if (!in_array($fieldName, $NOT_EXTRA_FIELDS)){
			$type = findType($fieldName, $fieldValue, $data["vocabulary"]);
			$aef = new ArtifactExtraField($at);
			
			$defaultExtraFieldsSettings = array(0,0,0);
			$defaultTextFieldsSettings = array(40,100,0);
			
			if($type==ARTIFACT_EXTRAFIELDTYPE_TEXT){
				$extraFieldSettings = $defaultTextFieldsSettings;				
			}
			else{
				$extraFieldSettings = $defaultExtraFieldsSettings;
			}
			if (!$aef->create(addslashes($fieldName), $type, $extraFieldSettings[0], $extraFieldSettings[1], $extraFieldSettings[2])) {
				db_rollback();
				return false;

			} else {
				if(!($type==ARTIFACT_EXTRAFIELDTYPE_TEXT)){
					createFieldElements($aef, $data['vocabulary'][$fieldName]);
				}
			}
		}
	}
	return true;
}

/**
 * createTracker - Create a specific tracker from data in the specified group
 * @param string Tracker type (bugs, support, ...)
 * @param Group	The group which the tracker belongs to
 * @param array	Tracker data from JSON
 * @return ArtifactType	the tracker created
 */

function createTracker($tracker, $group, $data){
	//	Create a tracker
	db_begin();
	$at = new ArtifactType($group);
	if (!$at || !is_object($at)) {
		db_rollback();
		return false;
	}
	//include $GLOBALS['gfcommon'].'import/import_arrays.php';
	
	$base_tracker_association = array( 'bugs' => TRACKER_BUGS, 'support' => TRACKER_SUPPORT, 'patches' => TRACKER_PATCHES, 'features' => TRACKER_FEATURES );
	if(array_key_exists($tracker, $base_tracker_association)){
		$valueType = $base_tracker_association[$tracker];
	} else {
		$valueType = 0;
	}
	
	$is_public = TRACKER_IS_PUBLIC;
	$allow_anon = TRACKER_ALLOW_ANON;
	$email_all = '';
	$email_address = '';
	$due_period = 30;
	$use_resolution = 0;
	$submit_instructions = 0;
	$use_resolution = 0;
	
	if (!$at->create($data["label"], $data["label"], $is_public, $allow_anon, $email_all, $email_address, $due_period, $use_resolution, $submit_instructions, $use_resolution, $valueType)) {
		db_rollback();
		return false;
	} else {
		//	Create each field in the tracker
		createFields($at, $data);
	}
	db_commit();
	return $at;
}

/**
 * deleteTrackers - Delete all existing default trackers from a projet
 * @param Group A Group object
 */
function deleteTrackers($group){
	$res = db_query_params ('SELECT group_artifact_id FROM artifact_group_list 
			WHERE group_id=$1 AND datatype > 0',
					array ($group->getID()));
	while($row=db_fetch_array($res)){
		$at = & artifactType_get_object($row['group_artifact_id']);
		$at->delete(true,true);
		//print $at->getID();
	}
	
}

/**
 * addComments - Add followup comments to an Artifact Object
 * @param Artifact	the artifact object where history should be added
 * @param array the artifact's data in json format (an array)
 */
function addComments($artifact, $jsonArtifact){
	foreach($jsonArtifact['comments'] as $c){
		$time = strtotime($c['date']);
		$uid =&user_get_object_by_name($c['submitter'])->getID();
		$importData = array('time' => $time, 'user' => $uid);
		$artifact->addMessage($c['comment'],false,false, $importData);
	}
}

/**
 * addHistory - Add history of changes to an Artifact Object
 * @param Artifact	the artifact object where history should be added
 * @param array the artifact's data in json format (an array)
 */
function addHistory($artifact, $jsonArtifact){
	foreach($jsonArtifact['history'] as $h){
		$time = strtotime($h['date']);
		$uid =&user_get_object_by_name($h['by'])->getID();
		$importData = array('time' => $time, 'user' => $uid);
//hack!!
		$old = $h['old'];
		if($h['field']=='assigned_to'){
			if($old!='none'){
				$old =&user_get_object_by_name($old)->getID();
			} else {
				$old = 100;
			}
		}
		if($h['field']=='status_id'){
			$status = array('Open' =>1, 'Closed' => 2, 'Deleted' => 3);
			$old = $status[$old];
		}
		if($h['field']=='close_date'){
			$old = strtotime($old);
		}
//end hack
		$artifact->addHistory($h['field'],$old, $importData);
	}
}


function addFiles($artifact, $jsonArtifact){
	foreach($jsonArtifact['attachments'] as $a){
		
		$path = '/tmp/'.$a['url'];
		if (is_file($path)){
			$af = new ArtifactFile($artifact);
			$fn = $a['filename'];
			//$bin_data = 0;//load bin data from $a['url']?
			
			$bin_data = file_get_contents($path); 
			
			$fs = filesize($path);
			
			
			$finfo = new finfo(FILEINFO_MIME, "/usr/share/misc/magic"); // Retourne le type mime
			if (!$finfo) {
	   			echo "error opening fileinfo";
	    		exit();
			}
			
			$ftype = $finfo->file($path);
			$time = strtotime($a['date']);
			$uid =&user_get_object_by_name($a['by'])->getID();
			$importData = array('user' => $uid, 'time' => $time);
			
			//we have no descriptions for files => None
			$af->create($fn,$ftype,$fs,$bin_data,'None',$importData);
		}		
	}	
}


/**
 * createArtifacts - Create all the artifacts for an ArtifactType from an array of data.
 * @param ArtifactType The ArtifactType object which the artifacts to be added belong to.
 * @param array	The data of all the artifacts of the current Type (dictionary)
 * @param $hashrn
 * @param $hashlogin
 */
function createArtifacts($at, $data, $hashrn, $hashlogin) {
	global $NOT_EXTRA_FIELDS;
	
	$name_id = array();
	//include $GLOBALS['gfcommon'].'import/import_arrays.php';
	$extra_fields_ids = $at->getExtraFields();


	foreach($extra_fields_ids as $extraField => $val){
	$extras[$val[2]] = $extraField;
	}

	foreach($extras as $fieldName => $fieldId){
		$elements = $at->getExtraFieldElements($fieldId);
		foreach($elements as $extra_element){
			$extra_elements[$fieldId][$extra_element['element_name']]=$extra_element['element_id'];
		}
	}
	//new dBug($extra_elements);
	//$arti = new Artifact($at);
	//$artifactTypeExtraFields = $arti->getExtraFieldDataText();
	//unset($arti);

	foreach ($data as $artifact){
		$arti = new Artifact($at);
		$extra_fields_array = array();
		foreach($artifact as $fieldName => $fieldValue){
			if(!in_array($fieldName, $NOT_EXTRA_FIELDS)){
//new dBug(array($fieldName,$fieldValue));
				if(is_array($fieldValue)){
					$mf = array();
					foreach($fieldValue as $multiFieldValue){
						$mf[] = $extra_elements[$extras[$fieldName]][$multiFieldValue];
					}
					$extra_fields_array[$extras[$fieldName]] = $mf;
				} else {
					$extra_fields_array[$extras[$fieldName]] = $extra_elements[$extras[$fieldName]][$fieldValue];
				}
			}
		}
		//create the artif here with $extra_fields_array as extra fields
		//new dBug($extra_fields_array);
		//get user id
		$uid =&user_get_object_by_name($artifact['submitter'])->getID();
		//TODO:Search in hash table for corresponding mail for id, lookup object by mail, get ID
		//get time from epoch
		$timestamp = strtotime($artifact['date']);
		//TODO:get the id of the assigned_to user, in the meantime -> Nobody (100)


		//assigned to : real name dans le pluck
		if($artifact['assigned_to']=='Nobody'){
			$assigned_to = 100;
		} else {
			$m = $hashrn[$artifact['assigned_to']];
			$assigned_to =&user_get_object_by_mail($m)->getID(); 
//new dBug(array($m,$assigned_to));
		}
		
		$arti->create($artifact['summary'],$artifact['description'],$assigned_to,substr($artifact['priority'],0,1),$extra_fields_array,array('user' => $uid, 'time' => $timestamp));
		//TODO:pass only relevant JSON info		
		addComments($arti, $artifact);
		addHistory($arti, $artifact);
		addFiles($arti, $artifact);

		if(array_key_exists('closed_at', $artifact)){
			
			$timestamp_closed = strtotime($artifact['closed_at']);

			$arti->setStatus(2, $timestamp_closed);			
		}
	}
}
/**
 * tracker_fill - Create trackers from an array in a given group
 * @param array Trackers part of a JSON pluck, including label, artifacts, vocabulary...
 * @param int	Group id of the group where the trackers should be added
 */
function tracker_fill($trackers, $group_id, $users){

	$group =& group_get_object($group_id);
	if (!$group || !is_object($group)) {
		print "error retrieving group from id";
	} else if ($group->isError()) {
		print "error";
	}

	//create hash table hashrn{real_name:mail} & hashlogin{id:mail}
	foreach($users as $user => $infos){
		$hashrn[$infos['real_name']] = $infos['mail'];
		$hashlogin[$user] = $infos['mail'];
	}

	//existing tracker deletion
	deleteTrackers($group);
	
	//Tracker creation
	foreach ($trackers as $data){	
		

		$at = createTracker($data['type'], $group, $data);
		createArtifacts($at, $data['artifacts'], $hashrn, $hashlogin);

	}

}

?>
