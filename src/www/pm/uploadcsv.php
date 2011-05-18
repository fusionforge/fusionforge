<?php
/**
 * Project Management Facility
 *
 * Copyright 2010, FusionForge Team
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


//
//	This page contains a form with a file-upload button
//	so a user can choose a file to upload a .csv file and store it in task mgr
//


pm_header(array('title'=>_('Upload data into the tasks.'),'group_project_id'=>$group_project_id));

?>
<p>
<?php echo _('This page lets you choose a file, in .csv format, and upload it so it can be inserted in the current subproject.'); ?>
</p>

<form enctype="multipart/form-data" method="post" action="<?php echo getStringFromServer('PHP_SELF')?>?group_project_id=<?php echo $group_project_id ?>&amp;group_id=<?php echo $group_id ?>&amp;func=postuploadcsv">
<?php echo _('Choose a file in the proper .csv format for uploading.'); ?><br />
<input type="file" name="userfile"  size="30" />
<input type="submit" name="submit" value="submit" />
</form>

<h2>Record Layout</h2>

<table border="1">
<tr><td><strong>Field Name</strong></td><td><strong>Description</strong></td></tr>
<tr><td>project_task_id</td><td>this is the ID in gforge database</td></tr>
<tr><td>external_task_id</td><td>optional, the equivalent of project_task_id but determined by
external application, such as MS Project. Primarily preserved for sorting purposes only.</td></tr>
<tr><td>parent_id</td><td>the project_task_id of the parent task, if any</td></tr>
<tr><td>external_parent_id</td><td>the equivalent of parent project_task_id but
        determined by external application, such as MS Project. Primarily preserved for matching purposes only.</td></tr>
<tr><td>title</td><td>The summary or brief description</td></tr>
<tr><td>duration</td><td>Duration in days</td></tr>
<tr><td>work</td><td>Number of hours required to complete</td></tr>
<tr><td>start_date</td><td>The start date in MM-DD-YYYY HH:MM:SS format</td></tr>
<tr><td>end_date</td><td>The end date in MM-DD-YYYY HH:MM:SS format</td></tr>
<tr><td>percent_complete</td><td>Percentage of completion</td></tr>
<tr><td>priority</td><td>integers 1 to 5</td></tr>
<tr><td>notes</td><td>optional, the details of the task or a comment to add to a task</td></tr>
<tr><td>resource1_unixname</td><td>optional, the unixname or precisely-matched realname of the assignee </td></tr>
<tr><td>resource2_unixname</td><td>optional, same as above</td></tr>
<tr><td>resource3_unixname</td><td>optional, same as above</td></tr>
<tr><td>resource4_unixname</td><td>optional, same as above</td></tr>
<tr><td>resource5_unixname</td><td>optional, same as above</td></tr>
<tr><td>dependenton1_project_task_id</td><td>optional, the GForge task_id of a task to be dependent on</td></tr>
<tr><td>dependenton1_external_task_id</td><td>optional, the ID used by the external application</td></tr>
<tr><td>dependenton1_linktype</td><td>SS, SF, FS, FF, - The same types as MS Project</td></tr>
<tr><td>dependenton2_project_task_id</td><td>repetition of dependenton1</td></tr>
<tr><td>dependenton2_external_task_id</td><td>repetition of dependenton1</td></tr>
<tr><td>dependenton2_linktype</td><td>repetition of dependenton1</td></tr>
<tr><td>dependenton3_project_task_id</td><td>repetition of dependenton1</td></tr>
<tr><td>dependenton3_external_task_id</td><td>repetition of dependenton1</td></tr>
<tr><td>dependenton3_linktype</td><td>repetition of dependenton1</td></tr>
<tr><td>dependenton4_project_task_id</td><td>repetition of dependenton1</td></tr>
<tr><td>dependenton4_external_task_id</td><td>repetition of dependenton1</td></tr>
<tr><td>dependenton4_linktype</td><td>repetition of dependenton1</td></tr>
<tr><td>dependenton5_project_task_id</td><td>repetition of dependenton1</td></tr>
<tr><td>dependenton5_external_task_id</td><td>repetition of dependenton1</td></tr>
<tr><td>dependenton5_linktype</td><td>repetition of dependenton1</td></tr>
</table>
<p />
<?php
pm_footer(array());
?>
