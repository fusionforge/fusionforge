<?php
/**
 * GForge Project Management Facility
 *
 * Copyright 2002 GForge, LLC
 * http://gforge.org/
 *
 * @version   $Id$
 */
/*

	Project/Task Manager
	By Tim Perdue, Sourceforge, 11/99
	Heavy rewrite by Tim Perdue April 2000

	Total rewrite in OO and GForge coding guidelines 12/2002 by Tim Perdue
*/

pm_header(array('title'=>$Language->getText('pm_deletetask','title'),'group_project_id'=>$group_project_id));

?>

<form action="<?php echo getStringFromServer('PHP_SELF')."?group_id=$group_id&amp;group_project_id=$group_project_id"; ?>" method="post">
<input type="hidden" name="func" value="postdeletetask" />
<input type="hidden" name="project_task_id" value="<?php echo $project_task_id; ?>" />

<table border="0" align="center">

	<tr>
		<td align="center"><h3><font color="#FF0000"><?php echo $Language->getText('pm_deletetask','are_you_sure'); ?></font></h3>
			<h3>&quot;<?php echo $pt->getSummary(); ?>&quot;</h3></td>
	</tr>
	<tr align="center">
		<td align="center"><input type="checkbox" value="1" name="confirm_delete"> <?php echo $Language->getText('pm_deletetask','confirm'); ?></td>
	</tr>
	<tr>
		<td align="center"><input type="submit" value="<?php echo $Language->getText('general','submit'); ?>" name="submit" /></td>
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
