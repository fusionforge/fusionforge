<?php
/**
 *
 * Display a calendar.
 * This file displays various sorts of calendars.
 *
 * Copyright 2002 (c) GForge Development Team
 *
 * @version   $Id$
 *
 * @todo some locales start the week with "Monday", and not "Sunday".
 * @todo display holidays.
 */

require_once('pre.php');

$group_id = getIntFromRequest('group_id');
$group_project_id = getIntFromRequest('group_project_id');
$year = getIntFromRequest('year');
$month = getIntFromRequest('month');
$day = getIntFromRequest('day');
$type = getStringFromRequest('type');

// Some sanity checks first.
if ($year && ($year < 1990 || $year > 2020)) {
	exit_error($Language->getText("calendar", "invalidyear"),
		   $Language->getText("calendar", "invalidyearexplain"));
}

if ($month && ($month < 1 || $month > 12)) {
	exit_error($Language->getText("calendar", "invalidmonth"),
		   $Language->getText("calendar", "invalidmonthexplain"));
}

if ($day && ($day < 1 || $day > 31)) {
	exit_error($Language->getText("calendar", "invalidday"),
		   $Language->getText("calendar", "invaliddayexplain"));
}

if ($year && isset($month) && isset($day)) {
	if (!checkdate($month, $day, $year)) {
		exit_error($Language->getText("calendar", "invaliddate"),
			   $Language->getText("calendar", "invaliddateexplain", "$year-$month-$day"));
	}
}

if ($type && $type != 'onemonth' && $type != 'threemonth' && $type != 'currentyear' && $type != 'comingyear') {
	exit_error($Language->getText("calendar", "invalidtype"),
		   $Language->getText("calendar", "invalidtypeexplain"));
}

// Fill in defaults
if (!$type) {
	$type = 'threemonth';
}


$today = getdate(time());

if (!$year) {
	$year = $today['year'];
}

if (!$month) {
	$month = $today['mon'];
}

if (!$day) {
	$day = $today['mday'];
}


$months = array(1 => 'january', 'february', 'march', 'april', 'may', 'june',
		'july', 'august', 'september', 'october', 'november', 'december');

if ($group_id && $group_project_id) {
	require_once('common/pm/ProjectTaskFactory.class');
	require_once('common/pm/ProjectGroup.class');

	$g =& group_get_object($group_id);
	if (!$g || !is_object($g)) {
		exit_no_group();
	} elseif ($g->isError()) {
		exit_error($Language->getText('global', 'error'), $g->getErrorMessage());
	}
	$pg = new ProjectGroup($g, $group_project_id);
	if (!$pg || !is_object($pg)) {
		exit_error($Language->getText('global', 'error'), 'BUG: Could Not Get Factory');
	} elseif ($pg->isError()) {
		exit_error($Language->getText('global', 'error'), $pg->getErrorMessage());
	}

	$ptf = new ProjectTaskFactory($pg);
	if (!$ptf || !is_object($ptf)) {
		exit_error($Language->getText('global', 'error'), 'BUG: Could Not Get ProjectTaskFactory');
	} elseif ($ptf->isError()) {
		exit_error($Language->getText('global', 'error'), $ptf->getErrorMessage());
	}
	// Violate all known laws about OOP here
	$ptf->offset=0;
	$ptf->order='start_date';
	$ptf->max_rows=50;
	$ptf->status=1;
	$ptf->assigned_to=0;
	$ptf->category=0;
	$pt_arr =& $ptf->getTasks();
	if ($ptf->isError()) {
		exit_error($Language->getText('global', 'error'), $ptf->getErrorMessage());
	}
}

$HTML->header(array('title'=>$Language->getText("calendar", "title"),'group'=>$group_id));

/**
 * Create link to a task.
 * This returns a string that is a link to a particular task.
 *
 * @author    Ryan T. Sammartino <ryants at shaw dot ca>
 * @param     $task  the task to make a link for.
 * @param     $type  either 'begin' for beginning of a task or 'end' for
 *                   end of a task.
 * @date      2002-01-04
 *
 */
function make_task_link($task, $type) {
	global $HTML, $Language, $group_id, $group_project_id;
	return '<a title="'. $Language->getText('calendar', 'task_link_title', $task->getSummary())
		. '" href="/pm/task.php?func=detailtask&amp;project_task_id=' . $task->getID()
		. '&amp;group_id=' . $group_id
		. '&amp;group_project_id=' .$group_project_id
		. '">' . ($type == 'begin' ?
			  $Language->getText('calendar', 'task_begins', $task->getID()) :
			  $Language->getText('calendar', 'task_ends', $task->getID()))
		. '</a>';
}


/**
 * Display one month.
 * This displays one month.  m may be less than 0 and greater than 12: display_month
 * uses mktime() to readjust it and the year in such cases.
 *
 * @author    Ryan T. Sammartino <ryants at shaw dot ca>
 * @param     m  month
 * @param     y  year
 * @date      2002-12-29
 *
 */
function display_month($m, $y) {
	global $months, $today, $month, $day, $year, $Language, $HTML,
		$pt_arr, $group_id, $group_project_id;
	$dow = array('sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday');

	$date = getdate(mktime(0, 0, 0, $m + 1, 0, $y));
	$days_in_month = $date['mday'];

	$date = getdate(mktime(0, 0, 0, $m, 1, $y));
	$first_dow = $date['wday'];

	$m = $date['mon'];
	$y = $date['year'];
?>
	<table align="center" cellpadding="1" cellspacing="1" border="1" width="100%">
		<tr>
			<th colspan="7"><?php echo $Language->getText("calendar", $months[$m]) . " $y"; ?></th>
		</tr>
		<tr>
<?php
	reset($dow);
	while (list ($key, $val) = each ($dow)) {
		print "\t\t\t<th width=\"14%\">" . $Language->getText("calendar", $val) . "</th>\n";
	}
?>
		</tr>
<?php
	$curr_dow = 0;
	$curr_date = 1;
	print "\t\t<tr>\n";
	while ($curr_dow != $first_dow) {
		print "\t\t\t<td></td>\n";
		$curr_dow++;
	}
	while ($curr_date <= $days_in_month) {
		while ($curr_dow < 7) {
			if ($curr_date <= $days_in_month) {
				$colour = "";
				if ($curr_date == $today['mday']
				    && $y == $today['year']
				    && $m == $today['mon']) {
					$colour = " today";
				} elseif ($curr_date == $day
					  && $y == $year
					  && $m == $month) {
					$colour = " day";
				}
				print "\t\t\t<td valign=\"top\" class=" . $colour . ">$curr_date";
				$cell_contents = '';
				$rows = count($pt_arr);
				for ($i = 0; $i < $rows; $i++) {
					$start_date = getdate($pt_arr[$i]->getStartDate());
					$end_date = getdate($pt_arr[$i]->getEndDate());
					if ($curr_date == $start_date['mday']
					    && $y == $start_date['year']
					    && $m == $start_date['mon']) {
						$cell_contents .= make_task_link($pt_arr[$i], 'begin');
					} elseif ($curr_date == $end_date['mday']
						  && $y == $end_date['year']
						  && $m == $end_date['mon']) {
						$cell_contents .= make_task_link($pt_arr[$i], 'end');
					}
				}
				if ($cell_contents == '') {
					$cell_contents = '<br /><br /><br />';
				}
				print "$cell_contents</td>\n";
			} else {
				print "\t\t\t<td></td>\n";
			}
			$curr_dow++;
			$curr_date++;
		}
		print "\t\t</tr>\n";
		if ($curr_date <= $days_in_month) {
			print "\t\t<tr>\n";
		}
		$curr_dow = 0;
	}
?>

	</table>

<?php
}

?>
	<form action="/pm/calendar.php" method="get">
	<table width="100%">
		<tr>
			<td><?php echo $Language->getText("calendar", "view"); ?><br />
				<select name="type">
<?php
	print '
				<option value="onemonth"' . ($type == 'onemonth' ? ' selected="selected"' : '') . '>'. $Language->getText("calendar", "onemonth") . '</option>';
	print '
				<option value="threemonth"' . ($type == 'threemonth' ? ' selected="selected"' : '') . '>'. $Language->getText("calendar", "threemonth") . '</option>';
	print '
				<option value="currentyear"' . ($type == 'currentyear' ? ' selected="selected"' : '') . '>' . $Language->getText("calendar", "currentyear") . '</option>';
	print '
				<option value="comingyear"' . ($type == 'comingyear' ? ' selected="selected"' : '') . '>' . $Language->getText("calendar", "comingyear") . '</option>';
?>
				</select>
			</td>
			<td><?php echo $Language->getText("calendar", "fordate"); ?><br />
				<select name="year">
<?php

	for ($i = 1990; $i < 2020; $i++) {
		print "\t\t\t\t<option value=\"$i\"" . ($year == $i ? ' selected="selected"' : '') . ">$i</option>\n";
	}
?>
				</select>
				<select name="month">
<?php
	for ($i = 1; $i <= 12; $i++) {
		print "\t\t\t\t<option value=\"$i\"" . ($month == $i ? ' selected="selected"' : '') . ">" . $Language->getText("calendar", $months[$i]) . "</option>\n";
	}
?>
				</select>
				<select name="day">
<?php
	for ($i = 1; $i <= 31; $i++) {
		print "\t\t\t\t<option value=\"$i\"" . ($day == $i ? ' selected="selected"' : '') . ">$i</option>\n";
	}
?>
				</select>
			</td>
			<td>
				<input type="submit" value="<?php echo $Language->getText("calendar", "update") ?>" />
			</td>
		</tr>
	</table>
<?php
	if (isset($group_id) && isset($group_project_id)) {
		print '
	<input type="hidden" name="group_id" value="'. $group_id .'" />
	<input type="hidden" name="group_project_id" value="'. $group_project_id .'" />';
	}
?>

	</form>
	<table width="100%">
		<tr>
			<td width="20px" class="selected"></td>
			<td><?php echo $Language->getText("calendar", "todaysdate") ?></td>
		</tr>
		<tr>
			<td width="20px"></td>
			<td><?php echo $Language->getText("calendar", "selecteddate") ?></td>
		</tr>
	</table>
<?php

if ($type == 'onemonth') {
	display_month($month, $year);
} elseif ($type == 'threemonth') {
	display_month($month - 1, $year);
	print "\t<br />\n\n";
	display_month($month, $year);
	print "\t<br />\n\n";
	display_month($month + 1, $year);
} elseif ($type == 'currentyear') {
	for ($i = 1; $i <= 12; $i++) {
		display_month($i, $year);
		print "\t<br />\n\n";
	}
} elseif ($type == 'comingyear') {
	for ($i = 0; $i < 12; $i++) {
		display_month($month + $i, $year);
		print "\t<br />\n\n";
	}
}

$HTML->footer(array());

?>
