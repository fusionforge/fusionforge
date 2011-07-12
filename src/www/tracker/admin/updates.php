<?php
/**
 * Tracker Facility
 *
 * Copyright 2010 (c) FusionForge Team
 * Copyright 2010 (c) Franck Villaume - Capgemini
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

		//
		//	Create an extra field
		//
		if (getStringFromRequest('add_extrafield')) {
			$name = getStringFromRequest('name');
			$field_type = getStringFromRequest('field_type');
			$attribute1 = getStringFromRequest('attribute1');
			$attribute2 = getStringFromRequest('attribute2');
			$is_required = getStringFromRequest('is_required');
			$alias = getStringFromRequest('alias');

			$ab = new ArtifactExtraField($ath);

			if (!$ab || !is_object($ab)) {
				$error_msg .= _('Unable to create ArtifactExtraField Object');
//			} elseif ($ab->isError())
//				$error_msg .= $ab->getErrorMessage();
			} else {
				if (!$ab->create($name,$field_type,$attribute1,$attribute2,$is_required,$alias)) {
					$error_msg .= _('Error inserting a custom field').': '.$ab->getErrorMessage();
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
					session_redirect('/tracker/admin/?group_id='.$group_id.'&atid='.$atid.'&add_extrafield=1&feedback='.urlencode($feedback));
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
//				} elseif ($ao->isError())
//					$error_msg .= $ao->getErrorMessage();
				} else {
					$name = getStringFromRequest('name');
					$status_id = getIntFromRequest('status_id');
					if (!$ao->create($name,$status_id)) {
						$error_msg .= _('Error inserting an element').': '.$ao->getErrorMessage();
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
//			} elseif ($acr->isError()) {
//				$error_msg .= $acr->getErrorMessage();
			} else {
				if (!$acr->create($title,$body)) {
					$error_msg .= _('Error inserting').' : '.$acr->getErrorMessage();
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
					$error_msg .= _('Error updating').' : '.$acr->getErrorMessage();
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
					$error_msg .= $aefe->getErrorMessage();
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
								$error_msg .= _('Error inserting an element').': '.$aefe->getErrorMessage();
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
			$attribute1 = getStringFromRequest('attribute1');
			$attribute2 = getStringFromRequest('attribute2');
			$is_required = getStringFromRequest('is_required');
			$alias = getStringFromRequest('alias');

			$ac = new ArtifactExtraField($ath,$id);
			if (!$ac || !is_object($ac)) {
				$error_msg .= _('Unable to create ArtifactExtraField Object');
			} elseif ($ac->isError()) {
				$error_msg .= $ac->getErrorMessage();
			} else {
				if (!$ac->update($name,$attribute1,$attribute2,$is_required,$alias)) {
					$error_msg .= _('Error updating a custom field').' : '.$ac->getErrorMessage();
					$ac->clearError();
				} else {
					$feedback .= _('Custom Field updated');
					$next = 'add_extrafield';
				}
			}

		//
		//	Update an Element
		//
		} elseif (getStringFromRequest('update_opt')) {
			$id = getStringFromRequest('id');
			$name = getStringFromRequest('name');
			$boxid = getStringFromRequest('boxid');

			$ac = new ArtifactExtraField($ath,$boxid);
			if (!$ac || !is_object($ac)) {
				$error_msg .= _('Unable to create ArtifactExtraField Object');
			} elseif ($ac->isError()) {
				$error_msg .= $ac->getErrorMessage();
			} else {
				$ao = new ArtifactExtraFieldElement($ac,$id);
				if (!$ao || !is_object($ao)) {
					$error_msg .= _('Unable to create ArtifactExtraFieldElement Object');
				} elseif ($ao->isError()) {
					$error_msg .= $ao->getErrorMessage();
				} else {
					$name = getStringFromRequest('name');
					$status_id = getIntFromRequest('status_id');
					if (!$ao->update($name,$status_id)) {
						$error_msg .= _('Error updating a custom field').' : '.$ao->getErrorMessage();
						$ao->clearError();
					} else {
						$feedback .= _('Element updated');
						$next = 'add_extrafield';
					}
				}
			}

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
				$error_msg .= _('Error updating').' : '.$ath->getErrorMessage();
				$ath->clearError();
			} else {
				$feedback .= _('Tracker Updated');
			}

		//
		//	Update the browse list of a tracker
		//
		} elseif (getStringFromRequest('customize_list')) {
			$browse_fields = getArrayFromRequest('browse_fields');
			foreach ($browse_fields as $name => $pos) {
				if ($pos)
					$list_fields[$pos][] = $name;
			}
			ksort($list_fields);
			$browse_fields = array();
			foreach ($list_fields as $pos => $list_name) {
				sort($list_name);
				foreach ($list_name as $name)
					$browse_fields[] = $name;
			}
			$browse_fields = join(',', $browse_fields);
			if (!$ath->setBrowseList($browse_fields)) {
				$error_msg .= _('Error updating').' : '.$ath->getErrorMessage();
				$ath->clearError();
			} else {
				$feedback .= _('Tracker Updated');
			}

		//
		//	Delete a tracker
		//
		} elseif (getStringFromRequest('delete')) {
			$sure = getStringFromRequest('sure');
			$really_sure = getStringFromRequest('really_sure');

			if (!$ath->delete($sure,$really_sure)) {
				$error_msg .= _('Error updating').' : '.$ath->getErrorMessage();
			} else {
				session_redirect('/tracker/admin/?group_id='.$group_id.'&tracker_deleted=1');
			}

		//
		//	Upload template
		//
		} elseif (getStringFromRequest('uploadtemplate')) {

			$input_file = getUploadedFile('input_file');
			$size = $input_file['size'];
			if ($size != 0 ) {
				if (!util_check_fileupload($input_file['tmp_name'])) {
					echo ('Invalid filename :'.$input_file['tmp_name']);
					exit;
				}
				$input_data = addslashes(fread(fopen($input_file['tmp_name'], 'r'), $size));

				db_query_params ('UPDATE artifact_group_list SET custom_renderer=$1 WHERE group_artifact_id=$2',
					 	array ($input_data,
						$ath->getID()));
				echo db_error();
				$feedback .= _('Renderer Uploaded');
			} else {
				$error_msg .= _('Renderer File empty');
			}
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
					$error_msg .= _('Error updating a custom field').' : '.$ac->getErrorMessage();
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
						$error_msg .= _('Error updating a custom field').' : '.$ac->getErrorMessage();
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
				if (!$ac->alphaorderValues($id)) {
					$error_msg .= _('Error updating a custom field').' : '.$ac->getErrorMessage();
					$ac->clearError();
				} else {
					$feedback .= _('Tracker Updated');
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
    			$error_msg .= _('ERROR: Initial values not saved, no initial state given.').'<br />';
    		} else {
	    		// Save values for the submit form (from=100).
	    		$atw->saveNextNodes('100', array_keys($wk[100]));
    			$feedback .= _('Initial values saved.').'<br />';
    		}

	    	$elearray = $ath->getExtraFieldElements($field_id);
			foreach ($elearray as $e) {
				$from = $e['element_id'];
				$next = isset($wk[$from]) ? array_keys($wk[$from]) : array();
				$atw->saveNextNodes($from, array_keys($wk[$from]));
	 		}
			$feedback .= _('Workflow saved');
		} elseif (getStringFromRequest('workflow_roles')) {
			require_once $gfcommon.'tracker/ArtifactWorkflow.class.php';
			$field_id = getIntFromRequest('field_id');
			$from = getIntFromRequest('from');
			$next = getIntFromRequest('next');
			$role = array_keys(getArrayFromRequest('role'));
			$atw = new ArtifactWorkflow($ath, $field_id);
    		$atw->saveAllowedRoles($from, $next, $role);
			$feedback .= _('Workflow saved');
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
						$error_msg .= _('Error deleting an element').': '.$ao->getErrorMessage();
						$ao->clearError();
					} else {
						$feedback .= _('Element deleted');
						$next = 'add_extrafield';
					}
				}
			}
		}
		?>
