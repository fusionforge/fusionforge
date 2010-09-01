<?php
/**
 * Copyright 2004 (c) GForge LLC
 * Copyright 2006 (c) Fabien Regnier - Sogeti
 * Copyright 2010 (c) Franck Villaume - Capgemini
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

require_once('../../env.inc.php');    
require_once $gfcommon.'include/pre.php';

$group_id = getIntFromRequest('group_id');
$sub_group_id = getIntFromRequest('sub_group_id');

session_require_perm ('project_admin', $group_id) ;
//update the link when the son allow the father
db_begin();
db_query_params ('UPDATE plugin_projects_hierarchy SET activated = true WHERE project_id  = $1 AND sub_project_id = $2',
		 array ($group_id, $sub_group_id)
	) or die(db_error());
db_commit();

//plugin webcal
$params[0] =  $sub_group_id;
$params[1] =  $group_id;

plugin_hook('add_cal_link_father',$params);

// send mail to admin of the parent project for share knowledge
$project_name_res = db_query_params ('SELECT group_name from groups where group_id=$1',
                                     array ( $group_id ) );
echo db_error();
$row =& db_fetch_array($project_name_res);

$project_name = $row['group_name'];

$child_project_name_res = db_query_params ('SELECT group_name from groups where group_id=$1',
                                     array ( $sub_group_id ) );
echo db_error();
$row =& db_fetch_array($child_project_name_res);

$child_project_name = $row['group_name'];

$message = sprintf(_('New Parent Relation Validated 

Parent Project Full Name: %1$s
Child Project Full Name: %2$s'), $project_name,$child_project_name);

$res = db_query_params ('SELECT users.email, users.language, users.user_id
                         FROM users, user_group
                         WHERE group_id=$1 
                         AND user_group.admin_flags=$2
                         AND users.user_id=user_group.user_id',
                         array ($group_id,'A'));

if (db_numrows($res) < 1) {
    $this->setError(_("There is no administrator to send the mail."));
    return false;
}

for ($i=0; $i<db_numrows($res) ; $i++) {
    $admin_email = db_result($res,$i,'email') ;

    util_send_message($admin_email,sprintf('New Parent %1$s Relation Validated', $project_name), $message);
}

header("Location: ".util_make_url ('/project/admin/index.php?group_id='.$sub_group_id));
?>
