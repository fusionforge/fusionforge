<?php
/**
 * Copyright (C) 2015 Vitaliy Pylypiv <vitaliy.pylypiv@gmail.com>
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

require_once $gfcommon.'include/Error.class.php';
require_once $gfplugins.'taskboard/common/TaskBoard.class.php';

class TaskBoardRelease extends Error {
	/**
	 * The Taskboard object.
	 *
	 * @var	object	$Taskboard.
	 */
	var $Taskboard; //taskboard object


	/**
	  * Array of release data.
	 *
	 * @var	array	$data_array.
	 */
	var $data_array;

	/**
	 * release title (name of related extra field element)
	 *
	 * @var	string	$title.
	 */
	private $_title = NULL;


	/**
	 * Constructor
	 */
	function TaskBoardRelease($Taskboard, $arr = false) {
		$this->Error();
		if (!$Taskboard || !is_object($Taskboard)) {
			$this->setError('No Valid Taskboard Object');
			return false;
		}
		if ($Taskboard->isError()) {
			$this->setError('TaskBoardRelease: '.$Taskboard->getErrorMessage());
			return false;
		}

		$this->Taskboard = $Taskboard;
		if (!$arr || !is_array($arr)) {
			if (!$this->fetchData($arr)) {
				return false;
			}
		} else {
			$this->data_array =& $arr;
			if ($this->data_array['taskboard_id'] != $this->Taskboard->getID()) {
				$this->setError('taskboard_id in db result does not match TaskBoard Object');
				$this->data_array = null;
				return false;
			}
		}
	}

	/**
	 * create - create taskboard release
	 *
	 * @return	boolean
	 */
	function create($element_id, $start_date, $end_date, $goals, $page_url) {
		$res = db_query_params (
				'INSERT INTO plugin_taskboard_releases(taskboard_id, element_id, start_date, end_date, goals, page_url)
				 VALUES($1, $2, $3, $4, $5, $6)',
				array(
					$this->Taskboard->getID(),
					$element_id,
					$start_date,
					$end_date,
					$goals,
					$page_url
				)
		) ;
		if (!$res) {
			return false;
		}
		db_free_result($res);

		return true;
	}

	/**
	 * update - update taskboard release
	 *
	 * @return	boolean
	 */
	function update($element_id, $start_date, $end_date, $goals, $page_url) {
		$res = db_query_params(
			'UPDATE plugin_taskboard_releases SET element_id=$1, start_date=$2, end_date=$3, goals=$4, page_url=$5 WHERE taskboard_release_id=$6',
			array(
				$element_id,
				$start_date,
				$end_date,
				$goals,
				$page_url,
				$this->getID()
			)
		);
		if (!$res) {
			return false;
		}
		db_free_result($res);

		return true;
	}

	function delete(){
		$res = db_query_params(
			'DELETE FROM plugin_taskboard_releases_snapshots WHERE taskboard_release_id=$1',
			array($this->getID())
		) ;

		if (!$res) {
			return false;
		}

		$res = db_query_params(
			'DELETE FROM plugin_taskboard_releases WHERE taskboard_release_id=$1',
			array($this->getID())
		) ;
		if (!$res) {
			return false;
		}

		return true;
	}

	/**
	 * fetchData - re-fetch the data for this TaskBoardColumn from the database.
	 *
	 * @param	int	The taskboard column ID.
	 * @return	boolean	success.
	 */
	function fetchData($id) {
		$res = db_query_params('SELECT * FROM plugin_taskboard_releases WHERE taskboard_release_id=$1', array ($id));
		if (!$res || db_numrows($res) < 1) {
			$this->setError('TaskBoard: Invalid TaskBoardReleaseID');
			return false;
		}
		$this->data_array = db_fetch_array($res);
		db_free_result($res);
		return true;
	}

	/**
	 * getID - get this TaskBoardReleaseID.
	 *
	 * @return	int	The taskboard_release_id
	 */
	function getID() {
		return $this->data_array['taskboard_release_id'];
	}

	/**
	 * getTaskboardID - get related TaskBoard ID.
	 *
	 * @return	int	The taskboard_id
	 */
	function getTaskBoardID() {
		return $this->data_array['taskboard_id'];
	}

	/**
	 * getElementID - get related element ID.
	 *
	 * @return	int	The element_id
	 */
	function getElementID() {
		return $this->data_array['element_id'];
	}

	/**
	 * getTitle - get release title
	 *
	 * @return	string
	 */
	function getTitle() {
		if( !$this->_title ) {
			// retrieve element name
			$releases = $this->Taskboard->getReleaseValues();
			foreach($releases as $release_name => $release_id) {
				if($release_id == $this->getElementID()) {
					$this->_title = $release_name;
				}
			}
		}

		return $this->_title;
	}

	/**
	 * getStartDate - get release start date as unixtime
	 *
	 * @return	integer
	 */
	function getStartDate() {
		return $this->data_array['start_date'];
	}

	/**
	 * getEndDate - get release end date as unixtime
	 *
	 * @return	integer
	 */
	function getEndDate() {
		return $this->data_array['end_date'];
	}

	/**
	 * getGoals - get release goals
	 *
	 * @return	string
	 */
	function getGoals() {
		return $this->data_array['goals'];
	}

	/**
	 * getPageUrl - get release page URL
	 *
	 * @return	string
	 */
	function getPageUrl() {
		return $this->data_array['page_url'];
	}

	/**
	 * Save current taskboard snapshot. So, we can have a history of release implementation,
	 * that could be used for different indicators calculation.
	 *
	 * @param	int	Snapshot unix date time
	 * @return	boolean	success.
	 */
	function saveSnapshot($snapshot_datetime) {
		$user_stories = $this->Taskboard->getUserStories($this->getTitle());		
		$columns = $this->Taskboard->getColumns($this->getTitle());

		$_columns_num = count($columns);
		$_completed_user_stories = 0;
 		$_completed_tasks = 0;
		$_completed_story_points = 0;
		$_completed_man_days = 0;
		
		foreach( $user_stories as $us ) {
			$completed_us = true;

			for($i=0; $i < $_columns_num ; $i++ ) {
				foreach( $us['tasks'] as $tsk ) {
					if( $tsk['phase_id'] == $columns[$i]->getID() ) {
						if( $i + 1 == $_columns_num ) {
							// last column, so- completed task
							$_completed_tasks++;
						} else {
							// incomplete task, so incomplete US
							$completed_us = false;
						}
					}
				}
				
			}
			
			if( $completed_us ) {
				$_completed_user_stories++;
				// TODO $_completed_story_points += ...
			}
		}
		
		
		$res = db_query_params(
				'SELECT taskboard_release_snapshot_id  FROM plugin_taskboard_releases_snapshots WHERE taskboard_release_id=$1 AND snapshot_date=$2', 
				array ($this->getID(), $snapshot_datetime )
		);

		if (!$res) {
			$this->setError('TaskBoardRelease: Cannot get release snapshot');
			return false;
		}
		
		$row = db_fetch_array($res);
		db_free_result($res);
		
		if( $row ) {
			$res = db_query_params(
					'UPDATE plugin_taskboard_releases_snapshots 
					SET completed_user_stories=$1, completed_tasks=$2, completed_story_points=$3, completed_man_days=$4
					WHERE taskboard_release_snapshot_id=$5',
					array(
							$_completed_user_stories,
							$_completed_tasks,
							0, //TODO
							0, //TODO
							intval($row['taskboard_release_snapshot_id'])
					)
			);
			if (!$res) {
				return false;
			}
			db_free_result($res);
		} else {
			$res = db_query_params(
					'INSERT INTO plugin_taskboard_releases_snapshots(taskboard_release_id, snapshot_date, completed_user_stories, completed_tasks, completed_story_points, completed_man_days)
					VALUES($1,$2,$3,$4,$5,$6)',
					array(
							$this->getID(),
							$snapshot_datetime,
							$_completed_user_stories,
							$_completed_tasks,
							0, //TODO
							0 //TODO
					)
			);
			if (!$res) {
				return false;
			}
			db_free_result($res);
		}

		return true;
	}
}
