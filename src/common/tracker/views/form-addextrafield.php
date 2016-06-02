<?php
/**
 * Tracker Facility
 *
 * Copyright 2010 (c) FusionForge Team
 * Copyright 2014-2015, Franck Villaume - TrivialDev
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

global $HTML;

//
//  FORM TO BUILD SELECTION BOXES
//

$title = sprintf(_('Manage Custom Fields for %s'), $ath->getName());
$ath->adminHeader(array('title'=>$title));

/*
	List of possible user built Selection Boxes for an ArtifactType
*/
$efarr = $ath->getExtraFields();
$eftypes=ArtifactExtraField::getAvailableTypes();
$keys=array_keys($efarr);
$rows=count($keys);
if ($rows > 0) {

	$title_arr=array();
	$title_arr[]=_('Custom Fields Defined');
	$title_arr[]=_('Type');
	$title_arr[]=_('Elements Defined');
	$title_arr[]=_('Add Options');
	echo $HTML->listTableTop($title_arr);

	for ($k=0; $k < $rows; $k++) {
		$i=$keys[$k];
		$id=str_replace('@','',$efarr[$i]['alias']);
		echo '<tr id="field-'.$id.'" '. $HTML->boxGetAltRowStyle($i) .">\n".
			'<td>'.$efarr[$i]['field_name'].(($efarr[$i]['is_required']) ? utils_requiredField() : '').
				util_make_link('/tracker/admin/?update_box=1&id='.$efarr[$i]['extra_field_id'].'&group_id='.$group_id.'&atid='.$ath->getID(), ' ['._('Edit').']').
				util_make_link('/tracker/admin/?deleteextrafield=1&id='.$efarr[$i]['extra_field_id'].'&group_id='.$group_id.'&atid='. $ath->getID(), ' ['._('Delete').']').
				util_make_link('/tracker/admin/?copy_opt=1&id='.$efarr[$i]['extra_field_id'].'&group_id='.$group_id.'&atid='. $ath->getID(), ' ['._('Copy').']').
				"</td>\n";
		echo '<td>'.$eftypes[$efarr[$i]['field_type']]."</td>\n";
		/*
			List of possible options for a user built Selection Box
		*/
		$elearray = $ath->getExtraFieldElements($efarr[$i]['extra_field_id']);

		if (!empty($elearray)) {
			$optrows=count($elearray);

			echo '<td>';
			for ($j=0; $j <$optrows; $j++) {
				echo $elearray[$j]['element_name'];
				echo util_make_link('/tracker/admin/?update_opt=1&id='.$elearray[$j]['element_id'].'&group_id='.$group_id.'&atid='.$ath->getID().'&boxid='.$efarr[$i]['extra_field_id'], ' ['._('Edit').']');
				echo util_make_link('/tracker/admin/?delete_opt=1&id='.$elearray[$j]['element_id'].'&group_id='.$group_id.'&atid='.$ath->getID().'&boxid='.$efarr[$i]['extra_field_id'], ' ['._('Delete').']');
				echo '<br />';
			}
		} else {
			echo '<td>';
		}

		echo "</td>\n";
		echo "<td>\n";
		if ($efarr[$i]['field_type'] == ARTIFACT_EXTRAFIELDTYPE_SELECT
			|| $efarr[$i]['field_type'] == ARTIFACT_EXTRAFIELDTYPE_RADIO
			|| $efarr[$i]['field_type'] == ARTIFACT_EXTRAFIELDTYPE_CHECKBOX
			|| $efarr[$i]['field_type'] == ARTIFACT_EXTRAFIELDTYPE_MULTISELECT
			|| $efarr[$i]['field_type'] == ARTIFACT_EXTRAFIELDTYPE_STATUS) {
			echo util_make_link('/tracker/admin/?add_opt=1&boxid='.$efarr[$i]['extra_field_id'].'&group_id='.$group_id.'&atid='.$ath->getID(), '['._('Add/Reorder choices').']');
		}
		echo "</td>\n";
		echo "</tr>\n";
	}
	echo $HTML->listTableBottom();
	echo $HTML->addRequiredFieldsInfoBox();
} else {
	echo $HTML->warning_msg(_('You have not defined any custom fields'));
}

echo "<h2>"._('Add New Custom Field')."</h2>";
echo $HTML->openForm(array('action' => '/tracker/admin/?group_id='.$group_id.'&atid='.$ath->getID(), 'method' => 'post'));
echo html_ao('p');
echo html_e('input', array('type'=>'hidden', 'name'=>'add_extrafield', 'value'=>'y'));

echo html_e('strong', array(), _('Custom Field Name').utils_requiredField()._(':')).html_e('br');
echo html_e('input', array('type'=>'text', 'name'=>'name', 'value'=>'', size=>'15', 'maxlength'=>'30', 'required'=>'required'));
echo html_ac(html_ap() - 1);

echo html_ao('p');
echo html_e('strong', array(), _('Field alias')._(':')).html_e('br');
echo html_e('input', array('type'=>'text', 'name'=>'alias', 'value'=>'', size=>'15', 'maxlength'=>'30'));
echo html_ac(html_ap() - 1);

echo html_ao('p');
echo html_e('strong', array(), _('Description')._(':')).html_e('br');
echo html_e('input', array('type'=>'text', 'name'=>'description', 'value'=>'', size=>'50', 'maxlength'=>'255'));
echo html_ac(html_ap() - 1);

echo html_ao('p');
echo html_e('strong', array(), _('Type of custom field').utils_requiredField()._(':')).html_e('br');

$vals = array(ARTIFACT_EXTRAFIELDTYPE_SELECT , ARTIFACT_EXTRAFIELDTYPE_CHECKBOX, ARTIFACT_EXTRAFIELDTYPE_RADIO, ARTIFACT_EXTRAFIELDTYPE_TEXT, ARTIFACT_EXTRAFIELDTYPE_MULTISELECT, ARTIFACT_EXTRAFIELDTYPE_TEXTAREA, ARTIFACT_EXTRAFIELDTYPE_STATUS, 8, ARTIFACT_EXTRAFIELDTYPE_RELATION);
// 8 = ARTIFACT_EXTRAFIELDTYPE_ASSIGNEE
// ARTIFACT_EXTRAFIELDTYPE_INTEGER, ARTIFACT_EXTRAFIELDTYPE_FORMULA, ARTIFACT_EXTRAFIELDTYPE_DATETIME, ARTIFACT_EXTRAFIELDTYPE_USER
$texts = array( _('Select Box'), _('Check Box'), _('Radio Buttons'), _('Text Field'), _('Multi-Select Box'), _('Text Area'), _('Status'), _('Box type technician'), _('Relation between artifacts'));

// ARTIFACT_EXTRAFIELDTYPE_ASSIGNEE not used
unset($vals[7]);
unset($texts[7]);

if ($ath->usesCustomStatuses()) {
	unset($vals[ARTIFACT_EXTRAFIELDTYPE_STATUS-1]);
	unset($texts[ARTIFACT_EXTRAFIELDTYPE_STATUS-1]);
}
// re-index
$vals = array_values($vals);
$texts = array_values($texts);

echo html_build_radio_buttons_from_arrays( $vals, $texts, 'field_type', '', false, '', false ,'', array('required'=>'required') );
echo html_ac(html_ap() - 1);

echo html_ao('p');
echo _('Text Fields and Text Areas need to have Size/Maxlength and Rows/Cols defined, respectively.').html_e('br');
echo _('Text Field Size/Text Area Rows');
echo html_e('input', array('type'=>'text', 'name'=>'attribute1', 'value'=>'20', size=>'2', 'maxlength'=>'2')).html_e('br');
echo _('Text Field Maxlength/Text Area Columns');
echo html_e('input', array('type'=>'text', 'name'=>'attribute2', 'value'=>'80', size=>'2', 'maxlength'=>'2')).html_e('br');
echo _('Text Field Pattern');
echo html_e('input', array('type'=>'text', 'name'=>'pattern', 'value'=>'', size=>'80', 'maxlength'=>'255')).html_e('br');
echo _('Hide the default none value');
echo html_build_checkbox('hide100','',false).html_e('br');
echo _('Label for the none value');
echo html_e('input', array('type'=>'text', 'name'=>'show100label', 'value'=>_('none'), size=>'30')).html_e('br');
echo html_ac(html_ap() - 1);

echo html_ao('p');
echo html_build_checkbox('is_required','',false);
echo html_e('label', array('for'=>'is_required'), _('Field is mandatory'));
echo html_ac(html_ap() - 1);

echo $HTML->warning_msg(_('Warning: this add new custom field'));

echo html_ao('p');
echo html_e('input', array('type'=>'submit', 'name'=>'post_changes', 'value'=>_('Add Custom Field')));
echo html_ac(html_ap() - 1);

echo $HTML->closeForm();

echo html_e('h2', array(), _('Custom Field Rendering Template'));
echo html_ao('p');
echo util_make_link('/tracker/admin/?edittemplate=1&group_id='.$group_id.'&atid='.$ath->getID(), _('Edit template')).html_e('br');
echo util_make_link('/tracker/admin/?deletetemplate=1&group_id='.$group_id.'&atid='.$ath->getID(), _('Delete template'));
echo html_ac(html_ap() - 1);

$ath->footer();

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
