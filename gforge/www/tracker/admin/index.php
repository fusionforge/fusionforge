<?php
//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net
//
// $Id$

require_once('pre.php');
require_once('common/tracker/Artifact.class');
require_once('common/tracker/ArtifactFile.class');
require_once('www/tracker/include/ArtifactFileHtml.class');
require_once('common/tracker/ArtifactType.class');
require_once('common/tracker/ArtifactTypeFactory.class');
require_once('www/tracker/include/ArtifactTypeHtml.class');
require_once('www/tracker/include/ArtifactHtml.class');
require_once('common/tracker/ArtifactGroup.class');
require_once('common/tracker/ArtifactCategory.class');
require_once('common/tracker/ArtifactCanned.class');
require_once('common/tracker/ArtifactResolution.class');

if ($group_id && $atid) {
//
//
//		UPDATING A PARTICULAR ARTIFACT TYPE
//
//
	//	
	//  get the Group object
	//	
	$group =& group_get_object($group_id);
	if (!$group || !is_object($group) || $group->isError()) {
		exit_no_group();
	}

	$perm =& $group->getPermission( session_get_user() );

	if (!$perm || !is_object($perm) || !$perm->isArtifactAdmin()) {
		exit_permission_denied();
	}

	//
	//  Create the ArtifactType object
	//
	$ath = new ArtifactTypeHtml($group,$atid);
	if (!$ath || !is_object($ath)) {
		exit_error('Error','ArtifactType could not be created');
	}
	if ($ath->isError()) {
		exit_error($Language->getText('general','error').'',$ath->getErrorMessage());
	}

	if ($post_changes) {
//
//
//		Update the database
//
//
		if ($add_cat) {

			$ac = new ArtifactCategory($ath);
			if (!$ac || !is_object($ac)) {
				$feedback .= 'Unable to create ArtifactCategory Object';
//			} elseif ($ac->isError()) {
//				$feedback .= $ac->getErrorMessage();			
			} else {
				if (!$ac->create($name,$assign_to)) {
					$feedback .= $Language->getText('tracker_admin','error_inserting').': '.$ac->getErrorMessage();
					$ac->clearError();
				} else {
					$feedback .= $Language->getText('tracker_admin','category_inserted');
				}
			}

		} elseif ($add_group) {

			$ag = new ArtifactGroup($ath);
			if (!$ag || !is_object($ag)) {
				$feedback .= 'Unable to create ArtifactGroup Object';
//			} elseif ($ag->isError()) {
//				$feedback .= $ag->getErrorMessage();
			} else {
				if (!$ag->create($name)) {
					$feedback .= $Language->getText('tracker_admin','error_inserting').' : '.$ag->getErrorMessage();
					$ag->clearError();
				} else {
					$feedback .= $Language->getText('tracker_admin','group_inserted');
				}
			}

		} elseif ($add_canned) {

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

		} elseif ($add_users) {

			//
			//	if "add all" option, get list of group members
			//	who are not already members of this ArtifactType
			//
			if ($add_all) {
				$sql="SELECT u.user_id
				FROM users u,user_group ug
				WHERE u.user_id=ug.user_id
				AND ug.group_id='$group_id' 
				AND NOT EXISTS (SELECT user_id FROM artifact_perm ap 
				WHERE ap.group_artifact_id='$atid' 
				AND ap.user_id=u.user_id);";
				$addids=util_result_column_to_array(db_query($sql));
			}
			$count=count($addids);
			for ($i=0; $i<$count; $i++) {
				$ath->addUser($addids[$i]);
			}
			if ($ath->isError()) {
				$feedback .= $ath->getErrorMessage();
				$ath->clearError();
			} else {
				$feedback .= $Language->getText('tracker_admin','users_added');
			}
			//go to the perms page
			$add_users=false;
			$update_users=true;

		} elseif ($update_users) {

			//
			//	Handle the 2-D array of user_id/permission level
			//
			$count=count($updateids);
			for ($i=0; $i<$count; $i++) {
				$ath->updateUser($updateids[$i][0],$updateids[$i][1]);
			}
			if ($ath->isError()) {
				$feedback .= $ath->getErrorMessage();
				$ath->clearError();
			} else {
				$feedback .= $Language->getText('tracker_admin','users_updated');
			}

			//
			//	Delete the checked ids
			//
			$count=count($deleteids);
			for ($i=0; $i<$count; $i++) {
				$ath->deleteUser($deleteids[$i]);
			}
			if ($ath->isError()) {
				$feedback .= $ath->getErrorMessage();
				$ath->clearError();
			} else {
				$feedback .= $Language->getText('tracker_admin','users_deleted');
			}

		} elseif ($update_canned) {

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

		} elseif ($update_cat) {

			$ac = new ArtifactCategory($ath,$id);
			if (!$ac || !is_object($ac)) {
				$feedback .= 'Unable to create ArtifactCategory Object';
			} elseif ($ac->isError()) {
				$feedback .= $ac->getErrorMessage();
			} else {
				if (!$ac->update($name,$assign_to)) {
					$feedback .= $Language->getText('tracker_admin','error_updating').' : '.$ac->getErrorMessage();
					$ac->clearError();
				} else {
					$feedback .= $Language->getText('tracker_admin','category_updated');
					$update_cat=false;
					$add_cat=true;
				}
			}

		} elseif ($update_group) {

			$ag = new ArtifactGroup($ath,$id);
			if (!$ag || !is_object($ag)) {
				$feedback .= 'Unable to create ArtifactGroup Object';
			} elseif ($ag->isError()) {
				$feedback .= $ag->getErrorMessage();
			} else {
				if (!$ag->update($name)) {
					$feedback .= $Language->getText('tracker_admin','error_updating').' : '.$ag->getErrorMessage();
					$ag->clearError();
				} else {
					$feedback .= $Language->getText('tracker_admin','group_updated');
					$update_group=false;
					$add_group=true;
				}
			}

		} elseif ($update_type) {

			if (!$ath->update($name,$description,$is_public,$allow_anon,$email_all,$email_address,
				$due_period,$status_timeout,$use_resolution,$submit_instructions,$browse_instructions)) {
				$feedback .= $Language->getText('tracker_admin','error_updating').' : '.$ath->getErrorMessage();
				$ath->clearError();
			} else {
				$feedback .= $Language->getText('tracker_admin','tracker_updated');
			}

		}

	} 
//
//
//
//		FORMS TO ADD/UPDATE DATABASE
//
//
//
	if ($add_cat) {
//
//  FORM TO ADD CATEGORIES
//
		$ath->adminHeader(array ('title'=>$Language->getText('tracker_admin_add_cat','title',$ath->getName())));

		echo "<h1>".$Language->getText('tracker_admin_add_cat','title',$ath->getName())."</h1>";

		/*
			List of possible categories for this ArtifactType
		*/
		$result=$ath->getCategories();
		echo "<p>&nbsp;</p>";
		$rows=db_numrows($result);
		if ($result && $rows > 0) {
			$title_arr=array();
			$title_arr[]=$Language->getText('tracker_admin','tracker_id');
			$title_arr[]=$Language->getText('tracker_admin','tracker_title');
			
			echo $GLOBALS['HTML']->listTableTop ($title_arr);
			
			for ($i=0; $i < $rows; $i++) {
				echo '<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'>'.
					'<td>'.db_result($result, $i, 'id').'</td>'.
					'<td><a href="'.$PHP_SELF.'?update_cat=1&amp;id='.
						db_result($result, $i, 'id').'&amp;group_id='.$group_id.'&amp;atid='. $ath->getID() .'">'.
						db_result($result, $i, 'category_name').'</a></td></tr>';
			}		   

			echo $GLOBALS['HTML']->listTableBottom();

		} else {
			echo "\n<h1>".$Language->getText('tracker_admin_add_cat','no_categories')."</h1>";
		}
		?>
		<p>
		<form action="<?php echo $PHP_SELF.'?group_id='.$group_id.'&atid='.$ath->getID(); ?>" method="post">
		<input type="hidden" name="add_cat" value="y" />
		<strong><?php echo $Language->getText('tracker_admin','category_name') ?>:</strong><br />
		<input type="text" name="name" value="" size="15" maxlength="30" /><br />
		<p>
		<strong><?php echo $Language->getText('tracker_admin','auto_assign_to') ?>:</strong><br />
		<?php echo $ath->technicianBox('assign_to'); ?></p>
		<p>
		<strong><span style="color:red"><?php echo $Language->getText('tracker_admin','category_add_warning') ?></span></strong></p>
		<p>
		<input type="submit" name="post_changes" value="<?php echo$Language->getText('general','submit') ?>" /></p>
		</form></p>
		<?php

		$ath->footer(array());

	} elseif ($add_group) {
//
//  FORM TO ADD GROUP
//
		$ath->adminHeader(array ('title'=>$Language->getText('tracker_admin_add_group','title', $ath->getName()),'pagename'=>'tracker_admin_add_group','titlevals'=>array($ath->getName())));

		/*
			List of possible groups for this ArtifactType
		*/
		$result=$ath->getGroups();
		echo "<p>";
		$rows=db_numrows($result);
		if ($result && $rows > 0) {
			$title_arr=array();
			$title_arr[]=$Language->getText('tracker_admin','tracker_id');
			$title_arr[]=$Language->getText('tracker_admin','tracker_title');
			
			echo $GLOBALS['HTML']->listTableTop ($title_arr);
			
			for ($i=0; $i < $rows; $i++) {
				echo '<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'>'.
					'<td>'.db_result($result, $i, 'id').'</td>'.
					'<td><a href="'.$PHP_SELF.'?update_group=1&amp;id='.
						db_result($result, $i, 'id').'&amp;group_id='.$group_id.'&amp;atid='. $ath->getID() .'">'.
						db_result($result, $i, 'group_name').'</a></td></tr>';
			}		   

			echo $GLOBALS['HTML']->listTableBottom();

		} else {
			echo "\n<h1>".$Language->getText('tracker_admin_add_group','no_groups_defined')."</h1>";
		}
		?>
		<p>
		<form action="<?php echo $PHP_SELF.'?group_id='.$group_id.'&atid='.$ath->getID(); ?>" method="post">
		<input type="hidden" name="add_group" value="y" />
		<strong><?php echo $Language->getText('tracker_admin','group_name')?>:</strong><br />
		<input type="text" name="name" value="" size="15" maxlength="30" /><br />
		<p>
		<strong><span style="color:red"><?php echo $Language->getText('tracker_admin_add_group','group_add_warning') ?></span></strong></p>
		<p>
		<input type="submit" name="post_changes" value="<?php echo $Language->getText('general','submit') ?>" /></p>
		</form></p>
		<?php

		$ath->footer(array());

	} elseif ($add_canned) {
//
//  FORM TO ADD CANNED RESPONSES
//
		$ath->adminHeader(array ('title'=>$Language->getText('tracker_admin_add_canned','title',$ath->getName()).'Add/Change Canned Responses to: '.$ath->getName()));

		echo "<h1>".$Language->getText('tracker_admin_add_canned','title', $ath->getName())."</h1>";

		/*
			List of existing canned responses
		*/
		$result=$ath->getCannedResponses();
		$rows=db_numrows($result);
		echo "<p>&nbsp;</p>";

		if ($result && $rows > 0) {
			//code to show existing responses and link to update page
			echo '
			<h2>'.$Language->getText('tracker_admin_add_canned','existing_responses').':</h2>
			<p>&nbsp;</p>';
			$title_arr=array();
			$title_arr[]=$Language->getText('tracker_admin','tracker_id');
			$title_arr[]=$Language->getText('tracker_admin','tracker_title');

			echo $GLOBALS['HTML']->listTableTop ($title_arr);

			for ($i=0; $i < $rows; $i++) {
				echo '<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'>'.
					'<td>'.db_result($result, $i, 'id').'</td>'.
					'<td><a href="'.$PHP_SELF.'?update_canned=1&amp;id='.
						db_result($result, $i, 'id').'&amp;group_id='.$group_id.'&amp;atid='. $ath->getID() .'">'.
						db_result($result, $i, 'title').'</a></td></tr>';
			}

			echo $GLOBALS['HTML']->listTableBottom();

		} else {
			echo "\n<h1>".$Language->getText('tracker_admin_add_canned','no_responses')."</h1>";
		}
		?>
		<p><?php echo $Language->getText('tracker_admin_add_canned','canned_response_info') ?></p>
		<p>
		<form action="<?php echo $PHP_SELF.'?group_id='.$group_id.'&atid='.$ath->getID(); ?>" method="post">
		<input type="hidden" name="add_canned" value="y" />
		<strong><?php echo $Language->getText('tracker_admin_add_canned','canned_response_title') ?>:</strong><br />
		<input type="text" name="title" value="" size="50" maxlength="50" />
		<p>
		<strong><?php echo $Language->getText('tracker_admin_add_canned','canned_response_body') ?>:</strong><br />
		<textarea name="body" rows="30" cols="65" wrap="hard"></textarea></p>
		<p>
		<input type="submit" name="post_changes" value="<?php echo $Language->getText('general','submit') ?>" /></p>
		</form></p>
		<?php

		$ath->footer(array());

	} elseif ($update_users) {
//
//  FORM TO ADD/UPDATE USERS
//

		$ath->adminHeader(array ('title'=>$Language->getText('tracker_admin_add_users','title', $ath->getName()),'pagename'=>'tracker_admin_update_users','titlevals'=>array($ath->getName())));

		$sql="SELECT * FROM artifactperm_user_vw WHERE group_artifact_id='". $ath->getID() ."'";
		$res=db_query($sql);

		if (!$res || db_numrows($res) < 1) {
			echo '<h2>'.$Language->getText('tracker_admin_add_users','no_developers').'</h2>';
		} else {
			?>
			<?php echo $Language->getText('tracker_admin_add_users','developers_info') ?>
			<form action="<?php echo $PHP_SELF.'?group_id='.$group_id.'&atid='.$ath->getID(); ?>" method="post">
			<input type="hidden" name="update_users" value="y" />
			<?php

			$arr=array();
			$arr[]=$Language->getText('tracker_admin_add_users','delete');
			$arr[]=$Language->getText('tracker_admin_add_users','user_name');
			$arr[]=$Language->getText('tracker_admin_add_users','category_permission');

			echo $GLOBALS['HTML']->listTableTop($arr);

			$i=0;
			//
			//	PHP4 allows multi-dimensional arrays to be passed in from form elements
			//
			while ($row_dev = db_fetch_array($res)) {
				print '
				<input type="hidden" name="updateids['.$i.'][0]" value="'.$row_dev['user_id'].'" />
				<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'>
				<td><input type="checkbox" name="deleteids[]" value="'.$row_dev['user_id'].'" /> '.$Language->getText('tracker_admin_add_users','delete').'</td>

				<td>'.$row_dev['realname'].' ( '. $row_dev['user_name'] .' )</td>

				<td><span style="font-size:smaller"><select name="updateids['.$i.'][1]">
				<option value="0"'.(($row_dev['perm_level']==0)?" selected=\"selected\"":"").'>-</option>
				<option value="1"'.(($row_dev['perm_level']==1)?" selected=\"selected\"":"").'>'.$Language->getText('tracker_admin_add_users','technician').'</option>
				<option value="2"'.(($row_dev['perm_level']==2)?" selected=\"selected\"":"").'>'.$Language->getText('tracker_admin_add_users','tech_admin').'</option>
				<option value="3"'.(($row_dev['perm_level']==3)?" selected=\"selected\"":"").'>'.$Language->getText('tracker_admin_add_users','admin_only').'</option>
				</select></span></td>

				</tr>';
				$i++;
			}
			echo '<tr><td colspan="3" align="center"><input type="submit" name="post_changes" value="'.$Language->getText('tracker_admin_add_users','update_permissions').'" />
			</form></td></tr>';

			echo $GLOBALS['HTML']->listTableBottom();

		}
		?>
		<p>&nbsp;</p>
		<?php echo $Language->getText('tracker_admin_add_users','add_user_info') ?>
		<div align="center">
		<form action="<?php echo $PHP_SELF.'?group_id='.$group_id.'&atid='.$ath->getID(); ?>" method="post">
		<input type="hidden" name="add_users" value="y" />
		<?php
		$sql="SELECT u.user_id, u.realname 
			FROM users u,user_group ug
			WHERE u.user_id=ug.user_id
			AND ug.group_id='$group_id' 
			AND NOT EXISTS (SELECT user_id FROM artifact_perm ap 
			WHERE ap.group_artifact_id='$atid'
			AND ap.user_id=u.user_id);";

		$res=db_query($sql);
		echo db_error();
		echo html_build_multiple_select_box ($res,'addids[]',array(),8,false);
		echo '<p>
		<input type="submit" name="post_changes" value="'.$Language->getText('tracker_admin_add_users','add_users').'" />&nbsp;<input type="checkbox" name="add_all" /> '.$Language->getText('tracker_admin_add_users','add_all_users').'</p>
		</form>
		</div>';

		$ath->footer(array());

	} elseif ($update_canned) {
//
//	FORM TO UPDATE CANNED MESSAGES
//
		$ath->adminHeader(array ('title'=>$Language->getText('tracker_admin_update_canned','title', $ath->getName())));

		echo "<h1>".$Language->getText('tracker_admin_update_canned','title', $ath->getName())."</h1>";

		$acr = new ArtifactCanned($ath,$id);
		if (!$acr || !is_object($acr)) {
			$feedback .= 'Unable to create ArtifactCanned Object';
		} elseif ($acr->isError()) {
			$feedback .= $acr->getErrorMessage();
		} else {
			?>
			<p><?php echo $Language->getText('tracker_admin_add_canned','canned_response_info') ?></p>
			<p>
			<form action="<?php echo $PHP_SELF.'?group_id='.$group_id.'&atid='.$ath->getID(); ?>" method="post">
			<input type="hidden" name="update_canned" value="y" />
			<input type="hidden" name="id" value="<?php echo $acr->getID(); ?>" />
			<strong><?php echo $Language->getText('tracker_admin_add_canned','canned_response_title') ?>:</strong><br />
			<input type="text" name="title" value="<?php echo $acr->getTitle(); ?>" size="50" maxlength="50" />
			<p>
			<strong><?php echo $Language->getText('tracker_admin_add_canned','canned_response_body') ?>:</strong><br />
			<textarea name="body" rows="30" cols="65" wrap="hard"><?php echo $acr->getBody(); ?></textarea></p>
			<p>
			<input type="submit" name="post_changes" value="<?php echo $Language->getText('general','submit') ?>" /></p>
			</form></p>
			<?php
		}
		$ath->footer(array());

	} elseif ($update_cat) {
//
//  FORM TO UPDATE CATEGORIES
//
		/*
			Allow modification of a artifact category
		*/
		$ath->adminHeader(array('title'=>$Language->getText('tracker_admin_update_cat','title',$ath->getName())));

		echo '
			<h1>'.$Language->getText('tracker_admin_update_cat','title',$ath->getName()).'</h1>';

		$ac = new ArtifactCategory($ath,$id);
		if (!$ac || !is_object($ac)) {
			$feedback .= 'Unable to create ArtifactCategory Object';
		} elseif ($ac->isError()) {
			$feedback .= $ac->getErrorMessage();
		} else {
			?>
			<p>
			<form action="<?php echo $PHP_SELF.'?group_id='.$group_id.'&atid='.$ath->getID(); ?>" method="post">
			<input type="hidden" name="update_cat" value="y" />
			<input type="hidden" name="id" value="<?php echo $ac->getID(); ?>" />
			<p>
			<strong><?php echo $Language->getText('tracker_admin_update_cat','category_name') ?>:</strong><br />
			<input type="text" name="name" value="<?php echo $ac->getName(); ?>" /></p>
			<p>
			<strong><?php echo $Language->getText('tracker_admin_update_cat','auto_assign_to') ?>:</strong><br />
			<?php echo $ath->technicianBox('assign_to',$ac->getAssignee()); ?></p>
			<p>
			<strong><span style="color:red"><?php echo $Language->getText('tracker_admin_update_cat','category_change_warning') ?>
				</span></strong></p>
			<p>
			<input type="submit" name="post_changes" value="<?php echo $Language->getText('general','submit') ?>" /></p>
			</form></p>
			<?php
		}

		$ath->footer(array());

	} elseif ($update_group) {
//
//  FORM TO UPDATE GROUPS
//
		/*
			Allow modification of a artifact group
		*/
		$ath->adminHeader(array('title'=>$Language->getText('tracker_admin_update_group','title',$ath->getName()),'pagename'=>'tracker_admin_update_group','titlevals'=>array($ath->getName())));

		$ag = new ArtifactGroup($ath,$id);
		if (!$ag || !is_object($ag)) {
			$feedback .= 'Unable to create ArtifactGroup Object';
		} elseif ($ag->isError()) {
			$feedback .= $ag->getErrorMessage();
		} else {
			?>
			<p>
			<form action="<?php echo $PHP_SELF.'?group_id='.$group_id.'&atid='.$ath->getID(); ?>" method="post">
			<input type="hidden" name="update_group" value="y" />
			<input type="hidden" name="id" value="<?php echo $ag->getID(); ?>" />
			<p>
			<strong><?php echo $Language->getText('tracker_admin','group_name') ?>:</strong><br />
			<input type="text" name="name" value="<?php echo $ag->getName(); ?>" /></p>
			<p>
			<strong><span style="color:red"><?php echo $Language->getText('tracker_admin_update_group','warning') ?></span></strong></p>
			<p>
			<input type="submit" name="post_changes" value="<?php echo $Language->getText('general','submit') ?>" /></p>
			</form></p>
			<?php
		}

		$ath->footer(array());

	} elseif ($update_type) {
//
//	FORM TO UPDATE ARTIFACT TYPES
//
		$ath->adminHeader(array ('title'=>$Language->getText('tracker_admin_update_type','title', $ath->getName()),'pagename'=>'tracker_admin_update_type','titlevals'=>array($ath->getName())));

		?>
		<p>
		<form action="<?php echo $PHP_SELF.'?group_id='.$group_id.'&atid='.$ath->getID(); ?>" method="post">
		<input type="hidden" name="update_type" value="y" />
		<p>
		<?php echo $Language->getText('tracker_admin_update_type','name') ?><br />
		<?php if ($ath->getDataType()) {
			echo $ath->getName();
		} else { 
			?>
			<input type="text" name="name" value="<?php echo $ath->getName(); ?>" /></p>
			<?php 
		} 
		?>
		<p>
		<strong><?php echo $Language->getText('tracker_admin_update_type','description') ?>:</strong><br />
		<?php if ($ath->getDataType()) {
			echo $ath->getDescription();
		} else {
			?>
			<input type="text" name="description" value="<?php echo $ath->getDescription(); ?>" size="50" /></p>
			<?php 
		} 
		?>
		<p>
		<input type="checkbox" name="is_public" value="1" <?php echo (($ath->isPublic())?'checked="checked"':''); ?> /> <strong><?php echo $Language->getText('tracker_admin_update_type','publicy_available') ?></strong><br />
		<input type="checkbox" name="allow_anon" value="1" <?php echo (($ath->allowsAnon())?'checked="checked"':''); ?> /> <strong><?php echo $Language->getText('tracker_admin_update_type','allow_anonymous') ?></strong><br />
		<input type="checkbox" name="use_resolution" value="1" <?php echo (($ath->useResolution())?'checked="checked"':''); ?> /> <strong><?php echo $Language->getText('tracker_admin_update_type','display_resolution') ?></strong></p>
		<p>
		<strong><?php echo $Language->getText('tracker_admin_update_type','send_submissions') ?>:</strong><br />
		<input type="text" name="email_address" value="<?php echo $ath->getEmailAddress(); ?>" /></p>
		<p>
		<input type="checkbox" name="email_all" value="1" <?php echo (($ath->emailAll())?'checked="checked"':''); ?> /> <strong><?php echo $Language->getText('tracker_admin_update_type','email_all_changes') ?></strong><br /></p>
		<p>
		<strong><?php echo $Language->getText('tracker_admin_update_type','days_overdue') ?>:</strong><br />
		<input type="text" name="due_period" value="<?php echo ($ath->getDuePeriod() / 86400); ?>" /></p>
		<p> 
		<strong><?php echo $Language->getText('tracker_admin_update_type','pending_timeout') ?>:</strong><br />
		<input type="text" name="status_timeout"  value="<?php echo($ath->getStatusTimeout() / 86400); ?>" /></p>
		<p>
		<strong><?php echo $Language->getText('tracker_admin_update_type','submit_item_form_text') ?>:</strong><br />
		<textarea name="submit_instructions" rows="10" cols="55" wrap="hard"><?php echo $ath->getSubmitInstructions(); ?></textarea></p>
		<p>
		<strong><?php echo $Language->getText('tracker_admin_update_type','browse_item_form_text') ?>:</strong><br />
		<textarea name="browse_instructions" rows="10" cols="55" wrap="hard"><?php echo $ath->getBrowseInstructions(); ?></textarea></p>
		<p>
		<input type="submit" name="post_changes" value="<?php echo $Language->getText('general','submit') ?>" /></p>
		</form></p>
		<?php

		$ath->footer(array());

	} else {
//
//  SHOW LINKS TO FEATURES
//

		$ath->adminHeader(array ('title'=>$Language->getText('tracker_admin','title').': '.$ath->getName(),'pagename'=>'tracker_admin','titlevals'=>array($ath->getName())));

		echo '<p>
			<a href="'.$PHP_SELF.'?group_id='.$group_id.'&amp;atid='.$ath->getID().'&amp;add_cat=1"><strong>'.$Language->getText('tracker_admin','add_categories').'</strong></a><br />
			'.$Language->getText('tracker_admin','add_categories_info').'</p>';
		echo '<p>
			<a href="'.$PHP_SELF.'?group_id='.$group_id.'&amp;atid='.$ath->getID().'&amp;add_group=1"><strong>'.$Language->getText('tracker_admin','add_groups').'</strong></a><br />
			'.$Language->getText('tracker_admin','add_group_infos').'</p>';
		echo '<p>
			<a href="'.$PHP_SELF.'?group_id='.$group_id.'&amp;atid='.$ath->getID().'&amp;add_canned=1"><strong>'.$Language->getText('tracker_admin','add_canned_responses').'</strong></a><br />
			'.$Language->getText('tracker_admin','add_canned_responses_info').'</p>';
		echo '<p>
			<a href="'.$PHP_SELF.'?group_id='.$group_id.'&amp;atid='.$ath->getID().'&amp;update_users=1"><strong>'.$Language->getText('tracker_admin','add_permissions').'</strong></a><br />
			'.$Language->getText('tracker_admin','add_permissions_info').'.</p>';
		echo '<p>
			<a href="'.$PHP_SELF.'?group_id='.$group_id.'&amp;atid='.$ath->getID().'&amp;update_type=1"><strong>'.$Language->getText('tracker_admin','update_preferences').'</strong></a><br />
			'.$Language->getText('tracker_admin','update_preferences_info').'.</p>';

		$ath->footer(array());
	}

} elseif ($group_id) {

	//
	//  get the Group object
	//
	$group =& group_get_object($group_id);
	if (!$group || !is_object($group) || $group->isError()) {
		exit_no_group();
	}

	$perm =& $group->getPermission( session_get_user() );

	if (!$perm || !is_object($perm) || !$perm->isArtifactAdmin()) {
		exit_permission_denied();
	}

	if ($post_changes) {
		if ($add_at) {
			$res=new ArtifactTypeHtml($group);
			if (!$res->create($name,$description,$is_public,$allow_anon,$email_all,$email_address,
				$due_period,$use_resolution,$submit_instructions,$browse_instructions)) {
				$feedback .= $res->getErrorMessage();
			} else {
				header ("Location: /tracker/admin/?group_id=$group_id&atid=".$res->getID()."&update_users=1");
			}

		}
	}


	$atf = new ArtifactTypeFactory($group);
	if (!$group || !is_object($group) || $group->isError()) {
		exit_error('Error','Could Not Get ArtifactTypeFactory');
	}

	$at_arr =& $atf->getArtifactTypes();

	//required params for site_project_header();
	$params['group']=$group_id;
	$params['toptab']='tracker';
	$params['pagename']='tracker_admin_choose';
	$params['title'] = $page_title;
	$params['sectionvals']=array(group_getname($group_id));
	
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

	if (!$at_arr || count($at_arr) < 1) {
		echo "<h1>".$Language->getText('tracker_admin','no_trackers_found')."</h1>";
		echo "<p>&nbsp;</p>";
	} else {

		echo '
		<p>'.$Language->getText('tracker_admin','choose_datatype').'.</p>';

		/*
			Put the result set (list of forums for this group) into a column with folders
		*/
		$tablearr=array($Language->getText('group','short_tracker'),$Language->getText('tracker_admin_update_type','description'));
		echo $HTML->listTableTop($tablearr);

		for ($j = 0; $j < count($at_arr); $j++) {
			echo '
			<tr '. $HTML->boxGetAltRowStyle($j) . '>
				<td><a href="/tracker/admin/?atid='. $at_arr[$j]->getID() . '&amp;group_id='.$group_id.'">' .
					html_image("ic/tracker20w.png","20","20",array("border"=>"0")) . ' &nbsp;'.
					$at_arr[$j]->getName() .'</a>
				</td>
				<td>'.$at_arr[$j]->getDescription() .'
				</td>
			</tr>';
		}
		echo $HTML->listTableBottom();
	}

	?><?php echo $Language->getText('tracker_admin','intro') ?>
	<p>
	<form action="<?php echo $PHP_SELF.'?group_id='.$group_id; ?>" method="post">
	<input type="hidden" name="add_at" value="y" />
	<p>
	<?php echo $Language->getText('tracker_admin_update_type','name') ?><br />
	<input type="text" name="name" value=""></p>
	<p>
	<strong><?php echo $Language->getText('tracker_admin_update_type','description') ?>:</strong><br />
	<input type="text" name="description" value="" size="50" /></p>
	<p>
	<input type="checkbox" name="is_public" value="1" /> <strong><?php echo $Language->getText('tracker_admin_update_type','publicy_available') ?></strong><br />
	<input type="checkbox" name="allow_anon" value="1" /> <strong><?php echo $Language->getText('tracker_admin_update_type','allow_anonymous') ?></strong><br />
	<input type="checkbox" name="use_resolution" value="1" /> <strong><?php echo $Language->getText('tracker_admin_update_type','display_resolution') ?></strong></p>
	<p>
	<strong><?php echo $Language->getText('tracker_admin_update_type','send_submissions') ?>:</strong><br />
	<input type="text" name="email_address" value="" /></p>
	<p>
	<input type="checkbox" name="email_all" value="1" /> <strong><?php echo $Language->getText('tracker_admin_update_type','email_all_changes') ?></strong><br /></p>
	<p>
	<strong><?php echo $Language->getText('tracker_admin_update_type','days_overdue') ?>:</strong><br />
	<input type="text" name="due_period" value="30" /></p>
	<p>
	<strong><?php echo $Language->getText('tracker_admin_update_type','pending_timeout') ?>:</strong><br />
	<input type="text" name="status_timeout" value="14" /></p>
	<p>
	<strong><?php echo $Language->getText('tracker_admin_update_type','submit_item_form_text') ?>:</strong><br />
	<textarea name="submit_instructions" rows="10" cols="55" wrap="hard"></textarea></p>
	<p>
	<strong><?php echo $Language->getText('tracker_admin_update_type','browse_item_form_text') ?>:</strong><br />
	<textarea name="browse_instructions" rows="10" cols="55" wrap="hard"></textarea></p>
	<p>
	<input type="submit" name="post_changes" value="<?php echo $Language->getText('general','submit') ?>" /></p>
	</form></p>
	<?php

	echo site_project_footer(array());

} else {

	//browse for group first message
	exit_no_group();

}

?>
