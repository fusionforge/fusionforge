<?php
/**
 * Copyright (C) 2013 Vitaliy Pylypiv <vitaliy.pylypiv@gmail.com>
 * Copyright 2016, Stéphane-Eymeric Bredtthauer - TrivialDev
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
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

define('RELEASE_OF_TASK', 1);
define('RELEASE_OF_USER_STORY', 2);

require_once $gfcommon.'include/FFError.class.php';
require_once $gfplugins.'taskboard/common/TaskBoardColumn.class.php';
require_once $gfplugins.'taskboard/common/TaskBoardRelease.class.php';

/**
 * Factory method which creates a taskboard from a taskboard ID
 *
 * @param	int	The taskboard ID
 * @param	array	The result array, if it's passed in
 * @return	object	TaskBoard object
 */
function &taskboard_get_object($taskboard_id, $data = false) {
	static $trackers = array();

	if( !array_key_exists($taskboard_id, $trackers)) {
		$res = db_query_params('SELECT * FROM plugin_taskboard WHERE taskboard_id=$1', array($taskboard_id));
		if (db_numrows($res) <1) {
			return false;
		}
		$data = db_fetch_array($res);

		$Group = group_get_object($data['group_id']);
		$trackers[$taskboard_id] = new TaskBoard($Group, $data);
	}
	return $trackers[$taskboard_id];
}

/**
 * Initialize a task board
 */
function &taskboard_init($group_id) {
	db_begin();
	$res = db_query_params('INSERT INTO plugin_taskboard(group_id) VALUES($1)', array($group_id));
	if (!$res) {
		$this->setError(db_error());
		db_rollback();
		return false;
	}
	$taskboard_id=db_insertid($res,'plugin_taskboard','taskboard_id');
	if ($taskboard_id==0) {
		db_rollback();
		return false;
	}
	$Group = group_get_object($group_id);
	if (!$Group) {
		db_rollback();
		return false;
	}
	$Taskboard = new TaskBoard($Group, $taskboard_id);
	if (!$Taskboard) {
		db_rollback();
		return false;
	}
	db_commit();
	return $Taskboard;
}


class TaskBoard extends FFError {
	/**
	 * The Group object.
	 *
	 * @var	object	$Group.
	 */
	var $Group;

	/**
	 * Trackers adapter object
	 *
	 * @var	object
	 */
	var $TrackersAdapter;

	/**
	  * Array of taskboard data.
	 *
	 * @var	array	$data_array.
	 */
	var $data_array;

	function TaskBoard($Group, $data = false) {
		parent::__construct();
		if (!$Group || !is_object($Group)) {
			$this->setError(_('No Valid Group Object'));
			return false;
		}
		if ($Group->isError()) {
			$this->setError(_('Taskboard')._(': ').$Group->getErrorMessage());
			return false;
		}

		$this->Group = $Group;

		if ($data) {
			if (is_array($data)) {
				$this->data_array =& $data;
				if ($this->data_array['group_id'] != $this->Group->getID()) {
					$this->setError('Group_id in db result does not match Group Object');
					$this->data_array = null;
					return false;
				}
			} else {
				$this->fetchData($data);
			}
		}

		$plugins_taskboard_trackers_adapter_class = forge_get_config('trackers_adapter_class', 'taskboard');
		if( !isset($plugins_taskboard_trackers_adapter_class) ) {
			$plugins_taskboard_trackers_adapter_class  = 'TaskBoardBasicAdapter';
		}
		global $gfplugins;
		$plugins_taskboard_trackers_adapter_module = $gfplugins.'taskboard/common/adapters/' . $plugins_taskboard_trackers_adapter_class . '.class.php';

		require_once( $plugins_taskboard_trackers_adapter_module );
		$this->TrackersAdapter = new $plugins_taskboard_trackers_adapter_class( $this );
	}


	/**
	 * create - create a row in the taskboards table
	 *
	 * @param	array	list of trackers IDs, linked to the taskboard
	 * @param	array	has of card background colors (key - tracker id, value - bg color)
	 * @param	string	Alias for of 'select' extra field used for release/sprint
	 * @param	string	Tracker type of extra field used for release/sprint (1 - task trackers, 2 - user story tracker)
	 * @param	string	Used for cost calculations together with remaining_cost_field_alias if specified
	 * @param	string	Used for cost calculations together with estimated_cost_field_alias if specified
	 *
	 * @return     true on success / false on failure.
	 */
	function create($name, $description = '', $trackers = array(), $bgcolors = array(), $release_field_alias = NULL, $release_field_tracker = 1,
			$estimated_cost_field_alias = NULL, $remaining_cost_field_alias = '',
			$user_stories_tracker = NULL, $user_stories_reference_field = NULL,
			$user_stories_sort_field = NULL, $first_column_by_default = 1) {

		//$estimated_cost_field = '';  // TODO define alias by default in configuration file
		//$remaining_cost_field = '';  // TODO define alias by default in configuration file
		//$user_stories_reference_field = '';  // TODO define alias by default in configuration file
		//$user_stories_sort_field = '';  // TODO define alias by default in configuration file

		//
		//      data validation
		//
		if (!session_loggedin()) {
			$this->setError(_('Must Be Logged In'));
			return false;
		}

		$ret = true;
		db_begin();
		$res = db_query_params(
				'INSERT INTO plugin_taskboard(taskboard_name, description, group_id, release_field_alias, release_field_tracker, estimated_cost_field_alias,
					remaining_cost_field_alias, user_stories_group_artifact_id, user_stories_reference_field_alias,user_stories_sort_field_alias,
				first_column_by_default) VALUES($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11)',
				array($name, $description, $this->Group->getID(), $release_field_alias, $release_field_tracker, $estimated_cost_field_alias,
					$remaining_cost_field_alias, ( $user_stories_tracker ? $user_stories_tracker: NULL ),
					$user_stories_reference_field, $user_stories_sort_field, $first_column_by_default)
			);
		if (!$res) {
			$this->setError(_('Cannot create taskboard'));
			$ret = false;
 		} else {
			$this->data_array['taskboard_id'] = db_insertid($res,'plugin_taskboard','taskboard_id');
		}

		if( $ret && is_array($trackers)) {
			foreach( $trackers as $tracker_id ) {
				$ret = $this->addUsedTracker( $tracker_id, ( array_key_exists($tracker_id, $bgcolors) ? $bgcolors[$tracker_id]  : NULL) );
			}
		}

		// TODO columns initialization

		if($ret) {
			db_commit();
		} else {
			db_rollback();
			$this->data_array['taskboard_id'] = NULL;
		}

		return $ret;
	}

	/**
	 * update - update a row in the taskboards table
	 *
	 * @param	array	list of trackers IDs, linked to the taskboard
	 * @param	array	has of card background colors (key - tracker id, value - bg color)
	 * @param	string	Alias for of 'select' extra field used for release/sprint
	 * @param	string	Tracke type of extra field used for release/sprint (1 - task trackers, 2 - user story tracker)
	 * @param	string	Used for cost calculations together with remaining_cost_field_alias if specified
	 * @param	string	Used for cost calculations together with estimated_cost_field_alias if specified
	 *
	 * @return	true on success / false on failure.
	 */
	function update($name, $description, $trackers=array(), $bgcolors=array(), $release_field_alias = NULL,  $release_field_tracker = 1,
			$estimated_cost_field_alias = NULL, $remaining_cost_field_alias = NULL,
			$user_stories_tracker = NULL, $user_stories_reference_field = NULL, $user_stories_sort_field = NULL, $first_column_by_default = 1 ) {
		//
		//      data validation
		//
		if (!session_loggedin()) {
			$this->setError(_('Must Be Logged In'));
			return false;
		}

		if ($name!=$this->getName() || $description!=$this->getDescription()) {
			$updateOnlyName = true;
		} else {
			$updateOnlyName = false;
		}

		if( count($trackers) == 0 && !$updateOnlyName) {
			$this->setError(_('Taskboard must be linked at least to one tracker'));
			return false;
		}

		$ret = true;
		db_begin();
		if($updateOnlyName) {
			$res = db_query_params(
					'UPDATE plugin_taskboard SET taskboard_name=$1, description=$2 WHERE taskboard_id=$3',
					array($name, $description, $this->getID()));
		} else {
			$res = db_query_params(
					'UPDATE plugin_taskboard SET taskboard_name=$1, description=$2, release_field_alias=$3, release_field_tracker=$4,
					estimated_cost_field_alias=$5, remaining_cost_field_alias=$6, user_stories_group_artifact_id=$7,
					user_stories_reference_field_alias=$8, user_stories_sort_field_alias=$9,
					first_column_by_default=$10 WHERE taskboard_id=$11',
					array(
							$name, $description, $release_field_alias, $release_field_tracker ,
							$estimated_cost_field_alias, $remaining_cost_field_alias, ( $user_stories_tracker ? $user_stories_tracker: NULL),
							$user_stories_reference_field, $user_stories_sort_field,
							$first_column_by_default, $this->getID())
					);
		}
		if (!$res) {
			$this->setError(_('Cannot update taskboard'));
			$ret = false;
		} else {
			$this->fetchData();
		}

		// update trackers
		if($ret && !$updateOnlyName) {
			$old_trackers = $this->getUsedTrackersIds();
			foreach($trackers as $tracker_id) {
				if(in_array($tracker_id, $old_trackers)) {
					// update tracker
					$ret = $this->updateUsedTracker(
						$tracker_id,
						(array_key_exists($tracker_id, $bgcolors) ? $bgcolors[$tracker_id] : NULL)
					);
				} else {
					// add tracker
					$ret = $this->addUsedTracker(
						$tracker_id,
						(array_key_exists($tracker_id, $bgcolors) ? $bgcolors[$tracker_id] : NULL)
					);
				}
			}

			foreach($old_trackers as $tracker_id){
				if(!in_array($tracker_id, $trackers)) {
					$ret = $this->deleteUsedTracker($tracker_id);
				}
			}
		}

		if($ret) {
			db_commit();
		} else {
			db_rollback();
			$this->data_array['taskboard_id'] = NULL;
		}

		return $ret;
	}


	/**
	 * delete - delete taskboard
	 *
	 * @return	bool
	 */
	function delete() {
		$res = db_query_params('DELETE FROM plugin_taskboard WHERE taskboard_id = $1', array($this->getID()));
		if (!$res) {
			$this->setError(_('Taskboard')._(': ')._('Cannot delete taskboard'));
			return false;
		}
		return true;
	}

	/**
	 * _checkExtraFields() - check where extra field exists in the tracker
	 *
	 */
	private function _checkExtraFields($group_artifacts, $alias) {
		$ret = true;

		/* TODO How to get ArtifactType objects ?
		foreach($group_artifacts as $group_artifacts) {
			getExtraFields
		}
		*/

		return $ret;
	}

	/**
	 * fetchData - re-fetch the data for this TaskBoard from the database.
	 *
	 * @param	int	The taskboard ID.
	 * @return	boolean	success.
	 */
	function fetchData($taskboard_id = NULL) {
		if(!$taskboard_id) {
			$taskboard_id = $this->getID();
		}
		$res = db_query_params('SELECT * FROM plugin_taskboard WHERE taskboard_id=$1', array($taskboard_id));
		if (!$res || db_numrows($res) < 1) {
			$this->setError(_('Taskboard')._(': ')._('Invalid TaskBoardID'));
			return false;
		}
		$this->data_array = db_fetch_array($res);
		db_free_result($res);
		return true;
	}

	/**
	 * getID - get this TaskBoardID.
	 *
	 * @return	int	The taskboard_id
	 */
	function getID() {
		return $this->data_array['taskboard_id'];
	}

	/**
	 * getName - get this TaskBoard Name.
	 *
	 * @return	string	The taskboard_name
	 */
	function getName() {
		return $this->data_array['taskboard_name'];
	}

	/**
	 * getDescription - get this TaskBoard Description.
	 *
	 * @return	string	The taskboard description
	 */
	function getDescription() {
		return $this->data_array['description'];
	}

	/**
	 * getReleaseField - get alias of field, used for release/sprint
	 *
	 * @return	string	extra field alias
	 */
	function getReleaseField() {
		return $this->data_array['release_field_alias'];
	}

	/**
	 * getReleaseFieldTracker - get a source tracker type of field, used for release/sprint
	 *
	 * @return	integer	1 - tasks tracker, 2 - user story tracker
	 */
	function getReleaseFieldTracker() {
		return $this->data_array['release_field_tracker'];
	}

	/**
	 * getEstimatedCostField - get alias of field, used for estimated cost value
	 *
	 * @return	string	extra field alias
	 */
	function getEstimatedCostField() {
		return $this->data_array['estimated_cost_field_alias'];
	}

	/**
	 * getRemainingCostField - get alias of field, used for remaining cost value
	 *
	 * @return	string	extra field alias
	 */
	function getRemainingCostField() {
		return $this->data_array['remaining_cost_field_alias'];
	}

	/**
	 * getUserStoriesTrackerID - get identifier of tracker, used for user stories
	 *
	 * @return	integer	tracker identifier
	 */
	function getUserStoriesTrackerID() {
		return $this->data_array['user_stories_group_artifact_id'];
	}

	/**
	 * getUserStoriesReferenceField - get alias of field, used as a reference to user stiry artifact
	 *
	 * @return	string	extra field alias
	 */
	function getUserStoriesReferenceField() {
		return $this->data_array['user_stories_reference_field_alias'];
	}

	/**
	 * getUserStoriesSortField - get alias of field, used as for user stories sorting (DESC)
	 *
	 * @return	string	extra field alias
	 */
	function getUserStoriesSortField() {
		return $this->data_array['user_stories_sort_field_alias'];
	}

	/**
	 * getFirstColumnByDefault
	 *
	 * @return	int
	 */
	function getFirstColumnByDefault() {
		return $this->data_array['first_column_by_default'];
	}


	/**
	 * getUsedTrackersIds - get identifiers of used trackers
	 *
	 * @return	array
	 */
	function getUsedTrackersIds() {
		$res = db_query_params('SELECT * FROM plugin_taskboard_trackers WHERE taskboard_id = $1', array ($this->getID()));
		if (!$res) {
			$this->setError(_('Taskboard')._(': ')._('Cannot get list of used trackers.'));
			return false;
		}

		$trackers = array();
		while($row =  db_fetch_array($res)) {
			$trackers[] = $row['group_artifact_id'];
		}
		db_free_result($res);
		return $trackers;
	}

	/**
	 * getUsedTrackersiData - get data of used trackers
	 *
	 * @return	array
	 */
	function getUsedTrackersData() {
		$res = db_query_params('SELECT * FROM plugin_taskboard_trackers WHERE taskboard_id = $1', array ($this->getID()));
		if (!$res) {
			$this->setError(_('Taskboard')._(': ')._('Cannot get list of used trackers.'));
			return false;
		}

		$trackers = array();
		while( $row =  db_fetch_array($res)) {
			$trackers[] = $row;
		}
		db_free_result($res);
		return $trackers;
	}

	/**
	 * cleanUsedTrackers - empty list of trackers, used with taskboard
	 *
	 * @return	bool
	 */
	function cleanUsedTrackers() {
		$res = db_query_params('DELETE FROM plugin_taskboard_trackers WHERE taskboard_id = $1', array ($this->getID()));
		if (!$res) {
			$this->setError(_('Taskboard')._(': ')._('Cannot empty list of used trackers.'));
				 return false;
		}

		return true;
	}

	/**
	 * addUsedTracker - add a tracker to use with taskboard
	 *
	 * @param	int	tracker identifier
	 * @param	string	optional card background color
	 *
	 * @return	bool
	 */
	function addUsedTracker($tracker_id, $bgcolor='') {
		$res = db_query_params('INSERT INTO plugin_taskboard_trackers(taskboard_id, group_artifact_id, card_background_color) VALUES($1, $2, $3)', array($this->getID(), $tracker_id, $bgcolor));
		 if (!$res) {
			$this->setError(_('Taskboard')._(': ')._('Cannot add used tracker'));
			return false;
		}

		return true;
	}

	/**
	 * updateUsedTracker - update used tracker
	 *
	 * @param	int	tracker identifier
	 * @param	string	optional card background color
	 *
	 * @return bool
	 */
	function updateUsedTracker($tracker_id, $bgcolor = '') {
		 $res = db_query_params('UPDATE plugin_taskboard_trackers SET card_background_color = $1 WHERE group_artifact_id = $2', array($bgcolor, $tracker_id));
		if (!$res) {
			$this->setError(_('Taskboard')._(': ')._('Cannot update used tracker'));
			return false;
		}

		return true;
	}

	/**
	 * deleteUsedTracker - delete used tracker
	 *
	 * @param	int	tracker identifier
	 *
	 * @return	bool
	 */
	function deleteUsedTracker($tracker_id) {
		$res = db_query_params('DELETE FROM plugin_taskboard_trackers WHERE group_artifact_id = $1', array($tracker_id));
		if (!$res) {
			$this->setError(_('Taskboard')._(': ')._('Cannot delete used tracker'));
			return false;
		}

		return true;
	}

	/**
	 * getUserStories - get taskboard user stories
	 *
	 * @return	array
	 */
	function getUserStories($release = NULL, $assigned_to = NULL) {
		$stories=array(
			'0' => array(
				'id' => 0,
				'title' => _('Unlinked tasks'),
				'description' => _('Tasks, which are not linked to any user story'),
				'tasks' => array(),
				'url' => '#'
			)
		);

		if($this->getReleaseFieldTracker() == RELEASE_OF_USER_STORY) {
			$tasks_trackers = $this->getUsedTrackersData();
			foreach($tasks_trackers as $tasks_tracker_data) {
				$tasks = $this->TrackersAdapter->getTasks($tasks_tracker_data['group_artifact_id'], $assigned_to, NULL, NULL);
				foreach($tasks as $task) {
					$task_maped = $this->getMappedTask($task);
					if ($task_maped['user_story']==0) {
						$stories[0]['tasks'][] = $task_maped;
					}
				}
			}
		}

		$user_stories_sort_field = $this->getUserStoriesSortField();
		$user_stories_sort_extra_field_id = NULL;
		if ($user_stories_sort_field) {
			$ef =  $this->TrackersAdapter->getFieldsIds($this->getUserStoriesTrackerID());
			if ( array_key_exists($user_stories_sort_field ,$ef) ) {
				$user_stories_sort_extra_field_id = $ef[ $user_stories_sort_field ];
			} else {
				$this->setError(_('Configured extra field for userstories sorting is not found.'));
				return false;
			}
		}

		$task_release = NULL;
		$user_story_release = NULL;

		if ($this->getReleaseFieldTracker() == RELEASE_OF_TASK) {
			$task_release = $release;
		} else {
			$user_story_release = $release;
		}

		$us = $this->TrackersAdapter->getUserStories($release);

		foreach($us as $story) {
			$stories[$story->getID()] = array(
				'id' => $story->getID(),
				'title' => $story->getSummary(),
				'description' => str_replace("\n", '<br>', util_gen_cross_ref($story->getDetails(),$this->Group->getID())),
				'priority' => $story->getPriority(),
				'tasks' => array(),
				'url' => $this->TrackersAdapter->getTaskUrl($story)
			);

			if ($user_stories_sort_extra_field_id) {
				$efd = $story ->getExtraFieldData();
				$stories[$story->getID()]['order'] = $efd[$user_stories_sort_extra_field_id];
			} else {
				//sort by GF priority if another field for sorting is not defined
				$stories[$story->getID()]['order'] = $stories[$story->getID()]['priority'];
			}

			if($this->getReleaseFieldTracker() == RELEASE_OF_USER_STORY) {
				$tasks_trackers = $this->getUsedTrackersData();
				foreach($tasks_trackers as $tasks_tracker_data) {
					$tasks = $this->TrackersAdapter->getTasks($tasks_tracker_data['group_artifact_id'], $assigned_to, NULL, $story->getID());
					foreach($tasks as $task) {
						$stories[$story->getID()]['tasks'][] = $this->getMappedTask($task);;
					}
				}
			}
		}

		if ($this->getReleaseFieldTracker() == RELEASE_OF_TASK) {
			$tasks_trackers = $this->getUsedTrackersData();
			foreach($tasks_trackers as $tasks_tracker_data) {
				$tasks = $this->TrackersAdapter->getTasks($tasks_tracker_data['group_artifact_id'], $assigned_to, $task_release);
				foreach($tasks as $task) {
					$task_maped = $this->getMappedTask($task);
					$stories[intval($task_maped['user_story'])]['tasks'][] = $task_maped;
				}
			}
		}

		$ret_stories = array_values($stories);
		usort($ret_stories, array($this, 'sortUserStories'));

		return $ret_stories;
	}

	/**
	 * getMappedTask - map artifact object into hash and add column and presentation specific fields
	 *
	 * @param	Artifact	artifact instance
	 *
	 * @return	array
	 */
	function getMappedTask($task) {
		static $_used_trackers_data = NULL;
		static $_first_column_id = NULL;

		if (!$_used_trackers_data) {
			foreach($this->getUsedTrackersData() as $tasks_tracker_data) {
				$_used_trackers_data[ $tasks_tracker_data['group_artifact_id'] ] = $tasks_tracker_data;
			}
		}

		$task_maped = $this->_mapTask($task);
		$column = taskboard_column_get_object_by_resolution($this, $task_maped['resolution']);
		if ($column) {
			 $task_maped['phase_id'] = $column->getID();
		} else {
			if($this->getFirstColumnByDefault()) {
				if(!$_first_column_id) {
					$columns = $this->getColumns();
					$_first_column_id = $columns[0]->getID();
				}
				$task_maped['phase_id'] = $_first_column_id;
			}
		}

		$card_title_background = $_used_trackers_data[$task->ArtifactType->getID()]['card_background_color'];
		if( method_exists($this->TrackersAdapter, 'cardBackgroundColor' ) ) {
			$task_maped['background'] = $this->TrackersAdapter->cardBackgroundColor($task, $card_title_background );
		} else {
			$task_maped['background'] = $card_title_background;
		}

		return $task_maped;
	}

	function sortUserStories($u1, $u2) {
		$ret = 0;

		usort( $u1['tasks'], array( $this, 'sortUserStoryTasks' ) );

		if (!array_key_exists('order', $u1)) {
			$ret = -1;
		} elseif (!array_key_exists('order', $u2)) {
			$ret = 1;
		} elseif ($u1['order'] < $u2['order']) {
			$ret = 1;
		} elseif ($u1['order'] > $u2['order']) {
			$ret = -1;
		}

		return $ret;
	}

	function sortUserStoryTasks($t1, $t2) {
		$ret = 0;

		if ($t1['priority'] < $t2['priority'] ) {
			$ret = 1;
		} elseif ( $t1['priority'] > $t2['priority'] ) {
			$ret = -1;
		}

		return $ret;
	}

	function getMandatoryFieldsMapping() {
		return array(
			'resolution' => 'resolution',
			'estimated_dev_effort' => $this->getEstimatedCostField(),
			'remaining_dev_effort' => $this->getRemainingCostField(),
			'user_story' => $this->getUserStoriesReferenceField()
		);
	}

	/**
	 * _mapTask - map artifact object into hash
	 *
	 * @param	Artifact	artifact instance
	 *
	 * @return	array
	 */
	private function _mapTask($task) {
		$ret = array();

		$ef_mapping = $this->getMandatoryFieldsMapping();

		$fields_ids = $this->TrackersAdapter->getFieldsIds($task->ArtifactType->getID());
		$extra_data = $task->getExtraFieldDataText();

		$ret['id'] = $task->getID();
		$ret['title'] = $task->getSummary();
		$ret['description'] = str_replace("\n", '<br>', util_gen_cross_ref($task->getDetails(),$this->Group->getID()) );
		$ret['assigned_to'] = $task->getAssignedRealName();
		$ret['priority'] = $task->getPriority();
		foreach( $ef_mapping as $k => $f){
			$ret[$k] = '';
			if (array_key_exists($f, $fields_ids)) {
				if (array_key_exists($fields_ids[$f], $extra_data)) {
					$ret[$k] = $extra_data[$fields_ids[$f]]['value'];
				}
			}
		}

		if (!$ret['user_story']) {
			// task is not assigend to any user story
			$ret['user_story'] = 0;
		}

		$ret['url'] = $this->TrackersAdapter->getTaskUrl($task);

		$params = array('user_id' => $task->getAssignedTo(), 'size' => 's', 'content' => '');
		plugin_hook_by_reference("user_logo", $params);
		if ($params['content']) {
			$ret['assigned_to_face'] = $params['content'];
		} else {
			$ret['assigned_to_face'] = '';
		}

		return $ret;
	}

	/**
	 * getColumns - get taskboard columns
	 *
	 * @return	array
	 */
	function getColumns() {
		$res = db_query_params('SELECT * FROM plugin_taskboard_columns WHERE taskboard_id=$1 ORDER BY order_num', array ($this->getID()));
		if (!$res) {
			$this->setError(_('Taskboard')._(': ')._('Cannot get list of columns.'));
			return false;
		}

		$columns = array();
		while($row =  db_fetch_array($res)) {
			$columns[] = new TaskBoardColumn($this, $row);
		}
		db_free_result($res);
		return $columns;
	}

	/**
	 * addColumn - add taskboard column
	 *
	 * @return	boolean
	 */
	function addColumn($title, $title_bg_color, $column_bg_color, $max_tasks) {
		$column_id = false;

		$res = db_query_params('SELECT COUNT(*) as count FROM plugin_taskboard_columns WHERE taskboard_id=$1', array ($this->getID()));
		if (!$res) {
			return false;
		}

		$row =  db_fetch_array($res);
		$order = intval($row['count']) + 1;
		db_free_result($res);

		$res = db_query_params(
			'INSERT INTO plugin_taskboard_columns(taskboard_id, title, title_background_color, column_background_color, max_tasks, order_num) VALUES($1,$2,$3,$4,$5,$6)',
			array (
				$this->getID(),
				$title,
				$title_bg_color,
				$column_bg_color,
				intval($max_tasks),
				$order
			)
		) ;
		if (!$res) {
			return false;
		}
		$column_id = db_insertid($res, 'plugin_taskboard_columns', 'taskboard_column_id');
		db_free_result($res);

		return $column_id;
	}

	/**
	 * getExtraFields - get a list of extra fields, existing is all used task trackers
	 *
	 * @param	array	list of allowed extra fields types
	 * @param	array	list of excluded aliases
	 *
	 * @return	array	array of aliases
	 */
	function getExtraFields($allowed_types = array(), $used_trackers = NULL, $excluded_aliases = array('resolution')) {
		$atf = $this->TrackersAdapter->getArtifactTypeFactory();
		if (!$atf || !is_object($atf) || $atf->isError()) {
			return _('Could Not Get ArtifactTypeFactory');
		}

		if (!$used_trackers) {
			$used_trackers = $this->getUsedTrackersIds();
		}

		$common_fields = array();

		foreach ($allowed_types as $allowed_type) {
			$common_fields[$allowed_type] = array();
		}

		$at_arr = $atf->getArtifactTypes();
		$init = true;
		for ($j = 0; $j < count($at_arr); $j++) {
			if (!is_object($at_arr[$j])) {
				//just skip it
			} elseif ($at_arr[$j]->isError()) {
				return $at_arr[$j]->getErrorMessage();
			} else {
				$tracker_id = $at_arr[$j]->getID();

				if (in_array($tracker_id, $used_trackers)) {
					// select common fields for the give types
					$fields = $at_arr[$j]->getExtraFields($allowed_types);
					$tmp = array();
					foreach ($allowed_types as $allowed_type) {
						$tmp[$allowed_type] = array();
					}

					foreach ($fields as $field) {
						// exclude 'resolution' field
						if (!in_array($field['alias'],$excluded_aliases)) {
							if($init) {
								if (in_array($field['field_type'], $allowed_types)) {
									$tmp[$field['field_type']][$field['alias']] = $field['field_name'];
								}
							} elseif (
									in_array($field['alias'], array_keys($common_fields[$field['field_type']])) &&
									in_array($field['field_type'], $allowed_types)
							) {
								$tmp[$field['field_type']][$field['alias']] = $field['field_name'];
							}
						}
					}
					$common_fields = $tmp;
					$init = false;
				}
			}
		}
		return $common_fields;
	}

	/**
	 * getExtraFieldValues - get hash of values, available for the given extra field
	 *
	 * @param	string	extra field alias
	 *
	 * @return	array	hash element_name => element_id
	 */
	function getExtraFieldValues($extra_field_alias) {
		$ret = array();

		$tasks_trackers = $this->getUsedTrackersIds();
		foreach($tasks_trackers as $tracker_id) {
			$ef_values = $this->TrackersAdapter->getExtraFieldValues($tracker_id, $extra_field_alias);
			if (count($ret) == 0) {
				$ret = $ef_values;
			} else {
				$buf = array();
				foreach($ret as $name => $id) {
					if( array_key_exists($name, $ef_values)) {
						$buf[$name] = $id;
					}
				}
				$ret = $buf;
			}
		}

		return $ret;
	}

	/**
	 * getReleases - get taskboard releases
	 *
	 * @return	array
	 */
	function getReleases() {
		$res = db_query_params('SELECT * FROM plugin_taskboard_releases WHERE taskboard_id = $1 ORDER BY start_date, end_date', array($this->getID()));
		if (!$res) {
			$this->setError(_('Taskboard')._(': ')._('Cannot get list of releases.'));
			return false;
		}

		$releases = array();
		while( $row =  db_fetch_array($res)) {
			$releases[] = new TaskBoardRelease($this, $row);
		}
		db_free_result($res);
		return $releases;
	}

	/**
	 * getCurrentRelease - get current or the most recent release object
	 *
	 * @return	object
	 */
	function getCurrentRelease() {
		$current_release = NULL;

		$res = db_query_params (
			'SELECT * FROM plugin_taskboard_releases WHERE taskboard_id=$1 AND start_date < $2 ORDER BY start_date DESC LIMIT 1',
				array(
					$this->getID(),
					strtotime(date('Y-m-d'))
				)
			);
		if (!$res) {
			$this->setError(_('Taskboard')._(': ')._('Cannot get current release.'));
			return false;
		} else {
			$row =  db_fetch_array($res);
			if ($row) {
				$current_release = new TaskBoardRelease($this, $row);
			}
		}
		db_free_result($res);

		return $current_release;
	}

	/**
	 * getReleaseValues - get list of all available reslease values
	 *
	 * @return	array
	 */
	function getReleaseValues() {
		$ret = array();

		if ($this->getReleaseFieldTracker() == 1) {
			// get values from tasks trackers
			$ret = $this->getExtraFieldValues($this->getReleaseField());
		} else {
			// get values from user stories trackers
			$ret = $this->TrackersAdapter->getExtraFieldValues($this->getUserStoriesTrackerID(), $this->getReleaseField());
		}

		return $ret;
	}

	/**
	 * getAvailableResolutions - get list of all available resolution values
	 *
	 * @return	array
	 */
	function getAvailableResolutions() {
		return array_keys($this->getExtraFieldValues('resolution'));
	}

	/**
	 * getUsedResolutions - get list of resolutions that are already used with taskboard columns
	 *
	 * @return	array
	 */
	function getUsedResolutions() {
		$res = db_query_params(
				'SELECT R.* FROM plugin_taskboard_columns_resolutions as R, plugin_taskboard_columns as C
				WHERE C.taskboard_id=$1 AND R.taskboard_column_id=C.taskboard_column_id',
				array($this->getID())
			);
		if (!$res) {
			$this->setError(_('Taskboard')._(': ')._('Cannot get used resolutions'));
			return false;
		}

		$resolutions= array();
		while($row = db_fetch_array($res)) {
			$resolutions[$row['taskboard_column_resolution']] = $row['taskboard_column_resolution'];
		}
		db_free_result($res);

		return array_keys($resolutions);
	}

	/**
	 * getUnusedResolutions - get list of resolutions that are not used with taskboard columns
	 *
	 * @return	array
	 */
	function getUnusedResolutions() {
		static $resolutions = NULL;

		if( !$resolutions ) {
			$all_resolutions = $this->getAvailableResolutions();
			$used_resolutions = $this->getUsedResolutions();

			$resolutions = array();
			if( $all_resolutions && count( $used_resolutions ) > 0 ) {
				$resolutions = array_diff( $all_resolutions, $used_resolutions );
			} else {
				$resolutions = $all_resolutions;
			}
		}

		return $resolutions;
	}
}
