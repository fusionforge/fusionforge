<?php
/**
 * Copyright (C) 2013 Vitaliy Pylypiv <vitaliy.pylypiv@gmail.com>
 * Copyright 2015, Franck Villaume - TrivialDev
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

global $group_id, $group, $pluginTaskboard, $taskboard, $HTML;

$column_id = getStringFromRequest('column_id', '');
if ($column_id) {
	$column = &taskboard_column_get_object($column_id);
	if ($column && $column->Taskboard->Group->getID() == $group_id) {

		$taskboard->header(
			array(
				'title' => _('Taskboard for ').$group->getPublicName()._(': ')._('Administration')._(': ')._('Column configuration'),
				'pagename' => _('Column configuration'),
				'sectionvals' => array($group->getPublicName()),
				'group' => $group_id
			)
		);

		if($taskboard->isError()) {
			echo $HTML->error_msg($taskboard->getErrorMessage());
		} else {
			echo html_e('div', array('id' => 'messages', 'style' => 'display: none;'));
		}

		$drop_rules_by_default = $column->getDropRulesByDefault(true);

		echo $HTML->openForm(array('action' => '/plugins/'.$pluginTaskboard->name.'/admin/?group_id='.$group_id.'&action=edit_column', 'method' => 'post'));
		echo html_e('input', array('type' => 'hidden', 'name' => 'post_changes', 'value' => 'y'));
		echo html_e('input', array('type' => 'hidden', 'name' => 'column_id', 'value' => $column_id));
		echo html_e('h2', _('Edit column')._(':'));
		echo $HTML->listTableTop();
		?>

	<tr><td><strong><?php echo _('Title') ?></strong>&nbsp;<?php echo utils_requiredField(); ?></td><td><input type="text" name="column_title" value="<?php echo htmlspecialchars( $column->getTitle() ) ?>"></td></tr>
	<tr><td><strong><?php echo _('Title backgound color') ?></strong></td><td><?php echo $taskboard->colorBgChooser('title_bg_color', $column->getTitleBackgroundColor() ) ?></td></tr>
	<tr><td><strong><?php echo _('Column Background color') ?></strong></td><td><?php echo $taskboard->colorBgChooser('column_bg_color', $column->getColumnBackgroundColor() ) ?></td></tr>
	<tr><td><strong><?php echo _('Maximum tasks number') ?></strong></td><td><input type="text" name="column_max_tasks" value="<?php echo $column->getMaxTasks() ?>"></td></tr>

	<tr><td colspan="2"><strong><?php echo _('Resolutions')._(':') ?></strong></td></tr>
<?php
// unused by any columns resolutions
foreach( $taskboard->getUnusedResolutions() as $resolution ) {
?>
	<tr><td><?php echo htmlspecialchars( $resolution ) ?></td><td><input type="checkbox" name="resolutions[]" value="<?php echo htmlspecialchars( $resolution)  ?>" ></td></tr>
<?php
}
// used by current column resolutions
foreach( $column->getResolutions() as $resolution ) {
	?>
	<tr><td><?php echo htmlspecialchars( $resolution ) ?></td><td><input type="checkbox" name="resolutions[]" value="<?php echo htmlspecialchars( $resolution)  ?>" checked></td></tr>
<?php
}

?>
	<tr><td><strong><?php echo _('Drop resolution by default') ?></strong>&nbsp;<?php echo utils_requiredField(); ?></td><td><select id="resolution_by_default" name="resolution_by_default"></select></td></tr>
	<tr><td><strong><?php echo _('Autoassign') ?></strong></td><td><input type="checkbox" name="autoassign" value="1" <?php  echo $drop_rules_by_default->getAutoassign() ? 'checked' : '' ; ?>></td></tr>
	<tr><td><strong><?php echo _('Alert message') ?></strong></td><td><textarea name="alert" cols="79" rows="5" ><?php echo htmlspecialchars($drop_rules_by_default->getAlertText()) ?></textarea></td></tr>
<?php
		echo $HTML->listTableBottom();
		echo html_e('p', array(), html_e('input', array('type' => 'submit', 'name' => 'post_changes', 'value' => _('Submit'))));
		echo utils_requiredField().' '._('Indicates required fields.');
		echo html_ao('script', array('type' => 'text/javascript'));
?>
//<![CDATA[
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
//]]>
<?php
		echo html_ac(html_ap() - 1);
	} else {
		$error_msg = _('Cannot edit column due to unknown column ID');
		session_redirect('/plugins/'.$pluginTaskboard->name.'/admin/?view=columns&group_id='.$group_id);
	}
} else {
	$warning_msg = _('Cannot edit column due to missing column ID');
	session_redirect('/plugins/'.$pluginTaskboard->name.'/admin/?view=columns&group_id='.$group_id);
}
