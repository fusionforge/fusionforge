<?php

$sysdebug_ignored = forge_get_config('sysdebug_ignored');

// error handler function
function ffErrorHandler($errno, $errstr, $errfile, $errline)
{
	global $ffErrors, $sysdebug_ignored;

	if ($sysdebug_ignored && error_reporting() == 0)
		/* prepended @ to statement => ignore */
		return false;

	if (!isset($ffErrors))
		$ffErrors = array();

	$msg = "[$errno] $errstr ($errfile at $errline)";

	// Display messages only once.
	foreach ($ffErrors as $m) {
		if ($m['message'] == $msg)
			return true;
	}

	switch ($errno) {
		case E_USER_ERROR:
		case E_ERROR:
			$type = 'error';
			break;

		case E_USER_WARNING:
		case E_WARNING:
			$type = 'warning';
			break;

		case E_USER_NOTICE:
		case E_NOTICE:
			$type = 'notice';
			break;

		case E_STRICT:
		case E_USER_DEPRECATED:
		case E_DEPRECATED:
			$type = "strict";
			break;

		default:
			$type = 'unknown';
			break;
	}
	
	$ffErrors[] = array('type' => $type, 'message' => $msg);
	/* Don't execute PHP internal error handler */
	return true;
}


function ffOutputHandler($buffer) {
	global $ffErrors;

	/* stop calling ffErrorHandler */
	restore_error_handler();

	if (!isset($ffErrors))
		$ffErrors = array();

	$divstring = "\n\n" . '<script type="text/javascript"><!-- <![CDATA[
		function toggle_ffErrors() {
			var errorsblock = document.getElementById("ffErrorsBlock");
			if (errorsblock.style.display == "none") {
				errorsblock.style.display = "block";
			} else {
				errorsblock.style.display = "none";
			}
		}' . "\n//]]> --></script>\n<div id=\"ffErrors\">\n" .
	    '<a href="javascript:toggle_ffErrors();">Click to toggle</a>' .
	    "\n<div id=\"ffErrorsBlock\">";

	/* cut off </body></html> (hopefully only) at the end */
	$buffer = rtrim($buffer);	/* spaces, newlines, etc. */
	if (substr($buffer, -strlen("</html>")) != "</html>") {
		$ffErrors[] = array('type' => "error",
		    'message' => htmlentities("does not end with </html> tag"));
		$buffer = str_ireplace("</html>", "", $buffer);
	} else
		$buffer = substr($buffer, 0, -strlen("</html>"));
	$buffer = rtrim($buffer);	/* spaces, newlines, etc. */
	if (substr($buffer, -strlen("</body>")) != "</body>") {
		$ffErrors[] = array('type' => "error",
		    'message' => htmlentities("does not end with </body> tag"));
		$buffer = str_ireplace("</body>", "", $buffer);
	} else
		$buffer = substr($buffer, 0, -strlen("</body>"));
	$buffer = rtrim($buffer);	/* spaces, newlines, etc. */

	/* append errors, if any */
	$has_div = false;
	foreach ($ffErrors as $msg) {
		if (!$has_div) {
			$buffer .= $divstring;
			$has_div = true;
		}
		$buffer .= "\n	<div class=\"" . $msg['type'] . '">' .
		    $msg['message'] . "</div>";
	}

	/* return final buffer */
	if ($has_div)
		$buffer .= "\n</div></div>";
	return ($buffer . "\n</body></html>\n");
}

// set to the user defined error handler
set_error_handler("ffErrorHandler");

ob_start("ffOutputHandler", 0, false);
