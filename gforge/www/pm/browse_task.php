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

pm_header(array('title'=>'Browse Tasks','pagename'=>$pagename,'group_project_id'=>$group_project_id,'sectionvals'=>$g->getPublicName()));

/*
		creating a custom technician box which includes "any" and "unassigned"
*/

$res_tech=$pg->getTechnicians();

$tech_id_arr=util_result_column_to_array($res_tech,0);
$tech_id_arr[]='0';  //this will be the 'any' row

$tech_name_arr=util_result_column_to_array($res_tech,1);
$tech_name_arr[]='Any';

$tech_box=html_build_select_box_from_arrays ($tech_id_arr,$tech_name_arr,'_assigned_to',$_assigned_to,true,'Unassigned');

/*
		creating a custom category box which includes "any" and "none"
*/

$res_cat=$pg->getCategories();

$cat_id_arr=util_result_column_to_array($res_cat,0);
$cat_id_arr[]='0';  //this will be the 'any' row

$cat_name_arr=util_result_column_to_array($res_cat,1);
$cat_name_arr[]='Any';

$cat_box=html_build_select_box_from_arrays ($cat_id_arr,$cat_name_arr,'_category_id',$_category_id,false);

/*
	Creating a custom sort box
*/
$title_arr=array();
$title_arr[]='Task ID';
$title_arr[]='Summary';
$title_arr[]='Start Date';
$title_arr[]='End Date';
$title_arr[]='Percent Complete';
$title_arr[]='Priority';

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
		<td><font size="-1">Assignee:<br />'. $tech_box .'</td>
		<td><font size="-1">Status:<br />'. $pg->statusBox('_status',$_status,'Any') .'</td>
		<td><font size="-1">Category:<br />'. $cat_box .'</td>
		<td><font size="-1">Sort On:<br />'. $order_box .'</td>
		<td><font size="-1"><input type="SUBMIT" name="SUBMIT" value="Browse"></td>
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

		echo '
			<tr bgcolor="'.html_get_priority_color( $pt_arr[$i]->getPriority() ).'">'.
			'<td><a href="/pm/task.php?func=detailtask'.
			'&project_task_id='. $pt_arr[$i]->getID() .
			'&group_id='.$group_id.
			'&group_project_id='. $group_project_id .'">'.
			$pt_arr[$i]->getID() .'</a></td>'.
			'<td>'. $pt_arr[$i]->getSummary() .'</td>'.
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
			<strong><-- Previous 50</strong></a>';
	} else {
		echo '&nbsp;';
	}
	echo '</td><td>&nbsp;</td><td colspan="2">';

	if ($rows==50) {
		echo '<a href="/pm/task.php?func=browse&group_project_id='.
			$group_project_id.'&group_id='.$group_id.'&offset='.($offset+50).
			'"><strong>Next 50 --></strong></a>';
	} else {
		echo '&nbsp;';
	}
	echo '</td></tr>';

	echo $GLOBALS['HTML']->listTableBottom();

	echo '<p>* Denotes overdue tasks';
	show_priority_colors_key();

}

pm_footer(array());

?>
