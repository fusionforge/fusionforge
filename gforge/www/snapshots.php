<?php

/**
 * GForge SCM Snapshots download page
 *
 * Portions Copyright 1999-2001 (c) VA Linux Systems
 * The rest Copyright 2003-2004 (c) GForge
 *
 * @version $Id$
 */

$no_gz_buffer=true;

require_once('pre.php');

// Get current information
$group_id=getIntFromGet('group_id');
if (!$group_id) {
	exit_no_group();
}
$group =& group_get_object($group_id);
if (!$group || !is_object($group)) {
	exit_error($Language->getText('general','error'),
		$Language->getText('error','error_creating_group'));
} else if ($group->isError()) {
	exit_error($Language->getText('general','error'),
		$group->getErrorMessage());
}

// Snapshot can be downloaded only if anon SCM is enabled or if the
// logged in user belongs the group
$permission = $group->enableAnonSCM();
if(session_loggedin()) {
	$perm =& $group->getPermission(session_get_user());
 	if ($perm && is_object($perm) && !$perm->isError() && $perm->isMember()) {
 		$permission = true;
 	}
}
if (!$permission) {
 	exit_permission_denied();
}

// Download file
$group_name=$group->getUnixName();

$filename=$group_name.'-scm-latest.tar.gz';

if (file_exists($sys_scm_snapshots_path.'/'.$filename)) {
	Header('Content-disposition: filename="'.str_replace('"', '', $filename).'"');
	Header('Content-type: application/x-gzip');
	$length = filesize($sys_scm_snapshots_path.'/'.$filename);
	Header('Content-length: '.$length);

	readfile($sys_scm_snapshots_path.'/'.$filename);
} else {
	session_redirect('/404.php');
}

?>
