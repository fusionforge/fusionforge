<?php
/**
  *
  * SourceForge Exports: Export project news as HTML
  *
  * Parameters:
  *	group_id	-	group_id
  *	limit		-	number of items to export
  *	show_summaries	-	0 to show only headlines, 1 to also show
  *				summaries
  *	flat		-	1 to use minimal HTML formatting
  *	
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
require_once('www/news/news_utils.php');

$group_name=$_GET['group_name'];
$group_id=$_GET['group_id'];
if ( $group_name ) {
	$group =& group_get_object_by_name($group_name);
	if ( ! $group_id && $group ) $group_id=$group->getID();
}

if ($group) {
	echo '<h2>Welcome to '.$group->getPublicName().' project!</h2>
<p>';
	echo $group->getDescription();
}
else echo "No such group";
?>
