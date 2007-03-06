<?php
/**
 * List of all groups in the system. 
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

site_admin_header(array('title'=>$Language->getText('admin_grouplist','group_list')));

$form_catroot = getStringFromRequest('form_catroot');
$form_pending = getStringFromRequest('form_pending');
$sortorder = getStringFromRequest('sortorder');
$group_name_search = getStringFromRequest('group_name_search');
$status = getStringFromRequest('status');

// start from root if root not passed in
if (!$form_catroot) {
	$form_catroot = 1;
}

if (!isset($sortorder) || empty($sortorder)) {
	$sortorder = "group_name";
}
if ($form_catroot == 1) {
	if (isset($group_name_search)) {
		echo "<p>".$Language->getText('admin_grouplist','groups_that_begin_with'). " <strong>".$group_name_search."</strong></p>\n";
		$res = db_query("SELECT group_name,register_time,unix_group_name,groups.group_id,is_public,status,license_name,COUNT(user_group.group_id) AS members "
			. "FROM groups LEFT JOIN user_group ON user_group.group_id=groups.group_id, licenses WHERE license_id=license AND group_name ILIKE '$group_name_search%' "
			. "GROUP BY group_name,register_time,unix_group_name,groups.group_id,is_public,status,license_name "
			. ($form_pending?"AND WHERE status='P' ":"")
			. " ORDER BY $sortorder");
	} else {
		$res = db_query("SELECT group_name,register_time,unix_group_name,groups.group_id,is_public,status,license_name, COUNT(user_group.group_id) AS members "
			. "FROM groups LEFT JOIN user_group ON user_group.group_id=groups.group_id, licenses "
			. "WHERE license_id=license "
			. ($status?"AND status='$status' ":"")
			. "GROUP BY group_name,register_time,unix_group_name,groups.group_id,is_public,status,license_name "
			. "ORDER BY $sortorder");
	}
} else {
	echo "<p>".$Language->getText('admin_grouplist','group_list_for_category').' ';
	echo "<strong>" . category_fullname($form_catroot) . "</strong></p>\n";
	$res = db_query("SELECT groups.group_name,groups.register_time,groups.unix_group_name,groups.group_id,"
		. "groups.is_public,"
		. "licenses.license_name,"
		. "groups.status "
		. "COUNT(user_group.group_id) AS members "
		. "FROM groups LEFT JOIN user_group ON user_group.group_id=groups.group_id,group_category,licenses "
		. "WHERE groups.group_id=group_category.group_id AND "
		. "group_category.category_id=".$form_catroot." AND "
		. "licenses.license_id=groups.license "
		. "GROUP BY group_name,register_time,unix_group_name,groups.group_id,is_public,status,license_name "
		. "ORDER BY $sortorder");
}

$headers = array(
	$Language->getText('admin_grouplist','group_name_click_to_edit'),
	$Language->getText('admin_grouplist','register_time'),
	$Language->getText('admin_grouplist','unix_name'),
	$Language->getText('admin_grouplist','status'),
	$Language->getText('admin_grouplist','public'),
	$Language->getText('admin_grouplist','license'),
	$Language->getText('admin_grouplist','members')
);

$headerLinks = array(
	'?sortorder=group_name',
	'?sortorder=register_time',
	'?sortorder=unix_group_name',
	'?sortorder=status',
	'?sortorder=is_public',
	'?sortorder=license_name',
	'?sortorder=members'
);

echo $HTML->listTableTop($headers, $headerLinks);

$i = 0;
while ($grp = db_fetch_array($res)) {

	if ($grp['status']=='A'){
		$status="active";
	}
	if ($grp['status']=='P'){
		$status="pending";
	}
	if ($grp['status']=='D'){
		$status="deleted";
	}
	
	$time_display = "";
	if ($grp['register_time'] != 0) {
		$time_display = date($sys_datefmt,$grp['register_time']);
	}
	echo '<tr '.$HTML->boxGetAltRowStyle($i).'>';
	echo '<td><a href="groupedit.php?group_id='.$grp['group_id'].'">'.$grp['group_name'].'</a></td>';
	echo '<td>'.$time_display.'</td>';
	echo '<td>'.$grp['unix_group_name'].'</td>';
	echo '<td class="'.$status.'">'.$grp['status'].'</td>';
	echo '<td>'.$grp['is_public'].'</td>';
	echo '<td>'.$grp['license_name'].'</td>';
	echo '<td>'.$grp['members'].'</td>';
	echo '</tr>';
	$i++;
}

echo $HTML->listTableBottom();

site_admin_footer(array());

?>
