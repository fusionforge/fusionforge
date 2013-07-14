<?php
/**
 * FusionForge : Project Management Facility
 *
 * Copyright 1999/2000, Sourceforge.net Tim Perdue
 * Copyright 2002 GForge, LLC, Tim Perdue
 * Copyright 2010, FusionForge Team
 * Copyright (C) 2012 Alain Peyrat - Alcatel-Lucent
 * Copyright 2013, Franck Villaume - TrivialDev
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

echo '<' . '?xml version="1.0" encoding="utf-8" ?' . ">\n" .
    $sysDTDs['transitional']['doctype']; ?>
<html <?php echo $sysXMLNSs; ?> xml:lang="en">

  <head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title><?php echo _('Gantt Chart');?></title>
	<link rel=stylesheet href="/scripts/jquery-teamwork-gantt/platform.css" type="text/css">
	<link rel=stylesheet href="/scripts/jquery-teamwork-gantt/libs/dateField/jquery.dateField.css" type="text/css">
	<link rel=stylesheet href="/scripts/jquery-teamwork-gantt/gantt.css" type="text/css">
	
	<script src="/scripts/jquery/jquery-1.8.3.js"></script>
	<script src="/scripts/jquery-ui/js/jquery-ui-1.9.2.custom.js"></script>
	<script src="/scripts/jquery-teamwork-gantt/libs/jquery.livequery.min.js"></script>
	<script src="/scripts/jquery-teamwork-gantt/libs/jquery.timers.js"></script>
	<script src="/scripts/jquery-teamwork-gantt/libs/platform.js"></script>
	<script src="/scripts/jquery-teamwork-gantt/libs/date.js"></script>
	<script src="/scripts/jquery-teamwork-gantt/libs/i18nJs.js"></script>
	<script src="/scripts/jquery-teamwork-gantt/libs/dateField/jquery.dateField.js"></script>
	<script src="/scripts/jquery-teamwork-gantt/libs/JST/jquery.JST.js"></script>

	<script src="/scripts/jquery-teamwork-gantt/ganttUtilities.js"></script>
	<script src="/scripts/jquery-teamwork-gantt/ganttTask.js"></script>
	<script src="/scripts/jquery-teamwork-gantt/ganttDrawer.js"></script>
	<script src="/scripts/jquery-teamwork-gantt/ganttGridEditor.js"></script>
	<script src="/scripts/jquery-teamwork-gantt/ganttMaster.js"></script>
  </head>
  <body style="background-color: #fff" >
<?php

require_once $gfcommon.'pm/ProjectTaskFactory.class.php';
require_once $gfwww.'include/unicode.php';

/* define global vars */
global $pg, $g;

$offset = getIntFromRequest('offset');
$_assigned_to = getIntFromRequest('_assigned_to', 0);
$_category_id = getIntFromRequest('_category_id');
$_order = getIntFromRequest('_order');
$_resolution = getStringFromRequest('_resolution');
$_status = getIntFromRequest('_status', 100);
$_order = getStringFromRequest('_order');
$max_rows = getIntFromRequest('max_rows',50);

$engine = RBACEngine::getInstance();
$techs = $engine->getUsersByAllowedAction('pm', $pg->getID(), 'tech');

$tech_id_arr = array();
$tech_name_arr = array();

$ptf = new ProjectTaskFactory($pg);
if (!$ptf || !is_object($ptf)) {
	exit_error(_('Could not get ProjectTaskFactory'), 'pm');
} elseif ($ptf->isError()) {
	exit_error(_('Error getting PTF: ').$ptf->getErrorMessage(), 'pm');
}

$ptf->setup($offset, $_order, $max_rows, 'custom', $_assigned_to, $_status, $_category_id);
if ($ptf->isError()) {
	exit_error(_('Error in PTF: ').$ptf->getErrorMessage(),'pm');
}

$pt_arr =& $ptf->getTasks();
if ($ptf->isError()) {
	exit_error($ptf->getErrorMessage(), 'pm');
}
$transformedTechsArr = array();
$transformedRolesArr = array();
foreach ($techs as $tech) {
	$tech_id_arr[] = $tech->getID();
	$tech_name_arr[] = $tech->getRealName();
	$transformedTechsArr[] = array('id' => $tech->getID(), 'name' => $tech->getRealName());
	$role = $tech->getRole($g);
	$transformedRolesArr[] = array('id' => $role->getID(), 'name' => $role->getName());
}
$transformedTasksArr = array();
$transformedTasksArr2 = array();
$minstartdate = 99999999999999999999;
//$maxstartdate = 0;
foreach ($pt_arr as $task) {
	$duration = ($task->getEndDate() - $task->getStartDate())/(24*60*60);
	if ($minstartdate > $task->getStartDate()) {
		$minstartdate = $task->getStartDate();
	}
// 	if ($maxstartdate < $task->getEndDate()) {
// 		$maxstartdate = $task->getEndDate();
// 	}

	$assignees = array();
	$assigneesIdArr = $task->getAssignedTo();
	foreach ($assigneesIdArr as $assigneeId) {
		$assigneeOjbject = user_get_object($assigneeId);
		$assigneeRole = $assigneeOjbject->getRole($g);
		$assignees[] = array(
					'resourceId' => $assigneeOjbject->getID(),
					'id' => $assigneeOjbject->getID(),
					'roleId' => $assigneeRole->getID(),
					'effort' => 0
				);
	}
	$transformedTasksArr[] = array(
					'id' => $task->getID(),
					'name' => $task->getSummary(),
					'code' => $task->getCategoryName(),
					'level' => 0,
					'status' => 'STATUS_ACTIVE',
					'start' => (int)$task->getStartDate()*1000,
					'duration' => (int)$duration,
					'end' => (int)$task->getEndDate()*1000,
					'startIsMilestone' => false,
					'endIsMilestone' => false,
					'assigs' => $assignees,
					'description' => $task->getDetails(),
					'progress' => (int)$task->getPercentComplete()
				);
}
for($j =0; $j <count($pt_arr); $j++) {
	$dependentTasksArr = $pt_arr[$j]->getDependentOn();
	$depends = '';
	$level = 0;
	foreach ($dependentTasksArr as $key => $dependentTask) {
		if ($key != 100) {
			for ($i =0; $i <count($transformedTasksArr); $i++) {
				if ($transformedTasksArr[$i]['id'] == $key) {
					// depends is based on the row number in the gantt editor ... which starts at 1 not 0... (0 is the th...)
					$newkey = $i+1;
					$level = $transformedTasksArr[$i]['level'] + 1;
					break;
				}
			}
			// bug here.... $key should be the array key of the task not the task id...
			$depends .= $newkey.',';
		}
	}
	$transformedTasksArr[$j]['depends'] = $depends;
	$transformedTasksArr[$j]['level'] = $level;
}

$tech_id_arr[] = '0';
$tech_name_arr[] = _('Any');

$tech_box = html_build_select_box_from_arrays($tech_id_arr, $tech_name_arr, '_assigned_to', $_assigned_to, true, _('Unassigned'), true, _('Any'));

$status_box = html_build_select_box($pg->getStatuses(), '_status', $_status, false, '', true, _('Any'));

$cat_box = html_build_select_box($pg->getCategories(), '_category_id', $_category_id, true, _('None'), true, _('Any'));

/*
	Creating a custom sort box
*/
$title_arr = array();
$title_arr[] = _('Task Id');
$title_arr[] = _('Task Summary');
$title_arr[] = _('Start Date');
$title_arr[] = _('End Date');
$title_arr[] = _('Percent Complete');

$order_col_arr = array();
$order_col_arr[] = 'project_task_id';
$order_col_arr[] = 'summary';
$order_col_arr[] = 'start_date';
$order_col_arr[] = 'end_date';
$order_col_arr[] = 'percent_complete';
$order_box = html_build_select_box_from_arrays($order_col_arr, $title_arr, '_order', $_order, false);

echo '	<form action="'. getStringFromServer('PHP_SELF') .'?group_id='.$group_id.'&amp;group_project_id='.$group_project_id.'&amp;func=ganttpage" method="post">
	<table width="10%" class="tableheading">
	<tr>
		<td>'._('Assignee').'<br />'. $tech_box .'</td>
		<td>'._('Status').'<br />'. $status_box .'</td>
		<td>'._('Category').'<br />'. $cat_box .'</td>
		<td>'._('Sort On').'<br />'. $order_box .'</td>
		<td><input type="submit" name="submit" value="'._('Browse').'" /></td>
	</tr></table></form>';
echo '<div id="workSpace" style="padding:0px; overflow-y:auto; overflow-x:hidden; border:1px solid #e5e5e5; position:relative; margin:0 5px;"></div>';
?>
<script type="text/javascript">
	var ge;
	jQuery(function() {
		ge = new GanttMaster();
		var workSpace = jQuery("#workSpace");
		workSpace.css({width:jQuery(window).width() - 20, height:jQuery(window).height() - 100});
		ge.init(workSpace);
		ge.loadProject({
			"tasks":
				<?php echo json_encode($transformedTasksArr); ?>,
			"resources":
				<?php echo json_encode($transformedTechsArr); ?>,
			"roles":
				<?php echo json_encode($transformedRolesArr); ?>,
			"canWrite":false,
			"canWriteOnParent":false,
			"selectedRow":0,
			"deletedTaskIds":[],
			"minEditableDate":<?php echo $minstartdate*1000 ?>,
		})
	});
</script>

<div id="gantEditorTemplates" style="display:none;">
	<div class="__template__" type="GANTBUTTONS"><!--
		<div class="ganttButtonBar">
			<div class="buttons" style="margin:0 0 0 0">
				<button onclick="$('#workSpace').trigger('zoomMinus.gantt');" class="button textual" title="zoom out"><span class="teamworkIcon">)</span></button>
				<button onclick="$('#workSpace').trigger('zoomPlus.gantt');" class="button textual" title="zoom in"><span class="teamworkIcon">(</span></button>
			</div>
		</div>
	--></div>

  <div class="__template__" type="TASKSEDITHEAD"><!--
  <table class="gdfTable" cellspacing="0" cellpadding="0">
    <thead>
    <tr style="height:40px">
      <th class="gdfColHeader" style="width:15px;"></th>
      <th class="gdfColHeader" style="width:25px;"></th>
      <th class="gdfColHeader gdfResizable">category</th>
      <th class="gdfColHeader gdfResizable"></th>
      <th class="gdfColHeader gdfResizable">task summary</th>
      <th class="gdfColHeader gdfResizable" style="width:80px;">start</th>
      <th class="gdfColHeader gdfResizable" style="width:80px;">end</th>
      <th class="gdfColHeader gdfResizable" style="width:25px;">dur.</th>
      <th class="gdfColHeader gdfResizable" style="width:25px;">dep.</th>
      <th class="gdfColHeader gdfResizable">assignees</th>
    </tr>
    </thead>
  </table>
  --></div>

  <div class="__template__" type="TASKROW"><!--
  <tr taskId="(#=obj.id#)" class="taskEditRow" level="(#=level#)">
    <th class="gdfCell edit" align="right" style="cursor:pointer;"><span class="taskRowIndex">(#=obj.getRow()+1#)</span></th>
    <td class="gdfCell" align="center"><div class="taskStatus cvcColorSquare" status="(#=obj.status#)"></div></td>
    <td class="gdfCell"><input type="text" name="code" value="(#=obj.code?obj.code:''#)"></td>
    <td class="gdfCell"><span>-</span></td>

    <td class="gdfCell indentCell" style="padding-left:(#=obj.level*10#)px;"><input type="text" name="name" value="(#=obj.name#)" style="(#=obj.level>0?'border-left:2px dotted orange':''#)"></td>

    <td class="gdfCell"><input type="text" name="start"  value="" class="date"></td>
    <td class="gdfCell"><input type="text" name="end" value="" class="date"></td>
    <td class="gdfCell"><input type="text" name="duration" value="(#=obj.duration#)"></td>
    <td class="gdfCell"><input type="text" name="depends" value="(#=obj.depends#)" (#=obj.hasExternalDep?"readonly":""#)></td>
    <td class="gdfCell taskAssigs">(#=obj.getAssigsString()#)</td>
  </tr>
  --></div>

  <div class="__template__" type="TASKEMPTYROW"><!--
  --></div>

  <div class="__template__" type="TASKBAR"><!--
  <div class="taskBox" taskId="(#=obj.id#)" >
    <div class="layout (#=obj.hasExternalDep?'extDep':''#)">
      <div class="taskStatus" status="(#=obj.status#)"></div>
      <div class="taskProgress" style="width:(#=obj.progress>100?100:obj.progress#)%; background-color:(#=obj.progress>100?'red':'rgb(153,255,51);'#);"></div>
      <div class="milestone (#=obj.startIsMilestone?'active':''#)" ></div>

      <div class="taskLabel"></div>
      <div class="milestone end (#=obj.endIsMilestone?'active':''#)" ></div>
    </div>
  </div>
  --></div>


  <div class="__template__" type="CHANGE_STATUS"><!--
    <div class="taskStatusBox">
      <div class="taskStatus cvcColorSquare" status="STATUS_ACTIVE" title="active"></div>
      <div class="taskStatus cvcColorSquare" status="STATUS_DONE" title="completed"></div>
      <div class="taskStatus cvcColorSquare" status="STATUS_FAILED" title="failed"></div>
      <div class="taskStatus cvcColorSquare" status="STATUS_SUSPENDED" title="suspended"></div>
      <div class="taskStatus cvcColorSquare" status="STATUS_UNDEFINED" title="undefined"></div>
    </div>
  --></div>


  <div class="__template__" type="TASK_EDITOR"><!--
  <div class="ganttTaskEditor">
  <table width="100%">
    <tr>
      <td>
        <table cellpadding="5">
          <tr>
            <td><label for="code">category</label><br><input type="text" name="code" id="code" value="" class="formElements"></td>
           </tr><tr>
            <td><label for="name">task summary</label><br><input type="text" name="name" id="name" value=""  size="35" class="formElements"></td>
          </tr>
          <tr></tr>
            <td>
              <label for="description">description</label><br>
              <textarea rows="5" cols="30" id="description" name="description" class="formElements"></textarea>
            </td>
          </tr>
        </table>
      </td>
      <td valign="top">
        <table cellpadding="5">
          <tr>
          <td colspan="2"><label for="status">status</label><br><div id="status" class="taskStatus" status=""></div></td>
          <tr>
          <td colspan="2"><label for="progress">progress</label><br><input type="text" name="progress" id="progress" value="" size="3" class="formElements"></td>
          </tr>
          <tr>
          <td><label for="start">start</label><br><input type="text" name="start" id="start"  value="" class="date" size="10" class="formElements"><input type="checkbox" id="startIsMilestone"> </td>
          <td rowspan="2" class="graph" style="padding-left:50px"><label for="duration">dur.</label><br><input type="text" name="duration" id="duration" value=""  size="5" class="formElements"></td>
        </tr><tr>
          <td><label for="end">end</label><br><input type="text" name="end" id="end" value="" class="date"  size="10" class="formElements"><input type="checkbox" id="endIsMilestone"></td>
        </table>
      </td>
    </tr>
    </table>
  </div>
  --></div>


  <div class="__template__" type="ASSIGNMENT_ROW"><!--
  --></div>

</div>
<?php
echo '<noscript>';
echo '<img src="'. getStringFromServer('PHP_SELF') .
		'?func=ganttchart&amp;group_id='.$group_id.
		'&amp;group_project_id='.$group_project_id.
		'&amp;_assigned_to='.$_assigned_to.
		'&amp;_status='.$_status.
		'&amp;_order='.$_order.
		'&amp;_resolution='.$_resolution.
		'&amp;_category_id='.$_category_id.
		'&amp;rand='.util_randnum().'" alt="'. _('Gantt Chart').'" />';
echo '</noscript>';

?>
</body>
</html>
