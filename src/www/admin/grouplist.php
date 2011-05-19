<?php
/**
 * List of all groups in the system. 
 *
 * Copyright 1999-2000 (c) The SourceForge Crew
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

require_once('../env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'admin/admin_utils.php';

site_admin_header(array('title'=>_('Project List')));

$sortorder = getStringFromRequest('sortorder');
$group_name_search = getStringFromRequest('group_name_search');
$status = getStringFromRequest('status');

$sortorder = util_ensure_value_in_set ($sortorder,
				       array ('group_name',
					      'register_time',
					      'unix_group_name',
					      'status',
					      'is_public',
					      'license_name',
					      'members',
					      'is_template')) ;

$sqlsortorder = $sortorder;

if ($sortorder == 'is_public') {
	$sortorder = 'group_name' ;
} elseif ($sortorder == 'is_template') {
	$sortorder = 'is_template DESC' ;
}

if ($group_name_search != '') {
	echo "<p>"._('Projects that begin with'). " <strong>".$group_name_search."</strong></p>\n";
	if (USE_PFO_RBAC) {
		$res = db_query_params ('SELECT group_name,register_time,unix_group_name,groups.group_id,groups.is_template,status,license_name,COUNT(DISTINCT(pfo_user_role.user_id)) AS members FROM groups LEFT OUTER JOIN pfo_role ON pfo_role.home_group_id=groups.group_id LEFT OUTER JOIN pfo_user_role ON pfo_user_role.role_id=pfo_role.role_id, licenses WHERE license_id=license AND lower(group_name) LIKE $1 GROUP BY group_name,register_time,unix_group_name,groups.group_id,groups.is_template,status,license_name ORDER BY '.$sqlsortorder,
					array (strtolower ("$group_name_search%"))) ;
	} else {
		$res = db_query_params ('SELECT group_name,register_time,unix_group_name,groups.group_id,groups.is_public,groups.is_template,status,license_name,COUNT(user_group.group_id) AS members
							FROM groups
							LEFT JOIN user_group ON user_group.group_id=groups.group_id, licenses
							WHERE license_id=license
							AND lower(group_name) LIKE $1
							GROUP BY group_name,register_time,unix_group_name,groups.group_id,groups.is_public,groups.is_template,status,license_name
							ORDER BY '.$sortorder,
							array (strtolower ("$group_name_search%"))) ;
	}
} else {
	if (USE_PFO_RBAC) {
		$qpa = db_construct_qpa (false, 'SELECT group_name,register_time,unix_group_name,groups.group_id,groups.is_template,status,license_name,COUNT(DISTINCT(pfo_user_role.user_id)) AS members FROM groups LEFT OUTER JOIN pfo_role ON pfo_role.home_group_id=groups.group_id LEFT OUTER JOIN pfo_user_role ON pfo_user_role.role_id=pfo_role.role_id, licenses WHERE license_id=license') ;
		if ($status) {
			$qpa = db_construct_qpa ($qpa, ' AND status=$1', array ($status)) ;
		}
		$qpa = db_construct_qpa ($qpa, ' GROUP BY group_name,register_time,unix_group_name,groups.group_id,groups.is_template,status,license_name ORDER BY '.$sqlsortorder) ;
		$res = db_query_qpa ($qpa) ;
	} else {
		$qpa = db_construct_qpa (false, 'SELECT group_name,register_time,unix_group_name,groups.group_id,groups.is_public,groups.is_template,status,license_name,COUNT(user_group.group_id) AS members
								FROM groups
								LEFT JOIN user_group ON user_group.group_id=groups.group_id, licenses
								WHERE license_id=license',
					 array ()) ;
		if ($status) {
			$qpa = db_construct_qpa ($qpa, ' AND status=$1', array ($status)) ;
		}
		$qpa = db_construct_qpa ($qpa, ' GROUP BY group_name,register_time,unix_group_name,groups.group_id,groups.is_public,groups.is_template,status,license_name ORDER BY '.$sortorder) ;
		$res = db_query_qpa ($qpa) ;
	}
}

$headers = array(
	_('Project Name (click to edit)'),
	_('Register Time'),
	_('Unix name'),
	_('Status'),
	_('Public?'),
	_('License'),
	_('Members'),
	_('Template?')
);

$headerLinks = array(
	'/admin/grouplist.php?sortorder=group_name',
	'/admin/grouplist.php?sortorder=register_time',
	'/admin/grouplist.php?sortorder=unix_group_name',
	'/admin/grouplist.php?sortorder=status',
	'/admin/grouplist.php?sortorder=is_public',
	'/admin/grouplist.php?sortorder=license_name',
	'/admin/grouplist.php?sortorder=members',
	'/admin/grouplist.php?sortorder=is_template'
);

echo $HTML->listTableTop($headers, $headerLinks);

if (USE_PFO_RBAC) {
	$public_rows = array();
	$private_rows = array();
	$ra = RoleAnonymous::getInstance() ;
	while ($grp = db_fetch_array($res)) {
		if ($ra->hasPermission('project_read', $grp['group_id'])) {
			$grp['is_public'] = 1;
			$public_rows[] = $grp;
		} else {
			$grp['is_public'] = 0;
			$private_rows[] = $grp;
		}
	}
	$rows = $private_rows;
	array_merge($rows, $public_rows);
} else {
	$rows = array();
	while ($grp = db_fetch_array($res)) {
		$rows[] = $grp;
	}
}

$i = 0;
foreach ($rows as $grp) {

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
	echo '<td>'.$grp['is_template'].'</td>';
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
