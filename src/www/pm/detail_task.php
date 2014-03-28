<?php
/**
 * Project Management Facility
 *
 * Copyright 1999/2000, Sourceforge.net Tim Perdue
 * Copyright 2002 GForge, LLC, Tim Perdue
 * Copyright 2010, FusionForge Team
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

if (getStringFromRequest('commentsort') == 'anti') {
	$sort_comments_chronologically = false;
} else {
	$sort_comments_chronologically = true;
}

global $pt;
global $HTML;

pm_header(array('title' => _('Task Detail'), 'group_project_id' => $group_project_id));

echo $HTML->listTableTop();
$cells = array();
$cells[][] = '<strong>'._('Submitted by')._(':').'</strong><br />'.$pt->getSubmittedRealName().'('.$pt->getSubmittedUnixName().')';
$cells[][] = '<strong>'.util_make_link('/pm/t_follow.php/'.$project_task_id, 'Permalink'.(':')).'</strong><br />'.util_make_url('/pm/t_follow.php/'.$project_task_id);
$cells[][] = '&nbsp;';
echo $HTML->multiTableRow(array(), $cells);
$cells = array();
$cells[][] = '<strong>'._('Category')._(': ').'</strong><br />'.$pt->getCategoryName();
$cells[][] = '<strong>'._('Task Detail Information (JSON)').(':').'</strong><br />'.
		util_make_link('/pm/t_lookup.php?tid='.$project_task_id, 'application/json').
		_(' or ').
		util_make_link('/pm/t_lookup.php?text=1&amp;tid='.$project_task_id, 'text/plain');
$cells[][] = '&nbsp;';
echo $HTML->multiTableRow(array(), $cells);
$cells = array();
$cells[][] = '<strong>'._('Percent Complete')._(':').'</strong><br />'.$pt->getPercentComplete().'%';
$cells[][] = '<strong>'._('Priority')._(':').'</strong><br />'.$pt->getPriority();
$cells[][] = util_make_link('/export/rssAboTask.php?tid='.$project_task_id, html_image('ic/rss.png',16, 16, array('border' => '0')).' '._('Subscribe to task'));
echo $HTML->multiTableRow(array(), $cells);
$cells = array();
$cells[][] = '<strong>'._('Start Date')._(':').'</strong><br />'.date(_('Y-m-d'), $pt->getStartDate());
$cells[][] = '<strong>'._('End Date')._(':').'</strong><br />'.date(_('Y-m-d'), $pt->getEndDate());
$cells[][] = '&nbsp;';
echo $HTML->multiTableRow(array(), $cells);
$cells = array();
$cells[] = array('<strong>'._('Task Summary')._(':').'</strong><br />'.$pt->getSummary(), 'colspan' => 3);
echo $HTML->multiTableRow(array(), $cells);
$cells = array();
$content = '<strong>'._('Original Comment')._(':').'</strong><br />';
$sanitizer = new TextSanitizer();
$body = $sanitizer->SanitizeHtml($pt->getDetails());
if (strpos($body,'<') === false) {
	$content .= nl2br($pt->getDetails());
} else {
	$content .= $body;
}
$cells[] = array($content, 'colspan' => 3);
echo $HTML->multiTableRow(array(), $cells);
?>
<tr>
	<td class="top" colspan="2">
	<?php
	/*
		Get the list of ids this is assigned to and convert to array
		to pass into multiple select box
	*/

	$result2=db_query_params ('SELECT users.user_name AS User_Name FROM users,project_assigned_to
		WHERE users.user_id=project_assigned_to.assigned_to_id AND project_task_id=$1',
		array($project_task_id));
	ShowResultSet($result2,_('Assigned to'), false, false);
	?>
	</td>
	<td class="top">
	<?php
	/*
		Get the list of ids this is dependent on and convert to array
		to pass into multiple select box
	*/
	$result2=db_query_params ('SELECT project_task.summary FROM project_dependencies,project_task
		WHERE is_dependent_on_task_id=project_task.project_task_id
		AND project_dependencies.project_task_id=$1',
		array($project_task_id));
	ShowResultSet($result2,_('Dependent on task'), false, false);
	?>
	</td>
</tr>
<?php
$cells = array();
$cells[][] = '<strong>'._('Hours').'</strong><br />'.$pt->getHours();
$cells[][] = '<strong>'._('Status').'</strong><br />'.$pt->getStatusName();
$cells[][] = '&nbsp;';
echo $HTML->multiTableRow(array(), $cells);
$cells = array();
$cells[] = array($pt->showDependentTasks(), 'colspan' => 3);
echo $HTML->multiTableRow(array(), $cells);
$cells = array();
$cells[] = array($pt->showRelatedArtifacts(), 'colspan' => 3);
echo $HTML->multiTableRow(array(), $cells);
$cells = array();
$cells[] = array($pt->showMessages($sort_comments_chronologically, "/pm/task.php?func=detailtask&amp;project_task_id=$project_task_id&amp;group_id=$group_id&amp;group_project_id=$group_project_id"), 'colspan' => 3);
echo $HTML->multiTableRow(array(), $cells);
$hookParams['task_id'] = $project_task_id;
$hookParams['group_id'] = $group_id;
plugin_hook('task_extra_detail', $hookParams);
$cells = array();
$cells[] = array($pt->showHistory(), 'colspan' => 3);
echo $HTML->multiTableRow(array(), $cells);
echo $HTML->listTableBottom();
pm_footer();

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
