<?php
/**
 * Site Admin user properties editing page
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright (C) 2011 Alain Peyrat - Alcatel-Lucent
 * Copyright 2011, Franck Villaume - Capgemini
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

require_once('../env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once $gfcommon.'include/account.php';
require_once $gfwww.'admin/admin_utils.php';
require_once $gfwww.'include/role_utils.php';

session_require_global_perm('forge_admin');

$unix_status2str = array(
	'N'=>_('No Unix account (N)'),
	'A'=>_('Active (A)'),
	'S'=>_('Suspended (S)'),
	'D'=>_('Deleted (D)')
);

$user_id = getIntFromRequest('user_id');
$u =& user_get_object($user_id);
if (!$u || !is_object($u)) {
	exit_error(_('Could Not Get User'),'admin');
} elseif ($u->isError()) {
	exit_error($u->getErrorMessage(),'admin');
}

if (getStringFromRequest('delete_user') != '' && getStringFromRequest('confirm_delete') == '1') {
	// delete user
	if (!$u->delete(true)) {
		exit_error( _('Could Not Complete Operation: ').$u->getErrorMessage(),'admin');
	} else {
		$feedback = _('Deleted (D)').'<br />';
	}

} elseif (getStringFromRequest('action') == "update_user" && getStringFromRequest('delete_user') == '') {
	$email = getStringFromRequest('email');
	$shell = getStringFromRequest('shell');
	$status = getStringFromRequest('status');
	$addToProjectArray =getStringFromRequest('group_id_add_member');

	//XXX use_shell
	if (!$u->setEmail($email)
		|| (forge_get_config('use_shell') && !$u->setShell($shell))
		|| !$u->setStatus($status)) {
		exit_error( _('Could Not Complete Operation: ').$u->getErrorMessage(),'admin');
	}

	if ($u->getUnixStatus() != 'N') {
		$u->setUnixStatus($status);
	} else {
		if (count($u->getGroups())>0 && $u->isActive()) {
			$u->setUnixStatus('A');
		}else{
			// make sure that user doesn't have LDAP entry
			$u->setUnixStatus('N');
		}
	}

	if (is_array($addToProjectArray)) {
		foreach($addToProjectArray as $project_id_to_add) {
			$feedbackMembership = '';
			$error_msgMembership = '';
			$projectRoleid = getIntFromRequest('role_id-'.$project_id_to_add);
			$projectObjectAction = group_get_object($project_id_to_add);
			if (!$projectObjectAction->addUser((int)$u->getID(), $projectRoleid)) {
				echo $projectObjectAction->getErrorMessage().$u->getID();
				$error_msgMembership .= $projectObjectAction->getErrorMessage().'<br/>';
			} else {
				$feedbackMembership .= _("Added Successfully to project ").$projectObjectAction->getPublicName().'<br/>';
				//if the user have requested to join this group
				//we should remove him from the request list
				//since it has already been added
				$gjr = new GroupJoinRequest($projectObjectAction, $u->getID());
				if ($gjr || is_object($gjr) || !$gjr->isError()) {
					$gjr->delete(true);
				}
			}
		}
	}

	if ($u->isError()) {
		$error_msg = $u->getErrorMessage();
		if (isset($error_msgMembership) && sizeof($error_msgMembership))
			$error_msg .= '<br/>'.$error_msgMembership;
	} else {
		$feedback = _('Updated');
		if (isset($feedbackMembership) && sizeof($feedbackMembership))
			$feedback .= '<br/>'.$feedbackMembership;
	}

}

$title = _('Site Admin: User Info');
site_admin_header(array('title'=>$title));

?>
<h2><?php echo _('Account Information'); ?><sup>1</sup></h2>

<form method="post" action="<?php echo getStringFromServer('PHP_SELF'); ?>">
<input type="hidden" name="action" value="update_user" />
<input type="hidden" name="user_id" value="<?php print $user_id; ?>" />

<table>
<tr>
<td>
<?php echo _('User Id:'); ?>
</td>
<td>
<?php echo $u->getID(); ?>
</td>
</tr>

<tr>
<td>
<?php echo _('User name:'); ?>
</td>
<td>
<?php echo $u->getUnixName(); ?>
</td>
</tr>

<tr>
<td>
<?php echo _('Real name'); ?>
</td>
<td>
<?php echo $u->getRealName(); ?>
</td>
</tr>

<tr>
<td>
<?php echo _('Web account status'); ?>
</td>
<td>
<?php
if ($u->getStatus() == 'D') {
	$status_letter = array('P','A','S','D');
	$status_text   = array(_('Pending (P)'),
		_('Active (A)'),
		_('Suspended (S)'),
		_('Deleted (D)'));
} else if ($u->getStatus() == 'P') {
	$status_letter = array('P','A','S');
	$status_text   = array(_('Pending (P)'),
		_('Active (A)'),
		_('Suspended (S)'));
} else {
	$status_letter = array('A','S');
	$status_text   = array(_('Active (A)'),
		_('Suspended (S)'));
}
echo html_build_select_box_from_arrays(
	$status_letter,	$status_text,'status', $u->getStatus(), false);
?>
</td>
</tr>

<?php


	if (forge_get_config('use_shell')) {
?>
<tr>
	<td>
		<?php echo _('Unix Account Status'); ?><sup>2</sup>:
	</td>
	<td>
		<?php echo $unix_status2str[$u->getUnixStatus()]; ?>
	</td>
</tr>

<tr>
	<td>
		<?php echo _('Unix Shell:'); ?>
	</td>
	<td>
<select name="shell">
<?php account_shellselects($u->getShell()); ?>
</select>
	</td>
</tr>
<?php
	}  // end of sys_use_shell conditionnal
?>

<tr>
<td>
<?php echo _('Email')._(':'); ?>
</td>
<td>
<input type="text" name="email" value="<?php echo $u->getEmail(); ?>" size="25" maxlength="255" />
</td>
</tr>

<tr>
<td>
<?php echo _('Current confirm hash:'); ?>
</td>
<td>
<?php echo $u->getConfirmHash(); ?>
</td>
</tr>
<?php if ($u->getStatus() != 'D') {	?>
<tr>
<td colspan="2"><input type="checkbox" name="confirm_delete" value="1" /><?php echo _('I want to delete this user'); ?>
&nbsp;<input type="submit" name="delete_user" value="<?php echo _('Delete'); ?>" /><br />&nbsp;
</td>
</tr>
<?php } ?>
</table>
<input type="submit" name="submit" value="<?php echo _('Update'); ?>" />
<p>
<sup>1</sup><?php echo _('This pages allows to change only direct properties of user object. To edit properties pertinent to user within specific group, visit admin page of that group (below).'); ?>
</p>

<?php


	if (forge_get_config('use_shell')) {
?>
<p>
<sup>2</sup><?php echo _('Unix status updated mirroring web status, unless it has value \'No unix account (N)\''); ?>
</p>
<?php
	} //end of sys_use_shell condition
?>

<hr />

<h2><?php echo _('Projects Membership'); ?></h2>

<?php
/*
	Iterate and show projects this user is in
*/
$projects = $u->getGroups();

$title = array();
$title[] = _('Name');
$title[] = _('Unix name');
$title[] = _('Operations');

$i = 0;
$userProjectsIdArray = array();
foreach ($projects as $p) {
	if ($i == 0) {
		echo $GLOBALS['HTML']->listTableTop($title);
	}
	print '
		<tr '.$GLOBALS['HTML']->boxGetAltRowStyle($i++).'>
		<td>'.util_unconvert_htmlspecialchars(htmlspecialchars($p->getPublicName())).'</td>
		<td>'.$p->getUnixName().'</td>
		<td width="40%">'.util_make_link('/project/admin/?group_id='.$p->getID(),_('[Project Admin]')).'</td>
		</tr>
	';
	$userProjectsIdArray[] = $p->getID();
	$i++;
}

if ($i > 0) {
	echo $GLOBALS['HTML']->listTableBottom();
} else {
	echo '<p>'._('This user is not a member of any project.').'</p>';
}

echo '<h2>'._('Add membership to new projects').'</h2>';
$addToNewProjectsTableTitle = array();
$addToNewProjectsTableTitle[] = '';
$addToNewProjectsTableTitle[] = _('Name');
$addToNewProjectsTableTitle[] = _('Unix name');
$addToNewProjectsTableTitle[] = _('Operations');
$addToNewProjectsTableTitle[] = _('Select role');
$fullListProjectsQueryResult = db_query_params('SELECT group_id from groups where status = $1 and is_template = 0', array('A'));
if ($fullListProjectsQueryResult) {
	echo $GLOBALS['HTML']->listTableTop($addToNewProjectsTableTitle);
	while ($projectQueryResult = db_fetch_array($fullListProjectsQueryResult)) {
		$projectObject = group_get_object($projectQueryResult['group_id']);
		if (!in_array($projectObject->getID(), $userProjectsIdArray)) {
			print '
				<tr '.$GLOBALS['HTML']->boxGetAltRowStyle($i++).'>
				<td><input type="checkbox" name="group_id_add_member[]" value="'.$projectObject->getID().'">
				<td>'.util_unconvert_htmlspecialchars(htmlspecialchars($projectObject->getPublicName())).'</td>
				<td>'.$projectObject->getUnixName().'</td>
				<td>'.util_make_link ('/project/admin/?group_id='.$projectObject->getID(),_('[Project Admin]')).'</td>
				<td>'.role_box($projectObject->getID(),'role_id-'.$projectObject->getID()).'</td>
				</tr>
			';
		}
	}
	echo $GLOBALS['HTML']->listTableBottom();
}
echo '<br/><input type="submit" name="submit" value="'. _('Update').'" />';
echo '</form>';

site_admin_footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
