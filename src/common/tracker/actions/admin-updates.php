<?php
/**
 * Tracker Facility
 *
 * Copyright 2010 (c) FusionForge Team
 * Copyright 2010 (c) Franck Villaume - Capgemini
 * Copyright 2012-2014,2016, Franck Villaume - TrivialDev
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

global $ath;
global $error_msg;
global $feedback;
global $group_id;
global $atid;

//
//	Create an extra field
//
if (getStringFromRequest('add_extrafield')) {
	$name = getStringFromRequest('name');
	$description = getStringFromRequest('description');
	$field_type = getStringFromRequest('field_type');
	$attribute1 = getStringFromRequest('attribute1');
	$attribute2 = getStringFromRequest('attribute2');
	$pattern = getStringFromRequest('pattern');
	$parent = getStringFromRequest('parent');
	$is_required = getStringFromRequest('is_required');
	$alias = getStringFromRequest('alias');
	$hide100 = getStringFromRequest('hide100');
	$show100label = getStringFromRequest('show100label');
	$autoassign = getStringFromRequest('autoassign');
	$is_hidden_on_submit = getStringFromRequest('is_hidden_on_submit');
	$is_disabled = getStringFromRequest('is_disabled');
	$ab = new ArtifactExtraField($ath);

	if (!$ab || !is_object($ab)) {
		$error_msg .= _('Unable to create ArtifactExtraField Object');
//	} elseif ($ab->isError())
//		$error_msg .= $ab->getErrorMessage();
	} else {
		if ($hide100) {
			$show100 = 0;
		} else {
			$show100 = 1;
		}
		if (!$ab->create($name, $field_type, $attribute1, $attribute2, $is_required, $alias, $show100, $show100label, $description, $pattern, $parent, $autoassign, $is_hidden_on_submit, $is_disabled)) {
			$error_msg .= _('Error inserting a custom field')._(': ').$ab->getErrorMessage();
			$ab->clearError();
		} else {
			$feedback .= _('Extra field inserted');
		}
	}
//
//	Delete an extra field and its contents
//
} elseif (getStringFromRequest('deleteextrafield')) {
	$id = getStringFromRequest('id');
	$ab = new ArtifactExtraField($ath,$id);

	if (!$ab || !is_object($ab)) {
		$error_msg .= _('Unable to create ArtifactExtraField Object');
	} elseif ($ab->isError()) {
		$error_msg .= $ab->getErrorMessage();
	} else {
		$sure = getStringFromRequest('sure');
		$really_sure = getStringFromRequest('really_sure');
		if (!$ab->delete($sure,$really_sure)) {
			$error_msg .= $ab->getErrorMessage();
		} else {
			$browse_list = $ath->getBrowseList();
			$arr = explode(',', $browse_list);
			$idx = array_search($id, $arr);
			if($idx !== False) {
				array_splice($arr, $idx, 1);
			}
			$ath->setBrowseList(join(',', $arr));
			$feedback .= _('Custom Field Deleted');
			$deleteextrafield=false;
			session_redirect('/tracker/admin/?group_id='.$group_id.'&atid='.$atid.'&add_extrafield=1');
		}
	}

//
//	Add an element to an extra field
//
} elseif (getStringFromRequest('add_opt')) {
	$boxid = getStringFromRequest('boxid');
	$ab = new ArtifactExtraField($ath,$boxid);
	if (!$ab || !is_object($ab)) {
		$error_msg .= _('Unable to create ArtifactExtraField Object');
	} elseif ($ab->isError()) {
		$error_msg .= $ab->getErrorMessage();
	} else {
		$ao = new ArtifactExtraFieldElement($ab);
		if (!$ao || !is_object($ao)) {
			$error_msg .= 'Unable to create ArtifactExtraFieldElement Object';
//		} elseif ($ao->isError())
//			$error_msg .= $ao->getErrorMessage();
		} else {
			$name = getStringFromRequest('name');
			$status_id = getIntFromRequest('status_id');
			if (!$ao->create($name,$status_id)) {
				$error_msg .= _('Insert Error')._(': ').$ao->getErrorMessage();
				$ao->clearError();
			} else {
				$feedback .= _('Element inserted');
			}
		}
	}

//
//	Add a canned response
//
} elseif (getStringFromRequest('add_canned')) {
	$title = getStringFromRequest('title');
	$body = getStringFromRequest('body');

	$acr = new ArtifactCanned($ath);
	if (!$acr || !is_object($acr)) {
		$error_msg .= _('Unable to create ArtifactCanned Object');
//	} elseif ($acr->isError()) {
//		$error_msg .= $acr->getErrorMessage();
	} else {
		if (!$acr->create($title,$body)) {
			$error_msg .= _('Insert Error')._(': ').$acr->getErrorMessage();
			$acr->clearError();
		} else {
			$feedback .= _('Canned Response Inserted');
		}
	}

//
//	Update a canned response
//
} elseif (getStringFromRequest('update_canned')) {
	$id = getStringFromRequest('id');
	$title = getStringFromRequest('title');
	$body = getStringFromRequest('body');

	$acr = new ArtifactCanned($ath,$id);
	if (!$acr || !is_object($acr)) {
		$error_msg .= _('Unable to create ArtifactCanned Object');
	} elseif ($acr->isError()) {
		$error_msg .= $acr->getErrorMessage();
	} else {
		if (!$acr->update($title,$body)) {
			$error_msg .= _('Update failed')._(': ').$acr->getErrorMessage();
			$acr->clearError();
		} else {
			$feedback .= _('Canned Response Updated');
			$next = 'add_canned';
		}
	}

//
//	Copy Categories
//
} elseif (getStringFromRequest('copy_opt')) {
	$copyid = getStringFromRequest('copyid');
	$selectid = getStringFromRequest('selectid');

	$copy_rows=count($copyid);
	if ($copy_rows > 0) {
		//
		// create an object for each selected type
		//
		$result = db_query_params ('SELECT * FROM artifact_extra_field_list
						WHERE extra_field_id=$1',
						array($selectid));
		$typeid = db_result($result,0,'group_artifact_id');
		$dest_tracker =& artifactType_get_object($typeid);
		if (!$dest_tracker || !is_object($dest_tracker)) {
			exit_error(_('ArtifactType could not be created'),'tracker');
		} elseif ($dest_tracker->isError()) {
			exit_error($dest_tracker->getErrorMessage(),'tracker');
		}
		//
		//  Copy elements into a field (box) for each tracker selected
		//
		$feedback .= _('Copy into Tracker: ');
		$feedback .= $dest_tracker->getName();
		$aef =new ArtifactExtraField($dest_tracker,$selectid);
		if (!$aef || !is_object($aef)) {
			$error_msg .= _('Unable to create ArtifactExtraField Object');
		} elseif ($aef->isError()) {
			$error_msg .= $aef->getErrorMessage();
		} else {
			$feedback .= ', Box: ';
			$feedback .= $aef->getName();
			$feedback .= '<br />';

			for ($k=0; $k < $copy_rows; $k++) {
				$aefe = new ArtifactExtraFieldElement($aef);
				if (!$aefe || !is_object($aefe)) {
					$error_msg .= 'Unable to create ArtifactExtraFieldElement Object';
				} elseif ($aefe->isError()) {
					$error_msg .= $aefe->getErrorMessage();
				} else {
					$name=$ath->getElementName($copyid[$k]);
					$status=$ath->getElementStatusID($copyid[$k]);
					if (!$aefe->create($name,$status)) {
						$error_msg .= _('Insert Error')._(': ').$aefe->getErrorMessage();
						$aefe->clearError();
					} else {
						$feedback .= '- Copied choice:'. $name;
					}
				}
			}
		}
	}
	$feedback .= '<br />';

//
//	Update an extra field
//
} elseif (getStringFromRequest('update_box')) {
	$id = getStringFromRequest('id');
	$name = getStringFromRequest('name');
	$description = getStringFromRequest('description');
	$attribute1 = getStringFromRequest('attribute1');
	$attribute2 = getStringFromRequest('attribute2');
	$pattern = getStringFromRequest('pattern');
	$parent = getStringFromRequest('parent');
	$is_required = getStringFromRequest('is_required');
	$alias = getStringFromRequest('alias');
	$hide100 = getStringFromRequest('hide100');
	$show100label = getStringFromRequest('show100label');
	$autoassign = getStringFromRequest('autoassign');
	$is_hidden_on_submit = getStringFromRequest('is_hidden_on_submit');
	$is_disabled = getStringFromRequest('is_disabled');
	$defaultArr = getArrayFromRequest('extra_fields', false);
	if (isset($defaultArr[$id])) {
		$default = $defaultArr[$id];
	} else {
		$default = false;
	}

	$ac = new ArtifactExtraField($ath, $id);
	if (!$ac || !is_object($ac)) {
		$error_msg .= _('Unable to create ArtifactExtraField Object');
	} elseif ($ac->isError()) {
		$error_msg .= $ac->getErrorMessage();
	} else {
		if (strlen($hide100)) {
			$show100 = 0;
		} else {
			$show100 = 1;
		}
		if (!$ac->update($name, $attribute1, $attribute2, $is_required, $alias, $show100, $show100label, $description, $pattern, $parent, $autoassign, $is_hidden_on_submit, $is_disabled)) {
			$error_msg .= _('Update failed')._(': ').$ac->getErrorMessage();
			$ac->clearError();
		} else {
			if($default!==false) {
				if (!$ac->setDefaultValues($default)){
					$error_msg .= _('Update failed')._(': ').$ac->getErrorMessage();
					$ac->clearError();
				} else {
					$feedback .= _('Custom Field updated');
					$next = 'add_extrafield';
				}
			}
		}
	}

//
//	Update an Element
//
} elseif (getStringFromRequest('update_opt')) {
	$boxid = getStringFromRequest('boxid');
	$is_default = getStringFromRequest('is_default');
	$ac = new ArtifactExtraField($ath,$boxid);
	if (!$ac || !is_object($ac)) {
		$error_msg .= _('Unable to create ArtifactExtraField Object');
	} elseif ($ac->isError()) {
		$error_msg .= $ac->getErrorMessage();
	} else {
		$id = getStringFromRequest('id');
		$ao = new ArtifactExtraFieldElement($ac,$id);
		if (!$ao || !is_object($ao)) {
			$error_msg .= _('Unable to create ArtifactExtraFieldElement Object');
		} elseif ($ao->isError()) {
			$error_msg .= $ao->getErrorMessage();
		} else {
			$name = getStringFromRequest('name');
			$status_id = getIntFromRequest('status_id');
			$autoAssignTo = getStringFromRequest('auto_assign_to');
			if (!$ao->update($name,$status_id,$autoAssignTo,$is_default)) {
				$error_msg .= _('Update failed')._(': ').$ao->getErrorMessage();
				$ao->clearError();
			} else {
				$feedback .= _('Element updated');
				$parentElements = getArrayFromRequest('parent_elements', array());
				if (!$ao->saveParentElements($parentElements)) {
					$error_msg .= _('Update failed')._(': ').$ao->getErrorMessage();
					$ao->clearError();
				} else {
				$feedback .= html_e('br')._('Parent Elements updated');
				$next = 'add_extrafield';
				}
			}
		}
	}

//
//	Update checked elements (add checked, delete uncheked)
//
} elseif (getStringFromRequest('update_checked_opt')) {
	$checkedElts = getStringFromRequest('element');
	$boxid = getStringFromRequest('boxid');
	$aef = new ArtifactExtraField($ath,$boxid);
	if (!$aef || !is_object($aef)) {
		$error_msg .= _('Unable to create ArtifactExtraField Object');
	} elseif ($aef->isError()) {
		$error_msg .= $aef->getErrorMessage();
	} else {
		$efValues = $aef->getAvailableValues();
		$efEltNames = array();
		foreach ($efValues as $efValue) {
			$efEltNames [$efValue['element_id']]= $efValue['element_name'];
		}
		if (is_array($checkedElts)) {
			foreach ($checkedElts as $checkedElt) {
				if (!in_array($checkedElt,$efEltNames)) {
					$aefe = new ArtifactExtraFieldElement($aef);
					if (!$aefe || !is_object($aefe)) {
						$error_msg .= 'Unable to create ArtifactExtraFieldElement Object';
						//		} elseif ($ao->isError())
						//			$error_msg .= $ao->getErrorMessage();
					} else {
						if (!$aefe->create($checkedElt)) {
							$error_msg .= _('Insert Error')._(': ').$aefe->getErrorMessage();
							$aefe->clearError();
						} else {
							$feedback .= _('Element inserted').' ';
						}
					}
				}
			}
		}
		if (is_array($efEltNames)) {
			foreach ($efEltNames as $efEltId=>$efEltName) {
				if (!in_array($efEltName,$checkedElts)) {
					$aefe = new ArtifactExtraFieldElement($aef,$efEltId);
					if (!$aefe || !is_object($aefe)) {
						$error_msg .= _('Unable to create ArtifactExtraFieldElement Object');
					} else {
						if (!$aefe->delete()) {
							$error_msg .= _('Error deleting an element')._(': ').$aefe->getErrorMessage();
							$aefe->clearError();
						} else {
							$feedback .= _('Element deleted').' ';
							$next = 'add_extrafield';
						}
					}
				}
			}
		}
	}
	$next = 'add_extrafield';
//
//	Clone a tracker's elements to this tracker
//
} elseif (getStringFromRequest('clone_tracker')) {
	$clone_id = getIntFromRequest('clone_id');

	if (!$clone_id) {
		exit_missing_param('',array(_('Clone ID')),'tracker');
	}
	if (!$ath->cloneFieldsFrom($clone_id)) {
		exit_error(_('Error cloning fields: ').$ath->getErrorMessage(),'tracker');
	} else {
		$feedback .= _('Successfully Cloned Tracker Fields ');
		$next = '*main*';
	}

//
//	Update a tracker
//
} elseif (getStringFromRequest('update_type')) {
	$name = getStringFromRequest('name');
	$description = getStringFromRequest('description');
	$email_all = getStringFromRequest('email_all');
	$email_address = getStringFromRequest('email_address');
	$due_period = getStringFromRequest('due_period');
	$status_timeout = getStringFromRequest('status_timeout');
	$use_resolution = getStringFromRequest('use_resolution');
	$submit_instructions = getStringFromRequest('submit_instructions');
	$browse_instructions = getStringFromRequest('browse_instructions');

	if (!$ath->update($name,$description,$email_all,$email_address,
		$due_period,$status_timeout,$use_resolution,$submit_instructions,$browse_instructions)) {
		$error_msg .= _('Update failed')._(': ').$ath->getErrorMessage();
		$ath->clearError();
	} else {
		$feedback .= _('Tracker Updated');
	}

//
//	Update the browse list of a tracker
//
} elseif (getStringFromRequest('customize_list')) {
	if (getStringFromRequest('add_field')) {
		$field_to_add = getStringFromRequest('field_to_add');
		if ($field_to_add) {
			$browse_fields = $ath->getBrowseList();
			$result = $ath->setBrowseList(($browse_fields ? $browse_fields.',' : '').$field_to_add);
		}
		else {
			$result = false;
		}
	}
	elseif (getStringFromRequest('updownorder_field')) {
		$id = getStringFromRequest('id');
		$new_pos = getIntFromRequest('new_pos');
		if ($new_pos) {
			$browse_fields = explode(',',$ath->getBrowseList());
			$pos_of_id = array_search($id, $browse_fields);
			$val_at_new_pos = $browse_fields[$new_pos - 1];
			$browse_fields[$new_pos - 1] = $id;
			$browse_fields[$pos_of_id] = $val_at_new_pos;
			$result = $ath->setBrowseList(implode(',', $browse_fields));
		}
		else {
			$result = false;
		}
	}
	elseif (getStringFromRequest('field_changes_order')) {
		$order = getArrayFromRequest('order');

		// Fields with not modified positions
		$not_changed = array_keys($order, '');

		// Get positions
		$list_size = count(explode(',',$ath->getBrowseList()));
		$not_changed = array();
		$changed = array();
		$out_before = array();
		$out_after = array();
		foreach ($order as $field => $new_pos) {
			if (! $new_pos || ! is_numeric($new_pos)) {
				$not_changed[] = $field;
				continue;
			}
			$new_pos = intval($new_pos);
			if ($new_pos < 1 ) {
				if (! isset($out_before[$new_pos]))
					$out_before[$new_pos] = array();
				$out_before[$new_pos][] = $field;
			}
			elseif ($new_pos > $list_size) {
				if (! isset($out_after[$new_pos]))
					$out_after[$new_pos] = array();
				$out_after[$new_pos][] = $field;
			}
			else {
				if (! isset($changed[$new_pos - 1]))
					$changed[$new_pos - 1] = array();
				$changed[$new_pos - 1][] = $field;
			}
		}
		ksort($changed, SORT_NUMERIC);

		// Start of the browse list
		$start_browse_fields = array();
		$index_start = 0;
		if (! empty($out_before)) {
			ksort($out_before, SORT_NUMERIC);
			foreach (array_values($out_before) as $list) {
				foreach ($list as $field) {
					$start_browse_fields[] = $field;
					$index_start++;
				}
			}
		}

		// Middle of the browse list
		$index = $index_start;
		foreach ($changed as $pos => $list) {
			for (; $index < $pos; $index++) {
				$start_browse_fields[] = array_shift($not_changed);
			}
			foreach ($list as $field) {
				$start_browse_fields[] = $field;
				$index++;
			}
		}

		// End of the browse list
		$end_browse_fields = array();
		if (! empty($out_after)) {
			ksort($out_after, SORT_NUMERIC);
			foreach (array_values($out_after) as $list) {
				foreach ($list as $field) {
					$end_browse_fields[] = $field;
				}
			}
		}

		// And we complete the browse list
		$new_browse_fields = array_merge($start_browse_fields, $not_changed, $end_browse_fields);

		$result = $ath->setBrowseList(implode(',', $new_browse_fields));
	}
	elseif (getStringFromRequest('delete_field')) {
		$id = getStringFromRequest('id');
		$browse_fields = explode(',',$ath->getBrowseList());
		$pos = array_search($id, $browse_fields);
		if ($pos !== false) {
			array_splice($browse_fields, $pos, 1);
			$result = $ath->setBrowseList(implode(',', $browse_fields));
		}
		else {
			$result = false;
		}
	}
	if ($result !== false) {
		$feedback .= _('Tracker Updated');
	}
	else {
		$error_msg .= _('Update failed')._(': ').$ath->getErrorMessage();
		$ath->clearError();
	}

//
//	Delete a tracker
//
} elseif (getStringFromRequest('delete')) {
	$sure = getStringFromRequest('sure');
	$really_sure = getStringFromRequest('really_sure');

	if (!$ath->delete($sure,$really_sure)) {
		$error_msg .= _('Update failed')._(': ').$ath->getErrorMessage();
	} else {
		session_redirect('/tracker/admin/?group_id='.$group_id.'&tracker_deleted=1');
	}

//
//	Update a template
//
} elseif (getStringFromRequest('update_template')) {

	$body = getStringFromRequest('body');
	$body = preg_replace('/^\s*<table>(.*)<\/table>\s*$/s', '\\1', $body);

	db_query_params('UPDATE artifact_group_list SET custom_renderer=$1 WHERE group_artifact_id=$2',
		array($body, $ath->getID()));
	$feedback .= _('Renderer Updated');

//
//	Up or down elements
//
} elseif (getStringFromRequest('updownorder_opt')) {
	$boxid = getStringFromRequest('boxid');
	$id = getIntFromRequest('id');
	$new_pos = getStringFromRequest('new_pos');
	$ac = new ArtifactExtraField($ath,$boxid);
	if (!$ac || !is_object($ac)) {
		$error_msg .= _('Unable to create ArtifactExtraField Object');
	} elseif ($ac->isError()) {
		$error_msg .= $ac->getErrorMessage();
	} else {
		if (!$ac->reorderValues($id, $new_pos)) {
			$error_msg .= _('Update failed')._(': ').$ac->getErrorMessage();
			$ac->clearError();
		} else {
			$feedback .= _('Tracker Updated');
		}
	}

//
//  Change order of elements
//
} elseif (getStringFromRequest('post_changes_order')) {
	$boxid = getStringFromRequest('boxid');
	$order = getArrayFromRequest('order');
	$ac = new ArtifactExtraField($ath,$boxid);
	if (!$ac || !is_object($ac)) {
		$error_msg .= _('Unable to create ArtifactExtraField Object');
	} elseif ($ac->isError()) {
		$error_msg .= $ac->getErrorMessage();
	} else {
		$updated_flag = 0;
		foreach ($order as $id => $new_pos) {
			if ($new_pos == '') continue;
			if (!$ac->reorderValues($id, $new_pos)) {
				$error_msg .= _('Update failed')._(': ').$ac->getErrorMessage();
				$ac->clearError();
				continue;
			}
			else {
				$updated_flag = 1;
			}
		}
		if ($updated_flag)
			$feedback .= _('Tracker Updated');
	}

} elseif (getStringFromRequest('post_changes_alphaorder')) {
	$boxid = getStringFromRequest('boxid');
	$ac = new ArtifactExtraField($ath,$boxid);
	if (!$ac || !is_object($ac)) {
		$error_msg .= _('Unable to create ArtifactExtraField Object');
	} elseif ($ac->isError()) {
		$error_msg .= $ac->getErrorMessage();
	} else {
		if (!$ac->alphaorderValues()) {
			$error_msg .= _('Update failed')._(': ').$ac->getErrorMessage();
			$ac->clearError();
		} else {
			$feedback .= _('Tracker Updated');
		}
	}

} elseif (getStringFromRequest('post_changes_default')) {
	$boxid = getStringFromRequest('boxid');
	$ac = new ArtifactExtraField($ath,$boxid);
	if (!$ac || !is_object($ac)) {
		$error_msg .= _('Unable to create ArtifactExtraField Object');
	} elseif ($ac->isError()) {
		$error_msg .= $ac->getErrorMessage();
	} else {
		if (in_array($ac->getType(), unserialize(ARTIFACT_EXTRAFIELDTYPEGROUP_SINGLECHOICE))) {
			$is_default = getIntFromRequest('is_default');
		} else {
			$is_default =  getArrayFromRequest('is_default');
			$is_default = array_keys($is_default);
		}
		if ($ac->setDefaultValues($is_default)) {
			$feedback .= _('Default value(s) Updated');
		} else {
			$error_msg .= _('Update default value(s) failed')._(': ').$ac->getErrorMessage();
		}
	}

//
// Configure workflow
//
} elseif (getStringFromRequest('workflow')) {
	require_once $gfcommon.'tracker/ArtifactWorkflow.class.php';
	$field_id = getIntFromRequest('field_id');
	$wk = getArrayFromRequest('wk');
	$atw = new ArtifactWorkflow($ath, $field_id);

	if (!isset($wk[100])) {
		$error_msg .= _('Error')._(': ')._('Initial values not saved, no initial state given.').'<br />';
	} else {
		// Save values for the submit form (from=100).
		$atw->saveNextNodes('100', array_keys($wk[100]));
		$feedback .= _('Initial values saved.').'<br />';
	}

	$elearray = $ath->getExtraFieldElements($field_id);
	foreach ($elearray as $e) {
		$from = $e['element_id'];
		$to = isset($wk[$from]) ? array_keys($wk[$from]) : array();
		$atw->saveNextNodes($from, $to);
	}
	$feedback .= _('Workflow saved');

} elseif (getStringFromRequest('workflow_roles')) {
	require_once $gfcommon.'tracker/ArtifactWorkflow.class.php';
	$field_id = getIntFromRequest('field_id');
	$from = getIntFromRequest('from');
	$to = getIntFromRequest('next');
	$role = array_keys(getArrayFromRequest('role'));
	$atw = new ArtifactWorkflow($ath, $field_id);
	$atw->saveAllowedRoles($from, $to, $role);
	$feedback .= _('Workflow (allowed roles) saved');
	$next = 'workflow';

} elseif (getStringFromRequest('workflow_required_fields')) {
	require_once $gfcommon.'tracker/ArtifactWorkflow.class.php';
	$field_id = getIntFromRequest('field_id');
	$from = getIntFromRequest('from');
	$to = getIntFromRequest('next');
	$extra_field = array_keys(getArrayFromRequest('extrafield'));
	$atw = new ArtifactWorkflow($ath, $field_id);
	$atw->saveRequiredFields($from, $to, $extra_field);
	$feedback .= _('Workflow (required fields) saved');
	$next = 'workflow';

} elseif (getStringFromRequest('delete_opt')) {
	$sure = getStringFromRequest('sure');
	$really_sure = getStringFromRequest('really_sure');

	$id = getStringFromRequest('id');
	$boxid = getStringFromRequest('boxid');
	$ab = new ArtifactExtraField($ath,$boxid);
	if (!$ab || !is_object($ab)) {
		$error_msg .= _('Unable to create ArtifactExtraField Object');
	} elseif ($ab->isError()) {
		$error_msg .= $ab->getErrorMessage();
	} else {
		$ao = new ArtifactExtraFieldElement($ab,$id);
		if (!$ao || !is_object($ao)) {
			$error_msg .= _('Unable to create ArtifactExtraFieldElement Object');
		} else {
			if (!$sure || !$really_sure || !$ao->delete()) {
				$error_msg .= _('Error deleting an element')._(': ').$ao->getErrorMessage();
				$ao->clearError();
			} else {
				$feedback .= _('Element deleted');
				$next = 'add_extrafield';
			}
		}
	}
}
