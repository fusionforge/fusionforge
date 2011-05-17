<?php

$sysdebug_ignored = forge_get_config('sysdebug_ignored');
if (!isset($ffErrors))
	$ffErrors = array();

// error handler function
function ffErrorHandler($errno, $errstr, $errfile, $errline)
{
	global $ffErrors, $sysdebug_ignored;

	if (!$sysdebug_ignored && error_reporting() == 0)
		/* prepended @ to statement => ignore */
		return false;

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

	if (forge_get_config('sysdebug_backtraces'))
		$msg .= "\n" .
		    '<pre style="font-weight:normal; font-size:90%; color:#000066;">' .
		    htmlentities(debug_string_backtrace()) . "</pre>";
	
	$ffErrors[] = array('type' => $type, 'message' => $msg);
	/* Don't execute PHP internal error handler */
	return true;
}


function ffOutputHandler($buffer) {
	global $ffErrors, $gfcommon, $sysDTDs;

	if (! getenv ('SERVER_SOFTWARE')) {
		return $buffer ;
	}

	/* stop calling ffErrorHandler */
	restore_error_handler();

	$dtdpath = $gfcommon . 'include/';
	// this is, sadly, necessary (especially in ff-plugin-mediawiki)
	$pre_tag = "<pre style=\"margin:0; padding:0; border:0;\">";

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

	/* generate buffer for checking */
	$cbuf = str_ireplace('http://www.w3.org/TR/xhtml1/DTD/',
	    'file://' . $dtdpath, $buffer);
	if ($has_div)
		$cbuf .= "\n</div></div>";
	$cbuf .= "\n</body></html>\n";

	/* now check XHTML validity… two means */
	$valck = array();
	$appsrc = false;

	if (forge_get_config('sysdebug_xmlstarlet')) {
		/* xmlstarlet (well-formed, DTD and DOCTYPE, encoding */
		$dspec = array(
			0 => array("pipe", "r"),
			1 => array("pipe", "w"),
			2 => array("pipe", "w"),
		    );
		$xmlstarlet = proc_open("xmlstarlet val -d " .
		    escapeshellarg($dtdpath . $sysDTDs['transitional']['dtdfile']) .
		    " -", $dspec, $pipes);
		$rv = 0;
		if (is_resource($xmlstarlet)) {
			fwrite($pipes[0], $cbuf);
			fclose($pipes[0]);
			$sout = stream_get_contents($pipes[1]);
			$serr = stream_get_contents($pipes[2]);
			fclose($pipes[1]);
			fclose($pipes[2]);
			$rv = proc_close($xmlstarlet);
		} else
			$valck[] = array(
				'msg' => "could not run xmlstarlet"
			    );
		if ($rv) {
			$valck[] = array(
				'msg' => "xmlstarlet found that this document is not valid (errorlevel $rv)!",
				'extra' => $pre_tag . htmlspecialchars(trim($serr .
				    "\n\n" . $sout)) . "</pre>",
				'type' => "error"
			    );
			$appsrc = true;
		}
	}

	$sysdebug_akelos = forge_get_config('sysdebug_akelos');
	if ($sysdebug_akelos) {
		/* Akelos XHTML Validator (most other stuff) */
		require_once($gfcommon . "include/XhtmlValidator.php");
		$XhtmlValidator = new XhtmlValidator();
		$sbuf = explode("<html", $cbuf, 2);
		$sbuf[1] = "<html" . $sbuf[1];
		$vbuf = $sbuf[1];
		if ($XhtmlValidator->validate($vbuf) === false) {
			//$vbuf = $XhtmlValidator->highlightErrors($sbuf[1]);
			$errs = '<ul><li>' . join("</li>\n<li>",
			    $XhtmlValidator->getErrors()) . '</li></ul>';
			$valck[] = array(
				'msg' => "Akelos XHTML Validator found some errors on this document!",
				'extra' => $errs,
				'type' => "error"
			    );
			$appsrc = true;
		}
	}

	/* append XHTML source code, if validation failed */
	if ($appsrc) {
		if (!$sysdebug_akelos || $vbuf == $sbuf[1])
			$vbuf = "<ol><li>" . $pre_tag . join("</pre></li>\n<li>" . $pre_tag, explode("\n", htmlentities(rtrim($cbuf)))) . "</pre></li></ol>";
		else
			$vbuf = $pre_tag . htmlentities(rtrim($sbuf[0])) . "</pre>" . $vbuf;
		$valck[] = array(
			'msg' => "Since XHTML validation failed, here’s the checked document for you to look at:",
			'extra' => $vbuf,
			'type' => 'normal'
		    );
	}

	/* append error messages from the validators */
	foreach ($valck as $msg) {
		if (!$has_div) {
			$buffer .= $divstring;
			$has_div = true;
		}
		$buffer .= "\n	<div class=\"" . $msg['type'] . '">' . $msg['msg'];
		if (isset($msg['extra']))
			$buffer .= "\n		<div style=\"font-weight:normal; font-size:90%; color:#333333;\">" .
			    $msg['extra'] . "</div>\n	";
		$buffer .= "</div>";
	}

	/* return final buffer */
	if ($has_div)
		$buffer .= "\n</div></div>";
	return ($buffer . "\n</body></html>\n");
}

if (forge_get_config('sysdebug_phphandler')) {
	// set to the user defined error handler
	set_error_handler("ffErrorHandler");
}

ob_start("ffOutputHandler", 0, false);
