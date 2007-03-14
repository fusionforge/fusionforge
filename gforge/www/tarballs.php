<?php

/**
 * GForge SCM Tarballs download page
 *
 * Portions Copyright 1999-2001 (c) VA Linux Systems
 * The rest Copyright 2003-2004 (c) GForge
 *
 * @version $Id$
 */

$no_gz_buffer=true;

require_once('env.inc.php');
require_once('pre.php');

// get current information
$group_id=getIntFromGet('group_id');
if (!$group_id) {
	exit_no_group();
}
session_require(array('group'=>$group_id));
$group =& group_get_object($group_id);
if (!$group || !is_object($group)) {
	exit_error(_('Error'),
		_('Error creating group'));
} else if ($group->isError()) {
	exit_error(_('Error'),
		$group->getErrorMessage());
}

$perm =& $group->getPermission( session_get_user() );
if (!$perm || !is_object($perm)) {
	exit_error(_('Error'),
		_('Error creating permission'));
} else if ($perm->isError()) {
	exit_error(_('Error'),
		$perm->getErrorMessage());
}

$group_name=$group->getUnixName();

$filename=$group_name.'-scmroot.tar.gz';

if (file_exists($sys_scm_tarballs_path.'/'.$filename)) {
	Header('Content-disposition: filename="'.str_replace('"', '', $filename).'"');
	Header("Content-type: application/x-gzip");
	$length = filesize($sys_scm_tarballs_path.'/'.$filename);
	Header("Content-length: ".$length);

	readfile_chunked($sys_scm_tarballs_path.'/'.$filename);
} else {
	session_redirect("/404.php");
}

?>
