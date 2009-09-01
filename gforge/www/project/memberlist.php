<?php
/**
 * Project Members Information
 *
 * Portions Copyright 1999-2001 (c) VA Linux Systems
 * The rest Copyright 2002-2004 (c) GForge Team
 * http://gforge.org/
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
require_once $gfwww.'include/pre.php';

$group_id = getIntFromGet("group_id");
$form_grp = getIntFromGet("form_grp");

if (!$group_id && $form_grp) {
	$group_id = $form_grp;
}

site_project_header(array('title'=>_('Project Member List'),'group'=>$group_id,'toptab'=>'memberlist'));

echo _('<p>If you would like to contribute to this project by becoming a developer, contact one of the project admins, designated in bold text below.</p>');

$title_arr=array();
$title_arr[]=_('Developer');
$title_arr[]=_('Username');
$title_arr[]=_('Role/Position');
if($GLOBALS['sys_use_people']) {
	$title_arr[]=_('Skills');
}

echo $GLOBALS['HTML']->listTableTop ($title_arr);

// list members
$res_memb = db_query_params("SELECT users.*,user_group.admin_flags,role.role_name AS role
	FROM users,user_group
	LEFT JOIN role ON user_group.role_id=role.role_id
	WHERE users.user_id=user_group.user_id
	AND user_group.group_id=$1
	AND users.status='A'
	ORDER BY users.user_name ", array($group_id));
$i=0;
while ( $row_memb=db_fetch_array($res_memb) ) {
	echo '<tr '.$HTML->boxGetAltRowStyle($i++).'>';
	if ( trim($row_memb['admin_flags'])=='A' ) {
		echo '		<td><strong>'.$row_memb['realname'].'</strong></td>';
	} else {
		echo '		<td>'.$row_memb['realname'].'</td>';
	}

	echo '<td align="center">'.util_make_link_u ($row_memb['user_name'],$row_memb['user_id'],$row_memb['user_name']).'</td>
	<td align="center">'.$row_memb['role'].'</td>';
	if($GLOBALS['sys_use_people']) {
		echo '<td align="center">'.util_make_link ('/people/viewprofile.php?user_id='.$row_memb['user_id'],_('View')).'</td>';
	}
	echo '</tr>';
}

echo $GLOBALS['HTML']->listTableBottom();

site_project_footer(array());

?>
