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
    

}
?>
