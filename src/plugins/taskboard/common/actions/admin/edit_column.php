<?php

/*
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


$column_id = getStringFromRequest('column_id','');

$column = &taskboard_column_get_object($column_id);
if (getStringFromRequest('post_changes')) {
	$resolutions  = getArrayFromRequest('resolutions', array());
	$column_title = getStringFromRequest('column_title','');
	$title_bg_color = getStringFromRequest('title_bg_color','');
	$color_bg_color = getStringFromRequest('column_bg_color','');
	$column_max_tasks = getStringFromRequest('column_max_tasks','');

	$column->update($column_title, $title_bg_color, $color_bg_color, $column_max_tasks);
	$column->setResolutions($resolutions);

	$resolution_by_default =  getStringFromRequest('resolution_by_default','');
	$alert = getStringFromRequest('alert','');
	$autoassign = getIntFromRequest('autoassign',0);

	db_begin();
	if( $column->setDropRule(NULL, $resolution_by_default, $alert, $autoassign) ) {
		db_commit();
		$feedback .= _('Succefully Updated');
	} else {
		db_rollback();
		exit_error( $column->getErrorMessage() );
	}
}



$taskboard->header(
	array(
		'title'=>'Taskboard for '.$group->getPublicName().' : Administration : Column configuration' ,
		'pagename'=>_('Column configuration'),
		'sectionvals'=>array(group_getname($group_id)),
		'group'=>$group_id
	)
);

if( $taskboard->isError() ) {
	echo '<div id="messages" class="error">'.$taskboard->getErrorMessage().'</div>';
} else {
	echo '<div id="messages" style="display: none;"></div>';
}

$drop_rules_by_default = $column->getDropRulesByDefault(true);

?>

<form action="/plugins/taskboard/admin/?group_id=<?php echo $group_id ?>&amp;action=edit_column" method="post">
<input type="hidden" name="post_changes" value="y">
<input type="hidden" name="column_id" value="<?php echo $column_id ?>">

<h2>Edit column:</h2>
<table>
	<tr><td><strong><?php echo _('Title') ?></strong>&nbsp;<?php echo utils_requiredField(); ?></td><td><input type="text" name="column_title" value="<?php echo htmlspecialchars( $column->getTitle() ) ?>"></td></tr>
	<tr><td><strong><?php echo _('Title backgound color') ?></strong></td><td><?php echo $taskboard->colorBgChooser('title_bg_color', $column->getTitleBackgroundColor() ) ?></td></tr>
	<tr><td><strong><?php echo _('Column Background color') ?></strong></td><td><?php echo $taskboard->colorBgChooser('column_bg_color', $column->getColumnBackgroundColor() ) ?></td></tr>
	<tr><td><strong><?php echo _('Maximum tasks number') ?></strong></td><td><input type="text" name="column_max_tasks" value="<?php echo $column->getMaxTasks() ?>"></td></tr>

	<tr><td colspan="2"><strong>Resolutions:</strong></td></tr>
<?php
$columns_resolutions = $column->getResolutions();
foreach( $taskboard->getAvailableResolutions() as $resolution ) {
?>
	<tr><td><?php echo htmlspecialchars( $resolution ) ?></td><td><input type="checkbox" name="resolutions[]" value="<?php echo htmlspecialchars( $resolution)  ?>" <?php echo ( in_array( $resolution, $columns_resolutions) ? 'checked' : '') ?>></td></tr>
<?php
}
?>


	<tr><td><strong><?php echo _('Drop resolution by default') ?></strong>&nbsp;<?php echo utils_requiredField(); ?></td><td><select id="resolution_by_default" name="resolution_by_default"></select></td></tr>
	<tr><td><strong><?php echo _('Autoassign') ?></strong></td><td><input type="checkbox" name="autoassign" value="1" <?php  echo $drop_rules_by_default->getAutoassign() ? 'checked' : '' ; ?>></td></tr>
	<tr><td><strong><?php echo _('Alert message') ?></strong></td><td><textarea name="alert" cols="79" rows="5" ><?php echo htmlspecialchars($drop_rules_by_default->getAlertText()) ?></textarea></td></tr>
</table>

<p>
<input type="submit" name="post_changes" value="<?php echo _('Submit') ?>" />
</p>

<?php
echo utils_requiredField().' '._('Indicates required fields.');
?>

<script>
jQuery(function($){
	function loadResolutions(select_name, default_value) {
		var selected = $('select[name=' + select_name + '] option:selected').val();
		if( !selected ) {
			selected = default_value;
		}

		var str = '';
		$('input:checked[name="resolutions[]"]').each( function(i, e) {
			str +='<option value="'+ e.value +'"'+ ( e.value == selected ? 'selected' : '' ) +'>'+ e.value +'</option>';
		});
		$('select[name=' + select_name + ']').empty().html(str);
	}


	loadResolutions('resolution_by_default', '<?php echo $column->getResolutionByDefault() ?>');

	$('input[name="resolutions[]"]').click( function () {
		loadResolutions('resolution_by_default', '<?php echo $column->getResolutionByDefault() ?>');
	});
});
</script>
