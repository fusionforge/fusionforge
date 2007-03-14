<?php


//
//	get the Group object
//
$group =& group_get_object($group_id);
if (!$group || !is_object($group)) {
	exit_no_group();
}
if ($group->isError()) {
	if($group->isPermissionDeniedError()) {
		exit_permission_denied($group->getErrorMessage());
	} else {
		exit_error(_('Error'), $group->getErrorMessage());
	}
}
//
//	Create the ArtifactType object
//
$ath = new ArtifactTypeHtml($group,$atid);

if (!$ath || !is_object($ath)) {
	exit_error('Error','ArtifactType could not be created');
}
if ($ath->isError()) {
	if($ath->isPermissionDeniedError()) {
		exit_permission_denied($group->getErrorMessage());
	} else {
		exit_error(_('Error'), $ath->getErrorMessage());
	}
}
switch (getStringFromRequest('func')) {

	case 'add' : {
		if (!$ath->allowsAnon() && !session_loggedin()) {
			exit_error('ERROR',_('Artifact: This ArtifactType Does Not Allow Anonymous Submissions. Please Login.'));
		} else {
			include ('add.php');
		}
		break;
	}
	case 'postadd' : {
		if (!form_key_is_valid(getStringFromRequest('form_key'))) {
			exit_form_double_submit();
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
		if (!$ah || !is_object($ah)) {
			form_release_key(getStringFromRequest('form_key'));
			exit_error('ERROR','Artifact Could Not Be Created');
		} else if (!$ath->allowsAnon() && !session_loggedin()) {
			exit_error('ERROR',_('Artifact: This ArtifactType Does Not Allow Anonymous Submissions. Please Login.'));
		} else {
			if (empty($user_email)) {
					$user_email=false;
			} else {
				if (!validate_email($user_email)) {
					form_release_key(getStringFromRequest('form_key'));
					exit_error('ERROR', _('Invalid Email Address'));
				}
			}
			if (!$ah->create($summary,$details,$assigned_to,$priority,$extra_fields)) {
				form_release_key(getStringFromRequest('form_key'));
				exit_error('ERROR',$ah->getErrorMessage());
			} else {
				//
				//	  Attach files to this Artifact.
				//
				for ($i=0; $i<5; $i++) {
					$error=$_FILES['input_file']['error'][$i];
					if (isset($error) && $error > 0) {
						continue;
					}
					$file_name=$_FILES['input_file']['name'][$i];
					$tmp_name=$_FILES['input_file']['tmp_name'][$i];
					if (!is_uploaded_file($tmp_name)) {
						continue;
					}
					$size=$_FILES['input_file']['size'][$i];
					$type=$_FILES['input_file']['type'][$i];

					$afh=new ArtifactFileHtml($ah);
					if (!$afh || !is_object($afh)) {
						$feedback .= 'Could Not Create File Object';
  //				} elseif ($afh->isError()) {
  //					$feedback .= $afh->getErrorMessage();
					} else {
						if (!util_check_fileupload($tmp_name)) {
							form_release_key(getStringFromRequest('form_key'));
							//delete the artifact
							$ah->delete(true);
							exit_error("Error","Invalid filename");
						}
						if (!$afh->upload($tmp_name,$file_name,$type,' ')) {
							form_release_key(getStringFromRequest('form_key'));
							//delete the artifact
							$ah->delete(true);
							exit_error(' Could Not Attach File to Item: '.$afh->getErrorMessage());
						}
					}
				}
				$feedback .= _('Item Successfully Created');
				include 'browse.php';
			}
		}
		break;
	}
	case 'massupdate' : {
		if (!form_key_is_valid(getStringFromRequest('form_key'))) {
			exit_form_double_submit();
		}

		$artifact_id_list = getArrayFromRequest('artifact_id_list');
		$priority = getStringFromRequest('priority');
		$status_id = getStringFromRequest('status_id');
		$category_id = getStringFromRequest('category_id');
		$artifact_group_id = getStringFromRequest('artifact_group_id');
		$resolution_id = getStringFromRequest('resolution_id');
		$assigned_to = getStringFromRequest('assigned_to');
		$canned_response = getIntFromRequest("canned_response");
		$extra_fields = getArrayFromRequest('extra_fields');
		
		$count=count($artifact_id_list);

		if (!$ath->userIsAdmin()) {
			exit_permission_denied();
		}

		$artifact_type_id=$ath->getID();

		for ($i=0; $i < $count; $i++) {
			$ah=new Artifact($ath,$artifact_id_list[$i]);
			if (!$ah || !is_object($ah)) {
				$feedback .= ' ID: '.$artifact_id_list[$i].'::Artifact Could Not Be Created';
			} else if ($ah->isError()) {
				$feedback .= ' ID: '.$artifact_id_list[$i].'::'.$ah->getErrorMessage();
			} else {
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
						}
					}
				}

				if (!$ah->update($_priority,$_status_id,$_assigned_to,$_summary,$canned_response,'',$artifact_type_id,$ef)) {
					$was_error=true;
				}

				if ($was_error) {
					$feedback .= ' ID: '.$artifact_id_list[$i].'::'.$ah->getErrorMessage();
				}else {
					$was_error=false;
				}
			}
			unset($ah);

		if (!$was_error) {
			$feedback = _('Updated Successfully');			}
		}
		unset ($extra_fields_choice);
		include ('browse.php');
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
		$new_artifact_type_id = getIntFromRequest('new_artifact_type_id');
		$extra_fields = getStringFromRequest('extra_fields');
	
		/*
			Technicians can modify limited fields - to be certain
			no one is hacking around, we override any fields they don't have
			permission to change.
		*/
		if (!form_key_is_valid(getStringFromRequest('form_key'))) {
			exit_form_double_submit();
		}	

		$ah=new ArtifactHtml($ath,$artifact_id);
		if (!$ah || !is_object($ah)) {
			exit_error('ERROR','Artifact Could Not Be Created');
		} else if ($ah->isError()) {
			exit_error('ERROR',$ah->getErrorMessage());
		} else if (!$ath->allowsAnon() && !session_loggedin()) {
			exit_error('ERROR',_('Artifact: This ArtifactType Does Not Allow Anonymous Submissions. Please Login.'));
		} else {

			/*

				The following logic causes fields to be overridden
				in the event that someone tampered with the HTML form

			*/
			if ($ath->userIsAdmin() || $ath->userIsTechnician()) {

				//admin and techs can do everything
				//techs will have certain fields overridden inside the update() function call
				if (!$ah->update($priority,$status_id,
					$assigned_to,$summary,$canned_response,$details,$new_artifact_type_id,$extra_fields)) {
					$feedback =_('Tracker Item'). ': '.$ah->getErrorMessage();
					$ah->clearError();
					$was_error=true;
				}

			} else {

				if (session_loggedin() && ($ah->getSubmittedBy() == user_getid())) {

					//submitter can only add files & comments

					$delete_file=false;
					if ($ah->addMessage($details,$user_email,true)) {
						$feedback=_('Comment added');
					} else {
						if ( (strlen($details)>0) ) { //if there was no message, then it´s not an error but addMessage returns false and sets missing params error
							//some kind of error in creation
							exit_error($ah->getErrorMessage(),$feedback);
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
						exit_error('ERROR',$ah->getErrorMessage());
					}

				}
			}

			//
			//	  Attach files to this Artifact.
			//
			for ($i=0; $i<5; $i++) {
				$error=$_FILES['input_file']['error'][$i];
				if (isset($error) && $error > 0) {
					continue;
				}
				$file_name=$_FILES['input_file']['name'][$i];
				$tmp_name=$_FILES['input_file']['tmp_name'][$i];
				if (!is_uploaded_file($tmp_name)) {
					continue;
				}
				$size=$_FILES['input_file']['size'][$i];
				$type=$_FILES['input_file']['type'][$i];

				$afh=new ArtifactFileHtml($ah);
				if (!$afh || !is_object($afh)) {
					$feedback .= 'Could Not Create File Object';
  //			} elseif ($afh->isError()) {
  //				$feedback .= $afh->getErrorMessage();
				} else {
					if (!util_check_fileupload($tmp_name)) {
						form_release_key(getStringFromRequest('form_key'));
						exit_error("Error","Invalid filename");
					}
					if (!$afh->upload($tmp_name,$file_name,$type,' ')) {
						$feedback .= ' <br />'._('File Upload: Error').':'.$afh->getErrorMessage();
						$was_error=true;
					} else {
						$feedback .= ' <br />'._('File Upload: Successful');
					}
				}
			}

			//
			//	Delete list of files from this artifact
			//
			$delete_file = getStringFromRequest('delete_file');
			if ($delete_file) {
				$count=count($delete_file);
				for ($i=0; $i<$count; $i++) {
					$afh=new ArtifactFileHtml($ah,$delete_file[$i]);
					if (!$afh || !is_object($afh)) {
						$feedback .= 'Could Not Create File Object::'.$delete_file[$i];
					} elseif ($afh->isError()) {
						$feedback .= $afh->getErrorMessage().'::'.$delete_file[$i];
					} else {
						if (!$afh->delete()) {
							$feedback .= ' <br />'._('File Delete:').': '.$afh->getErrorMessage();
							$was_error=true;
						} else {
							$feedback .= ' <br />'._('File Delete: Successful');
						}
					}
				}
			}
			//
			//	Show just one feedback entry if no errors
			//
			if (!$was_error) {
				$feedback = _('Updated successfully');
			}
			include ('browse.php');
		}
		break;
	}
	case 'monitor' : {
		$artifact_id = getIntFromRequest('artifact_id');
		if ($artifact_id) {
			$ah=new ArtifactHtml($ath,$artifact_id);
			if (!$ah || !is_object($ah)) {
				exit_error('ERROR','Artifact Could Not Be Created');
			} else if ($ah->isError()) {				
				exit_error('ERROR',$ah->getErrorMessage());
			} else {
				$ah->setMonitor();
				$feedback=$ah->getErrorMessage();

				include 'browse.php';
			}
		} else {
			$at=new ArtifactType($group,$atid);
			if (!$at || !is_object($at)) {
				exit_error('ERROR','Artifact Could Not Be Created');
			} else if ($at->isError()) {				
				exit_error('ERROR',$at->getErrorMessage());
			} else {
				$at->setMonitor();
				$feedback=$at->getErrorMessage();	
				$at->clearError();
				include 'browse.php';
			}
		}
		break;
	}


	//
	//	Show delete form
	//
	case 'deleteartifact' : {
		if ($ath->userIsAdmin()) {
			$aid = getStringFromRequest('aid');
			$ah= new ArtifactHtml($ath,$aid);
			if (!$ah || !is_object($ah)) {
				exit_error('ERROR','Artifact Could Not Be Created');
			} elseif ($ah->isError()) {
				exit_error('ERROR',$ah->getErrorMessage());
			}
			include 'deleteartifact.php';
		} else {
			exit_permission_denied();
		}
		break;
	}

	//
	//	Handle the actual delete
	//

	case 'postdeleteartifact' : {
		if (!form_key_is_valid(getStringFromRequest('form_key'))) {
			exit_form_double_submit();
		}
		if ($ath->userIsAdmin()) {
			$aid = getStringFromRequest('aid');
			$ah= new ArtifactHtml($ath,$aid);
			if (!$ah || !is_object($ah)) {
				exit_error('ERROR','Artifact Could Not Be Created');
			} elseif ($ah->isError()) {
				exit_error('ERROR',$ah->getErrorMessage());
			}
			if (!getStringFromRequest('confirm_delete')) {
				$feedback .= _('Confirmation failed. Artifact not deleted');
			}
			else {
				if (!$ah->delete(true)) {
					$feedback .= _('Artifact Delete Failed') . ': '.$ah->getErrorMessage();
				} else {
					$feedback .= _('Artifact Deleted Successfully');
				}
			}
			include 'browse.php';
		} else {
			exit_permission_denied();
		}
		break;
	}


	case 'taskmgr' : {
		include 'taskmgr.php';
		break;
	}
	case 'browse' : {
		include 'browse.php';
		break;
	}
	case 'query' : {
		include ('query.php');
		break;
	}
	case 'downloadcsv' : {
		include ('downloadcsv.php');
		break;
	}
	case 'download' : {
		$aid = getStringFromRequest('aid');
		Header("Redirect: ".$GLOBALS['sys_urlprefix']."/tracker/download.php?group_id=$group_id&atid=$atid&aid=$aid&file_id=$file_id");
		break;
	}
	case 'detail' : {
		$aid = getStringFromRequest('aid');

		//
		//	users can modify their own tickets in a limited way if they submitted them
		//	even if they are not artifact admins
		//
		$ah=new ArtifactHtml($ath,$aid);
		if (!$ah || !is_object($ah)) {
			exit_error('ERROR','Artifact Could Not Be Created');
		} else if ($ah->isError()) {
			exit_error('ERROR',$ah->getErrorMessage());
		} else {
			if ($ath->userIsAdmin()) {
				include 'mod.php';
			} elseif ($ath->userIsTechnician()) {
				include 'mod-limited.php';
			} else {
				include 'detail.php';
			}
		}
		break;
	}
	default : {
		include 'browse.php';
		break;
	}
}

?>
