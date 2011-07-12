<?php
/**
 * FusionForge escaping library
 *
 * Copyright 2003-2004, Guillaume Smet
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
 * getIntFromRequest - get an int from REQUEST
 *
 * @param string $key key of the wanted value
 * @param int $defaultValue if we can't find the wanted value, it returns the default value
 * @return int the value
 */
function getIntFromRequest($key, $defaultValue = 0) {
	return _getIntFromArray(_getRequestArray(), $key, $defaultValue);
}

/**
 * getStringFromRequest - get a string from REQUEST
 *
 * @param string $key key of the wanted value
 * @param string $defaultValue if we can't find the wanted value, it returns the default value
 * @return string the value
 */
function getStringFromRequest($key, $defaultValue = '') {
	return _getStringFromArray(_getRequestArray(), $key, $defaultValue);
}

/**
 * getArrayFromRequest - get an array from REQUEST
 * @param	string $key	Key of the wanted value
 * @param	string $defaultValue	if we can't find the wanted value, it returns the default value
 * @return	array	The value
 */
function getArrayFromRequest($key, $defaultValue = array()) {
	return _getArrayFromArray(_getRequestArray(), $key, $defaultValue);
}

/**
 * getIntFromPost - get an int from POST
 *
 * @param string $key key of the wanted value
 * @param int $defaultValue if we can't find the wanted value, it returns the default value
 * @return int the value
 */
function getIntFromPost($key, $defaultValue = 0) {
	return _getIntFromArray(_getPostArray(), $key, $defaultValue);
}

/**
 * getStringFromPost - get a string from POST
 *
 * @param string $key key of the wanted value
 * @param string $defaultValue if we can't find the wanted value, it returns the default value
 * @return string the value
 */
function getStringFromPost($key, $defaultValue = '') {
	return _getStringFromArray(_getPostArray(), $key, $defaultValue);
}

/**
 * getIntFromGet - get an int from GET
 *
 * @param string $key key of the wanted value
 * @param int $defaultValue if we can't find the wanted value, it returns the default value
 * @return int the value
 */
function getIntFromGet($key, $defaultValue = 0) {
	return _getIntFromArray(_getGetArray(), $key, $defaultValue);
}

/**
 * getStringFromGet - get a string from GET
 *
 * @param string $key key of the wanted value
 * @param string $defaultValue if we can't find the wanted value, it returns the default value
 * @return string the value
 */
function getStringFromGet($key, $defaultValue = '') {
	return _getStringFromArray(_getGetArray(), $key, $defaultValue);
}

/**
 * getIntFromCookie - get an int set by a cookie
 *
 * @param string $key key of the wanted value
 * @param int $defaultValue if we can't find the wanted value, it returns the default value
 * @return int the value
 */
function getIntFromCookie($key, $defaultValue = 0) {
	return _getIntFromArray(_getCookieArray(), $key, $defaultValue);
}

/**
 * getStringFromCookie - get a string set by a cookie
 *
 * @param string $key key of the wanted value
 * @param string $defaultValue if we can't find the wanted value, it returns the default value
 * @return string the value
 */
function getStringFromCookie($key, $defaultValue = '') {
	return _getStringFromArray(_getCookieArray(), $key, $defaultValue);
}

/**
 * getUploadedFile - get the uploaded file information
 *
 * The returned array is in the format given by PHP, as described in
 * http://php.net/manual/en/features.file-upload.php
 *
 * If there was no such file upload control in form, empty array is
 * returned.  If there was file upload control but no file was
 * entered, then $result['tmp_name'] is empty string.
 *
 * @param string name of the file
 * @return array uploaded file information
 */
function getUploadedFile($key) {
	$filesArray = & _getFilesArray();
	if(isset($filesArray[$key])) {
		$result = $filesArray[$key];
		if ($result['tmp_name'] == 'none') {
			$result['tmp_name'] = '';
		}
		return $result;
	}
	else {
		return array();
	}
}

/**
 * getStringFromServer - get a string from Server environment
 *
 * @param string $key key of the wanted value
 * @param string $defaultValue if we can't find the wanted value, it returns the default value
 * @return string the value
 */
function getStringFromServer($key) {
	$serverArray = & _getServerArray();
	if(isset($serverArray[$key])) {
		return $serverArray[$key];
	}
	else {
		return '';
	}
}

/* private */

/**
 * _getIntFromArray - get an int from an array
 *
 * @param array $array the array
 * @param string $key the key of the wanted value
 * @param int $defaultValue an int which is returned if we can't find the key in the array
 * @return int the wanted value
 */
function _getIntFromArray( $array, $key, $defaultValue = 0) {
	if(isset($array[$key]) && is_numeric($array[$key]) &&
		$array[$key] <= 2147483647 && $array[$key] >= -2147483648 ) {
		return (int) $array[$key];
	}
	elseif(is_numeric($defaultValue)) {
		return (int) $defaultValue;
	}
	else {
		return 0;
	}
}

/**
 * _getStringFromArray - get a string from an array
 *
 * @param array $array the array
 * @param string $key the key of the wanted value
 * @param int $defaultValue an int which is returned if we can't find the key in the array
 * @return string the wanted value
 */
function _getStringFromArray( $array, $key, $defaultValue = '') {
	if(isset($array[$key])) {
		return $array[$key];
	}
	else {
		return $defaultValue;
	}
}

/**
 * _getArrayFromArray - get an array from another array
 *
 * @param array $array the array
 * @param string $key the key of the wanted value
 * @param int $defaultValue an array which is returned if we can't find the key in the array
 * @return string the wanted value
 */
function _getArrayFromArray( $array, $key, $defaultValue = array()) {
	if(isset($array[$key])) {
		return $array[$key];
	}
	else {
		return $defaultValue;
	}
}

/**
 * _getPredefinedArray - get one of the predefined array (GET, POST, COOKIE...)
 *
 * @param string $superGlobalName name of the super global array (_POST, _GET)
 * @param string $oldName name of the old array (HTTP_POST_VARS, HTTP_GET_VARS) for older php versions
 * @return array a predefined array
 */
function & _getPredefinedArray($superGlobalName, $oldName) {
	if(isset($GLOBALS[$superGlobalName])) {
		$array = & $GLOBALS[$superGlobalName];
	} elseif(isset($GLOBALS[$oldName])) {
		$array = & $GLOBALS[$oldName];
	} else {
		$array = array();
	}
	return $array;
}

/**
 * _getRequestArray - wrapper to get the request array
 *
 * @return array the REQUEST array
 */
function & _getRequestArray() {
	if(isset($_REQUEST)) {
		return $_REQUEST;
	} else {
		return array_merge($GLOBALS['HTTP_GET_VARS'], $GLOBALS['HTTP_POST_VARS'], $GLOBALS['HTTP_COOKIE_VARS']);
	}
}

/**
 * _getPostArray - wrapper to get the post array
 *
 * @return array the POST array
 */
function & _getPostArray() {
	return _getPredefinedArray('_POST', 'HTTP_POST_VARS');
}

/**
 * _getPostArray - wrapper to get the GET array
 *
 * @return array the GET array
 */
function & _getGetArray() {
	return _getPredefinedArray('_GET', 'HTTP_GET_VARS');
}

/**
 * _getFilesArray - wrapper to get the FILES array
 *
 * @return array the FILES array
 */
function & _getFilesArray() {
	return _getPredefinedArray('_FILES', 'HTTP_POST_FILES');
}

/**
 * _getServerArray - wrapper to get the SERVER array
 *
 * @return array the SERVER array
 */
function & _getServerArray() {
	return _getPredefinedArray('_SERVER', 'HTTP_SERVER_VARS');
}

/**
 * _getCookieArray - wrapper to get the post array
 *
 * @return array the COOKIE array
 */
function & _getCookieArray() {
	return _getPredefinedArray('_COOKIE', 'HTTP_COOKIE_VARS');
}

/**
* inputSpecialchars - escape a string which is in an input
*
* @param string $string string to escape
* @return string escaped string
*/
function inputSpecialchars($string) {
	return str_replace('"', '&quot;', $string);
}

/**
* unInputSpecialchars - clean a string escaped with inputSpecialchars
*
* @param string $string escaped string
* @return string clean string
*/
function unInputSpecialchars($string) {
	return str_replace('&quot;', '"', $string);
}

/**
 * getFilteredStringFromRequest - get a string from REQUEST
 *
 * @param string $key key of the wanted value
 * @param string $pattern Regular expression of allowed values.
 * @param string $defaultValue if we can't find the wanted value, it returns the default value
 * @return string the value or false if not valid.
 */
function getFilteredStringFromRequest($string, $pattern, $defaultValue = '') {
	$value = getStringFromRequest($string, $defaultValue);
	if (preg_match($pattern, $value)) {
		return $value;
	} else {
		return $defaultValue;
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
