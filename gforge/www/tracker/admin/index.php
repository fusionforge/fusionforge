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
		exit_error('Error',$ath->getErrorMessage());
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
					$feedback .= ' Error inserting: '.$ac->getErrorMessage();
					$ac->clearError();
				} else {
					$feedback .= ' Category Inserted ';
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
					$feedback .= ' Error inserting: '.$ag->getErrorMessage();
					$ag->clearError();
				} else {
					$feedback .= ' Group Inserted ';
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
					$feedback .= ' Error inserting: '.$acr->getErrorMessage();
					$acr->clearError();
				} else {
					$feedback .= ' Canned Response Inserted ';
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
				$feedback .= ' User(s) Added ';
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
				$feedback .= ' User(s) Updated ';
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
				$feedback .= ' User(s) Deleted ';
			}

		} elseif ($update_canned) {

			$acr = new ArtifactCanned($ath,$id);
			if (!$acr || !is_object($acr)) {
				$feedback .= 'Unable to create ArtifactCanned Object';
			} elseif ($acr->isError()) {
				$feedback .= $acr->getErrorMessage();
			} else {
				if (!$acr->update($title,$body)) {
					$feedback .= ' Error updating: '.$acr->getErrorMessage();
					$acr->clearError();
				} else {
					$feedback .= ' Canned Response Updated ';
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
					$feedback .= ' Error updating: '.$ac->getErrorMessage();
					$ac->clearError();
				} else {
					$feedback .= ' Category Updated ';
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
					$feedback .= ' Error updating: '.$ag->getErrorMessage();
					$ag->clearError();
				} else {
					$feedback .= ' Group Updated ';
					$update_group=false;
					$add_group=true;
				}
			}

		} elseif ($update_type) {

			if (!$ath->update($name,$description,$is_public,$allow_anon,$email_all,$email_address,
				$due_period,$status_timeout,$use_resolution,$submit_instructions,$browse_instructions)) {
				$feedback .= ' Error updating: '.$ath->getErrorMessage();
				$ath->clearError();
			} else {
				$feedback .= ' Tracker Updated ';
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
		$ath->adminHeader(array ('title'=>'Add Categories to: '.$ath->getName()));

		echo "<h1>Add Categories to: ". $ath->getName() ."</h1>";

		/*
			List of possible categories for this ArtifactType
		*/
		$result=$ath->getCategories();
		echo "<p>&nbsp;</p>";
		$rows=db_numrows($result);
		if ($result && $rows > 0) {
			$title_arr=array();
			$title_arr[]='ID';
			$title_arr[]='Title';
			
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
			echo "\n<h1>No categories defined</h1>";
		}
		?>
		<p>
		<form action="<?php echo $PHP_SELF.'?group_id='.$group_id.'&atid='.$ath->getID(); ?>" method="post">
		<input type="hidden" name="add_cat" value="y" />
		<strong>New Category Name:</strong><br />
		<input type="text" name="name" value="" size="15" maxlength="30" /><br />
		<p>
		<strong>Auto-Assign To:</strong><br />
		<?php echo $ath->technicianBox('assign_to'); ?></p>
		<p>
		<strong><span style="color:red">Once you add a category, it cannot be deleted</span></strong></p>
		<p>
		<input type="submit" name="post_changes" value="SUBMIT" /></p>
		</form></p>
		<?php

		$ath->footer(array());

	} elseif ($add_group) {
//
//  FORM TO ADD GROUP
//
		$ath->adminHeader(array ('title'=>'Add/Change Groups to: '.$ath->getName(),'pagename'=>'tracker_admin_add_group','titlevals'=>array($ath->getName())));

		/*
			List of possible groups for this ArtifactType
		*/
		$result=$ath->getGroups();
		echo "<p>";
		$rows=db_numrows($result);
		if ($result && $rows > 0) {
			$title_arr=array();
			$title_arr[]='ID';
			$title_arr[]='Title';
			
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
			echo "\n<h1>No groups defined</h1>";
		}
		?>
		<p>
		<form action="<?php echo $PHP_SELF.'?group_id='.$group_id.'&atid='.$ath->getID(); ?>" method="post">
		<input type="hidden" name="add_group" value="y" />
		<strong>New group Name:</strong><br />
		<input type="text" name="name" value="" size="15" maxlength="30" /><br />
		<p>
		<strong><span style="color:red">Once you add a group, it cannot be deleted</span></strong></p>
		<p>
		<input type="submit" name="post_changes" value="SUBMIT" /></p>
		</form></p>
		<?php

		$ath->footer(array());

	} elseif ($add_canned) {
//
//  FORM TO ADD CANNED RESPONSES
//
		$ath->adminHeader(array ('title'=>'Add/Change Canned Responses to: '.$ath->getName()));

		echo "<h1>Add Canned Responses to: ". $ath->getName() ."</h1>";

		/*
			List of existing canned responses
		*/
		$result=$ath->getCannedResponses();
		$rows=db_numrows($result);
		echo "<p>&nbsp;</p>";

		if ($result && $rows > 0) {
			//code to show existing responses and link to update page
			echo '
			<h2>Existing Responses:</h2>
			<p>&nbsp;</p>';
			$title_arr=array();
			$title_arr[]='ID';
			$title_arr[]='Title';

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
			echo "\n<h1>No responses set up in this group</h1>";
		}
		?>
		<p>Creating useful generic messages can save you a lot of time when
		handling common artifact requests.</p>
		<p>
		<form action="<?php echo $PHP_SELF.'?group_id='.$group_id.'&atid='.$ath->getID(); ?>" method="post">
		<input type="hidden" name="add_canned" value="y" />
		<strong>Title:</strong><br />
		<input type="text" name="title" value="" size="50" maxlength="50" />
		<p>
		<strong>Message Body:</strong><br />
		<textarea name="body" rows="30" cols="65" wrap="hard"></textarea></p>
		<p>
		<input type="submit" name="post_changes" value="SUBMIT" /></p>
		</form></p>
		<?php

		$ath->footer(array());

	} elseif ($update_users) {
//
//  FORM TO ADD/UPDATE USERS
//

		$ath->adminHeader(array ('title'=>'Add/Update Users in: '.$ath->getName(),'pagename'=>'tracker_admin_update_users','titlevals'=>array($ath->getName())));

		$sql="SELECT * FROM artifactperm_user_vw WHERE group_artifact_id='". $ath->getID() ."'";
		$res=db_query($sql);

		if (!$res || db_numrows($res) < 1) {
			echo '<h2>No Developers Found</h2>';
		} else {
			?>
			<p>Each tracker that you define has separate user lists and user permissions.</p>

			<p>Simply add developers to this tracker, then update their permissions.</p>
			<p><dl>
			<dt><strong>Technicians</strong></dt>
			<dd>can be assigned items</dd>

			<dt><strong>Admins</strong></dt>
			<dd>can make changes to items</dd>
			</dl></p>
			<form action="<?php echo $PHP_SELF.'?group_id='.$group_id.'&atid='.$ath->getID(); ?>" method="post">
			<input type="hidden" name="update_users" value="y" />
			<?php

			$arr=array();
			$arr[]='Delete';
			$arr[]='User Name';
			$arr[]='Permission';

			echo $GLOBALS['HTML']->listTableTop($arr);

			$i=0;
			//
			//	PHP4 allows multi-dimensional arrays to be passed in from form elements
			//
			while ($row_dev = db_fetch_array($res)) {
				print '
				<input type="hidden" name="updateids['.$i.'][0]" value="'.$row_dev['user_id'].'" />
				<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'>
				<td><input type="checkbox" name="deleteids[]" value="'.$row_dev['user_id'].'" /> Delete</td>

				<td>'.$row_dev['realname'].' ( '. $row_dev['user_name'] .' )</td>

				<td><span style="font-size:smaller"><select name="updateids['.$i.'][1]">
				<option value="0"'.(($row_dev['perm_level']==0)?" selected=\"selected\"":"").'>-</option>
				<option value="1"'.(($row_dev['perm_level']==1)?" selected=\"selected\"":"").'>Technician</option>
				<option value="2"'.(($row_dev['perm_level']==2)?" selected=\"selected\"":"").'>Tech & Admin</option>
				<option value="3"'.(($row_dev['perm_level']==3)?" selected=\"selected\"":"").'>Admin Only</option>
				</select></span></td>

				</tr>';
				$i++;
			}
			echo '<tr><td colspan="3" align="center"><input type="submit" name="post_changes" value="Update Developer Permissions" />
			</form></td></tr>';

			echo $GLOBALS['HTML']->listTableBottom();

		}
		?>
		<p>&nbsp;</p>
		<h3>Add These Users:</h3>

		<p>You can pick and choose users for your tracker, or simply add them all by checking "Add All Users".</p>
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
		<input type="submit" name="post_changes" value="Add Users" />&nbsp;<input type="checkbox" name="add_all" /> Add All Users</p>
		</form>
		</div>';

		$ath->footer(array());

	} elseif ($update_canned) {
//
//	FORM TO UPDATE CANNED MESSAGES
//
		$ath->adminHeader(array ('title'=>'Update Canned Responses in: '.$ath->getName()));

		echo "<h1>Update Canned Responses ". $ath->getName() ."</h1>";

		$acr = new ArtifactCanned($ath,$id);
		if (!$acr || !is_object($acr)) {
			$feedback .= 'Unable to create ArtifactCanned Object';
		} elseif ($acr->isError()) {
			$feedback .= $acr->getErrorMessage();
		} else {
			?>
			<p>Creating useful generic messages can save you a lot of time when
			handling common requests.</p>
			<p>
			<form action="<?php echo $PHP_SELF.'?group_id='.$group_id.'&atid='.$ath->getID(); ?>" method="post">
			<input type="hidden" name="update_canned" value="y" />
			<input type="hidden" name="id" value="<?php echo $acr->getID(); ?>" />
			<strong>Title:</strong><br />
			<input type="text" name="title" value="<?php echo $acr->getTitle(); ?>" size="50" maxlength="50" />
			<p>
			<strong>Message Body:</strong><br />
			<textarea name="body" rows="30" cols="65" wrap="hard"><?php echo $acr->getBody(); ?></textarea></p>
			<p>
			<input type="submit" name="post_changes" value="SUBMIT" /></p>
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
		$ath->adminHeader(array('title'=>'Change an Category in: '.$ath->getName()));

		echo '
			<h1>Modify an Category in: '. $ath->getName() .'</h1>';

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
			<strong>Category Name:</strong><br />
			<input type="text" name="name" value="<?php echo $ac->getName(); ?>" /></p>
			<p>
			<strong>Auto-Assign To:</strong><br />
			<?php echo $ath->technicianBox('assign_to',$ac->getAssignee()); ?></p>
			<p>
			<strong><span style="color:red">It is not recommended that you change the artifact
				category name because other things are dependent upon it. When you change
				the category name, all related items will be changed to the new name.</span></strong></p>
			<p>
			<input type="submit" name="post_changes" value="SUBMIT" /></p>
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
		$ath->adminHeader(array('title'=>'Change a Group in: '.$ath->getName(),'pagename'=>'tracker_admin_update_group','titlevals'=>array($ath->getName())));

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
			<strong>Group Name:</strong><br />
			<input type="text" name="name" value="<?php echo $ag->getName(); ?>" /></p>
			<p>
			<strong><span style="color:red">It is not recommended that you change the artifact
				group name because other things are dependent upon it. When you change
				the group name, all related items will be changed to the new name.</span></strong></p>
			<p>
			<input type="submit" name="post_changes" value="SUBMIT" /></p>
			</form></p>
			<?php
		}

		$ath->footer(array());

	} elseif ($update_type) {
//
//	FORM TO UPDATE ARTIFACT TYPES
//
		$ath->adminHeader(array ('title'=>'Tracker Administration: '.$ath->getName(),'pagename'=>'tracker_admin_update_type','titlevals'=>array($ath->getName())));

		?>
		<p>
		<form action="<?php echo $PHP_SELF.'?group_id='.$group_id.'&atid='.$ath->getID(); ?>" method="post">
		<input type="hidden" name="update_type" value="y" />
		<p>
		<strong>Name:</strong> (examples: meeting minutes, test results, RFP Docs)<br />
		<?php if ($ath->getDataType()) {
			echo $ath->getName();
		} else { 
			?>
			<input type="text" name="name" value="<?php echo $ath->getName(); ?>" /></p>
			<?php 
		} 
		?>
		<p>
		<strong>Description:</strong><br />
		<?php if ($ath->getDataType()) {
			echo $ath->getDescription();
		} else {
			?>
			<input type="text" name="description" value="<?php echo $ath->getDescription(); ?>" size="50" /></p>
			<?php 
		} 
		?>
		<p>
		<input type="checkbox" name="is_public" value="1" <?php echo (($ath->isPublic())?'checked="checked"':''); ?> /> <strong>Publicly Available</strong><br />
		<input type="checkbox" name="allow_anon" value="1" <?php echo (($ath->allowsAnon())?'checked="checked"':''); ?> /> <strong>Allow non-logged-in postings</strong><br />
		<input type="checkbox" name="use_resolution" value="1" <?php echo (($ath->useResolution())?'checked="checked"':''); ?> /> <strong>Display the "Resolution" box</strong></p>
		<p>
		<strong>Send email on new submission to address:</strong><br />
		<input type="text" name="email_address" value="<?php echo $ath->getEmailAddress(); ?>" /></p>
		<p>
		<input type="checkbox" name="email_all" value="1" <?php echo (($ath->emailAll())?'checked="checked"':''); ?> /> <strong>Send email on all changes</strong><br /></p>
		<p>
		<strong>Days till considered overdue:</strong><br />
		<input type="text" name="due_period" value="<?php echo ($ath->getDuePeriod() / 86400); ?>" /></p>
		<p>
		<strong>Days till pending tracker items time out:</strong><br />
		<input type="text" name="status_timeout"  value="<?php echo($ath->getStatusTimeout() / 86400); ?>" /></p>
		<p>
		<strong>Free form text for the "submit new item" page:</strong><br />
		<textarea name="submit_instructions" rows="10" cols="55" wrap="hard"><?php echo $ath->getSubmitInstructions(); ?></textarea></p>
		<p>
		<strong>Free form text for the "browse items" page:</strong><br />
		<textarea name="browse_instructions" rows="10" cols="55" wrap="hard"><?php echo $ath->getBrowseInstructions(); ?></textarea></p>
		<p>
		<input type="submit" name="post_changes" value="SUBMIT" /></p>
		</form></p>
		<?php

		$ath->footer(array());

	} else {
//
//  SHOW LINKS TO FEATURES
//

		$ath->adminHeader(array ('title'=>'Tracker Administration: '.$ath->getName(),'pagename'=>'tracker_admin','titlevals'=>array($ath->getName())));

		echo '<p>
			<a href="'.$PHP_SELF.'?group_id='.$group_id.'&amp;atid='.$ath->getID().'&amp;add_cat=1"><strong>Add/Update Categories</strong></a><br />
			Add categories like, \'mail module\',\'gant chart module\',\'cvs\', etc</p>';
		echo '<p>
			<a href="'.$PHP_SELF.'?group_id='.$group_id.'&amp;atid='.$ath->getID().'&amp;add_group=1"><strong>Add/Update Groups</strong></a><br />
			Add groups like, \'v1.2\',\'unsupported\',\'unverified\', etc</p>';
		echo '<p>
			<a href="'.$PHP_SELF.'?group_id='.$group_id.'&amp;atid='.$ath->getID().'&amp;add_canned=1"><strong>Add/Update Canned Responses</strong></a><br />
			Create/Change generic response messages for the tracker.</p>';
		echo '<p>
			<a href="'.$PHP_SELF.'?group_id='.$group_id.'&amp;atid='.$ath->getID().'&amp;update_users=1"><strong>Add/Update Users &amp; Permissions</strong></a><br />
			Add/remove users to/from this tracker.</p>';
		echo '<p>
			<a href="'.$PHP_SELF.'?group_id='.$group_id.'&amp;atid='.$ath->getID().'&amp;update_type=1"><strong>Update preferences</strong></a><br />
			Set up prefs like expiration times, email addresses, etc.</p>';

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
	echo '<strong><a href="/tracker/admin/?group_id='.$group_id.'">Admin</a></strong>';

	if (!$at_arr || count($at_arr) < 1) {
		echo "<h1>No Trackers Found</h1>";
		echo "<p>&nbsp;</p>";
	} else {

		echo '
		<p>Choose a data type and you can set up prefs, categories, groups, users, and permissions.</p>';

		/*
			Put the result set (list of forums for this group) into a column with folders
		*/

		for ($j = 0; $j < count($at_arr); $j++) {
			echo '
			<p><a href="/tracker/admin/?atid='. $at_arr[$j]->getID() .
			'&amp;group_id='.$group_id.'">' .
			html_image("ic/tracker20w.png","20","20",array("border"=>"0")) . ' &nbsp;'.
			$at_arr[$j]->getName() .'</a><br />'.
			$at_arr[$j]->getDescription() .'</p>';
		}
	}

	?>
	<h3>Create a new tracker</h3>

	<p>You can use this system to track virtually any kind of data, with each
	tracker having separate user, group, category, and permission lists. You
	can also easily move items between trackers when needed.</p>

	<p>Trackers are referred to as "Artifact Types" and individual pieces of data
	are "Artifacts". "Bugs" might be an Artifact Type, whiles a bug report would be
	an Artifact. You can create as many Artifact Types as you want, but remember
	you need to set up categories, groups, and permission for each type, which
	can get time-consuming.</p>
	<p>
	<form action="<?php echo $PHP_SELF.'?group_id='.$group_id; ?>" method="post">
	<input type="hidden" name="add_at" value="y" />
	<p>
	<strong>Name:</strong> (examples: meeting minutes, test results, RFP Docs)<br />
	<input type="text" name="name" value=""></p>
	<p>
	<strong>Description:</strong><br />
	<input type="text" name="description" value="" size="50" /></p>
	<p>
	<input type="checkbox" name="is_public" value="1" /> <strong>Publicly Available</strong><br />
	<input type="checkbox" name="allow_anon" value="1" /> <strong>Allow non-logged-in postings</strong><br />
	<input type="checkbox" name="use_resolution" value="1" /> <strong>Display the "Resolution" box</strong></p>
	<p>
	<strong>Send email on new submission to address:</strong><br />
	<input type="text" name="email_address" value="" /></p>
	<p>
	<input type="checkbox" name="email_all" value="1" /> <strong>Send email on all changes</strong><br /></p>
	<p>
	<strong>Days till considered overdue:</strong><br />
	<input type="text" name="due_period" value="30" /></p>
	<p>
	<strong>Days till pending tracker items time out:</strong><br />
	<input type="text" name="status_timeout" value="14" /></p>
	<p>
	<strong>Free form text for the "submit new item" page:</strong><br />
	<textarea name="submit_instructions" rows="10" cols="55" wrap="hard"></textarea></p>
	<p>
	<strong>Free form text for the "browse items" page:</strong><br />
	<textarea name="browse_instructions" rows="10" cols="55" wrap="hard"></textarea></p>
	<p>
	<input type="submit" name="post_changes" value="SUBMIT" /></p>
	</form></p>
	<?php

	echo site_project_footer(array());

} else {

	//browse for group first message
	exit_no_group();

}

?>
