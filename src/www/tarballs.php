<?php

/**
 * GForge SCM Tarballs download page
 *
 * Portions Copyright 1999-2001 (c) VA Linux Systems
 * The rest Copyright 2003-2004 (c) GForge
 *
 */

$no_gz_buffer=true;

require_once('env.inc.php');
require_once $gfcommon.'include/pre.php';

// get current information
$group_id=getIntFromGet('group_id');
if (!$group_id) {
	exit_no_group();
}
$group =& group_get_object($group_id);
if (!$group || !is_object($group)) {
	exit_error(_('Error'),
		_('Error creating group'));
} else if ($group->isError()) {
	exit_error(_('Error'),
		$group->getErrorMessage());
}

// Tarball downloads require the same permissions as SCM read access
if (!forge_check_perm ('scm', $group->getID(), 'read')) {
 	exit_permission_denied();
}

$group_name=$group->getUnixName();

$filename=$group_name.'-scmroot.tar.gz';

if (file_exists(forge_get_config('scm_tarballs_path').'/'.$filename)) {
	Header('Content-disposition: filename="'.str_replace('"', '', $filename).'"');
	Header("Content-type: application/x-gzip");
	$length = filesize(forge_get_config('scm_tarballs_path').'/'.$filename);
	Header("Content-length: ".$length);

	readfile_chunked(forge_get_config('scm_tarballs_path').'/'.$filename);
} else {
	session_redirect(util_make_url("/404.php"));
}

?>
