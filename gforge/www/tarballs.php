<?php
/**
  *
  * Fetch a multimedia data from database
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */

$no_gz_buffer=true;

require_once('pre.php');

session_require(array('group'=>$group_id));

// get current information
$group =& group_get_object($group_id);
exit_assert_object($group,'Group');

$perm =& $group->getPermission( session_get_user() );
exit_assert_object($perm,'Permission');

$sys_tar_dir='/var/lib/gforge/cvstarballs';

$group_name=$group->getUnixName();

$filename=$group_name.'-cvsroot.tar.gz';

if (file_exists($sys_tar_dir.'/'.$filename)) {
	Header("Content-disposition: filename=".$filename);
	Header("Content-type: application/binary");
	$length = filesize($sys_tar_dir.'/'.$filename);
	Header("Content-length: $length");

	readfile($sys_tar_dir.'/'.$filename);
} else {
	session_redirect("/404.php");
}
?>
