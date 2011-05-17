<?php
/**
 * FusionForge field validator
 *
 * Copyright 2002, GForge, LLC
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

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
