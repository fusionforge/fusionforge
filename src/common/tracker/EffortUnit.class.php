<?php
/**
 * FusionForge Effort Units
 *
 * Copyright 2016, Stéphane-Eymeric Bredthauer
 * http://fusionforge.org
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
require_once $gfcommon.'tracker/EffortUnitSet.class.php';

class EffortUnit extends FFError {


	var $EffortUnitSet;
	/**
	 * Associative array of data from db.
	 *
	 * @var	array	$data_array.
	 */
	var $data_array;

	var $level;
	/**
	 * EffortUnit - EffortUnit object constructor
	 *
	 * @param	int|bool	$id	Required - Id of the Effort Unit you want to instantiate.
	 * @param	int|bool	$res	Database result from select query OR associative array of all columns.
	 */
	function __construct(&$EffortUnitSet, $id = false, $res = false) {
		parent::__construct();

		$this->EffortUnitSet = &$EffortUnitSet;

		if (!$id) {
			//setting up an empty object
			//probably going to call create()
			return;
		}
		if (!$res) {
			if (!$this->fetchData($id)) {
				return;
			}
		} else {
			//
			//	Assoc array was passed in
			//
			if (is_array($res)) {
				$this->data_array =& $res;
			} else {
				if (db_numrows($res) < 1) {
					//function in class we extended
					$this->setError(_('Effort Unit Not Found'));
					$this->data_array=array();
					return;
				} else {
					//set up an associative array for use by other functions
					$this->data_array = db_fetch_array_by_row($res, 0);
				}
			}
		}
	}

	/**
	 * fetchData - May need to refresh database fields if an update occurred.
	 *
	 * @param	int	$unit_id The unit_id.
	 * @return	bool	success or not
	 */
	function fetchData($unit_id) {
		$res = db_query_params ('SELECT * FROM effort_unit WHERE unit_id=$1',
				array($unit_id));
		if (!$res || db_numrows($res) < 1) {
			$this->setError(sprintf('fetchData(): %s', db_error()));
			return false;
		}
		$this->data_array = db_fetch_array($res);
		return true;
	}

	/**
	 * create - Create new Effort Unit in the database.
	 *
	 * @param	string	$name				The name of the unit.
	 * @param	integer	$conversion_factor	The conversion factor to define the current unit.
	 * @param	integer	$to_unit			The unity used for the definition of the current unit.
	 * @param	integre	$unit_position		The position of the unit when listed.
	 * @param	boolean	$is_base_unit		True if this unit is a base unit.
	 * @param	array	$importData			For import
	 * @return	bool	success or not
	 */
	function create($name, $conversion_factor, $to_unit, $unit_position = false, $is_base_unit = false, $importData = array()) {
		db_begin();
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
		if ($is_base_unit) {
			$to_unit = 1;
		}
		$res = db_query_params('INSERT INTO effort_unit(unit_set_id, unit_name, conversion_factor, to_unit, unit_position, is_base_unit, created_date, created_by, modified_date, modified_by) VALUES($1, $2, $3, $4, $5, $6, $7, $8, $9, $10)',
				array($this->EffortUnitSet->GetID(), $name, $conversion_factor, $to_unit, $unit_position, ($is_base_unit?1:0), $time, $user, $time, $user));
		if (!$res || db_affected_rows($res) < 1) {
			$this->setError(_('Error')._(':').' '._('Cannot create Effort Unit')._(':').' '.db_error());
			db_rollback();
			return false;
		}
		$id = db_insertid($res, 'effort_unit', 'unit_id');
		if (!$id) {
			$this->setError(_('Error')._(':').' '._('Cannot get Effort Unit id')._(':').' '.db_error());
			db_rollback();
			return false;
		}
		if (!$this->fetchData($id)) {
			db_rollback();
			return false;
		}
		if ($is_base_unit) {
			$res = db_query_params('UPDATE effort_unit SET to_unit = $1 WHERE unit_id = $2',
					array($id, $id));
			if (!$res || db_affected_rows($res) < 1) {
				$this->setError(_('Error')._(':').' '._('Cannot create Effort Unit')._(':').' '.db_error());
				db_rollback();
				return false;
			}
		}
		db_commit();
		return $id;
	}

	/**
	 * getID - get this Effort Unit ID.
	 *
	 * @return	integer	The unit_id.
	 */
	function getID() {
		return $this->data_array['unit_id'];
	}

	/**
	 * getName - get this Effort Unit name.
	 *
	 * @return	string	The name.
	 */
	function getName() {
		return $this->data_array['unit_name'];
	}

	/**
	 * getBase - get this Effort Unit base ID.
	 *
	 * @return	integer	unit base ID.
	 */
	function getToUnit() {
		return $this->data_array['to_unit'];
	}

	function getToUnitName() {
		if (!empty($this->data_array['to_unit'])) {
			$toUnit = new EffortUnit($this->EffortUnitSet, $this->data_array['to_unit']);
			return $toUnit->getName();
		} else {
			return '';
		}
	}
	/**
	 * getMultiplier - get this unit base multiplier.
	 *
	 * @return	float	unit base multiplier.
	 */
	function getConversionFactor() {
		return $this->data_array['conversion_factor'];
	}

	/**
	 * getPosition - get this Effort Unit display position.
	 *
	 * @return	integer	unit display position.
	 */
	function getPosition() {
		return $this->data_array['unit_position'];
	}

	function &getEffortUnitSet(){
		return $this->EffortUnitSet;
	}

	/**
	 * isDeleted - this Effort Unit is or not deleted.
	 *
	 * @return	boolean	is or not deleted.
	 */
	function isDeleted()  {
		return ($this->data_array['is_deleted']?true:false);
	}

	function isBaseUnit() {
		return ($this->data_array['is_base_unit']?true:false);
	}

	function delete($importData = array()) {
		db_begin();
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
		$res = db_query_params ('UPDATE effort_unit SET is_deleted=1, modified_date=$1, modified_by=$2 WHERE unit_id=$3',
				array ($time, $user, $this->getID())) ;
		if (!$res) {
			$this->setError(_('Error deleting Effort Unit')._(': ').db_error());
			db_rollback();
			return false;
		}
		$data_array['is_deleted'] = 1;
		db_commit();
		return true;
	}

	function update($name, $conversion_factor, $to_unit, $importData = array()){
		db_begin();
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
		$res = db_query_params('UPDATE effort_unit SET unit_name=$1, conversion_factor=$2, to_unit=$3, modified_date=$4, modified_by=$5 WHERE unit_id=$6',
				array($name, $conversion_factor, $to_unit, $time, $user, $this->getID()));
		if (!$res || db_affected_rows($res) < 1) {
			$this->setError(_('Error')._(':').' '._('Cannot update Effort Unit')._(':').' '.db_error());
			db_rollback();
			return false;
		}

		if (!$this->fetchData($this->getID())) {
			db_rollback();
			return false;
		}
		db_commit();
		return true;

	}

	function updatePosition($unit_position) {

		$result=db_query_params ('UPDATE effort_unit SET unit_position= $1 WHERE unit_id=$2',
				array($unit_position, $this->getID()));
		if ($result && db_affected_rows($result) > 0) {
			return true;
		}
		else {
			$this->setError(db_error());
			return false;
		}
	}

	function reorderUnits($new_position) {
		$unitFactory = new EffortUnitFactory($this->EffortUnitSet);
		$unitsData = $unitFactory->getUnitsData();
		$max = count($unitsData);
		if ($new_position < 1 || $new_position > $max) {
			$this->setError(_('Out of range value'));
			return false;
		}
		$pos = 1;
		$data = array();
		for ($i = 0; $i < $max; $i++) {
			if ($pos == $new_position) {
				$data[$pos] = $this->getID();
				$pos++;
			}
			if ($unitsData[$i]['unit_id'] != $this->getID()) {
				$data[$pos] = $unitsData[$i]['unit_id'];
				$pos++;
			}
		}
		if ($pos == $new_position) {
			$data[$pos] = $this->getID();
			$pos++;
		}
		for ($pos = 1; $pos <= count($data); $pos++) {
			$unit = new EffortUnit($this->EffortUnitSet,$data[$pos]);
			if (! $unit->updatePosition($pos))
				return false;
		}
		return true;
	}

	function copy($from_unit) {
		$name = $from_unit->getName();
		$conversion_factor = $from_unit->getConversionFactor();
		$position = $from_unit->getPosition();
		$is_base_unit = $from_unit->isBaseUnit();
		if (!$is_base_unit) {
			$to_unit_name = $from_unit->getToUnitName();
			$effortUnitFactory = new EffortUnitFactory($this->EffortUnitSet);
			$to_unit_id = $effortUnitFactory->getUnitByName($to_unit_name)->getID();
		} else {
			$to_unit_id = 1;
		}
		if (!$this->create($name, $conversion_factor, $to_unit_id, $position, $is_base_unit)) {
		}
	}
}
