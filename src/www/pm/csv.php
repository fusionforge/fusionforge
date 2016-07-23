<?php
/**
 * Copyright (C) 2009 Alain Peyrat, Alcatel-Lucent
 * Copyright 2015, Franck Villaume - TrivialDev
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

/**
 * Standard Alcatel-Lucent disclaimer for contributing to open source
 *
 * "The provided file ("Contribution") has not been tested and/or
 * validated for release as or in products, combinations with products or
 * other commercial use. Any use of the Contribution is entirely made at
 * the user's own responsibility and the user can not rely on any features,
 * functionalities or performances Alcatel-Lucent has attributed to the
 * Contribution.
 *
 * THE CONTRIBUTION BY ALCATEL-LUCENT IS PROVIDED AS IS, WITHOUT WARRANTY
 * OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, COMPLIANCE,
 * NON-INTERFERENCE AND/OR INTERWORKING WITH THE SOFTWARE TO WHICH THE
 * CONTRIBUTION HAS BEEN MADE, TITLE AND NON-INFRINGEMENT. IN NO EVENT SHALL
 * ALCATEL-LUCENT BE LIABLE FOR ANY DAMAGES OR OTHER LIABLITY, WHETHER IN
 * CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
 * CONTRIBUTION OR THE USE OR OTHER DEALINGS IN THE CONTRIBUTION, WHETHER
 * TOGETHER WITH THE SOFTWARE TO WHICH THE CONTRIBUTION RELATES OR ON A STAND
 * ALONE BASIS."
 */

//
//	This page contains a form with a file-upload button
//	so a user can choose a file to upload a .csv file and store it in task mgr
//

global $HTML;

pm_header(array('title'=>_('Upload data into the tasks'),'group_project_id'=>$group_project_id));

$default = array('headers' => 1, 'full' => 1, 'sep' => ',');

if (session_loggedin()) {
	$u = session_get_user();
	$pref = $u->getPreference('csv');
	if ($pref) {
		$default = array_merge($default, unserialize($pref));
	}
}

$headers = getIntFromRequest('headers', $default['headers']);
$full = getIntFromRequest('full', $default['full']);
$sep = getStringFromRequest('sep', $default['sep']);

if (session_loggedin()) {
	if ( ($sep !== $default['sep']) || ($headers !== $default['headers']) ) {
		$pref = array_merge( $default, array('headers' => $headers, 'full' => $full, 'sep' => $sep));
		$u->setPreference('csv', serialize($pref));
	}
}

$format = $full ? "Full CSV" : "Normal CSV";
$format .= $headers ? ' with headers' : ' without headers';
$format .= " using '$sep' as separator.";

echo html_e('p', array(), _('This page allows you to export or import all the tasks using a CSV (<a href="http://en.wikipedia.org/wiki/Comma-separated_values">Comma Separated Values</a>) File. This format can be used to view tasks using Microsoft Excel.'));
echo html_e('h2', array(), _('Export tasks as a CSV file'));

echo html_e('strong', array(), _('Selected CSV Format')._(':')).' '.$format.' '.util_make_link('/pm/task.php?group_id='.$group_id.'&group_project_id='.$group_project_id.'&func=format_csv&sep='.$sep.'&full='.$full.'&headers='.$headers, _('(Change)'));
echo html_e('p', array(), util_make_link('/pm/task.php?group_id='.$group_id.'&group_project_id='.$group_project_id.'&func=downloadcsv&sep='.$sep.'&full='.$full.'&headers='.$headers, _('Export CSV file')));

echo html_e('h2', array(), _('Import tasks using a CSV file'));
echo $HTML->openForm(array('enctype' => 'multipart/form-data', 'method' => 'post', 'action' => getStringFromServer('PHP_SELF').'?group_project_id='.$group_project_id.'&group_id='.$group_id.'&func=postuploadcsv'));
echo html_e('p', array(), _('Choose a file in the proper .csv format for uploading.'));
echo html_e('input', array('type' => 'file', 'name' => 'userfile', 'required' => 'required')).html_e('br');
echo html_e('p', array(),
	html_e('label', array(), html_e('input', array('type' => 'radio', 'name' => 'replace', 'value' => 1, 'checked' => 'checked')). _('Replace all tasks by the ones present in the file')).
	html_e('label', array(), html_e('input', array('type' => 'radio', 'name' => 'replace', 'value' => 0)). _('Add the ones from the file to the existing ones')));
echo html_e('input', array('type' => 'submit', 'name' => 'submit', 'value' => _('Submit')));
echo $HTML->closeForm();
echo html_e('h3', array(), _('Notes'));
$elementsArray = array();
$elementsArray[] = array('content' => _('If project_task_id is empty, then a new task will be created.'));
$elementsArray[] = array('content' => _('If project_task_id is present, then the corresponding task will be updated.'));
echo $HTML->html_list($elementsArray);

echo html_e('h2', array(), _('Record Layout'));

$thArray= array(_('Field Name'), _('Description'));
echo $HTML->listTableTop($thArray);
$cells = array();
$cells[][] = 'project_task_id';
$cells[][] = _('this is the ID in database');
echo $HTML->multiTableRow(array(), $cells);
$cells = array();
$cells[][] = 'external_task_id';
$cells[][] = _('optional, the equivalent of project_task_id but determined by external application, such as Microsoft Project. Primarily preserved for sorting purposes only.');
echo $HTML->multiTableRow(array(), $cells);
$cells = array();
$cells[][] = 'parent_id';
$cells[][] = _('the project_task_id of the parent task, if any');
echo $HTML->multiTableRow(array(), $cells);
$cells = array();
$cells[][] = 'external_parent_id';
$cells[][] = _('the equivalent of parent project_task_id but determined by external application, such as Microsoft Project. Primarily preserved for matching purposes only.');
echo $HTML->multiTableRow(array(), $cells);
$cells = array();
$cells[][] = 'title';
$cells[][] = _('The summary or brief description');
echo $HTML->multiTableRow(array(), $cells);
$cells = array();
$cells[][] = 'category';
$cells[][] = _('The category name (must be defined, only available in full export)');
echo $HTML->multiTableRow(array(), $cells);
$cells = array();
$cells[][] = 'duration';
$cells[][] = _('Duration in days');
echo $HTML->multiTableRow(array(), $cells);
$cells = array();
$cells[][] = 'work';
$cells[][] = _('Number of hours required to complete');
echo $HTML->multiTableRow(array(), $cells);
$cells = array();
$cells[][] = 'start_date';
$cells[][] = _('The start date in YYYY-MM-DD HH:MM:SS format');
echo $HTML->multiTableRow(array(), $cells);
$cells = array();
$cells[][] = 'end_date';
$cells[][] = _('The end date in YYYY-MM-DD HH:MM:SS format');
echo $HTML->multiTableRow(array(), $cells);
$cells = array();
$cells[][] = 'percent_complete';
$cells[][] = _('Percentage of completion');
echo $HTML->multiTableRow(array(), $cells);
$cells = array();
$cells[][] = 'priority';
$cells[][] = _('integers 1 to 5');
echo $HTML->multiTableRow(array(), $cells);
$cells = array();
$cells[][] = 'notes';
$cells[][] = _('optional, the details of the task or a comment to add to a task');
echo $HTML->multiTableRow(array(), $cells);
$cells = array();
$cells[][] = 'resource1_unixname';
$cells[][] = _('optional, the unixname or precisely-matched realname of the assignee');
echo $HTML->multiTableRow(array(), $cells);
$cells = array();
$cells[][] = 'resource2_unixname';
$cells[][] = _('optional, same as above');
echo $HTML->multiTableRow(array(), $cells);
$cells = array();
$cells[][] = 'resource3_unixname';
$cells[][] = _('optional, same as above');
echo $HTML->multiTableRow(array(), $cells);
$cells = array();
$cells[][] = 'resource4_unixname';
$cells[][] = _('optional, same as above');
echo $HTML->multiTableRow(array(), $cells);
$cells = array();
$cells[][] = 'resource5_unixname';
$cells[][] = _('optional, same as above');
echo $HTML->multiTableRow(array(), $cells);
$cells = array();
$cells[][] = 'dependenton1_project_task_id';
$cells[][] = _('optional, the task_id of a task to be dependent on');
echo $HTML->multiTableRow(array(), $cells);
$cells = array();
$cells[][] = 'dependenton1_external_task_id';
$cells[][] = _('optional, the ID used by the external application');
echo $HTML->multiTableRow(array(), $cells);
$cells = array();
$cells[][] = 'dependenton1_linktype';
$cells[][] = _('SS, SF, FS, FF, - The same types as Microsoft Project');
echo $HTML->multiTableRow(array(), $cells);
$cells = array();
$cells[][] = 'dependenton2_project_task_id';
$cells[][] = _('repetition of dependenton1');
echo $HTML->multiTableRow(array(), $cells);
$cells = array();
$cells[][] = 'dependenton2_external_task_id';
$cells[][] = _('repetition of dependenton1');
echo $HTML->multiTableRow(array(), $cells);
$cells = array();
$cells[][] = 'dependenton2_linktype';
$cells[][] = _('repetition of dependenton1');
echo $HTML->multiTableRow(array(), $cells);
$cells = array();
$cells[][] = 'dependenton3_project_task_id';
$cells[][] = _('repetition of dependenton1');
echo $HTML->multiTableRow(array(), $cells);
$cells = array();
$cells[][] = 'dependenton3_external_task_id';
$cells[][] = _('repetition of dependenton1');
echo $HTML->multiTableRow(array(), $cells);
$cells = array();
$cells[][] = 'dependenton3_linktype';
$cells[][] = _('repetition of dependenton1');
echo $HTML->multiTableRow(array(), $cells);
$cells = array();
$cells[][] = 'dependenton4_project_task_id';
$cells[][] = _('repetition of dependenton1');
echo $HTML->multiTableRow(array(), $cells);
$cells = array();
$cells[][] = 'dependenton4_external_task_id';
$cells[][] = _('repetition of dependenton1');
echo $HTML->multiTableRow(array(), $cells);
$cells = array();
$cells[][] = 'dependenton4_linktype';
$cells[][] = _('repetition of dependenton1');
echo $HTML->multiTableRow(array(), $cells);
$cells = array();
$cells[][] = 'dependenton5_project_task_id';
$cells[][] = _('repetition of dependenton1');
echo $HTML->multiTableRow(array(), $cells);
$cells = array();
$cells[][] = 'dependenton5_external_task_id';
$cells[][] = _('repetition of dependenton1');
echo $HTML->multiTableRow(array(), $cells);
$cells = array();
$cells[][] = 'dependenton5_linktype';
$cells[][] = _('repetition of dependenton1');
echo $HTML->multiTableRow(array(), $cells);
echo $HTML->listTableBottom();
pm_footer();
