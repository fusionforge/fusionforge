<?php
/*
 *
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

// The novaforge/auth.php file MUST be included before pre.php,
// in order to properly declare the variables of apibull/config.php
// If not, pre.php will include MantisPlugin.class in a function,
// and the sys_auth_* variables will exist but will eb empty
require_once ("common/novaforge/auth.php");
require_once ("pre.php");
require_once ("common/novaforge/log.php");
require_once ("plugins/mantis/include/gforgefunctions.php");
require_once ("plugins/mantis/include/mantisfunctions.php");

if (session_loggedin () == false)
{
	exit_not_logged_in ();
}
if ((isset ($project_id) == false) || ($project_id <= 0))
{
	exit_error ($Language->getText ("gforge-plugin-mantis", "title_delete"), $Language->getText ("gforge-plugin-mantis", "missing_project_id"));
}
if (getProject ($project_id,
                $group_id,
                $project_url,
                $project_mantis_id,
                $project_name,
                $project_description,
                $project_status,
                $project_visibility,
                $project_css_regex_1,
                $project_css_regex_2,
                $project_css_regex_3,
                $project_css_regex_4) == false)
{
	exit_error ($Language->getText ("gforge-plugin-mantis", "title_delete"), $Language->getText ("gforge-plugin-mantis", "incorrect_project_id"));
}
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
		exit_error ($Language->getText ("gforge-plugin-mantis", "title_delete"), $group->getErrorMessage ());
	}
}
if ($group->usesPlugin ("mantis") == false)
{
	exit_error ($Language->getText ("gforge-plugin-mantis", "title_delete"), $Language->getText ("gforge-plugin-mantis", "not_enabled"));
}
$perm = &$group->getPermission (session_get_user ());
if ($perm->isAdmin () == false)
{
	exit_permission_denied ();
}
$ok = true;
if ((isset ($sure) == true)
&&  ($sure == true)
&&  (isset ($reallysure) == true)
&&  ($reallysure == true))
{
	if (deleteMantisProject ($project_mantis_id,$project_url) == true)
	{
		if (deleteRolesMapping ($project_id) == false)
		{
			$ok = false;
			$error = $Language->getText ("gforge-plugin-mantis", "database_error");
		}
		if (deleteProject ($project_id) == false)
		{
			$ok = false;
			$error .=  $Language->getText ("gforge-plugin-mantis", "not_deleted");
		}
	}
	else
	{
		$ok = false;
		$error = $Language->getText ("gforge-plugin-mantis", "mantis_error", $project_url);
	}
}
else
{
	$ok = false;
	$error =  $Language->getText ("gforge-plugin-mantis", "not_deleted");
}
site_project_header (array ("title" => $Language->getText ("gforge-plugin-mantis", "title_delete"), "group" => $group_id, "toptab" => "admin"));
?>
<h2><? echo $Language->getText ("gforge-plugin-mantis", "title_delete") ?></h2>
<?
if ($ok == true)
{
?>
	<h2><? echo $Language->getText ("gforge-plugin-mantis", "successfully_deleted"); ?></h2>
<?
}
else
{
?>
<p>
<? echo $error; 
}
?>
<p>
<a href="admin.php?group_id=<?php echo $group_id; ?>"><?php echo $Language->getText ("gforge-plugin-mantis", "backto"); ?></a>
<? site_project_footer (array ()); ?>
