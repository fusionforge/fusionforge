<?php
/**
  *
  * Project Summary Page
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  */


require_once('../env.inc.php');
require_once $gfcommon.'include/pre.php';

/*
	Project Summary Page
	Written by dtype Oct. 1999
*/
$group_id = getIntFromRequest("group_id");

if ((!$group_id) && $form_grp) {
	$group_id=$form_grp;
}

if (!$group_id) {
	exit_error("Missing Group Argument","A group must be specified for this page.");
}

if (isset ($sys_noforcetype) && $sys_noforcetype) {
	$project = &group_get_object($group_id);
	include $gfwww.'include/project_home.php';
} else {
	header ('Location: '.util_make_url ('/projects/'. group_getunixname($group_id) .'/'));
}

?>
