<?php   
/**
 * FusionForge base error class
 *
 * Copyright 1999-2001, VA Linux Systems, Inc.
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

define('ERROR__NO_ERROR', 0);
define('ERROR__UNCLASSIFIED_ERROR', 1);
define('ERROR__PERMISSION_DENIED_ERROR', 2);
define('ERROR__INVALID_EMAIL_ERROR', 3);
define('ERROR__ON_UPDATE_ERROR', 4);
define('ERROR__GROUPID_ERROR', 5);
define('ERROR__MISSING_PARAMS_ERROR', 6);

/**
 * Error handling utility class
 *
 */

class Error {
	/**
	 * The current error state.
	 *
	 * @var bool $error_state.
	 */
	var $error_state;

	/**
	 * The current error message(s).
	 *
	 * @var string $error_message.
	 */
	var $error_message;

	/**
	 * The current error code
	 *
	 * @var int $error_code.
	 */
	var $error_code;
	
	/**
	 * Error() - Constructor.
	 * Constructor for the Error class.
	 * Sets the error state to false.
	 *
	 */
	function Error() {
		//nothing
		$this->error_state=false;
		$this->error_code=ERROR__NO_ERROR;
	}

	/**
	 * setError() - Sets the error string.
	 * Set the error string $error_message to the value of $string
	 * and enable the $error_state flag.
	 *
	 * @param	string  The error string to set.
	 * @param	int	The error code
	 */
	function setError($string, $code=ERROR__UNCLASSIFIED_ERROR) {
		$this->error_state=true;
		$this->error_message=$string;
		$this->error_code=$code;
		return false;
	}

	/**
	 * clearError() - Clear the current error.
	 * Clear the current error string and disable the $error_state flag.
	 *
	 */
	function clearError() {
		$this->error_state=false;
		$this->error_code=ERROR__NO_ERROR;
		$this->error_message='';
	}

	/**
	 * getErrorMessage() - Retrieve the error message string.
	 * Returns the value of $error_message.
	 *
	 * @return    $error_message The current error message string.
	 *
	 */
	function getErrorMessage() {
		if ($this->error_state)	{
			return $this->error_message;
		} else {
			return 'No Error';
		}
	}

	/**
	 * isError() - Determines the current error state.
	 * This function returns the current value of $error_state.
	 *
	 * @return    $error_state     The boolean error status.
	 *
	 */
	function isError() {
		return $this->error_state;
	}
	

	/**
	 * setPermissionDeniedError() - sets a Permission Denied error
	 *  retrieves the localized error string for Permission Denied and calls exit_error()
	 *
	 *
	 */
	function setPermissionDeniedError(){
		$this->setError(_('Permission denied.'), ERROR__PERMISSION_DENIED_ERROR);
	}
	
	/**
	 * isPermissionDeniedError() - Determines if it is a permission denied error
	 *
	 * @return	boolean
	 */
	function isPermissionDeniedError(){
		return ($this->error_code == ERROR__PERMISSION_DENIED_ERROR);
	}

	/**
	 * setInvalidEmailError() - sets a Invalid Email error
	 *  retrieves the localized error string for Invalid Email and calls exit_error()
	 */
	function setInvalidEmailError($adr=false){
		$e = _('Invalid Email Address');
		if ($adr)
			$e .= " '" . htmlspecialchars($adr) . "'";
		else if ($adr !== false)
			$e .= ' ' . _('(none given)');
		$this->setError($e, ERROR__INVALID_EMAIL_ERROR);
	}
	
	/**
	 * isInvalidEmailError() - Determines if it is an invalid email error
	 *
	 * @return	boolean
	 */
	function isInvalidEmailError(){
		return ($this->error_code == ERROR__INVALID_EMAIL_ERROR);
	}
	
	/**
	 * setOnUpdateError() - sets an On Update Error
	 *  retrieves the localized error string for On Update
	 *
	 * @param	string  The db result to be written.
	 *
	 */
	function setOnUpdateError($result=""){
		$this->setError(sprintf(_('Error On Update: %s'), $result), ERROR__ON_UPDATE_ERROR);
	}
	
	/**
	 * isOnUpdateError() - Determines if it is an on update error
	 *
	 * @return	boolean
	 */
	function isOnUpdateError(){
		return ($this->error_code == ERROR__ON_UPDATE_ERROR);
	}

	/**
	 * setGroupIdError() - sets an Group ID Error
	 *  retrieves the localized error string for Group ID 
	 */
	function setGroupIdError(){
		$this->setError(_('Group_id in db result does not match Group Object'), ERROR__GROUPID_ERROR);
		
	}
	
	/**
	 * isGroupIdError() - Determines if it is a group ID error
	 *
	 * @return	boolean
	 */
	function isGroupIdError(){
		return ($this->error_code == ERROR__GROUPID_ERROR);
	}

	/**
	 * setMissingParamsError() - sets an Group ID Error
	 *  retrieves the localized error string for missing pparams
	 *
	 * @param	string  The name of the missing parameter
	 *
	 */
	function setMissingParamsError($param=''){
		if ($param) {
			$param = ': ' . $param;
		}
		$this->setError(_('Missing Parameters').$param, ERROR__MISSING_PARAMS_ERROR);
	}
	
	/**
	 * isMissingParamsError() - Determines if it is a missing params error
	 *
	 * @return	boolean
	 */
	function isMissingParamsError(){
		return ($this->error_code == ERROR__MISSING_PARAMS_ERROR);
	}

}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
