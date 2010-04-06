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

echo '<h1>' . _('Project Member List') . '</h1>';

echo '<p>' . _('If you would like to contribute to this project by becoming a developer, contact one of the project admins, designated in bold text below.') . '</p>';
// beginning of the user descripion block
$project =& group_get_object($group_id);
$project_stdzd_uri = util_make_url_g ($project->getUnixName(), $group_id);
$usergroup_stdzd_uri = $project_stdzd_uri.'members/';
print '<div about="'. $usergroup_stdzd_uri .'" typeof="sioc:UserGroup" xmlns:sioc="http://rdfs.org/sioc/ns#">';
print '<span rel="http://www.w3.org/2002/07/owl#sameAs" resource=""></span>';
print '<span rev="sioc:has_usergroup" resource="'. $project_stdzd_uri . '"></span>';
print '</div>';

$title_arr=array();
$title_arr[]=_('Member');
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
	echo '<tr '.$HTML->boxGetAltRowStyle($i++).'>'."\n";
	// RDFa
	$member_uri = util_make_url_u ($row_memb['user_name'],$row_memb['user_id']);
	print '<div about="'. $member_uri .'" typeof="sioc:UserAccount">';
	print '<span rev="sioc:has_member" resource="'. $usergroup_stdzd_uri .'"></span>';
	print '<span property="sioc:name" content="'. $row_memb['user_name'] .'"></span>';
	if ( trim($row_memb['admin_flags'])=='A' ) {
//                echo '<div rev="doap:developer" typeof="doap:Project" xmlns:doap="http://usefulinc.com/ns/doap#">';
		echo '		<td><strong>'.$row_memb['realname'].'</strong></td>';
//                echo '</div>';
	} else {
//		echo '<div rev="doap:maintainer" typeof="doap:Project" xmlns:doap="http://usefulinc.com/ns/doap#">';
		echo '		<td>'.$row_memb['realname'].'</td>';
//                echo '</div>';
	}
	
	/*
        print '<span property ="dc:Identifier" content="'.$row_memb['user_id'].'" xmlns:dc="http://purl.org/dc/elements/1.1/">';
        echo '</span>';
        print '<span property="foaf:accountName" content="'.$row_memb['user_name'].'">';
        echo '</span>';
        print '<span property="fusionforge:has_job" content="'.$row_memb['role'].'" xmlns:fusionforge="http://fusionforge.org/fusionforge#">';
        echo '</span>';*/
	echo '<td align="center">'.util_make_link_u ($row_memb['user_name'],$row_memb['user_id'],$row_memb['user_name']).'</td>
	<td align="center">'.$row_memb['role'].'</td>';
	if($GLOBALS['sys_use_people']) {
		echo '<td align="center">'.util_make_link ('/people/viewprofile.php?user_id='.$row_memb['user_id'],_('View')).'</td>';
	}
	print '</div>';
   	echo '</tr>';
}
// end of community member description block 
echo $GLOBALS['HTML']->listTableBottom();

site_project_footer(array());

?>
