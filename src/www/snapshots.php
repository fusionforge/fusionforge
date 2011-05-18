<?php

/**
 * FusionForge SCM Snapshots download page
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2003-2004 (c) GForge
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

$no_gz_buffer=true;

require_once('env.inc.php');
require_once $gfcommon.'include/pre.php';

// Get current information
$group_id=getIntFromGet('group_id');
if (!$group_id) {
	exit_no_group();
}
$group = group_get_object($group_id);
if (!$group || !is_object($group)) {
	exit_error(_('Error creating group'),'home');
} else if ($group->isError()) {
	exit_error($group->getErrorMessage(),'home');
}

// Snapshot downloads require the same permissions as SCM read access
if (!forge_check_perm ('scm', $group->getID(), 'read')) {
	exit_permission_denied('home');
}

// Download file
$group_name=$group->getUnixName();

$filename=$group_name.'-scm-latest.tar.gz';

if (file_exists(forge_get_config('scm_snapshots_path').'/'.$filename)) {
	Header('Content-disposition: filename="'.str_replace('"', '', $filename).'"');
	Header('Content-type: application/x-gzip');
	$length = filesize(forge_get_config('scm_snapshots_path').'/'.$filename);
	Header('Content-length: '.$length);

	readfile_chunked(forge_get_config('scm_snapshots_path').'/'.$filename);
} else {
	session_redirect(util_make_url('/404.php'));
}

?>
