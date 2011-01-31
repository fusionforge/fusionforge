<?php
/**
 * FusionForge project manager
 *
 * Copyright 1999-2000, Tim Perdue/Sourceforge
 * Copyright 2002, Tim Perdue/GForge, LLC
 * Copyright 2009, Roland Mas
 * Copyright (C) 2010 Alain Peyrat - Alcatel-Lucent
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published
 * by the Free Software Foundation; either version 2 of the License,
 * or (at your option) any later version.
 * 
 * FusionForge is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307
 * USA
 */

require_once $gfcommon.'include/Error.class.php';
require_once $gfcommon.'include/Validator.class.php';

function &projecttask_get_object($project_task_id,$data=false) {
		global $PROJECTTASK_OBJ;
		if (!isset($PROJECTTASK_OBJ["_".$project_task_id."_"])) {
			if ($data) {
				//the db result handle was passed in
			} else {
				$res = db_query_params ('SELECT * FROM project_task_vw WHERE project_task_id=$1',
							array ($project_task_id)) ;

				if (db_numrows($res) <1 ) {
					$PROJECTTASK_OBJ["_".$project_task_id."_"]=false;
					return false;
				}
				$data = db_fetch_array($res);

			}
			$ProjectGroup =& projectgroup_get_object($data["group_project_id"]);
			$PROJECTTASK_OBJ["_".$project_task_id."_"]= new ProjectTask($ProjectGroup,$project_task_id,$data);

		}

		return $PROJECTTASK_OBJ["_".$project_task_id."_"];
}

/*
	Types of task dependencies
*/
define('PM_LINK_DEFAULT','FS');
define('PM_LINK_START_START','SS');
define('PM_LINK_START_FINISH','SF');
define('PM_LINK_FINISH_START','FS');
define('PM_LINK_FINISH_FINISH','FF');

class ProjectTask extends Error {

	/**
	 * Associative array of data from db.
	 *
	 * @var	 array   $data_array.
	 */
	var $data_array;

	/**
	 * The ProjectGroup object.
	 *
	 * @var	 object  $ProjectGroup.
	 */
	var $ProjectGroup;
	var $dependon;
	var $assignedto;
	var $relatedartifacts;

	/**
	 *  Constructor.
	 *
	 *	@param	object	The ProjectGroup object to which this ProjectTask is associated.
	 *  @param  int	 The project_task_id.
	 *  @param  array   The associative array of data.
	 *	@return	boolean success.
	 */
	function ProjectTask(&$ProjectGroup, $project_task_id=false, $arr=false) {
		$this->Error();
		if (!$ProjectGroup || !is_object($ProjectGroup)) {
			$this->setError('ProjectTask:: No Valid ProjectGroup Object');
			return false;
		}
		if ($ProjectGroup->isError()) {
			$this->setError('ProjectTask:: '.$ProjectGroup->getErrorMessage());
			return false;
		}
		$this->ProjectGroup =& $ProjectGroup;

		if ($project_task_id) {
			if (!$arr || !is_array($arr)) {
				if (!$this->fetchData($project_task_id)) {
					return false;
				}
			} else {
				$this->data_array =& $arr;
				//
				//	Verify this message truly belongs to this ProjectGroup
				//
				if ($this->data_array['group_project_id'] != $this->ProjectGroup->getID()) {
					$this->setError('Group_project_id in db result does not match ProjectGroup Object');
					return false;
				}
			}
		}
		return true;
	}

	/**
	 *	create - create a new ProjectTask in the database.
	 *
	 *	@param	string	The summary of this task.
	 *	@param	string	The detailed description of this task.
	 *	@param	int	The Priority of this task.
	 *	@param	int	The Hours estimated to complete this task.
	 *	@param	int	The (unix) start date of this task.
	 *	@param	int	The (unix) end date of this task.
	 *	@param	int	The category_id of this task.
	 *	@param	int	The percentage of completion in integer format of this task.
	 *	@param	array	An array of user_id's that are assigned this task.
	 *	@param	array	An array of project_task_id's that this task depends on.
	 *	@param	int	The duration of the task in days.
	 *	@param	int	The id of the parent task, if any.
	 *	@return	boolean success.
	 */
	function create($summary,$details,$priority,$hours,$start_date,$end_date,
			$category_id,$percent_complete,&$assigned_arr,&$depend_arr,$duration=0,$parent_id=0) {
		$v = new Validator();
		$v->check($summary, "summary");
		$v->check($details, "details");
		$v->check($priority, "priority");
		$v->check($hours, "hours");
		$v->check($start_date, "start date");
		$v->check($end_date, "end date");
		$v->check($category_id, "category");
		if (!$v->isClean()) {
			$this->setError($v->formErrorMsg("Must include "));
			return false;
 		}
		if (!$parent_id) {
			$parent_id=0;
		}
		if (!$duration) {
			$duration=0;
		}
		if (!forge_check_perm ('pm', $this->ProjectGroup->getID(), 'manager')) {
			$this->setPermissionDeniedError();
			return false;
		}

		db_begin();
		$res = db_query_params ('SELECT nextval($1) AS id', 
					array ('project_task_pk_seq'));
		if (!$project_task_id=db_result($res,0,'id')) {
			$this->setError( 'Could Not Get Next Project Task ID' );
			db_rollback();
			return false;
		}
		
		$this->data_array['project_task_id']=$project_task_id;
		
		$result = db_query_params ('INSERT INTO project_task (project_task_id,group_project_id,created_by,summary,details,start_date,end_date,status_id,category_id,priority,percent_complete,hours,duration,parent_id) VALUES ($1,$2,$3,$4,$5,$6,$7,$8,$9,$10,$11,$12,$13,$14)',
					   array ($project_task_id,
						  $this->ProjectGroup->getID(),
						  user_getid(),
						  htmlspecialchars($summary),
						  htmlspecialchars($details),
						  $start_date,
						  $end_date,
						  1,
						  $category_id,
						  $priority,
						  $percent_complete,
						  $hours,
						  $duration,
						  $parent_id)) ;
		
		if (!$result || db_affected_rows($result) < 1) {
			$this->setError('ProjectTask::create() Posting Failed '.db_error());
			db_rollback();
			return false;
		}

		if (!$this->setDependentOn($depend_arr)) {
			db_rollback();
			return false;
		}
		if (!$this->setAssignedTo($assigned_arr)) {
			db_rollback();
			return false;
		}
		if (!$this->fetchData($project_task_id)) {
			db_rollback();
			return false;
		}
		$this->sendNotice(1);
		db_commit();
		return true;
	}

	/**
	 *  fetchData - re-fetch the data for this ProjectTask from the database.
	 *
	 *  @param  int	 The project_task_id.
	 *  @return	boolean	success.
	 */
	function fetchData($project_task_id) {
		$res = db_query_params ('SELECT * FROM project_task_vw
			WHERE project_task_id=$1
			AND group_project_id=$2',
					array ($project_task_id,
					       $this->ProjectGroup->getID())) ;
		if (!$res || db_numrows($res) < 1) {
			$this->setError('ProjectTask::fetchData() Invalid Task ID'.db_error());
			return false;
		}
		$this->data_array = db_fetch_array($res);
		db_free_result($res);
		return true;
	}

	/**
	 *	getProjectGroup - get the ProjectGroup object this ProjectTask is associated with.
	 *
	 *	@return	Object	The ProjectGroup object.
	 */
	function &getProjectGroup() {
		return $this->ProjectGroup;
	}

	/**
	 *	getID - get this project_task_id.
	 *
	 *	@return	int	The project_task_id.
	 */
	function getID() {
		return $this->data_array['project_task_id'];
	}

	/**
	 *	getSubmittedRealName - get the real name of the person who created this task.
	 *
	 *	@return	string	The real name person who created this task.
	 */
	function getSubmittedRealName() {
		return $this->data_array['realname'];
	}
	
	/**
	 *	getDuration - the duration of the task.
	 *
	 *	@return	int	The number of days of duration.
	 */
	function getDuration() {
		return $this->data_array['duration'];
	}
	
	/**
	 *	getParentID - the task_id of the parent task, if any.
	 *
	 *	@return	string	The real name person who created this task.
	 */
	function getParentID() {
		return $this->data_array['parent_id'];
	}
	
	/**
	 *	getSubmittedUnixName - get the unix name of the person who created this task.
	 *
	 *	@return	string	The unix name of the person who created this task.
	 */
	function getSubmittedUnixName() {
		return $this->data_array['user_name'];
	}

	/**
	 *	getSummary - get the subject/summary of this task.
	 *
	 *	@return	string	The summary.
	 */
	function getSummary() {
		return $this->data_array['summary'];
	}

	/**
	 *	getDetails - get the body/details of this task.
	 *
	 *	@return	string	The body/details.
	 */
	function getDetails() {
		return $this->data_array['details'];
	}

	/**
	 *	getPercentComplete - an integer between 0 and 100.
	 *
	 *	@return	int	The percentage of completion of this task.
	 */
	function getPercentComplete() {
		return $this->data_array['percent_complete'];
	}

	/**
	 *	getPriority - the priority, between 1 and 9 of this task.
	 *
	 *	@return	int	The priority.
	 */
	function getPriority() {
		return $this->data_array['priority'];
	}

	/**
	 *	getHours - the hours this task is expected to take.
	 *
	 *	@return	int	The hours.
	 */
	function getHours() {
		return $this->data_array['hours'];
	}

	/**
	 *	getStartDate - the unix time that this task will start.
	 *
	 *	@return	int	The unix start time of this task.
	 */
	function getStartDate() {
		return $this->data_array['start_date'];
	}

	/**
	 *	getEndDate - the unix time that this task will end.
	 *
	 *	@return	int	The unix end time of this task.
	 */
	function getEndDate() {
		return $this->data_array['end_date'];
	}

	/**
	 *	getStatusID - the integer of the status of this task.
	 *
	 *	@return	int	the status_id.
	 */
	function getStatusID() {
		return $this->data_array['status_id'];
	}

	/**
	 *	getStatusName - the string of the status of this task.
	 *
	 *	@return	string	the status_name.
	 */
	function getStatusName() {
		return $this->data_array['status_name'];
	}

	/**
	 *	getCategoryID - the category_id of this task.
	 *
	 *	@return	int	the category_id.
	 */
	function getCategoryID() {
		return $this->data_array['category_id'];
	}

	/**
	 *	getCategoryName - the category_name of this task.
	 *
	 *	@return	int	the category_name.
	 */
	function getCategoryName() {
		return $this->data_array['category_name'];
	}

	/**
	 *	getLastModifiedDate - the last_modified_date of this task.
	 *
	 *	@return	int	the last_modified_date.
	 */
	function getLastModifiedDate() {
		return $this->data_array['last_modified_date'];
	}
	
	/**
	 *	setExternalID - set a row in project_task_external_order which stores 
	 *	an id, for example an ID generated by MS Project, which needs to be restored later
	 */
	function setExternalID($id) {
		$res = db_query_params ('UPDATE project_task_external_order SET external_id=$1
			WHERE project_task_id=$2',
					array ($id,
					       $this->getID())) ;
		if (db_affected_rows($res) < 1) {
			$res = db_query_params ('INSERT INTO project_task_external_order (project_task_id,external_id) VALUES ($1, $2)', 
						array ($this->getID(),
						       $id)) ;
		}
	}

	/**
	 *	getExternalID - get the ID that MS Project uses to sort tasks
	 *
	 *	@return	int	the id.
	 */
	function getExternalID() {
		return $this->data_array['external_id'];
	}

	/**
	 *	getRelatedArtifacts - Return a result set of artifacts which are related to this task.
	 *
	 *	@returns Database result set.
	 */
	function getRelatedArtifacts() {
		if (!$this->relatedartifacts) {
			$this->relatedartifacts=
				db_query_params ('SELECT agl.group_id,agl.name,agl.group_artifact_id,a.artifact_id,a.open_date,a.summary 
			FROM artifact_group_list agl, artifact a 
			WHERE a.group_artifact_id=agl.group_artifact_id
			AND EXISTS (SELECT artifact_id FROM project_task_artifact 
				WHERE artifact_id=a.artifact_id
				AND project_task_id=$1)',
						 array ($this->getID())) ;
		}
		return $this->relatedartifacts;
	}

	/**
	 *	addRelatedArtifacts - take an array of artifact_id's and build relationships.
	 *
	 *	@param	array	An array of artifact_id's to be attached to this task.
	 *	@return	boolean	success.
	 */
	function addRelatedArtifacts($art_array) {
		if (!forge_check_perm ('pm', $this->ProjectGroup->getID(), 'manager')) {
			$this->setPermissionDeniedError();
			return false;
		}
//
//	SHOULD REALLY INSTANTIATE THIS ARTIFACT OBJECT TO ENSURE PROPER SECURITY - FUTURE
//
//	new ArtifactFromID($id)
//
		for ($i=0; $i<count($art_array); $i++) {
			if ($art_array[$i] < 1) {
				continue;
			}
			$res = db_query_params ('INSERT INTO project_task_artifact (project_task_id,artifact_id) VALUES ($1,$2)', 
						array ($this->getID(),
						       $art_array[$i])) ;
			if (!$res) {
				$this->setError('Error inserting artifact relationship: '.db_error());
				return false;
			}
		}
		return true;
	}

	/**
	 *	removeRelatedArtifacts - take an array of artifact_id's and delete relationships.
	 *
	 *	@param	array	An array of artifact_id's to be removed from this task.
	 *	@return	boolean	success.
	 */
	function removeRelatedArtifacts($art_array) {
		if (!forge_check_perm ('pm', $this->ProjectGroup->getID(), 'manager')) {
			$this->setPermissionDeniedError();
			return false;
		}

		for ($i=0; $i<count($art_array); $i++) {
			$res = db_query_params ('DELETE FROM project_task_artifact
				WHERE project_task_id=$1
				AND artifact_id=$2',
						array ($this->getID(),
						       $art_array[$i])) ;
			if (!$res) {
				$this->setError('Error deleting artifact relationship: '.db_error());
				return false;
			}
		}
		return true;
	}

	/**
	 *  delete - delete this tracker and all its related data.
	 *
	 *  @param  bool	I'm Sure.
	 *  @return bool true/false;
	 */
	function delete($sure) {
		if (!$sure) {
			$this->setMissingParamsError(_('Please tick all checkboxes.'));
			return false;
		}
		if (!forge_check_perm ('pm', $this->ProjectGroup->getID(), 'manager')) {
			$this->setPermissionDeniedError();
			return false;
		}
		db_begin();

		$res = db_query_params ('DELETE FROM project_assigned_to WHERE project_task_id=$1',
					array ($this->getID())) ;
		if (!$res) {
			$this->setError('Error deleting assigned users relationship: '.db_error());
			db_rollback();
			return false;
		}
		$res = db_query_params ('DELETE FROM project_dependencies WHERE project_task_id=$1',
					array ($this->getID())) ;
		if (!$res) {
			$this->setError('Error deleting dependencies: '.db_error());
			db_rollback();
			return false;
		}
		$res = db_query_params ('DELETE FROM project_history WHERE project_task_id=$1',
					array ($this->getID())) ;
		if (!$res) {
			$this->setError('Error deleting history: '.db_error());
			db_rollback();
			return false;
		}
		$res = db_query_params ('DELETE FROM project_messages WHERE project_task_id=$1',
					array ($this->getID())) ;
		if (!$res) {
			$this->setError('Error deleting messages: '.db_error());
			db_rollback();
			return false;
		}
		$res = db_query_params ('DELETE FROM project_task_artifact WHERE project_task_id=$1',
					array ($this->getID())) ;
		if (!$res) {
			$this->setError('Error deleting artifacts: '.db_error());
			db_rollback();
			return false;
		}
		$res = db_query_params ('DELETE FROM rep_time_tracking WHERE project_task_id=$1',
					array ($this->getID())) ;
		if (!$res) {
			$this->setError('Error deleting time tracking report: '.db_error());
			db_rollback();
			return false;
		}
		$res = db_query_params ('DELETE FROM project_task WHERE project_task_id=$1',
					array ($this->getID())) ;
		if (!$res) {
			$this->setError('Error deleting task: '.db_error());
			db_rollback();
			return false;
		}

		db_commit();
		return true;
	}

	/**
	 *	getOtherTasks - Return a result set of tasks in this subproject that do not equal
	 *	the current task_id.
	 *
	 *	@returns Database result set.
	 */
	function getOtherTasks () {
		//
		//	May not yet have an ID, if we are creating a NEW task
		//
		if ($this->getID()) {
			return db_query_params ('SELECT project_task_id,summary 
		FROM project_task 
		WHERE group_project_id=$1
		AND project_task_id <> $2
                ORDER BY project_task_id DESC',
						array ($this->ProjectGroup->getID(),
						       $this->getID())) ;
		} else {
			return db_query_params ('SELECT project_task_id,summary 
		FROM project_task 
		WHERE group_project_id=$1
		ORDER BY project_task_id DESC',
						array ($this->ProjectGroup->getID())) ;
		}
	}

	/**
	 *  getHistory - returns a result set of audit trail for this ProjectTask.
	 *
	 *  @return database result set.
	 */
	function getHistory() {
		return db_query_params ('SELECT * 
		FROM project_history_user_vw 
		WHERE project_task_id=$1
		ORDER BY mod_date DESC',
					array ($this->getID())) ;
	}

	/**
	 *  getMessages - get the list of messages attached to this ProjectTask.
	 *
	 *  @return database result set.
	 */
	function getMessages() {
		return db_query_params ('SELECT * 
		FROM project_message_user_vw 
		WHERE project_task_id=$1
		ORDER BY postdate DESC',
					array ($this->getID())) ;
	}

	/**
	 * addMessage - Handle the addition of a followup message to this task.
	 *
	 * @param	   string  The message.
	 * @returns	boolean	success.
	 */
	function addMessage($message) {
		//prevent posting the same message
		if ($this->getDetails() == htmlspecialchars($message)) {
			return true;
		}
		$res = db_query_params ('SELECT * FROM project_messages 
			WHERE project_task_id=$1
			AND body=$2',
					array ($this->getID(),
					       htmlspecialchars($message))) ;
		if (!$res || db_numrows($res) < 1) {
			$res = db_query_params ('INSERT INTO project_messages (project_task_id,body,posted_by,postdate) VALUES ($1,$2,$3,$4)',
						array ($this->getID(),
						       htmlspecialchars($message),
						       user_getid(),
						       time())) ;
			if (!$res || db_affected_rows($res) < 1) {
				$this->setError('AddMessage():: '.db_error());
				return false;
			} else {
				return true;
			}
		} else {
			return true;
		}
	}

	/**
	 * addHistory - Handle the insertion of history for these parameters.
	 *
	 * @param	string  The field name.
	 * @param	string  The old value.
	 * @returns	boolean	success.
	 */
	function addHistory ($field_name,$old_value) {
		$result = db_query_params ('INSERT INTO project_history (project_task_id,field_name,old_value,mod_by,mod_date) VALUES ($1,$2,$3,$4,$5)',
					   array ($this->getID(),
						  $field_name,
						  $old_value,
						  user_getid(),
						  time())) ;
		if (!$result) {
			$this->setError('ERROR IN AUDIT TRAIL - '.db_error());
			return false;
		} else {
			return true;
		}
	}

	/**
	 * checkCircular - recursive function calls itself to look at all tasks you are dependent on.
	 *
	 * @param	int	The project_task_id you are dependent on.
	 * @param	int	The project_task_id you are checking circular dependencies for.
	 * @returns	boolean	success.
	 */
	function checkCircular($depend_on_id, $original_id) {
		//for msproject users - ms project has more complex logic than gforge
		return true;

		if ($depend_on_id == $original_id) {
			$this->setError(_('Circular Dependency Detected\''));
	 		return false;
		}

		$res = db_query_params ('SELECT is_dependent_on_task_id AS id 
			FROM project_dependencies 
			WHERE project_task_id=$1',
					array ($depend_on_id)) ;
		$rows=db_numrows($res);

		for ($i=0; $i<$rows; $i++) {
			if (!$this->checkCircular(db_result($res,$i,'id'), $original_id)) {
				return false;
			}
		}
		return true;
	}

	/**
	 * setDependentOn - takes an array of project_task_id's and builds dependencies.
	 *
	 * @param	array	The array of project_task_id's.
	 * @returns	boolean	success.
	 */
	function setDependentOn(&$arr_) {
//printr($arr_,'setDependentOn entry');
//
//	IMPORTANT - MUST VERIFY NO CIRCULAR DEPENDENCY!! 
//
		if (!$arr_ || empty($arr_)) {
			$arr_=array('100'=>PM_LINK_DEFAULT);
		}
		$arr = array_keys($arr_);
		//get existing dependencies to diff against
		$arr2 = array_keys($this->getDependentOn());

		if (count($arr) || count($arr2)) {
			$add_arr = array_values (array_diff ($arr, $arr2));
//echo "add arr: ".print_r($add_arr);
			$del_arr = array_values (array_diff ($arr2, $arr));
//echo "del arr: ".print_r($del_arr);
			for ($i=0; $i<count($del_arr); $i++) {
				db_query_params ('DELETE FROM project_dependencies 
					WHERE project_task_id=$1
					AND is_dependent_on_task_id=$2',
						 array ($this->getID(),
							$del_arr[$i])) ;
				if (db_error()) {
					$this->setError('setDependentOn()-1:: '.db_error());
					return false;
				}
			}
			for ($i=0; $i<count($add_arr); $i++) {
				//
				//	Check task for circular dependencies
				//
				if (!$this->checkCircular($add_arr[$i],$this->getID())) {
					return false;
				}
				$lnk = $arr_[$add_arr[$i]];
				if (!$lnk) {
					$lnk=PM_LINK_DEFAULT;
				}
				db_query_params ('INSERT INTO project_dependencies (project_task_id,is_dependent_on_task_id,link_type) VALUES ($1,$2,$3)',
						 array ($this->getID(),
							$add_arr[$i],
							$lnk)) ;
				if (db_error()) {
					$this->setError('setDependentOn()-2:: '.db_error().$sql);
					return false;
				}
			}
			return true;
		} else {
			return true;
		}
	}

	/**
	 *	convertDependentOn - converts a regular array of dependencies, such 
	 *	as from a multiple-select-box to an associative array with default 
	 *	link types. Should be called from web code as part of the create/update calls.
	 *  Here we are converting an array like array(1,5,9,77) to array(1=>SS,5=>SF,9=>FS,77=>SS)
	 */
	function &convertDependentOn($arr) {
		$deps = $this->getDependentOn();
		for ($i=0; $i<count($arr); $i++) {
			if ($deps[$arr[$i]]) {
				//use existing link_type if it exists
				$new[$arr[$i]]=$deps[$arr[$i]];
			} else {
				//else create with default link type
				$new[$arr[$i]]=PM_LINK_DEFAULT;
			}	
		}
		return $new;
	}

	/**
	 *	getDependentOn - get an array of project_task_id's that you are dependent on.
	 *
	 *	@return	array	The array of project_task_id's in this format: 
	 *  array($id=>$link_type,id2=>link_type2).
	 */
	function getDependentOn() {
		if (!$this->getID()) {
			return array();
		}
		if (!$this->dependon) {
			$res = db_query_params ('SELECT is_dependent_on_task_id,link_type
				FROM project_dependencies
				WHERE project_task_id=$1',
						array ($this->getID())) ;
			for ($i=0; $i<db_numrows($res); $i++) {
				$this->dependon[db_result($res,$i,'is_dependent_on_task_id')] = db_result($res,$i,'link_type');
			}
		}
		/* fix bug 319: if dependentlist is emtpy, set it to 100 (none) */
		if (!$this->dependon) {
			$this->dependon[100]=PM_LINK_DEFAULT;
		}
		return $this->dependon;
	}

	/**
	 * setAssignedTo - takes an array of user_id's and builds assignments.
	 *
	 * @param	array	The array of user_id's.
	 * @returns	boolean	success.
	 */
	function setAssignedTo(&$arr) {
		$arr2 = $this->getAssignedTo();
		$this->assignedto =& $arr;

		//If no one is assigned, then assign it to "100" - NOBODY
		if (!$arr || count($arr) < 1 || ((count($arr)==1) && ($arr[0]==''))) {
			$arr=array('100');
		}
		if (count($arr) || count($arr2)) {
			$add_arr = array_values(array_diff ($arr, $arr2));
			$del_arr = array_values(array_diff ($arr2, $arr));
			for ($i=0; $i<count($del_arr); $i++) {
				db_query_params ('DELETE FROM project_assigned_to
					WHERE project_task_id=$1
					AND assigned_to_id=$2',
						 array ($this->getID(),
							$del_arr[$i])) ;
				if (db_error()) {
					$this->setError('setAssignedTo()-1:: '.db_error());
					return false;
				}
			}
			for ($i=0; $i<count($add_arr); $i++) {
				db_query_params ('INSERT INTO project_assigned_to (project_task_id,assigned_to_id) VALUES ($1,$2)',
						 array ($this->getID(),
							$add_arr[$i])) ;
				if (db_error()) {
					$this->setError('setAssignedTo()-2:: '.db_error());
					return false;
				}
			}
			return true;
		} else {
			return true;
		}
	}

	/**
	 *	getAssignedTo - get an array of user_id's that you are assigned to.
	 *
	 *	@return	array	The array of user_id's.
	 */
	function getAssignedTo() {
		if (!$this->getID()) {
			return array();
		}
		if (!$this->assignedto) {
			$this->assignedto =& util_result_column_to_array(db_query_params('SELECT assigned_to_id FROM project_assigned_to WHERE project_task_id=$1',
											 array ($this->getID()))) ;
		}
		return $this->assignedto;
	}

	/**
	 *	update - update this ProjectTask in the database.
	 *
	 *	@param	string	The summary of this task.
	 *	@param	string	The detailed description of this task.
	 *	@param	int	The Priority of this task.
	 *	@param	int	The Hours estimated to complete this task.
	 *	@param	int	The (unix) start date of this task.
	 *	@param	int	The (unix) end date of this task.
	 *	@param	int	The status_id of this task.
	 *	@param	int	The category_id of this task.
	 *	@param	int	The percentage of completion in integer format of this task.
	 *	@param	array	An array of user_id's that are assigned this task.
	 *	@param	array	An array of project_task_id's that this task depends on.
	 *	@param	int	The GroupProjectID of a new subproject that you want to move this Task to.
	 *	@param	int	The duration of the task in days.
	 *	@param	int	The id of the parent task, if any.
	 *	@return	boolean success.
	 */
	function update($summary,$details,$priority,$hours,$start_date,$end_date,
		$status_id,$category_id,$percent_complete,&$assigned_arr,&$depend_arr,
		$new_group_project_id,$duration=0,$parent_id=0) {
		$has_changes = false; // if any of the values passed is different from
		
		$arrChangedAndInNotice = array("details"=>"","summary"=>"",
		"complete"=>"","status"=>"","subproject"=>""); /* includes only 
		entries that changed and will be sended by E-Mail (sendNotice()) */
		
		$v = new Validator();
		$v->check($summary, "summary");
		$v->check($priority, "priority");
		$v->check($hours, "hours");
		$v->check($start_date, "start date");
		$v->check($end_date, "end date");
		$v->check($status_id, "status");
		$v->check($category_id, "category");
		if (!$v->isClean()) {
			$this->setError($v->formErrorMsg("Must include "));
			return false;
		}
		if (!$parent_id) {
			$parent_id=0;
		}
		if ( ($this->getParentID()) != $parent_id ) {
			$has_changes = true;
		}
		if (!$duration) {
			$duration=0;
		}
		if ( ($this->getDuration()) != $duration ) {
			$has_changes = true;
		}

		if (!forge_check_perm ('pm', $this->ProjectGroup->getID(), 'manager')) {
			$this->setPermissionDeniedError();
			return false;
		}

		/*if ( ($this->getSummary() != $summary) || ($this->getDetails() != $details) ||
			 ($this->getPriority() != $priority) || ($this->getHours() != $hours) ||
			 ($this->getStartDate() != $start_date) || ($this->getEndDate() != $end_date) ||
			 ($this->getStatusID() != $status_id) || ($this->getCategoryID() != $category_id) ||
			 ($this->getPercentComplete() != $percent_complete) ) {
			 
			 $has_changes = true;
		}*/
		
		
		db_begin();

		//
		//  Attempt to move this Task to a new Subproject
		//  need to instantiate new ProjectGroup obj and test if it works
		//
		$group_project_id = $this->ProjectGroup->getID();
		if ($new_group_project_id != $group_project_id) {
			$newProjectGroup= new ProjectGroup($this->ProjectGroup->getGroup(), $new_group_project_id);
			if (!is_object($newProjectGroup) || $newProjectGroup->isError()) {
				$this->setError('ProjectTask: Could not move to new ProjectGroup'. $newProjectGroup->getErrorMessage());
				db_rollback();
				return false;
			}
			if (!forge_check_perm ('pm', $newProjectGroup->getID(), 'manager')) {
				$this->setPermissionDeniedError();
				db_rollback();
				return false;
			}
			//
			//  Now set ProjectGroup, Category, and Assigned to 100 in the new ProjectGroup
			//
			$status_id=1;
			$category_id='100';
			unset($assigned_to);
			$assigned_to=array('100');
			$this->ProjectGroup =& $newProjectGroup;
			$this->addHistory ('group_project_id',$group_project_id);
			$has_changes = true;
			$arrChangedAndInNotice['subproject'] = ">";
		}


		if ($details) {
			$has_changes = true;
			if($details != "" && $details != null) {$arrChangedAndInNotice['details'] = ">";}
			//Message vorhanden;
			if (!$this->addMessage($details)) {
				db_rollback();
				return false;
			}
		}
		if ($this->getStatusID() != $status_id) { 
			$this->addHistory ('status_id',$this->getStatusID());
			$has_changes = true;
			$arrChangedAndInNotice['status'] = ">";
		}

		if ($this->getCategoryID() != $category_id)	{
			$this->addHistory ('category_id',$this->getCategoryID());
			$has_changes = true;
		}

		if ($this->getPriority() != $priority) {
			$this->addHistory ('priority',$this->getPriority());
			$has_changes = true;
		}

		if ($this->getSummary() != htmlspecialchars($summary)) {
			$this->addHistory ('summary',$this->getSummary());
			$has_changes = true;
			$arrChangedAndInNotice['summary'] = ">";
		}

		if ($this->getPercentComplete() != $percent_complete) {
			$this->addHistory ('percent_complete',$this->getPercentComplete());
			$has_changes = true;
			$arrChangedAndInNotice['complete'] = ">";
		}

		if ($this->getHours() != $hours) {
			$this->addHistory ('hours',$this->getHours());
			$has_changes = true;
		}

		if ($this->getStartDate() != $start_date) {
			$this->addHistory ('start_date',$this->getStartDate());
			$has_changes = true;
		}

		if ($this->getEndDate() != $end_date) {
			$this->addHistory ('end_date',$this->getEndDate());
			$has_changes = true;
		}

		$old_assigned = $this->getAssignedTo();
		$diff_assigned_array=array_diff($old_assigned, $assigned_arr);
		if (count($diff_assigned_array)>0) { 
				for ($tmp=0;$tmp<count($old_assigned);$tmp++) {
					$this->addHistory('assigned_to_id',$old_assigned[$tmp]);
				}
				$has_changes = true;
		}
		$old_array = array_keys($this->getDependentOn());			
		$diff_array=array_diff($old_array,array_keys($depend_arr));
		if (count($diff_array)>0) { 
			for ($tmp=0;$tmp<count($old_array);$tmp++) {
				$this->addHistory('dependent_on_id', $old_array[$tmp]);	
			}
			$has_changes = true;
		}
		
		if (!$this->setDependentOn($depend_arr)) {
			db_rollback();
			return false;
		} elseif (!$this->setAssignedTo($assigned_arr)) {
			db_rollback();
			return false;
		} else {
			$res = db_query_params ('UPDATE project_task SET
				summary=$1,
				priority=$2,
				hours=$3,
				start_date=$4,
				end_date=$5,
				status_id=$6,
				percent_complete=$7,
				category_id=$8,
				group_project_id=$9,
				duration=$10,
				parent_id=$11
				WHERE group_project_id=$12
				AND project_task_id=$13',
						array (htmlspecialchars($summary),
						       $priority,
						       $hours,
						       $start_date,
						       $end_date,
						       $status_id,
						       $percent_complete,
						       $category_id,
						       $new_group_project_id,
						       $duration,
						       $parent_id,
						       $group_project_id,
						       $this->getID())) ;
			if (!$res) {
				$this->setError('Error On ProjectTask::update-5: '.db_error().$sql);
				db_rollback();
				return false;
			} else {
				if (!$this->fetchData($this->getID())) {
					$this->setError('Error On ProjectTask::update-6: '.db_error());
					db_rollback();
					return false;
				} else {
					if ($has_changes) { //only send email if there was any change
						$this->sendNotice(false, $arrChangedAndInNotice);
					}
					db_commit();
					return true;
				}
			}
		}

	}

	/**
	 *	sendNotice - contains the logic for sending email/jabber updates.
	 *
	 *	@return	boolean	success.
	 */
	function sendNotice($first=false, $arrChangedAndInNotice=array()) {
		global $send_task_email;

		if ($send_task_email===false) {
			return true;
		}
		$ids = $this->getAssignedTo();

		//
		//	See if there is anyone to send messages to
		//
		if (count($ids) < 1 && !$this->ProjectGroup->getSendAllPostsTo()) {
			return true;
		}

		$body = "Task #". $this->getID() ." has been updated. ".
			"\n\nProject: ". $this->ProjectGroup->Group->getPublicName(); 
			if (isset($arrChangedAndInNotice['subproject']))
				$body .= "\n". $arrChangedAndInNotice['subproject']."Subproject: ". $this->ProjectGroup->getName();

			if (isset($arrChangedAndInNotice['summary']))
				$body .= "\n". $arrChangedAndInNotice['summary']. "Summary: ".util_unconvert_htmlspecialchars( $this->getSummary() );

			if (isset($arrChangedAndInNotice['complete']))
				$body .= "\n". $arrChangedAndInNotice['complete']. "Complete: ". $this->getPercentComplete() ."%";

			if (isset($arrChangedAndInNotice['status']))
				$body .= "\n". $arrChangedAndInNotice['status']. "Status: ". $this->getStatusName();

			$body .= "\n\nDescription: ". util_unconvert_htmlspecialchars( $this->getDetails() );

		/*
			Now get the followups to this task
		*/
		$result2=$this->getMessages();

		$rows=db_numrows($result2);

		if ($result2 && $rows > 0) {
			$body .= "\n\nFollow-Ups:";
			for ($i=0; $i<$rows;$i++) {
				if($i===0){ $temp = $arrChangedAndInNotice['details']; } else {$temp = "";}
				$body .= "\n\n-------------------------------------------------------";
				$body .= "\nDate: ". date(_('Y-m-d H:i'),db_result($result2,$i,'postdate'));
				$body .= "\nBy: ".db_result($result2,$i,'user_name');
				$body .= "\n\n". $temp ."Comment:\n".util_unconvert_htmlspecialchars(db_result($result2,$i,'body'));
			}
		}

		$body .= "\n\n-------------------------------------------------------".
			"\nFor more info, visit:".
			"\n\n".util_make_url ('/pm/task.php?func=detailtask&project_task_id='.$this->getID().
					      "&group_id=".$this->ProjectGroup->Group->getID().
					      "&group_project_id=".$this->ProjectGroup->getID());

		$subject=sprintf (_('[%1$s - %2$s] [Task #%3$d] '),
				  $this->ProjectGroup->Group->getUnixName(),
				  $this->ProjectGroup->getName(),
				  $this->getID()) .
			util_unconvert_htmlspecialchars( $this->getSummary() );

		util_handle_message(array_unique($ids),$subject,$body,$this->ProjectGroup->getSendAllPostsTo());
		return true;
	}

}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
