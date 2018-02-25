<?php
/**
 * Site Admin page for approving/rejecting new projects
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2010 (c) Franck Villaume - Capgemini
 * Copyright (C) 2011 Alain Peyrat - Alcatel-Lucent
 * Copyright 2016, Franck Villaume - TrivialDev
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

// Show no more pending projects per page than specified here
$LIMIT = 50;

require_once '../env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfcommon.'include/account.php';
require_once $gfwww.'include/canned_responses.php';
require_once $gfwww.'admin/admin_utils.php';
require_once $gfwww.'project/admin/project_admin_utils.php';
require_once $gfcommon.'tracker/ArtifactTypes.class.php';
require_once $gfcommon.'forum/Forum.class.php';

global $HTML;

session_require_global_perm ('approve_projects');

function activate_group($group_id) {
	global $feedback;
	global $error_msg;

	$group = group_get_object($group_id);

	if (!$group || !is_object($group)) {
		$error_msg .= _('Error creating group');
		return false;
	} elseif ($group->isError()) {
		$error_msg .= $group->getErrorMessage();
		return false;
	}

	if ($group->approve(session_get_user())) {
		$feedback .= _('Approving Project')._(': ').$group->getUnixName().'<br />';
	} else {
		$error_msg .= _('Error when approving Project')._(': ').$group->getUnixName().'<br />';
		$error_msg .= $group->getErrorMessage();
		return false;
	}

	return true;
}

$action = getStringFromRequest('action');
if ($action == 'activate') {
	$group_id = getIntFromRequest('group_id');
	$list_of_groups = getStringFromRequest('list_of_groups');

	$groups = explode(',', $list_of_groups);
	array_walk($groups, 'activate_group');

} elseif ($action == 'delete') {
	$group_id = getIntFromRequest('group_id');
	$response_id = getIntFromRequest('response_id');
	$add_to_can = getStringFromRequest('add_to_can');
	$response_text = getStringFromRequest('response_text');
	$response_title = getStringFromRequest('response_title');

	$group = group_get_object($group_id);
	if (!$group || !is_object($group)) {
		exit_no_group();
	} elseif ($group->isError()) {
		exit_error($group->getErrorMessage(), 'admin');
	}

	if (!$group->setStatus(session_get_user(), 'D')) {
		exit_error(_('Error during group rejection')._(': ').$this->getErrorMessage(), 'admin');
	}

	$group->addHistory('rejected', 'x');

	// Determine whether to send a canned or custom rejection letter and send it
	if($response_id == 100) {

		$group->sendRejectionEmail(0, $response_text);

		if( $add_to_can ) {
			add_canned_response($response_title, $response_text);
		}

	} else {

		$group->sendRejectionEmail($response_id);

	}
}

site_admin_header(array('title'=>_('Approving Pending Projects')), 'approve_projects');

// get current information
$res_grp = db_query_params("SELECT * FROM groups WHERE status='P' AND is_template!=1", array(), $LIMIT);

$rows = db_numrows($res_grp);

if ($rows < 1) {
	echo $HTML->information(_('No Pending Projects to Approve'));
	site_admin_footer();
	exit;
}

if ($rows > $LIMIT) {
	print '<p>'. _('Pending projects')._(':'). " $LIMIT+ ($LIMIT shown)</p>";
} else {
	print '<p>'. _('Pending projects')._(':'). " $rows</p>";
}

while ($row_grp = db_fetch_array($res_grp)) {

	?>
	<hr />
	<h2><?php echo _('Pending')._(': ').'<i>'. $row_grp['group_name'] . '</i>'; ?></h2>

	<h3><?php  echo _('Pre-approval modifications :'); ?></h3>

	<p><?php echo util_make_link('/admin/groupedit.php?group_id='.$row_grp['group_id'],_('Edit Project Details'));
	echo _(' or ');
	echo util_make_link('/project/admin/?group_id='.$row_grp['group_id'],_('Project Admin'));
	echo _(' or ');
	echo util_make_link('/admin/userlist.php?group_id='.$row_grp['group_id'],_('View/Edit Project Members')); ?></p>

	<h3><?php echo _('Decision')._(':'); ?></h3>
	<table><tr class="bottom"><td>
	<?php
	echo $HTML->openForm(array('name' => 'approve'.$row_grp['unix_group_name'], 'action' => '/admin/approve-pending.php', 'method' => 'post'));
	?>
	<input type="hidden" name="action" value="activate" />
	<input type="hidden" name="list_of_groups" value="<?php print $row_grp['group_id']; ?>" />
	<input type="submit" name="submit" value="<?php echo _('Approve'); ?>" />
	<?php echo $HTML->closeForm(); ?>

	</td><td><?php echo _(' or '); ?>
	</td><td>
	<?php
	echo $HTML->openForm(array('action' => '/admin/approve-pending.php', 'method' => 'post'));
	?>
	<input type="hidden" name="action" value="delete" />
	<input type="hidden" name="group_id" value="<?php print $row_grp['group_id']; ?>" />
	<?php echo _('Rejection canned responses'); ?><br />
	<?php print get_canned_responses();
	echo util_make_link('/admin/responses_admin.php', _('(manage responses)')); ?>
	<br />
	<label for="response_title"><?php echo _('Custom response title and text'); ?></label><br />
	<input id="response_title" type="text" name="response_title" size="30" maxlength="25"/><br />
	<textarea name="response_text" rows="10" cols="50"></textarea>
	<input type="checkbox" name="add_to_can" value="<?php echo _('Yes'); ?>" /><?php echo _('Add this custom response to canned responses') ;?>
	<br />
	<input type="submit" name="submit" value="<?php echo _('Reject'); ?>" />
	<?php echo $HTML->closeForm(); ?>
	</td></tr>
	</table>

	<h3><?php  echo _('Project details')._(':'); ?></h3>

	<table>
	<tr class="top"><td>
	<?php

		if (forge_get_config('use_shell')) {
	?>
	<strong><?php echo _('Home Box')._(': ')."</strong></td><td>"; print $row_grp['unix_box']; ?></tr>
	<?php
		} //end of sys_use_shell
	?>
	<tr><td><strong><?php echo _('HTTP Domain')._(': ')."</strong></td><td>"; print $row_grp['http_domain']; ?></td>

	</tr>

	<?php

	// ########################## OTHER INFO

//	print "<p><strong>" ._('Other Information')."</strong></p>";
	print "<tr><td>" ._('Project Unix Name') . _(':'). "</td><td>".$row_grp['unix_group_name']."</td></tr>";

	print "<tr><td>" ._('Submitted Description')._(':'). "</td><td><blockquote>".$row_grp['short_description']."</blockquote></td></tr>";

	print "<tr><td>" ._('Purpose of submission')._(':'). "</td><td><blockquote>".$row_grp['register_purpose']."</blockquote></td></tr>";

	if ($row_grp['license']=="other") {
		print "<tr><td>" ._('License Other')._(':'). "</td><td><blockquote>".$row_grp['license_other']."</blockquote></td></tr>";
	}

	if (isset($row_grp['status_comment'])) {
		print "<tr><td>" ._('Pending reason')._(':'). "</td><td><span class=\"important\">".$row_grp['status_comment']."</span></td></tr>";
	}

	$submitter = NULL ;
	$project = group_get_object($row_grp['group_id']) ;
	foreach (get_group_join_requests ($project) as $gjr) {
		$submitter = user_get_object($gjr->getUserID()) ;
		echo '<tr><td>'
			._('Submitted by') .'</td><td>'. make_user_link($submitter->getUnixName(),$submitter->getRealName())
			.'</td></tr>';
	}

	if ($row_grp['built_from_template']) {
		$templateproject = group_get_object($row_grp['built_from_template']) ;
		print "<tr><td>" . _('Based on template project') . '</td><td>'. $templateproject->getPublicName() .' ('. $templateproject->getUnixName().")</td></tr>";
	}

	echo "</table><hr />";
}

//list of group_id's of pending projects
$arr = util_result_column_to_array($res_grp, 0);
$group_list = implode($arr, ',');

echo $HTML->openForm(array('action' => '/admin/approve-pending.php', 'method' => 'post'));
echo '
	<p class="align-center">
	<input type="hidden" name="action" value="activate" />
	<input type="hidden" name="list_of_groups" value="'.$group_list.'" />
	<input type="submit" name="submit" value="'._('Approve All On This Page').'" />
	</p>';
echo $HTML->closeForm();
site_admin_footer();

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
