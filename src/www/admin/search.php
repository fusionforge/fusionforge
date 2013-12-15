<?php
/**
 * Site Admin generic user/group search page
 *
 * This is the single page for searching/selection of users/groups for
 * Site Admin. Currently, it supports querying by (sub)string match in
 * string user/group properties (names, fullnames, email) and status.
 * If new search criteria will be required, they should be added here,
 * not any other (new) page.
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2013, French Ministry of National Education
 * Copyright 2013, Franck Villaume - TrivialDev
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

$search = trim(getStringFromRequest('search'));
$usersearch = trim(getStringFromRequest('usersearch'));

site_admin_header(array('title'=>_('Admin Search Results')));

function format_name($name, $status) {
	if ($status == 'D') {
		return "<strong><span class=\"strike\">$name</span></strong>";
	} elseif ($status == 'S') {
		return "<strong><span style=\"text-decoration:underline\">$name</span></strong>";
	} elseif ($status == 'H') {
		return "<strong><span style=\"text-decoration:underline\">$name</span></strong>";
	} elseif ($status == 'P') {
		return "<strong><em>$name</em></strong>";
	} elseif ($status == 'I') {
		return "<strong><em>$name</em></strong>";
	}

	return $name;
}

/*
	Main code
*/
if ($usersearch) {
	$result = db_query_params ('SELECT DISTINCT * FROM users
		WHERE cast(user_id as text) LIKE $1
		OR lower(user_name) LIKE $1
		OR lower(email) LIKE $1
		OR lower(realname) LIKE $1',
		array (strtolower("%$search%")));

	print '<p><strong>' .sprintf(ngettext('User search with criteria <em>%1$s</em>: %2$s match', 'User search with criteria <em>%1$s</em>: %2$s matches', db_numrows($result)), $search, db_numrows($result)).'</strong></p>';

	if (db_numrows($result) >= 1) {
		$title=array();
		$title[]=_('Id');
		$title[]=_('User Name');
		$title[]=_('Real Name');
		$title[]=_('Email');
		$title[]=_('Member since');
		$title[]=_('Status');

		echo $GLOBALS['HTML']->listTableTop($title);
		$i = 0 ;
		while ($row = db_fetch_array($result)) {
			print '
				<tr '.$GLOBALS['HTML']->boxGetAltRowStyle($i++).'>
				<td><a href="useredit.php?user_id='.$row['user_id'].'">'.$row['user_id'].'</a></td>
				<td>'.format_name($row['user_name'], $row['status']).'</td>
				<td>'.$row['realname'].'</td>
				<td>'.$row['email'].'</td>
				<td>'.date(_('Y-m-d H:i'), $row['add_date']).'</td>
				<td class="align-center">'.format_name($row['status'].'/'.$row['unix_status'], $row['status']).'</td>
				</tr>
			';
		}

		echo $GLOBALS['HTML']->listTableBottom();
	} else {
		echo '<p class="information">'._('No user found.').'</p>';
	}
} // end if ($usersearch)

if (getStringFromRequest('groupsearch')) {
	$status = getStringFromRequest('status');
	$is_public = getIntFromRequest('is_public', -1);
	$crit_desc = '';
	$qpa = db_construct_qpa () ;

	if(is_numeric($search)) {
		$qpa = db_construct_qpa ($qpa, 'SELECT DISTINCT * FROM groups
						WHERE (group_id=$1 OR lower (unix_group_name) LIKE $2 OR lower (group_name) LIKE $2)',
					    array ($search,
						   strtolower ("%$search%"))) ;
	} else {
		$qpa = db_construct_qpa ($qpa, 'SELECT DISTINCT * FROM groups WHERE (lower (unix_group_name) LIKE $1 OR lower (group_name) LIKE $1)',
					    array (strtolower ("%$search%"))) ;
	}

	if ($status) {
		$qpa = db_construct_qpa ($qpa, ' AND status=$1', array ($status)) ;
		$crit_desc .= " status=$status";
	}

	if ($crit_desc) {
		$crit_desc = "(".trim($crit_desc).")";
	}

	$result = db_query_qpa ($qpa) ;
	if (db_numrows($result) >= 1) {
		$rows = array();
		$ra = RoleAnonymous::getInstance() ;
		while ($row = db_fetch_array($result)) {

			if ($is_public == 1) {
				if ($ra->hasPermission('project_read', $row['group_id'])) {
					$rows[] = $row;
				}
			} elseif ($is_public == 0) {
				if (!$ra->hasPermission('project_read', $row['group_id'])) {
					$rows[] = $row;
				}
			} else {
				$rows[] = $row;
			}
		}

		print '<p><strong>'.sprintf(ngettext('Project search with criteria <em>%s</em>: %d match', 'Project search with criteria <em>%s</em>: %d matches', count($rows)), $crit_desc, count($rows)).'</strong></p>';

		$title=array();
		$title[]=_('Id');
		$title[]=_('Unix Name');
		$title[]=_('Full Name');
		$title[]=_('Registered');
		$title[]=_('Status');

		echo $GLOBALS['HTML']->listTableTop($title);

		$i = 0;
		foreach ($rows as $row) {
			$extra_status = "";
			if (!$ra->hasPermission('project_read', $row['group_id'])) {
				$extra_status = "/PRV";
			}

			print '
				<tr '.$GLOBALS['HTML']->boxGetAltRowStyle($i++).'>
				<td><a href="groupedit.php?group_id='.$row['group_id'].'">'.$row['group_id'].'</a></td>
				<td>'.format_name($row['unix_group_name'], $row['status']).'</td>
				<td>'.$row['group_name'].'</td>
				<td>'.date(_('Y-m-d H:i'), $row['register_time']).'</td>
				<td class="align-center">'.format_name($row['status'].$extra_status, $row['status']).'</td>
				</tr>
			';

		}

		echo $GLOBALS['HTML']->listTableBottom();
	} else {
		echo '<p class="information">'._('No project found').'</p>';
	}
} //end if($groupsearch)

site_admin_footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
