<?php
/**
 * FusionForge Project Management Facility
 *
 * Copyright 1999-2000, Tim Perdue/Sourceforge
 * Copyright 2002, Tim Perdue/GForge, LLC
 * Copyright 2014, Franck Villaume - TrivialDev
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

require_once $gfwww.'include/note.php';

global $HTML;

$related_artifact_id = getIntFromRequest('related_artifact_id');
$related_artifact_summary = getStringFromRequest('related_artifact_summary');

pm_header(array('title'=>_('Add a new Task'),'group_project_id'=>$group_project_id));
echo notepad_func();

$params['name'] = 'details';
$params['body'] = '';
$params['height'] = "500";
$params['width'] = "100%";
$params['content'] = '<textarea required="required" name="details" rows="5" cols="80"></textarea>';
plugin_hook_by_reference("text_editor", $params);

?>

<form id="addtaskform" action="<?php echo util_make_uri('/pm/task.php?group_id='.$group_id.'&group_project_id='.$group_project_id) ?>" method="post">
<input type="hidden" name="func" value="postaddtask" />
<input type="hidden" name="add_artifact_id[]" value="<?php echo $related_artifact_id; ?>" />

<?php
echo $HTML->listTableTop();
$cells = array();
$cells[][] = '<strong>'._('Category')._(':').'</strong><br />'.
		$pg->categoryBox('category_id').util_make_link('/pm/admin/?group_id='.$group_id.'&add_cat=1&group_project_id='.$group_project_id,'('._('Admin').')');
$cells[][] = '<input type="submit" value="'._('Submit').'" name="submit" />';
echo $HTML->multiTableRow(array(), $cells);
$cells = array();
$cells[][] = '<strong>'._('Percent Complete')._(':').'</strong><br />'.$pg->percentCompleteBox('percent_complete', 0, false);
$cells[][] = '<strong>'._('Priority')._(':').'</strong><br />'.html_build_priority_select_box();
echo $HTML->multiTableRow(array(), $cells);
$cells = array();
$cells[] = array('<strong>'._('Task Summary').utils_requiredField()._(':').'</strong><br />'.
		'<input required="required" type="text" name="summary" size="65" maxlength="65" value="'.$related_artifact_summary.'" />',
		'colspan' => 2);
echo $HTML->multiTableRow(array(), $cells);
$cells = array();
$cells[] = array('<strong>'._('Task Details').utils_requiredField()._(':').'</strong><br />'.
		notepad_button('document.forms.addtaskform.details').'<br />'.$params['content'],
		'colspan' => 2);
echo $HTML->multiTableRow(array(), $cells);
$cells = array();
$cells[] = array('<strong>'._('Estimated Hours').utils_requiredField()._(':').'</strong><br />'.
		'<input required="required" type="number" name="hours" size="5" value="1" />',
		'colspan' => 2);
echo $HTML->multiTableRow(array(), $cells);
$cells = array();
$cells[] = array('<strong>'._('Start Date')._(':').'</strong><br />'.
		$pg->showDayBox('start_day', date('d', time()), false).
		$pg->showMonthBox ('start_month', date('m', time()), false).
		$pg->showYearBox ('start_year',date('Y', time()), false).
		$pg->showHourBox ('start_hour',date('G', time()), false).
		$pg->showMinuteBox ('start_minute', date('i', 15*(time()%15)), false).
		'<br />'._('The system will modify your start/end dates if you attempt to create a start date earlier than the end date of any tasks you depend on.').
		'<br />'.util_make_link('/pm/calendar.php?group_id='.$group_id.'&group_project_id='.$group_project_id, _('View Calendar'), array('target' => '_blank')),
		'colspan' => 2);
echo $HTML->multiTableRow(array(), $cells);
$cells = array();
$cells[] = array('<strong>'._('End Date')._(':').'</strong><br />'.
		$pg->showDayBox('end_day', date('d', time()+604800), false).
		$pg->showMonthBox ('end_month', date('m', time()+604800), false).
		$pg->showYearBox ('end_year',date('Y', time()+604800), false).
		$pg->showHourBox ('end_hour',date('G', time()+604800), false).
		$pg->showMinuteBox ('end_minute', date('i', 15*(time()+604800%15)), false),
		'colspan' => 2);
echo $HTML->multiTableRow(array(), $cells);
$cells = array();
$cells[] = array('<strong>'._('Assigned to').utils_requiredField()._(':').'</strong><br />'.
		$pt->multipleAssignedBox(),
		'class' => 'top');
$cells[] = array('<strong>'._('Dependent on task').utils_requiredField()._(':').'</strong><br />'.
		$pt->multipleDependBox().'<br />'.
		_('Dependent note'),
		'class' => 'top');
echo $HTML->multiTableRow(array(), $cells);

//TODO will add duration and parent_id choices at some point
$cells = array();
$cells[] = array('<input type="submit" value="'._('Submit').'" name="submit" />'.
		'<input type="hidden" name="duration" value="0" />'.
		'<input type="hidden" name="parent_id" value="0" />',
		'colspan' => 2);
echo $HTML->multiTableRow(array(), $cells);
echo $HTML->listTableBottom();
?>
</form>
<?php

pm_footer();

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
