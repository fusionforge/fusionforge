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
	global $Language;
	if (!$member_roles) {
		$sql="SELECT category_id,name FROM people_job_category";
		$member_roles=db_query($sql);
	}
	return html_build_select_box($member_roles,$name,$checked,true,$Language->getText('project_admin_userpermedit','undefined'));
}

// Since there're lot of permissions, and each of them has complex
// HTML rendition (SELECT boxes, etc.), this function is used to reduce
// the background noise.
function render_row($name, $val, $i) {
	print '
	<tr '.$GLOBALS['HTML']->boxGetAltRowStyle($i).'>
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
				$Language->getText('project_admin_userpermedit','invalid_user'),
				$Language->getText('project_admin_userpermedit','user_does_not_exist')
			);
		}

		if (!$group->addUser($u->getUnixName())) {
			exit_error('Error', $group->getErrorMessage());
		} else {
			$feedback = $Language->getText('project_admin_userpermedit','user_added').' <br />';
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
		//$cvs_flags = 1;

		//call to control function in the $Group object
		if ($group->updateUser($user_id, $admin_flags, $forum_flags, $project_flags, 
				$doc_flags, $cvs_flags, $release_flags, $member_role, $artifact_flags)) {

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
				$feedback = $Language->getText('project_admin_userpermedit','permissions_updated').' <br />';
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

project_admin_header(array('title'=>$Language->getText('project_admin_userpermedit','title'),'group'=>$group_id,'pagename'=>'project_admin_userpermedit','sectionvals'=>array(group_getname($group_id))));

$u =& user_get_object($user_id);
if ($u && is_object($u)){
	print $Language->getText('project_admin_userpermedit','editing_permissions',array($u->getUnixName(), $u->getRealName())). ".</p>
<p>" ;
}

// Show description of roles/permissions
echo permissions_blurb();

$res_dev = db_query("
	SELECT * 
	FROM user_group 
	WHERE group_id='$group_id'
	AND user_id='$user_id'
");

if (!$res_dev || db_numrows($res_dev) < 1) {
	echo '<h2>'.$Language->getText('project_admin_userpermedit','developer_not_found').'</h2>';
	echo db_error();
} else {
	echo '
	<p>
	<form action="'.$PHP_SELF.'?group_id='.$group_id.'&user_id='. $user_id .'" method="post">';

	$row_dev = db_fetch_array($res_dev);

	$arr=array();
	$arr[]=$Language->getText('project_admin_userpermedit','property');
	$arr[]=$Language->getText('project_admin_userpermedit','value');

	echo $GLOBALS['HTML']->listTableTop($arr);

	render_row(
		$Language->getText('project_admin_userpermedit','project_role'),
		member_role_box('member_role',$row_dev['member_role']),
		$i++
	);

	render_row(
		$Language->getText('project_admin_userpermedit','project_admin'),
		html_build_checkbox('admin_flags', 'A', stristr($row_dev['admin_flags'],'A')),
		$i++
	);

	render_row(
		$Language->getText('project_admin_userpermedit','release_technician'),
		html_build_checkbox('release_flags', '1', $row_dev['release_flags']==1),
		$i++
	);

	render_row(
		'CVS Commit',
		html_build_checkbox('cvs_flags', '1', $row_dev['cvs_flags']==1),
		$i++
	);

	render_row(
		$Language->getText('project_admin_userpermedit','tracker_manager'),
		html_build_select_box_from_arrays(
			array(0,2),
			array('-','Admin'),
			'artifact_flags',$row_dev['artifact_flags'],false
		),
		$i++
	);

	$tracker_ids   = array(0,1,2,3);
	$tracker_texts = array('-',
		$Language->getText('project_admin_userpermedit','technician'),
		$Language->getText('project_admin_userpermedit','admin_tech'),
		$Language->getText('project_admin_userpermedit','admin'));

	render_row(
		$Language->getText('project_admin_userpermedit','pm'),
		html_build_select_box_from_arrays(
			$tracker_ids,
			$tracker_texts,
			'project_flags',$row_dev['project_flags'],false
		),
		$i++
	);

	render_row(
		$Language->getText('project_admin_userpermedit','forums'),
		html_build_select_box_from_arrays(
			array(0,2),
			array('-',$Language->getText('project_admin_userpermedit','moderator')),
			'forum_flags',$row_dev['forum_flags'],false
		),
		$i++
	);

	render_row(
		$Language->getText('project_admin_userpermedit','docman'),
		html_build_select_box_from_arrays(
			array(0,1),
			array('-',$Language->getText('project_admin_userpermedit','editor')),
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
		<input type="hidden" name="updateperms['.$i.'][0]" value="'. db_result($res,$i,'group_artifact_id').'" />
		<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'>

		<td>'. db_result($res,$i,'name') .'</td>

		<td><select name="updateperms['.$i.'][1]" style="font-size:smaller">
		<option value="0"'.((db_result($res,$i,'perm_level')==0)?' selected="selected"':'').'>-</option>
		<option value="1"'.((db_result($res,$i,'perm_level')==1)?' selected="selected"':'').'>'.$Language->getText('project_admin_userpermedit','technician').'</option>
		<option value="2"'.((db_result($res,$i,'perm_level')==2)?' selected="selected"':'').'>'.$Language->getText('project_admin_userpermedit','admin_tech').'</option>
		<option value="3"'.((db_result($res,$i,'perm_level')==3)?' selected="selected"':'').'>'.$Language->getText('project_admin_userpermedit','admin_only').'</option>
		</select>  <input type="checkbox" name="deletefrom[]" value="'. db_result($res,$i,'group_artifact_id').'" /> '.$Language->getText('project_admin_userpermedit','remove').'</td>

		</tr>';
	}

	?>

	<tr><td colspan="2"><p align="center">
		<input type="submit" name="submit" value="<?php echo $Language->getText('project_admin_userpermedit','update_permissions') ?>" />
		<input type="reset" value="<?php echo $Language->getText('project_admin_userpermedit','reset_changes') ?>" />
		</form></p>
	</td></tr>

	<?php echo $GLOBALS['HTML']->listTableBottom(); ?>

	<p>&nbsp;</p>
	<?php echo $Language->getText('project_admin_userpermedit','tracker_info') ?>
	<div align="center">
	<form action="<?php echo $PHP_SELF.'?group_id='.$group_id.'&user_id='.$user_id ?>" method="post">
	<input type="hidden" name="addtotracker" value="y" />
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
	echo '<p>
	<input type="submit" name="submit" value="'.$Language->getText('project_admin_userpermedit','add_to_tracker').'" />&nbsp;<input type="checkbox" name="add_all" /> '.$Language->getText('project_admin_userpermedit','add_to_all').'</p>
	</form></div>';

}

project_admin_footer(array());

?>
