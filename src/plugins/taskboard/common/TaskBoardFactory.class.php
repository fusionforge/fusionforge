<?php
/**
 * Copyright 2016, Stéphane-Eymeric Bredthauer - TrivialDev
 *
 * This file is part of FusionForge. FusionForge is free software;
 * you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or (at your option)
 * any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with FusionForge; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

global $gfplugins;
require_once $gfcommon.'include/FFError.class.php';
require_once $gfplugins.'taskboard/common/TaskBoard.class.php';

class TaskboardFactory extends FFError {

	/**
	 * The Group object.
	 *
	 * @var	 object  $Group.
	 */
	var $Group;

	/**
	 * The Taskboards array.
	 *
	 * @var	 array	Taskboards.
	 */
	var $Taskboards;

	/**
	 * @param	Group	$Group The Group object to which this ArtifactTypeFactory is associated
	 */
	function __construct(&$Group, $skip_check=false) {
		parent::__construct();
		if (!$Group || !is_object($Group)) {
			$this->setError(_('Invalid Project'));
			return;
		}
		if ($Group->isError()) {
			$this->setError('TaskboardFactory: '.$Group->getErrorMessage());
			return;
		}
		if (!$skip_check && !$Group->usesTracker()) {
			$this->setError(sprintf(_('%s does not use the Tracker tool'),
			    $Group->getPublicName()));
			return;
		}
		$this->Group =& $Group;
	}

	/**
	 * getGroup - get the Group object this TaskboardFactory is associated with.
	 *
	 * @return	object	The Group object.
	 */
	function &getGroup() {
		return $this->Group;
	}

	/**
	 * getAllTaskboardIds - return a list of taskboards ids.
	 *
	 * @return	array	The array of Taskboard object ids.
	 */
	function &getAllTaskboardIds() {
		$result = array () ;
		$res = db_query_params ('SELECT taskboard_id FROM plugin_taskboard
			WHERE group_id=$1
			ORDER BY taskboard_id ASC',
					array ($this->Group->getID())) ;
		if (!$res) {
			return $result ;
		}
		while ($arr = db_fetch_array($res)) {
			$result[] = $arr['taskboard_id'] ;
		}
		return $result ;
	}

	/**
	 * getTaskboards - return an array of Taskboard objects.
	 *
	 * @return	array	The array of Taskboard objects.
	 */
	function getTaskboards() {
		if ($this->Taskboards) {
			return $this->Taskboards;
		}

		$this->Taskboards = array () ;
		$ids = $this->getAllTaskboardIds() ;

		foreach ($ids as $id) {
			$taskboard = new Taskboard($this->Group, $id);
			if($taskboard->isError()) {
				$this->setError($taskboard->getErrorMessage());
			} else {
				$this->Taskboards[] = $taskboard;
			}
		}
		return $this->Taskboards;
	}
}
