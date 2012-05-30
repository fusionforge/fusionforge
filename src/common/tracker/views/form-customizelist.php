<?php
/**
 * FusionForge Artifact update Form
 *
 * Copyright 2010, FusionForge Team
 * Copyright (C) 2011 Alain Peyrat - Alcatel-Lucent
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

$ath->adminHeader(array('title'=>_('Customize Browse List'),'pagename'=>'tracker_admin_customize_liste','titlevals'=>array($ath->getName())));

/*
	List of possible user built Selection Boxes for an ArtifactType
*/
$efarr = $ath->getExtraFields();

$browse_fields = explode(',',$ath->getBrowseList());

// Display regular fields.
$fields = array (
	'summary' => _('Summary'),
	'open_date' => _('Open Date'),
	'status_id' => _('State'),
	'priority'  => _('Priority'),
	'assigned_to' => _('Assigned To'),
	'submitted_by' => _('Submitted By'),
	'close_date' => _('Close Date'),
	'details' => _('Detailed description'),
	'related_tasks' => _('Related tasks'),
	'last_modified_date' => _('Last Modified Date')
);

if(count($ath->getExtraFields(array(ARTIFACT_EXTRAFIELDTYPE_STATUS))) > 0) {
	unset($fields['status_id']);
}

// Extra fields
foreach ($efarr as $f) {
	$fields[$f[0]] = $f['field_name'];
}

asort($fields);

$rows = array();
$select = '';
foreach ($fields as $f => $name) {
	$pos = array_search($f, $browse_fields);
	if ($pos !== false) {
		$rows[$pos] = '<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($pos) .'>'.'<td>'.$name.'</td>'."\n".
					'<td class="align-right">'.
					($pos + 1).' --&gt; <input type="text" name="order['.$f.']" value="" size="3" maxlength="3" />'.
					'</td>'."\n".
					'<td class="align-center">'.
					'<a href="index.php?group_id='.$group_id.'&amp;atid='.$ath->getID().'&amp;id='.$f.
					'&amp;customize_list=1&amp;post_changes=1&amp;updownorder_field=1&amp;new_pos='.(($pos == 0)? $pos + 1 : $pos).'">'.html_image('ic/btn_up.png','19','18',array('alt'=>"Up")).'</a> '.
					'<a href="index.php?group_id='.$group_id.'&amp;atid='.$ath->getID().'&amp;id='.$f.
					'&amp;customize_list=1&amp;post_changes=1&amp;updownorder_field=1&amp;new_pos='.(($pos == count($browse_fields) - 1)? $pos + 1 : $pos + 2).'">'.html_image('ic/btn_down.png','19','18',array('alt'=>"Down")).'</a>'.
					'</td>'."\n".
					'<td class="align-center">'.
					'<a href="index.php?group_id='.$group_id.'&amp;atid='.$ath->getID().'&amp;id='.$f.
					'&amp;customize_list=1&amp;post_changes=1&amp;delete_field=1">'.
					html_image('ic/trash.png','','',array('alt'=>"Delete")).'</a>'.
					'</td>'."\n".
					'</tr>'."\n";
	}
	else {
		$select .= '<option value="'.$f.'">'.$name.'</option>'."\n";
	}
}
ksort($rows);

?>
	<p>
	<?php echo _('Set order of the fields that will be displayed on the browse view of your tracker:') ?>
	</p>
	<form action="<?php echo getStringFromServer('PHP_SELF').'?group_id='.$group_id.'&amp;atid='.$ath->getID(); ?>" method="post">
	<input type="hidden" name="customize_list" value="1" />
	<input type="hidden" name="post_changes" value="1" />
<?php
$title_arr = array();
$title_arr[] = _('Fields');
$title_arr[] = _('Current / New positions');
$title_arr[] = _('Up/Down positions');
$title_arr[] = _('Delete');

echo $GLOBALS['HTML']->listTableTop ($title_arr,false, ' ');
echo implode('', $rows);
echo '<tr class="noborder">
	<td>
	</td>
	<td class="align-right">
	<input type="submit" name="field_changes_order" value="'._('Reorder').'" />
	</td>
	<td>
	</td>
      </tr>';
echo $GLOBALS['HTML']->listTableBottom();
?>
	</form>
<?php if ($select) { ?>
	<p>
	<?php echo _('Select the fields that will be displayed on the browse view of your tracker:') ?>
	</p>
	<form action="<?php echo getStringFromServer('PHP_SELF').'?group_id='.$group_id.'&amp;atid='.$ath->getID(); ?>" method="post">
		<input type="hidden" name="customize_list" value="1" />
		<input type="hidden" name="add_field" value="1" />
		<strong><?php echo _('Add New Field') ?>:</strong>
<?php
echo '<select name="field_to_add">'."\n";
echo $select;
echo '</select>'."\n";
?>
		<input type="submit" name="post_changes" value="<?php echo _('Add') ?>" />
	</form>
<?php
}

$ath->footer(array());

?>
