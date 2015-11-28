<?php
/**
 * List of all groups in the system.
 *
 * Copyright 1999-2000 (c) The SourceForge Crew
 * Copyright 2013,2015 Franck Villaume - TrivialDev
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

site_admin_header(array('title'=>_('Project List')));

$sortorder = getStringFromRequest('sortorder', 'group_name');
$group_name_search = getStringFromRequest('group_name_search');
$status = getStringFromRequest('status');
$usingplugin = getStringFromRequest('usingplugin');
$filter = '';

$sortorder = util_ensure_value_in_set($sortorder,
				       array ('group_name',
					      'register_time',
					      'unix_group_name',
					      'status',
					      'is_public',
					      'license_name',
					      'members',
					      'is_template')) ;

if ($sortorder == 'is_public') {
	$sortorder = 'group_name';
} elseif ($sortorder == 'is_template' || $sortorder == 'members') {
	$sortorder .= ' DESC';
}


$sqlsortorder = $sortorder;
if ($usingplugin) {
	$filter='&usingplugin='.$usingplugin;
	echo "<p>"._('Projects that use plugin'). " <strong>".$usingplugin."</strong></p>\n";
	$qpa = db_construct_qpa(false, 'SELECT group_name,register_time,unix_group_name,groups.group_id,groups.is_template,status,license_name,COUNT(DISTINCT(pfo_user_role.user_id)) AS members FROM groups LEFT OUTER JOIN pfo_role ON pfo_role.home_group_id=groups.group_id LEFT OUTER JOIN pfo_user_role ON pfo_user_role.role_id=pfo_role.role_id, licenses WHERE license_id=license and groups.group_id in (SELECT group_plugin.group_id from group_plugin where group_plugin.plugin_id = (SELECT plugins.plugin_id FROM plugins where plugins.plugin_name = $1)) GROUP BY group_name,register_time,unix_group_name,groups.group_id,groups.is_template,status,license_name',
				array(strtolower($usingplugin)));
	$qpa = db_construct_qpa($qpa, ' ORDER BY '.$sqlsortorder);
	$res = db_query_qpa($qpa);
} else if  ($group_name_search != '') {
	$filter='&group_name_search='.$group_name_search;
	echo "<p>"._('Projects that begin with'). " <strong>".$group_name_search."</strong></p>\n";
	$res = db_query_params('SELECT group_name,register_time,unix_group_name,groups.group_id,groups.is_template,status,license_name,COUNT(DISTINCT(pfo_user_role.user_id)) AS members FROM groups LEFT OUTER JOIN pfo_role ON pfo_role.home_group_id=groups.group_id LEFT OUTER JOIN pfo_user_role ON pfo_user_role.role_id=pfo_role.role_id, licenses WHERE license_id=license AND lower(group_name) LIKE $1 GROUP BY group_name,register_time,unix_group_name,groups.group_id,groups.is_template,status,license_name ORDER BY '.$sqlsortorder,
				array(strtolower ("$group_name_search%")));
} else {
	$qpa = db_construct_qpa(false, 'SELECT group_name,register_time,unix_group_name,groups.group_id,groups.is_template,status,license_name,COUNT(DISTINCT(pfo_user_role.user_id)) AS members FROM groups LEFT OUTER JOIN pfo_role ON pfo_role.home_group_id=groups.group_id LEFT OUTER JOIN pfo_user_role ON pfo_user_role.role_id=pfo_role.role_id, licenses WHERE license_id=license') ;
	if ($status) {
		$qpa = db_construct_qpa($qpa, ' AND status=$1', array($status));
	}
	$qpa = db_construct_qpa($qpa, ' GROUP BY group_name,register_time,unix_group_name,groups.group_id,groups.is_template,status,license_name ORDER BY '.$sqlsortorder);
	$res = db_query_qpa($qpa);
}

$headers = array(
	_('Project Name'),
	_('Register Time'),
	_('Unix Name'),
	_('Status'),
	_('Public?'),
	_('License'),
	_('Members'),
	_('Template?')
);

$headerLinks = array(
	'/admin/grouplist.php?sortorder=group_name'.$filter,
	'/admin/grouplist.php?sortorder=register_time'.$filter,
	'/admin/grouplist.php?sortorder=unix_group_name'.$filter,
	'/admin/grouplist.php?sortorder=status'.$filter,
	'/admin/grouplist.php?sortorder=is_public'.$filter,
	'/admin/grouplist.php?sortorder=license_name'.$filter,
	'/admin/grouplist.php?sortorder=members'.$filter,
	'/admin/grouplist.php?sortorder=is_template'.$filter
);

$headerClass = array(
	'',
	'',
	'',
	'',
	'',
	'',
	'',
	'',
);

$headerTitle = array(
	_('Order by project name.'),
	_('Order by register time.'),
	_('Order by Unix name.'),
	_('Order by status.'),
	_('Order by public visibility.'),
	_('Order by licence type.'),
	_('Order by number of members.'),
	_('Order by is the project a template.')
);

echo $HTML->listTableTop($headers, $headerLinks, '', '', $headerClass, $headerTitle);

$rows = array();
$private_rows = array();
$public_rows = array();
$ra = RoleAnonymous::getInstance();
while ($grp = db_fetch_array($res)) {
	if ($ra->hasPermission('project_read', $grp['group_id'])) {
		$grp['is_public'] = 1;
		if (getStringFromRequest('sortorder') == 'is_public') {
			$public_rows[] = $grp;
		}
	} else {
		$grp['is_public'] = 0;
		if (getStringFromRequest('sortorder') == 'is_public') {
			$private_rows[] = $grp;
		}
	}
	if (getStringFromRequest('sortorder') != 'is_public') {
		$rows[] = $grp;
	}
}

if (getStringFromRequest('sortorder') == 'is_public') {
	$rows = array_merge($public_rows, $private_rows);
}

$i = 1;
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
	$cells = array();
	$cells[][] = util_make_link('/admin/groupedit.php?group_id='.$grp['group_id'], $grp['group_name'], array('title' => _('Click to edit this project.')));
	$cells[][] = $time_display;
	$cells[][] = $grp['unix_group_name'];
	$cells[] = array($grp['status'], 'class' => $status);
	$cells[][] = $grp['is_public'];
	$cells[][] = $grp['license_name'];
	$cells[][] = $grp['members'];
	$cells[][] = $grp['is_template'];
	echo $HTML->multiTableRow(array('class' => $HTML->boxGetAltRowStyle($i++, true)), $cells);
}

echo $HTML->listTableBottom();

site_admin_footer();

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
