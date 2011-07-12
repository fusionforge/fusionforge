<?php
/**
 * Tracker Front Page
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2002-2004 (c) GForge Team
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


html_use_tooltips();

//
//	get the Group object
//
$group =& group_get_object($group_id);
if (!$group || !is_object($group)) {
	exit_no_group();
}
if ($group->isError()) {
	if($group->isPermissionDeniedError()) {
		exit_permission_denied($group->getErrorMessage(),'tracker');
	} else {
		exit_error($group->getErrorMessage(),'tracker');
	}
}

//
//	Create the ArtifactType object
//
$ath = new ArtifactTypeHtml($group,$atid);

if (!$ath || !is_object($ath)) {
	exit_error(_('ArtifactType could not be created'),'tracker');
}
if ($ath->isError()) {
	if($ath->isPermissionDeniedError()) {
		exit_permission_denied($ath->getErrorMessage(),'tracker');
	} else {
		exit_error($ath->getErrorMessage(),'tracker');
	}
}
switch (getStringFromRequest('func')) {

	case 'add' : {
		if (!$ath->allowsAnon() && !session_loggedin()) {
			exit_permission_denied('tracker');
		}
		include $gfwww.'tracker/add.php';
		break;
	}
	case 'postadd' : {
		if (!form_key_is_valid(getStringFromRequest('form_key'))) {
			exit_form_double_submit('tracker');
		}

		$user_email = getStringFromRequest('user_email');
		$category_id = getIntFromRequest('category_id');
		$artifact_group_id = getIntFromRequest('artifact_group_id');
		$summary = getStringFromRequest('summary');
		$details = getStringFromRequest('details');
		$assigned_to = getStringFromRequest('assigned_to');
		$priority = getStringFromRequest('priority');
		$extra_fields = getStringFromRequest('extra_fields');

		/*
			Create a new Artifact

		*/
		$ah=new ArtifactHtml($ath);
		$feedback = '';
		if (!$ah || !is_object($ah)) {
			form_release_key(getStringFromRequest('form_key'));
			exit_error(_('Artifact Could Not Be Created'),'tracker');
		} else if (!$ath->allowsAnon() && !session_loggedin()) {
			exit_error(_('Artifact: This ArtifactType Does Not Allow Anonymous Submissions. Please Login.'),'tracker');
		} else {
			if (empty($user_email)) {
				$user_email=false;
			} else {
				if (!validate_email($user_email)) {
					form_release_key(getStringFromRequest('form_key'));
					exit_error(_('Invalid Email Address') . htmlspecialchars($user_email),'tracker');
				}
			}
			if ($user_email) {
				$details = "Anonymous message posted by $user_email\n\n".
				$details;
			}
			if (!$ah->create($summary,$details,$assigned_to,$priority,$extra_fields)) {
				form_release_key(getStringFromRequest('form_key'));
				exit_error($ah->getErrorMessage(),'tracker');
			} else {
				//
				//	  Attach files to this Artifact.
				//
				$ext_feedback = '';
				for ($i=0; $i<5; $i++) {
					$f = getUploadedFile("input_file$i");
					$error = $f['error'];
					if (isset($error) && $error > 0) {
						$n = $i+1;
						if ($error === 1 || $error === 2) {
							// UPLOAD_ERR_INI_SIZE or UPLOAD_ERR_FORM_SIZE
							$ext_feedback .= "<br />ERROR: Skipping attachement $n: file is too large.";
						} elseif ($error === 3) {
							// UPLOAD_ERR_PARTIAL
							$ext_feedback .= "<br />ERROR: Skipping attachement $n: transfert interrupted.";
						}
						continue;
					}
					$file_name = $f['name'];
					$tmp_name = $f['tmp_name'];
					$size = $f['size'];
					$type = $f['type'];
					if (!is_uploaded_file($tmp_name)) {
						continue;
					}

					$afh=new ArtifactFileHtml($ah);
					if (!$afh || !is_object($afh)) {
						$error_msg .= _('Could Not Create File Object');
					} elseif ($afh->isError()) {
						$error_msg .= $afh->getErrorMessage();
					} else {
						if (!util_check_fileupload($tmp_name)) {
							form_release_key(getStringFromRequest('form_key'));
							//delete the artifact
							$ah->delete(true);
							exit_error(_('Invalid filename'),'tracker');
						}
						if (!$afh->upload($tmp_name,$file_name,$type,' ')) {
							form_release_key(getStringFromRequest('form_key'));
							//delete the artifact
							$ah->delete(true);
							exit_error(_('Could Not Attach File to Item: '.$afh->getErrorMessage()),'tracker');
						}
					}
				}
				$feedback .= sprintf(_('Item %s successfully created'),'[#'.$ah->getID().']');
				$feedback .= $ext_feedback;
				include $gfwww.'tracker/browse.php';
			}
		}
		break;
	}
	case 'massupdate' : {
		if (!form_key_is_valid(getStringFromRequest('form_key'))) {
			exit_form_double_submit('tracker');
		}

		$artifact_id_list = getArrayFromRequest('artifact_id_list');
		$priority = getStringFromRequest('priority');
		$status_id = getIntFromRequest('status_id');
		$category_id = getIntFromRequest('category_id');
		$artifact_group_id = getIntFromRequest('artifact_group_id');
		$resolution_id = getIntFromRequest('resolution_id');
		$assigned_to = getStringFromRequest('assigned_to');
		$canned_response = getIntFromRequest("canned_response");
		$extra_fields = getArrayFromRequest('extra_fields');
		$was_error=false;

		$count=count($artifact_id_list);

		session_require_perm ('tracker', $ath->getID(), 'manager') ;

		$artifact_type_id=$ath->getID();

		for ($i=0; $i < $count; $i++) {
			$ah=new Artifact($ath,$artifact_id_list[$i]);
			if (!$ah || !is_object($ah)) {
				$feedback .= ' ID: '.$artifact_id_list[$i].'::Artifact Could Not Be Created';
			} else if ($ah->isError()) {
				$feedback .= ' ID: '.$artifact_id_list[$i].'::'.$ah->getErrorMessage();
			} else {
				$_summary = '';
				$_priority=(($priority != 100) ? $priority : $ah->getPriority());
				$_status_id=(($status_id != 100) ? $status_id : $ah->getStatusID());
				//yikes, we want the ability to mass-update to "un-assigned", which is the ID=100, which
				//conflicts with the "no change" ID! Sorry for messy use of 100.1
				$_assigned_to=(($assigned_to != '100.1') ? $assigned_to : $ah->getAssignedTo());

				//
				//	get existing extra field data
				//	we will then override individual elements if needed
				//
				$ef = $ah->getExtraFieldData();
				$keys = array_keys($ef);
				foreach ($keys as $efid) {
					if (is_array($ef[$efid])) {
						$f = $extra_fields[$efid];
						// in this case, if $extra_fields is not setted, it
						// means no option was selected, so we have to delete
						// the original values
						if (!is_array($f) || count($f) == 0) {
							$ef[$efid] = array();
						} else if (in_array('100', $extra_fields[$efid])) {	// "No change" option selected?
							// no change
						} else {
							$ef[$efid] = $f;		// replace old values with new values
						}
					} else {
						// in some cases (ie: textfields) the value is not passed, but
						// this doesn't mean we must delete the existing value
						if (array_key_exists($efid, $extra_fields)) {
							$f = $extra_fields[$efid];
							if ($f == '100') {
								// no change
							} else {
								$ef[$efid] = $f;
							}
						} else {
							$ef[$efid] = addslashes($ef[$efid]);
						}
					}
				}

				if (!$ah->update($_priority,$_status_id,$_assigned_to,$_summary,$canned_response,'',$artifact_type_id,$ef)) {
					$was_error=true;
				}

				if ($was_error) {
					$error_msg .= ' ID: '.$artifact_id_list[$i].'::'.$ah->getErrorMessage();
				}else {
					$was_error=false;
				}
			}
			unset($ah);

		if (!$was_error) {
			$feedback = _('Updated Successfully');			}
		}
		unset ($extra_fields_choice);
		include $gfwww.'tracker/browse.php';
		break;
	}
	case 'postmod' : {
		$artifact_id = getIntFromRequest('artifact_id');
		$priority = getIntFromRequest('priority');
		$status_id = getIntFromRequest('status_id');
		$category_id = getIntFromRequest('category_id');
		$artifact_group_id = getIntFromRequest('artifact_group_id');
		$resolution_id = getIntFromRequest('resolution_id');
		$assigned_to = getStringFromRequest('assigned_to');
		$summary = getStringFromRequest('summary');
		$canned_response = getStringFromRequest('canned_response');
		$details = getStringFromRequest('details');
		$description = getStringFromRequest('description');
		$new_artifact_type_id = getIntFromRequest('new_artifact_type_id');
		$extra_fields = getStringFromRequest('extra_fields');
		$user_email = getStringFromRequest('user_email', false);
		$was_error = false;

		/*
			Technicians can modify limited fields - to be certain
			no one is hacking around, we override any fields they don't have
			permission to change.
		*/
		if (!form_key_is_valid(getStringFromRequest('form_key'))) {
			exit_form_double_submit('tracker');
		}

		$ah=new ArtifactHtml($ath,$artifact_id);
		if (!$ah || !is_object($ah)) {
			exit_error(_('Artifact Could Not Be Created'),'tracker');
		} else if ($ah->isError()) {
			exit_error($ah->getErrorMessage(),'tracker');
		} else if (!$ath->allowsAnon() && !session_loggedin()) {
			exit_error(_('Artifact: This ArtifactType Does Not Allow Anonymous Submissions. Please Login.'),'tracker');
		} else {

			$remlink = getArrayFromRequest('remlink');
			if (count($remlink) > 0 && forge_check_perm ('tracker_admin', $ah->ArtifactType->Group->getID())) {
				require_once $gfcommon.'pm/ProjectTask.class.php';
				foreach ($remlink as $tid) {
					$pt = &projecttask_get_object($tid);
					if (!$pt || $pt->isError())
						exit_error(_('Error'), sprintf(_('Could not get Project Task for %d'), $tid));
					if (!$pt->removeRelatedArtifacts(array($artifact_id)))
						exit_error($tid."->removeRelatedArtifacts(".$artifact_id.")", $pt->getErrorMessage());
				}
			}
			/*

				The following logic causes fields to be overridden
				in the event that someone tampered with the HTML form

			*/
			if (forge_check_perm ('tracker', $ath->getID(), 'tech')
					|| forge_check_perm ('tracker', $ath->getID(), 'manager')) {
				//admin and techs can do everything
				//techs will have certain fields overridden inside the update() function call
				if (!$ah->update($priority,$status_id,
					$assigned_to,$summary,$canned_response,$details,$new_artifact_type_id,$extra_fields, $description)) {
					form_release_key(getStringFromRequest('form_key'));
					$error_msg .= _('Tracker Item'). ': '.$ah->getErrorMessage();
					$ah->clearError();
					$was_error=true;
				}

			} else {

				// Everyone else can add comments
				if ($details) {
					if ($ah->addMessage($details,$user_email,true)) {
						$feedback=_('Comment added');
					} else {
						if ( (strlen($details)>0) ) { //if there was no message, then it's not an error but addMessage returns false and sets missing params error
							//some kind of error in creation
							exit_error($ah->getErrorMessage(),'tracker');
						} else {
							// we have to unset the error if the user added a file ( add a file and no comment)
							if ( (getStringFromRequest('add_file')) ) {
								$ah->clearError();
							}
						}
					}

				} else {

					//everyone else can only add comments
					$delete_file=false;
					$add_file=false;
					if ($ah->addMessage($details,$user_email,true)) {
						$feedback=_('Comment added');
					} else {
						//some kind of error in creation
						exit_error($ah->getErrorMessage(),'tracker');
					}
				}
			}

			// Admin, Techs and Submitter can add files.
			if (forge_check_perm ('tracker', $ath->getID(), 'tech')
					|| forge_check_perm ('tracker', $ath->getID(), 'manager')
					|| (session_loggedin() && ($ah->getSubmittedBy() == user_getid()))) {
				//
				//	  Attach files to this Artifact.
				//
				$ext_feedback = '';
				for ($i=0; $i<5; $i++) {
					$f = getUploadedFile("input_file$i");
					$error = $f['error'];
					if (isset($error) && $error > 0) {
						$n = $i+1;
						if ($error === 1 || $error === 2) {
							// UPLOAD_ERR_INI_SIZE or UPLOAD_ERR_FORM_SIZE
							$ext_feedback .= "<br />" .
								sprintf(_("ERROR: Skipping attachment %d: file is too large."), $n);
						} elseif ($error === 3) {
							// UPLOAD_ERR_PARTIAL
							$ext_feedback .= "<br />" .
								sprintf(_("ERROR: Skipping attachment %d: transfer interrupted."), $n);
						}
						continue;
					}
					$file_name = $f['name'];
					$tmp_name = $f['tmp_name'];
					$size = $f['size'];
					$type = $f['type'];

					if (!is_uploaded_file($tmp_name)) {
						continue;
					}

					$afh=new ArtifactFileHtml($ah);
					if (!$afh || !is_object($afh)) {
						$error_msg .= _('Could Not Create File Object');
					} elseif ($afh->isError()) {
						$error_msg .= $afh->getErrorMessage();
					} else {
						if (!util_check_fileupload($tmp_name)) {
							form_release_key(getStringFromRequest('form_key'));
							exit_error(_('Invalid filename'),'tracker');
						}
						if (!$afh->upload($tmp_name,$file_name,$type,' ')) {
							$error_msg .= ' <br />'._('File Upload: Error').':'.$afh->getErrorMessage();
							$was_error=true;
						} else {
							$feedback .= ' <br />'._('File Upload: Successful');
						}
					}
				}

				// Admin and Techs can delete files.
				if (forge_check_perm ('tracker', $ath->getID(), 'tech')
						|| forge_check_perm ('tracker', $ath->getID(), 'manager')) {
					//
					//	Delete list of files from this artifact
					//
					$delete_file = getStringFromRequest('delete_file');
					if ($delete_file) {
						$count=count($delete_file);
						for ($i=0; $i<$count; $i++) {
							$afh=new ArtifactFileHtml($ah,$delete_file[$i]);
							if (!$afh || !is_object($afh)) {
								$error_msg .= _('Could Not Create File Object::').$delete_file[$i];
							} elseif ($afh->isError()) {
								$error_msg .= $afh->getErrorMessage().'::'.$delete_file[$i];
							} else {
								if (!$afh->delete()) {
									$error_msg .= ' <br />'._('File Delete:').': '.$afh->getErrorMessage();
									$was_error=true;
								} else {
									$feedback .= ' <br />'._('File Delete: Successful');
								}
							}
						}
					}
				}

				//
				//	Show just one feedback entry if no errors
				//
				if (!$was_error) {
					$feedback = sprintf(_('Item %s successfully updated'),'[#'.$ah->getID().']');
				}
				$feedback .= $ext_feedback;
				include $gfwww.'tracker/browse.php';
				break;
			}
		}
	}
		case 'monitor' : {
			if (!session_loggedin()) {
				exit_permission_denied();
			}
			$start = getIntFromRequest('start');
			$stop = getIntFromRequest('stop');
			$artifact_id = getIntFromRequest('artifact_id');

			// Fix to prevent collision with the start variable used in browse.
			$_GET['start'] = 0;

			if ($artifact_id) {
				$ah=new ArtifactHtml($ath,$artifact_id);
				if (!$ah || !is_object($ah)) {
					exit_error(_('Artifact Could Not Be Created'),'tracker');
				} else if ($ah->isError()) {
					exit_error($ah->getErrorMessage(),'tracker');
				} else {
					if ($start && $ah->isMonitoring())
						$feedback = _('Monitoring Started');
					elseif ($stop && !$ah->isMonitoring())
						$feedback = _('Monitoring Deactivated');
					else {
						$ah->setMonitor();
						$error_msg = $ah->getErrorMessage();
					}
					include $gfwww.'tracker/browse.php';
				}
			} else {
				$at=new ArtifactType($group,$atid);
				if (!$at || !is_object($at)) {
					exit_error(_('Artifact Could Not Be Created'),'tracker');
				} else if ($at->isError()) {
					exit_error($at->getErrorMessage(),'tracker');
				} else {
					if ($start && $at->isMonitoring())
						$feedback = _('Monitoring Started');
					elseif ($stop && !$at->isMonitoring())
						$feedback = _('Monitoring Deactivated');
					else {
						$at->setMonitor();
						$feedback=$at->getErrorMessage();
						$at->clearError();
					}
					include $gfwww.'tracker/browse.php';
				}
			}
			break;
		}


		//
		//	Show delete form
		//
		case 'deleteartifact' : {
			session_require_perm ('tracker', $ath->getID(), 'manager') ;

			$aid = getIntFromRequest('aid');
			$ah= new ArtifactHtml($ath,$aid);
			if (!$ah || !is_object($ah)) {
				exit_error(_('Artifact Could Not Be Created'),'tracker');
			} elseif ($ah->isError()) {
				exit_error($ah->getErrorMessage(),'tracker');
			}
			include $gfwww.'tracker/deleteartifact.php';
			break;
		}

		//
		//	Handle the actual delete
		//

		case 'postdeleteartifact' : {
			if (!form_key_is_valid(getStringFromRequest('form_key'))) {
				exit_form_double_submit('tracker');
			}
			session_require_perm ('tracker', $ath->getID(), 'manager') ;

			$aid = getStringFromRequest('aid');
			$ah= new ArtifactHtml($ath,$aid);
			if (!$ah || !is_object($ah)) {
				exit_error(_('Artifact Could Not Be Created'),'tracker');
			} elseif ($ah->isError()) {
				exit_error($ah->getErrorMessage(),'tracker');
			}
			if (!getStringFromRequest('confirm_delete')) {
				$warning_msg .= _('Confirmation failed. Artifact not deleted');
			}
			else {
				if (!$ah->delete(true)) {
					$error_msg .= _('Artifact Delete Failed') . ': '.$ah->getErrorMessage();
				} else {
					$feedback .= _('Artifact Deleted Successfully');
				}
			}
			include $gfwww.'tracker/browse.php';
			break;
		}


		case 'taskmgr' : {
			include $gfwww.'tracker/taskmgr.php';
			break;
		}
		case 'browse' : {
			include $gfwww.'tracker/browse.php';
			break;
		}
		case 'query' : {
			include $gfwww.'tracker/query.php';
			break;
		}
		case 'downloadcsv' : {
			include $gfwww.'tracker/downloadcsv.php';
			break;
		}
		case 'download' : {
			$aid = getIntFromRequest('aid');
			session_redirect('/tracker/download.php?group_id='.$group_id.'&atid='.$atid.'&aid='.$aid.'&file_id='.$file_id);
			break;
		}
		case 'detail' : {
			$aid = getIntFromRequest('aid');

			//
			//	users can modify their own tickets in a limited way if they submitted them
			//	even if they are not artifact admins
			//
			$ah=new ArtifactHtml($ath,$aid);
			if (!$ah || !is_object($ah)) {
				exit_error(_('Artifact Could Not Be Created'),'tracker');
			} else if ($ah->isError()) {
				exit_error($ah->getErrorMessage(),'tracker');
			} else {
				if (forge_check_perm ('tracker', $ath->getID(), 'manager')) {
					include $gfwww.'tracker/mod.php';
				} elseif (forge_check_perm ('tracker', $ath->getID(), 'tech')) {
					include $gfwww.'tracker/mod-limited.php';
				} else {
					include $gfwww.'tracker/detail.php';
				}
			}
			break;
		}
		default : {
			include $gfwww.'tracker/browse.php';
			break;
		}
	}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
