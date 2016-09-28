<?php
/**
 * Workflow Required Files Form
 *
 * Copyright 2010, FusionForge Team
 * Copyright (C) 2011 Alain Peyrat - Alcatel-Lucent
 * Copyright 2015, Franck Villaume - TrivialDev
 * Copyright 2016, Stéphane-Eymeric Bredthauer - TrivialDev
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

// based on form-workflow_roles.php

require_once 'common/tracker/ArtifactWorkflow.class.php';

global $HTML;

$from = getIntFromRequest('from');
$next = getIntFromRequest('next');

//	FORM TO UPDATE ARTIFACT TYPES

/*
	List of possible user built Selection Boxes for an ArtifactType
*/
$efarr = $ath->getExtraFields(array(ARTIFACT_EXTRAFIELDTYPE_STATUS));
if (count($efarr) === 0) {
	// TODO: Normal status is not implemented right now.
	return false;
} elseif (count($efarr) !== 1) {
	// Internal error.
	return false;
}

$keys=array_keys($efarr);
$field_id = $keys[0];

$atw = new ArtifactWorkflow($ath, $field_id);
$requiredFields = $atw->getRequiredFields($from, $next);

$elearray = $ath->getExtraFieldElements($field_id);
foreach ($elearray as $e) {
	$name[ $e['element_id'] ] = $e['element_name'];
}

$title = sprintf(_('Configuring required files for the transitions from %1$s to %2$s'), $name[$from], $name[$next]);
$ath->adminHeader(array('title'=>$title,
	'pagename'=>'tracker_admin_customize_liste',
	'titlevals'=>array($ath->getName())));

echo $HTML->openForm(array('action' => '/tracker/admin/?group_id='.$group_id.'&atid='.$ath->getID(), 'method' => 'post'));
?>
<input type="hidden" name="field_id" value="<?php echo $field_id ?>" />
<input type="hidden" name="workflow_required_fields" value="1" />
<input type="hidden" name="from" value="<?php echo $from ?>" />
<input type="hidden" name="next" value="<?php echo $next ?>" />

<?php
$extra_fields = $ath->getExtraFields() ;
//sortRoleList ($group_roles) ;
foreach ($extra_fields as $key => $row) {
	$extra_fields_names[$key] = $row['field_name'];
}
array_multisort($extra_fields_names, SORT_ASC, SORT_LOCALE_STRING | SORT_FLAG_CASE, $extra_fields);

foreach ($extra_fields as $field) {
	if ($field['field_type'] != ARTIFACT_EXTRAFIELDTYPE_STATUS) {
		$value = in_array($field['extra_field_id'], $requiredFields)? ' checked="checked"' : '';
		$str = '<input type="checkbox" name="extrafield['.$field['extra_field_id'].']"'.$value.' />';
		$str .= ' '.$field['field_name'];
		echo $str."<br />\n";
	}
}
?>
<p>
<input type="submit" name="post_changes" value="<?php echo _('Submit') ?>" /></p>
<?php
echo $HTML->closeForm();
$ath->footer();
