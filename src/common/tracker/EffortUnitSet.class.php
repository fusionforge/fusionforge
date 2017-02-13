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

	/**
	 * @var	 null|Group|ArtifactType	$Object	The object associated to this Effort Unit Set.
	 */
	private $Object;

	/**
	 * @var	 Group	$Group 	The ArtifactType's group or the group associated to this Effort Unit Set
	 */
	private $Group;

	/**
	 * @var	 ArtifactType	$ArtifactType 	The ArtifactType associated to this Effort Unit Set
	 */
	private $ArtifactType;

	/**
	 * @var	array	$data_array	Associative array of data from db.
	 */
	private $data_array;

	/**
	 * @var	integer	$objectLevel	The level (forge/projet/tracker) of the object $Object.
	 */
	private $objectLevel;

	/**
	 * EffortUnitSet - EffortUnitSet object constructor
	 *
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

	/**
	 * create - Create new Effort Unit Set in the database.
	 *
	 * @param	array	$importData			For import
	 * @return	bool	success or not
	 */
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
				$res = db_query_params('SELECT 1 FROM effort_unit_set WHERE group_id=$1 AND group_artifact_id=$2 AND level=$3',
						array($this->Group->getID(), $this->ArtifactType->getID(), EFFORTUNITSET_TRACKER_LEVEL));
				if (!$res) {
					$this->setError(_('Error checking if Effort Unit Set already exist')._(':').' '.db_error());
					return false;
				}
				if (db_numrows($res)>0) {
					$this->setError(sprintf(_('Effort Unit Set already exist for tracker %s'),$this->ArtifactType->getName()));
					return false;
				}
				$query ='INSERT INTO effort_unit_set(level, group_id, group_artifact_id, created_date, created_by) VALUES($1, $2, $3, $4, $5)';
				$params = array(EFFORTUNITSET_TRACKER_LEVEL, $this->Group->getID(), $this->ArtifactType->getID(), $time, $user);
				break;
			case EFFORTUNITSET_PROJECT_LEVEL:
				$res = db_query_params('SELECT 1 FROM effort_unit_set WHERE group_id=$1 AND group_artifact_id IS NULL AND level=$2',
						array($this->Group->getID(), EFFORTUNITSET_PROJECT_LEVEL));
				if (!$res) {
					$this->setError(_('Error checking if Effort Unit Set already exist')._(':').' '.db_error());
					return false;
				}
				if (db_numrows($res)>0) {
					$this->setError(sprintf(_('Effort Unit Set already exist for project %s'),$this->Group->getPublicName()));
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

	/**
	 * copy - Copy new Effort Unit Set from an other Effort Unit Set.
	 *
	 * @param	EffortUnitSet	$from_unit_set Effort Unit Set to be copied
	 * @return	bool	success or not
	 */
	function copy($from_unit_set){
		db_begin();
		$unit_set_id = $this->create();
		if (!$unit_set_id) {
			db_rollback();
			return false;
		}
		$from_unit_factory = new EffortUnitFactory($from_unit_set);
		$from_baseUnit = $from_unit_factory->getBaseUnit();

		if (!$this->recursiveUnitCopy($from_baseUnit)) {
			db_rollback();
			return false;
		}
		switch ($this->objectLevel) {
			case EFFORTUNITSET_PROJECT_LEVEL:
				if (!$this->Group->setEffortUnitSet($this->getID())) {
					$this->setError(_('Error setting new effort unit set for the group')._(':').' '.$this->Group->getErrorMessage());
					db_rollback();
					return false;
				}
				$atf = new ArtifactTypeFactory($this->Group);
				if (!$atf) {
					$this->setError(_('Error on new ArtifactTypeFactory'));
					db_rollback();
					return false;
				}
				if ($atf->isError()) {
					$this->setError(_('Error on new ArtifactTypeFactory')._(':').' '.$atf->getErrorMessage());
					db_rollback();
					return false;
				}
				$ats = $atf->getArtifactTypes();
				if (!empty($ats) && is_array($ats)) {
					foreach ($ats as $at) {
						if ($at->getEffortUnitSet() == $from_unit_set->getID()) {
							if (!$at->setEffortUnitSet($unit_set_id)) {
								$this->setError(_('Error on updating artifact type')._(':').' '.$at->getErrorMessage());
								db_rollback();
								return false;
							}
						}
					}
				}
				break;
			case EFFORTUNITSET_TRACKER_LEVEL:
				if (!$this->ArtifactType->setEffortUnitSet($unit_set_id)) {
					$this->setError(_('Error setting new effort unit set for the artifact type')._(':').' '.$this->ArtifactType->getErrorMessage());
					db_rollback();
					return false;
				}
				break;
		}
		db_commit();
		return true;
	}

	/**
	 * recursiveUnitCopy - Copy Effort Unit Set and units depending on.
	 *
	 * @param	EffortUnit	$from_unit Effort Unit to be copied
	 * @return	bool	success or not
	 */
	private function recursiveUnitCopy($from_unit) {
		db_begin();
		$new_unit = new EffortUnit($this);
		if (!$new_unit) {
			$this->setError(_('Error coping Effort Unit').' '.$from_unit->getName());
			db_rollback();
			return false;
		}
		$new_unit_id = $new_unit->copy($from_unit);
		if (!$new_unit_id || $new_unit->isError()) {
			$this->setError(_('Error coping Effort Unit').' '.$from_unit->getName()._(':').' '.$new_unit->getErrorMessage());
			db_rollback();
			return false;
		}
		$from_unit_set = $from_unit->getEffortUnitSet();
		$from_unit_factory = new EffortUnitFactory($from_unit_set);
		$units = $from_unit_factory->getUnits();
		foreach ($units as $unit) {
			if ($unit->getToUnit()==$from_unit->getID() && $unit->getID()!=$from_unit->getID()) {
				if (!$this->recursiveUnitCopy($unit)) {
					db_rollback();
					return false;
				}
			}
		}
		db_commit();
		return true;
	}

	/**
	 * fetchData - May need to refresh database fields if an update occurred.
	 *
	 * @param	integer	$group_id The group_id.
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
	 * @return integer unit_set_id.
	 */
	function getID() {
		return $this->data_array['unit_set_id'];
	}

	/**
	 * getLevel - return the level (forge/projet/tracker) at which the set is defined.
	 *
	 * @return integer level.
	 */
	function getLevel() {
		return $this->data_array['level'];
	}

	/**
	 * getObjectLevel - return the level of the object used.
	 *
	 * @return integer objectLevel.
	 */
	function getObjectLevel() {
		return $this->objectLevel;
	}

	/**
	 * getGroup - get the Group object this EffortUnitSet is associated with.
	 *
	 * @return	Group	The Group object.
	 */
	function &getGroup() {
		return $this->Group;
	}

	/**
	 * getArtifactType - get the ArtifactType object this EffortUnitSet is associated with.
	 *
	 * @return	ArtifactType	The ArtifactType object.
	 */
	function &getArtifactType() {
		return $this->ArtifactType;
	}

	/**
	 * getObject - get the Object this EffortUnitSet is associated with.
	 *
	 * @return	null|Group|ArtifactType	The Object.
	 */
	function &getObject() {
		return $this->Object;
	}

	/**
	 * isEquivalentTo - test if this unit set is equivalent to the other.
	 * @param	EffortUnitSet $effortUnitSet	Unit set to test
	 *
	 * @return	boolean	true/false.
	 */
	function isEquivalentTo($effortUnitSet){
		if ($this->getID() == $effortUnitSet->getID()) {
			return true;
		}
		$thisEffortUnitFactory = new EffortUnitFactory($this);
		$effortUnitFactory = new EffortUnitFactory($effortUnitSet);
		if (count($thisEffortUnitFactory)==count($effortUnitFactory)) {
			$thisUnits = $thisEffortUnitFactory->getUnits();
			$units = $effortUnitFactory->getUnits();
			foreach ($thisUnits as $thisUnit) {
				$found = false;
				foreach ($units as $unit) {
					if ($thisUnit->isEquivalentTo($unit)) {
						$found=true;
						break;
					}
				}
				if (!$found) {
					return false;
				}
			}
		} else {
			return false;
		}
		return true;
	}
}

/**
 * getAvailableEffortUnitSets - Get list of EffortUnitSets available for the object.
 * @param	null|Group|ArtifactType $Object	Object
 *
 * @return	array	list of EffortUnitSets.
 */
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
				return false;
			}
			if ($Object->isError()) {
				$this->setError(_('Effort Unit Set')._(':').' '.$Object->getErrorMessage());
				return false;
			}
			$at_name = $Object->getName();
			$group_name = $Object->getGroup()->getPublicName();
			$objectLevel = EFFORTUNITSET_TRACKER_LEVEL;
			break;
		case 'Group':
			if (!$Object || !is_object($Object)) {
				$this->setError(_('Invalid Project'));
				return false;
			}
			if ($Object->isError()) {
				$this->setError(_('Effort Unit Set')._(':').' '.$Object->getErrorMessage());
				return false;
			}
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
			$id = getEffortUnitSetForLevel($Object,EFFORTUNITSET_TRACKER_LEVEL);
			if ($id) {
				$effortUnitSets[$id] = sprintf(_('Tracker “%s” level Effort Unit Set'),$at_name);
			}
			// no break
		case EFFORTUNITSET_PROJECT_LEVEL:
			$id = getEffortUnitSetForLevel($Object,EFFORTUNITSET_PROJECT_LEVEL);
			if ($id) {
				$effortUnitSets[$id] = sprintf(_('Project “%s” level Effort Unit Set'),$group_name);
			}
			// no break
		case EFFORTUNITSET_FORGE_LEVEL:
			$id = getEffortUnitSetForLevel($Object,EFFORTUNITSET_FORGE_LEVEL);
			if ($id) {
				$effortUnitSets[$id] = _('Forge level Effort Unit Set');
			}
	}
	return $effortUnitSets;
}

/**
 * getAvailableEffortUnitSets - Get EffortUnitSet ID available for the object at this level.
 * @param	null|Group|ArtifactType $Object	Object
 * @param	integer $level	level
 *
 * @return	integer	EffortUnitSet ID.
 */
function getEffortUnitSetForLevel(&$Object,$level) {
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
				return false;
			}
			if ($Object->isError()) {
				$this->setError(_('Effort Unit Set')._(':').' '.$Object->getErrorMessage());
				return false;
			}
			$atid = $Object->getID();
			$at_name = $Object->getName();
			$group_id = $Object->getGroup()->getID();
			$group_name = $Object->getGroup()->getPublicName();
			break;
		case 'Group':
			if (!$Object || !is_object($Object)) {
				$this->setError(_('Invalid Project'));
				return false;
			}
			if ($Object->isError()) {
				$this->setError(_('Effort Unit Set')._(':').' '.$Object->getErrorMessage());
				return false;
			}
			$group_id = $Object->getID();
			$group_name = $Object->getPublicName();
			if ($level<EFFORTUNITSET_PROJECT_LEVEL) {
				return false;
			}
			break;
		case '':
			if ($level<EFFORTUNITSET_FORGE_LEVEL) {
				return false;
			}
			break;
	}
	switch($level) {
		case EFFORTUNITSET_TRACKER_LEVEL:
			$res = db_query_params ('SELECT unit_set_id FROM effort_unit_set WHERE group_id=$1 AND group_artifact_id=$2 AND level=$3',
			array($group_id, $atid, EFFORTUNITSET_TRACKER_LEVEL));
			if (!$res) {
				$this->setError(sprintf(_('Error getting Tracker “%s” level Effort Unit Set'),$at_name), db_error());
				return false;
			}
			if (db_numrows($res)>0) {
				$row = db_fetch_array($res);
				return $row['unit_set_id'];
			} else {
				return false;
			}
			break;
		case EFFORTUNITSET_PROJECT_LEVEL:
			$res = db_query_params ('SELECT unit_set_id FROM effort_unit_set WHERE group_id=$1 AND group_artifact_id IS NULL AND level=$2',
			array($group_id, EFFORTUNITSET_PROJECT_LEVEL));
			if (!$res) {
				$this->setError(sprintf(_('Error getting Project “%s” level Effort Unit Set'),$group_name), db_error());
				return false;
			}
			if (db_numrows($res)>0) {
				$row = db_fetch_array($res);
				return $row['unit_set_id'];
			} else {
				return false;
			}
			break;
		case EFFORTUNITSET_FORGE_LEVEL:
			$res = db_query_params ('SELECT unit_set_id FROM effort_unit_set WHERE group_id IS NULL AND group_artifact_id IS NULL AND level=$1',
			array(EFFORTUNITSET_FORGE_LEVEL));
			if (!$res) {
				$this->setError(_('Error getting Forge level Effort Unit Set'), db_error());
				return false;
			}
			if (db_numrows($res)>0) {
				$row = db_fetch_array($res);
				return $row['unit_set_id'];
			} else {
				return false;
			}
			break;
	}
	return false;
}
