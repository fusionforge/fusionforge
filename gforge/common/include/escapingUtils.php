<?php

/**
 * GForge Mailing Lists Facility
 *
 * Copyright 2003 Guillaume Smet
 * http://gforge.org/
 *
 * @version   $Id$
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
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

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
 * @param string name of the file
 * @return array uploaded file information
 */
function getUploadedFile($key) {
	$filesArray = & _getFilesArray();
	if(isset($filesArray[$key])) {
		return $filesArray[$key];
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
function _getIntFromArray(& $array, $key, $defaultValue = 0) {
	if(isset($array[$key]) && is_numeric($array[$key])) {
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
 * _getIntFromArray - get an int from an array
 *
 * @param array $array the array
 * @param string $key the key of the wanted value
 * @param int $defaultValue an int which is returned if we can't find the key in the array
 * @return int the wanted value
 */
function _getStringFromArray(& $array, $key, $defaultValue = '') {
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
	if(isset($$superGlobalName)) {
		$array = & $$superGlobalName;
	}
	elseif(isset($GLOBALS[$oldName])) {
		$array = & $GLOBALS[$oldName];
	}
	else {
		$array = array();
	}
	return $array;
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
* optionSpecialchars - escape a string which is in a <option>string</option>
*
* @param string $string string to escape
* @return string escaped string
*/
function optionSpecialchars($string) {
	return htmlSpecialchars($string);
}

$htmlTranslationTable = get_html_translation_table(HTML_SPECIALCHARS);
unset($htmlTranslationTable['&']);

/**
* textareaSpecialchars - escape a string which is in a textarea
*
* @param string $string string to escape
* @return string escaped string
*/
function textareaSpecialchars($string) {
	global $htmlTranslationTable;

	return strtr($string, $htmlTranslationTable);
}

/**
* unTextareaSpecialchars - clean a string escaped with textareaSpecialchars
*
* @param string $string escaped string
* @return string clean string
*/
function unTextareaSpecialchars($string) {
	global $htmlTranslationTable;
	
	return strtr($string, array_flip($htmlTranslationTable));
}

?>