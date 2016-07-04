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
//  FORM TO UPDATE POP-UP BOXES
//
/*
	Allow modification of a artifact Selection Box
*/
$title = sprintf(_('Modify a custom field in %s'),$ath->getName());
$ath->adminHeader(array('title'=>$title));

$id = getStringFromRequest('id');
$ac = new ArtifactExtraField($ath,$id);
if (!$ac || !is_object($ac)) {
	$error_msg .= _('Unable to create ArtifactExtraField Object');
} elseif ($ac->isError()) {
	$error_msg .= $ac->getErrorMessage();
} else {
	echo html_ao('p');
	echo html_e('strong', array(), _('Type of custom field')._(': ').$ac->getTypeName());
	echo html_ac(html_ap() - 1);
	echo $HTML->openForm(array('action' => '/tracker/admin/?group_id='.$group_id.'&id='.$id.'&atid='.$ath->getID(), 'method' => 'post'));

	echo html_e('input', array('type'=>'hidden', 'name'=>'update_box', 'value'=>'y'));
	echo html_e('input', array('type'=>'hidden', 'name'=>'id', 'value'=> $ac->getID()));

	echo html_ao('p');
	echo html_e('label', array('for'=>'name'), html_e('strong', array(), _('Custom Field Name')._(':')).html_e('br'));
	echo html_e('input', array('type'=>'text', 'id'=>'name', 'name'=>'name', 'value'=>$ac->getName(), 'size'=>'15', 'maxlength'=>'30', 'required'=>'required'));
	echo html_ac(html_ap() - 1);

	echo html_ao('p');
	echo html_e('label', array('for'=>'alias'), html_e('strong', array(), _('Field alias')._(':')).html_e('br'));
	echo html_e('input', array('type'=>'text', 'id'=>'alias', 'name'=>'alias', 'value'=>$ac->getAlias(), 'size'=>'15', 'maxlength'=>'30'));
	echo html_ac(html_ap() - 1);

	echo html_ao('p');
	echo html_e('label', array('for'=>'description'), html_e('strong', array(), _('Description')._(':')).html_e('br'));
	echo html_e('input', array('type'=>'text', 'name'=>'description', 'value'=>$ac->getDescription(), 'size'=>'50', 'maxlength'=>'255'));
	echo html_ac(html_ap() - 1);

	echo html_ao('p');
	echo html_build_checkbox('is_required', false, $ac->isRequired());
	echo html_e('label', array('for'=>'is_required'), _('Field is mandatory'));
	echo html_ac(html_ap() - 1);

	$efType=$ac->getType();
	if ($efType == ARTIFACT_EXTRAFIELDTYPE_TEXTAREA) {
		echo html_ao('p');
		echo html_e('label', array('for'=>'attribute1'), _('Text Area Columns'));
		echo html_e('input', array('type'=>'text', 'id'=>'attribute1', 'name'=>'attribute1', 'value'=>$ac->getAttribute1(), 'size'=>'2', 'maxlength'=>'2'));
		echo html_ac(html_ap() - 1);

		echo html_ao('p');
		echo html_e('label', array('for'=>'attribute2'), _('Text Area Columns'));
		echo html_e('input', array('type'=>'text', 'id'=>'attribute2', 'name'=>'attribute2', 'value'=>$ac->getAttribute2(), 'size'=>'2', 'maxlength'=>'2'));
		echo html_ac(html_ap() - 1);

	} elseif ($efType == ARTIFACT_EXTRAFIELDTYPE_TEXT || $efType == ARTIFACT_EXTRAFIELDTYPE_RELATION) {
		echo html_ao('p');
		echo html_e('label', array('for'=>'attribute1'), _('Text Field Size'));
		echo html_e('input', array('type'=>'text', 'id'=>'attribute1', 'name'=>'attribute1', 'value'=>$ac->getAttribute1(), 'size'=>'2', 'maxlength'=>'2'));
		echo html_ac(html_ap() - 1);

		echo html_ao('p');
		echo html_e('label', array('for'=>'attribute2'), _('Text Field Maxlength'));
		echo html_e('input', array('type'=>'text', 'id'=>'attribute2', 'name'=>'attribute2', 'value'=>$ac->getAttribute2(), 'size'=>'2', 'maxlength'=>'2'));
		echo html_ac(html_ap() - 1);

		if ($efType == ARTIFACT_EXTRAFIELDTYPE_TEXT) {
			echo html_ao('p');
			echo html_e('label', array('for'=>'pattern'), _('Text Field Pattern'));
			echo html_e('input', array('type'=>'text', 'id'=>'pattern', 'name'=>'pattern', 'value'=>$ac->getPattern(), 'size'=>'50', 'maxlength'=>'255'));
			echo html_ac(html_ap() - 1);
		}
	} else {
		echo html_e('input', array('type'=>'hidden', 'name'=>'attribute1', 'value'=>'0'));
		echo html_e('input', array('type'=>'hidden', 'name'=>'attribute2', 'value'=>'0'));

		echo html_ao('p');
		echo html_build_checkbox('hide100', false, !$ac->getShow100());
		echo html_e('label', array('for'=>'hide100'), _('Hide the default none value'));
		echo html_ac(html_ap() - 1);

		echo html_ao('p');
		echo html_e('label', array('for'=>'show100label'), html_e('b', array(), _('Label for the none value')).html_e('br'));
		echo html_e('input', array('type'=>'text', 'name'=>'show100label', 'value'=>$ac->getShow100label(), 'size'=>'30'));
		echo html_ac(html_ap() - 1);

		if (in_array($efType, array(ARTIFACT_EXTRAFIELDTYPE_RADIO, ARTIFACT_EXTRAFIELDTYPE_CHECKBOX,ARTIFACT_EXTRAFIELDTYPE_SELECT,ARTIFACT_EXTRAFIELDTYPE_MULTISELECT))) {
			$pfarr = $ath->getExtraFields(array(ARTIFACT_EXTRAFIELDTYPE_RADIO, ARTIFACT_EXTRAFIELDTYPE_CHECKBOX,ARTIFACT_EXTRAFIELDTYPE_SELECT,ARTIFACT_EXTRAFIELDTYPE_MULTISELECT));
			$parentField = array();
			$progenyField = $ac->getProgeny();
			if (is_array($pfarr)) {
				foreach ($pfarr as $pf) {
					if ($pf['extra_field_id'] != $id && !in_array($pf['extra_field_id'], $progenyField))
					$parentField[$pf['extra_field_id']] = $pf['field_name'];
				}
			}
			asort($parentField,SORT_FLAG_CASE | SORT_STRING);
			echo html_ao('p');
			echo html_e('label', array('for'=>'parent'), html_e('strong', array(), _('Parent Field')._(':')).html_e('br'));
			echo html_build_select_box_from_arrays(array_keys($parentField), array_values($parentField), 'parent', $ac->getParent(), true, 'none').html_e('br');
			echo html_ac(html_ap() - 1);
		}
		if (in_array($efType, array(ARTIFACT_EXTRAFIELDTYPE_RADIO, ARTIFACT_EXTRAFIELDTYPE_SELECT))) {
			echo html_ao('p');
			echo html_build_checkbox('autoassign', false, $ac->isAutoAssign());
			echo html_e('label', array('for'=>'autoassign'), _('Field that triggers auto-assignment rules'));
			echo html_ac(html_ap() - 1);
		}
	}

	echo $HTML->warning_msg(_('It is not recommended that you change the custom field name because other things are dependent upon it. When you change the custom field name, all related items will be changed to the new name.'));

	echo html_ao('p');
	echo html_e('input', array('type'=>'submit', 'name'=>'post_changes', 'value'=>_('Submit')));
	echo html_ac(html_ap() - 1);
	echo $HTML->closeForm();
}

$ath->footer();

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
