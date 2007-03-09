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

require_once('../env.inc.php');
require_once('pre.php');
require_once('www/pm/include/ProjectGroupHTML.class');
require_once('common/pm/ProjectGroupFactory.class');

$group_id = getIntFromRequest('group_id');
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

pm_header(array('title'=>$Language->getText('pm','title')));

if (count($pg_arr) < 1 || $pg_arr == false) {
	echo '<p>'.$Language->getText('pm','noprj').'</p>';
} else {
	echo '
	<p>'.$Language->getText('pm','intro').'</p>';

	/*
		Put the result set (list of projects for this group) into a column with folders
	*/
	$tablearr=array($Language->getText('pm_index','short_name'),
	$Language->getText('pm_index','description'),
	$Language->getText('pm_index','open'),
	$Language->getText('pm_index','total'));
	echo $HTML->listTableTop($tablearr);

	for ($j = 0; $j < count($pg_arr); $j++) {
		if (!is_object($pg_arr[$j])) {
			//just skip it
		} elseif ($pg_arr[$j]->isError()) {
			echo $pg_arr[$j]->getErrorMessage();
		} else {
		echo '
		<tr '. $HTML->boxGetAltRowStyle($j) . '>
			<td><a href="'.$GLOBALS['sys_urlprefix'].'/pm/task.php?group_project_id='. $pg_arr[$j]->getID().'&amp;group_id='.$group_id.'&amp;func=browse">' .
		html_image("ic/taskman20w.png","20","20",array("border"=>"0")) . ' &nbsp;'.
		$pg_arr[$j]->getName() .'</a></td>
			<td>'.$pg_arr[$j]->getDescription() .'</td>
			<td style="text-align:center">'. (int) $pg_arr[$j]->getOpenCount().'</td>
			<td style="text-align:center">'. (int) $pg_arr[$j]->getTotalCount().'</td>
		</tr>';
		}
	}
	echo $HTML->listTableBottom();

}

pm_footer(array());

?>
