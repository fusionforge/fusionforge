<?php

/**
 * GForge SCM Snapshots download page
 *
 * Portions Copyright 1999-2001 (c) VA Linux Systems
 * The rest Copyright 2003-2004 (c) GForge
 *
 * @version $Id$
 */

# WARNING: this code does NOT make any verification: the snapshot of a
# private repository can be downloaded...
# TODO: how to verify that a project allows anonscm?
# The gforge-plugin-scmcvs plugin use the enableAnonSCM() function in
# Project.class while the gforge-plugin-scmscm plugin uses
# UsesAnonSVN() function in SVNPlugin.class...

$no_gz_buffer=true;

require_once('pre.php');

session_require(array('group'=>$group_id));

// get current information
$group =& group_get_object($group_id);
exit_assert_object($group,'Group');

$group_name=$group->getUnixName();

$filename=$group_name.'-scm-latest.tar.gz';

if (file_exists($sys_scm_snapshots_path.'/'.$filename)) {
	Header('Content-disposition: filename="'.str_replace('"', '', $filename).'"');
	Header("Content-type: application/x-gzip");
	$length = filesize($sys_scm_snapshots_path.'/'.$filename);
	Header("Content-length: ".$length);

	readfile($sys_scm_snapshots_path.'/'.$filename);
} else {
	session_redirect("/404.php");
}

?>
