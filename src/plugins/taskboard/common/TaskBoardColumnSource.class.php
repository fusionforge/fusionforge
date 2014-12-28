<?php
/**
 *
 * Copyright (C) 2013 Vitaliy Pylypiv <vitaliy.pylypiv@gmail.com> 
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
require_once $gfplugins.'taskboard/common/TaskBoardColumn.class.php';

/**
 *       Factory method which creates a taskboard column frop rule for source and target taskboard columns ID
 *       
 *       @param int      The source taskboard column ID
 *       @param int      The target taskboard column ID
 *       @param array    The result array, if it's passed in
 *       @return object  TaskBoardColumnSource object
 */
function &taskboard_column_source_get_object($taskboard_source_column_id,$taskboard_target_column_id, $data=NULL) {
	if( !$data ) {
		$res = db_query_params ('SELECT * FROM plugin_taskboard_columns_sources i
			WHERE source_taskboard_column_id=$1 AND target_taskboard_column_id=$2', 
		array($taskboard_source_column_id,$taskboard_target_column_id) ) ;
		if (db_numrows($res) <1 ) {
			$data = array(
				'taskboard_column_source_id' => NULL,
				'target_taskboard_column_id' => $taskboard_target_column_id,
				'source_taskboard_column_id' => $taskboard_source_column_id,
				'target_resolution' => '',
				'alert' => NULL,
				'autoassign' => 0
			);
		} else {
			$data = db_fetch_array($res);
		}
	}

	$ColumnSource = new TaskBoardColumnSource($data);
	return $ColumnSource;
}




/**
 * Factory method which creates a taskboard column drop rule by default (if source is not defined)
 *       
 *       @param int      The target taskboard column ID
 *       @param array    The result array, if it's passed in
 *       @return object  TaskBoardColumnSource object
 */
function &taskboard_default_column_source_get_object($taskboard_target_column_id, $data=NULL) {
	if( !$data ) {
		$res = db_query_params ('SELECT * FROM plugin_taskboard_columns_sources 
			WHERE source_taskboard_column_id IS NULL AND target_taskboard_column_id=$1',
			array($taskboard_target_column_id) ) ;
		if (db_numrows($res) <1 ) {
			$data = array(
				'taskboard_column_source_id' => NULL,
				'target_taskboard_column_id' => $taskboard_target_column_id,
				'source_taskboard_column_id' => NULL,
				'target_resolution' => '',
				'alert' => NULL,
				'autoassign' => 0
			);
		} else {
			$data = db_fetch_array($res);
		}
	}

	$ColumnSource = new TaskBoardColumnSource($data);
	return $ColumnSource;
}

class TaskBoardColumnSource extends Error {
	/**
	 * The Taskboard object.
	 *
	 * @var         object  $Taskboard.
	*/
	var $Taskboard; //taskboard object


	/**
	 * Array of artifact data.
	 *
	 * @var         array   $data_array.
	 */
	var $data_array;
	

	function TaskBoardColumnSource($arr=false) {
		if (is_array($arr)) {
			$this->data_array =& $arr;
		}

		// check source and target columns 
	}

	/**
	 *      fetchData - re-fetch the data for this TaskBoardColumn from the database.
	 *
	 *      @param  int             The taskboard column ID.
	 *      @return boolean success.
	 */
	function fetchData($id) {
		$res = db_query_params ('SELECT * FROM plugin_taskboard_columns_sources WHERE taskboard_column_source_id=$1', array ($id)) ;
		if (!$res || db_numrows($res) < 1) {
			$this->setError('TaskBoard: Invalid TaskBoardColumnSourceID');
			return false;
		}
		$this->data_array = db_fetch_array($res);
		db_free_result($res);
		return true;
	}

	/**
	 *      getID - get this TaskBoardColumnSourceID.
	 *
	 *      @return int     The taskboard_column_source_id
	 */
	function getID() {
		return $this->data_array['taskboard_column_source_id'];
	}

	/**
	 *      getSourceColumnID - get source column ID
	 *
	 *      @return int     The taskboard_column_source_id
	 */
	function getSourceColumnID() {
		return $this->data_array['source_taskboard_column_id'];
	}

	/**
	 *      getTargetColumnID - get target column ID
	 *
	 *      @return int     The taskboard_column_target_id
	 */
	function getTargetColumnID() {
		return $this->data_array['target_taskboard_column_id'];
	}


	/**
	 *      getTargetResolution - get resolution, that should be assigned when card is droped from source column to the target one
	 *
	 *      @return string
	 */
	function getTargetResolution() {
		return $this->data_array['target_resolution'];
	}

	/**
	 *      getAlertText - get text, that should be shown when card is droped from source column to the target one
	 *
	 *      @return string
	 */
	function getAlertText() {
		return $this->data_array['alert'];
	}

	/**
	 *      getAutoassign - get autoassign flag. Current user is assigend to the task if autoassign is 1
	 *
	 *      @return int
	 */
	function getAutoassign() {
		return $this->data_array['autoassign'];
	}


	function save($target_resolution, $alert='', $autoassign=0) {

		if( $this->getSourceColumnID()  ) {
			$source_column_id = intval(  $this->getSourceColumnID() );
			$wsql = ' AND source_taskboard_column_id = '. $source_column_id ;
		} else {
			$source_column_id = 'NULL';
			$wsql = ' AND source_taskboard_column_id is NULL';
		}

		$res = db_query_params (
			'SELECT * FROM plugin_taskboard_columns_sources WHERE target_taskboard_column_id=$1'.$wsql,
			array ($this->getTargetColumnID())
		) ;
		if (!$res) {
			$this->setError('TaskBoardColumnSource: cannot save drop rule');
			return false;
		}
		$row = db_fetch_array($res);

		if( $row ) {
			// update rule
			$res = db_query_params (
				"UPDATE plugin_taskboard_columns_sources SET target_resolution=$1, alert=$2, autoassign=$3
				WHERE taskboard_column_source_id=$4",
				array (
					$target_resolution,
					$alert,
					$autoassign,
					$row['taskboard_column_source_id']
				)
			) ;
		} else {
			// insert rule
			$res = db_query_params (
				"INSERT INTO plugin_taskboard_columns_sources(target_taskboard_column_id, source_taskboard_column_id, target_resolution, alert, autoassign) 
				VALUES($1,$source_column_id,$2,$3,$4)",
				 array (
					$this->getTargetColumnID(),
					$target_resolution, 
					$alert, 
					$autoassign 
				)
			) ;
		}

		if( !$res ) {
			$this->setError('TaskBoardColumnSource: cannot save drop rule');
		}
	}

	/**
	 *      drop task
	 *
	 *      @return string    error message if cannot drop
	 */
	function drop(&$task) {
		$msg = '';

		$assigned_to = NULL;
		if( $this->getAutoassign() ) {
			$assigned_to = user_getid();
		}

		$msg = $this->getTaskboard()->TrackersAdapter->updateTask( $task,$assigned_to, $this->getTargetResolution() );
		if( $msg ) {
			$msg = _('Tracker error:') .' '.$msg;
		}

		return $msg;
	}

	function getTaskBoard() {
		if( !$this->Taskboard ) {
			$TargetColumn = taskboard_column_get_object( $this->getTargetColumnID() );
			$this->Taskboard = $TargetColumn->Taskboard;
		}

		return $this->Taskboard;
	}
}

