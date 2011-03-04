<?php
/**
 *
 * Copyright 1999-2000 (c) The SourceForge Crew
 * Copyright 2010 (c) Franck Villaume - Capgemini
 * http://fusionforge.org
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

require_once('../env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'admin/admin_utils.php';

// user must be forge admin to proceed
session_require_global_perm ('forge_admin');
 
$HTML->header(array('title'=>_('User List')));

/**
 * performAction() - Updates the indicated user status
 *
 * @param               string  $newStatus - the new user status
 * @param               string  $statusString - the status string to display
 * @param               string  $user_id - the user id to act upon
 */
function performAction($newStatus, $statusString, $user_id) {
	$u = user_get_object($user_id);
	if (!$u || !is_object($u)) {
		exit_error(_('Could Not Get User'),'home');
	} elseif ($u->isError()) {
		exit_error($u->getErrorMessage(),'home');
	}
	if($newStatus=="D") {
		if(!$u->setStatus($newStatus)) {
			exit_error($u->getErrorMessage(),'home');
		}
		if(!$u->delete(true)) {
			exit_error($u->getErrorMessage(),'home');
		}
	} else {
		if(!$u->setStatus($newStatus)) {
			exit_error($u->getErrorMessage(),'home');
		}
		if(!$u->setUnixStatus($newStatus)) {
			exit_error($u->getErrorMessage(),'home');
		}

	}
	echo '<p class="feedback">' .sprintf(_('User updated to %1$s status'), $statusString)."</p>";
}

function show_users_list ($users, $filter='') {
	echo '<p>' ._('Key') .':
		<span class="active">'._('Active'). '</span>
		<span class="deleted">' ._('Deleted') .'</span>
		<span class="suspended">' ._('Suspended'). '</span>
		<span class="pending">' ._('(*)Pending'). '</span>'.'</p>';

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
	  '/admin/userlist.php?sortorder=user_name',
	  '/admin/userlist.php?sortorder=add_date',
	  '/admin/userlist.php?sortorder=user_name',
	  '/admin/userlist.php?sortorder=user_name',
	  '/admin/userlist.php?sortorder=user_name',
	  '/admin/userlist.php?sortorder=user_name',
	  '/admin/userlist.php?sortorder=user_name'
	);

	echo $GLOBALS['HTML']->listTableTop($headers, $headerLinks);

	$count = 0;
	foreach ($users as $u) {
		print '<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($count) . '><td class="';
		if ($u->getStatus() == 'A') print "active";
		if ($u->getStatus() == 'D') print "deleted";
		if ($u->getStatus() == 'S') print "suspended";
		if ($u->getStatus() == 'P') print "pending";
		print '"><a href="useredit.php?user_id='.$u->getID().'">';
		if ($u->getStatus() == 'P') print "*";
		echo $u->getRealName().' ('.$u->getUnixName().')</a>';
		echo '</td>';
		echo '<td width="15%" style="text-align:center">';
		echo ($u->getAddDate() ? date(_('Y-m-d H:i'), $u->getAddDate()) : '-');
		echo '</td>';
		echo '<td width="12%" style="text-align:center">'.util_make_link ('/developer/?form_dev='.$u->getID(),_('[DevProfile]')).'</td>';
		echo '<td width="12%" style="text-align:center">'.util_make_link ('/admin/userlist.php?action=activate&amp;user_id='.$u->getID().$filter,_('[Activate]')).'</td>';
		echo '<td width="12%" style="text-align:center">'.util_make_link ('/admin/userlist.php?action=delete&amp;user_id='.$u->getID().$filter,_('[Delete]')).'</td>';
		echo '<td width="12%" style="text-align:center">'.util_make_link ('/admin/userlist.php?action=suspend&amp;user_id='.$u->getID().$filter,_('[Suspend]')).'</td>';
		echo '<td width="12%" style="text-align:center">'.util_make_link ('/admin/passedit.php?user_id='.$u->getID().$filter,_('[Change PW]')).'</td>';
		echo '</tr>';
		$count ++;
	}
	
	echo $GLOBALS['HTML']->listTableBottom();

}

// Administrative functions

$group_id = getIntFromRequest('group_id');
$action = getStringFromRequest('action');
$user_id = getIntFromRequest('user_id');
$status = getStringFromRequest('status');

if ($action=='delete') {
	performAction('D', "DELETED", $user_id);
} else if ($action=='activate') {
	performAction('A', "ACTIVE", $user_id);
} else if ($action=='suspend') {
	performAction('S', "SUSPENDED", $user_id);
}

//	Show list of users
print "<p>" ._('User list for project: ');
if (!$group_id) {
	$user_name_search = getStringFromRequest('user_name_search');

	print "<strong>" ._('All Projects'). "</strong>";
	print "\n</p>";

	if ($user_name_search) {
		$res = db_query_params ('SELECT user_id FROM users WHERE lower(user_name) LIKE $1 OR lower(lastname) LIKE $1 ORDER BY realname',
					   array (strtolower("$user_name_search%")));
	} elseif ($status) {
		$res = db_query_params ('SELECT user_id FROM users WHERE status = $1 ORDER BY realname',
					   array ($status));
	} else {
		$sortorder = getStringFromRequest('sortorder', 'realname');
		util_ensure_value_in_set ($sortorder,
					  array('realname','user_name','lastname','firstname','user_id','status','add_date')) ;
		$res = db_query_params('SELECT user_id FROM users ORDER BY '.$sortorder,
					  array ());
	}
	$filter='';
	if (in_array($status,array('D','A','S','P'))) {
		$filter = '&amp;status='.$status;
	}
	show_users_list (user_get_objects(util_result_column_to_array($res,0)),$filter);
} else {
	/*
		Show list for one group
	*/
	$project = group_get_object($group_id) ;
	print "<strong>" . $project->getPublicName() . "</strong></p>";

	show_users_list ($project->getUsers());
}

$HTML->footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
