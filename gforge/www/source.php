<?php
/**
 *
 * Show source code of a given file.
 *
 * Given a file, show the source code for that file.
 *
 * Copyright 2002 (C) GForge Development Team
 *
 * @version   $Id$
 *
 */

require_once('pre.php');

global $sys_show_source;
if (!$sys_show_source) {
	exit_permission_denied();
}

if (!$file) {
	exit_error("Missing File Argument", "A file must be specified for this page.");
}

if (strstr($file,'..')) {
	exit_error("Invalid File Argument", "The file argument is invalid.");
}

$dir = dirname($file);

// If this is a legal dir, then it is under the docroot, else use basename
if ($dir) {
	$fname = $DOCUMENT_ROOT . $file;
} else {
	$fname = basename($file);
}

if (!file_exists($fname) || @is_dir($fname)) {
	exit_error("File Not Found", "Cannot find specified file to display.");
}

$HTML->header(array('title'=>"Source of $file",'pagename'=>'viewsource'));

show_source($fname);


$HTML->footer(array());
?>
