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

		echo "<H1>Add Categories to: ". $ath->getName() ."</H1>";

		/*
			List of possible categories for this ArtifactType
		*/
		$result=$ath->getCategories();
		echo "<P>";
		$rows=db_numrows($result);
		if ($result && $rows > 0) {
			$title_arr=array();
			$title_arr[]='ID';
			$title_arr[]='Title';
			
			echo html_build_list_table_top ($title_arr);
			
			for ($i=0; $i < $rows; $i++) {
				echo '<TR BGCOLOR="'. html_get_alt_row_color($i) .'">'.
					'<TD>'.db_result($result, $i, 'id').'</TD>'.
					'<TD><A HREF="'.$PHP_SELF.'?update_cat=1&id='.
						db_result($result, $i, 'id').'&group_id='.$group_id.'&atid='. $ath->getID() .'">'.
						db_result($result, $i, 'category_name').'</A></TD></TR>';
			}		   
			echo '</TABLE>';
		} else {
			echo "\n<H1>No categories defined</H1>";
		}
		?>
		<P>
		<FORM ACTION="<?php echo $PHP_SELF.'?group_id='.$group_id.'&atid='.$ath->getID(); ?>" METHOD="POST">
		<INPUT TYPE="HIDDEN" NAME="add_cat" VALUE="y">
		<B>New Category Name:</B><BR>
		<INPUT TYPE="TEXT" NAME="name" VALUE="" SIZE="15" MAXLENGTH="30"><BR>
		<P>
		<B>Auto-Assign To:</B><BR>
		<?php echo $ath->technicianBox('assign_to'); ?>
		<P>
		<B><FONT COLOR="RED">Once you add a category, it cannot be deleted</FONT></B>
		<P>
		<INPUT TYPE="SUBMIT" NAME="post_changes" VALUE="SUBMIT">
		</FORM>
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
		echo "<P>";
		$rows=db_numrows($result);
		if ($result && $rows > 0) {
			$title_arr=array();
			$title_arr[]='ID';
			$title_arr[]='Title';
			
			echo html_build_list_table_top ($title_arr);
			
			for ($i=0; $i < $rows; $i++) {
				echo '<TR BGCOLOR="'. html_get_alt_row_color($i) .'">'.
					'<TD>'.db_result($result, $i, 'id').'</TD>'.
					'<TD><A HREF="'.$PHP_SELF.'?update_group=1&id='.
						db_result($result, $i, 'id').'&group_id='.$group_id.'&atid='. $ath->getID() .'">'.
						db_result($result, $i, 'group_name').'</A></TD></TR>';
			}		   
			echo '</TABLE>';
		} else {
			echo "\n<H1>No groups defined</H1>";
		}
		?>
		<P>
		<FORM ACTION="<?php echo $PHP_SELF.'?group_id='.$group_id.'&atid='.$ath->getID(); ?>" METHOD="POST">
		<INPUT TYPE="HIDDEN" NAME="add_group" VALUE="y">
		<B>New group Name:</B><BR>
		<INPUT TYPE="TEXT" NAME="name" VALUE="" SIZE="15" MAXLENGTH="30"><BR>
		<P>
		<B><FONT COLOR="RED">Once you add a group, it cannot be deleted</FONT></B>
		<P>
		<INPUT TYPE="SUBMIT" NAME="post_changes" VALUE="SUBMIT">
		</FORM>
		<?php

		$ath->footer(array());

	} elseif ($add_canned) {
//
//  FORM TO ADD CANNED RESPONSES
//
		$ath->adminHeader(array ('title'=>'Add/Change Canned Responses to: '.$ath->getName()));

		echo "<H1>Add Canned Responses to: ". $ath->getName() ."</H1>";

		/*
			List of existing canned responses
		*/
		$result=$ath->getCannedResponses();
		$rows=db_numrows($result);
		echo "<P>";

		if ($result && $rows > 0) {
			//code to show existing responses and link to update page
			echo '
			<H2>Existing Responses:</H2>
			<P>';
			$title_arr=array();
			$title_arr[]='ID';
			$title_arr[]='Title';

			echo html_build_list_table_top ($title_arr);

			for ($i=0; $i < $rows; $i++) {
				echo '<TR BGCOLOR="'. html_get_alt_row_color($i) .'">'.
					'<TD>'.db_result($result, $i, 'id').'</TD>'.
					'<TD><A HREF="'.$PHP_SELF.'?update_canned=1&id='.
						db_result($result, $i, 'id').'&group_id='.$group_id.'&atid='. $ath->getID() .'">'.
						db_result($result, $i, 'title').'</A></TD></TR>';
			}
			echo '</TABLE>';

		} else {
			echo "\n<H1>No responses set up in this group</H1>";
		}
		?>
		<P>
		Creating useful generic messages can save you a lot of time when 
		handling common artifact requests.
		<P>
		<FORM ACTION="<?php echo $PHP_SELF.'?group_id='.$group_id.'&atid='.$ath->getID(); ?>" METHOD="POST">
		<INPUT TYPE="HIDDEN" NAME="add_canned" VALUE="y">
		<b>Title:</b><BR>
		<INPUT TYPE="TEXT" NAME="title" VALUE="" SIZE="50" MAXLENGTH="50">
		<P>
		<B>Message Body:</B><BR>
		<TEXTAREA NAME="body" ROWS="30" COLS="65" WRAP="HARD"></TEXTAREA>
		<P>
		<INPUT TYPE="SUBMIT" NAME="post_changes" VALUE="SUBMIT">
		</FORM>
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
			echo '<H2>No Developers Found</H2>';
		} else {
			?>
			<P>
			Each tracker that you define has separate user lists and user permissions.
			<P>
			Simply add developers to this tracker, then update their permissions.
			<P>
			<dt><B>Technicians</B></dt>
			<dd>can be assigned items</dd>

			<dt><B>Admins</B></dt>
			<dd>can make changes to items</dd>

			<FORM action="<?php echo $PHP_SELF.'?group_id='.$group_id.'&atid='.$ath->getID(); ?>" method="post">
			<INPUT TYPE="HIDDEN" NAME="update_users" VALUE="y">
			<?php

			$arr=array();
			$arr[]='Delete';
			$arr[]='User Name';
			$arr[]='Permission';

			echo html_build_list_table_top($arr);

			$i=0;
			//
			//	PHP4 allows multi-dimensional arrays to be passed in from form elements
			//
			while ($row_dev = db_fetch_array($res)) {
				print '
				<INPUT TYPE="HIDDEN" NAME="updateids['.$i.'][0]" VALUE="'.$row_dev['user_id'].'">
				<TR BGCOLOR="'. html_get_alt_row_color($i) .'">
				<TD><INPUT TYPE="CHECKBOX" NAME="deleteids[]" VALUE="'.$row_dev['user_id'].'"> Delete</TD>

				<TD>'.$row_dev['realname'].' ( '. $row_dev['user_name'] .' )</TD>

				<TD><FONT size="-1"><SELECT name="updateids['.$i.'][1]">
				<OPTION value="0"'.(($row_dev['perm_level']==0)?" selected":"").'>-
				<OPTION value="1"'.(($row_dev['perm_level']==1)?" selected":"").'>Technician
				<OPTION value="2"'.(($row_dev['perm_level']==2)?" selected":"").'>Tech & Admin
				<OPTION value="3"'.(($row_dev['perm_level']==3)?" selected":"").'>Admin Only
				</SELECT></FONT></TD>

				</TR>';
				$i++;
			}
			echo '<TR><TD COLSPAN=3 ALIGN=MIDDLE><INPUT type="submit" name="post_changes" value="Update Developer Permissions">
			</FORM></TD></TR>';
			echo '</TABLE>';
		}
		?>
		<P>
		<h3>Add These Users:</H3>
		<P>
		You can pick and choose users for your tracker, or simply add them all by checking "Add All Users".
		<P>
		<CENTER>
		<FORM action="<?php echo $PHP_SELF.'?group_id='.$group_id.'&atid='.$ath->getID(); ?>" method="post">
		<INPUT TYPE="HIDDEN" NAME="add_users" VALUE="y">
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
		echo '<P>
		<INPUT type="submit" name="post_changes" value="Add Users">&nbsp;<INPUT type="checkbox" name="add_all"> Add All Users
		</FORM>
		</CENTER>';

		$ath->footer(array());

	} elseif ($update_canned) {
//
//	FORM TO UPDATE CANNED MESSAGES
//
		$ath->adminHeader(array ('title'=>'Update Canned Responses in: '.$ath->getName()));

		echo "<H1>Update Canned Responses ". $ath->getName() ."</H1>";

		$acr = new ArtifactCanned($ath,$id);
		if (!$acr || !is_object($acr)) {
			$feedback .= 'Unable to create ArtifactCanned Object';
		} elseif ($acr->isError()) {
			$feedback .= $acr->getErrorMessage();
		} else {
			?>
			<P>
			Creating useful generic messages can save you a lot of time when
			handling common requests.
			<P>
			<FORM ACTION="<?php echo $PHP_SELF.'?group_id='.$group_id.'&atid='.$ath->getID(); ?>" METHOD="POST">
			<INPUT TYPE="HIDDEN" NAME="update_canned" VALUE="y">
			<INPUT TYPE="HIDDEN" NAME="id" VALUE="<?php echo $acr->getID(); ?>">
			<b>Title:</b><BR>
			<INPUT TYPE="TEXT" NAME="title" VALUE="<?php echo $acr->getTitle(); ?>" SIZE="50" MAXLENGTH="50">
			<P>
			<B>Message Body:</B><BR>
			<TEXTAREA NAME="body" ROWS="30" COLS="65" WRAP="HARD"><?php echo $acr->getBody(); ?></TEXTAREA>
			<P>
			<INPUT TYPE="SUBMIT" NAME="post_changes" VALUE="SUBMIT">
			</FORM>
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
			<H1>Modify an Category in: '. $ath->getName() .'</H1>';

		$ac = new ArtifactCategory($ath,$id);
		if (!$ac || !is_object($ac)) {
			$feedback .= 'Unable to create ArtifactCategory Object';
		} elseif ($ac->isError()) {
			$feedback .= $ac->getErrorMessage();
		} else {
			?>
			<P>
			<FORM ACTION="<?php echo $PHP_SELF.'?group_id='.$group_id.'&atid='.$ath->getID(); ?>" METHOD="POST">
			<INPUT TYPE="HIDDEN" NAME="update_cat" VALUE="y">
			<INPUT TYPE="HIDDEN" NAME="id" VALUE="<?php echo $ac->getID(); ?>">
			<P>
			<B>Category Name:</B><BR>
			<INPUT TYPE="TEXT" NAME="name" VALUE="<?php echo $ac->getName(); ?>">
			<P>
			<B>Auto-Assign To:</B><BR>
			<?php echo $ath->technicianBox('assign_to',$ac->getAssignee()); ?>
			<P>
			<B><FONT COLOR="RED">It is not recommended that you change the artifact 
				category name because other things are dependent upon it. When you change 
				the category name, all related items will be changed to the new name.</FONT></B>
			<P>
			<INPUT TYPE="SUBMIT" NAME="post_changes" VALUE="SUBMIT">
			</FORM>
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
			<P>
			<FORM ACTION="<?php echo $PHP_SELF.'?group_id='.$group_id.'&atid='.$ath->getID(); ?>" METHOD="POST">
			<INPUT TYPE="HIDDEN" NAME="update_group" VALUE="y">
			<INPUT TYPE="HIDDEN" NAME="id" VALUE="<?php echo $ag->getID(); ?>">
			<P>
			<B>Group Name:</B><BR>
			<INPUT TYPE="TEXT" NAME="name" VALUE="<?php echo $ag->getName(); ?>">
			<P>
			<B><FONT COLOR="RED">It is not recommended that you change the artifact 
				group name because other things are dependent upon it. When you change 
				the group name, all related items will be changed to the new name.</FONT></B>
			<P>
			<INPUT TYPE="SUBMIT" NAME="post_changes" VALUE="SUBMIT">
			</FORM>
			<?php
		}

		$ath->footer(array());

	} elseif ($update_type) {
//
//	FORM TO UPDATE ARTIFACT TYPES
//
		$ath->adminHeader(array ('title'=>'Tracker Administration: '.$ath->getName(),'pagename'=>'tracker_admin_update_type','titlevals'=>array($ath->getName())));

		?>
		<P>
		<FORM ACTION="<?php echo $PHP_SELF.'?group_id='.$group_id.'&atid='.$ath->getID(); ?>" METHOD="POST">
		<INPUT TYPE="HIDDEN" NAME="update_type" VALUE="y">
		<P>
		<B>Name:</B> (examples: meeting minutes, test results, RFP Docs)<BR>
		<?php if ($ath->getDataType()) {
			echo $ath->getName();
		} else { 
			?>
			<INPUT TYPE="TEXT" NAME="name" VALUE="<?php echo $ath->getName(); ?>">
			<?php 
		} 
		?>
		<P>
		<B>Description:</B><BR>
		<?php if ($ath->getDataType()) {
			echo $ath->getDescription();
		} else {
			?>
			<INPUT TYPE="TEXT" NAME="description" VALUE="<?php echo $ath->getDescription(); ?>" SIZE="50">
			<?php 
		} 
		?>
		<P>
		<INPUT TYPE=CHECKBOX NAME="is_public" VALUE="1" <?php echo (($ath->isPublic())?'CHECKED':''); ?>> <B>Publicly Available</B><BR>
		<INPUT TYPE=CHECKBOX NAME="allow_anon" VALUE="1" <?php echo (($ath->allowsAnon())?'CHECKED':''); ?>> <B>Allow non-logged-in postings</B><BR>
		<INPUT TYPE=CHECKBOX NAME="use_resolution" VALUE="1" <?php echo (($ath->useResolution())?'CHECKED':''); ?>> <B>Display the "Resolution" box</B>
		<P>
		<B>Send email on new submission to address:</B><BR>
		<INPUT TYPE="TEXT" NAME="email_address" VALUE="<?php echo $ath->getEmailAddress(); ?>">
		<P>
		<INPUT TYPE=CHECKBOX NAME="email_all" VALUE="1" <?php echo (($ath->emailAll())?'CHECKED':''); ?>> <B>Send email on all changes</B><BR>
		<P>
		<B>Days till considered overdue:</B><BR>
		<INPUT TYPE="TEXT" NAME="due_period" VALUE="<?php echo ($ath->getDuePeriod() / 86400); ?>">
		<P>
		<B>Days till pending tracker items time out:</B><BR>
		<INPUT TYPE="TEXT" NAME="status_timeout"  VALUE="<?php echo($ath->getStatusTimeout() / 86400); ?>">
		<P>
		<B>Free form text for the "submit new item" page:</B><BR>
		<TEXTAREA NAME="submit_instructions" ROWS="10" COLS="55" WRAP="HARD"><?php echo $ath->getSubmitInstructions(); ?></TEXTAREA>
		<P>
		<B>Free form text for the "browse items" page:</B><BR>
		<TEXTAREA NAME="browse_instructions" ROWS="10" COLS="55" WRAP="HARD"><?php echo $ath->getBrowseInstructions(); ?></TEXTAREA>
		<P>
		<INPUT TYPE="SUBMIT" NAME="post_changes" VALUE="SUBMIT">
		</FORM>
		<?php

		$ath->footer(array());

	} else {
//
//  SHOW LINKS TO FEATURES
//

		$ath->adminHeader(array ('title'=>'Tracker Administration: '.$ath->getName(),'pagename'=>'tracker_admin','titlevals'=>array($ath->getName())));

		echo '<P>
			<A HREF="'.$PHP_SELF.'?group_id='.$group_id.'&atid='.$ath->getID().'&add_cat=1"><B>Add/Update Categories</B></A><BR>
			Add categories like, \'mail module\',\'gant chart module\',\'cvs\', etc<P>';
		echo '<P>
			<A HREF="'.$PHP_SELF.'?group_id='.$group_id.'&atid='.$ath->getID().'&add_group=1"><B>Add/Update Groups</B></A><BR>
			Add groups like, \'v1.2\',\'unsupported\',\'unverified\', etc<P>';
		echo '<P>
			<A HREF="'.$PHP_SELF.'?group_id='.$group_id.'&atid='.$ath->getID().'&add_canned=1"><B>Add/Update Canned Responses</B></A><BR>
			Create/Change generic response messages for the tracker.<P>';
		echo '<P>
			<A HREF="'.$PHP_SELF.'?group_id='.$group_id.'&atid='.$ath->getID().'&update_users=1"><B>Add/Update Users &amp; Permissions</B></A><BR>
			Add/remove users to/from this tracker.<P>';
		echo '<P>
			<A HREF="'.$PHP_SELF.'?group_id='.$group_id.'&atid='.$ath->getID().'&update_type=1"><B>Update preferences</B></A><BR>
			Set up prefs like expiration times, email addresses, etc.<P>';

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

	$sql="SELECT * FROM artifact_group_list WHERE group_id='$group_id' ORDER BY group_artifact_id";

	//required params for site_project_header();
	$params['group']=$group_id;
	$params['toptab']='tracker';
	$params['pagename']='tracker_admin_choose';
	
	echo site_project_header($params);
	echo '<B><A HREF="/tracker/admin/?group_id='.$group_id.'">Admin</A></B><P>';

	$result = db_query ($sql);
	$rows = db_numrows($result);
	if (!$result || $rows < 1) {
		echo "<H1>No Trackers Found</H1>";
		echo "<P>";
	} else {

		echo '
		<P>
		Choose a data type and you can set up prefs, categories, groups, users, and permissions.
		<P>';

		/*
			Put the result set (list of forums for this group) into a column with folders
		*/

		for ($j = 0; $j < $rows; $j++) {
			echo '
			<A HREF="/tracker/admin/?atid='.db_result($result, $j, 'group_artifact_id').
			'&group_id='.$group_id.'">' .
			html_image("images/ic/index.png","15","13",array("BORDER"=>"0")) . ' &nbsp;'.
			db_result($result, $j, 'name').'</A><BR>'.
			db_result($result, $j, 'description').'<P>';
		}
	}

	?>
	<H3>Create a new tracker</H3>
	<P>
	You can use this system to track virtually any kind of data, with each 
	tracker having separate user, group, category, and permission lists. You 
	can also easily move items between trackers when needed.
	<P>
	Trackers are referred to as "Artifact Types" and individual pieces of data
	are "Artifacts". "Bugs" might be an Artifact Type, whiles a bug report would be 
	an Artifact. You can create as many Artifact Types as you want, but remember 
	you need to set up categories, groups, and permission for each type, which 
	can get time-consuming.
	<P>
	<FORM ACTION="<?php echo $PHP_SELF.'?group_id='.$group_id; ?>" METHOD="POST">
	<INPUT TYPE="HIDDEN" NAME="add_at" VALUE="y">
	<P>
	<B>Name:</B> (examples: meeting minutes, test results, RFP Docs)<BR>
	<INPUT TYPE="TEXT" NAME="name" VALUE="">
	<P>
	<B>Description:</B><BR>
	<INPUT TYPE="TEXT" NAME="description" VALUE="" SIZE="50">
	<P>
	<INPUT TYPE=CHECKBOX NAME="is_public" VALUE="1"> <B>Publicly Available</B><BR>
	<INPUT TYPE=CHECKBOX NAME="allow_anon" VALUE="1"> <B>Allow non-logged-in postings</B><BR>
	<INPUT TYPE=CHECKBOX NAME="use_resolution" VALUE="1"> <B>Display the "Resolution" box</B>
	<P>
	<B>Send email on new submission to address:</B><BR>
	<INPUT TYPE="TEXT" NAME="email_address" VALUE="">
	<P>
	<INPUT TYPE=CHECKBOX NAME="email_all" VALUE="1"> <B>Send email on all changes</B><BR>
	<P>
	<B>Days till considered overdue:</B><BR>
	<INPUT TYPE="TEXT" NAME="due_period" VALUE="30">
	<P>
	<B>Days till pending tracker items time out:</B><BR>
	<INPUT TYPE="TEXT" NAME="status_timeout" VALUE="14">
	<P>
	<B>Free form text for the "submit new item" page:</B><BR>
	<TEXTAREA NAME="submit_instructions" ROWS="10" COLS="55" WRAP="HARD"></TEXTAREA>
	<P>
	<B>Free form text for the "browse items" page:</B><BR>
	<TEXTAREA NAME="browse_instructions" ROWS="10" COLS="55" WRAP="HARD"></TEXTAREA>
	<P>
	<INPUT TYPE="SUBMIT" NAME="post_changes" VALUE="SUBMIT">
	</FORM>
	<?php

	echo site_project_footer(array());

} else {

	//browse for group first message
	exit_no_group();

}

?>
