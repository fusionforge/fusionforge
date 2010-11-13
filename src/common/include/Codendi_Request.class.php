<?php
/**
 * Copyright (c) Xerox Corporation, Codendi Team, 2001-2009. All rights reserved
 *
 * This file is a part of Codendi.
 *
 * Codendi is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Codendi is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Codendi. If not, see <http://www.gnu.org/licenses/>.
 */

/* abstract */ class Codendi_Request {
	/**
	 * @var array
	 * @access private
	 */
	var $_validated_input;

	/**
	 * @var array
	 * @access private
	 */
	var $_last_access_to_input;

	/**
	 * @var array
	 */
	var $params;

	/**
	 * Constructor
	 */
	function Codendi_Request($params) {
		$this->params                = $params;
		$this->_validated_input      = array();
		$this->_last_access_to_input = array();
	}


	/**
	 * Get the value of $variable in $this->params (user submitted values).
	 *
	 * @param string $variable Name of the parameter to get.
	 * @return mixed If the variable exist, the value is returned (string)
	 * otherwise return false;
	 */
	function get($variable) {

		return $this->_get($variable, $this->params);
	}

	/**
	 * Add a param and/or set its value
	 *
	 */
	function set($name, $value) {
		$this->params[$name] = $value;
	}


	/**
	 * Get the value of $variable in $array. 
	 *
	 * @access private
	 * @param string $variable Name of the parameter to get.
	 * @param array $array Name of the parameter to get.
	 */
	function _get($variable, $array) {
		if ($this->_exist($variable, $array)) {
			return $array[$variable];
		} else {
			return false;
		}
	}

/**
     * Returns from where the variable is accessed.
     *
     * @return string
     */
    function _getCallTrace() {
        $backtrace = debug_backtrace();
        $files = explode('/', $backtrace[1]['file']);
        return $files[count($files) - 4] . '/'.
            $files[count($files) - 3] . '/'.
            $files[count($files) - 2] . '/'.
            $files[count($files) - 1] . ' Line: '.
            $backtrace[1]['line'];
    }


	/**
	 * Check if $variable exists in user submitted parameters.
	 *
	 * @param string $variable Name of the parameter.
	 * @return boolean
	 */
	function exist($variable) {
		return $this->_exist($variable, $this->params);
	}

	/**
	 * Check if $variable exists in $array.
	 *
	 * @access private
	 * @param string $variable Name of the parameter.
	 * @return boolean
	 */
	function _exist($variable, $array) {
		return isset($array[$variable]);
	}
	/**
	 * Apply validator on submitted user value.
	 *
	 * @param Valid  Validator to apply
	 * @return boolean
	 */
	function valid(&$validator) {
		$this->_validated_input[$validator->getKey()] = true;
		return $validator->validate($this->get($validator->getKey()));
	} 

	/**
	 * Apply validator on all values of a submitted user array.
	 *
	 * @param Valid  Validator to apply
	 * @return boolean
	 */
	function validArray(&$validator) {
		$this->_validated_input[$validator->getKey()] = true;
		$isValid = true;
		$array = $this->get($validator->getKey());
		if (is_array($array)) {
			if (count($array)>0) {
				foreach ($array as $key => $v) {
					if (!$validator->validate($v)) {
						$isValid = false;
					}
				}
			} else {
				$isValid = $validator->validate(null);
			}
		} else {
			$isValid = false;
		}
		return $isValid;
	}
	/**
	 * Apply validator on submitted user value and return the value if valid
	 * Else return default value
	 * @param string $variable Name of the parameter to get.
	 * @param mixed $validator Name of the validator (string, uint, email) or an instance of a validator
	 * @param mixed $default_value Value return if the validator is not valid. Optional, default is null.
	 */
	function getValidated($variable, $validator = 'string', $default_value = null) {
		/*$is_valid = false;
		  if ($v = ValidFactory::getInstance($validator, $variable)) {
		  $is_valid = $this->valid($v);
		  } else {
		  trigger_error('Validator '. $validator .' is not found', E_USER_ERROR);
		  }
		  return $is_valid ? $this->get($variable) : $default_value;*/
		return $this->get($variable);
	}
 /**
     * Apply validator on submitted user array.
     *
     * @param string Index in the user submitted values where the array stands.
     * @param Valid  Validator to apply
     * @return boolean
     */
    function validInArray($index, &$validator) {
        $this->_validated_input[$index][$validator->getKey()] = true;
        return $validator->validate($this->getInArray($index, $validator->getKey()));
    }
 /**
     * Get value of $idx[$variable] in $this->params (user submitted values).
     *
     * @param string The index of the variable array in $this->params.
     * @param string Name of the parameter to get.
     * @return mixed If the variable exist, the value is returned (string)
     * otherwise return false;
     */
    function getInArray($idx, $variable) {
        $this->_last_access_to_input[$idx][$variable] = $this->_getCallTrace();
        if(is_array($this->params[$idx])) {
            return $this->_get($variable, $this->params[$idx]);
        } else {
            return false;
        }
    }
function isAjax() {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtoupper($_SERVER['HTTP_X_REQUESTED_WITH']) == 'XMLHTTPREQUEST';
    }



}
?>
