<?php
/**
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

require_once $gfcommon.'include/FFError.class.php';
require_once $gfplugins.'taskboard/common/TaskBoard.class.php';
require_once $gfplugins.'taskboard/common/TaskBoardColumnSource.class.php';

/**
 * Factory method which creates a taskboard column from a taskboard column ID
 *
 * @param	int	The taskboard column ID
 * @param	array	The result array, if it's passed in
 * @return	object	TaskBoardColumn object
 */
function &taskboard_column_get_object($taskboard_column_id, $data = false) {
	static $taskboard_columns = array();

	if (!array_key_exists($taskboard_column_id, $taskboard_columns)) {
		$res = db_query_params('SELECT * FROM plugin_taskboard_columns WHERE taskboard_column_id = $1', array($taskboard_column_id));
		if (db_numrows($res) <1) {
			return false;
		}
		if ($data && is_array($data) && isset($data['taskboard_id'])) {
			// the db result handle was passed in
		} else {
			$data = db_fetch_array($res);
		}

		$Taskboard = &taskboard_get_object($data['taskboard_id']);
		$taskboard_columns[$taskboard_column_id] = new TaskBoardColumn($Taskboard, $data);
	}

	return $taskboard_columns[$taskboard_column_id];
}

/**
 * Factory method which creates a taskboard column by reolustion name
 *
 * @param	object	Taskboard
 * @param	string	Resolution label
 * @return	object	TaskBoardColumn object
 */
function &taskboard_column_get_object_by_resolution($taskboard, $resolution_label) {
	static $columns = array();

	if (!array_key_exists($taskboard->getID(), $columns)) {
		$columns[ $taskboard->getID() ] = array();
	}

	if (!array_key_exists($resolution_label, $columns[ $taskboard->getID()])) {
		$res = db_query_params(
				'SELECT C.* FROM plugin_taskboard_columns as C, plugin_taskboard_columns_resolutions as R
				WHERE C.taskboard_column_id = R.taskboard_column_id AND C.taskboard_id = $1 and taskboard_column_resolution = $2',
				array(
					$taskboard->getID(),
					$resolution_label
				)
			);
		if (db_numrows($res) <1) {
			$columns[$taskboard->getID()][$resolution_label] = false;
		} else {
			$data = db_fetch_array($res);

			$Column = new TaskBoardColumn($taskboard,$data);
			$columns[$taskboard->getID()][$resolution_label] = $Column;
		}
	}

	return $columns[$taskboard->getID()][$resolution_label];
}

class TaskBoardColumn extends FFError {
	/**
	 * The Taskboard object.
	 *
	 * @var	object	$Taskboard.
	 */
	var $Taskboard; //taskboard object


	/**
	  * Array of artifact data.
	 *
	 * @var	array	$data_array.
	 */
	var $data_array;

	var $drop_rules_by_default;

	var $drop_rules;

	function __construct($Taskboard, $arr = false) {
		parent::__construct();
		if (!$Taskboard || !is_object($Taskboard)) {
			$this->setError('No Valid Taskboard Object');
			return false;
		}
		if ($Taskboard->isError()) {
			$this->setError('TaskBoardColumn'._(': ').$Taskboard->getErrorMessage());
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
	 * update - update taskboard column
	 *
	 * @return	boolean
	 */
	function update($title, $title_bg_color, $column_bg_color, $max_tasks) {
		$res = db_query_params(
				'UPDATE plugin_taskboard_columns SET title = $1, title_background_color=$2, column_background_color=$3, max_tasks=$4 WHERE taskboard_column_id=$5',
				array(
					$title,
					$title_bg_color,
					$column_bg_color,
					intval($max_tasks),
					$this->getID()
				)
			);
		if (!$res) {
			return false;
		}
		db_free_result($res);

		if (!$this->fetchData($this->getID())) {
			return false;
		}
		return true;
	}

	/**
	 *
	 */
	function delete(){
		$res = db_query_params(
				'DELETE FROM plugin_taskboard_columns_resolutions WHERE taskboard_column_id = $1',
				array($this->getID())
			);
		if (!$res) {
			return false;
		}

		$res = db_query_params(
				'DELETE FROM plugin_taskboard_columns_sources WHERE target_taskboard_column_id = $1',
				array($this->getID())
			);
		if (!$res) {
			return false;
		}

		$res = db_query_params (
				'DELETE FROM plugin_taskboard_columns_sources WHERE source_taskboard_column_id = $1',
				array($this->getID())
			);
		if (!$res) {
			return false;
		}

		$res = db_query_params(
				'DELETE FROM plugin_taskboard_columns WHERE taskboard_column_id=$1',
				array($this->getID())
			);
		if (!$res) {
			return false;
		}

		// reorder other fields
		$res = db_query_params(
				'UPDATE plugin_taskboard_columns SET order_num=order_num-1 WHERE order_num>$1 AND taskboard_id=$2',
				array(
					$this->getOrder(),
					$this->getTaskBoardID()
				)
			);
		if (!$res) {
			return false;
		}

		return true;
	}

	/**
	 *
	 */
	function setOrder($order){
		// get columns having the given order
		$res = db_query_params(
				'UPDATE plugin_taskboard_columns SET order_num=$1 WHERE order_num=$2 AND taskboard_id=$3',
				array(
					$order-1,
					$order,
					$this->getTaskBoardID()
				)
			);
		if (!$res) {
			return false;
		}


		$res = db_query_params(
				'UPDATE plugin_taskboard_columns SET order_num=$1 WHERE taskboard_column_id=$2',
				array(
					$order,
					$this->getID()
				)
			);
		if (!$res) {
			return false;
		}
		db_free_result($res);

		return true;
	}

	/**
	 *
	 */
	function setResolutions($resolutions) {
		// clean current resolutions
		$res = db_query_params('DELETE FROM plugin_taskboard_columns_resolutions WHERE taskboard_column_id=$1', array($this->getID()));
		if (!$res) {
			return false;
		}

		// set new resolutions
		foreach($resolutions as $r) {
			$res = db_query_params('INSERT INTO plugin_taskboard_columns_resolutions(taskboard_column_id, taskboard_column_resolution) VALUES($1, $2)', array($this->getID(), $r));
			if (!$res) {
				return false;
			}
		}

		//TODO remove relations from other columns

		return true;
	}

	/**
	 * fetchData - re-fetch the data for this TaskBoardColumn from the database.
	 *
	 * @param	int	The taskboard column ID.
	 * @return	boolean	success.
	 */
	function fetchData($id) {
		$res = db_query_params('SELECT * FROM plugin_taskboard_columns WHERE taskboard_column_id = $1', array($id));
		if (!$res || db_numrows($res) < 1) {
			$this->setError('TaskBoard: Invalid TaskBoardColumnID');
			return false;
		}
		$this->data_array = db_fetch_array($res);
		db_free_result($res);
		return true;
	}

	/**
	 * getID - get this TaskBoardColumnID.
	 *
	 * @return	int	The taskboard_column_id
	 */
	function getID() {
		return $this->data_array['taskboard_column_id'];
	}

	/**
	 * getiTaskboardID - get related TaskBoard ID.
	 *
	 * @return int     The taskboard_id
	 */
	function getTaskBoardID() {
		return $this->data_array['taskboard_id'];
	}


	/**
	 * getDomID - get identifier for DOM on HTML page
	 *
	 * @return string     DOM identifier
	 */
	function getDomID() {
		$stops = array("'", '"', ',', '.', ':', ';' );
		return str_replace($stops, '', str_replace(' ', '-', strtolower($this->data_array['title'])));
	}

	/**
	 * getOrder - get order of columns on taskboard
	 *
	 * @return int
	 */
	function getOrder() {
		return $this->data_array['order_num'];
	}

	/**
	 * getTitle - get column title
	 *
	 * @return string
	 */
	function getTitle() {
		return $this->data_array['title'];
	}

	/**
	 * getTitleBackgroundColor - get column title background color
	 *
	 * @return string
	 */
	function getTitleBackgroundColor() {
		return $this->data_array['title_background_color'];
	}

	/**
	 * getColumnBackgroundColor - get column title background color
	 *
	 * @return string
	 */
	function getColumnBackgroundColor() {
		return $this->data_array['column_background_color'];
	}

	/**
	 * getMaxTasks - get maximum number of tasks, allowed for the column
	 *
	 * @return integer
	 */
	function getMaxTasks() {
		return $this->data_array['max_tasks'];
	}

	/**
	 * getResolutions
	 *
	 * @return array
	 */
	function getResolutions() {
		$res = db_query_params('SELECT * FROM plugin_taskboard_columns_resolutions WHERE taskboard_column_id=$1', array ($this->getID()));
		if (!$res) {
			$this->setError('TaskBoardColumn'._(': ')._('cannot get resolutions'));
			return false;
		}
		$resolutions= array();
		while($row = db_fetch_array($res)) {
			$resolutions[$row['taskboard_column_value_id']] = $row['taskboard_column_resolution'];
		}
		db_free_result($res);
		return $resolutions;
	}

	/**
	 * getDropRules
	 *
	 * @return array
	 */
	function getDropRules() {
		if (!$this->drop_rules) {
			$res = db_query_params(
					'SELECT * FROM plugin_taskboard_columns_sources WHERE target_taskboard_column_id=$1',
					array ($this->getID())
				);
			if (!$res) {
				$this->setError('TaskBoardColumn'._(': ')._('cannot get drop rules'));
				return false;
			}
			$this->drop_rules = array();
			while($row = db_fetch_array($res)) {
				if ($row['source_taskboard_column_id']) {
					$this->drop_rules[$row['source_taskboard_column_id']] = taskboard_column_source_get_object(
						$row['source_taskboard_column_id'],
						$row['target_taskboard_column_id'],
						$row
					);
				} else {
					// drop rule by default
					$this->drop_rules['*'] = taskboard_default_column_source_get_object($row['target_taskboard_column_id'], $row);

					if (!$this->drop_rules_by_default) {
						$this->drop_rules_by_default = $this->drop_rules['*'];
					}
				}
			}
			db_free_result($res);
		}

		return $this->drop_rules;
	}

	/**
	 * getDropRulesByDefault - TBC
	 *
	 * @param	boolean	$reread	default value is false
	 */
	function getDropRulesByDefault($reread = false) {
		if (!$this->drop_rules_by_default || $reread) {
			$this->drop_rules_by_default = taskboard_default_column_source_get_object($this->getID());
		}

		return $this->drop_rules_by_default;
	}

	/**
	 *
	 */
	function getResolutionByDefault() {
		if (!$this->getDropRulesByDefault()) {
			$this->setError('TaskBoardColumn'._(': ')._('cannot get resolution by default'));
			return false;
		}
		return $this->drop_rules_by_default->getTargetResolution();
	}


	/**
	 *
	 */
	function setDropRule($source_column_id, $target_resolution, $alert = '', $autoassign = 0) {
		if ($source_column_id) {
			$rule = taskboard_column_source_get_object($source_column_id, $this->getID());
		} else {
			$rule = $this->getDropRulesByDefault();
		}

		if ($rule) {
			$rule->save($target_resolution, $alert, $autoassign);
			if( $rule->isError() ) {
				$this->setError( $rule->getErrorMessage());
				return false;
			}
		} else {
			$this->setError('TaskBoardColumn'._(': ')._('cannot set drop rule'));
			return false;
		}

		return $rule;
	}
}
