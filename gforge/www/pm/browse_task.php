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

$offset = getIntFromRequest('offset');
$max_rows = getIntFromRequest('max_rows');

$ptf = new ProjectTaskFactory($pg);
if (!$ptf || !is_object($ptf)) {
	exit_error('Error','Could Not Get ProjectTaskFactory');
} elseif ($ptf->isError()) {
	exit_error('Error',$ptf->getErrorMessage());
}

$offset = getIntFromRequest('offset');
$_order = getStringFromRequest('_order');
$set = getStringFromRequest('set');
$_assigned_to = getIntFromRequest('_assigned_to');
$_status = getStringFromRequest('_status');
$_category_id = getIntFromRequest('_category_id');
$_view = getStringFromRequest('_view');

$ptf->setup($offset,$_order,$max_rows,$set,$_assigned_to,$_status,$_category_id,$_view);
if ($ptf->isError()) {
	exit_error('Error',$ptf->getErrorMessage());
}
$pt_arr =& $ptf->getTasks(true);
if ($ptf->isError()) {
	exit_error('Error',$ptf->getErrorMessage());
}

$_assigned_to=$ptf->assigned_to;
$_status=$ptf->status;
$_order=$ptf->order;
$_category_id=$ptf->category;
$_view=$ptf->view_type;

pm_header(array('title'=>$Language->getText('pm_browsetask','title'),'group_project_id'=>$group_project_id));

/*
		creating a custom technician box which includes "any" and "unassigned"
*/
$res_tech=$pg->getTechnicians();
$tech_id_arr=util_result_column_to_array($res_tech,0);
$tech_id_arr[]='0';  //this will be the 'any' row
$tech_name_arr=util_result_column_to_array($res_tech,1);
$tech_name_arr[]=$Language->getText('pm','tech_any');
$tech_box=html_build_select_box_from_arrays ($tech_id_arr,$tech_name_arr,'_assigned_to',
$_assigned_to,true,$Language->getText('pm','unassigned'));

/*
		creating a custom category box which includes "any" and "none"
*/
$res_cat=$pg->getCategories();
$cat_id_arr=util_result_column_to_array($res_cat,0);
$cat_id_arr[]='0';  //this will be the 'any' row
$cat_name_arr=util_result_column_to_array($res_cat,1);
$cat_name_arr[]=$Language->getText('pm','cat_any');
$cat_box=html_build_select_box_from_arrays ($cat_id_arr,$cat_name_arr,'_category_id',$_category_id,true,'none');


/*
	Creating a custom sort box
*/
$order_title_arr=array();
$order_title_arr[]=$Language->getText('pm','task_id');
$order_title_arr[]=$Language->getText('pm','summary');
$order_title_arr[]=$Language->getText('pm','start_date');
$order_title_arr[]=$Language->getText('pm','end_date');
$order_title_arr[]=$Language->getText('pm','percent_complete');

$order_col_arr=array();
$order_col_arr[]='project_task_id';
$order_col_arr[]='summary';
$order_col_arr[]='start_date';
$order_col_arr[]='end_date';
$order_col_arr[]='percent_complete';
$order_box=html_build_select_box_from_arrays ($order_col_arr,$order_title_arr,'_order',$_order,false);

/*
	Creating View array
*/
$view_arr=array();
$view_arr[]=$Language->getText('pm','view_type_summary');
$view_arr[]=$Language->getText('pm','view_type_detail');
$order_col_arr=array();
$view_col_arr[]='summary';
$view_col_arr[]='detail';
$view_box=html_build_select_box_from_arrays ($view_col_arr,$view_arr,'_view',$_view,false);

/*
	Show the new pop-up boxes to select assigned to and/or status
*/
echo '	<form action="'. getStringFromServer('PHP_SELF') .'?group_id='.$group_id.'&amp;group_project_id='.$group_project_id.'" method="post">
	<input type="hidden" name="set" value="custom" />
	<table width="10%" border="0">
	<tr>
		<td><font size="-1">'.$Language->getText('pm_modtask','assignee').'<br />'. $tech_box .'</font></td>
		<td><font size="-1">'.$Language->getText('pm','status').'<br />'. $pg->statusBox('_status',$_status,true, $Language->getText('pm','status_any')) .'</font></td>
		<td><font size="-1">'.$Language->getText('pm','category').'<br />'. $cat_box .'</font></td>
		<td><font size="-1">'.$Language->getText('pm_modtask','sort_on').'<br />'. $order_box .'</font></td>
		<td><font size="-1">'.$Language->getText('pm','view_type').'<br />'. $view_box .'</font></td>
		<td><font size="-1"><input type="submit" name="submit" value="'.$Language->getText('pm_browsetask','browse').'" /></font></td>
	</tr></table></form><p />';


$rows=count($pt_arr);
if ($rows < 1) {

	echo '
		<h1>'.$Language->getText('pm','no_tasks_found').'</h1>
		<p />
		<strong>'.$Language->getText('pm','no_tasks_found_text').'</strong>';
	echo db_error();
} else {

	//create a new $set string to be used for next/prev button
	if ($set=='custom') {
		$set .= '&amp;_assigned_to='.$_assigned_to.'&amp;_status='.$_status;
	}

	/*
		Now display the tasks in a table with priority colors
	*/
	$IS_ADMIN=($pg->userIsAdmin());

	if ($IS_ADMIN) {
		echo '
		<form name="taskList" action="'. getStringFromServer('PHP_SELF') .'?group_id='.$group_id.'&group_project_id='.$pg->getID().'" METHOD="POST">
		<input type="hidden" name="func" value="massupdate">';
	}

//this array can be customized to display whichever columns you want
//it could be built by querying a table on a per-user basis as well
	$display_col=array('summary'=>1,
		'start_date'=>1,
		'end_date'=>1,
		'percent_complete'=>1,
		'category'=>0,
		'assigned_to'=>0,
		'priority'=>0);

	$title_arr=array();
	$title_arr[]=$Language->getText('pm','task_id');
	if ($display_col['summary'])
		$title_arr[]=$Language->getText('pm','summary');
	if ($display_col['start_date'])
		$title_arr[]=$Language->getText('pm','start_date');
	if ($display_col['end_date'])
		$title_arr[]=$Language->getText('pm','end_date');
	if ($display_col['percent_complete'])
		$title_arr[]=$Language->getText('pm','percent_complete');
	if ($display_col['category'])
		$title_arr[]=$Language->getText('pm','category');
	if ($display_col['assigned_to'])
		$title_arr[]=$Language->getText('pm','assigned_to');
	if ($display_col['priority'])
		$title_arr[]=$Language->getText('pm','priority');

	echo $GLOBALS['HTML']->listTableTop ($title_arr);

	$now=time();

	for ($i=0; $i < $rows; $i++) {
		$url = getStringFromServer('PHP_SELF')."?func=detailtask&amp;project_task_id=".$pt_arr[$i]->getID()."&amp;group_id=".$group_id."&amp;group_project_id=".$group_project_id;
		
		echo '
			<tr bgcolor="'.html_get_priority_color( $pt_arr[$i]->getPriority() ).'">'.
			'<td>'.
			($IS_ADMIN?'<input type="CHECKBOX" name="project_task_id_list[]" value="'.
			$pt_arr[$i]->getID() .'"> ':'').
			$pt_arr[$i]->getID() .'</td>';
		if ($display_col['summary'])
			echo '<td><a href="'.$url.'">'.$pt_arr[$i]->getSummary() .'</a></td>';
		if ($display_col['start_date']) 
			echo '<td>'.date($sys_datefmt, $pt_arr[$i]->getStartDate() ).'</td>';
		if ($display_col['end_date']) 
			echo '<td>'. (($now>$pt_arr[$i]->getEndDate() && $pt_arr[$i]->getStatusId() != 2 )?'<strong>* ':'&nbsp; ') .
				date($sys_datefmt, $pt_arr[$i]->getEndDate() ).'</strong></td>';
		if ($display_col['percent_complete']) 
			echo '<td>'. $pt_arr[$i]->getPercentComplete() .'%</td>';
		if ($display_col['category']) 
			echo '<td>'. $pt_arr[$i]->getCategoryName() .'</td>';
		if ($display_col['assigned_to'])
			echo '<td>'. $pg->renderAssigneeList($pt_arr[$i]->getAssignedTo()) .'</td>';
		if ($display_col['priority'])
			echo '<td>'. $pt_arr[$i]->getPriority() .'</td>';

		echo '
			</tr>';

		if ($_view=="detail") {
			echo '
			<tr bgcolor="'.html_get_priority_color( $pt_arr[$i]->getPriority() ).'">
				<td>&nbsp;</td><td colspan="'.(count($title_arr)-1).'">'. nl2br( $pt_arr[$i]->getDetails() ) .'</td>
			</tr>';

		}
/*

		//PLEASE MAKE THIS INTO A SEPARATE PAGE INSTEAD OF doing such a big hack to this one

		//show detail view
		} else if ($_view == "detail") {
			echo '
				<tr bgcolor="'.html_get_priority_color( $pt_arr[$i]->getPriority() ).'">'.
				'<td>'.
				($IS_ADMIN?'<input type="CHECKBOX" name="project_task_id_list[]" value="'.
				$pt_arr[$i]->getID() .'"> ':'').
				$pt_arr[$i]->getID() .'</td>'.
				'<td><a href="'.$url.'">'.$pt_arr[$i]->getSummary() .'</a></td>'.
				'<td>'.date($sys_datefmt, $pt_arr[$i]->getStartDate() ).'</td>'.
				'<td>'. (($now>$pt_arr[$i]->getEndDate() )?'<strong>* ':'&nbsp; ') .
					date($sys_datefmt, $pt_arr[$i]->getEndDate() ).'</strong></td>'.
				'<td>'. $pt_arr[$i]->getPercentComplete() .'%</td></tr>';
				echo '
				<tr>'.
					'<td>&nbsp;</td>'.
					'<td colspan="4">';
				echo nl2br( $pt_arr[$i]->getDetails() );
				//
				// Show details
				//
				$result=$pt_arr[$i]->getMessages();
				$rows=db_numrows($result);
		
				if ($rows > 0) {
					echo '
					<h3>'.$Language->getText('pm_include_taskhtml','followups').'</h3>
					<p>';
		
					$title_arr=array();
					$title_arr[]=$Language->getText('pm_include_taskhtml','comment');
					$title_arr[]=$Language->getText('pm_include_taskhtml','date');
					$title_arr[]=$Language->getText('pm_include_taskhtml','by');
				
					echo $GLOBALS['HTML']->listTableTop ($title_arr);
		
					for ($j=0; $j < $rows; $j++) {
						echo '
						<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($j) .'>
							<td>'. nl2br(db_result($result, $j, 'body')).'</td>
							<td valign="TOP">'.date($sys_datefmt,db_result($result, $j, 'postdate')).'</td>
							<td valign="TOP">'.db_result($result, $j, 'user_name').'</td></tr>';
					}
		
					echo $GLOBALS['HTML']->listTableBottom();
		
				} else {
					echo '
					<h3>'.$Language->getText('pm_include_taskhtml','no_comments').'</h3>';
				}
				//
				// End of show details
				//
				echo '</td></tr>';
		}
		*/
	}

	/*
		Show extra rows for <-- Prev / Next -->
	*/
	echo '<tr><td colspan="2">';
	if ($offset > 0) {
		echo '<a href="/pm/task.php?func=browse&amp;group_project_id='.
			$group_project_id.'&amp;group_id='.$group_id.'&amp;offset='.($offset-50).'">
			<strong>'.$Language->getText('pm_browsetask','previous').'<--</strong></a>';
	} else {
		echo '&nbsp;';
	}
	echo '</td><td>&nbsp;</td><td colspan="2">';

	if ($rows==50) {
		echo '<a href="/pm/task.php?func=browse&amp;group_project_id='.
			$group_project_id.'&amp;group_id='.$group_id.'&amp;offset='.($offset+50).
			'"><strong>'.$Language->getText('pm_browsetask','next').' --></strong></a>';
	} else {
		echo '&nbsp;';
	}
	echo '</td></tr>';

	echo $GLOBALS['HTML']->listTableBottom();

	if ($IS_ADMIN) {
		/*
			creating a custom technician box which includes "No Change" and "Nobody"
		*/

		$res_tech=$pg->getTechnicians();

		$tech_id_arr=util_result_column_to_array($res_tech,0);
		$tech_id_arr[]='100.1';  //this will be the 'any' row

		$tech_name_arr=util_result_column_to_array($res_tech,1);
		$tech_name_arr[]=$Language->getText('pm','unassigned');

		$tech_box=html_build_select_box_from_arrays ($tech_id_arr,$tech_name_arr,'assigned_to',
		'100',true,$Language->getText('pm_browsetask','no_change'));

		echo '<script language="JavaScript">
	<!--
	function checkAll(val) {
		al=document.taskList;
		len = al.elements.length;
		var i=0;
		for( i=0 ; i<len ; i++) {
			if (al.elements[i].name==\'project_task_id_list[]\') {
				al.elements[i].checked=val;
			}
		}
	}
	//-->
	</script>

			<table width="100%" border="0">
			<tr><td colspan="2">
<font size=1>
<a href="javascript:checkAll(1)">'.$Language->getText('pm_browsetask','check_all').'</a>
-
   <a href="javascript:checkAll(0)">'.$Language->getText('pm_browsetask','clear_all').'</a>
</font>
<p>
<font color="#FF0000">'.$Language->getText('pm_browsetask','admin_mass_update').'
			</td></tr>

			<tr>
			<td><strong>'.$Language->getText('pm_detailtask','category').
				'</strong><br />'. $pg->categoryBox ('category_id','xzxz',true,
				$Language->getText('pm_browsetask','no_change')) .'</td>
			<td><strong>'.$Language->getText('pm_detailtask','priority').
				'</strong><br />';
			echo build_priority_select_box ('priority', '100', true);
			echo '</td>
			</tr>

			<tr>
			<td><strong>'.$Language->getText('pm_detailtask','assigned_to').
				'</strong><br />'. $tech_box .'</td>
			<td><strong>'.$Language->getText('tracker','status').
				'</strong><br />'. $pg->statusBox ('status_id','xzxz',true,$Language->getText('pm_browsetask','no_change')) .'</td>
			</tr>

			<tr><td><strong>'.$Language->getText('pm_detailtask','subproject').'</strong><br />
			'.$pg->groupProjectBox('new_group_project_id',$group_project_id,false).'</td>
			<td align="middle"><input type="submit" name="submit" value="'.
			$Language->getText('pm_browsetask','mass_update').'"></td></tr>

			</table>
		</form>';
	}

	echo '<p />'.$Language->getText('pm_browsetask','overdue_tasks');
	show_priority_colors_key();

}

pm_footer(array());

?>
