<?php
/**
 * FusionForge Effort Unit Set
 *
 * Copyright 2017, Stéphane-Eymeric Bredthauer - TrivialDev
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

require_once $gfcommon.'include/FFError.class.php';
require_once $gfcommon.'include/Group.class.php';

define('EFFORTUNITSET_FORGE_LEVEL', 1);
define('EFFORTUNITSET_PROJECT_LEVEL', 2);
define('EFFORTUNITSET_TRACKER_LEVEL', 3);

class EffortUnitSet extends FFError {
	var $Object;
	/**
	 * The Group object.
	 *
	 * @var	 object  $Group.
	 */
	var $Group;

	/**
	 * Artifact Type object.
	 *
	 * @var	object	$ArtifactType.
	 */
	var $ArtifactType;

	var $data_array;

	var $objectLevel;

	/**
	 * @param	Object	$Object Null, group, tracker object to which this EffortUnitSet is associated
	 */
	function __construct(&$Object=null, $unit_set_id=false, $arr=false) {
		parent::__construct();
		$this->Object = $Object;
		if (!$Object) {
			$class ='';
		} else {
			$class = get_class($Object);
		}
		switch ($class) {
			case 'ArtifactType':
			case 'ArtifactTypeHtml':
				if (!$Object || !is_object($Object)) {
					$this->setError(_('Invalid Artifact Type'));
					return;
				}
				if ($Object->isError()) {
					$this->setError(_('Effort Unit Set')._(':').' '.$Object->getErrorMessage());
					return;
				}
				$this->ArtifactType = $Object;
				$this->Group = $Object->getGroup();
				$this->objectLevel = EFFORTUNITSET_TRACKER_LEVEL;
				break;
			case 'Group':
				if (!$Object || !is_object($Object)) {
					$this->setError(_('Invalid Project'));
					return;
				}
				if ($Object->isError()) {
					$this->setError(_('Effort Unit Set')._(':').' '.$Object->getErrorMessage());
					return;
				}
				$this->ArtifactType = null;
				$this->Group = $Object;
				$this->objectLevel = EFFORTUNITSET_PROJECT_LEVEL;
				break;
			case '':
				$this->ArtifactType = null;
				$this->Group = null;
				$this->objectLevel = EFFORTUNITSET_FORGE_LEVEL;
				break;
		}
		if ($unit_set_id) {
			if (!$arr || !is_array($arr)) {
				if (!$this->fetchData($unit_set_id)) {
					return;
				}
			} else {
				$this->data_array =& $arr;
			}
		}
		return;
	}

	function create($importData = array()) {
		if(array_key_exists('user', $importData)){
			$user = $importData['user'];
		} else {
			$user = ((session_loggedin()) ? user_getid() : 100);
		}
		if (array_key_exists('time',$importData)){
			$time = $importData['time'];
		} else {
			$time = time();
		}
		// Only Effort Unit Set of the object level can be created as a new one
		switch ($this->objectLevel){
			case EFFORTUNITSET_TRACKER_LEVEL:
				$res = db_query_params('SELECT 1 FROM effort_unit_set WHERE group_id=$1 AND group_artifact_id=$2',
						array($this->Group->getID(), $this->ArtifactType->getID()));
				if (!$res) {
					$this->setError(_('Error checking if Effort Unit Set already exist')._(':').' '.db_error());
					db_rollback();
					return false;
				}
				if (db_numrows($res)>0) {
					$this->setError(sprintf(_('Effort Unit Set already exist for tracker $s'),$this->ArtifactType->getName()));
					db_rollback();
					return false;
				}
				$query ='INSERT INTO effort_unit_set(level, group_id, group_artifact_id, created_date, created_by) VALUES($1, $2, $3, $4, $5)';
				$params = array(EFFORTUNITSET_TRACKER_LEVEL, $this->Group->getID(), $this->ArtifactType->getID(), $time, $user);
				break;
			case EFFORTUNITSET_PROJECT_LEVEL:
				$res = db_query_params('SELECT 1 FROM effort_unit_set WHERE group_id=$1',
						array($this->Group->getID()));
				if (!$res) {
					$this->setError(_('Error checking if Effort Unit Set already exist')._(':').' '.db_error());
					db_rollback();
					return false;
				}
				if (db_numrows($res)>0) {
					$this->setError(sprintf(_('Effort Unit Set already exist for project $s'),$this->Group->getPublicName()));
					db_rollback();
					return false;
				}
				$query ='INSERT INTO effort_unit_set(level, group_id, created_date, created_by) VALUES($1, $2, $3, $4)';
				$params = array(EFFORTUNITSET_PROJECT_LEVEL, $this->Group->getID(), $time, $user);
				break;
			case EFFORTUNITSET_FORGE_LEVEL:
			default:
				$this->setError(_('Effort Unit Set already exist for this forge'));
				return false;
				break;
		}
		db_begin();
		$res = db_query_params($query,$params);
		$id = db_insertid($res, 'effort_unit_set', 'unit_set_id');

		if (!$res || !$id) {
			$this->setError(_('Effort Unit Set')._(':').' '.db_error());
			db_rollback();
			return false;
		} elseif (!$this->fetchData($id)) {
			db_rollback();
			return false;
		} else {
			db_commit();
			return $id;
		}
	}

	function copy($from_unit_set){
		$unit_set_id = $this->create();
		$from_unit_factory = new EffortUnitFactory($from_unit_set);
		$from_baseUnit = $from_unit_factory->getBaseUnit();
		//$from_baseUnit_id = $from_baseUnit->getID();
		if (!$this->_recursive_copy($from_baseUnit)) {
			return false;
		}
		switch ($this->objectLevel) {
			case EFFORTUNITSET_PROJECT_LEVEL:
				$this->Group->setEffortUnitSet($this->getID());
				break;
			case EFFORTUNITSET_TRACKER_LEVEL:
				$this->ArtifactType->setEffortUnitSet($this->getID());
				break;
		}
		return true;
	}

	function _recursive_copy($from_unit) {
		$new_unit = new EffortUnit($this);
		$new_unit_id = $new_unit->copy($from_unit);
		$from_unit_set = $from_unit->getEffortUnitSet();
		$from_unit_factory = new EffortUnitFactory($from_unit_set);
		$units = $from_unit_factory->getUnits();
		foreach ($units as $unit) {
			if ($unit->getToUnit()==$from_unit->getID() && $unit->getID()!=$from_unit->getID()) {
				$this->_recursive_copy($unit);
			}
		}
		return true;
	}

	/**
	 * fetchData - May need to refresh database fields if an update occurred.
	 *
	 * @param	int	$group_id The group_id.
	 * @return	bool	success or not
	 */
	function fetchData($unit_set_id) {
		$res = db_query_params ('SELECT * FROM effort_unit_set WHERE unit_set_id=$1',
				array($unit_set_id));
		if (!$res || db_numrows($res) < 1) {
			$this->setError(sprintf('fetchData(): %s', db_error()));
			return false;
		}
		$this->data_array = db_fetch_array($res);
		return true;
	}

	/**
	 * getID - Simply return the unit_set_id for this object.
	 *
	 * @return int unit_set_id.
	 */
	function getID() {
		return $this->data_array['unit_set_id'];
	}

	function getLevel() {
		return $this->data_array['level'];
	}

	function getObjectLevel() {
		return $this->objectLevel;
	}

	/**
	 * getGroup - get the Group object this EffortUnitFactory is associated with.
	 *
	 * @return	object	The Group object.
	 */
	function &getGroup() {
		return $this->Group;
	}

	function &getArtifactType() {
		return $this->ArtifactType;
	}

	function &getObject() {
		return $this->Object;
	}
}

function getAvailableEffortUnitSets(&$Object=null) {
	if (!$Object) {
		$class ='';
	} else {
		$class = get_class($Object);
	}
	switch ($class) {
		case 'ArtifactType':
		case 'ArtifactTypeHtml':
			if (!$Object || !is_object($Object)) {
				$this->setError(_('Invalid Artifact Type'));
				return;
			}
			if ($Object->isError()) {
				$this->setError(_('Effort Unit Set')._(':').' '.$Object->getErrorMessage());
				return;
			}
			$atid = $Object->getID();
			$at_name = $Object->getName();
			$group_id = $Object->getGroup()->getID();
			$group_name = $Object->getGroup()->getPublicName();
			$objectLevel = EFFORTUNITSET_TRACKER_LEVEL;
			break;
		case 'Group':
			if (!$Object || !is_object($Object)) {
				$this->setError(_('Invalid Project'));
				return;
			}
			if ($Object->isError()) {
				$this->setError(_('Effort Unit Set')._(':').' '.$Object->getErrorMessage());
				return;
			}
			$group_id = $Object->getID();
			$group_name = $Object->getPublicName();
			$objectLevel = EFFORTUNITSET_PROJECT_LEVEL;
			break;
		case '':
			$objectLevel = EFFORTUNITSET_FORGE_LEVEL;
			break;
	}
	$effortUnitSets = array();
	switch($objectLevel) {
		case EFFORTUNITSET_TRACKER_LEVEL:
			$res = db_query_params ('SELECT unit_set_id FROM effort_unit_set WHERE group_id=$1 AND group_artifact_id=$2 AND level=$3',
					array($group_id, $atid, EFFORTUNITSET_TRACKER_LEVEL));
			if (!$res) {
				$this->setError(sprintf(_('Error getting Tracker "%s" level Effort Unit Set'),$at_name), db_error());
				return false;
			}
			if (db_numrows($res)>0) {
				$row = db_fetch_array($res);
				$effortUnitSets[$row['unit_set_id']]= sprintf(_('Tracker "%s" level Effort Unit Set'),$at_name);
			}
			// no break
		case EFFORTUNITSET_PROJECT_LEVEL:
			$res = db_query_params ('SELECT unit_set_id FROM effort_unit_set WHERE group_id=$1 AND group_artifact_id IS NULL AND level=$2',
					array($group_id, EFFORTUNITSET_PROJECT_LEVEL));
			if (!$res) {
				$this->setError(sprintf(_('Error getting Project "%s" level Effort Unit Set'),$group_name), db_error());
				return false;
			}
			if (db_numrows($res)>0) {
				$row = db_fetch_array($res);
				$effortUnitSets[$row['unit_set_id']]= sprintf(_('Project "%s" level Effort Unit Set'),$group_name);
			}
			// no break
		case EFFORTUNITSET_FORGE_LEVEL:
			$res = db_query_params ('SELECT unit_set_id FROM effort_unit_set WHERE group_id IS NULL AND group_artifact_id IS NULL AND level=$1',
					array(EFFORTUNITSET_FORGE_LEVEL));
			if (!$res) {
				$this->setError(_('Error getting Forge level Effort Unit Set'), db_error());
				return false;
			}
			if (db_numrows($res)>0) {
				$row = db_fetch_array($res);
				$effortUnitSets[$row['unit_set_id']]= _('Forge level Effort Unit Set');
			}
	}
	return $effortUnitSets;
}
