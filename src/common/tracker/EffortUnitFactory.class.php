<?php
/**
 * FusionForge Effort Unit Factory
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
require_once $gfcommon.'tracker/EffortUnitSet.class.php';
require_once $gfcommon.'tracker/EffortUnit.class.php';

class EffortUnitFactory extends FFError {

	/**
	 * @var	EffortUnitSet	$EffortUnitSet	Effort Unit Set of the factory.
	 */
	private $EffortUnitSet;

	/**
	 * @var	 array	$Units array of EffortUnit object.
	 */
	private $Units;

	/**
	 * @var	 array	$UnitsData array of EffortUnits data.
	 */
	var $UnitsData;

	/**
	 * EffortUnitFactory - EffortUnitFactory object constructor
	 *
	 * @param	EffortUnitSet	$EffortUnitSet	The Effort Unit Set object to which this EffortUnitFactory is associated.
	 */
	function __construct(&$EffortUnitSet) {
		parent::__construct();
		if (!$EffortUnitSet || !is_object($EffortUnitSet)) {
			$this->setError('EffortUnitFactory: No Valid EffortUnitSet Object');
			return;
		}
		if ($EffortUnitSet->isError()) {
			$this->setError('EffortUnitFactory: '.$EffortUnitSet->getErrorMessage());
			return;
		}
		$this->EffortUnitSet =& $EffortUnitSet;
	}

	/**
	 * getUnitsArr - return a array of Units data.
	 *
	 * @return	array	The array of Units data.
	 */
	function getUnitsData() {
		if ($this->UnitsData) {
			return $this->UnitsData;
		}
		$this->UnitsData = array ();
		$res = db_query_params ('SELECT * FROM effort_unit WHERE unit_set_id=$1 AND is_deleted=0 ORDER BY unit_position ASC',
				array ($this->EffortUnitSet->getID()));
		if (!$res) {
			$this->setError(db_error());
			return false;
		}
		while ($arr = db_fetch_array($res)) {
			$this->UnitsData[] = $arr;
		}
		return $this->UnitsData;
	}

	/**
	 * getUnits - return an array of Effort Unit objects.
	 *
	 * @return	array	The array of Effort Unit objects.
	 */
	function getUnits() {
		if ($this->Units) {
			return $this->Units;
		}
		$this->Units = array ();
		$unitsArr = $this->getUnitsData();
		if (!$unitsArr && $this->isError()) {
			$this->setError($this->getErrorMessage());
			return false;
		}
		foreach ($unitsArr as $unitArr) {
			$unit = new EffortUnit($this->EffortUnitSet, $unitArr['unit_id'], $unitArr);
			if (!$unit && $unit->isError()) {
				$this->setError($unit->getErrorMessage());
				return false;
			} else {
				$this->Units[] = $unit;
			}
		}
		return $this->Units;
	}

	/**
	 * getUnitsArr - return an associative array of Units name & id.
	 *
	 * @return	array	The associative array of Units name & id.
	 */
	function getUnitsArr() {
		$result = array ();
		$unitsArr = $this->getUnitsData();
		if (!$unitsArr && $this->isError()) {
			$this->setError($this->getErrorMessage());
			return false;
		}
		foreach ($unitsArr as $unitArr) {
			$result[$unitArr['unit_id']] = $unitArr['unit_name'];
		}
		return $result ;
	}

	/**
	 * getBaseUnit - return the base EffortUnit object.
	 *
	 * @return	EffortUnit	the base EffortUnit object.
	 */
	function getBaseUnit() {
		$units = $this->getUnits();
		foreach($units as $unit) {
			if ($unit->isBaseUnit()) {
				return $unit;
			}
		}
		return false;
	}

	/**
	 * getUnitByName - get a EffortUnit object by name.
	 *
	 * @param	string	$name	the name of the EffortUnit
	 * @return	EffortUnit	the base EffortUnit object.
	 */
	function getUnitByName($name) {
		$units = $this->getUnits();
		foreach($units as $unit) {
			if ($unit->getName()==$name) {
				return $unit;
			}
		}
		return false;
	}

	/**
	 * encodedToValue - return the value of an effort expressed in encoded unit.
	 *
	 * @param	string	$encoded	encoded effort
	 * @return	integer	the value of an effort expressed in encoded unit.
	 */
	function encodedToValue($encoded){
		$value = 0;
		if (preg_match('/^(\d+)U(\d+)$/',$encoded,$matches)) {
			$unit = new EffortUnit($this->EffortUnitSet, $matches[2]);
			$value = intval(intval($matches[1])/$unit->getConversionFactorForBaseUnit());
		}
		return $value;
	}

	/**
	 * encodedToValueInBaseUnit - return the value of an effort expressed in base unit.
	 *
	 * @param	string	$encoded	encoded effort
	 * @return	integer	the value of an effort expressed in base unit.
	 */
	function encodedToValueInBaseUnit($encoded){
		$value = 0;
		if (preg_match('/^(\d+)U(\d+)$/',$encoded,$matches)) {
			$value = intval(intval($matches[1]));
		}
		return $value;
	}

	/**
	 * encodedToUnitId - return the unit id of an encoded effort.
	 *
	 * @param	string	$encoded	encoded effort
	 * @return	integer	the unit id of an encoded effort.
	 */
	function encodedToUnitId($encoded) {
		if (preg_match('/^(\d+)U(\d+)$/',$encoded,$matches)) {
			$unitId = intval($matches[2]);
		} else {
			$units = $this->getUnitsArr();
			reset($units);
			$unitId = key($units);
		}
		return $unitId;
	}

	/**
	 * encodedToUnitName - return the unit name of an encoded effort.
	 *
	 * @param	string	$encoded	encoded effort
	 * @return	string	the unit name of an encoded effort.
	 */
	function encodedToUnitName($encoded) {
		$units = $this->getUnitsArr();
		if (preg_match('/^(\d+)U(\d+)$/',$encoded,$matches)) {
			$unitName = $units[$matches[2]];
		} else {
			$unitName = reset($units);
		}
		return $unitName;
	}

	/**
	 * encodedToString - return the value and the unit name of an encoded effort.
	 *
	 * @param	string	$encoded	encoded effort
	 * @return	string	the value and the unit name of an encoded effort.
	 */
	function encodedToString($encoded) {
		if (preg_match('/^(\d+)U(\d+)$/',$encoded,$matches)) {
			$unit = new EffortUnit($this->EffortUnitSet, $matches[2]);
			$string = intval(intval($matches[1])/$unit->getConversionFactorForBaseUnit()).' '.$unit->getName();
		} else {
			$units = $this->getUnitsArr();
			$string = '0 '.reset($units);
		}
		return $string;
	}
}
