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

pm_header(array('title'=>_('Task Detail'),'group_project_id'=>$group_project_id));

?>

<table class="fullwidth">

	<tr>
		<td><strong><?php echo _('Submitted by') . _(': '); ?></strong><br /><?php echo $pt->getSubmittedRealName(); ?> (<?php echo $pt->getSubmittedUnixName(); ?>)</td>

		<td>
			<strong><a href="<?php echo util_make_url("/pm/t_follow.php/" . $project_task_id); ?>">Permalink</a>:</strong><br />
			<?php echo util_make_url("/pm/t_follow.php/" . $project_task_id); ?>
		</td>
	</tr>

	<tr>
		<td>
		<strong><?php echo _('Category') ?></strong><br />
		<?php echo $pt->getCategoryName(); ?>
		</td>

		<td>
		<strong>Task Detail Information (JSON):</strong><br />
		<a href="<?php echo util_make_url("/pm/t_lookup.php?tid=" . $project_task_id); ?>">application/json</a>
		or
		<a href="<?php echo util_make_url("/pm/t_lookup.php?text=1&amp;tid=" . $project_task_id); ?>">text/plain</a>
		</td>
	</tr>

	<tr>
		<td>
		<strong><?php echo _('Percent Complete') . _(': '); ?></strong><br />
		<?php echo $pt->getPercentComplete(); ?>%
		</td>

		<td>
		<strong><?php echo _('Priority') . _(': '); ?></strong><br />
		<?php echo $pt->getPriority(); ?>
		</td>

		<td>
		<?php echo util_make_link("/export/rssAboTask.php?tid=" .
		    $project_task_id, html_image('ic/rss.png',
		    16, 16, array('border' => '0')) . " " .
		    _('Subscribe to task'));
		?>
		</td>
	</tr>

	<tr>
		<td>
		<strong><?php echo _('Start Date') . _(': '); ?></strong><br />
		<?php echo date(_('Y-m-d'), $pt->getStartDate() ); ?>
		</td>
		<td>
		<strong><?php echo _('End Date') . _(': '); ?></strong><br />
		<?php echo date(_('Y-m-d'), $pt->getEndDate() ); ?>
		</td>
	</tr>

  	<tr>
		<td colspan="2">
		<strong><?php echo _('Task Summary') . _(': '); ?></strong><br />
		<?php echo $pt->getSummary(); ?>
		</td>
	</tr>

	<tr>
		<td colspan="2">
		<strong><?php echo _('Original Comment') . _(': '); ?></strong><br />
		<?php
             $sanitizer = new TextSanitizer();
             $body = $sanitizer->SanitizeHtml($pt->getDetails());
             if (strpos($body,'<') === false) {
                 echo nl2br($pt->getDetails());
             } else {
                 echo $body;
             }
        ?>
		</td>
	</tr>

	<tr>
		<td class="top">
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

	<tr>
		<td>
		<strong><?php echo _('Hours') ?></strong><br />
		<?php echo $pt->getHours(); ?>
		</td>

		<td>
		<strong><?php echo _('Status') ?></strong><br />
		<?php
		echo $pt->getStatusName();
		?>
		</td>
	</tr>

	<tr>
		<td colspan="2">
			<?php $pt->showDependentTasks(); ?>
		</td>
	</tr>

	<tr>
		<td colspan="2">
			<?php $pt->showRelatedArtifacts(); ?>
		</td>
	</tr>

	<tr>
		<td colspan="2">
			<?php $pt->showMessages($sort_comments_chronologically, "/pm/task.php?func=detailtask&amp;project_task_id=$project_task_id&amp;group_id=$group_id&amp;group_project_id=$group_project_id"); ?>
		</td>
	</tr>
	<?php
		$hookParams['task_id'] = $project_task_id;
		$hookParams['group_id'] = $group_id;
		plugin_hook("task_extra_detail", $hookParams);
	?>
	<tr>
		<td colspan="2">
			<?php $pt->showHistory(); ?>
		</td>
	</tr>

</table>
<?php

pm_footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
