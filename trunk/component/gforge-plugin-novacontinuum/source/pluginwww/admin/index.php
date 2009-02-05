<?php
/*
 * Novaforge is a registered trade mark from Bull S.A.S
 * Copyright (C) 2007 Bull S.A.S.
 * 
 * http://novaforge.org/
 *
 *
 * This file has been developped within the Novaforge(TM) project from Bull S.A.S
 * and contributed back to GForge community.
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
 * along with this file; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

require_once ("www/env.inc.php");

require_once ("include/pre.php");
require_once ("common/novaforge/log.php");


if ((isset ($group_id) == false) || ($group_id <= 0))
{
	exit_no_group ();
}
$group = &group_get_object ($group_id);
if ((isset ($group) == false) || (is_object ($group) == false))
{
	exit_no_group ();
}
else
{
	if ($group->isError () == true)
	{
		exit_error (dgettext ("gforge-plugin-novacontinuum", "title_admin"), $group->getErrorMessage ());
	}
}
if ($group->usesPlugin ("novacontinuum") == false)
{
	exit_error (dgettext ("gforge-plugin-novacontinuum", "title_admin"), dgettext ("gforge-plugin-novacontinuum", "not_enabled"));
}

require_once('plugins/novacontinuum/include/services/ServicesManager.php');
$serviceManager =& ServicesManager::getInstance();

$selectedRoles = $serviceManager->hasRoleForGroup($group_id,'manage_private_instance');
$selectedRoles = $selectedRoles || $serviceManager->hasRoleForGroup($group_id,'select_instance');
$selectedRoles = $selectedRoles || $serviceManager->hasRoleForGroup($group_id,'manage_build_def');
$selectedRoles = $selectedRoles || $serviceManager->hasRoleForGroup($group_id,'run_build_def');
$selectedRoles = $selectedRoles || $serviceManager->hasRoleForGroup($group_id,'manage_project');
$selectedRoles = $selectedRoles || $serviceManager->hasRoleForGroup($group_id,'run_project');
$selectedRoles = $selectedRoles || $serviceManager->hasRoleForGroup($group_id,'run_continuum_project');
$selectedRoles = $selectedRoles || $serviceManager->hasRoleForGroup($group_id,'show_build_result');
$selectedRoles = $selectedRoles || $serviceManager->hasRoleForGroup($group_id,'show_project_detail');
$selectedRoles = $selectedRoles || $serviceManager->hasRoleForGroup($group_id,'manage_role');
$selectedRoles = $selectedRoles || $serviceManager->hasRoleForGroup($group_id,'view_access');

if (!$selectedRoles)
{
	if (session_loggedin () == false)
	{
		exit_not_logged_in ();
	}else{
		exit_permission_denied ();
	}
}

site_project_header (array ("title" => dgettext ("gforge-plugin-novacontinuum", "title_admin"), "group" => $group_id, "toptab" => "admin"));

require_once 'plugins/novacontinuum/include/admin/controller.php';

?>

<?php

site_project_footer (array ());
?>
