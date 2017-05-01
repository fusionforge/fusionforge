<?php
/**
 * Project Admin page to edit units
 *
 * Copyright 2017, Stéphane-Eymeric Bredthauer - TrivialDev
 * http://fusionforge.org/
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

require_once $gfcommon.'include/pre.php';
require_once $gfcommon.'tracker/EffortUnit.class.php';
require_once $gfcommon.'tracker/EffortUnitSet.class.php';
require_once $gfcommon.'tracker/EffortUnitFactory.class.php';
require_once $gfcommon.'tracker/include/ArtifactTypeHtml.class.php';
require_once $gfcommon.'tracker/include/ArtifactTypeFactoryHtml.class.php';


global $HTML, $error_msg, $feedback;

$group_id = getIntFromRequest('group_id',false);
$atid= getIntFromRequest('atid',false);

$object = null;
$group = false;
$ath = false;
$unitSetId = false;

if (!$atid && !$group_id) {
	// Forge level
	session_require_global_perm('forge_admin');
	$title = _('Effort Units for the Forge');
	$headerArr = array('title'=>$title);
	site_admin_header($headerArr);
	// hardcode
	$unitSetId = 1;
	$object = null;

} elseif (!$atid && $group_id) {
	// Projet level
	$group = group_get_object($group_id);
	if (!$group || !is_object($group)) {
		exit_error(_('Error creating group'), 'admin');
	} elseif ($group->isError()) {
		exit_error($group->getErrorMessage(), 'admin');
	}
	$perm = $group->getPermission();
	if (!$perm || !is_object($perm) || !$perm->isArtifactAdmin()) {
		exit_permission_denied();
	}


	$title = _('Effort Units for project').' '.$group->getPublicName();
	$headerArr = array('title'=>$title, 'group'=>$group->getID());

	project_admin_header($headerArr);
	$object = $group;
	$unitSetId = $group->getEffortUnitSet();

} elseif ($atid && $group_id) {
	// Tracker level
	session_require_perm('tracker_admin', $group_id);
	$group = group_get_object($group_id);
	if (!$group || !is_object($group)) {
		exit_error(_('Error creating group'), 'tracker');
	} elseif ($group->isError()) {
		exit_error($group->getErrorMessage(), 'tracker');
	}
	$ath = new ArtifactTypeHtml($group,$atid);
	if (!$ath || !is_object($ath)) {
		exit_error(_('ArtifactType could not be created'),'tracker');
	}
	if ($ath->isError()) {
		exit_error($ath->getErrorMessage(),'tracker');
	}

	$title = _('Effort Units for tracker').' '.$ath->getName();
	$headerArr = array('title'=>$title,	'modal'=>1);
	$ath->adminHeader($headerArr);
	$unitSetId = $ath->getEffortUnitSet();
	$object = $ath;

}
$effortUnitSet = new EffortUnitSet($object, $unitSetId);

switch (getStringFromRequest('function','')) {
	case 'add':
		add_unit($effortUnitSet);
		break;
	case 'postadd':
		postadd_unit($effortUnitSet);
		show_units($effortUnitSet);
		break;
	case 'postdelete':
		postdelete_unit($effortUnitSet);
		show_units($effortUnitSet);
		break;
	case 'delete':
		delete_unit($effortUnitSet);
		break;
	case 'edit':
		edit_unit($effortUnitSet);
		break;
	case 'postedit':
		postedit_unit($effortUnitSet);
		show_units($effortUnitSet);
		break;
	case 'updownorder':
		order_unit($effortUnitSet);
		show_units($effortUnitSet);
		break;
	case 'copy_set':
		copy_set($effortUnitSet);
		break;
	case 'postcopy_set':
		postcopy_set($effortUnitSet);
		show_units($effortUnitSet);
		break;
	case 'update_set':
		update_set($effortUnitSet);
		show_units($effortUnitSet);
		break;
	default:
		show_units($effortUnitSet);
}

if (!$atid && !$group_id) {
	// FusionForge level
	site_admin_footer();
} elseif (!$atid && $group_id) {
	// Projet level
	project_admin_footer();
} elseif ($atid && $group_id) {
	$ath->footer();
	// Tracker level
}

function show_units(&$effortUnitSet){
	global $HTML;

	$currentURL = getStringFromServer('PHP_SELF');
	$urlParameters = urlParameters($effortUnitSet);
	$inputParameters = inputParameters($effortUnitSet);

	switch ($effortUnitSet->getObjectLevel()) {
		case EFFORTUNITSET_FORGE_LEVEL:
			$AvailableEffortUnitSets = getAvailableEffortUnitSets();
			$isEditable = true;
			break;
		case EFFORTUNITSET_PROJECT_LEVEL:
			echo html_e('h2', array(), _('Effort Unit Set'));
			echo html_ao('p');
			echo sprintf(_('The Project “%s” is using'),$effortUnitSet->getGroup()->getPublicName()).' ';
			$AvailableEffortUnitSets = getAvailableEffortUnitSets($effortUnitSet->getGroup());
			switch ($effortUnitSet->getLevel()) {
				case EFFORTUNITSET_FORGE_LEVEL:
					$currentSetName = _('Forge level Effort Unit Set');
					echo $currentSetName.' '.util_make_link('/admin/effortunitsedit.php','('._('Admin').')',true);
					$isEditable = false;
					break;
				case EFFORTUNITSET_PROJECT_LEVEL:
					echo sprintf(_('Project “%s” level Effort Unit Set'),$effortUnitSet->getGroup()->getPublicName());
						$isEditable = true;
					break;
			}
			echo html_ac(html_ap() - 1);
			break;
		case EFFORTUNITSET_TRACKER_LEVEL:
			echo html_e('h2', array(), _('Effort Unit Set'));
			echo html_ao('p');
			echo sprintf(_('The Tracker “%s” is using'),$effortUnitSet->getArtifactType()->getName()).' ';
			$AvailableEffortUnitSets = getAvailableEffortUnitSets($effortUnitSet->getArtifactType());
			switch ($effortUnitSet->getLevel()) {
				case EFFORTUNITSET_FORGE_LEVEL:
					$currentSetName = _('Forge level Effort Unit Set');
					echo $currentSetName.' '.util_make_link('/admin/effortunitsedit.php','('._('Admin').')',true);
					$isEditable = false;
					break;
				case EFFORTUNITSET_PROJECT_LEVEL:
					$currentSetName = sprintf(_('Project “%s” level Effort Unit Set'),$effortUnitSet->getGroup()->getPublicName());
					echo $currentSetName.' '.util_make_link('/project/admin/effortunits.php?group_id='.$effortUnitSet->getGroup()->getID(),'('._('Admin').')',true);
					$isEditable = false;
					break;
				case EFFORTUNITSET_TRACKER_LEVEL:
					echo sprintf(_('Tracker “%s” level Effort Unit Set'),$effortUnitSet->getArtifactType()->getName());
					$isEditable = true;
					break;
			}
			echo html_ac(html_ap() - 1);
			break;
	}

	// TODO: chose an other set

	//copy current set to the current level
	if (!$isEditable) {
		echo $HTML->openForm(array('action' => $currentURL, 'method' => 'get'));
		echo $inputParameters;
		echo html_e('input', array('type'=>'hidden', 'name'=>'function', 'value'=>'copy_set'));
		echo html_ao('p');
		echo sprintf(_('Copy the %s'), $currentSetName)._(':');
		echo html_ac(html_ap() - 1);
		echo html_ao('p');
		echo html_e('input',array('type'=>'submit', 'value'=>_('Copy')));
		echo html_ac(html_ap() - 1);
		echo $HTML->closeForm();
	} else {
		echo $HTML->openForm(array('action' => $currentURL, 'method' => 'get'));
		echo $inputParameters;
		echo html_ao('p');
		if ($effortUnitSet->isAutoconvert()) {
			echo html_e('input', array('id'=>'is_autoconvert', 'type'=>'checkbox', 'name'=>'is_autoconvert', 'checked'=>'checked'));
		} else {
			echo html_e('input', array('id'=>'is_autoconvert', 'type'=>'checkbox', 'name'=>'is_autoconvert'));
		}
		echo html_e('label', array('for'=>'is_autoconvert'),_('Enable auto convert effort value'));
		echo html_ac(html_ap() - 1);
		echo html_ao('p');
		echo html_e('input', array('type'=>'hidden', 'name'=>'function', 'value'=>'update_set'));
		echo html_e('input', array('type'=>'submit', 'value'=>_('Update')));
		echo html_ac(html_ap() - 1);
		echo $HTML->closeForm();
	}

	echo html_e('h2', array(), _('Effort Units list'));
	$unitFactory = new EffortUnitFactory($effortUnitSet);
	if (!$unitFactory || !is_object($unitFactory)) {
		echo $HTML->error_msg(_('Error creating EffortUnitFactory'));
		return;
	} elseif ($unitFactory->isError()) {
		echo $HTML->error_msg($unitFactory->getErrorMessage());
		return;
	}
	$units = $unitFactory->getUnits();
	if ($unitFactory->isError()) {
		echo $HTML->error_msg($unitFactory->getErrorMessage());
		return false;
	}

	if (is_array($units) && count($units)>0) {
		if ($isEditable) {
			$titleArray = array(_('Up/Down positions'), _('Unit name'), _('Definition'));
		} else {
			$titleArray = array(_('Unit name'), _('Definition'));
		}
		$linksArray = array();
		$class = ' ';
		$id = 'unit_id';
		$thClassArray = array();
		$thTitleArray = array();
		$thOtherAttrsArray = array();
		echo $HTML->listTableTop($titleArray, $linksArray, $class, $id, $thClassArray, $thTitleArray, $thOtherAttrsArray);
		foreach($units as $unit) {
			$cells = array();
			if ($isEditable) {
				$pos =  $unit->getPosition();
				if ($pos==1) {
					$content = html_image('ic/btn_up.png', 19, 18, array('alt'=>'Up'));
				} else {
					$content = util_make_link($currentURL.'?'.($urlParameters ? $urlParameters.'&':'').'unit_id='.$unit->getID().'&new_pos='.($pos - 1).'&function=updownorder', html_image('ic/btn_up.png', 19, 18, array('alt'=>'Up', 'title'=>_('Move Up this custom field element'))));
				}
				if ($pos == count($units)) {
					$content .= html_image('ic/btn_down.png', 19, 18, array('alt'=>'Down'));
				} else {
					$content .= util_make_link($currentURL.'?'.($urlParameters ? $urlParameters.'&':'').'unit_id='.$unit->getID().'&new_pos='.($pos + 1).'&function=updownorder', html_image('ic/btn_down.png', 19, 18, array('alt'=>'Down', 'title'=>_('Move Down this custom field element'))));
				}
				$cells[] = array($content, 'class'=>'align-center');
			}
			$content = $unit->getName();
			if ($isEditable) {
				$content .= util_make_link($currentURL.'?'.($urlParameters ? $urlParameters.'&':'').'unit_id='.$unit->getID().'&function=edit', $HTML->getEditFilePic(_('Edit')));
				if (!$unit->isBaseUnit()) {
					$content .= util_make_link($currentURL.'?'.($urlParameters ? $urlParameters.'&':'').'unit_id='.$unit->getID().'&function=delete', $HTML->getDeletePic(_('Delete')));
				}
			}
			$cells[][] = $content;
			$cells[][] = ($unit->isBaseUnit()?_('Base Unit'):$unit->getConversionFactor().' x '.$unit->getToUnitName());
			echo $HTML->multiTableRow(array(), $cells);
		}
		echo $HTML->listTableBottom();
	} else {
		echo html_e('p',array(),_('No unit'));
	}
	if ($isEditable) {
		echo html_e('br');
		echo $HTML->openForm(array('action' => $currentURL, 'method' => 'get'));
		if (is_array($units) && count($units)==0) {
			echo html_e('input', array('type'=>'hidden', 'name'=>'is_base', 'value'=>1));
		}
		echo $inputParameters;
		echo html_e('input', array('type'=>'hidden', 'name'=>'function', 'value'=>'add'));
		echo html_e('input',array('type'=>'submit', 'value'=>_('Add new unit')));
		echo $HTML->closeForm();
	}
}

function add_unit(&$effortUnitSet){
	global $HTML;
	$currentURL = getStringFromServer('PHP_SELF');
	$urlParameters = urlParameters($effortUnitSet);
	$inputParameters = inputParameters($effortUnitSet);
	echo html_e('h2', array(), _('Add Effort Unit'));
	echo $HTML->openForm(array_merge(array('action' => $currentURL, 'method' => 'post')));
	echo html_e('input', array('type'=>'hidden', 'name'=>'form_key', 'value'=>form_generate_key()));
	echo $inputParameters;
	echo html_e('input', array('type'=>'hidden', 'name'=>'function', 'value'=>'postadd'));
	echo html_ao('p');
	echo html_e('label', array('for'=>'name'),_('Name').utils_requiredField()._(': '));
	echo html_e('input', array('type'=>'text', 'name'=>'name', 'id'=>'name', 'required'=>'required'));
	echo html_ac(html_ap() - 1);
	echo html_ao('p');
	echo html_e('label', array('for'=>'factor'),_('Definition').utils_requiredField()._(': '));
	echo html_e('input', array('type'=>'number', 'name'=>'factor', 'id'=>'factor', 'min'=>1, 'required'=>'required'));
	$unitFactory = new EffortUnitFactory($effortUnitSet);
	$unitsArr = $unitFactory->getUnitsArr();
	echo html_build_select_box_from_array($unitsArr, 'to_unit');
	echo html_ac(html_ap() - 1);
	echo html_ao('p');
	echo html_e('input', array('type' => 'submit', 'value' => _('Add')));
	echo html_e('input', array('type' => 'button', 'value' => _('Cancel'), 'onclick' => 'window.location=\''.util_make_uri($currentURL.($urlParameters ? '?'.$urlParameters:'')).'\''));
	echo html_ac(html_ap() - 1);
	echo $HTML->closeForm();
}

function postadd_unit(&$effortUnitSet){
	global $HTML;
	$unit = new EffortUnit($effortUnitSet);
	if (!$unit || !is_object($unit)) {
		echo $HTML->error_msg(_('Effort Unit could not be created'));
		return false;
	}
	if ($unit->isError()) {
		echo $HTML->error_msg($unit->getErrorMessage());
		return false;
	}
	$name = getStringFromRequest('name', '');
	$factor = getIntFromRequest('factor', 1);
	$to_unit = getIntFromRequest('to_unit');
	$position = 0;
	$unit->create($name, $factor, $to_unit, $position);
	if ($unit->isError()) {
		echo $HTML->error_msg($unit->getErrorMessage());
		return false;
	}
	echo $HTML->feedback(sprintf(_('%s successfully created.'), $name));
	return true;
}

function edit_unit (&$effortUnitSet) {
	global $HTML;
	$currentURL = getStringFromServer('PHP_SELF');
	$urlParameters = urlParameters($effortUnitSet);
	$inputParameters = inputParameters($effortUnitSet);
	$unitId = getIntFromRequest('unit_id',0);
	$unit = new EffortUnit($effortUnitSet,$unitId);
	if (!$unit || !is_object($unit)) {
		echo $HTML->error_msg(_('Effort Unit could not be created'));
		return false;
	}
	if ($unit->isError()) {
		echo $HTML->error_msg($unit->getErrorMessage());
		return false;
	}
	echo html_e('h2', array(), _('Edit Effort Unit'));
	echo $HTML->openForm(array('action' => $currentURL, 'method' => 'post'));
	echo html_e('input', array('type'=>'hidden', 'name'=>'function', 'value'=>'postedit'));
	echo html_e('input', array('type'=>'hidden', 'name'=>'unit_id', 'value'=>$unitId));
	echo html_e('input', array('type'=>'hidden', 'name'=>'form_key', 'value'=>form_generate_key()));
	echo $inputParameters;
	echo html_ao('p');
	echo html_e('label', array('for'=>'name'),_('Name').utils_requiredField()._(': '));
	echo html_e('input', array('type'=>'text', 'name'=>'name', 'id'=>'name', 'value'=> $unit->getName(), 'required'=>'required'));
	echo html_ac(html_ap() - 1);
	if ($unit->isBaseUnit()) {
		echo html_e('input', array('type'=>'hidden', 'name'=>'factor', 'value'=>1 ));
		echo html_e('input', array('type'=>'hidden', 'name'=>'to_unit', 'value'=>$unit->getToUnit() ));
	} else {
		echo html_ao('p');
		echo html_e('label', array('for'=>'factor'),_('Definition').utils_requiredField()._(': '));
		echo html_e('input', array('type'=>'number', 'name'=>'factor', 'id'=>'factor', 'min'=>1, 'value'=> $unit->getConversionFactor()));
		$unitFactory = new EffortUnitFactory($effortUnitSet);
		$unitsArr = $unitFactory->getUnitsArr();
		unset($unitsArr[$unit->getID()]);
		echo html_build_select_box_from_array($unitsArr, 'to_unit', $unit->getToUnit());
		echo html_ac(html_ap() - 1);
	}
	echo html_ao('p');
	echo html_e('input', array('type' => 'submit', 'value' => _('Update')));
	echo html_e('input', array('type' => 'button', 'value' => _('Cancel'), 'onclick' => 'window.location=\''.util_make_uri($currentURL.($urlParameters ? '?'.$urlParameters:'')).'\''));
	echo html_ac(html_ap() - 1);
	echo $HTML->closeForm();
}

function postedit_unit(&$effortUnitSet){
	global $HTML;
	$unitId = getIntFromRequest('unit_id',0);
	$unit = new EffortUnit($effortUnitSet,$unitId);
	if (!$unit || !is_object($unit)) {
		echo $HTML->error_msg(_('Effort Unit could not be created'));
		return false;
	}
	if ($unit->isError()) {
		echo $HTML->error_msg($unit->getErrorMessage());
		return false;
	}
	$name = getStringFromRequest('name', '');
	$factor = getIntFromRequest('factor', 1);
	$to_unit = getIntFromRequest('to_unit');
	$unit->update($name, $factor, $to_unit);
	if ($unit->isError()) {
		echo $HTML->error_msg($unit->getErrorMessage());
		return false;
	}
	echo $HTML->feedback(sprintf(_('%s successfully updated.'), $name));
	return true;
}

function delete_unit($effortUnitSet){
	global $HTML;
	$currentURL = getStringFromServer('PHP_SELF');
	$urlParameters = urlParameters($effortUnitSet);
	$inputParameters = inputParameters($effortUnitSet);
	$unitId = getIntFromRequest('unit_id',0);
	echo html_e('h2', array(), _('Effort Unit delete confirmation'));
	echo $HTML->openForm(array('action' => $currentURL, 'method' => 'post'));
	echo html_e('input', array('type'=>'hidden', 'name'=>'function', 'value'=>'postdelete'));
	echo html_e('input', array('type'=>'hidden', 'name'=>'unit_id', 'value'=>$unitId));
	echo html_e('input', array('type'=>'hidden', 'name'=>'form_key', 'value'=>form_generate_key()));
	echo $inputParameters;
	echo html_ao('p',array('class'=>'important'));
	echo _('Are you sure you want to delete this Effort Unit?');
	echo html_ac(html_ap() - 1);
	echo html_ao('p', array('class'=>'align-center'));
	echo html_e('input', array('type'=>'checkbox', 'name'=>'confirm_delete', 'id'=>'confirm_delete', 'value'=>1));
	echo html_e('label', array('for'=>'confirm_delete'),_('I am Sure'));
	echo html_ac(html_ap() - 1);
	echo html_ao('p');
	echo html_e('input', array('type' => 'submit', 'value' => _('Delete')));
	echo html_e('input', array('type' => 'button', 'value' => _('Cancel'), 'onclick' => 'window.location=\''.util_make_uri($currentURL.($urlParameters ? '?'.$urlParameters:'')).'\''));
	echo html_ac(html_ap() - 1);
	echo $HTML->closeForm();
}

function postdelete_unit(&$effortUnitSet) {
	global $HTML;
	$unitId = getIntFromRequest('unit_id','0');
	$unit = new EffortUnit($effortUnitSet,$unitId);
	if (!$unit || !is_object($unit)) {
		echo $HTML->error_msg(_('Effort Unit could not be created'));
		return false;
	}
	if ($unit->isError()) {
		echo $HTML->error_msg($unit->getErrorMessage());
		return false;
	}
	$sure = getIntFromRequest('confirm_delete',0);
	if (!$sure) {
		echo $HTML->error_msg(_('You haven\'t confirmed the deletion of the Unit'));
		return false;
	}
	$name = $unit->getName();
	$unit->delete();
	if ($unit->isError()) {
		echo $HTML->error_msg($unit->getErrorMessage());
		return false;
	}
	echo $HTML->feedback(sprintf(_('%s successfully deleted.'), $name));
	return true;
}

function order_unit(&$effortUnitSet) {
	global $HTML;
	$unitId = getIntFromRequest('unit_id','0');
	$unit = new EffortUnit($effortUnitSet,$unitId);
	$new_pos = getIntFromRequest('new_pos','0');
	if ($new_pos) {
		$unit->reorderUnits($new_pos);
	}
	return true;
}

function copy_set(&$effortUnitSet){
	global $HTML;
	$currentURL = getStringFromServer('PHP_SELF');
	$urlParameters = urlParameters($effortUnitSet);
	$inputParameters = inputParameters($effortUnitSet);
	echo html_e('h2', array(), _('Effort Unit Set copy confirmation'));
	echo $HTML->openForm(array('action' => $currentURL, 'method' => 'post'));
	echo html_e('input', array('type'=>'hidden', 'name'=>'function', 'value'=>'postcopy_set'));
	echo html_e('input', array('type'=>'hidden', 'name'=>'form_key', 'value'=>form_generate_key()));
	echo $inputParameters;
	echo html_ao('p',array('class'=>'important'));
	echo _('Are you sure you want to copy this Effort Units?');
	echo html_ac(html_ap() - 1);
	echo html_ao('p', array('class'=>'align-center'));
	echo html_e('input', array('type'=>'checkbox', 'name'=>'confirm_copy', 'id'=>'confirm_copy', 'value'=>1));
	echo html_e('label', array('for'=>'confirm_copy'),_('I am Sure'));
	echo html_ac(html_ap() - 1);
	echo html_ao('p');
	echo html_e('input', array('type' => 'submit', 'value' => _('Copy')));
	echo html_e('input', array('type' => 'button', 'value' => _('Cancel'), 'onclick' => 'window.location=\''.util_make_uri($currentURL.($urlParameters ? '?'.$urlParameters:'')).'\''));
	echo html_ac(html_ap() - 1);
	echo $HTML->closeForm();
}

function postcopy_set(&$effortUnitSet) {
	global $HTML;
	$sure = getIntFromRequest('confirm_copy',0);
	if (!$sure) {
		echo $HTML->error_msg( _('You haven\'t confirmed the copy of the Effort Unit Set'));
		return false;
	}
	$object = $effortUnitSet->getObject();
	$newEffortUnitSet = new EffortUnitSet($object);
	if (!$newEffortUnitSet) {
		echo $HTML->error_msg(_('Error copying Effort Unit Set')._(':').' '._('Error on new EffortUnitSet'));
		return false;
	}
	if ($newEffortUnitSet->isError()) {
		echo $HTML->error_msg(_('Error copying Effort Unit Set')._(':').' '._('Error on new EffortUnitSet').' '.$newEffortUnitSet->getErrorMessage());
		return false;
	}
	if (!$newEffortUnitSet->copy($effortUnitSet)) {
		echo $HTML->error_msg(_('Error copying Effort Unit Set')._(':').' '.$newEffortUnitSet->getErrorMessage());
		return false;
	}
	if ($newEffortUnitSet->isError()) {
		echo $HTML->error_msg($newEffortUnitSet->getErrorMessage());
		return false;
	}
	$effortUnitSet = $newEffortUnitSet;
	echo $HTML->feedback(sprintf(_('Effort Unit Set successfully copied.')));
	return true;
}

function update_set(&$effortUnitSet) {
	global $HTML;
	$isAutoconvert = (getStringFromRequest('is_autoconvert')=='on'?1:0);
	var_dump($isAutoconvert);
	if (!$effortUnitSet->update($isAutoconvert)) {
		echo $HTML->error_msg(_('Error updating Effort Unit Set')._(':').' '.$effortUnitSet->getErrorMessage());
		return false;
	}
	if ($effortUnitSet->isError()) {
		echo $HTML->error_msg($effortUnitSet->getErrorMessage());
		return false;
	}
	echo $HTML->feedback(sprintf(_('Effort Unit Set successfully updated.')));
	return true;
}

function inputParameters($effortUnitSet){
	$return = '';
	switch ($effortUnitSet->getObjectLevel()) {
		case EFFORTUNITSET_TRACKER_LEVEL:
			$return .= html_e('input', array('type'=>'hidden', 'name'=>'effort_units', 'value'=>1));
			$return .= html_e('input', array('type'=>'hidden', 'name'=>'atid', 'value'=>$effortUnitSet->getArtifactType()->getID()));
		case EFFORTUNITSET_PROJECT_LEVEL:
			$return .= html_e('input', array('type'=>'hidden', 'name'=>'group_id', 'value'=>$effortUnitSet->getGroup()->getID()));
	}
	return $return;
}

function urlParameters($effortUnitSet){
	$return = '';
	switch ($effortUnitSet->getObjectLevel()) {
		case EFFORTUNITSET_TRACKER_LEVEL:
			$return = 'group_id='.$effortUnitSet->getGroup()->getID().'&atid='.$effortUnitSet->getArtifactType()->getID().'&effort_units=1';
			break;
		case EFFORTUNITSET_PROJECT_LEVEL:
			$return = 'group_id='.$effortUnitSet->getGroup()->getID();
			break;
	}
	return $return;
}
