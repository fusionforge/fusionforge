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

	/**
	 * @var	EffortUnitSet	$EffortUnitSet	Effort unit set to which the unit belongs.
	 */
	private $EffortUnitSet;

	/**
	 * @var	array	$data_array	Associative array of data from db.
	 */
	private $data_array;

	/**
	 * EffortUnit - EffortUnit object constructor
	 *
	 * @param	EffortUnitSet	$EffortUnitSet	Required - Effort Unit Set.
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
					$this->data_array = array();
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
	 * @param	int	$conversion_factor	The conversion factor to define the current unit.
	 * @param	int	$to_unit			The unity used for the definition of the current unit.
	 * @param	bool|int	$unit_position		The position of the unit when listed.
	 * @param	bool	$is_base_unit		True if this unit is a base unit.
	 * @param	array	$importData			For import
	 * @return	bool	success or not
	 */
	function create($name, $conversion_factor, $to_unit, $unit_position = false, $is_base_unit = false, $importData = array()) {
		if (!ctype_digit(strval($conversion_factor)) || $conversion_factor<1) {
			$this->setError(_('Conversion factor must be an integer greater or equal to 1'));
			return false;
		}
		$name = trim($name);
		if ($name=='') {
			$this->setError(_('An Unit name is required'));
			return false;
		}
		$res = db_query_params('SELECT 1 FROM effort_unit WHERE is_deleted <> 1 AND unit_name = $1 AND unit_set_id = $2', array(htmlspecialchars($name), $this->EffortUnitSet->GetID()));
		if (db_numrows($res) > 0) {
			$this->setError(sprintf(_('Unit name %s already exists'),$name));
			return false;
		}

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

		if (!$unit_position) {
			$res = db_query_params('SELECT MAX(unit_position) AS max_position FROM effort_unit WHERE unit_set_id = $1', array($this->EffortUnitSet->GetID()));
			if (db_numrows($res) > 0) {
				$unit_position =  db_result($res, 0, 'max_position') + 1;
			} else {
				$unit_position = 0;
			}
		}
		$res = db_query_params('INSERT INTO effort_unit(unit_set_id, unit_name, conversion_factor, to_unit, unit_position, is_base_unit, created_date, created_by, modified_date, modified_by) VALUES($1, $2, $3, $4, $5, $6, $7, $8, $9, $10)',
				array($this->EffortUnitSet->GetID(), htmlspecialchars($name), $conversion_factor, $to_unit, $unit_position, ($is_base_unit?1:0), $time, $user, $time, $user));
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
	 * @return	int	The unit_id.
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
	 * @return	int	unit base ID.
	 */
	function getToUnit() {
		return $this->data_array['to_unit'];
	}

	/**
	 * getToUnitName - get unit name use to define this unit.
	 *
	 * @return	string	unit name use to define this unit.
	 */
	function getToUnitName() {
		if (!empty($this->data_array['to_unit'])) {
			$toUnit = new EffortUnit($this->EffortUnitSet, $this->data_array['to_unit']);
			return $toUnit->getName();
		} else {
			return '';
		}
	}
	/**
	 * getConversionFactor - get conversion factor that define this unit .
	 *
	 * @return	int	conversion factor.
	 */
	function getConversionFactor() {
		return $this->data_array['conversion_factor'];
	}

	/**
	 * getPosition - get this Effort Unit display position.
	 *
	 * @return	int	unit display position.
	 */
	function getPosition() {
		return $this->data_array['unit_position'];
	}

	/**
	 * getEffortUnitSet - get Effort Unit Set of this unit.
	 *
	 * @return	EffortUnitSet	Effort Unit Set.
	 */
	function &getEffortUnitSet(){
		return $this->EffortUnitSet;
	}

	/**
	 * isDeleted - this Effort Unit is or not deleted.
	 *
	 * @return	bool	is or not deleted.
	 */
	function isDeleted()  {
		return ($this->data_array['is_deleted']?true:false);
	}

	/**
	 * isBaseUnit - this Effort Unit is or not the base unit.
	 *
	 * @return	bool	is or not base unit.
	 */
	function isBaseUnit() {
		return ($this->data_array['is_base_unit']?true:false);
	}

	/**
	 * delete - delete this Effort Unit.
	 * @param	array	$importData			For import
	 *
	 * @return	bool	success or not.
	 */
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

		// Update unit define with this unit
		$res = db_query_params('UPDATE effort_unit AS eu1
								SET conversion_factor = eu1.conversion_factor*eu2.conversion_factor,
									to_unit = eu2.to_unit
								FROM effort_unit AS eu2
								WHERE
									eu1.to_unit = eu2.unit_id AND
									eu2.unit_id = $1',
								array($this->getID()));
		if (!$res) {
			$this->setError(_('Error deleting Effort Unit')._(': ').db_error());
			db_rollback();
			return false;
		}

		// Update extra_field data using this unit
		$res = db_query_params('WITH t AS (
									SELECT data_id
										FROM artifact_extra_field_data
										INNER JOIN artifact_extra_field_list USING (extra_field_id)
										INNER JOIN artifact_group_list USING (group_artifact_id)
									WHERE
										field_type = $1 AND
										field_data like $2 AND
										unit_set_id = $3
									)
								UPDATE artifact_extra_field_data AS d
								SET field_data = CAST(SUBSTRING(field_data FROM \'#"%#"U%\' FOR \'#\') AS INTEGER) || \'U\' || $4
								FROM t
								WHERE d.data_id = t.data_id',
								array(ARTIFACT_EXTRAFIELDTYPE_EFFORT,'%U'.$this->getID(), $this->getEffortUnitSet()->getID(), $this->getToUnit()));
		if (!$res) {
			$this->setError(_('Error deleting Effort Unit')._(': ').db_error());
			db_rollback();
			return false;
		}
		$data_array['is_deleted'] = 1;
		db_commit();
		return true;
	}

	/**
	 * update - update this Effort Unit, name or definition.
	 * @param	string	$name				The name of the unit.
	 * @param	int	$conversion_factor	The conversion factor to define the current unit.
	 * @param	int	$to_unit			The unity used for the definition of the current unit.
	 * @param	array	$importData			For import
	 *
	 * @return	bool	success or not.
	 */
	function update($name, $conversion_factor, $to_unit, $importData = array()){
		if (!ctype_digit(strval($conversion_factor)) || $conversion_factor<1) {
			$this->setError(_('Conversion factor must be an integer greater or equal to 1'));
			return false;
		}
		$name = trim($name);
		if ($name=='') {
			$this->setError(_('An Unit name is required'));
			return false;
		}
		$res = db_query_params('SELECT 1 FROM effort_unit WHERE is_deleted <> 1 AND unit_name = $1 AND unit_id <> $2 AND unit_set_id = $3', array(htmlspecialchars($name), $this->getID(), $this->EffortUnitSet->GetID()));
		if (db_numrows($res) > 0) {
			$this->setError(sprintf(_('Unit name %s already exists'),$name));
			return false;
		}
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
		// get conversion factor for base unit before update
		$old_factor = $this->getConversionFactorForBaseUnit();
		// get depenfing units before update
		$units = array_merge(array($this), $this->getUnitsDependingOn());
		$res = db_query_params('UPDATE effort_unit SET unit_name=$1, conversion_factor=$2, to_unit=$3, modified_date=$4, modified_by=$5 WHERE unit_id=$6',
				array(htmlspecialchars($name), $conversion_factor, $to_unit, $time, $user, $this->getID()));
		if (!$res || db_affected_rows($res) < 1) {
			$this->setError(_('Error')._(':').' '._('Cannot update Effort Unit')._(':').' '.db_error());
			db_rollback();
			return false;
		}
		if (!$this->fetchData($this->getID())) {
			db_rollback();
			return false;
		}
		// get conversion factor for base unit after update
		$new_factor = $this->getConversionFactorForBaseUnit();
		if ($old_factor != $new_factor) {
			foreach ($units as $unit) {
				$res = db_query_params('WITH t AS (
										SELECT data_id
											FROM artifact_extra_field_data
											INNER JOIN artifact_extra_field_list USING (extra_field_id)
											INNER JOIN artifact_group_list USING (group_artifact_id)
										WHERE
											field_type = $1 AND
											field_data like $2 AND
											unit_set_id = $3
										)
									UPDATE artifact_extra_field_data AS d
									SET field_data = (CAST(SUBSTRING(field_data FROM \'#"%#"U%\' FOR \'#\') AS INTEGER)*$4)/$5 || \'U\' || $6
									FROM t
									WHERE d.data_id = t.data_id',
						array(ARTIFACT_EXTRAFIELDTYPE_EFFORT,'%U'.$unit->getID(), $this->EffortUnitSet->getID(), $new_factor, $old_factor, $unit->getID()));
				if (!$res) {
					$this->setError(_('Error')._(':').' '._('Cannot update Effort Unit (artifacts data update)')._(':').' '.db_error());
					db_rollback();
					$this->fetchData($this->getID());
					return false;
				}
				$res = db_query_params('WITH t AS (
										SELECT default_id
											FROM artifact_extra_field_default
											INNER JOIN artifact_extra_field_list USING (extra_field_id)
											INNER JOIN artifact_group_list USING (group_artifact_id)
										WHERE
											field_type = $1 AND
											default_value like $2 AND
											unit_set_id = $3
										)
									UPDATE artifact_extra_field_default AS d
									SET default_value = (CAST(SUBSTRING(default_value FROM \'#"%#"U%\' FOR \'#\') AS INTEGER)*$4)/$5 || \'U\' || $6
									FROM t
									WHERE d.default_id = t.default_id',
						array(ARTIFACT_EXTRAFIELDTYPE_EFFORT,'%U'.$unit->getID(), $this->EffortUnitSet->getID(), $new_factor, $old_factor, $unit->getID()));
				if (!$res) {
					$this->setError(_('Error')._(':').' '._('Cannot update Effort Unit (default value update)')._(':').' '.db_error());
					db_rollback();
					$this->fetchData($this->getID());
					return false;
				}
			}
		}
		db_commit();
		return true;
	}

	/**
	 * updatePosition - Update this Effort Unit dispaly position.
	 * @param int	$unit_position	new position
	 *
	 * @return	bool	success or not.
	 */
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

	/**
	 * reorderUnits - Reorder all units.
	 * @param int	$new_position	new position of this unit
	 *
	 * @return	bool	success or not.
	 */
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
			if (! $unit->updatePosition($pos)) {
				return false;
			}
		}
		return true;
	}

	/**
	 * copy - copy definition from an other unit.
	 * @param	EffortUnit	$from_unit	Unit to be copied
	 *
	 * @return	bool	success or not.
	 */
	function copy($from_unit) {
		db_begin();
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
		$id = $this->create($name, $conversion_factor, $to_unit_id, $position, $is_base_unit);
		if ($id) {
			// Update artifacts data
			$res = db_query_params('WITH t AS (
										SELECT data_id
											FROM artifact_extra_field_data
											INNER JOIN artifact_extra_field_list USING (extra_field_id)
											INNER JOIN artifact_group_list USING (group_artifact_id)
										WHERE
											field_type = $1 AND
											field_data like $2 AND
											unit_set_id = $3
										)
									UPDATE artifact_extra_field_data AS d
									SET field_data = CAST(SUBSTRING(field_data FROM \'#"%#"U%\' FOR \'#\') AS INTEGER) || \'U\' || $4
									FROM t
									WHERE d.data_id = t.data_id',
					array(ARTIFACT_EXTRAFIELDTYPE_EFFORT,'%U'.$from_unit->getID(), $from_unit->getEffortUnitSet()->getID(), $id));
			if (!$res) {
				$this->setError(_('Error coping Effort Unit')._(': ').db_error());
				db_rollback();
				return false;
			}
			// Update default values
			$res = db_query_params('WITH t AS (
										SELECT default_id
											FROM artifact_extra_field_default
											INNER JOIN artifact_extra_field_list USING (extra_field_id)
											INNER JOIN artifact_group_list USING (group_artifact_id)
										WHERE
											field_type = $1 AND
											default_value like $2 AND
											unit_set_id = $3
										)
									UPDATE artifact_extra_field_default AS d
									SET default_value = CAST(SUBSTRING(default_value FROM \'#"%#"U%\' FOR \'#\') AS INTEGER) || \'U\' || $4
									FROM t
									WHERE d.default_id = t.default_id',
					array(ARTIFACT_EXTRAFIELDTYPE_EFFORT,'%U'.$from_unit->getID(), $from_unit->getEffortUnitSet()->getID(), $id));
			if (!$res) {
				$this->setError(_('Error')._(':').' '._('Cannot update Effort Unit (default value update)')._(':').' '.db_error());
				db_rollback();
				return false;
			}
		}
		db_commit();
		return $id;
	}

	/**
	 * getConversionFactorForBaseUnit - get conversion factor for Base Unit.
	 *
	 * @return	int	conversion factor.
	 */
	function getConversionFactorForBaseUnit() {
		$factor = $this->getConversionFactor();
		$toUnitId = $this->getToUnit();
		$toUnit = new EffortUnit($this->EffortUnitSet,$toUnitId);
		if (!$toUnit->isBaseUnit()) {
			$factor *= $toUnit->getConversionFactorForBaseUnit();
		}
		return $factor;
	}

	/**
	 * getUnitsDependingOn - get array of units depending on this unit.
	 *
	 * @return	array	array of EffortUnit depending on this unit.
	 */
	function getUnitsDependingOn(){
		$unitsDependingOn = array();
		$effortUnitFactory = new EffortUnitFactory($this->EffortUnitSet);
		$units = $effortUnitFactory->getUnits();
		foreach ($units as $unit) {
			if ($unit->getToUnit()==$this->getID()) {
				$unitsDependingOn[] = $unit;
				$unitsDependingOn = array_merge($unitsDependingOn, $unit->getUnitsDependingOn());
			}
		}
		return $unitsDependingOn;
	}

	/**
	 * isEquivalentTo - test if this unit is equivalent to the other.
	 * @param	EffortUnit $effortUnit	Unit to compare to
	 *
	 * @return	bool	success or not.
	 */
	function isEquivalentTo($effortUnit){
		if ($this->getName() != $effortUnit->getName()) {
			return false;
		}
		if ($this->getConversionFactor() != $effortUnit->getConversionFactor()) {
			return false;
		}
		if ($this->isBaseUnit() && $effortUnit->isBaseUnit()) {
			return true;
		}
		$thisToUnit = new EffortUnit($this->EffortUnitSet, $this->getToUnit());
		$toUnit = new EffortUnit($effortUnit->EffortUnitSet, $effortUnit->getToUnit());
		if (!$toUnit->isEquivalentTo($thisToUnit)) {
			return false;
		}
		return true;
	}
}
function getEffortUnitById($unit_id){
	$res = db_query_params ('SELECT * FROM effort_unit INNER JOIN effort_unit_set USING (unit_set_id) WHERE unit_id=$1',
			array($unit_id));
	if (!$res || db_numrows($res) < 1) {
		return false;
	}
	$data_array = db_fetch_array($res);
	switch ($data_array['level']) {
		case EFFORTUNITSET_FORGE_LEVEL:
			$object = null;
			break;
		case EFFORTUNITSET_PROJECT_LEVEL:
			$object= new Group($data_array['group_id']);
			break;
		case EFFORTUNITSET_TRACKER_LEVEL:
			$Group = new Group($data_array['group_id']);
			$object= new ArtifactType($Group, $data_array['group_artifact_id']);
			break;
		default:
			$object = null;
	}
	$effortUnitSet = new EffortUnitSet($object, $data_array['unit_set_id']);
	$effortUnit = new EffortUnit($effortUnitSet, $unit_id);
	return $effortUnit;
}
