<?php
/**
 * GForge Project Management Facility
 *
 * Copyright 2002 GForge, LLC
 * http://gforge.org/
 *
 * @version   $Id$
 */
/*

	Project/Task Manager
	By Tim Perdue, Sourceforge, 11/99
	Heavy rewrite by Tim Perdue April 2000

	Total rewrite in OO and GForge coding guidelines 12/2002 by Tim Perdue
*/

require_once('pre.php');
require_once('www/pm/include/ProjectGroupHTML.class');
require_once('common/pm/ProjectGroupFactory.class');

if (!$group_id) {
	exit_no_group();
}

$g =& group_get_object($group_id);
if (!$g || !is_object($g)) {
	exit_no_group();
} elseif ($g->isError()) {
	exit_error('Error',$g->getErrorMessage());
}

$pgf = new ProjectGroupFactory($g);
if (!$pgf || !is_object($pgf)) {
    exit_error('Error','Could Not Get Factory');
} elseif ($pgf->isError()) {
    exit_error('Error',$pgf->getErrorMessage());
}

$pg_arr =& $pgf->getProjectGroups();
if ($pg_arr && $pgf->isError()) {
	exit_error('Error',$pgf->getErrorMessage());
}

pm_header(array('title'=>$Language->getText('pm','title'). $g->getPublicName(),'pagename'=>'pm','sectionvals'=>$g->getPublicName()));

if (count($pg_arr) < 1 || $pg_arr == false) {
	echo '<p>'.$Language->getText('pm','noprj').'</p>';
} else {
	echo '
	<p>'.$Language->getText('pm','intro').'</p>';

	/*
		Put the result set (list of projects for this group) into a column with folders
	*/

	for ($j = 0; $j < count($pg_arr); $j++) {
		if ($pg_arr[$j]->isError()) {
			echo $pg_arr[$j]->getErrorMessage();
		}
		echo '
		<p><a href="/pm/task.php?group_project_id='. $pg_arr[$j]->getID().
		'&amp;group_id='.$group_id.'&amp;func=browse">' .
		html_image("ic/taskman20w.png","20","20",array("border"=>"0")) . ' &nbsp;'.
		$pg_arr[$j]->getName() .'</a><br />'.
		$pg_arr[$j]->getDescription() .'</p>';
	}

}

pm_footer(array());

?>
