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

require_once('common/pm/ProjectTaskFactory.class');
//build page title to make bookmarking easier
//if a user was selected, add the user_name to the title
//same for status

$pagename = "pm_browse_custom";

$ptf = new ProjectTaskFactory($pg);
if (!$ptf || !is_object($ptf)) {
	exit_error('Error','Could Not Get ProjectTaskFactory');
} elseif ($ptf->isError()) {
	exit_error('Error',$ptf->getErrorMessage());
}

$ptf->setup($offset,$_order,$max_rows,$set,$_assigned_to,$_status,$_category_id);
if ($ptf->isError()) {
	exit_error('Error',$ptf->getErrorMessage());
}

$pt_arr =& $ptf->getTasks();
if ($ptf->isError()) {
	exit_error('Error',$ptf->getErrorMessage());
}

$_assigned_to=$ptf->assigned_to;
$_status=$ptf->status;
$_order=$ptf->order;

pm_header(array('title'=>$Language->getText('pm_browsetask','title'),'pagename'=>$pagename,'group_project_id'=>$group_project_id,'sectionvals'=>$g->getPublicName()));

/*
		creating a custom technician box which includes "any" and "unassigned"
*/

$res_tech=$pg->getTechnicians();

$tech_id_arr=util_result_column_to_array($res_tech,0);
$tech_id_arr[]='0';  //this will be the 'any' row

$tech_name_arr=util_result_column_to_array($res_tech,1);
$tech_name_arr[]=$Language->getText('pm','any');

$tech_box=html_build_select_box_from_arrays ($tech_id_arr,$tech_name_arr,'_assigned_to',$_assigned_to,true,$Language->getText('pm','unassigned'));

/*
		creating a custom category box which includes "any" and "none"
*/

$res_cat=$pg->getCategories();

$cat_id_arr=util_result_column_to_array($res_cat,0);
$cat_id_arr[]='0';  //this will be the 'any' row

$cat_name_arr=util_result_column_to_array($res_cat,1);
$cat_name_arr[]=$Language->getText('pm','any');;

$cat_box=html_build_select_box_from_arrays ($cat_id_arr,$cat_name_arr,'_category_id',$_category_id,false);

/*
	Creating a custom sort box
*/
$title_arr=array();
$title_arr[]=$Language->getText('pm','task_id');
$title_arr[]=$Language->getText('pm','summary');
$title_arr[]=$Language->getText('pm','start_date');
$title_arr[]=$Language->getText('pm','end_date');
$title_arr[]=$Language->getText('pm','percent_complete');
$title_arr[]=$Language->getText('pm','priority');

$order_col_arr=array();
$order_col_arr[]='project_task_id';
$order_col_arr[]='summary';
$order_col_arr[]='start_date';
$order_col_arr[]='end_date';
$order_col_arr[]='percent_complete';
$order_col_arr[]='priority';
$order_box=html_build_select_box_from_arrays ($order_col_arr,$title_arr,'_order',$_order,false);

/*
	Show the new pop-up boxes to select assigned to and/or status
*/
echo '<table width="10%" border="0">
	<form action="'. $PHP_SELF .'?group_id='.$group_id.'&group_project_id='.$group_project_id.'" method="post">
	<input type="hidden" name="set" value="custom">
	<tr>
		<td><font size="-1">'.$Language->getText('pm_modtask','assignee').'<br />'. $tech_box .'</td>
		<td><font size="-1">'.$Language->getText('pm','status').'<br />'. $pg->statusBox('_status',$_status,'Any') .'</td>
		<td><font size="-1">'.$Language->getText('pm','category').'<br />'. $cat_box .'</td>
		<td><font size="-1">'.$Language->getText('pm_modtask','sort_on').'<br />'. $order_box .'</td>
		<td><font size="-1"><input type="SUBMIT" name="SUBMIT" value="'.$Language->getText('pm_browsetask','browse').'"></td>
	</tr></form></table><p>';


$rows=count($pt_arr);
if ($rows < 1) {

	echo '
		<h1>No Matching Tasks found</h1>
		<p>
		<strong>Add tasks using the link above</strong>';
	echo db_error();
} else {

	//create a new $set string to be used for next/prev button
	if ($set=='custom') {
		$set .= '&_assigned_to='.$_assigned_to.'&_status='.$_status;
	}

	/*
		Now display the tasks in a table with priority colors
	*/

	echo $GLOBALS['HTML']->listTableTop ($title_arr);

	$now=time();

	for ($i=0; $i < $rows; $i++) {
		$url = "/pm/task.php?func=detailtask&project_task_id=".$pt_arr[$i]->getID()."&group_id=".$group_id."&group_project_id=".$group_project_id;
		echo '
			<tr bgcolor="'.html_get_priority_color( $pt_arr[$i]->getPriority() ).'">'.
			'<td><a href="'.$url.'">'.$pt_arr[$i]->getID() .'</a></td>'.
			'<td><a href="'.$url.'">'.$pt_arr[$i]->getSummary() .'</a></td>'.
			'<td>'.date('Y-m-d', $pt_arr[$i]->getStartDate() ).'</td>'.
			'<td>'. (($now>$pt_arr[$i]->getEndDate() )?'<strong>* ':'&nbsp; ') .
				date('Y-m-d',$pt_arr[$i]->getEndDate() ).'</td>'.
			'<td>'. $pt_arr[$i]->getPercentComplete() .'%</td>'.
			'<td>'. $pt_arr[$i]->getPriority() .'</td></tr>';

	}

	/*
		Show extra rows for <-- Prev / Next -->
	*/
	echo '<tr><td colspan="2">';
	if ($offset > 0) {
		echo '<a href="/pm/task.php?func=browse&group_project_id='.
			$group_project_id.'&group_id='.$group_id.'&offset='.($offset-50).'">
			<strong>'.$Language->getText('pm_browsetask','previous').'<--</strong></a>';
	} else {
		echo '&nbsp;';
	}
	echo '</td><td>&nbsp;</td><td colspan="2">';

	if ($rows==50) {
		echo '<a href="/pm/task.php?func=browse&group_project_id='.
			$group_project_id.'&group_id='.$group_id.'&offset='.($offset+50).
			'"><strong>'.$Language->getText('pm_browsetask','next').' --></strong></a>';
	} else {
		echo '&nbsp;';
	}
	echo '</td></tr>';

	echo $GLOBALS['HTML']->listTableBottom();

	echo '<p>'.$Language->getText('pm_browsetask','overdue_tasks');
	show_priority_colors_key();

}

pm_footer(array());

?>
