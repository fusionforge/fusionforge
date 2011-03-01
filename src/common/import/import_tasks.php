<?php

require_once('../env.inc.php');
require_once $gfwww.'include/pre.php';
require_once $gfcommon.'pm/ProjectGroupFactory.class.php';
require_once $gfcommon.'pm/ProjectCategory.class.php';
require_once $gfcommon.'pm/ProjectGroup.class.php';
require_once $gfcommon.'pm/ProjectTask.class.php';
require_once($gfcommon.'include/User.class.php');
//require_once($gfcommon.'import/import_arrays.php');

class Tasks{

	function __construct($trackers, $group_id, $users) {
		
		$this->group =& group_get_object($group_id);
		if (!$this->group || !is_object($this->group)) {
			print "error retrieving group from id";
		} else if ($this->group->isError()) {
			print "error";
		}
		$this->hashrn=array();
		$this->hashlogin=array();
		//create hash table hashrn{real_name:mail} & hashlogin{id:mail}
		foreach($users as $user => $infos){
			$this->hashrn[$infos['real_name']] = $infos['mail'];
			$this->hashlogin[$user] = $infos['mail'];
		}
		$this->trackers = $trackers;
	}
	/**
	 * addComments - Add followup comments to an Artifact Object
	 * @param Artifact	the artifact object where history should be added
	 * @param array the artifact's data in json format (an array)
	 */
	function addComments($artifact, $comments){
		foreach($comments as $c){
			$time = strtotime($c['date']);
			$uid =&user_get_object_by_name($c['submitter'])->getID();
			$importData = array('time' => $time, 'user' => $uid);
			$artifact->addMessage($c['comment'], $importData);
		}
	}
	
	/**
	 * addHistory - Add history of changes to an Artifact Object
	 * @param Artifact	the artifact object where history should be added
	 * @param array the artifact's data in json format (an array)
	 */
	function addHistory($artifact, $history){
		foreach($history as $h){
			$time = strtotime($h['date']);
			$uid =&user_get_object_by_name($h['by'])->getID();
			$importData = array('time' => $time, 'user' => $uid);
	//hack!!
			$old = $h['old'];
	//		if($h['field']=='assigned_to'){
	//			if($old!='none'){
	//				$old =&user_get_object_by_name($old)->getID();
	//			} else {
	//				$old = 100;
	//			}
	//		}
	//		if($h['field']=='status_id'){
	//			$status = array('Open' =>1, 'Closed' => 2, 'Deleted' => 3);
	//			$old = $status[$old];
	//		}
	//		if($h['field']=='close_date'){
	//			$old = strtotime($old);
	//		}
	//end hack
			$artifact->addHistory($h['field'],$old, $importData);
		}
	}
	
	/**
	 * createTaskTracker - Create a specific tracker from data in the specified group
	 * @param string Tracker type (bugs, support, ...)
	 * @param Group	The group which the tracker belongs to
	 * @param array	Tracker data from JSON
	 * @return ArtifactType	the tracker created
	 */
	
	function createTaskTracker($data){
		// TaskTracker's type
		$tracker = $data['type'];
		//	Create a tracker
		db_begin();
		$pg = new ProjectGroup($this->group);
		if (!$pg || !is_object($pg)) {
			db_rollback();
			return false;
		}
//		include $GLOBALS['gfcommon'].'import/import_arrays.php';
	//	if(array_key_exists($tracker, $base_tracker_association)){
	//		$valueType = $base_tracker_association[$tracker];
	//	} else {
	//		$valueType = 0;
	//	}
		if (!$pg->create($data["label"], $data["label"])) {
			new dBug($pg);
			db_rollback();
			return false;
		} else {
			if (count($data['vocabulary']['category']) >1){
				//  Create each category
				$cats = $this->createCategories($pg, $data['vocabulary']['category']);
			} else {
				$cats = array('None'=>100);
			}
			if (count($data['artifacts']) >0){
				//	Create each task in the TaskTracker
				$output = array($pg, $data, $cats);
//				$this->createTasks($pg, $data, $cats);
			} else {
				$output = false;
			}
		}
		db_commit();
		return $output;
	}
	
	/**
	 * Create each category for a single project group
	 * @param ProjectGroup $pg
	 * @param array $categoriesList
	 */
	function createCategories($pg, $categoriesList){
		$cats = array();
		foreach($categoriesList as $cat){
			if($cat != 'None'){
				$pc = new ProjectCategory($pg);
				if ($pc){
					if (!$pc->create($cat)) {
						db_rollback();
						return false;
					} else {
						$cats[$cat] = $pc->getID();
					}
				} else {
					return false;
				}
			} else {
				$cats['None'] = 100;
			}
		}
		return $cats;
	}
	
	/**
	 * Create each task for the considered project group
	 * @param ProjectGroup $pg
	 * @param array $data
	 */
	function createTasks($pg, $data, $cats){
		// Stores each atid in a name:id array
		$atids = array();
		// Stores each artifact dependent on another artifact in a id:dependent_on_id array, with dependent tasks represented with their name
		$dependent = array();
		foreach($data['artifacts'] as $a){
			//for each artifact
			$artifact = new ProjectTask($pg);
			if ($artifact){
				$start = strtotime($a['start_date']);
				$end = strtotime($a['end_date']); 
				$assigned = array();
				if(is_array($a['assigned_to[]'])){
					foreach($a['assigned_to[]'] as $realname){
						if($realname == 'None'){
							$assigned[]=100;
						}else{
							$assigned[] = user_get_object_by_mail($this->hashrn[$realname])->getID(); // this should be done once instead of for each artifact, TODO	
						}
					}
				} else {
					if($a['assigned_to[]']=='None'){
						$assigned[]=100;
					}else{
						$assigned[] = user_get_object_by_mail($this->hashrn[$a['assigned_to[]']])->getID();
					}
				}
				$uid =user_get_object_by_name($a['submitter'])->getID();
				$dependentTemp = array();
//				new dBug(array($a['summary'], $a['description'], $a['priority'], $a['hours'], $start, $end, $cats[$a['category']], $a['percent_complete'], &$assigned, &$dependentTemp, 0, 0, array('user' => $uid)));
				if(!$artifact->create($a['summary'], $a['description'], $a['priority'], $a['hours'], $start, $end, $cats[$a['category']], $a['percent_complete'], &$assigned, &$dependentTemp, 0, 0, array('user' => $uid))){
					return false;
				} else {
					
					$atid =  $artifact->getID();
					$atids[$a['summary']] = $atid;
					$dependent[$atid] = $a['dependent_on[]'];
					$this->addComments($artifact, $a['comments']);
					$this->addHistory($artifact, $a['history']);
	//				addTimeTracking($artifact, $a);
				}
			}
		}
		//Sets dependent tasks for each artifact, must be done after all artifacts are created
		foreach($dependent as $mainId => $depNames){
			if($depNames!='None'){
				$artifact = new ProjectTask($pg, $mainId);
				$dependentIds = array();
				if(is_array($depNames)){
					foreach($depNames as $taskName){
						$dependentIds[$atids[$taskName]] = 'FS'; // Default to PM_LINK_DEFAULT defined as FS in ProjectTask class, it seems there is no way to use any of the other relations anyway...
					}
				} else {
					$dependentIds[$atids[$depNames]] = 'FS';				
				}
				$artifact->setDependentOn($dependentIds);
			}
		}
	}
	
	/**
	 * deleteTrackers - Delete all existing default trackers from a projet
	 * @param Group A Group object
	 */
	function deleteTrackers(){
		$pgf = new ProjectGroupFactory($this->group);
		$pgs = $pgf->getProjectGroups();
		if($pgs){
			foreach($pgs as $pg){
				$pg->delete(true, true);
			}
		}
	}
	
	function createAllTasks(){
		if($this->taskCreationArray){
			foreach($this->taskCreationArray as $taskCreation){
				$this->createTasks($taskCreation[0],$taskCreation[1],$taskCreation[2]);
			}
		}
	}
	
	/**
	 * tracker_fill - Create trackers from an array in a given group
	 * @param array Trackers part of a JSON pluck, including label, artifacts, vocabulary...
	 * @param int	Group id of the group where the trackers should be added
	 */
	function tasks_fill(){
	
		//existing tracker deletion
		$this->deleteTrackers();
		
		//Tracker creation
		$this->taskCreationArray = array(); // This array is used to store each projectGroup and each artifacts which will be imported later, we need to stop the script so as to update permissions again (default to Read for each new TaskTracker, and thus nobody can be assigned to a task except userid 100 which is Nobody)
		foreach ($this->trackers as $data){
			$output = $this->createTaskTracker($data);
			if($output){
				$this->taskCreationArray[]=$output;
			}
		}
	}
}

