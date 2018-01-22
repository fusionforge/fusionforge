<?php
/**
 * Copyright (C) 2013 Vitaliy Pylypiv <vitaliy.pylypiv@gmail.com>
 * Copyright 2018, Franck Villaume - TrivialDev
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

global $gfcommon;
require_once $gfcommon.'tracker/ArtifactType.class.php';
require_once $gfcommon.'tracker/ArtifactTypeFactory.class.php';
require_once $gfcommon.'tracker/ArtifactFactory.class.php';
require_once $gfcommon.'tracker/ArtifactExtraField.class.php';

class TaskBoardBasicAdapter extends FFError {
	/**
	 * The TaskBoard object.
	 *
	 * @var	object	$TaskBoard.
	 */
	var $TaskBoard;

	var $_atf = NULL; // artifact trackers factory
	var $_ust = NULL; // user stories tracker
	var $_tt = array(); // tasks trackers
	var $_fields = array();
	var $_elements = array(); // hash of extra fields values tracker_id => extra_field_id => element_name => element_id

	function TaskBoardBasicAdapter($TaskBoard) {
		$this->TaskBoard = $TaskBoard;
	}


	/**
	 * TODO - filters
	 */
	function getArtifactTypeFactory() {
		if( !$this->_atf ) {
			$this->_atf = new ArtifactTypeFactory($this->TaskBoard->Group);
		}

		return $this->_atf;
	}

	/**
	 * Get an instance of artifacts tracker, used for user stories tracking
	 */
	function getUserStoriesTracker() {
		if (!$this->_ust) {
			$this->_ust = new ArtifactType($this->TaskBoard->Group, $this->TaskBoard->getUserStoriesTrackerID());
		}

		return $this->_ust;
	}

	/**
	 * Get list of instances of artifacts trackers, used for tasks tracking
	 *
	 * @param	integer	group artifact identifier (primary key)
	 *
	 * @return	array
	 */
	function getTasksTracker($tracker_id) {
		if (!array_key_exists($tracker_id, $this->_tt)) {
			$this->_tt[$tracker_id] = new ArtifactType($this->TaskBoard->Group, $tracker_id);
		}

		return $this->_tt[$tracker_id];
	}

	/**
	 * Get list of instances of user stories artifacts
	 *
	 * @return	array|bool
	 */
	function getUserStories($release_value = NULL) {
		$at = $this->getUserStoriesTracker();
		$af = new ArtifactFactory($at);
		if (!$af || !is_object($af)) {
			$this->setError('Could Not Get Factory');
			return false;
		} elseif ($af->isError()) {
			$this->setError($af->getErrorMessage());
			return false;
		}

		$_status = 1;
		$extra_fields = array();

		if ($release_value) {
			$release_field_alias = $this->TaskBoard->getReleaseField();

			if ($release_field_alias) {
				$fields = $this->getFieldsIds($at->getID());

				if (array_key_exists($release_field_alias, $fields) ) {
					$extra_field_id = $fields[$release_field_alias];

					if ( $release_value == 100) {
						$extra_fields[$extra_field_id] = 100;
					} else {
						$elements = $this->getExtraFieldValues($at->getID(), $release_field_alias);
						if( array_key_exists($release_value, $elements) ) {
							$extra_fields[$extra_field_id] = $elements[$release_value];
						}
					}
				}
			}
		}

		$af->setup(NULL, NULL, NULL, NULL, 'agileboard', NULL, $_status, $extra_fields);

		return $af->getArtifacts();
	}

	/**
	 * Get an extra fields hash, where key is an extra field alias, and a value is an extra field identifier (primary key)
	 *
	 * @param	integer	group artifact identifier (primary key)
	 *
	 * @return	array
	 */
	function getFieldsIds($tracker_id) {
		$ret = array();

		if (!array_key_exists($tracker_id, $this->_fields)) {
			$at = $this->getTasksTracker($tracker_id);

			$extra_fields = $at->getExtraFields();
			foreach ($extra_fields as $f) {
				$ret[ $f['alias'] ] = $f['extra_field_id'];
			}
			$this->_fields[$tracker_id] = $ret;
		}

		return $this->_fields[$tracker_id];
	}

	/**
	 * Get a list of extra field elements.
	 * Item of the list is a hash, where key is an element name, and a value is an element identifier (primary key)
	 *
	 * @param	integer	group artifact identifier (primary key)
	 * @param	string	extra field alias
	 *
	 * @return	array
	 */
	function getExtraFieldValues($tracker_id, $field_alias) {
		$ret = array();

		$fields = $this->getFieldsIds($tracker_id);

		if (array_key_exists($field_alias, $fields)) {
			$extra_field_id = $fields[$field_alias];
			if ($extra_field_id ) {
				if (!array_key_exists($tracker_id, $this->_elements)) {
					$this->_elements[$tracker_id] = array();
				}

				if (!array_key_exists($extra_field_id, $this->_elements[$tracker_id])) {
					$this->_elements[$tracker_id][$extra_field_id] = array();
					$at = $this->getTasksTracker($tracker_id);

					$elements = $at->getExtraFieldElements($extra_field_id);
					foreach ($elements as $e) {
						if ($field_alias == 'resolution' && $e['status_id'] == 2) {
							continue;
						}
						$this->_elements[$tracker_id][$extra_field_id][$e['element_name']] = $e['element_id'];
					}
				}

				$ret = $this->_elements[$tracker_id][$extra_field_id];
			}
		}

		return $ret;
	}

	/**
	 * Get a list of task artifacts, linked to the give tracker according to the filter crtireria (assigned tech and sprint/release)
	 *
	 * @param	integer	group artifact identifier (primary key)
	 * @param	integer	optional identifier of assigned person
	 * @param	string	optional value (name) of sprint/release
	 *
	 * @return	array|bool
	 */
	function getTasks($tracker_id, $assigned_to = NULL, $release_value = NULL, $user_story_value = NULL) {
		$tasks = array();

		$at = $this->getTasksTracker($tracker_id);
		if ($at) {
			$af = new ArtifactFactory($at);
			if (!$af || !is_object($af)) {
				$this->setError('Could Not Get Factory');
				return false;
			} elseif ($af->isError()) {
				$this->setError($af->getErrorMessage());
				return false;
			}

			$_status = 1;
			$extra_fields = array();
			$fields = $this->getFieldsIds($tracker_id);

			if ($release_value) {
				$release_field_alias = $this->TaskBoard->getReleaseField();

				if ($release_field_alias) {
					$extra_field_id = $fields[$release_field_alias];

					if ($release_value == 100) {
						$extra_fields[$extra_field_id] = 100;
					} else {
						$elements = $this->getExtraFieldValues($tracker_id, $release_field_alias);
						if (array_key_exists($release_value, $elements)) {
							$extra_fields[$extra_field_id] = $elements[$release_value];
						}
					}
				}
			}

			if ($user_story_value) {
				$user_story_field_alias = $this->TaskBoard->getUserStoriesReferenceField();

				if ($user_story_field_alias) {
					$extra_field_id = $fields[$user_story_field_alias];
					$extra_fields[$extra_field_id] = $user_story_value;
				}
			}

			$af->setup(NULL, NULL, NULL, NULL, 'agileboard', $assigned_to, $_status, $extra_fields);

			$tasks = $af->getArtifacts();
		}

		return $tasks;
	}

	/**
	 * Get an instance of artifact
	 *
	 * @param	integer	artifact identifier (primary key)
	 *
	 * @return	object
	 */
	function getTask($task_id) {
		return artifact_get_object($task_id);
	}

	/**
	 * Create new task artifact
	 *
	 * @param	integer	group artifact identifier (primary key)
	 * @param	string	artifact summary
	 * @param	string	artifact description
	 * @param	integer	user story artifact identifier
	 * @param	string	release name
	 *
	 * @return	string	error message in case of fail
	 */
	function createTask($tracker_id, $title, $description, $user_story_id = null, $release_value = NULL, $assigned_to = 100, $priority = 3) {
		$tracker = $this->getTasksTracker($tracker_id);
		if ($tracker) {
			$artifact = new Artifact($tracker);

			$user_story_alias = $this->TaskBoard->getUserStoriesReferenceField();
			$release_alias = $this->TaskBoard->getReleaseField();
			$fields_ids = $this->getFieldsIds($tracker_id);
			$at = $this->getTasksTracker($tracker_id);
			$extra_fields = $at->getExtraFields();

			if (array_key_exists('resolution', $fields_ids)) {
				$elements = $this->getExtraFieldValues($tracker_id, 'resolution');
				$extra_fields[ $fields_ids['resolution'] ] = array_shift($elements);
			}

			// link create task to user story (if specified)
			if (!is_null($user_story_id) && $user_story_alias) {
				if(array_key_exists($user_story_alias, $fields_ids)) {
					$extra_fields[ $fields_ids[ $user_story_alias ] ] = ($user_story_id!=0 ? $user_story_id : '');
				}
			}

			// link create task to release (if specified)
			if ($release_value && $release_alias) {
				if(array_key_exists($release_alias, $fields_ids)) {
					$elements = $this->getExtraFieldValues($tracker_id, $release_alias);
					if(array_key_exists($release_value, $elements)) {
						$extra_fields[ $fields_ids[ $release_alias ] ] = $elements[$release_value];
					}
				}
			}

			$ret = $artifact->create($title, $description, $assigned_to, $priority, $extra_fields);

			if (!$ret) {
				return $artifact->getErrorMessage();
			}
		}

		return '';
	}


	/**
	 * Update existing task artifact
	 *
	 * @param	integer	group artifact identifier (primary key)
	 * @param	integer	identifier of assigned person
	 * @param	string	resolution value (name)
	 * @param	string	artifact summary
	 * @param	string	artifact description
	 *
	 * @return	string	error message in case of fail
	 */
	function updateTask(&$artifact, $assigned_to, $resolution, $title = NULL, $description = NULL, $remaining_cost = NULL) {
		if (!$assigned_to) {
			$assigned_to = $artifact->getAssignedTo();
		}

		$tracker_id = $artifact->ArtifactType->getID();
		$extra_fields = $artifact->getExtraFieldData();

		$fields_ids = $this->getFieldsIds($tracker_id);

		if (array_key_exists('resolution', $fields_ids)) {
			$elements = $this->getExtraFieldValues($tracker_id, 'resolution');
			$resolution_field_id = $fields_ids['resolution'];

			if (array_key_exists($resolution, $elements )){
				$extra_fields[ $resolution_field_id ] = $elements[$resolution];
			}
		}

		if($remaining_cost!==NULL) {
			$remaining_cost_alias = $this->TaskBoard->getRemainingCostField();

			if($remaining_cost_alias) {
				if (array_key_exists($remaining_cost_alias, $fields_ids)){
					$remaining_cost_field_id = $fields_ids[$remaining_cost_alias];
					$extra_fields[ $remaining_cost_field_id ] = $remaining_cost;
				}
			}
		}

		if (!$title) {
			$title = htmlspecialchars_decode($artifact->getSummary());
		}

		if (!$description) {
			$description = htmlspecialchars_decode($artifact->getDetails());
		}

		$ret = $artifact->update(
			$artifact->getPriority(),
			$artifact->getStatusId(),
			$assigned_to,
			$title,
			100,
			'',
			$tracker_id,
			$extra_fields,
			$description
			);

		$user_id = user_getid();
		if ($ret && ($user_id == $assigned_to)) {
			//$ret = $artifact->assignToMe();
			$res = db_query_params('UPDATE artifact SET assigned_to=$1 WHERE artifact_id=$2',
					array($user_id, $artifact->getID()));
			if (!$res) {
				return _('Error updating assigned_to in artifact')._(': ').db_error();
			}
		}

		if (!$ret) {
			return $artifact->getErrorMessage();
		}

		return '';
	}

	/**
	 * Returns true if current user can manage trackers
	 *
	 * @return	bool
	 */
	function isManager() {
		$ret = true;
		$tasks_trackers = $this->TaskBoard->getUsedTrackersData();
		foreach( $tasks_trackers as $tasks_tracker_data ) {
			if (!forge_check_perm('tracker', $tasks_tracker_data['group_artifact_id'], 'manager')) {
				$ret = false;
			}
		}
		return $ret;
	}

	/**
	 * Returns true if current user can modify artifacts
	 *
	 * @return	bool
	 */
	function isTechnician() {
		$ret = true;
		$tasks_trackers = $this->TaskBoard->getUsedTrackersData();
		foreach( $tasks_trackers as $tasks_tracker_data ) {
			if (!forge_check_perm('tracker', $tasks_tracker_data['group_artifact_id'], 'tech')) {
				$ret = false;
			}
		}
		return $ret;
	}

	/**
	 * Returns the html code for direct link to an artifact
	 *
	 * @param	object	$artifact	the artifact to link to.
	 *
	 * @return	string	html code.
	 */
	function getTaskUrl($artifact) {
		return util_make_url('/tracker/a_follow.php/'.$artifact->getID());
	}
}
