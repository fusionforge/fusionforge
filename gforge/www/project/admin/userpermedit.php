<?php
/**
  *
  * Project Admin page to edit permissions for the specific group member
  *
  * This page is linked from userperms.php and from forms to add users
  * to group (located on Project/Foundry Admin main pages).
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */


require_once('pre.php');	
require_once('www/project/admin/project_admin_utils.php');
require_once('common/tracker/ArtifactType.class');

session_require(array('group'=>$group_id,'admin_flags'=>'A'));

// Builds role selection box with given selected item
function member_role_box($name, $checked) {
	global $member_roles;
	if (!$member_roles) {
		$sql="SELECT category_id,name FROM people_job_category";
		$member_roles=db_query($sql);
	}
	return html_build_select_box($member_roles,$name,$checked,true,'Undefined');
}

// Since there're lot of permissions, and each of them has complex
// HTML rendition (SELECT boxes, etc.), this function is used to reduce
// the background noise.
function render_row($name, $val, $i) {
	print '
	<tr bgcolor="'.html_get_alt_row_color($i).'">
	<td>'.$name.'</td>
	<td>'.$val.'</td></tr>
	';
}

$group =& group_get_object($group_id);
exit_assert_object($group, 'Group');

// ########################### form submission, make updates

// Netscape allows to submit single-field form by pressing
// Return in the field. In this case, it won't set value for
// submit button
if ($submit || $form_unix_name) {


	if ($GLOBALS['func']=='adduser') {

		/*
			We came here from Add User page, need to add user
			and fall thru to show permissions for one
		*/

		$u =& user_get_object_by_name($form_unix_name);
		if (!$u || !is_object($u)){
			exit_error(
				'Invalid user',
				'User does not exist.'
			);
		}

		if (!$group->addUser($u->getUnixName())) {
			exit_error('Error', $u->getErrorMessage());
		} else {
			$feedback = ' User Added Successfully<br>';
		}

		$user_id = $u->getID();

	} elseif ($addtotracker) {

		$u =& user_get_object($user_id);
		exit_assert_object($u, 'User');

		//
		//  if "add all" option, get list of ArtifactTypes
		//  that this user is not already a member of
		//
		if ($add_all) {
			$sql="SELECT group_artifact_id 
			FROM artifact_group_list
			WHERE group_id='$group_id' 
			AND NOT EXISTS (SELECT user_id 
			FROM artifact_perm
			WHERE artifact_perm.group_artifact_id=artifact_group_list.group_artifact_id 
			AND user_id='".$u->getID()."')";

			$addtoids = util_result_column_to_array(db_query($sql));
		}

		//
		//	Now take the array of ids and add this user to them
		//
		$count = count($addtoids);

		for ($i=0; $i<$count; $i++) {
			$ath = new ArtifactType($group,$addtoids[$i]);

			$ath->addUser($u->getID());
			if ($ath->isError()) {
				$feedback .= $addtoids[$i] .': '. $ath->getErrorMessage();
				$was_error = true;
			}
		}

	} else {

		/*
			Else, we are updating user's permissions
		*/

		$u =& user_get_object($user_id);
		exit_assert_object($u, 'User');

		// XXX: remove when CVS roles will be activated!
		$cvs_flags = 1;

		//call to control function in the $Group object
		if ($group->updateUser($user_id,
			$admin_flags, $bug_flags, $forum_flags,
			$project_flags, $patch_flags, $support_flags,
			$doc_flags, $cvs_flags, $release_flags,
			$member_role, $artifact_flags)) {

			group_add_history ('Changed Permissions for',$u->getUnixName(),$group_id);


			//  
			//  Delete the checked ids
			//

			//	keep an assoc array of artifacts this user 
			//	was removed from, so we don't then try to update
			//	those artifact type perms in the next step
			$del_arr=array();

			$count=count($deletefrom);
			for ($i=0; $i<$count; $i++) {
				$del_arr["$deletefrom[$i]"]=true;
				$ath = new ArtifactType($group,$deletefrom[$i]);
				$ath->deleteUser($user_id);
				if ($ath->isError()) {
					$feedback .= $deletefrom[$i] .': '. $ath->getErrorMessage();
					$was_error=true;
				}
			}

			//
			//  Handle the 2-D array of group_artifact_id/permission level
			//
			$count=count($updateperms);

			for ($i=0; $i<$count; $i++) {
				//
				//	quick check of that assoc array to prevent 
				//	updating of perms that don't exist anymore
				//
				if (!$del_arr["$updateperms[$i][0]"]) {
					$ath = new ArtifactType($group,$updateperms[$i][0]);
					$ath->updateUser($user_id,$updateperms[$i][1]);
					if ($ath->isError()) {
						$feedback .= $updateperms[$i][0] .': '. $ath->getErrorMessage();
						$was_error=true;
					} 
				}
			}
			
			//if no errors occurred, show just one feedback message
			//instead of the coredump of messages;
			if (!$was_error) {
				$feedback = ' Permissions Updated<br>';
			}
		} else {
			$feedback .= $group->getErrorMessage();
		}

	}

} else {
	//
	//  Set up this user's object
	//
	$u =& user_get_object($user_id);
	if (!$u || !is_object($u)) {
		exit_error('Error', 'Error creating user object');
	} else if ($u->isError()) {
		exit_error('Error', $u->getErrorMessage());
	}
}

project_admin_header(array('title'=>'Project Developer Permissions','group'=>$group_id,'pagename'=>'project_admin_userpermedit','sectionvals'=>array(group_getname($group_id))));

// Show description of roles/permissions
echo permissions_blurb();

$res_dev = db_query("
	SELECT * 
	FROM user_group 
	WHERE group_id='$group_id'
	AND user_id='$user_id'
");

if (!$res_dev || db_numrows($res_dev) < 1) {
	echo '<H2>Developer Not Found In This Group</H2>';
	echo db_error();
} else {
	echo '
	<P>
	<FORM action="'.$PHP_SELF.'?group_id='.$group_id.'&user_id='. $user_id .'" method="post">';

	$row_dev = db_fetch_array($res_dev);

	$arr=array();
	$arr[]='Property';
	$arr[]='Value';

	echo html_build_list_table_top($arr);

	render_row(
		'Project role',
		member_role_box('member_role',$row_dev['member_role']),
		$i++
	);

	render_row(
		'Project Admin',
		html_build_checkbox('admin_flags', 'A', stristr($row_dev['admin_flags'],'A')),
		$i++
	);

	render_row(
		'Release Technician',
		html_build_checkbox('release_flags', '1', $row_dev['release_flags']==1),
		$i++
	);

/*
	render_row(
		'CVS Access',
		html_build_select_box_from_arrays(
			array(0,1,2),
			array('Read-only','Write','Admin'),
			'cvs_flags',$row_dev['cvs_flags'],false
		),
		$i++
	);
*/

	render_row(
		'Tracker Manager',
		html_build_select_box_from_arrays(
			array(0,2),
			array('-','Admin'),
			'artifact_flags',$row_dev['artifact_flags'],false
		),
		$i++
	);

	$tracker_ids   = array(0,1,2,3);
	$tracker_texts = array('-','Technician','Admin & Tech','Admin');

	render_row(
		'Project/Task Manager',
		html_build_select_box_from_arrays(
			$tracker_ids,
			$tracker_texts,
			'project_flags',$row_dev['project_flags'],false
		),
		$i++
	);

	render_row(
		'Forums',
		html_build_select_box_from_arrays(
			array(0,2),
			array('-','Moderator'),
			'forum_flags',$row_dev['forum_flags'],false
		),
		$i++
	);

	render_row(
		'Documentation Manager',
		html_build_select_box_from_arrays(
			array(0,1),
			array('-','Editor'),
			'doc_flags',$row_dev['doc_flags'],false
		),
		$i++
	);

	//
	//	Get the list of permissions that this user has 
	//	for ArtifactTypes in this Group
	//
	$res = db_query("SELECT * FROM artifactperm_artgrouplist_vw 
		WHERE user_id='$user_id' 
		AND group_id='$group_id'");

	$rows=db_numrows($res);

	// Iterate over all trackers of the group
	for ($i=0; $i<$rows; $i++) {
		print '
		<INPUT TYPE="HIDDEN" NAME="updateperms['.$i.'][0]" VALUE="'. db_result($res,$i,'group_artifact_id').'">
		<TR BGCOLOR="'. html_get_alt_row_color($i) .'">

		<TD>'. db_result($res,$i,'name') .'</TD>

		<TD><FONT size="-1"><SELECT name="updateperms['.$i.'][1]">
		<OPTION value="0"'.((db_result($res,$i,'perm_level')==0)?' selected':'').'>-
		<OPTION value="1"'.((db_result($res,$i,'perm_level')==1)?' selected':'').'>Technician
		<OPTION value="2"'.((db_result($res,$i,'perm_level')==2)?' selected':'').'>Tech & Admin
		<OPTION value="3"'.((db_result($res,$i,'perm_level')==3)?' selected':'').'>Admin Only
		</SELECT></FONT>  <INPUT TYPE="CHECKBOX" NAME="deletefrom[]" VALUE="'. db_result($res,$i,'group_artifact_id').'"> Remove</TD>

		</TR>';
	}

	?>

	<TR><TD COLSPAN=2><p align="center">
		<INPUT type="submit" name="submit" value="Update Developer Permissions">
		<INPUT type="reset" value="Reset Changes">
		</FORM>
	</TD></TR>

	</TABLE>

	<P> 
	<h3>Add User To These Trackers:</H3>
	<P>
	You can pick and choose which trackers this user has any privileges in, 
	or simply add the user to all trackers by checking "Add To All".
	<P> 
	<CENTER>
	<FORM action="<?php echo $PHP_SELF.'?group_id='.$group_id.'&user_id='.$user_id ?>" method="post">
	<INPUT TYPE="HIDDEN" NAME="addtotracker" VALUE="y">
	<?php
	$sql="SELECT group_artifact_id,name 
		FROM artifact_group_list
		WHERE group_id='$group_id' 
		AND NOT EXISTS (SELECT user_id 
		FROM artifact_perm
		WHERE artifact_perm.group_artifact_id=artifact_group_list.group_artifact_id 
		AND user_id='$user_id')";

	$res=db_query($sql);
	echo db_error();
	echo html_build_multiple_select_box ($res,'addtoids[]',array(),8,false);
	echo '<P>
	<INPUT type="submit" name="submit" value="Add To Tracker">&nbsp;<INPUT type="checkbox" name="add_all"> Add To All
	</FORM>
	</CENTER>';

}

project_admin_footer(array());

?>
