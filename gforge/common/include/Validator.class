<?php
/**
 * GForge Project Management Facility
 *
 * Copyright 2002 GForge, LLC
 * http://gforge.org/
 *
 * This file is part of GForge.
 *
 * GForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  US
 */

/**
* This class is a simple utility to validate fields
* 
* Sample usage:
*
* $v = new Validator();
* $v->check($summary, "summary");
* $v->check($detail, "detail");
* if (!$v->isClean()) {
*  print $v->formErrorMsg("The following fields were null:");
* }
*
*/
class Validator {
	var $badfields;

	/**
	* Checks to see if a field is null; if so, the field name is added to an internal array
	*
	* @param field - a variable to check for null
	* @param name - the variable name
	*/
	function check($field, $name) {
		if (!$field) {
			$this->badfields[] = $name;
		}
	}

	/**
	* Returns true if no null fields have been checked so far
	*
	* @return boolean - True if there are no null fields so far
	*/
	function isClean() {
		return count($this->badfields) == 0;
	}
	
	/**
	* Returns an error message which contains the null field names which have been checked
	*
	* @param preamble string - A string with which to start the error message
	* @return string - A complete error message
	*/
	function formErrorMsg($preamble) {
		foreach ($this->badfields as $field) {
			$preamble = $preamble.$field.",";
		}
		return substr($preamble, 0, strlen($preamble)-1);
	}
}
?>
