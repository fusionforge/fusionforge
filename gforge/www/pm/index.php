<?php
/**
  *
  * SourceForge Project/Task Manager (PM)
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */


require_once('pre.php');
require_once('www/pm/pm_utils.php');

if ($group_id) {

	pm_header(array('title'=>'Projects for '.group_getname($group_id),'pagename'=>'pm','sectionvals'=>group_getname($group_id)));

	if (session_loggedin() && user_ismember($group_id)) {
		$public_flag='0,1';
	} else {
		$public_flag='1';
	}

	$sql="SELECT * FROM project_group_list WHERE group_id='$group_id' AND is_public IN ($public_flag)";

	$result = db_query ($sql);
	$rows = db_numrows($result); 
	if (!$result || $rows < 1) {
		echo $Language->getText('pm', 'noprj');
		pm_footer(array());
		exit;
	}

	echo '
		<P>
		Choose a Subproject and you can browse/edit/add tasks to it.
		<P>';

	/*
		Put the result set (list of forums for this group) into a column with folders
	*/

	for ($j = 0; $j < $rows; $j++) { 
		echo '
		<A HREF="/pm/task.php?group_project_id='.db_result($result, $j, 'group_project_id').
		'&group_id='.$group_id.'&func=browse">' .
		html_image("ic/index.png","15","13",array("BORDER"=>"0")) . ' &nbsp;'.
		db_result($result, $j, 'project_name').'</A><BR>'.
		db_result($result, $j, 'description').'<P>';
	}

} else {
	pm_header(array('title'=>'Choose a Group First','pagename'=>'pm'));
	echo '<H1>Error - choose a group first</H1>';
}
pm_footer(array()); 

?>
