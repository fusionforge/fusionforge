<?php
/**
 * List of all groups in the system. 
 *
 * Copyright 1999-2000 (c) The SourceForge Crew
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
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'admin/admin_utils.php';

site_admin_header(array('title'=>_('Project List')));
echo '<h1>' . _('Project List') . '</h1>';

$form_catroot = getStringFromRequest('form_catroot');
$form_pending = getStringFromRequest('form_pending');
$sortorder = getStringFromRequest('sortorder');
$group_name_search = getStringFromRequest('group_name_search');
$status = getStringFromRequest('status');

// start from root if root not passed in
if (!$form_catroot) {
	$form_catroot = 1;
}

$sortorder = util_ensure_value_in_set ($sortorder,
				       array ('group_name',
					      'register_time',
					      'unix_group_name',
					      'status',
					      'is_public',
					      'license_name',
					      'members')) ;

if ($form_catroot == 1) {
	if (isset($group_name_search)) {
		echo "<p>"._('Groups that begin with'). " <strong>".$group_name_search."</strong></p>\n";
		$res = db_query_params ('SELECT group_name,register_time,unix_group_name,groups.group_id,is_public,status,license_name,COUNT(user_group.group_id) AS members
FROM groups
LEFT JOIN user_group ON user_group.group_id=groups.group_id, licenses
WHERE license_id=license
AND lower(group_name) LIKE $1
AND (status=$2 OR 1!=$3)
GROUP BY group_name,register_time,unix_group_name,groups.group_id,is_public,status,license_name
ORDER BY '.$sortorder,
					array (strtolower ("$group_name_search%"),
					       'P',
					       $form_pending ? 1 : 0)) ;
	} else {
		$qpa = db_construct_qpa (false, 'SELECT group_name,register_time,unix_group_name,groups.group_id,is_public,status,license_name, COUNT(user_group.group_id) AS members FROM groups LEFT JOIN user_group ON user_group.group_id=groups.group_id, licenses WHERE license_id=license') ;
		if ($status) {
			$qpa = db_construct_qpa ($qpa, ' AND status=$1', array ($status)) ;
		}
		$qpa = db_construct_qpa ($qpa, ' GROUP BY group_name,register_time,unix_group_name,groups.group_id,is_public,status,license_name ORDER BY '.$sortorder) ;
		$res = db_query_qpa ($qpa) ;
	}
} else {
	echo "<p>"._('Group List for Category:').' ';
	echo "<strong>" . category_fullname($form_catroot) . "</strong></p>\n";
	$res = db_query_params('SELECT groups.group_name,groups.register_time,groups.unix_group_name,groups.group_id,groups.is_public,licenses.license_name,groups.status, count(user_group.group_id) AS members FROM groups LEFT JOIN user_group ON user_group.group_id=groups.group_id,group_category,licenses WHERE groups.group_id=group_category.group_id AND group_category.category_id=$1 AND licenses.license_id=groups.license GROUP BY group_name,register_time,unix_group_name,groups.group_id,is_public,status,license_name ORDER BY '.$sortorder,
			       array($form_catroot));
}

$headers = array(
	_('Group Name (click to edit)'),
	_('Register Time'),
	_('Unix name'),
	_('Status'),
	_('Public?'),
	_('License'),
	_('Members')
);

$headerLinks = array(
	'/admin/grouplist.php?sortorder=group_name',
	'/admin/grouplist.php?sortorder=register_time',
	'/admin/grouplist.php?sortorder=unix_group_name',
	'/admin/grouplist.php?sortorder=status',
	'/admin/grouplist.php?sortorder=is_public',
	'/admin/grouplist.php?sortorder=license_name',
	'/admin/grouplist.php?sortorder=members'
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
		$time_display = date(_('Y-m-d H:i'),$grp['register_time']);
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

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
