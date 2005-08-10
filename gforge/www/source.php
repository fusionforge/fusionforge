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

$file = getStringFromRequest('file');

if (!$file) {
	exit_error($Language->getText('source','missing_file'), $Language->getText('source','missing_file_text'));
}

if (strstr($file,'..')) {
	exit_error($Language->getText('source','invalid_argument'), $Language->getText('source','invalid_argument_text'));
}

$dir = dirname($file);

// If this is a legal dir, then it is under the docroot, else use basename
if ($dir) {
	$fname = getStringFromServer('DOCUMENT_ROOT') . $file;
} else {
	$fname = basename($file);
}

if (!file_exists($fname) || @is_dir($fname)) {
	exit_error($Language->getText('source','file_not_found'), $Language->getText('source','file_not_found_text'));
}

$HTML->header(array('title'=>$Language->getText('source','source_of',$file),'pagename'=>'viewsource'));

show_source($fname);


$HTML->footer(array());
?>
