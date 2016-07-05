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
//  FORM TO UPDATE POP-UP CHOICES FOR A BOX
//
/*
	Allow modification of a Choice for a Pop-up Box
*/
$boxid = getIntFromRequest('boxid');
$ac = new ArtifactExtraField($ath,$boxid);
if (!$ac || !is_object($ac)) {
	exit_error(_('Unable to create ArtifactExtraField Object'),'tracker');
} elseif ($ac->isError()) {
	exit_error($ac->getErrorMessage(),'tracker');
} else {
	$id = getStringFromRequest('id');
	$ao = new ArtifactExtraFieldElement($ac,$id);
	if (!$ao || !is_object($ao)) {
		exit_error(_('Unable to create ArtifactExtraFieldElement Object'),'tracker');
	} elseif ($ao->isError()) {
		exit_error($ao->getErrorMessage(),'tracker');
	} else {
		$title = sprintf(_('Update a custom field element in %s'), $ath->getName()) ;
		$ath->adminHeader(array('title'=>$title, 'modal'=>1));
		echo html_e('h2', array(),  _('Custom Field Name')._(': ').$ac->getName());
		echo $HTML->openForm(array('action' => '/tracker/admin/?group_id='.$group_id.'&atid='.$ath->getID(), 'method' => 'post'));
		echo html_e('input', array('type'=>'hidden', 'name'=>'update_opt', 'value'=>'y'));
		echo html_e('input', array('type'=>'hidden', 'name'=>'id', 'value'=>$ao->getID()));
		echo html_e('input', array('type'=>'hidden', 'name'=>'boxid', 'value'=>$boxid));
		echo html_ao('p');
		echo html_e('label', array('for'=>'name'), html_e('strong', array(), _('Element')._(':')).html_e('br'));
		echo html_e('input', array('type'=>'text', 'id'=>'name', 'name'=>'name', 'value'=>$ao->getName()));
		echo html_ac(html_ap()-1);
		// Show a pop-up box to choose the possible statuses that this element will map to
		if ($ac->getType() == ARTIFACT_EXTRAFIELDTYPE_STATUS) {
			echo html_e('strong',array(),_('Status')).html_e('br');
			echo $ath->statusBox('status_id',$ao->getStatusID(),false,false);
		}
		if ($ac->getParent()!=100) {
			echo html_e('strong',array(),_('Select Parent Values')._(':')).html_e('br');
			$parentFieldElmntsArr=$ath->getExtraFieldElements($ac->getParent());
			$parentFieldElmntVals=array();
			foreach ($parentFieldElmntsArr as $parentFieldElmnt) {
				$parentFieldElmntVals[$parentFieldElmnt['element_id']] = $parentFieldElmnt['element_name'];
			}
			$checkedElmntsArr = $ao->getParentElements();
			echo html_build_checkboxes_from_array($parentFieldElmntVals, 'parent_elements', $checkedElmntsArr, true);
		}
		if ($ac->isAutoAssign()) {
			echo html_e('strong',array(),_('Auto assign to')._(':')).html_e('br');
			$engine = RBACEngine::getInstance () ;
			$techs = $engine->getUsersByAllowedAction ('tracker', $ath->getID(), 'tech') ;
			sortUserList($techs);
			foreach ($techs as $tech) {
				$ids[] = $tech->getID() ;
				$names[] = $tech->getRealName().(($tech->getStatus()=='S') ? ' '._('[SUSPENDED]') : '');
			}
			$AutoAssignTo = $ao->getAutoAssignTo();
			echo html_build_select_box_from_arrays($ids, $names, 'auto_assign_to', $AutoAssignTo, true,'nobody');
		} else {
			echo html_e('input', array('type'=>'hidden', 'name'=>'auto_assign_to', 'value'=>100));
		}
		echo $HTML->warning_msg(_('It is not recommended that you change the custom field name because other things are dependent upon it. When you change the custom field name, all related items will be changed to the new name.'));
		echo html_ao('p');
		echo html_e('input', array('type'=>'submit', 'name'=>'post_changes', 'value'=> _('Update')));
		echo html_ac(html_ap()-1);
		echo $HTML->closeForm();
		$ath->footer();
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
