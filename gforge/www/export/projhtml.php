<?php
/**
  *
  * SourceForge Exports: Export project summary page as HTML
  *
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */

require_once('pre.php');
require_once('project_summary.php');

$group_name=$_GET['group_name'];
$group_id=$_GET['group_id'];
if ( $group_name ) {
	$group =& group_get_object_by_name($group_name);
	if ( ! $group_id && $group ) $group_id=$group->getID();
}

if ($group_id) echo project_summary($group_id,$mode,$no_table);
else echo "No such group";

?>
