<?php

/**
 * GForge CVS Tarballs download page
 *
 * Portions Copyright 1999-2001 (c) VA Linux Systems
 * The rest Copyright 2003-2004 (c) GForge
 *
 * @version $Id$
 */

$no_gz_buffer=true;

require_once('pre.php');

session_require(array('group'=>$group_id));

// get current information
$group =& group_get_object($group_id);
exit_assert_object($group,'Group');

$perm =& $group->getPermission( session_get_user() );
exit_assert_object($perm,'Permission');

$group_name=$group->getUnixName();

$filename=$group_name.'-cvsroot.tar.gz';

if (file_exists($sys_cvs_tarballs_path.'/'.$filename)) {
	Header('Content-disposition: filename="'.str_replace('"', '', $filename).'"');
	Header("Content-type: application/x-gzip");
	$length = filesize($sys_cvs_tarballs_path.'/'.$filename);
	Header("Content-length: ".$length);

	readfile($sys_cvs_tarballs_path.'/'.$filename);
} else {
	session_redirect("/404.php");
}

?>
