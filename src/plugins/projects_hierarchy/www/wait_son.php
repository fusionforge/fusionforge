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

session_require_perm('project_admin', $group_id);
//update the link when the son allow the father
db_begin();
db_query_params('UPDATE plugin_projects_hierarchy SET activated = true WHERE project_id  = $1 AND sub_project_id = $2',
		array($group_id, $sub_group_id)
	) or die(db_error());
db_commit();

$project = group_get_object($group_id);
$subproject = group_get_object($sub_group_id);

//plugin webcal
$params[0] = $sub_group_id;
$params[1] = $group_id;

plugin_hook('add_cal_link_father',$params);

// send mail to admin of the parent project for share knowledge
$message = sprintf(_('New Parent Relation Validated 

Parent Project Full Name: %1$s
Child Project Full Name: %2$s'),
			$project->getPublicName(),
			$subproject->getPublicName());

$admins = $project->getAdmins();
if (count($admins) < 1) {
	$project->setError(_("There is no administrator to send the mail."));
}

foreach ($admins as $u) {
	util_send_message($u->getEmail(),
			sprintf(_('New Parent %1$s Relation Validated'),
				$project->getPublicName()),
			$message);
}

header("Location: ".util_make_url ('/project/admin/index.php?group_id='.$sub_group_id));
?>
