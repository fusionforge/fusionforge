<?php
/**
 *
 *
 * Copyright 1999-2000 (c) The SourceForge Crew
 *
 * $Id$
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

require_once('../env.inc.php');
require_once('pre.php');
require_once('www/admin/admin_utils.php');
session_require(array('group'=>'1','admin_flags'=>'A'));
 
$HTML->header(array('title'=>_(': User List')));

/**
 * performAction() - Updates the indicated user status
 *
 * @param               string  $newStatus - the new user status
 * @param               string  $statusString - the status string to display
 * @param               string  $user_id - the user id to act upon
 */
function performAction($newStatus, $statusString, $user_id) {
	$u =& user_get_object($user_id);
	if (!$u || !is_object($u)) {
		exit_error('Error','Could Not Get User');
	} elseif ($u->isError()) {
		exit_error('Error',$u->getErrorMessage());
	}
	if($newStatus=="D") {
		if(!$u->delete(true)) {
			exit_error('Error',$u->getErrorMessage());
		}
	} else {
		if(!$u->setStatus($newStatus)) {
			exit_error('Error',$u->getErrorMessage());
		}	
	}
	echo "<h2>" .sprintf(_('User updated to %1$s status'), $GLOBALS['statusString'])."</h2>";
}

function show_users_list ($result) {
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
		'&nbsp;'
	);

	$headerLinks = array(
	  '?sortorder=user_name',
	  '?sortorder=add_date',
	  '?sortorder=user_name',
	  '?sortorder=user_name',
	  '?sortorder=user_name',
	  '?sortorder=user_name'
	);

	echo $GLOBALS['HTML']->listTableTop($headers, $headerLinks);

	$count = 0;
	while ($usr = db_fetch_array($result)) {
		print '<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($count) . '><td class="';
		if ($usr['status'] == 'A') print "active";
		if ($usr['status'] == 'D') print "deleted";
		if ($usr['status'] == 'S') print "suspended";
		if ($usr['status'] == 'P') print "pending";
		print '"><a href="useredit.php?user_id='.$usr['user_id'].'">';
		if ($usr['status'] == 'P') print "*";
		echo $usr['firstname'].' '.$usr['lastname'].' ('.$usr['user_name'].')</a>';
		echo '</td>';
		echo '<td width="15%" style="text-align:center">';
		echo ($usr['add_date'] ? date(_('Y-m-d H:i'), $usr['add_date']) : '-');
		echo '</td>';
		echo '<td width="15%" style="text-align:center"><a href="'.$GLOBALS['sys_urlprefix'].'/developer/?form_dev='.$usr['user_id'].'">[' ._('DevProfile'). ']</a></td>';
		echo '<td width="15%" style="text-align:center"><a href="userlist.php?action=activate&amp;user_id='.$usr['user_id'].'">[' ._('Activate'). ']</a></td>';
		echo '<td width="15%" style="text-align:center"><a href="userlist.php?action=delete&amp;user_id='.$usr['user_id'].'">[' ._('Delete') .']</a></td>';
		echo '<td width="15%" style="text-align:center"><a href="userlist.php?action=suspend&amp;user_id='.$usr['user_id'].'">[' ._('Suspend'). ']</a></td>';
		echo '</tr>';
		$count ++;
	}
	
	echo $GLOBALS['HTML']->listTableBottom();

}

// Administrative functions

$group_id = getIntFromRequest('group_id');
$action = getStringFromRequest('action');
$user_id = getStringFromRequest('user_id');

if ($action=='delete') {
	performAction('D', "DELETED", $user_id);
	//plugin webcal
	//del webcal user
	plugin_hook('del_cal_user',$user_id);
} else if ($action=='activate') {
	performAction('A', "ACTIVE", $user_id);
	//plugin webcal
	//create webcal user
	plugin_hook('add_cal_user',$user_id);
} else if ($action=='suspend') {
	performAction('S', "SUSPENDED", $user_id);
}

// Add a user to this group
if (getStringFromRequest('action') == 'add_to_group') {
	echo "ACTION NOT SUPPORTED";
}

//	Show list of users
print "<p>" ._('User list for group:');
if (!$group_id) {
	$user_name_search = getStringFromRequest('user_name_search');

	print "<strong>" ._('All Groups'). "</strong>";
	print "\n</p>";

	if ($user_name_search) {
		$sql = 'SELECT user_name,lastname,firstname,user_id,status,add_date FROM users WHERE user_name ';
	    $sql .= $sys_database_type == 'mysql' ? 'LIKE' : 'ILIKE';
		$sql .= ' \''.$user_name_search.'%\' OR lastname ';
	    $sql .= $sys_database_type == 'mysql' ? 'LIKE' : 'ILIKE';
		$sql .= ' \''.$user_name_search.'%\' ORDER BY lastname';
		$result = db_query($sql);
	} else {
		$sortorder = $_GET['sortorder'];
		if (!isset($sortorder) || empty($sortorder)) {
		  $sortorder = "user_name";
		}
		$result = db_query("SELECT user_name,lastname,firstname,user_id,status,add_date FROM users ORDER BY ".$sortorder);
	}
	show_users_list ($result);
} else {
	/*
		Show list for one group
	*/
	print "<strong>" . group_getname($group_id) . "</strong></p>";


	$result = db_query("SELECT users.user_id AS user_id,users.user_name AS user_name,users.status AS status, users.add_date AS add_date "
		. "FROM users,user_group "
		. "WHERE users.user_id=user_group.user_id AND "
		. "user_group.group_id='$group_id' ORDER BY users.user_name");
	show_users_list ($result);

	/*
        	Show a form so a user can be added to this group
	*/
	?>
	<hr />
	<p>
	<form action="<?php echo getStringFromServer('PHP_SELF'); ?>" method="post">
	<input type="hidden" name="action" value="add_to_group" />
	<input name="user_id" type="TEXT" value="" />
	<br />
	Add User to Group (<?php print group_getname($group_id); ?>):
	<br />
	<input type="hidden" name="group_id" value="<?php print $group_id; ?>" />
	<br />
	<input type="submit" name="Submit" value="<?php echo _('Submit'); ?>" />
	</form>
	</p>
	<?php
}

$HTML->footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
