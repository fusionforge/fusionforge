<?php
/**
  *
  * Project Summary Page
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */


require_once('../env.inc.php');
require_once('pre.php');    

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

header ("Location: ".$GLOBALS['sys_urlprefix']."/projects/". group_getunixname($group_id) ."/");

?>
