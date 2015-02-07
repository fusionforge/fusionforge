<?php
/**
 * Copyright (C) 2013 Vitaliy Pylypiv <vitaliy.pylypiv@gmail.com>
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published
 * by the Free Software Foundation; either version 2 of the License,
 * or (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

global $group_id, $group, $HTML;

session_require_perm('tracker_admin', $group_id);
$taskboard = new TaskBoardHtml($group);

$taskboard->header(
	array(
		'title'=>'Taskboard for '.$group->getPublicName().' : Administration : Columns configuration' ,
		'pagename'=>_('Columns configuration'),
		'sectionvals'=>array(group_getname($group_id)),
		'group'=>$group_id
	)
);

if(count($taskboard->getUsedTrackersIds()) == 0) {
	echo $HTML->warning_msg(_('Choose at least one tracker for using with taskboard.'));
} else {
	if($taskboard->isError()) {
		echo $HTML->error_msg($taskboard->getErrorMessage());
	} else {
		echo '<div id="messages" style="display: none;"></div>';
	}

	$columns = $taskboard->getColumns();
	$tablearr = array(_('Order'), _('Title'), _('Max number of tasks'), _('Assigned resolutions'), _('Drop resolution'));

	echo $HTML->listTableTop($tablearr, false, 'sortable_table_tracker', 'sortable_table_tracker');
	foreach($columns as $column) {
		$downLink = '';
		if($column->getOrder() < count($columns)) {
			$downLink = util_make_link('/plugins/taskboard/admin/?group_id='.$group_id.'&view=down_column&column_id='.$column->getID(), "<img alt='" ._('Down'). "' src='/images/pointer_down.png'>" );
		}

		echo '
			<tr valign="middle">
				<td>'.
					$column->getOrder().
					"&nbsp;".
					$downLink .
				'</td>
				<td>
				<div style="float: left; border: 1px solid grey; height: 30px; width: 20px; background-color: '.$column->getColumnBackgroundColor().'; margin-right: 10px;"><div style="width: 100%; height: 10px; background-color: '.$column->getTitleBackgroundColor().';"></div></div>'.
					util_make_link('/plugins/taskboard/admin/?group_id='.$group_id.'&view=edit_column&column_id='.$column->getID(),
					$column->getTitle()).'</td>
				<td>'.$column->getMaxTasks().'</td>
				<td>'.implode(', ', array_values($column->getResolutions())).'</td>
				<td>'.$column->getResolutionByDefault().'</td>
			</tr>
			';
	}
	echo $HTML->listTableBottom();

?>

<form action="<?php echo util_make_url('/plugins/taskboard/admin/?group_id='.$group_id.'&action=columns' ) ?>" method="post">
<input type="hidden" name="post_changes" value="y">

<h2>Add new column:</h2>
<table>
	<tr><td><strong><?php echo _('Title') ?></strong>&nbsp;<?php echo utils_requiredField(); ?></td><td><input type="text" name="column_title"></td></tr>
	<tr><td><strong><?php echo _('Title backgound color') ?></strong></td><td><?php echo $taskboard->colorBgChooser('title_bg_color') ?></td></tr>
	<tr><td><strong><?php echo _('Column Background color') ?></strong></td><td><?php echo $taskboard->colorBgChooser('column_bg_color', 'none') ?></td></tr>
	<tr><td><strong><?php echo _('Maximum tasks number') ?></strong></td><td><input type="text" name="column_max_tasks"></td></tr>
	<tr><td><strong><?php echo _('Drop resolution by default') ?></strong>&nbsp;<?php echo utils_requiredField(); ?></td><td><select id="resolution_by_default" name="resolution_by_default">
<?php
foreach( $taskboard->getUnusedResolutions() as $resolution ) {
	echo '<option value="'.htmlspecialchars( $resolution).'">' . htmlspecialchars( $resolution) . "</option>";
}
?>
</select></td></tr>
</table>

<p>
<input type="submit" name="post_changes" value="<?php echo _('Submit') ?>" />
</p>
</form>
<?php
	echo $HTML->addRequiredFieldsInfoBox();
}
