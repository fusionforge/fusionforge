<?php
/**
 * FusionForge Effort
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
require_once $gfcommon.'tracker/EffortUnitFactory.class.php';

class Effort extends FFError {

	/**
	 * @var	 integer	value
	 */
	private $value;

	/**
	 * @var	 EffortUnit	Effort Unit
	 */
	private $effortUnit;

	/**
	 * Effort - Effort object constructor
	 *
	 * @param	integer
	 * @param	EffortUnit
	 */
	function __construct($value,$effortUnit) {
		parent::__construct();

		$this->value = $value;
		$this->effortUnit = $effortUnit;

		return;
	}
	
	function getEffortUnit(){
		return $this->effortUnit;
	}
	
	function getValue(){
		return $this->value;
	}

	function add(Effort $effort) {
		$thisFactor = $this->getEffortUnit()->getConversionFactorForBaseUnit();
		$effortFactor = $effort->getEffortUnit()->getConversionFactorForBaseUnit();
		$add = $this->value*$thisFactor+ $effort->getValue()*$effortFactor;
		if ($thisFactor >= $effortFactor) {
			$unit = $this->getEffortUnit();
		} else {
			$unit = $effort->getEffortUnit();
		}
		if ($add % $unit->getConversionFactorForBaseUnit() != 0) {
			$effortUnitSet = $effort->getEffortUnit()->getEffortUnitSet();
			$effortUnitFactory = new EffortUnitFactory($effortUnitSet);
			$unitsObj = $effortUnitFactory->getUnits();
			$maxFactor = 0;
			foreach ($unitsObj as $unitObj) {
				$unitFactor = $unitObj->getConversionFactorForBaseUnit();
				if ($unitFactor> $maxFactor && $add % $unitFactor== 0) {
					$maxFactor = $thisFactor;
					$unit = $unitObj;
				}
			}
		}
		return new Effort($add/$unit->getConversionFactorForBaseUnit(), $unit);
	}

	function sub(Effort $effort) {
		$thisFactor = $this->getEffortUnit()->getConversionFactorForBaseUnit();
		$effortFactor = $effort->getEffortUnit()->getConversionFactorForBaseUnit();
		$sub = $this->value*$thisFactor - $effort->getValue()*$effortFactor;
		if ($thisFactor >= $effortFactor) {
			$unit = $this->getEffortUnit();
		} else {
			$unit = $effort->getEffortUnit();
		}
		if ($sub% $unit->getConversionFactorForBaseUnit() != 0) {
			$effortUnitSet = $effort->getEffortUnit()->getEffortUnitSet();
			$effortUnitFactory = new EffortUnitFactory($effortUnitSet);
			$unitsObj = $effortUnitFactory->getUnits();
			$maxFactor = 0;
			foreach ($unitsObj as $unitObj) {
				$unitFactor = $unitObj->getConversionFactorForBaseUnit();
				if ($unitFactor> $maxFactor && $sub % $unitFactor== 0) {
					$maxFactor = $thisFactor;
					$unit = $unitObj;
				}
			}
		}
		return new Effort($sub/$unit->getConversionFactorForBaseUnit(), $unit);
	}
	function toEncoded() {
		return $this->value*$this->effortUnit->getConversionFactorForBaseUnit().'U'.$this->effortUnit->getID();
	}
}

function encodedEffortToEffort($encoded) {
	if (preg_match('/^(\d+)U(\d+)$/',$encoded,$matches)) {
		$unit = getEffortUnitById($matches[2]);
		$value = intval(intval($matches[1])/$unit->getConversionFactorForBaseUnit());
		$effort = new Effort($value, $unit);
	} else {
		$effort = false;
	}
	return $effort;
}
