<?php
/**
 * Site Admin user properties editing page
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright (C) 2011 Alain Peyrat - Alcatel-Lucent
 * Copyright 2011, Franck Villaume - Capgemini
 * Copyright 2014, Franck Villaume - TrivialDev
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

require_once '../env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfcommon.'include/account.php';
require_once $gfwww.'admin/admin_utils.php';
require_once $gfwww.'include/role_utils.php';

global $HTML;

session_require_global_perm('forge_admin');

$unix_status2str = array(
	'N'=>_('No Unix account (N)'),
	'A'=>_('Active (A)'),
	'S'=>_('Suspended (S)'),
	'D'=>_('Deleted (D)')
);

$user_id = getIntFromRequest('user_id');
$u = user_get_object($user_id);
if (!$u || !is_object($u)) {
	exit_error(_('Could Not Get User'),'admin');
} elseif ($u->isError()) {
	exit_error($u->getErrorMessage(),'admin');
}

if (getStringFromRequest('delete_user') != '') {
	if (getStringFromRequest('confirm_delete') == '1') {
		// delete user
		if (!$u->delete(true)) {
			exit_error( _('Could Not Complete Operation: ').$u->getErrorMessage(),'admin');
		} else {
			$feedback = _('Deleted (D)').'<br />';
		}
	} else {
		$error_msg = _('Please check the confirmation box if you really want to delete this user.');
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

	if (is_array($addToProjectArray)) {
		foreach($addToProjectArray as $project_id_to_add) {
			$feedbackMembership = '';
			$error_msgMembership = '';
			$projectRoleid = getIntFromRequest('role_id-'.$project_id_to_add);
			$projectObjectAction = group_get_object($project_id_to_add);
			if (!$projectObjectAction->addUser((int)$u->getID(), $projectRoleid)) {
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

	if ($u->getUnixStatus() == 'A') {
		$u->setUnixStatus($status);
	} else {
		if (count($u->getGroups())>0 && $u->isActive()) {
			$u->setUnixStatus('A');
		}else{
			// make sure that user doesn't have LDAP entry
			$u->setUnixStatus('N');
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
<h2><?php echo _('Account Information'); ?></h2>

<?php
echo $HTML->openForm(array('method' => 'post', 'action' => getStringFromServer('PHP_SELF'))); ?>
<input type="hidden" name="action" value="update_user" />
<input type="hidden" name="user_id" value="<?php print $user_id; ?>" />

<table class="infotable">
<tr>
<td>
<?php echo _('User Id')._(':'); ?>
</td>
<td>
<?php echo $u->getID(); ?>
</td>
</tr>

<tr>
<td>
<?php echo _('User Name')._(':'); ?>
</td>
<td>
<?php echo $u->getUnixName(); ?>
</td>
</tr>

<tr>
<td>
<?php echo _('Real Name')._(':'); ?>
</td>
<td>
<?php echo $u->getRealName(); ?>
</td>
</tr>

<tr>
<td>
<?php echo _('Web account status') . _(':'); ?>
</td>
<td>
<?php
if ($u->getStatus() == 'D') {
	$status_letter = array('P','A','S','D');
	$status_text   = array(_('Pending (P)'),
		_('Active (A)'),
		_('Suspended (S)'),
		_('Deleted (D)'));
} elseif ($u->getStatus() == 'P') {
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
		<?php echo _('Unix Account Status'); ?>:
	</td>
	<td>
		<?php echo $unix_status2str[$u->getUnixStatus()]; ?>
	</td>
</tr>

<tr>
	<td>
        <label for="unix-shell"><?php echo _('Unix Shell:'); ?></label>
	</td>
	<td>
		<select id="unix-shell" name="shell">
			<?php account_shellselects($u->getShell()); ?>
        </select>
    </td>
</tr>
<?php
	}  // end of sys_use_shell conditional
?>

<tr>
<td>
<label for="email"><?php echo _('Email')._(':'); ?></label>
</td>
<td>
<input id="email" type="text" name="email" value="<?php echo $u->getEmail(); ?>" size="40" maxlength="255" />
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
<td colspan="2">
	<input id="confirm-delete"  type="checkbox" name="confirm_delete" value="1" />
	<label for="confirm-delete"><?php echo _('I want to delete this user'); ?></label>&nbsp;
	<input type="submit" name="delete_user" value="<?php echo _('Delete'); ?>" /><br />&nbsp;
</td>
</tr>
<?php } ?>
</table>
<input type="submit" name="submit" value="<?php echo _('Update'); ?>" />
<p>
<?php echo _('This pages allows to change only direct properties of user object. To edit properties pertinent to user within specific project, visit admin page of that project (below).'); ?>
</p>

<?php
	if (forge_get_config('use_shell')) {
?>
<p>
<?php echo _('Unix status updated mirroring web status, unless it has value “No Unix account (N)”'); ?>
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
$title[] = _('Unix Name');
$title[] = _('Operations');

$i = 0;
$userProjectsIdArray = array();
foreach ($projects as $p) {
	if ($i == 0) {
		echo $HTML->listTableTop($title);
	}
	$cells = array();
	$cells[][] = util_unconvert_htmlspecialchars(htmlspecialchars($p->getPublicName()));
	$cells[][] = $p->getUnixName();
	$cells[] = array(util_make_link('/project/admin/?group_id='.$p->getID(),'['._('Project Admin').']'), 'width' => '40%');
	echo $HTML->multiTableRow(array('class' => $HTML->boxGetAltRowStyle($i++, true)), $cells);
	$userProjectsIdArray[] = $p->getID();
}

if ($i > 0) {
	echo $HTML->listTableBottom();
} else {
	echo '<p>'._('This user is not a member of any project.').'</p>';
}

echo '<h2>'._('Add membership to new projects').' '._('(100 shown)').'</h2>';
$addToNewProjectsTableTitle = array();
$addToNewProjectsTableTitle[] = '';
$addToNewProjectsTableTitle[] = _('Name');
$addToNewProjectsTableTitle[] = _('Unix Name');
$addToNewProjectsTableTitle[] = _('Operations');
$addToNewProjectsTableTitle[] = _('Select role');
$fullListProjectsQueryResult = db_query_params('SELECT group_id from groups where status = $1 and is_template = 0 LIMIT 100', array('A'));
if ($fullListProjectsQueryResult) {
	echo $HTML->listTableTop($addToNewProjectsTableTitle);
	while ($projectQueryResult = db_fetch_array($fullListProjectsQueryResult)) {
		$projectObject = group_get_object($projectQueryResult['group_id']);
		if (!in_array($projectObject->getID(), $userProjectsIdArray)) {
			$cells = array();
			$cells[][] = '<input type="checkbox" name="group_id_add_member[]" value="'.$projectObject->getID().'">';
			$cells[][] = util_unconvert_htmlspecialchars(htmlspecialchars($projectObject->getPublicName()));
			$cells[][] = $projectObject->getUnixName();
			$cells[][] = util_make_link('/project/admin/?group_id='.$projectObject->getID(),'['._('Project Admin').']');
			$cells[][] = role_box($projectObject->getID(),'role_id-'.$projectObject->getID());
			echo $HTML->multiTableRow(array('class' => $HTML->boxGetAltRowStyle($i++, true)), $cells);
		}
	}
	echo $HTML->listTableBottom();
}
echo '<br/><input type="submit" name="submit" value="'. _('Update').'" />';
echo $HTML->closeForm();

site_admin_footer();

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
