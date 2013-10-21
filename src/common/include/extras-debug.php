<?php
/**
 * FusionForge extradebug feature
 *
 * Copyright 2011, Fusionforge Team
 * http://fusionforge.org
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

// initialise globals used by the debugging code
$sysdebug_dberrors = forge_get_config('sysdebug_dberrors');
$sysdebug_dbquery = forge_get_config('sysdebug_dbquery');
$sysdebug_ignored = forge_get_config('sysdebug_ignored');
if (!isset($ffErrors)) {
	$ffErrors = array();
}

// error handler function
function ffErrorHandler($errno, $errstr, $errfile, $errline) {
	global $ffErrors, $sysdebug_ignored, $sysdebug__aborted;
	/* Debian-specific MediaWiki patch */
	global $wf__warnings_suppressed;

	if ($sysdebug__aborted) {
		/* inside the exception handler, ignore everything */
		return true;
	}

	if (isset($wf__warnings_suppressed) && $wf__warnings_suppressed) {
		/*
		 * MediaWiki makes use of surrounding sloppily written,
		 * unclean, unsafe code with wfSuppressWarnings(); and
		 * wfRestoreWarnings(); calls, we never want to see them
		 */
		return true;
	}

	if (!$sysdebug_ignored && error_reporting() == 0) {
		/* prepended @ to statement => ignore */
		return false;
	}

	$msg = "[$errno] $errstr ($errfile at $errline)";

	// Display messages only once.
	foreach ($ffErrors as $m) {
		if ($m['message'] == $msg) {
			return true;
		}
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

	if (forge_get_config('sysdebug_backtraces')) {
		$msg .= "\n" .
		    '<pre style="font-weight:normal; font-size:90%; color:#000066; line-height:100%;">' .
		    htmlentities(debug_string_backtrace()) . "</pre>";
	}

	$ffErrors[] = array(
		'type' => $type,
		'message' => $msg
	);
	/* Don't execute PHP internal error handler */
	return true;
}

// output buffer finaliser function
function ffOutputHandler($buffer) {
	global $ffErrors, $sysdebug_enable, $sysdebug__aborted,
	    $sysdebug_lazymode_on, $sysdebug_doframe, $gfcommon,
	    $sysDTDs, $sysXMLNSs, $HTML;

	if ($sysdebug__aborted) {
		/* called from exception handler, discard */
		$p = strrpos($buffer, "\r\n");
		return (($p === false) ? "" : substr($buffer, $p + 2));
	}

	if (!getenv('SERVER_SOFTWARE')) {
		return $buffer;
	}

	/* in case we’re aborted */
	if (!$sysdebug_enable) {
		return $buffer;
	}

	/* if content-type != text/html* assume abortion */
	if ($sysdebug_lazymode_on) {
		$thdr = 'content-type:';
		$tstr = 'content-type: text/html';
		foreach (headers_list() as $h) {
			if (strncasecmp($h, $thdr, strlen($thdr))) {
				continue;
			}
			if (strncasecmp($h, $tstr, strlen($tstr))) {
				/* application/something, maybe */
				return $buffer;
			}
		}
	}

	/* stop calling ffErrorHandler */
	restore_error_handler();

	$dtdpath = $gfcommon . 'include/';
	// this is, sadly, necessary (especially in ff-plugin-mediawiki)
	$pre_tag = "<pre style=\"margin:0; padding:0; border:0; line-height:125%;\">";

	$divstring = "\n\n" . '<script type="text/javascript">//<![CDATA[
		function toggle_ffErrors() {
			var errorsblock = document.getElementById("ffErrorsBlock");
			var errorsgroup = document.getElementById("ffErrors");
			if (errorsblock.style.display == "none") {
				errorsblock.style.display = "block";
				errorsgroup.style.right = "10px";
			} else {
				errorsblock.style.display = "none";
				errorsgroup.style.right = "300px";
			}
		}' . "\n//]]></script>\n<div id=\"ffErrors\">\n" .
	    '<a href="javascript:toggle_ffErrors();">Click to toggle</a>' .
	    "\n<div id=\"ffErrorsBlock\">";

	if (!($doctype = util_ifsetor($HTML->doctype))) {
		$doctype = 'transitional';
	}

	if ($sysdebug_doframe) {
		$initial = '<?xml version="1.0" encoding="utf-8"?>' .
		    $sysDTDs[$doctype]['doctype'] .
		    '<html xml:lang="en" ' . $sysXMLNSs .
		    "><head><title>AJAX frame</title></head><body>\n";
		$bufferstrip = strlen($initial);
		$buffer = $initial . $buffer . '</body></html>';
	}

	/* cut off </body></html> (hopefully only) at the end */
	$buffer = rtrim($buffer);	/* spaces, newlines, etc. */
	$bufend = array(false, substr($buffer, -100));
	if (substr($buffer, -strlen("</html>")) != "</html>") {
		$bufend[0] = true;
		$ffErrors[] = array(
			'type' => "error",
			'message' => htmlentities("does not end with </html> tag"),
		    );
		$buffer = str_ireplace("</html>", "", $buffer);
	} else {
		$buffer = substr($buffer, 0, -strlen("</html>"));
	}
	$buffer = rtrim($buffer);	/* spaces, newlines, etc. */
	if (substr($buffer, -strlen("</body>")) != "</body>") {
		$bufend[0] = true;
		$ffErrors[] = array(
			'type' => "error",
			'message' => htmlentities("does not end with </body> tag"),
		    );
		$buffer = str_ireplace("</body>", "", $buffer);
	} else {
		$buffer = substr($buffer, 0, -strlen("</body>"));
	}
	$buffer = rtrim($buffer);	/* spaces, newlines, etc. */

	if ($bufend[0]) {
		$ffErrors[] = array(
			'type' => "info",
			'message' => "The output has ended thus: " .
			    htmlentities($bufend[1]),
		   );
	}

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
	    'file://' . $dtdpath, str_ireplace('http://evolvis.org/DTD/',
	    'file://' . $dtdpath, $buffer));
	if ($has_div) {
		$cbuf .= "\n</div></div>";
	}
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
		    escapeshellarg($dtdpath . $sysDTDs[$doctype]['dtdfile']) .
		    " -e -", $dspec, $pipes);
		$rv = 0;
		if (is_resource($xmlstarlet)) {
			fwrite($pipes[0], $cbuf);
			fclose($pipes[0]);
			$sout = stream_get_contents($pipes[1]);
			$serr = stream_get_contents($pipes[2]);
			fclose($pipes[1]);
			fclose($pipes[2]);
			$rv = proc_close($xmlstarlet);
			/* work around Debian #627158 */
			$serr = join("\n", preg_grep(
			    '/^-:[0-9]*: Entity'." 'nbsp' ".'not defined$/',
			    explode("\n", $serr), PREG_GREP_INVERT));
		} else {
			$valck[] = array(
				'msg' => "could not run xmlstarlet",
			    );
		}
		if ($rv) {
			$valck[] = array(
				'msg' => "xmlstarlet found that this document is not valid (errorlevel $rv)!",
				'extra' => $pre_tag .
				    htmlspecialchars(trim($serr .
				    "\n\n" . $sout)) . "</pre>",
				'type' => "error",
			    );
			$appsrc = true;
		}
	}

	/* append XHTML source code, if validation failed */
	if ($appsrc) {
		$vbuf = "<ol><li>" . $pre_tag .
		    join(" </pre></li>\n<li>" . $pre_tag,
		    explode("\n", htmlentities(rtrim($cbuf)))) .
		    " </pre></li></ol>";
		$valck[] = array(
			'msg' => "Since XHTML validation failed, here’s the checked document for you to look at:",
			'extra' => $vbuf,
			'type' => 'normal',
		    );
	}

	/* append error messages from the validators */
	foreach ($valck as $msg) {
		if (!$has_div) {
			$buffer .= $divstring;
			$has_div = true;
		}
		if (!isset($msg['type']) || !$msg['type']) {
			$msg['type'] = 'unknown';
		}
		$buffer .= "\n	<div class=\"" . $msg['type'] . '">' .
		    $msg['msg'];
		if (isset($msg['extra'])) {
			$buffer .= "\n		<div style=\"font-weight:normal; font-size:90%; color:#333333;\">" .
			    $msg['extra'] . "</div>\n	";
		}
		$buffer .= "</div>";
	}

	/* return final buffer */
	if ($has_div) {
		$buffer .= "\n</div></div>";
	}
	if ($sysdebug_doframe) {
		return substr($buffer, $bufferstrip);
	} else {
		return $buffer . "\n</body></html>\n";
	}
}

// exception handler function
function ffExceptionHandler($e) {
	global $sysdebug__aborted;

	/* drop output buffers and error handler */
	$sysdebug__aborted = true;
	while (ob_get_length() > 0 && ob_end_clean()) {
		/* loop */ ;
	}
	restore_error_handler();

	/* issue exception information */
	header('HTTP/1.0 500 Exception not handled');
	header('Content-type: text/plain');
	echo "\r\nUncaught exception:\n" . str_replace("\r", "",
	    $e->getMessage() . "\n\nBacktrace:\n" . $e->getTraceAsString()) .
	    "\n";
	exit(1);
}

if (forge_get_config('sysdebug_phphandler')) {
	// set to the user defined error handler
	set_error_handler("ffErrorHandler");
}

set_exception_handler("ffExceptionHandler");

$sysdebug_lazymode_on = false;
$sysdebug_doframe = false;
$sysdebug__aborted = false;
ob_start("ffOutputHandler", 0, false);

function sysdebug_ajaxbody($enable=true) {
	global $sysdebug_doframe;

	$sysdebug_doframe = $enable;
}

function sysdebug_off($hdr=false, $replace=true, $resp=false) {
	global $ffErrors, $sysdebug_enable;

	if ($sysdebug_enable) {
		$sysdebug_enable = false;
		$buf = @ob_get_flush();

		if ($buf === false) {
			$buf = "";
		}

		/* if we had any old errors, log them */
		$olderrors = "";
		foreach ($ffErrors as $msg) {
			$olderrors .= "\n(" . $msg['type'] . ") " .
			    $msg['message'];
		}
		if ($olderrors) {
			if (!forge_get_config('sysdebug_backtraces')) {
				$olderrors .= "\n" . debug_string_backtrace();
			}
			$olderrors = rtrim($olderrors);
			$pfx = "";
			foreach (explode("\n",
			    "sysdebug_off: previous errors found:" . $olderrors)
			    as $olderrorline) {
				error_log($pfx . $olderrorline);
				/* followup lines get indented */
				$pfx = ">>> ";
			}
		}
	} else {
		$buf = false;
	}

	if ($hdr !== false) {
		if ($resp === false) {
			header($hdr, $replace);
		} else {
			header($hdr, $replace, $resp);
		}
	}

	return $buf;
}

function sysdebug_lazymode($enable) {
	global $sysdebug_lazymode_on;

	$sysdebug_lazymode_on = $enable ? true : false;
}

function ffDebug($type, $intro, $pretext=false) {
	global $ffErrors;

	if (!$type) {
		$type = 'debug';
	}
	$text = "";
	if ($intro) {
		$text .= htmlentities($intro);
	}
	if ($pretext) {
		$text .= '<pre style="font-weight:normal; font-size:90%; color:#000066;">' .
		    htmlentities($pretext) . "</pre>";
	}

	$ffErrors[] = array(
		'type' => $type,
		'message' => $text,
	    );
}
