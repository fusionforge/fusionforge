<?php
/**
 * FusionForge : Project Management Facility
 *
 * Copyright 1999/2000, Sourceforge.net Tim Perdue
 * Copyright 2002 GForge, LLC, Tim Perdue
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

pm_header(array('title'=>sprintf(_("Delete Task [T%s]"), $project_task_id),
                'group_project_id'=>$group_project_id));
?>

<form action="<?php echo getStringFromServer('PHP_SELF')."?group_id=$group_id&amp;group_project_id=$group_project_id"; ?>" method="post">
<input type="hidden" name="func" value="postdeletetask" />
<input type="hidden" name="project_task_id" value="<?php echo $project_task_id; ?>" />

<table class="centered">

	<tr>
		<td ><span class="important"><?php echo _('Are you sure you want to delete this task?'); ?></span>
			<p><strong><?php echo $pt->getSummary(); ?></strong></p>
		</td>
	</tr>
	<tr class="align-center">
		<td class="align-center">
			<input id="confirm_delete" type="checkbox" value="1" name="confirm_delete" />
			<label for="confirm_delete">
				<?php echo _('I am Sure'); ?>
			</label>
		</td>
	</tr>
	<tr>
		<td class="align-center">
			<input type="submit" value="<?php echo _('Submit'); ?>" name="submit" />
		</td>
	</tr>

</table>
</form>

<?php

pm_footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
