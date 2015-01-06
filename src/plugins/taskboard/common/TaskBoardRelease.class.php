<?php
/**
 *
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
	 * @var         object  $Taskboard.
	 */
	var $Taskboard; //taskboard object


	/**
	  * Array of release data.
	 *
	 * @var         array   $data_array.
	 */
	var $data_array;
	
	/**
	 * release title (name of related extra field element)  
	 *
	 * @var         string   $title.
	 */
	private $_title=NULL;


	/*
	 * Constructor
	 */
	function TaskBoardRelease($Taskboard,$arr=false) {
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
	 *      create - create taskboard release
	 *
	 *      @return boolean
	 */
	function create( $element_id, $start_date, $end_date, $goals, $page_url ) {
		$res = db_query_params (
				'INSERT INTO plugin_taskboard_releases(taskboard_id, element_id, start_date, end_date, goals, page_url)
				 VALUES($1,$2,$3,$4,$5,$6)',
				array (
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
	 *      update - update taskboard release
	 *
	 *      @return boolean
	 */
	function update( $element_id, $start_date, $end_date, $goals, $page_url  ) {
		$res = db_query_params (
			'UPDATE plugin_taskboard_releases SET element_id=$1, start_date=$2, end_date=$3, goals=$4, page_url=$5 WHERE taskboard_release_id=$6',
			array (
				$element_id,
				$start_date, 
				$end_date,
				$goals, 
				$page_url,
				$this->getID()
			)
		) ;
		if (!$res) {
			return false;
		}
		db_free_result($res);

		return true;
	}

	function delete(){
		$res = db_query_params (
			'DELETE FROM plugin_taskboard_releases_snapshots WHERE taskboard_release_i=$1',
			array ( $this->getID() )
		) ;

		if (!$res) {
			return false;
		}

		$res = db_query_params (
			'DELETE FROM plugin_taskboard_releases WHERE taskboard_column_id=$1',
			array ( $this->getID() )
		) ;
		if (!$res) {
			return false;
		}

		return true;
	}

	/**
	 *      fetchData - re-fetch the data for this TaskBoardColumn from the database.
	 *
	 *      @param  int             The taskboard column ID.
	 *      @return boolean success.
	 */
	function fetchData($id) {
		$res = db_query_params ('SELECT * FROM plugin_taskboard_releases WHERE taskboard_release_id=$1', array ($id)) ;
		if (!$res || db_numrows($res) < 1) {
			$this->setError('TaskBoard: Invalid TaskBoardReleaseID');
			return false;
		}
		$this->data_array = db_fetch_array($res);
		db_free_result($res);
		return true;
	}

	/**
	 *      getID - get this TaskBoardReleaseID.
	 *
	 *      @return int     The taskboard_release_id
	 */
	function getID() {
		return $this->data_array['taskboard_release_id'];
	}

	/**
	 *      getTaskboardID - get related TaskBoard ID.
	 *
	 *      @return int     The taskboard_id
	 */
	function getTaskBoardID() {
		return $this->data_array['taskboard_id'];
	}
	
	/**
	 *      getElementID - get related element ID.
	 *
	 *      @return int     The element_id
	 */
	function getElementID() {
		return $this->data_array['element_id'];
	}

	/**
	 *      getTitle - get release title
	 *
	 *      @return string
	 */
	function getTitle() {
		if( !$this->_title ) {
			// retrieve element name
			$releases = $this->Taskboard->getReleaseValues();
			foreach( $releases as $release_name => $release_id ) {
				if(  $release_id == $this->getElementID() ) {
					$this->_title = $release_name;
				}
			}
		}
		
		return $this->_title;
	}

	/**
	 *      getStartDate - get release start date as unixtime
	 *
	 *      @return integer
	 */
	function getStartDate() {
		return $this->data_array['start_date'];
	}
	
	/**
	 *      getEndDate - get release end date as unixtime
	 *
	 *      @return integer
	 */
	function getEndDate() {
		return $this->data_array['end_date'];
	}
	
	/**
	 *      getGoals - get release goals
	 *
	 *      @return string
	 */
	function getGoals() {
		return $this->data_array['goals'];
	}
	
	/**
	 *      getPageUrl - get release page URL
	 *
	 *      @return string
	 */
	function getPageUrl() {
		return $this->data_array['page_url'];
	}
}

