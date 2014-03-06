<?php
/**
 * FusionForge : Project Management Facility
 *
 * Copyright 1999-2001 (c) VA Linux Systems, Tim Perdue
 * Copyright 2002 GForge, LLC, Tim Perdue
 * Copyright 2010, FusionForge Team
 * Copyright (C) 2011 Alain Peyrat - Alcatel-Lucent
 * Copyright 2014, Franck Villaume - TrivialDev
 * Copyright 2014, Stéphane-Eymeric Bredthauer
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

$pagename = "pm_browse_custom";

$offset = getIntFromRequest('offset');
if ($offset < 0) {
	$offset = 0 ;
}
$max_rows = getIntFromRequest('max_rows');

$ptf = new ProjectTaskFactory($pg);
if (!$ptf || !is_object($ptf)) {
	exit_error(_('Could Not Get ProjectTaskFactory'),'pm');
} elseif ($ptf->isError()) {
	exit_error($ptf->getErrorMessage(),'pm');
}

$_order = getStringFromRequest('_order');
$_sort_order = getStringFromRequest('_sort_order');
$set = getStringFromRequest('set');
$_assigned_to = getIntFromRequest('_assigned_to');
$_status = getStringFromRequest('_status');
$_category_id = getIntFromRequest('_category_id');
$_view = getStringFromRequest('_view');

$paging = 0;
if (session_loggedin()) {
    $u = UserManager::instance()->getCurrentUser();
	if (getStringFromRequest('setpaging')) {
		/* store paging preferences */
		$paging = getIntFromRequest('nres');
		if (!$paging) {
			$paging = 25;
		}
		$u->setPreference("paging", $paging);
	} else
		$paging = $u->getPreference("paging");
}
if (!$paging) {
	$paging = 25;
}

$ptf->setup($offset,$_order,$paging,$set,$_assigned_to,$_status,$_category_id,$_view,$_sort_order);
if ($ptf->isError()) {
	exit_error($ptf->getErrorMessage(),'pm');
}
$pt_arr =& $ptf->getTasks(true);
if ($ptf->isError()) {
	exit_error($ptf->getErrorMessage(),'pm');
}

$_assigned_to=$ptf->assigned_to;
$_status=$ptf->status;
$_order=$ptf->order;
$_sort_order=$ptf->sort_order;
$_category_id=$ptf->category;
$_view=$ptf->view_type;

html_use_coolfieldset();

pm_header(array('title'=>_('Browse tasks'),'group_project_id'=>$group_project_id));

/*
		creating a custom technician box which includes "any" and "unassigned"
*/
$engine = RBACEngine::getInstance () ;
$techs = $engine->getUsersByAllowedAction ('pm', $pg->getID(), 'tech') ;

$tech_id_arr = array () ;
$tech_name_arr = array () ;

foreach ($techs as $tech) {
	$tech_id_arr[] = $tech->getID() ;
	$tech_name_arr[] = $tech->getRealName() ;
}
$tech_id_arr[]='0';
$tech_name_arr[]=_('Any');

$tech_box=html_build_select_box_from_arrays ($tech_id_arr,$tech_name_arr,'_assigned_to',$_assigned_to,true,_('Unassigned'));

/*
		creating a custom category box which includes "any" and "none"
*/
$res_cat=$pg->getCategories();
$cat_id_arr=util_result_column_to_array($res_cat,0);
$cat_id_arr[]='0';  //this will be the 'any' row
$cat_name_arr=util_result_column_to_array($res_cat,1);
$cat_name_arr[]=_('Any');
$cat_box=html_build_select_box_from_arrays ($cat_id_arr,$cat_name_arr,'_category_id',$_category_id,true,'none');

/*
	Creating a custom order box
*/
$order_title_arr=array();
$order_title_arr[]=_('Task Id');
$order_title_arr[]=_('Task Summary');
$order_title_arr[]=_('Start Date');
$order_title_arr[]=_('End Date');
$order_title_arr[]=_('Percent Complete');
$order_title_arr[]=_('Priority');

$order_col_arr=array();
$order_col_arr[]='project_task_id';
$order_col_arr[]='summary';
$order_col_arr[]='start_date';
$order_col_arr[]='end_date';
$order_col_arr[]='percent_complete';
$order_col_arr[]='priority';

$order_box=html_build_select_box_from_arrays ($order_col_arr,$order_title_arr,'_order',$_order,false);

/*
	Creating a custom sort box
*/

$sort_title_arr=array();
$sort_title_arr[]=_('Ascending');
$sort_title_arr[]=_('Descending');

$sort_col=array();
$sort_col[]='ASC';
$sort_col[]='DESC';

$sort_box=html_build_select_box_from_arrays($sort_col,$sort_title_arr,'_sort_order',$_sort_order,false);
/*
	Creating View array
*/
$view_arr=array();
$view_arr[]=_('Summary');
$view_arr[]=_('Detailed');
$order_col_arr=array();
$view_col_arr[]='summary';
$view_col_arr[]='detail';
$view_box=html_build_select_box_from_arrays ($view_col_arr,$view_arr,'_view',$_view,false);

$rows=count($pt_arr);
$totalTasks = $pg->getCount($_status, $_category_id);

if (session_loggedin()) {
	/* logged in users get configurable paging */
	echo '<form action="'. getStringFromServer('PHP_SELF') .'?group_id='.$group_id.'&group_project_id='.$pg->getID().'&offset='.$offset.'" method="post">'."\n";

}
printf('<p>' . _('Displaying results %1$s out of %2$d total.'),$rows ? ($offset + 1).'-'.($offset + $rows) : '0', $totalTasks);

if (session_loggedin()) {
	printf(' ' . _('Displaying %2$s results.') . "\n\t<input " .
			'type="submit" name="setpaging" value="%1$s" />' .
			"\n</p>\n</form>\n", _('Change'),
			html_build_select_box_from_array(array(
			'10', '25', '50', '100', '1000'), 'nres', $paging, 1));
} else {
	echo "</p>\n";
}

/*
	Show the new pop-up boxes to select assigned to and/or status
*/
echo '	<form action="'. getStringFromServer('PHP_SELF') .'?group_id='.$group_id.'&group_project_id='.$group_project_id.'" method="post">
	<input type="hidden" name="set" value="custom" />
	<table>
	<tr>
		<td>'._('Assignee')._(': ').'<br />'. $tech_box .'</td>
		<td>'._('Status')._(': ').'<br />'. $pg->statusBox('_status',$_status,true, _('Any')) .'</td>
		<td>'._('Category')._(': ').'<br />'. $cat_box .'</td>
		<td>'._('Sort On')._(': ').'<br />'. $order_box . $sort_box .'</td>
		<td>'._('Detail View')._(': ').'<br />'. $view_box .'</td>
		<td><br /><input type="submit" name="submit" value="'._('Browse').'" /></td>
	</tr></table></form>';

if ($rows < 1) {

	echo '
		<p class="information">'._('No Matching Tasks found').'</p>
		<p />
		<p class="important">'._('Add tasks using the link above').'</p>';
	echo db_error();
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
		echo '
		<form name="taskList" action="'. getStringFromServer('PHP_SELF') .'?group_id='.$group_id.'&amp;group_project_id='.$pg->getID().'" method="post">
		<input type="hidden" name="func" value="massupdate" />';

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
		'priority'=>0);

	$title_arr=array();
	$title_arr[] = "";
	$title_arr[]=_('Task Id');
	if ($display_col['summary'])
		$title_arr[]=_('Task Summary');
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

	echo $GLOBALS['HTML']->listTableTop ($title_arr);

	$now=time();

	for ($i=0; $i < $rows; $i++) {
		$url = getStringFromServer('PHP_SELF')."?func=detailtask&project_task_id=".$pt_arr[$i]->getID()."&group_id=".$group_id."&group_project_id=".$group_project_id;

		echo '
			<tr class="priority'.$pt_arr[$i]->getPriority().'"><td style="width:16px; background-color:#FFFFFF">' .
			util_make_link("/export/rssAboTask.php?tid=" .
			    $pt_arr[$i]->getID(), html_image('ic/rss.png',
			    16, 16, array('border' => '0'))
			) . "</td>\n" .
			'<td>'.
			($IS_ADMIN?'<input type="checkbox" name="project_task_id_list[]" value="'.
			$pt_arr[$i]->getID() .'" /> ':'').
			$pt_arr[$i]->getID() ."</td>\n";
		if ($display_col['summary'])
			echo '<td>'.util_make_link($url,$pt_arr[$i]->getSummary())."</td>\n";
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
				<td>&nbsp;</td><td colspan="'.(count($title_arr)-1).'">'. nl2br( $pt_arr[$i]->getDetails() ) .'</td>
			</tr>';

		}
	}

	echo $GLOBALS['HTML']->listTableBottom();

	/*
	 Show extra rows for <-- Prev / Next -->
	*/
	if ($offset > 0) {
		echo util_make_link (getStringFromServer('PHP_SELF').'?func=browse&group_project_id='.$group_project_id.'&group_id='.$group_id.'&offset='.($offset-$paging),'<strong>← '._('previous').'</strong>');
		echo '&nbsp;&nbsp;';	
	} 
	$pages = $totalTasks / $paging;
	$currentpage = intval($offset / $paging);
	if ($pages > 1) {
		$skipped_pages=false;
		for ($j=0; $j<$pages; $j++) {
			if ($pages > 20) {
				if ((($j > 4) && ($j < ($currentpage-5))) || (($j > ($currentpage+5)) && ($j < ($pages-5)))) {
					if (!$skipped_pages) {
						$skipped_pages=true;
						echo "....&nbsp;";
					}
					continue;
				} else {
					$skipped_pages=false;
				}
			}
			if ($j * $paging == $offset) {
				echo '<strong>'.($j+1).'</strong>&nbsp;&nbsp;';
			} else {
				echo util_make_link (getStringFromServer('PHP_SELF').'?func=browse&group_project_id='.$group_project_id.'&group_id='.$group_id.'&offset='.($j*$paging),'<strong>'.($j+1).'</strong>').'&nbsp;&nbsp;';
			}
		}
	}	
	if ( $totalTasks > $offset + $paging) {
		echo util_make_link (getStringFromServer('PHP_SELF').'?func=browse&group_project_id='.$group_project_id.'&group_id='.$group_id.'&offset='.($offset+$paging),'<strong>'._('next').' →</strong>');
	}	
	
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
			<div>
			<table class="fullwidth">
			<tr><td colspan="2">
			<p>
			<span class="important">'._('If you wish to apply changes to all items selected above, use these controls to change their properties and click once on “Mass Update”.').'</span>
			</p>
			</td></tr>

			<tr>
			<td><strong>'._('Category')._(':').
				'</strong><br />'. $pg->categoryBox ('category_id','xzxz',true,
				_('No Change')) .'</td>
			<td><strong>'._('Priority')._(':').
				'</strong><br />';
			build_priority_select_box ('priority', '100', true);
			echo '</td>
			</tr>

			<tr>
			<td><strong>'._('Assigned to')._(':').
				'</strong><br />'. $tech_box .'</td>
			<td><strong>'._('State')._(':').
				'</strong><br />'. $pg->statusBox ('status_id','xzxz',true,_('No Change')) .'</td>
			</tr>

			<tr><td><strong>'._('Subproject')._(':').'</strong><br />
			'.$pg->groupProjectBox('new_group_project_id',$group_project_id,false).'</td>
			<td><input type="submit" name="submit" value="'.
			_('Mass Update').'" /></td></tr>

			</table>
			</div>
			</fieldset>
		</form>';
	}
}

pm_footer();

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
