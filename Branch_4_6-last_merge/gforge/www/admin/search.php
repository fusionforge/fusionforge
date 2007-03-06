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


require_once('pre.php');
require_once('www/admin/admin_utils.php');

$search = getStringFromRequest('search');
$substr = getStringFromRequest('substr');
$usersearch = getStringFromRequest('usersearch');

if (!$search) {
	exit_error($Language->getText('general','error'), $Language->getText('admin_search','refusing_to_display_whole_db'));
}

site_admin_header(array('title'=>$Language->getText('admin_search','index')));

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

	$result = db_query("
	    SELECT DISTINCT * 
	    FROM users
	    WHERE user_id ILIKE '$search'
	    OR user_name ILIKE '%$search%'
	    OR email ILIKE '%$search%'
	    OR realname ILIKE '%$search%'
	"); 

	print '<p><strong>' .$Language->getText('admin_search','user_search_criteria').' "<em>'.$search.'</em>": '
	      .db_numrows($result) .' '. $Language->getText('admin_search','matches').'</strong></p>';

	if (db_numrows($result) < 1) {

		echo db_error();

	} else {

		$title=array();
		$title[]=$Language->getText('admin_search','id');
		$title[]=$Language->getText('admin_search','username');
		$title[]=$Language->getText('admin_search','real_name');
		$title[]=$Language->getText('admin_search','email');
		$title[]=$Language->getText('admin_search','member_since');
		$title[]=$Language->getText('admin_search','status');
					 
		echo $GLOBALS['HTML']->listTableTop($title);

		while ($row = db_fetch_array($result)) {
			print '
				<tr '.$GLOBALS['HTML']->boxGetAltRowStyle($i++).'>
				<td><a href="useredit.php?user_id='.$row['user_id'].'">'.$row['user_id'].'</a></td>
				<td>'.format_name($row['user_name'], $row['status']).'</td>
				<td>'.$row['realname'].'</td>
				<td>'.$row['email'].'</td>
				<td>'.date($sys_datefmt, $row['add_date']).'</td>
				<td align="center">'.format_name($row['status'].'/'.$row['unix_status'], $row['status']).'</td>
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

	$result = db_query("
		SELECT DISTINCT *
		FROM groups
		WHERE (group_id ILIKE '%$search%'
		OR unix_group_name ILIKE '%$search%'
		OR group_name ILIKE '%$search%')
		$crit_sql
	"); 

	if ($crit_desc) {
		$crit_desc = "($crit_desc )";
	}
	print '<p><strong>' .$Language->getText('admin_search','group_search_criteria').'"<em>'.$search.'</em>" '.$crit_desc.': '
	      .db_numrows($result).' '.$Language->getText('admin_search','matches').'</strong></p>';

	if (db_numrows($result) < 1) {
		echo db_error();
	} else {

		$title=array();
		$title[]=$Language->getText('admin_search','id');
		$title[]=$Language->getText('admin_search','unix_name');
		$title[]=$Language->getText('admin_search','full_name');
		$title[]=$Language->getText('admin_search','registered');
		$title[]=$Language->getText('admin_search','status');

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
				<td>'.date($sys_datefmt, $row['register_time']).'</td>
				<td align="center">'.format_name($row['status'].$extra_status, $row['status']).'</td>
				</tr>
			';
					
		}
		
		echo $GLOBALS['HTML']->listTableBottom();

	} 


} //end if($groupsearch)

site_admin_footer(array());

?>
