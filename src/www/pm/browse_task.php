<?php
/**
 * FusionForge : Project Management Facility
 *
 * Copyright 1999-2001 (c) VA Linux Systems, Tim Perdue
 * Copyright 2002 GForge, LLC, Tim Perdue
 * Copyright 2010, FusionForge Team
 * Copyright (C) 2011 Alain Peyrat - Alcatel-Lucent
 * Copyright 2014,2015, Franck Villaume - TrivialDev
 * Copyright 2014, Stéphane-Eymeric Bredthauer
 * Copyright 2015, nitendra tripathi
 * http://fusionforge.org
 *
 * This file is part of FusionForge. FusionForge is free software;
 * you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or (at your option)
 * any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with FusionForge; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

require_once $gfcommon.'include/UserManager.class.php';
require_once $gfcommon.'pm/ProjectTaskFactory.class.php';
//build page title to make bookmarking easier
//if a user was selected, add the user_name to the title
//same for status

global $HTML;
global $LUSER;

$pagename = "pm_browse_custom";

$start = getIntFromRequest('start');
if ($start < 0) {
	$start = 0;
}

$ptf = new ProjectTaskFactory($pg);
if (!$ptf || !is_object($ptf)) {
	exit_error(_('Could Not Get ProjectTaskFactory'), 'pm');
} elseif ($ptf->isError()) {
	exit_error($ptf->getErrorMessage(), 'pm');
}

$_order = getStringFromRequest('_order');
$_sort_order = getStringFromRequest('_sort_order');
$set = getStringFromRequest('set');
$_assigned_to = getIntFromRequest('_assigned_to');
$_status = getStringFromRequest('_status');
$_category_id = getIntFromRequest('_category_id');
$_view = getStringFromRequest('_view');

if (session_loggedin()) {
	if (getStringFromRequest('setpaging')) {
		/* store paging preferences */
		$paging = getIntFromRequest('nres');
		if (!$paging) {
			$paging = 25;
		}
		$LUSER->setPreference('paging', $paging);
	} else {
		$paging = $LUSER->getPreference('paging');
	}
}

if(!isset($paging) || !$paging)
	$paging = 25;

$ptf->setup($start, $_order, $paging, $set, $_assigned_to, $_status, $_category_id, $_view, $_sort_order);
if ($ptf->isError()) {
	exit_error($ptf->getErrorMessage(), 'pm');
}
$pt_arr =& $ptf->getTasks(true);
if ($ptf->isError()) {
	exit_error($ptf->getErrorMessage(), 'pm');
}

$_assigned_to = $ptf->assigned_to;
$_status = $ptf->status;
$_order = $ptf->order;
$_sort_order = $ptf->sort_order;
$_category_id = $ptf->category;
$_view = $ptf->view_type;

html_use_coolfieldset();

pm_header(array('title' => _('Browse tasks'), 'group_project_id' => $group_project_id));

/*
		creating a custom technician box which includes "any" and "unassigned"
*/
$engine = RBACEngine::getInstance();
$techs = $engine->getUsersByAllowedAction('pm', $pg->getID(), 'tech');

$tech_select_arr = array();

foreach ($techs as $tech) {
	$tech_select_arr[$tech->getID()] = $tech->getRealName() ;
}
$tech_select_arr[0] = _('Any');

$tech_box = html_build_select_box_from_assoc($tech_select_arr ,'_assigned_to', $_assigned_to, false, true, _('Unassigned'));

/*
		creating a custom category box which includes "any" and "none"
*/
$res_cat = $pg->getCategories();
$cat_id_arr = util_result_column_to_array($res_cat, 0);
$cat_id_arr[] = '0';  //this will be the 'any' row
$cat_name_arr = util_result_column_to_array($res_cat, 1);
$cat_name_arr[] = _('Any');
$cat_box = html_build_select_box_from_arrays($cat_id_arr, $cat_name_arr, '_category_id', $_category_id, true, 'none');

/*
	Creating a custom order box
*/
$order_select_arr = array();
$order_select_arr['project_task_id'] = _('Task Id');
$order_select_arr['summary'] = _('Task Summary');
$order_select_arr['start_date'] = _('Start Date');
$order_select_arr['end_date'] = _('End Date');
$order_select_arr['percent_complete'] = _('Percent Complete');
$order_select_arr['priority'] = _('Priority');

$order_box = html_build_select_box_from_assoc($order_select_arr, '_order', $_order, false, false);

/*
	Creating a custom sort box
*/

$sort_select_arr=array();
$sort_select_arr['ASC'] = _('Ascending');
$sort_select_arr['DESC'] = _('Descending');

$sort_box = html_build_select_box_from_assoc($sort_select_arr, '_sort_order', $_sort_order, false, false);
/*
	Creating View array
*/
$view_select_arr = array();
$view_select_arr['summary'] = _('Summary');
$view_select_arr['detail'] = _('Detailed');
$view_box = html_build_select_box_from_assoc($view_select_arr, '_view', $_view, false, false);

$rows = count($pt_arr);
$totalTasks = $pg->getCount($_status, $_category_id);
$max = ($rows > ($start + $paging)) ? ($start + $paging) : $rows;

echo $HTML->paging_top($start, $paging, $totalTasks, $max, '/pm/task.php?group_id='.$group_id.'&group_project_id='.$pg->getID());

/*
	Show the new pop-up boxes to select assigned to and/or status
*/
echo $HTML->openForm(array('action' => '/pm/task.php?group_id='.$group_id.'&group_project_id='.$group_project_id, 'method' => 'post'));
echo '	<input type="hidden" name="set" value="custom" />
	<table>
	<tr>
		<td>'._('Assignee')._(': ').'<br />'. $tech_box .'</td>
		<td>'._('Status')._(': ').'<br />'. $pg->statusBox('_status',$_status,true, _('Any')) .'</td>
		<td>'._('Category')._(': ').'<br />'. $cat_box .'</td>
		<td>'._('Sort On')._(': ').'<br />'. $order_box . $sort_box .'</td>
		<td>'._('Detail View')._(': ').'<br />'. $view_box .'</td>
		<td><input type="submit" name="submit" value="'._('Browse').'" /></td>
	</tr></table>';
echo $HTML->closeForm();
if ($rows < 1) {
	echo $HTML->information(_('No Matching Tasks found'));
	echo '<p class="important">'._('Add tasks using the link above')."</p>\n";

} else {

	//create a new $set string to be used for next/prev button
	if ($set=='custom') {
		$set .= '&_assigned_to='.$_assigned_to.'&_status='.$_status;
	}

	/*
		Now display the tasks in a table with priority colors
	*/
	$IS_ADMIN = forge_check_perm ('pm', $pg->getID(), 'manager') ;

	if ($IS_ADMIN) {
		echo $HTML->openForm(array('name' => 'taskList', 'action' => '/pm/task.php?group_id='.$group_id.'&group_project_id='.$pg->getID(), 'method' => 'post'));
		echo '<input type="hidden" name="func" value="massupdate" />';

		$check_all = '
		<a href="javascript:checkAllTasks(1)">'._('Check all').'</a>
		-
		<a href="javascript:checkAllTasks(0)">'._('Clear all').'</a>';
	} else {
		$check_all = '';
	}

//this array can be customized to display whichever columns you want
//it could be built by querying a table on a per-user basis as well
	$display_col=array('summary'=>1,
		'start_date'=>1,
		'end_date'=>1,
		'percent_complete'=>1,
		'category'=>0,
		'assigned_to'=>0,
		'priority'=>0,
		'status' => 1
		);

	$title_arr=array();
	$title_arr[] = "";
	$title_arr[]=_('Task Id');
	if ($display_col['summary'])
		$title_arr[]=_('Task Summary');
	if ($display_col['status'])
		$title_arr[]=_('Status');
	if ($display_col['start_date'])
		$title_arr[]=_('Start Date');
	if ($display_col['end_date'])
		$title_arr[]=_('End Date');
	if ($display_col['percent_complete'])
		$title_arr[]=_('Percent Complete');
	if ($display_col['category'])
		$title_arr[]=_('Category');
	if ($display_col['assigned_to'])
		$title_arr[]=_('Assigned to');
	if ($display_col['priority'])
		$title_arr[]=_('Priority');


	echo $HTML->listTableTop($title_arr);

	$now = time();

	for ($i=0; $i < $rows; $i++) {
		$url = '/pm/task.php?func=detailtask&project_task_id='.$pt_arr[$i]->getID().'&group_id='.$group_id.'&group_project_id='.$group_project_id;

		echo '
			<tr class="priority'.$pt_arr[$i]->getPriority().'"><td style="width:16px; background-color:#FFFFFF">' .
			util_make_link('/export/rssAboTask.php?tid=' .
			    $pt_arr[$i]->getID(), html_image('ic/rss.png', 16, 16)) . "</td>\n" .
			'<td>'.
			($IS_ADMIN?'<input type="checkbox" name="project_task_id_list[]" value="'.
			$pt_arr[$i]->getID() .'" /> ':'').
			$pt_arr[$i]->getID() ."</td>\n";
		if ($display_col['summary'])
			echo '<td>'.util_make_link($url,$pt_arr[$i]->getSummary())."</td>\n";
		if ($display_col['status'])
			echo '<td>'. $pt_arr[$i]->getStatusName() ."</td>\n";
		if ($display_col['start_date'])
			echo '<td>'.date(_('Y-m-d H:i'), $pt_arr[$i]->getStartDate() )."</td>\n";
		if ($display_col['end_date'])
			echo '<td>';
			if ($now>$pt_arr[$i]->getEndDate() && $pt_arr[$i]->getStatusId() != 2 ) {
				echo '<strong>* ';
				$x = "</strong>";
			} else {
				echo '&nbsp; ';
				$x = "";
			}
			echo date(_('Y-m-d H:i'), $pt_arr[$i]->getEndDate()) .
			    $x . "</td>\n";
		if ($display_col['percent_complete'])
			echo '<td>'. $pt_arr[$i]->getPercentComplete() ."%</td>\n";
		if ($display_col['category'])
			echo '<td>'. $pt_arr[$i]->getCategoryName() ."</td>\n";
		if ($display_col['assigned_to'])
			echo '<td>'. $pg->renderAssigneeList($pt_arr[$i]->getAssignedTo()) ."</td>\n";
		if ($display_col['priority'])
			echo '<td>'. $pt_arr[$i]->getPriority() ."</td>\n";

		echo '
			</tr>';

		if ($_view=="detail") {
			echo '
			<tr class="priority'.$pt_arr[$i]->getPriority() .'">
				<td>&nbsp;</td><td colspan="'.(count($title_arr)-1).'">'. nl2br($pt_arr[$i]->getDetails()) .'</td>
			</tr>';
		}
	}

	echo $HTML->listTableBottom();
	/*
	 Show extra rows for <-- Prev / Next -->
	*/
	echo $HTML->paging_bottom($start, $paging, $totalTasks, '/pm/task.php?func=browse&group_project_id='.$group_project_id.'&group_id='.$group_id);

	echo '<div style="display:table;width:100%">';
	echo '<div style="display:table-row">';

	echo '<div style="display:table-cell">';
	echo _('* Denotes overdue tasks');
	echo '</div>';

	echo '<div style="display:table-cell;text-align:right">';
	show_priority_colors_key();
	echo '</div>';

	echo '</div>';

	echo '<div style="display:table-row">';

	echo '<div style="display:table-cell">'.$check_all.'</div>';
//	echo '<div style="display:table-cell;text-align:right">'.$pager.'</div>'."\n";

	echo '</div>';
	echo '</div>';

	if ($IS_ADMIN) {
		/*
			creating a custom technician box which includes "No Change" and "Nobody"
		*/

		$engine = RBACEngine::getInstance () ;
		$techs = $engine->getUsersByAllowedAction ('pm', $pg->getID(), 'tech') ;

		$tech_id_arr = array () ;
		$tech_name_arr = array () ;

		foreach ($techs as $tech) {
			$tech_id_arr[] = $tech->getID() ;
			$tech_name_arr[] = $tech->getRealName() ;
		}
		$tech_id_arr[]='100.1';
		$tech_name_arr[]=_('Unassigned');

		$tech_box=html_build_select_box_from_arrays ($tech_id_arr,$tech_name_arr,'assigned_to',
		'100',true,_('No Change'));

		echo '<fieldset id="fieldset1_closed" class="coolfieldset">
			<legend>'._('Mass Update').'</legend>
			<div>';
		echo $HTML->information(_('If you wish to apply changes to all items selected above, use these controls to change their properties and click once on “Mass Update”.'));
		echo '	<table class="infotable">

			<tr>
			<td>'._('Category')._(':').'</td>
			<td>'. $pg->categoryBox ('category_id','xzxz',true, _('No Change')) .'</td>
			</tr>

			<tr>
			<td>'._('Priority')._(':').'</td>
			<td>';
			echo build_priority_select_box ('priority', '100', true);
			echo '</td>
			</tr>

			<tr>
			<td>'._('Assigned to')._(':').'</td>
			<td>'. $tech_box .'</td>
			</tr>

			<tr>
			<td>'._('State')._(':').'</td>
			<td>'. $pg->statusBox ('status_id','xzxz',true,_('No Change')) .'</td>
			</tr>

			<tr>
			<td>'._('Subproject')._(':').'</td>
			<td>'.$pg->groupProjectBox('new_group_project_id',$group_project_id,false).'</td>
			</tr>

			<tr>
			<td colspan="2"><input type="submit" name="submit" value="'. _('Mass Update').'" /></td>
			</tr>

			</table>
			</div>
			</fieldset>';
		echo $HTML->closeForm();
	}
}

pm_footer();

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
