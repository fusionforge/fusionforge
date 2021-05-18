<?php
/**
 *
 * Copyright 1999-2000 (c) The SourceForge Crew
 * Copyright 2010 (c) Franck Villaume - Capgemini
 * Copyright 2013-2015, Franck Villaume - TrivialDev
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
require_once $gfcommon.'include/UserManager.class.php';
require_once $gfwww.'admin/admin_utils.php';

global $HTML;
global $LUSER;

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

function show_users_list($users, $filter = '', $sortorder = 'realname', $start, $rows, $paging, $totalUsers) {
	global $HTML;
	echo '<p>' ._('Status')._(': ');
	if (!$filter) {
		echo util_make_link('/admin/userlist.php?sortorder='.$sortorder, _('All'));
	} else {
		if (preg_match('/status=/', $filter)) {
			$filter = preg_replace('/status=.*/','', $filter);
		}
		$filter = rtrim($filter, '&');
		echo util_make_link('/admin/userlist.php?sortorder='.$sortorder.$filter, _('All'));
	}
	echo '	<span class="active">'.util_make_link('/admin/userlist.php?status=A&sortorder='.$sortorder.$filter,_('Active')). '</span>
		<span class="deleted">'.util_make_link('/admin/userlist.php?status=D&sortorder='.$sortorder.$filter,_('Deleted')).'</span>
		<span class="suspended">'.util_make_link('/admin/userlist.php?status=S&sortorder='.$sortorder.$filter,_('Suspended')).'</span>
		<span class="pending">'.util_make_link('/admin/userlist.php?status=P&sortorder='.$sortorder.$filter,_('(*)Pending')).'</span>'.'</p>';

	if (!count($users)) {
		echo $HTML->warning_msg(_('No user found matching selected criteria.'));
		return;
	}

	$headers = array(
		_('Login'),
		_('Add date'),
		'',
		'',
		'',
		'',
		''
	);

	$headerLinks = array(
		'/admin/userlist.php?sortorder=user_name'.$filter,
		'/admin/userlist.php?sortorder=add_date'.$filter,
		null,
		null,
		null,
		null,
		null
	);

	echo $HTML->paging_top($start, $paging, $totalUsers, $rows, '/admin/userlist.php?sortorder='.$sortorder.$filter);
	echo $HTML->listTableTop($headers, $headerLinks);

	foreach ($users as $key => $uid) {
		$cells = array();
		$u = user_get_object($uid);
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
		$cells[] = array(($u->getAddDate() ? date(_('Y-m-d H:i'), $u->getAddDate()) : '-'), 'style' => 'width:15%', 'class' => 'align-center');
		if ($u->getStatus() != 'D') {
			$nextcell = util_make_link_u($u->getUnixName(), $u->getID(), _('User Profile'));
		} else {
			$nextcell = '<span class="strike">'._('User Profile').'</span>';
		}
		$cells[] = array($nextcell, 'style' => 'width:15%', 'class' => 'align-center');
		if ($u->getStatus() != 'A') {
			$nextcell = util_make_link('/admin/userlist.php?action=activate&user_id='.$u->getID().$filter,_('Activate'));
		} else {
			$nextcell = '<span class="strike">'._('Activate').'</span>';
		}
		$cells[] = array($nextcell, 'style' => 'width:15%', 'class' => 'align-center');
		if ($u->getStatus() != 'D') {
			$nextcell = util_make_link('/admin/userlist.php?action=delete&user_id='.$u->getID().$filter,_('Delete'));
		} else {
			$nextcell = '<span class="strike">'._('Delete').'</span>';
		}
		$cells[] = array($nextcell, 'style' => 'width:15%', 'class' => 'align-center');
		if ($u->getStatus() != 'S') {
			$nextcell = util_make_link('/admin/userlist.php?action=suspend&user_id='.$u->getID().$filter,_('Suspend'));
		} else {
			$nextcell = '<span class="strike">'._('Suspend').'</span>';
		}
		$cells[] = array($nextcell, 'style' => 'width:15%', 'class' => 'align-center');
		$cells[] = array(util_make_link('/admin/passedit.php?user_id='.$u->getID().$filter,_('Change Password')), 'style' => 'width:12%', 'class' => 'align-center');
		echo $HTML->multiTableRow(array(), $cells);
	}
	echo $HTML->listTableBottom();
	echo $HTML->paging_bottom($start, $paging, $totalUsers, '/admin/userlist.php?sortorder='.$sortorder.$filter);
}

if (getStringFromRequest('setpaging')) {
	/* store paging preferences */
	$paging = getIntFromRequest('nres');
	if (!$paging) {
		$paging = 25;
	}
	$LUSER->setPreference('paging', $paging);
} else {
	$paging = $LUSER->getPreference('paging');
}

if (!$paging) {
	$paging = 25;
}
// Administrative functions

$group_id = getIntFromRequest('group_id');
$action = getStringFromRequest('action');
$user_id = getIntFromRequest('user_id');
$status = getStringFromRequest('status');
$usingplugin = getStringFromRequest('usingplugin');
$start = getIntFromRequest('start');

if ($start < 0) {
	$start = 0 ;
}

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
	echo html_e('h2', array(), _('Users that use plugin').' '.$usingplugin);
	$res = db_query_params('SELECT u.user_id FROM plugins p, user_plugin up, users u WHERE p.plugin_name = $1 and up.user_id = u.user_id and p.plugin_id = up.plugin_id and users.user_id != 100 ORDER BY users.realname LIMIT $2 OFFSET $3',
				array($usingplugin, $paging, $start));
	$totalUsers = FusionForge::getInstance()->getNumberOfUsersUsingAPlugin($usingplugin);
	$max = ($totalUsers > ($start + $paging)) ? ($start + $paging) : $totalUsers;
	show_users_list(util_result_column_to_array($res, 0), '', 'realname', $start, $max, $paging, $totalUsers);

} elseif (!$group_id) {
	$filter = '';
	$user_name_search = getStringFromRequest('user_name_search');
	$sort_order = getStringFromRequest('sortorder', 'realname');
	util_ensure_value_in_set($sort_order, array('realname','user_name','lastname','firstname','user_id','status','add_date'));

	$qpa = db_construct_qpa(false, 'SELECT user_id FROM users WHERE users.user_id != 100');
	if ($user_name_search) {
		$qpa = db_construct_qpa($qpa, ' AND (lower(user_name) LIKE $1 OR lower(lastname) LIKE $1)', array(strtolower("$user_name_search%")));
		$msg = sprintf(_('User list beginning with “%s” for all projects'), $user_name_search);
		$filter .= '&user_name_search='.$user_name_search;
	} else {
		$msg = _('User list for all projects');
	}
	if ($status && in_array($status, array('D', 'A', 'S', 'P'))) {
		$qpa = db_construct_qpa($qpa, ' AND status = $1', array($status));
		$filter .= '&status='.$status;
	}
	$qpa = db_construct_qpa($qpa, ' ORDER BY '.$sort_order);
	$res = db_query_qpa($qpa, $paging, $start);
	$list_id = util_result_column_to_array($res, 0);
	echo html_e('h2', array(), $msg);

	$params = array('status' => $status, 'user_name_search' => $user_name_search);
	$totalUsers = FusionForge::getInstance()->getNumberOfUsersByStatusAndName($params);
	$max = ($totalUsers > ($start + $paging)) ? ($start + $paging) : $totalUsers;
	show_users_list($list_id, $filter, $sort_order, $start, $max, $paging, $totalUsers);
} else {
	/*
		Show list for one project
	*/
	$project = group_get_object($group_id);
	echo html_e('h2', array(), _('User list for project')._(': ').$project->getPublicName());
	$users = $project->getUsers();
	$totalUsers = count($users);
	if ($users) {
		$sort_order = getStringFromRequest('sortorder', 'realname');
		util_ensure_value_in_set($sort_order,
					array('realname','user_name','lastname','firstname','user_id','status','add_date'));
		sortUserList($users, $sort_order);
		$users_paged = array_slice($users, $start, $paging);
		unset($users);
		foreach ($users_paged as $key => $user) {
			$users_id[] = $user->getID();
		}
		$filter = '&group_id='.$group_id;
		$max = ($totalUsers > ($start + $paging)) ? ($start + $paging) : $totalUsers;
		show_users_list($users_id, $filter, $sort_order, $start, $max, $paging, $totalUsers);
	} else {
		echo $HTML->information(_('No user in this project'));
	}
}

$HTML->footer();

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
