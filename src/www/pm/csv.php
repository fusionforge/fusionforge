<?php
/**
 * Copyright (C) 2009 Alain Peyrat, Alcatel-Lucent
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

$url_set_format = '/pm/task.php?group_id='.$group_id.'&amp;group_project_id='.$group_project_id.'&amp;func=format_csv&amp;sep='.urlencode($sep).'&amp;full='.$full.'&amp;headers='.$headers;

$url_export = '/pm/task.php?group_id='.$group_id.'&amp;group_project_id='.$group_project_id.'&amp;func=downloadcsv&amp;sep='.urlencode($sep).'&amp;full='.$full.'&amp;headers='.$headers;

$format = $full ? "Full CSV" : "Normal CSV";
$format .= $headers ? ' with headers' : ' without headers';
$format .= " using '$sep' as separator.";
?>
<p><?php echo _('This page allows you to export or import all the tasks using a CSV (<a href="http://en.wikipedia.org/wiki/Comma-separated_values">Comma Separated Values</a>) File. This format can be used to view tasks using Microsoft Excel.'); ?></p>
<h2><?php echo _('Export tasks as a CSV file'); ?></h2>

<strong><?php echo _('Selected CSV Format:'); ?></strong> <?php echo $format ?> <a href="<?php echo $url_set_format ?>">(Change)</a>

<p><a href="<?php echo $url_export ?>"><?php echo _('Export CSV file'); ?></a></p>

<h2><?php echo _('Import tasks using a CSV file'); ?></h2>
<form enctype="multipart/form-data" method="post" action="<?php echo getStringFromServer('PHP_SELF')?>?group_project_id=<?php echo $group_project_id ?>&amp;group_id=<?php echo $group_id ?>&amp;func=postuploadcsv">
<p><?php echo _('Choose a file in the proper .csv format for uploading.'); ?></p>
<input type="file" name="userfile" required="required" /><br/>
<label><input type="radio" name="replace" value="1" checked /> <?php echo _('Replace all tasks by the ones present in the file'); ?></label>
<label><input type="radio" name="replace" value="0" /> <?php echo _('Add the ones from the file to the existing ones'); ?></label>
<br/><br/>
<input type="submit" name="submit" value="<?php echo _('Submit'); ?>" />
</form>

<h3><?php echo _('Notes'); ?></h3>
<ul>
<li><?php echo _('If project_task_id is empty, then a new task will be created.'); ?></li>
<li><?php echo _('If project_task_id is present, then the corresponding task will be updated.'); ?></li>
</ul>

<h2><?php echo _('Record Layout'); ?></h2>

<table class="listing full">
<tr>
    <th><?php echo _('Field Name'); ?></th>
    <th><?php echo _('Description'); ?></th>
</tr>
<tr>
    <td>project_task_id</td>
    <td><?php echo _('this is the ID in database'); ?></td>
</tr>
<tr>
    <td>external_task_id</td>
    <td><?php echo _('optional, the equivalent of project_task_id but determined by external application, such as Microsoft Project. Primarily preserved for sorting purposes only.'); ?></td>
</tr>
<tr>
    <td>parent_id</td>
    <td><?php echo _('the project_task_id of the parent task, if any'); ?></td>
</tr>
<tr>
    <td>external_parent_id</td>
    <td><?php echo _('the equivalent of parent project_task_id but determined by external application, such as Microsoft Project. Primarily preserved for matching purposes only.'); ?></td>
</tr>
<tr>
    <td>title</td>
    <td><?php echo _('The summary or brief description'); ?></td>
</tr>
<tr>
    <td>category</td>
    <td><?php echo _('The category name (must be defined, only available in full export)'); ?></td>
</tr>
<tr>
    <td>duration</td>
    <td><?php echo _('Duration in days'); ?></td>
</tr>
<tr>
    <td>work</td>
    <td><?php echo _('Number of hours required to complete'); ?></td>
</tr>
<tr>
    <td>start_date</td>
    <td><?php echo _('The start date in MM-DD-YYYY HH:MM:SS format'); ?></td>
</tr>
<tr>
    <td>end_date</td>
    <td><?php echo _('The end date in MM-DD-YYYY HH:MM:SS format'); ?></td>
</tr>
<tr>
    <td>percent_complete</td>
    <td><?php echo _('Percentage of completion'); ?></td>
</tr>
<tr>
    <td>priority</td>
    <td><?php echo _('integers 1 to 5'); ?></td>
</tr>
<tr>
    <td>notes</td>
    <td><?php echo _('optional, the details of the task or a comment to add to a task'); ?></td>
</tr>
<tr>
    <td>resource1_unixname</td>
    <td><?php echo _('optional, the unixname or precisely-matched realname of the assignee'); ?></td>
</tr>
<tr>
    <td>resource2_unixname</td>
    <td><?php echo _('optional, same as above'); ?></td>
</tr>
<tr>
    <td>resource3_unixname</td>
    <td><?php echo _('optional, same as above'); ?></td>
</tr>
<tr>
    <td>resource4_unixname</td>
    <td><?php echo _('optional, same as above'); ?></td>
</tr>
<tr>
    <td>resource5_unixname</td>
    <td><?php echo _('optional, same as above'); ?></td>
</tr>
<tr>
    <td>dependenton1_project_task_id</td>
    <td><?php echo _('optional, the task_id of a task to be dependent on'); ?></td>
</tr>
<tr>
    <td>dependenton1_external_task_id</td>
    <td><?php echo _('optional, the ID used by the external application'); ?></td>
</tr>
<tr>
    <td>dependenton1_linktype</td>
    <td><?php echo _('SS, SF, FS, FF, - The same types as Microsoft Project'); ?></td>
</tr>
<tr>
    <td>dependenton2_project_task_id</td>
    <td><?php echo _('repetition of dependenton1'); ?></td>
</tr>
<tr>
    <td>dependenton2_external_task_id</td>
    <td><?php echo _('repetition of dependenton1'); ?></td>
</tr>
<tr>
    <td>dependenton2_linktype</td>
    <td><?php echo _('repetition of dependenton1'); ?></td>
</tr>
<tr>
    <td>dependenton3_project_task_id</td>
    <td><?php echo _('repetition of dependenton1'); ?></td>
</tr>
<tr>
    <td>dependenton3_external_task_id</td>
    <td><?php echo _('repetition of dependenton1'); ?></td>
</tr>
<tr>
    <td>dependenton3_linktype</td>
    <td><?php echo _('repetition of dependenton1'); ?></td>
</tr>
<tr>
    <td>dependenton4_project_task_id</td>
    <td><?php echo _('repetition of dependenton1'); ?></td>
</tr>
<tr>
    <td>dependenton4_external_task_id</td>
    <td><?php echo _('repetition of dependenton1'); ?></td>
</tr>
<tr>
    <td>dependenton4_linktype</td>
    <td><?php echo _('repetition of dependenton1'); ?></td>
</tr>
<tr>
    <td>dependenton5_project_task_id</td>
    <td><?php echo _('repetition of dependenton1'); ?></td>
</tr>
<tr>
    <td>dependenton5_external_task_id</td>
    <td><?php echo _('repetition of dependenton1'); ?></td>
</tr>
<tr>
    <td>dependenton5_linktype</td>
    <td><?php echo _('repetition of dependenton1'); ?></td>
</tr>
</table>

<?php
pm_footer();
