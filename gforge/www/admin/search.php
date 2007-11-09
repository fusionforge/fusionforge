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
 *
 * @version   $Id$
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

$search = getStringFromRequest('search');
$substr = getStringFromRequest('substr');
$usersearch = getStringFromRequest('usersearch');

if (!$search) {
	exit_error(_('Error'), _('Refusing to display whole DB,That would display whole DB.  Please use a CLI query if you wish to do this.'));
}

site_admin_header(array('title'=>_('Admin Search Results')));

function format_name($name, $status) {
	if ($status == 'D') {
		return "<strong><strike>$name</strike></strong>";
	} else if ($status == 'S') {
		return "<strong><span style=\"text-decoration:underline\">$name</span></strong>";
	} else if ($status == 'H') {
		return "<strong><span style=\"text-decoration:underline\">$name</span></strong>";
	} else if ($status == 'P') {
		return "<strong><em>$name</em></strong>";
	} else if ($status == 'I') {
		return "<strong><em>$name</em></strong>";
	}

	return $name;
}

/*
	Main code
*/
if ($substr) {
	$search = "%$search%";
}


if ($usersearch) {

	$sql = "
		SELECT DISTINCT * 
		FROM users";
	if ( $sys_database_type == "mysql" ) {
		$sql .= "
		WHERE user_id LIKE '$search'
		OR user_name LIKE '%$search%'
		OR email LIKE '%$search%'
		OR realname LIKE '%$search%'"; 
	} else {
		$sql .= "
		WHERE user_id ILIKE '$search'
		OR user_name ILIKE '%$search%'
		OR email ILIKE '%$search%'
		OR realname ILIKE '%$search%'"; 
	}
	$result = db_query($sql);

	print '<p><strong>' .sprintf(ngettext('User search with criteria <em>%1$s</em>: %2$s match', 'User search with criteria <em>%1$s</em>: %2$s matches', $search, db_numrows($result)), db_numrows($result)).'</strong></p>';

	if (db_numrows($result) < 1) {

		echo db_error();

	} else {

		$title=array();
		$title[]=_('ID');
		$title[]=_('User name');
		$title[]=_('Real name');
		$title[]=_('Email');
		$title[]=_('Member since');
		$title[]=_('Status');
					 
		echo $GLOBALS['HTML']->listTableTop($title);

		while ($row = db_fetch_array($result)) {
			print '
				<tr '.$GLOBALS['HTML']->boxGetAltRowStyle($i++).'>
				<td><a href="useredit.php?user_id='.$row['user_id'].'">'.$row['user_id'].'</a></td>
				<td>'.format_name($row['user_name'], $row['status']).'</td>
				<td>'.$row['realname'].'</td>
				<td>'.$row['email'].'</td>
				<td>'.date(_('Y-m-d H:i'), $row['add_date']).'</td>
				<td style="text-align:center">'.format_name($row['status'].'/'.$row['unix_status'], $row['status']).'</td>
				</tr>
			'; 
		}

		echo $GLOBALS['HTML']->listTableBottom();

	} 
} // end if ($usersearch)


if (getStringFromRequest('groupsearch')) {
	$status = getStringFromRequest('status');
	$is_public = getStringFromRequest('is_public');
	$crit_desc = getStringFromRequest('crit_desc');

	if ($status) {
		$crit_sql  .= " AND status='$status'";
		$crit_desc .= " status=$status";
	}
	if ($is_public) {
		$crit_sql  .= " AND is_public='$is_public'";
		$crit_desc .= " is_public=$is_public";
	}

	$sql = "
		SELECT DISTINCT *
		FROM groups";
	if ( $sys_database_type == "mysql" ) {
		$sql .= "
		WHERE (group_id LIKE '%$search%'
		OR unix_group_name LIKE '%$search%'
		OR group_name LIKE '%$search%')
		$crit_sql"; 
	} else {
		$sql .= "
		WHERE (group_id ILIKE '%$search%'
		OR unix_group_name ILIKE '%$search%'
		OR group_name ILIKE '%$search%')
		$crit_sql"; 
	}
	$result = db_query($sql);

	if ($crit_desc) {
		$crit_desc = "($crit_desc )";
	}
	print '<p><strong>' .sprintf(ngettext('Group search with criteria <em>%1$s</em>: %2$s match', 'Group search with criteria <em>%1$s</em>: %2$s matches', $search, db_numrows($result)), db_numrows($result)).'</strong></p>';

	if (db_numrows($result) < 1) {
		echo db_error();
	} else {

		$title=array();
		$title[]=_('ID');
		$title[]=_('Unix name');
		$title[]=_('Full Name');
		$title[]=_('Registered');
		$title[]=_('Status');

		echo $GLOBALS['HTML']->listTableTop($title);

		while ($row = db_fetch_array($result)) {

			$extra_status = "";
			if (!$row['is_public']) {
				$extra_status = "/PRV";
			}
			
			print '
				<tr '.$GLOBALS['HTML']->boxGetAltRowStyle($i++).'>
				<td><a href="groupedit.php?group_id='.$row['group_id'].'">'.$row['group_id'].'</a></td>
				<td>'.format_name($row['unix_group_name'], $row['status']).'</td>
				<td>'.$row['group_name'].'</td>
				<td>'.date(_('Y-m-d H:i'), $row['register_time']).'</td>
				<td style="text-align:center">'.format_name($row['status'].$extra_status, $row['status']).'</td>
				</tr>
			';
					
		}
		
		echo $GLOBALS['HTML']->listTableBottom();

	} 


} //end if($groupsearch)

site_admin_footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
