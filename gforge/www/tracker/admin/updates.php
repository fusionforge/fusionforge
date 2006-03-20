<?php
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
				$feedback .= 'Unable to create ArtifactExtraField Object';
//			} elseif ($ab->isError())
//				$feedback .= $ab->getErrorMessage();			
			} else {
				if (!$ab->create($name,$field_type,$attribute1,$attribute2,$is_required,$alias)) {
					$feedback .= $Language->getText('tracker_admin_build_boxes','error_inserting_box').': '.$ab->getErrorMessage();
					$ab->clearError();
				} else {
					$feedback .= $Language->getText('tracker_admin_build_boxes','box_name_inserted');
				}
			}
		//
		//	Delete an extra field and its contents
		//
		} elseif (getStringFromRequest('deleteextrafield')) {
			$id = getStringFromRequest('id');
			$ab = new ArtifactExtraField($ath,$id);
		
			if (!$ab || !is_object($ab)) {
				$feedback .= 'Unable to create ArtifactExtraField Object';
			} elseif ($ab->isError()) {
				$feedback .= $ab->getErrorMessage();			
			} else {
				$sure = getStringFromRequest('sure');
				$really_sure = getStringFromRequest('really_sure');
				if (!$ab->delete($sure,$really_sure)) {
					$feedback .= $ab->getErrorMessage();
				} else {
					$feedback .= $Language->getText('tracker_admin_build_boxes','extrafield_deleted');
					$deleteextrafield=false;
				}
			}

		//
		//	Add an element to an extra field
		//
		} elseif (getStringFromRequest('add_opt')) {
			$boxid = getStringFromRequest('boxid');
			$ab = new ArtifactExtraField($ath,$boxid);
			if (!$ab || !is_object($ab)) {
				$feedback .= 'Unable to create ArtifactExtraField Object';
			} elseif ($ab->isError()) {
				$feedback .= $ab->getErrorMessage();			
			} else {
				$ao = new ArtifactExtraFieldElement($ab);
				if (!$ao || !is_object($ao)) {
					$feedback .= 'Unable to create ArtifactExtraFieldElement Object';
//				} elseif ($ao->isError())
//					$feedback .= $ao->getErrorMessage();			
				} else {
					$name = getStringFromRequest('name');
					$status_id = getIntFromRequest('status_id');
					if (!$ao->create($name,$status_id)) {
						$feedback .= $Language->getText('tracker_admin_build_boxes','error_inserting_choice').': '.$ao->getErrorMessage();
						$ao->clearError();
					} else {
						$feedback .= $Language->getText('tracker_admin_build_boxes','choice_inserted');
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
				$feedback .= 'Unable to create ArtifactCanned Object';
//			} elseif ($acr->isError()) {
//				$feedback .= $acr->getErrorMessage();			
			} else { 
				if (!$acr->create($title,$body)) {
					$feedback .= $Language->getText('tracker_admin','error_inserting').' : '.$acr->getErrorMessage();
					$acr->clearError();
				} else {
					$feedback .= $Language->getText('tracker_admin','canned_response_inserted');
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
				$feedback .= 'Unable to create ArtifactCanned Object';
			} elseif ($acr->isError()) {
				$feedback .= $acr->getErrorMessage();
			} else {
				if (!$acr->update($title,$body)) {
					$feedback .= $Language->getText('tracker_admin','error_updating').' : '.$acr->getErrorMessage();
					$acr->clearError();
				} else {
					$feedback .= $Language->getText('tracker_admin','canned_response_updated');
					$update_canned=false;
					$add_canned=true;
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
				$result = db_query("SELECT * FROM artifact_extra_field_list 
					WHERE extra_field_id='$selectid'");
				$typeid = db_result($result,0,'group_artifact_id');
				$dest_tracker =& artifactType_get_object($typeid);
				if (!$dest_tracker || !is_object($dest_tracker)) {
					exit_error('Error','ArtifactType could not be created');
				} elseif ($dest_tracker->isError()) {
					exit_error($Language->getText('general','error'),$dest_tracker->getErrorMessage());
				}
				//
				//  Copy elements into a field (box) for each tracker selected 
				//
				$feedback .= 'Copy into Tracker: ';
				$feedback .= $dest_tracker->getName();
				$aef =new ArtifactExtraField($dest_tracker,$selectid);
				if (!$aef || !is_object($aef)) {
					$feedback .= 'Unable to create ArtifactExtraField Object';
				} elseif ($aef->isError()) {
					$feedback .= $aefe->getErrorMessage();
				} else {
					$feedback .= ', Box: ';
					$feedback .= $aef->getName();
					$feedback .= '<br />';

					for ($k=0; $k < $copy_rows; $k++) {
					$aefe = new ArtifactExtraFieldElement($aef);
						if (!$aefe || !is_object($aefe)) {
							$feedback .= 'Unable to create ArtifactExtraFieldElement Object';
						} elseif ($aefe->isError()) {
							$feedback .= $aefe->getErrorMessage();			
						} else {
							$name=addslashes($ath->getElementName($copyid[$k]));
							$status=$ath->getElementStatusID($copyid[$k]);
							if (!$aefe->create($name,$status)) {
								$feedback .= $Language->getText('tracker_admin_build_boxes','error_inserting_choice').': '.$aefe->getErrorMessage();
								$aefe->clearError();
							} else {
								$feedback .= '- Copied choice:';
								$feedback .= stripslashes($name);
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
				$feedback .= 'Unable to create ArtifactExtraField Object';
			} elseif ($ac->isError()) {
				$feedback .= $ac->getErrorMessage();
			} else {
				if (!$ac->update($name,$attribute1,$attribute2,$is_required,$alias)) {
					$feedback .= $Language->getText('tracker_admin_build_boxes','error_updating').' : '.$ac->getErrorMessage();
					$ac->clearError();
				} else {
					$feedback .= $Language->getText('tracker_admin_build_boxes','box_name_updated');
					$update_box=false;
					$add_extrafield=true;
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
				$feedback .= 'Unable to create ArtifactExtraField Object';
			} elseif ($ac->isError()) {
				$feedback .= $ac->getErrorMessage();
			} else {
				$ao = new ArtifactExtraFieldElement($ac,$id);
				if (!$ao || !is_object($ao)) {
					$feedback .= 'Unable to create ArtifactExtraFieldElement Object';
				} elseif ($ao->isError()) {
					$feedback .= $ao->getErrorMessage();
				} else {
					$name = getStringFromRequest('name');
					$status_id = getIntFromRequest('status_id');
					if (!$ao->update($name,$status_id)) {
						$feedback .= $Language->getText('tracker_admin_build_boxes','error_updating').' : '.$ao->getErrorMessage();
						$ao->clearError();
					} else {
						$feedback .= $Language->getText('tracker_admin_build_boxes','choice_updated');
						$update_opt=false;
						$add_extrafield=true;
					}
				}
			}

		//
		//	Clone a tracker's elements to this tracker
		//
		} elseif (getStringFromRequest('clone_tracker')) {
			$clone_id = getStringFromRequest('clone_id');

			if (!$clone_id) {
				exit_missing_param();
			}
			if (!$ath->cloneFieldsFrom($clone_id)) {
				exit_error('Error','Error cloning fields: '.$ath->getErrorMessage());
			} else {
				$feedback .= 'Successfully Cloned Tracker Fields ';
				$clone_tracker='';
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
				$feedback .= $Language->getText('tracker_admin','error_updating').' : '.$ath->getErrorMessage();
				$ath->clearError();
			} else {
				$feedback .= $Language->getText('tracker_admin','tracker_updated');
			}

		//
		//	Delete a tracker
		//
		} elseif (getStringFromRequest('delete')) {
			$sure = getStringFromRequest('sure');
			$really_sure = getStringFromRequest('really_sure');

			if (!$ath->delete($sure,$really_sure)) {
				$feedback .= $Language->getText('tracker_admin','error_updating').' : '.$ath->getErrorMessage();
			} else {
				header ("Location: /tracker/admin/?group_id=${group_id}&tracker_deleted=1");
				exit;
			}

		//
		//	Upload template
		//
		} elseif (getStringFromRequest('uploadtemplate')) {

			$input_file = getUploadedFile('input_file');
			if (!util_check_fileupload($input_file)) {
				echo ('Invalid filename');
				exit;
			}
			$size = $input_file['size'];
			$input_data = addslashes(fread(fopen($input_file['tmp_name'], 'r'), $size));

			db_query("UPDATE artifact_group_list SET custom_renderer='$input_data' WHERE group_artifact_id='".$ath->getID()."'");
			echo db_error();
			$feedback .= 'Renderer Uploaded';

		}

?>
