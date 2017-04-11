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
//  FORM TO ADD ELEMENTS TO EXTRA FIELD
//
$boxid = getIntFromRequest('boxid');
$ac = new ArtifactExtraField($ath,$boxid);
if (!$ac || !is_object($ac)) {
	exit_error(_('Unable to create ArtifactExtraField Object'),'tracker');
} elseif ($ac->isError()) {
	exit_error($ac->getErrorMessage(),'tracker');
} else {
//	$efearr=$ath->getExtraFieldElements($boxid);
	$efType = $ac->getType();
	$efearr=$ac->getAvailableValues();
	switch ($efType) {
		case ARTIFACT_EXTRAFIELDTYPE_USER:
			$title = sprintf(_('Add/Update Roles for user choices in %s'), $ath->getName());
			break;
		case ARTIFACT_EXTRAFIELDTYPE_RELEASE:
			$title = sprintf(_('Add/Update Packages for user choices in %s'), $ath->getName());
			break;
		default:
			$title = sprintf(_('Add/Update Custom Field Elements in %s'), $ath->getName());
	}
	$ath->adminHeader(array('title'=>$title, 'modal'=>1));
	echo html_e('h2', array(), _('Custom Field Name')._(': ').$ac->getName());
	switch ($efType) {
		case ARTIFACT_EXTRAFIELDTYPE_USER:
			$vals = array();
			// specific for user select box
			echo html_e('p',array(),_('Choose roles used for the user select box'));
			$g=$ath->getGroup();
			$roles = $g->getRoles();
			foreach ($roles as $role) {
				if (!in_array(get_class($role), array('RoleLoggedIn','RoleAnonymous'))) {
					$vals[$role->getID()]=$role->getName();
				}
			}
			// end
			asort($vals,SORT_FLAG_CASE);
			$rows=count($efearr);
			// change by:
			// $checked_array = array_column($efearr, 'element_name');
			// for php>=5.5.0
			$checked_array = array();
			for ($i=0; $i < $rows; $i++) {
				$checked_array []= $efearr[$i]['element_name'];
			}
			echo $HTML->openForm(array('action' => '/tracker/admin/?group_id='.$group_id.'&atid='.$ath->getID().'&boxid='.$boxid, 'method' => 'post'));
			echo html_e('input', array('type'=>'hidden', 'name'=>'update_checked_opt', 'value'=>'y'));
			echo html_build_checkboxes_from_array($vals, 'element', $checked_array, true, false);
			echo html_e('input', array('type'=>'submit', 'name'=>'post_changes', 'value'=>_('Submit')));
			echo $HTML->closeForm();
			break;
		default:
			$rows=count($efearr);
			if ($rows > 0) {
				echo $HTML->openForm(array('action' => '/tracker/admin/?group_id='.$group_id.'&atid='.$ath->getID().'&boxid='.$boxid, 'method' => 'post'));
				$title_arr=array();
				$title_arr[]=_('Current/New positions');
				if ($efType == ARTIFACT_EXTRAFIELDTYPE_STATUS) {
					$title_arr[] = _('Mapping');
				}
				$title_arr[]=_('Up/Down positions');
				$title_arr[]=_('Elements Defined');
				if (in_array($efType, unserialize(ARTIFACT_EXTRAFIELDTYPEGROUP_CHOICE))) {
					$title_arr[]=_('Default');
				}
				$title_arr[]='';
				echo $HTML->listTableTop($title_arr);
				if (in_array($efType, unserialize(ARTIFACT_EXTRAFIELDTYPEGROUP_CHOICE))) {
					$row_attrs = array();
					$cells = array();
					$cells[] = array('', 'class'=>'align-right');
					if ($efType == ARTIFACT_EXTRAFIELDTYPE_STATUS) {
						$cells[] = array('');
					}
					$cells[] = array('', 'class'=>'align-center');
					$cells[] = array(_('None'));
					if (in_array($efType, unserialize(ARTIFACT_EXTRAFIELDTYPEGROUP_MULTICHOICE))) {
						$defaultValues = $ac->getDefaultValues();
						if (is_array($defaultValues)) {
							if (in_array('100', $defaultValues)) {
								$content = html_build_checkbox('is_default[100]', false, true);
							} else {
								$content = html_build_checkbox('is_default[100]', false, false);
							}
						} else {
							if ($defaultValues == 100) {
								$content = html_build_checkbox('is_default[100]', false, true);
							} else {
								$content = html_build_checkbox('is_default[100]', false, false);
							}
						}
					} elseif (in_array($efType, unserialize(ARTIFACT_EXTRAFIELDTYPEGROUP_SINGLECHOICE))) {
						$defaultValues = $ac->getDefaultValues();
						if ($defaultValues == 100) {
							$content = html_build_radio_button('is_default', 100, true);
						} else {
							$content = html_build_radio_button('is_default', 100, false);
						}
					}
					$cells[] = array($content, 'class'=>'align-center');
					$cells[] = array('', 'class'=>'align-center');
					echo $HTML->multiTableRow($row_attrs, $cells);
				}
				for ($i=0; $i < $rows; $i++) {
					$row_attrs = array();
					$cells = array();
					$content = ($i + 1).' --&gt;'.html_e('input', array('type'=>'text', 'name'=>'order['. $efearr[$i]['element_id'] .']', 'value'=>'', 'size'=>'3', 'maxlength'=>'3'));
					$cells[] = array($content, 'class'=>'align-right');
					if ($efType == ARTIFACT_EXTRAFIELDTYPE_STATUS) {
						$cells[] = array($ath->getStatusName($efearr[$i]['status_id']));
					}
					$content = util_make_link('/tracker/admin/?group_id='.$group_id.'&atid='.$ath->getID().'&boxid='.$boxid.'&id='.$efearr[$i]['element_id'].'&updownorder_opt=1&new_pos='.(($i == 0)? $i + 1 : $i), html_image('ic/btn_up.png','19','18',array('alt'=>'Up', 'title'=>_('Move Up this custom field element'))));
					$content .= util_make_link('/tracker/admin/?group_id='.$group_id.'&atid='.$ath->getID().'&boxid='.$boxid.'&id='.$efearr[$i]['element_id'].'&updownorder_opt=1&new_pos='.(($i == $rows - 1)? $i + 1 : $i + 2), html_image('ic/btn_down.png','19','18',array('alt'=>'Down', 'title'=>_('Move Down this custom field element'))));
					$cells[] = array($content, 'class'=>'align-center');
					$cells[] = array($efearr[$i]['element_name']);
					if (in_array($efType, unserialize(ARTIFACT_EXTRAFIELDTYPEGROUP_MULTICHOICE))) {
						$content = html_build_checkbox('is_default['. $efearr[$i]['element_id'] .']', false, $efearr[$i]['is_default']);
						$cells[] = array($content, 'class'=>'align-center');
					} else {
						$content = html_build_radio_button('is_default', $efearr[$i]['element_id'], $efearr[$i]['is_default']);
						$cells[] = array($content, 'class'=>'align-center');
					}
					$content = util_make_link('/tracker/admin/?update_opt=1&id='.$efearr[$i]['element_id'].'&boxid='.$boxid.'&group_id='.$group_id.'&atid='. $ath->getID(), html_image('ic/configure.png','22','22',array('alt'=>_('Edit'), 'title'=>_('Edit custom field element'))));
					$cells[] = array($content, 'class'=>'align-center');
					echo $HTML->multiTableRow($row_attrs, $cells);
				}
				$row_attrs = array('class'=>'noborder');
				$cells = array();
				$content = html_e('input', array('type'=>'submit', 'name'=>'post_changes_order', 'value'=>_('Reorder')));
				$cells[] = array($content, 'class'=>'align-right');
				$cells[] = array('');
				if ($efType == ARTIFACT_EXTRAFIELDTYPE_STATUS) {
					$cells[] = array('');
				}
				$content = html_e('input', array('type'=>'submit', 'name'=>'post_changes_alphaorder', 'value'=>_('Alphabetical order')));
				$cells[] = array($content, 'class'=>'align-left');
				if (in_array($efType, unserialize(ARTIFACT_EXTRAFIELDTYPEGROUP_CHOICE))) {
					$content = html_e('input', array('type'=>'submit', 'name'=>'post_changes_default', 'value'=>_('Update default')));
					$cells[] = array($content, 'class'=>'align-center');
				}
				$cells[] = array('');
				echo $HTML->multiTableRow($row_attrs, $cells);
				echo $HTML->listTableBottom();
				echo $HTML->closeForm();
			} else {
				echo html_e('strong', array(), _('You have not defined any elements.'));
			}
			echo html_e('br').html_e('br');
			echo $HTML->openForm(array('action' => '/tracker/admin/?group_id='.$group_id.'&boxid='.$boxid.'&atid='.$ath->getID(), 'method' => 'post'));
			echo html_e('input', array('type'=>'hidden', 'name'=>'add_opt', 'value'=>'y'));
			echo html_e('label', array('for'=>'name'),html_e('strong', array(), _('Add New Element')._(':')));
			echo html_e('input', array('type'=>'text', 'id'=>'name', 'name'=>'name', 'value'=>'', 'size'=>'15', 'maxlength'=>'30', 'required'=>'required'));
			// Show a pop-up box to choose the possible statuses that this element will map to
			if ($efType == ARTIFACT_EXTRAFIELDTYPE_STATUS) {
				echo html_e('strong', array(), _('Status'));
				echo $ath->statusBox('status_id',1,false,false);
			}
			echo html_e('input', array('type'=>'submit', 'name'=>'post_changes', 'value'=>_('Submit')));
			echo $HTML->closeForm();
	}
	if ($efType != ARTIFACT_EXTRAFIELDTYPE_USER) {} else {}
	$ath->footer();

}
// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
