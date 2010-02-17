<?php
/**
 * Project Admin Main Page
 *
 * This page contains administrative information for the project as well
 * as allows to manage it. This page should be accessible to all project
 * members, but only admins may perform most functions.
 *
 * Copyright 2004 GForge, LLC
 *
 * @author Tim Perdue tim@gforge.org
 *
 * This file is part of GForge.
 *
 * GForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

require_once('../../env.inc.php');
require_once $gfwww.'include/pre.php';
require_once $gfwww.'include/role_utils.php';
require_once $gfwww.'project/admin/project_admin_utils.php';
require_once $gfcommon.'include/GroupJoinRequest.class.php';

$group_id = getIntFromRequest('group_id');
session_require(array('group'=>$group_id,'admin_flags'=>'A'));

// get current information
$group =& group_get_object($group_id);
if (!$group || !is_object($group)) {
	exit_error('Error','Could Not Get Group');
} elseif ($group->isError()) {
	exit_error('Error',$group->getErrorMessage());
}

$perm =& $group->getPermission( session_get_user() );
if (!$perm || !is_object($perm)) {
	exit_error('Error','Could Not Get Permission');
} elseif ($perm->isError()) {
	exit_error('Error',$perm->getErrorMessage());
}

if (!$perm->isAdmin()) {
	exit_permission_denied();
}

$group->clearError();

$adminheadertitle=sprintf(_('Project Admin: %1$s'), $group->getPublicName() );
project_admin_header(array('title'=>$adminheadertitle, 'group'=>$group->getID()));
?>

<table class="my-layout-table">
	<tr>
		<td>

<?php echo $HTML->boxTop(_('Misc. Project Information'));  ?>


<?php
	global $sys_use_shell;
	if ($sys_use_shell) {
?> 
<p><?php echo _('Group shell (SSH) server:&nbsp;') ?><strong><?php echo $group->getUnixName().'.'.$GLOBALS['sys_default_domain']; ?></strong></p>
<p><?php echo _('Group directory on shell server:&nbsp;') ?><br/><strong><?php echo account_group_homedir($group->getUnixName()); ?></strong></p>
<p><?php echo _('Project WWW directory on shell server:&nbsp;') ?><br /><strong><?php echo account_group_homedir($group->getUnixName()).'/htdocs'; ?></strong></p>
<?php
	} //end of use_shell condition
?> 

<?php
// If this was a submission, make updates
if (getStringFromRequest('submit')) {
	$form_group_name = getStringFromRequest('form_group_name');
	$form_shortdesc = getStringFromRequest('form_shortdesc');
	$form_homepage = getStringFromRequest('form_homepage');
	$logo_image_id = getIntFromRequest('logo_image_id');
	$use_mail = getStringFromRequest('use_mail');
	$use_survey = getStringFromRequest('use_survey');
	$use_forum = getStringFromRequest('use_forum');
	$use_pm = getStringFromRequest('use_pm');
	$use_scm = getStringFromRequest('use_scm');
	$use_news = getStringFromRequest('use_news');
	$use_docman = getStringFromRequest('use_docman');
	$use_ftp = getStringFromRequest('use_ftp');
	$use_tracker = getStringFromRequest('use_tracker');
	$use_frs = getStringFromRequest('use_frs');
	$use_stats = getStringFromRequest('use_stats');
	$tags = getStringFromRequest('form_tags');
	$is_public = getIntFromRequest('is_public');
	$new_doc_address = getStringFromRequest('new_doc_address');
	$send_all_docs = getStringFromRequest('send_all_docs');

	$res = $group->update(
		session_get_user(),
		$form_group_name,
		$form_homepage,
		$form_shortdesc,
		$use_mail,
		$use_survey,
		$use_forum,
		$use_pm,
		1,
		$use_scm,
		$use_news,
		$use_docman,
		$new_doc_address,
		$send_all_docs,
		100,
		$use_ftp,
		$use_tracker,
		$use_frs,
		$use_stats,
		$tags,
		$is_public
	);
	
	//100 $logo_image_id

	if (!$res) {
		$feedback .= $group->getErrorMessage();
	} else {
		$feedback .= _('Project information updated');
	}
}

?>

<form action="<?php echo getStringFromServer('PHP_SELF'); ?>" method="post">

<input type="hidden" name="group_id" value="<?php echo $group->getID(); ?>" />

<p>
<?php echo _('Descriptive Project Name') ?>:<br />
<input type="text" name="form_group_name" value="<?php echo $group->getPublicName(); ?>" size="40" maxlength="40" />
</p>

<p>
<?php echo _('Short Description (255 Character Max, HTML will be stripped from this description)') ?>:<br />
<textarea cols="80" rows="3" name="form_shortdesc">
<?php echo $group->getDescription(); ?>
</textarea>
</p>

<p>
<?php echo _('Tags (use comma as separator)') ?>:<br />
<input type="text" name="form_tags" size="100" value="<?php echo $group->getTags(); ?>" />
</p>

<p><?php echo _('Trove Categorization:&nbsp;') ?><a href="/project/admin/group_trove.php?group_id=<?php echo $group->getID(); ?>">[<?php echo _('Edit') ?>]</a></p>

<p>
<?php echo _('Homepage Link') ?>:<br />
<input type="text" name="form_homepage" size="100" value="<?php echo $group->getHomePage(); ?>" />
</p>

<?php
	if ($sys_use_private_project) {
		echo '<p>' ;
		echo _('Visibility: ');
		echo html_build_select_box_from_arrays(
               array('0','1'),
               array(  _('Private'), _('Public') ),
               'is_public', $group->isPublic(), false);
	} else {
		echo "<input type=hidden name=\"is_public\" value=\"1\">";
	}
?>

<?php
// This function is used to render checkboxes below
function c($v) {
        if ($v) {
                return 'checked="checked"';
        } else {
                return '';
        }
}
?>

<?php
if($sys_use_mail) {
?>
<input type="hidden" name="use_mail" value="<?php echo ($group->usesMail() ? '1' : '0'); ?>" />
<?php
} 

if($sys_use_survey) {
?>
<input type="hidden" name="use_survey" value="<?php echo ($group->usesSurvey() ? '1' : '0'); ?>" />
<?php
}

if($sys_use_forum) {
?>
<input type="hidden" name="use_forum" value="<?php echo ($group->usesForum() ? '1' : '0'); ?>" />
<?php
}

if($sys_use_pm) {
?>
<input type="hidden" name="use_pm" value="<?php echo ($group->usesPM() ? '1' : '0'); ?>" />
<?php
}

if($sys_use_scm) {
?>
<input type="hidden" name="use_scm" value="<?php echo ($group->usesSCM() ? '1' : '0'); ?>" />
<?php
}

if($sys_use_news) {
?>
<input type="hidden" name="use_news" value="<?php echo ($group->usesNews() ? '1' : '0'); ?>" />
<?php
}

if($sys_use_docman) {
?>
<input type="hidden" name="use_docman" value="<?php echo ($group->usesDocman() ? '1' : '0'); ?>" />
<?php
}

if($sys_use_ftp) {
?>
<input type="hidden" name="use_ftp" value="<?php echo ($group->usesFTP() ? '1' : '0'); ?>" />
<?php
}

if($sys_use_tracker) {
?>
<input type="hidden" name="use_tracker" value="<?php echo ($group->usesTracker() ? '1' : '0'); ?>" />
<?php
}

if($sys_use_frs) {
?>
<input type="hidden" name="use_frs" value="<?php echo ($group->usesFRS() ? '1' : '0'); ?>" />
<?php } ?>

<input type="hidden" name="use_stats" value="<?php echo ($group->usesStats() ? '1' : '0'); ?>" />

<p>
<?php echo _('If you wish, you can provide default email addresses to which new submissions will be sent') ?>.<br />
<strong><?php echo _('New Document Submissions') ?>:</strong><br />
<input type="text" name="new_doc_address" value="<?php echo $group->getDocEmailAddress(); ?>" size="40" maxlength="250" />
<?php echo _('(send on all updates)') ?>
<input type="checkbox" name="send_all_docs" value="1" <?php echo c($group->docEmailAll()); ?> />
</p>

<p>
<input type="submit" name="submit" value="<?php echo _('Update') ?>" />
</p>

</form>

<?php
echo $HTML->boxBottom(); 
?>
		</td>
		<td>&nbsp;</td>
		<td>

<?php

		echo $HTML->boxTop(_('Project Members'));

		/*

			Show the members of this project

		*/

		$res_memb = db_query_params ('SELECT users.realname,users.user_id,users.status,
			users.user_name,user_group.admin_flags,user_group.role_id
			FROM users,user_group 
			WHERE users.user_id=user_group.user_id 
			AND user_group.group_id=$1 ORDER BY users.lastname,users.firstname',
					     array ($group_id));

		echo '
		<table class="width-100p100">
			<tr><td><strong>'._('Unix name').'</strong></td>
			<td><strong>'._('Role').'</strong></td>
			<td><strong>'._('Update').'</strong></td>
			<td><strong>'._('Remove').'</strong></td></tr>';

while ($row_memb=db_fetch_array($res_memb)) {

		if ($row_memb['status']=='P') {
			$status = "<span class=\"pending\">"._("Pending (P)")."</span>";
		} else if ($row_memb['status']=='S') {
			$status = "<span class=\"suspended\">"._("Suspended (S)")."</span>";
		} else {
			$status = "";
		}

		echo '
			<form action="'.getStringFromServer('PHP_SELF').'" method="post">
			<input type="hidden" name="submit" value="y" />
			<input type="hidden" name="user_id" value="'.$row_memb['user_id'].'" />
			<input type="hidden" name="group_id" value="'. $group_id .'" />
			<td>'.$row_memb['realname'].' ('.$row_memb['user_name'].') '.$status.'</td>
			<td>'.role_box($group_id,'role_id',$row_memb['role_id']).'</td>
			<td><input type="submit" name="updateuser" value="'._('Update').'"></td>
			<td><input type="submit" name="rmuser" value="'._('Remove').'"></td>
			</tr></form>';
}
		echo '
			<tr><td>'._('Observer').'</td>
			<td></td>
			<form action="roleedit.php?group_id='. $group_id .'&amp;role_id=observer" method="POST">
			<td colspan="2"><input type="submit" name="edit" value="'._('Edit Observer').'"></td></form></tr>';

/*
	Add member form
*/

?>
			<form action="<?php echo getStringFromServer('PHP_SELF').'?group_id='.$group_id; ?>" method="post">
			<input type="hidden" name="submit" value="y" />
			<tr><td><input type="text" name="form_unix_name" size="10" value="" /></td>
			<td><?php echo role_box($group_id,'role_id',$row_memb['role_id']); ?></td>
			<td colspan="2"><input type="submit" name="adduser" value="<?php echo _('Add user') ?>" /></td>
			</tr></form>

			<tr><td colspan="4"><a href="massadd.php?group_id=<?php echo $group_id; ?>"><?php echo _('Add Users From List'); ?></a></td></tr>
		</table>
<!--	</td></tr>
</td>
<td width="50%">
&nbsp;
</td>-->
<?php 
//
//	Pending requests
//
echo $HTML->boxMiddle(_('Pending Requests'));
$reqs =& get_group_join_requests($group);
if (count($reqs) < 1) {
	echo _('No Pending Requests');
} else {
	for ($i=0; $i<count($reqs); $i++) {
		$user =& user_get_object($reqs[$i]->getUserId());
		if (!$user || !is_object($user)) {
			echo "Invalid User";
		}
		?>
		<table class="width-100p100">
		<form action="<?php echo getStringFromServer('PHP_SELF').'?group_id='.$group_id; ?>" method="post">
		<input type="hidden" name="submit" value="y" />
		<input type="hidden" name="form_userid" value="<?php echo $user->getId(); ?>" />
		<tr><td><input type="hidden" name="form_unix_name" value="<?php echo $user->getUnixName(); ?>" /><?php echo util_make_link_u ($user->getUnixName(),$user->getId(),$user->getRealName()); ?></td>
		<td><?php echo role_box($group_id,'role_id',$row_memb['role_id']); ?>
			<input type="submit" name="acceptpending" value="<?php echo _('Accept') ?>" />
			<input type="submit" name="rejectpending" value="<?php echo _('Reject') ?>" /></td>
			</tr></form>
		</table>
		
		<?php
	}
}


//
//	RBAC Editing Functions
//
echo $HTML->boxMiddle(_('Edit Roles'));
echo '<form action="roleedit.php?group_id='. $group_id .'" method="POST">';
echo role_box($group_id,'role_id','');
echo '<input type="submit" name="edit" value="'._('Edit Role').'"></form>';

echo '<p><a href="roleedit.php?group_id='.$group_id.'">'._('Add Role').'</a>';

//
//	Project hierarchy functions

plugin_hook('admin_project_link',$group_id) ;


echo $HTML->boxBottom();?>

		</td>
	</tr>

</table>

<?php

project_admin_footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
