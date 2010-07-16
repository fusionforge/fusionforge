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
			$type = 'error';
			break;

		case E_USER_WARNING:
			$type = 'warning';
			break;

		case E_USER_NOTICE:
			$type = 'notice';
			break;

		default:
			$type = 'unknown';
			break;
	}
	
	$ffErrors[] = array('type' => $type, 'message' => $msg);
	/* Don't execute PHP internal error handler */
	return true;
}


function ffErrorDisplay() {
	global $ffErrors;

	if (isset($ffErrors) && $ffErrors) {
		echo '<div id="ffErrors">';
		foreach ($ffErrors as $msg) {
			echo '<div class="'.$msg['type'].'">'.$msg['message'].'</div>'."\n";
		}
		echo '</div>';
	}
}

// set to the user defined error handler
set_error_handler("ffErrorHandler");

register_shutdown_function('ffErrorDisplay');
