<?php

/**
 * Show source code of a given file.
 *
 * Copyright 2002 (C) GForge Development Team
 * Copyright 2010 (c) Franck Villaume
 * http://fusionforge.org/
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

require_once('env.inc.php');
require_once $gfcommon.'include/pre.php';


if (!forge_get_config('show_source')) {
	exit_permission_denied('home');
}

$file = getStringFromRequest('file');

if (!$file) {
	exit_missing_param('',array(_('A file must be specified for this page.')),'home');
}

if (strstr($file,'..')) {
	exit_error(_('The file argument is invalid.'),'home');
}

$dir = dirname($file);

// If this is a legal dir, then it is under the docroot, else use basename
if ($dir) {
	$fname = getStringFromServer('DOCUMENT_ROOT') . $file;
} else {
	$fname = basename($file);
}

if (!file_exists($fname) || @is_dir($fname)) {
	exit_error(_('Cannot find specified file to display.'),'home');
}

$HTML->header(array('title'=>sprintf(_('Source of %1$s'), $file)));

show_source($fname);


$HTML->footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
