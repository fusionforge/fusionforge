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

require_once('pre.php');
require_once('www/admin/admin_utils.php');

site_admin_header(array('title'=>$Language->getText('admin_grouplist','group_list')));

// start from root if root not passed in
if (!$form_catroot) {
	$form_catroot = 1;
}

//CB removed from 2.6 and 2.5 was link to a page saying to use new project
//echo "<br /><a href=\"groupedit-add.php\">[Add Group]</a>";
echo "<p>".$Language->getText('admin_grouplist','group_list_for_category');

$sortorder = $_GET['sortorder'];
if (!isset($sortorder) || empty($sortorder)) {
	$sortorder = "group_name";
}
if ($form_catroot == 1) {
	if (isset($group_name_search)) {
		echo "<strong>" .$Language->getText('admin_grouplist','groups_that_begin_with'). "$group_name_search</strong>\n";
		$res = db_query("SELECT group_name,register_time,unix_group_name,groups.group_id,is_public,status,license,COUNT(user_group.group_id) AS members "
			. "FROM groups LEFT JOIN user_group ON user_group.group_id=groups.group_id WHERE group_name ILIKE '$group_name_search%' "
			. "GROUP BY group_name,register_time,unix_group_name,groups.group_id,is_public,status,license "
			. ($form_pending?"AND WHERE status='P' ":"")
			. " ORDER BY $sortorder");
	} else {
		echo "<strong>All Categories</strong>\n";
		$res = db_query("SELECT group_name,register_time,unix_group_name,groups.group_id,is_public,status,license, COUNT(user_group.group_id) AS members "
			. "FROM groups LEFT JOIN user_group ON user_group.group_id=groups.group_id "
			. ($status?"WHERE status='$status' ":"")
			. "GROUP BY group_name,register_time,unix_group_name,groups.group_id,is_public,status,license "
			. "ORDER BY $sortorder");
	}
} else {
	echo "<strong>" . category_fullname($form_catroot) . "</strong>\n";
	$res = db_query("SELECT groups.group_name,groups.register_time,groups.unix_group_name,groups.group_id,"
		. "groups.is_public,"
		. "groups.license,"
		. "groups.status "
		. "COUNT(user_group.group_id) AS members "
		. "FROM groups LEFT JOIN user_group ON user_group.group_id=groups.group_id,group_category "
		. "WHERE groups.group_id=group_category.group_id AND "
		. "group_category.category_id=$GLOBALS[form_catroot] "
		. "GROUP BY group_name,register_time,unix_group_name,groups.group_id,is_public,status,license "
		. "ORDER BY $sortorder");
}

?>
</p>
<?php
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
	'?sortorder=license',
	'?sortorder=members'
);

echo $HTML->listTableTop($headers, $headerLinks);

$i = 0;
while ($grp = db_fetch_array($res)) {
	$time_display = "";
	if ($grp['register_time'] != 0) {
		$time_display = date($sys_datefmt,$grp['register_time']);
	}
	echo '<tr '.$HTML->boxGetAltRowStyle($i).'>';
	echo '<td><a href="groupedit.php?group_id='.$grp['group_id'].'">'.$grp['group_name'].'</a></td>';
	echo '<td>'.$time_display.'</td>';
	echo '<td>'.$grp['unix_group_name'].'</td>';
	echo '<td>'.$grp['status'].'</td>';
	echo '<td>'.$grp['is_public'].'</td>';
	echo '<td>'.$grp['license'].'</td>';
	echo '<td>'.$grp['members'].'</td>';
	echo '</tr>';
	$i++;
}

echo $HTML->listTableBottom();

site_admin_footer(array());

?>
