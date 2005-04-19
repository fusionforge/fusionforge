<?php

/**
 * Utilitary class for the GForge ViewCVS wrapper.
 *
 * Portion of this file is inspired from the ViewCVS wrapper
 * contained in CodeX.
 * Copyright (c) Xerox Corporation, CodeX / CodeX Team, 2001,2002. All Rights Reserved.
 * http://codex.xerox.com
 *
 * @version   $ID$
 */

define ('SEPARATOR', "\n\t\r\0\x0B");

/**
 *      viewcvs_is_html() - Test if ViewCVS returns HTML.
 *
 *      @return true if the content type of the ViewCVS is text/html.
 */
function viewcvs_is_html() {
	$request_uri = getStringFromServer('REQUEST_URI');
	$query_string = getStringFromServer('QUERY_STRING');

	return (strpos($request_uri,"*checkout*") === false && 
		strpos($query_string,"view=graphimg") === false &&
		strpos($query_string,"view=tar") === false &&
		strpos($request_uri,"*docroot*") === false &&
		strpos($request_uri,"makepatch=1") === false);
}

/**
 *      viewcvs_execute() - Call to viewcvs.cgi and returned the output.
 *
 *      @return String the output of the ViewCVS command.
 */
function viewcvs_execute() {

	$request_uri = getStringFromServer('REQUEST_URI');
	$query_string = getStringFromServer('QUERY_STRING');

	// this is very important ...
 	if (getStringFromServer('PATH_INFO') == '') {
 		$path = '/';
 	} else {
 		$path = getStringFromServer('PATH_INFO');
 		// hack: path must always end with /
 		if (strrpos($path,'/') != (strlen($path)-1)) {
 			$path .= '/';
 		}
 	}
	$command = 'HTTP_COOKIE="'.getStringFromServer('HTTP_COOKIE').'" '.
		'REMOTE_ADDR="'.getStringFromServer('REMOTE_ADDR').'" '.
		'QUERY_STRING="'.$query_string.'" '.
		'SERVER_SOFTWARE="'.getStringFromServer('SERVER_SOFTWARE').'" '.
		'SCRIPT_NAME="'.getStringFromServer('SCRIPT_NAME').'" '.
		'HTTP_USER_AGENT="'.getStringFromServer('HTTP_USER_AGENT').'" '.
		'HTTP_ACCEPT_ENCODING="'.getStringFromServer('HTTP_ACCEPT_ENCODING').'" '.
		'HTTP_ACCEPT_LANGUAGE="'.getStringFromServer('HTTP_ACCEPT_LANGUAGE').'" '.
		'PATH_INFO="'.$path.'" '.
		'PATH="'.getStringFromServer('PATH').'" '.
		'HTTP_HOST="'.getStringFromServer('HTTP_HOST').'" '.
		$GLOBALS['sys_path_to_scmweb'].'/viewcvs.cgi 2>&1';

	ob_start();
	passthru($command);
	$content = ob_get_contents();
	ob_end_clean();
	return $content;
}

?>
