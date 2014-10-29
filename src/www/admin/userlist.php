<?php
/**
 *
 * Copyright 1999-2000 (c) The SourceForge Crew
 * Copyright 2010 (c) Franck Villaume - Capgemini
 * Copyright 2013-2014, Franck Villaume - TrivialDev
 * http://fusionforge.org
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
require_once $gfwww.'admin/admin_utils.php';

// user must be forge admin to proceed
session_require_global_perm('forge_admin');

/**
 * performAction() - Updates the indicated user status
 *
 * @param	string	$newStatus - the new user status
 * @param	string	$statusString - the status string to display
 * @param	string	$user_id - the user id to act upon
 */
function performAction($newStatus, $statusString, $user_id) {
	global $feedback;

	$u = user_get_object($user_id);
	if (!$u || !is_object($u)) {
		exit_error(_('Could Not Get User'), 'home');
	} elseif ($u->isError()) {
		exit_error($u->getErrorMessage(), 'home');
	}
	if($newStatus=="D") {
		if(!$u->setStatus($newStatus)) {
			exit_error($u->getErrorMessage(), 'home');
		}
		if(!$u->delete(true)) {
			exit_error($u->getErrorMessage(), 'home');
		}
	} else {
		if(!$u->setStatus($newStatus)) {
			exit_error($u->getErrorMessage(), 'home');
		}
		if(!$u->setUnixStatus($newStatus)) {
			exit_error($u->getErrorMessage(), 'home');
		}
	}
	$feedback = sprintf(_('User updated to %s status'), $statusString);
}

function show_users_list($users, $filter = '', $sortorder = 'realname') {
	global $HTML;
	echo '<p>' ._('Status')._(': ').
		util_make_link('/admin/userlist.php', _('All')). '
		<span class="active">'.util_make_link('/admin/userlist.php?status=A&sortorder='.$sortorder,_('Active')). '</span>
		<span class="deleted">'.util_make_link('/admin/userlist.php?status=D&sortorder='.$sortorder,_('Deleted')).'</span>
		<span class="suspended">'.util_make_link('/admin/userlist.php?status=S&sortorder='.$sortorder,_('Suspended')).'</span>
		<span class="pending">'.util_make_link('/admin/userlist.php?status=P&sortorder='.$sortorder,_('(*)Pending')).'</span>'.'</p>';

	if (!count($users)) {
		echo $HTML->warning_msg(_('No user found matching selected criteria.'));
		return;
	}

	$headers = array(
		_('Login'),
		_('Add date'),
		'&nbsp;',
		'&nbsp;',
		'&nbsp;',
		'&nbsp;',
		'&nbsp;'
	);

	$headerLinks = array(
		'/admin/userlist.php?sortorder=user_name'.$filter,
		'/admin/userlist.php?sortorder=add_date'.$filter,
		'',
		'',
		'',
		'',
		''
	);

	echo $HTML->listTableTop($headers, $headerLinks);

	$res = db_query_params('SELECT * FROM users WHERE user_id = ANY ($1) ORDER BY '.$sortorder,
					array(db_int_array_to_any_clause($users)));
	$count = 0;
	while ($arr = db_fetch_array($res)) {
		$cells = array();
		$u = new GFUser($arr['user_id'], $arr);
		$nextcell = $u->getRealName().' ('.$u->getUnixName().')';
		if ($u->getStatus() == 'P') {
			$nextcell = '*'.$nextcell;
		}
		$nextcell = util_make_link('/admin/useredit.php?user_id='.$u->getID(), $nextcell);
		if ($u->getStatus() == 'A') {
			$cells[] = array($nextcell, 'class' => 'active');
		}
		if ($u->getStatus() == 'D') {
			$cells[] = array($nextcell, 'class' => 'deleted');
		}
		if ($u->getStatus() == 'S') {
			$cells[] = array($nextcell, 'class' => 'suspended');
		}
		if ($u->getStatus() == 'P') {
			$cells[] = array($nextcell, 'class' => 'pending');
		}
		$cells[] = array(($u->getAddDate() ? date(_('Y-m-d H:i'), $u->getAddDate()) : '-'), 'width' => '15%', 'class' => 'align-center');
		if ($u->getStatus() != 'D') {
			$nextcell = util_make_link('/developer/?form_dev='.$u->getID(),_('User Profile'));
		} else {
			$nextcell = '<s>'._('User Profile').'</s>';
		}
		$cells[] = array($nextcell, 'width' => '15%', 'class' => 'align-center');
		if ($u->getStatus() != 'A') {
			$nextcell = util_make_link('/admin/userlist.php?action=activate&user_id='.$u->getID().$filter,_('Activate'));
		} else {
			$nextcell = '<s>'._('Activate').'</s>';
		}
		$cells[] = array($nextcell, 'width' => '15%', 'class' => 'align-center');
		if ($u->getStatus() != 'D') {
			$nextcell = util_make_link('/admin/userlist.php?action=delete&user_id='.$u->getID().$filter,_('Delete'));
		} else {
			$nextcell = '<s>'._('Delete').'</s>';
		}
		$cells[] = array($nextcell, 'width' => '15%', 'class' => 'align-center');
		if ($u->getStatus() != 'S') {
			$nextcell = util_make_link('/admin/userlist.php?action=suspend&user_id='.$u->getID().$filter,_('Suspend'));
		} else {
			$nextcell = '<s>'._('Suspend').'</s>';
		}
		$cells[] = array($nextcell, 'width' => '15%', 'class' => 'align-center');
		$cells[] = array(util_make_link('/admin/passedit.php?user_id='.$u->getID().$filter,_('Change Password')), 'width' => '12%', 'class' => 'align-center');
		echo $HTML->multiTableRow(array('class' => $HTML->boxGetAltRowStyle($count, true)), $cells);
		$count ++;
	}
	echo $HTML->listTableBottom();
}

// Administrative functions

$group_id = getIntFromRequest('group_id');
$action = getStringFromRequest('action');
$user_id = getIntFromRequest('user_id');
$status = getStringFromRequest('status');
$usingplugin = getStringFromRequest('usingplugin');

if ($action=='delete') {
	performAction('D', "DELETED", $user_id);
} elseif ($action=='activate') {
	performAction('A', "ACTIVE", $user_id);
} elseif ($action=='suspend') {
	performAction('S', "SUSPENDED", $user_id);
}

$HTML->header(array('title'=>_('User List')));

//	Show list of users
if ($usingplugin) {
	echo "<p>"._('Users that use plugin'). " <strong>".$usingplugin."</strong></p>\n";
	$res = db_query_params('SELECT u.user_id FROM plugins p, user_plugin up, users u WHERE p.plugin_name = $1 and up.user_id = u.user_id and p.plugin_id = up.plugin_id', array($usingplugin));
	show_users_list(user_get_objects(util_result_column_to_array($res, 0)));

} elseif (!$group_id) {
	$user_name_search = getStringFromRequest('user_name_search');

	if ($user_name_search) {
		$res = db_query_params('SELECT user_id FROM users WHERE lower(user_name) LIKE $1 OR lower(lastname) LIKE $1',
					array(strtolower("$user_name_search%")));
		$list_id = util_result_column_to_array($res,0);
		$msg = sprintf(_('User list beginning with “%s” for all projects'), $user_name_search);
	} else {
		$msg = _('User list for all projects');
	}
	print "<p><strong>$msg</strong></p>\n";

	if ($status) {
		$res = db_query_params ('SELECT user_id FROM users WHERE status = $1',
					   array ($status));
		if (isset($list_id)) {
			$list_id = array_merge($list_id, util_result_column_to_array($res, 0));
		}
		else {
			$list_id = util_result_column_to_array($res, 0);
		}
	}
	if (! isset($list_id)) {
		$res = db_query_params('SELECT user_id FROM users',
				array());
		$list_id = util_result_column_to_array($res, 0);
	}
	$filter='';
	if (in_array($status,array('D','A','S','P'))) {
		$filter = '&status='.$status;
	}
	$sort_order = getStringFromRequest('sortorder', 'realname');
	util_ensure_value_in_set($sort_order,
				array('realname','user_name','lastname','firstname','user_id','status','add_date'));
	show_users_list($list_id, $filter, $sort_order);
} else {
	/*
		Show list for one project
	*/
	$project = group_get_object($group_id);
	print "<p>" ._('User list for project: ') . "<strong>" . $project->getPublicName() . "</strong></p>";
	$users = $project->getUsers();
	if ($users) {
		foreach ($project->getUsers() as $user) {
			$users_id[] = $user->getID();
		}
		$filter = '&group_id='.$group_id;
		$sort_order = getStringFromRequest('sortorder', 'realname');
		util_ensure_value_in_set($sort_order,
					array('realname','user_name','lastname','firstname','user_id','status','add_date'));
		show_users_list($users_id, $filter, $sort_order);
	}
	else {
		echo '<p>'._('No user in this project').'</p>';
	}
}

$HTML->footer();

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
