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

pm_header(array('title'=>_('Delete a Task'),'group_project_id'=>$group_project_id));

?>

<form action="<?php echo getStringFromServer('PHP_SELF')."?group_id=$group_id&amp;group_project_id=$group_project_id"; ?>" method="post">
<input type="hidden" name="func" value="postdeletetask" />
<input type="hidden" name="project_task_id" value="<?php echo $project_task_id; ?>" />

<table border="0" align="center">

	<tr>
		<td ><span class="veryimportant"><?php echo _('Are you sure you want to delete this task?'); ?></span>
			<h3>&quot;<?php echo $pt->getSummary(); ?>&quot;</h3></td>
	</tr>
	<tr align="center">
		<td style="text-align:center"><input type="checkbox" value="1" name="confirm_delete" /> <?php echo _('Yes, I want to delete this task'); ?></td>
	</tr>
	<tr>
		<td style="text-align:center"><input type="submit" value="<?php echo _('Submit'); ?>" name="submit" /></td>
	</tr>

</table>
</form>

<?php

pm_footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
