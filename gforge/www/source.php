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

require_once('env.inc.php');
require_once('pre.php');

global $sys_show_source;
if (!$sys_show_source) {
	exit_permission_denied();
}

$file = getStringFromRequest('file');

if (!$file) {
	exit_error(_('Missing File Argument'), _('Missing File Argument'));
}

if (strstr($file,'..')) {
	exit_error(_('Invalid File Argument'), _('Invalid File Argument'));
}

$dir = dirname($file);

// If this is a legal dir, then it is under the docroot, else use basename
if ($dir) {
	$fname = getStringFromServer('DOCUMENT_ROOT') . $file;
} else {
	$fname = basename($file);
}

if (!file_exists($fname) || @is_dir($fname)) {
	exit_error(_('File Not Found'), _('File Not Found'));
}

$HTML->header(array('title'=>sprintf(_('Source of %1$s'), $file)));

show_source($fname);


$HTML->footer(array());
?>
