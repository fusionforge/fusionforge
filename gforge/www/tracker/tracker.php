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
		exit_error($Language->getText('general','error'), $group->getErrorMessage());
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
		exit_error($Language->getText('general','error'), $ath->getErrorMessage());
	}
}
switch ($func) {

	case 'add' : {
		include ('add.php');
		break;
	}
	case 'postadd' : {
		/*
			Create a new Artifact

		*/
		$ah=new ArtifactHtml($ath);
		if (!$ah || !is_object($ah)) {
			exit_error('ERROR','Artifact Could Not Be Created');
		} else {
			if (empty($user_email)) {
					$user_email=false;
			} else {
				if (!validate_email($user_email)) {
					exit_error('ERROR', $Language->getText('general','invalid_email'));
				}
			}
			if (!$ah->create($category_id,$artifact_group_id,$summary,$details,$assigned_to,$priority,$extra_fields)) {
				exit_error('ERROR',$ah->getErrorMessage());
			} else {
				//
				//	Attach file to this Artifact.
				//
				if ($add_file) {
					$afh=new ArtifactFileHtml($ah);
					if (!$afh || !is_object($afh)) {
						$feedback .= 'Could Not Create File Object';
//					} elseif ($afh->isError()) {
//						$feedback .= $afh->getErrorMessage();
					} else {
						if (!$afh->upload($input_file,$input_file_name,$input_file_type,$file_description)) {
							$feedback .= ' Could Not Attach File to Item: '.$afh->getErrorMessage();
						}
					}
				}
				$feedback .= $Language->getText('tracker','item_created');
				include 'browse.php';
			}
		}
		break;
	}
	case 'massupdate' : {
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
				$_category_id=(($category_id != 100) ? $category_id : $ah->getCategoryID());
				$_artifact_group_id=(($artifact_group_id != 100) ? $artifact_group_id : $ah->getArtifactGroupID());
				$_resolution_id=(($resolution_id != 100) ? $resolution_id : $ah->getResolutionID());
				//yikes, we want the ability to mass-update to "un-assigned", which is the ID=100, which
				//conflicts with the "no change" ID! Sorry for messy use of 100.1
				$_assigned_to=(($assigned_to != '100.1') ? $assigned_to : $ah->getAssignedTo());
				$_summary=addslashes($ah->getSummary());

				if (!$ah->update($_priority,$_status_id,$_category_id,$_artifact_group_id,$_resolution_id,$_assigned_to,$_summary,$canned_response,'',$artifact_type_id)) {
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
			$feedback = $Language->getText('tracker','updated_successful');			}
		}
		unset ($extra_fields_choice);
		include ('browse.php');
		break;
	}
	case 'postmod' : {
		/*
			Technicians can modify limited fields - to be certain
			no one is hacking around, we override any fields they don't have
			permission to change.
		*/
		$ah=new ArtifactHtml($ath,$artifact_id);
		if (!$ah || !is_object($ah)) {
			exit_error('ERROR','Artifact Could Not Be Created');
		} else if ($ah->isError()) {
			exit_error('ERROR',$ah->getErrorMessage());
		} else {
			if ((!$ath->userIsAdmin() && $ath->userIsTechnician()) || (session_loggedin() && ($ah->getSubmittedBy() == user_getid()))) {
//				&& !(session_loggedin() && ($ah->getSubmittedBy() == user_getid())) 
//				&& (session_loggedin() && ($ah->getAssignedTo() == user_getid()))) {
				$priority=$ah->getPriority();
				$category_id=$ah->getCategoryID();
				$artifact_group_id=$ah->getArtifactGroupID();
				$summary=addslashes($ah->getSummary());
				$canned_response=100;
				$new_artfact_type_id=$ath->getID();
				$delete_file=false;
			}
//echo "$priority|$status_id|$category_id|$artifact_group_id|$resolution_id|
//                $assigned_to|$summary|$canned_response|$details|$new_artfact_type_id";
			if (!$ah->update($priority,$status_id,$category_id,$artifact_group_id,$resolution_id,
				$assigned_to,$summary,$canned_response,$details,$new_artfact_type_id,$extra_fields)) {
				$feedback =$Language->getText('tracker','tracker_item'). ': '.$ah->getErrorMessage();
				$ah->clearError();
				$was_error=true;
			}

			//
			//  Attach file to this Artifact.
			//
			if ($add_file) {
				$afh=new ArtifactFileHtml($ah);
				if (!$afh || !is_object($afh)) {
					$feedback .= 'Could Not Create File Object';
//				} elseif ($afh->isError()) {
//					$feedback .= $afh->getErrorMessage();
				} else {
					if (!util_check_fileupload($input_file)) {
						exit_error("Error","Invalid filename");
					}
					if (!$afh->upload($input_file,$input_file_name,$input_file_type,$file_description)) {
						$feedback .= ' <br />'.$Language->getText('tracker','file_upload_upload').':'.$afh->getErrorMessage();
						$was_error=true;
					} else {
						$feedback .= ' <br />'.$Language->getText('tracker','file_upload_successful');
					}
				}
			}

			//
			//	Delete list of files from this artifact
			//
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
							$feedback .= ' <br />'.$Language->getText('tracker','file_delete').': '.$afh->getErrorMessage();
							$was_error=true;
						} else {
							$feedback .= ' <br />'.$Language->getText('tracker','file_delete_successful');
						}
					}
				}
			}
			//
			//	Show just one feedback entry if no errors
			//
			if (!$was_error) {
				$feedback = $Language->getText('general','update_successful');
			}
			include ('browse.php');
		}
		break;
	}
	case 'postaddcomment' : {
		/*
			Attach a comment to an artifact
			Used by non-admins
		*/
		$ah=new ArtifactHtml($ath,$artifact_id);
		if (!$ah || !is_object($ah)) {
			exit_error('ERROR','Artifact Could Not Be Created');
		} else if ($ah->isError()) {
			exit_error('ERROR',$ah->getErrorMessage());
		} else {
			if ($ah->addMessage($details,$user_email,true)) {
				$feedback=$Language->getText('tracker','comment_added');
				include ('browse.php');
			} else {
				//some kind of error in creation
				exit_error('ERROR',$feedback);
			}
		}
		break;
	}
	case 'monitor' : {
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
	case 'download' : {
		Header("Redirect: /tracker/download.php?group_id=$group_id&atid=$atid&aid=$aid&file_id=$file_id");
		break;
	}
	case 'detail' : {
		//
		//	users can modify their own tickets if they submitted them
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
			} elseif ($ath->userIsTechnician() || (session_loggedin() && ($ah->getSubmittedBy() == user_getid()))) {
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
