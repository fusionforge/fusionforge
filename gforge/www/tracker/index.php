<?php
/**
  *
  * SourceForge Generic Tracker facility
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */


require_once('pre.php');
require_once('note.php');
require_once('common/tracker/Artifact.class');
require_once('common/tracker/ArtifactFile.class');
require_once('www/tracker/include/ArtifactFileHtml.class');
require_once('common/tracker/ArtifactType.class');
require_once('www/tracker/include/ArtifactTypeHtml.class');
require_once('www/tracker/include/ArtifactHtml.class');
require_once('common/tracker/ArtifactGroup.class');
require_once('common/tracker/ArtifactCategory.class');
require_once('common/tracker/ArtifactCanned.class');
require_once('common/tracker/ArtifactResolution.class');
require_once('common/tracker/ArtifactTypeFactory.class');

if (!$sys_use_tracker) {
	exit_disabled();
}


if ($group_id && $atid) {

	//
	//	get the Group object
	//
	$group =& group_get_object($group_id);
	if (!$group || !is_object($group) || $group->isError()) {
		exit_no_group();
	}

	//
	//	Create the ArtifactType object
	//
	$ath = new ArtifactTypeHtml($group,$atid);
	if (!$ath || !is_object($ath)) {
		exit_error('Error','ArtifactType could not be created');
	}
	if ($ath->isError()) {
		exit_error('Error',$ath->getErrorMessage());
	}

	switch ($func) {

		case 'add' : {
			include 'add.php';
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
				if (!$ah->create($category_id,$artifact_group_id,$summary,$details,$assigned_to,$priority, $user_email)) {
					exit_error('ERROR',$ah->getErrorMessage());
				} else {
					//
					//	Attach file to this Artifact.
					//
					if ($add_file) {
						$afh=new ArtifactFileHtml($ah);
						if (!$afh || !is_object($afh)) {
							$feedback .= 'Could Not Create File Object';
//						} elseif ($afh->isError()) {
//							$feedback .= $afh->getErrorMessage();
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
						$feedback .= ' ID: '.$artifact_id_list[$i].'::'.$ah->getErrorMessage();
					}

				}
				unset($ah);
			}
			if (!$was_error) {
				$feedback = $Language->getText('tracker','updated_successful');
			}
			include 'browse.php';
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
				if (!$ath->userIsAdmin() && $ath->userIsTechnician()) {
//					&& !(session_loggedin() && ($ah->getSubmittedBy() == user_getid())) 
//					&& (session_loggedin() && ($ah->getAssignedTo() == user_getid()))) {
					$priority=$ah->getPriority();
					$category_id=$ah->getCategoryID();
					$artifact_group_id=$ah->getArtifactGroupID();
					$summary=addslashes($ah->getSummary());
					$canned_response=100;
					$new_artfact_type_id=$ath->getID();
					$add_file=false;
					$delete_file=false;
				}
				if (!$ah->update($priority,$status_id,$category_id,$artifact_group_id,$resolution_id,
					$assigned_to,$summary,$canned_response,$details,$new_artfact_type_id)) {
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
//					} elseif ($afh->isError()) {
//						$feedback .= $afh->getErrorMessage();
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
				include 'browse.php';
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
					include 'browse.php';
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
				if ($ath->userIsAdmin() || (session_loggedin() && ($ah->getSubmittedBy() == user_getid()))) {
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

} elseif ($group_id) {
	//
	//  get the Group object
	//
	$group =& group_get_object($group_id);
	if (!$group || !is_object($group) || $group->isError()) {
		exit_no_group();
	}

	$atf = new ArtifactTypeFactory($group);
	if (!$group || !is_object($group) || $group->isError()) {
		exit_error('Error','Could Not Get ArtifactTypeFactory');
	}

	$at_arr =& $atf->getArtifactTypes();

	//required params for site_project_header();
	$params['group']=$group_id;
	$params['toptab']='tracker';
	$params['pagename']='tracker';
	$params['sectionvals']=array($group->getPublicName());

	echo site_project_header($params);
	echo $HTML->subMenu(
		array(
			$Language->getText('group','short_tracker'),
			$Language->getText('tracker','reporting'),
			$Language->getText('tracker','admin')
		),
		array(
			'/tracker/?group_id='.$group_id,
			'/tracker/reporting/?group_id='.$group_id,
			'/tracker/admin/?group_id='.$group_id
			
		)
	);
	/*
	echo '<strong><a href="/tracker/reporting/?group_id='.$group_id.'">'.$Language->getText('tracker','reporting').'</a> | '
		 .'<a href="/tracker/admin/?group_id='.$group_id.'">'.$Language->getText('tracker','admin').'</a>'
		 .'</strong><p>';
	*/

	if (!$at_arr || count($at_arr) < 1) {
		echo "<h1>".$Language->getText('tracker','no_trackers')."</h1>";
		echo "<p><strong>".$Language->getText('tracker','no_trackers_text',array('<a href="/tracker/admin/?group_id='.$group_id.'">','</a>'))."</strong>";
		} else {

		echo '<p>'.$Language->getText('tracker', 'choose').'<p>';

		/*
			Put the result set (list of trackers for this group) into a column with folders
		*/
		$tablearr=array($Language->getText('group','short_tracker'),$Language->getText('general','open'),$Language->getText('general','total'),$Language->getText('tracker_admin_update_type','description'));
		echo $HTML->listTableTop($tablearr);

		for ($j = 0; $j < count($at_arr); $j++) {
			echo '
			<tr '. $HTML->boxGetAltRowStyle($j) . '>
				<td><a href="/tracker/?atid='.$at_arr[$j]->getID().'&group_id='.$group_id.'&func=browse">'.
					html_image("ic/tracker20w.png","20","20",array("border"=>"0")).' &nbsp;'.
					$at_arr[$j]->getName() .'</a>
				</td>
				<td align="center">'. (int) $at_arr[$j]->getOpenCount() . '
				</td>
				<td align="center">'. (int) $at_arr[$j]->getTotalCount() .'
				</td>
				<td>' .  $at_arr[$j]->getDescription() .'
				</td>
			</tr>';
		}
		echo $HTML->listTableBottom();
	}

	echo site_project_footer(array());

} else {

	exit_no_group();

}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
