<?
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
		exit_error ($Language->getText ("gforge-plugin-mantis", "title_create"), $group->getErrorMessage ());
	}
}
if ($group->usesPlugin ("mantis") == false)
{
	exit_error ($Language->getText ("gforge-plugin-mantis", "title_create"), $Language->getText ("gforge-plugin-mantis", "not_enabled"));
}
$perm = &$group->getPermission (session_get_user ());
if ($perm->isAdmin () == false)
{
	exit_permission_denied ();
}
$name = trim ($name);
$description = trim ($description);
$url = trim ($url);
$ok = false;
$error = "";
$project_id = 0;
if ((strpos ($name, ">") !== false)
||  (strpos ($name, "<") !== false)
||  (strpos ($description, ">") !== false)
||  (strpos ($description, "<") !== false))
{
	$error = $Language->getText ("gforge-plugin-mantis", "create_forbidden_characters");
}
else
{
	if (createMantisProject ($url,
	                         $mantis_id,
	                         $name,
	                         $description,
	                         $status,
	                         $visibility) == true)
	{
		if (createProject ($group_id,
		                   $url,
		                   $mantis_id,
		                   $name,
		                   $description,
		                   $status,
		                   $visibility,
		                   $css_regex_1,
		                   $css_regex_2,
		                   $css_regex_3,
		                   $css_regex_4,
		                   $project_id) == true)
		{
			$ok = true;
		}
		else
		{
			$error = $Language->getText ("gforge-plugin-mantis", "database_error");
		}
	}
	else
	{
		$error = $Language->getText ("gforge-plugin-mantis", "mantis_error", $url);
	}
}
if ($ok == true)
{
	$url = "http";
	if ($sys_use_ssl == true)
	{
		$url .= "s";
	}
	$url .= "://" . $sys_default_domain . "/plugins/mantis/editRoles.php?project_id=" . $project_id;
	header ("Location: " . $url);
}
else
{
	site_project_header (array ("title" => $Language->getText ("gforge-plugin-mantis", "title_create"), "group" => $group_id, "toptab" => "admin"));
?>
<h2><? echo $Language->getText ("gforge-plugin-mantis", "title_create"); ?></h2>
<p>
<? echo $error; ?>
<p>
<a href="admin.php?group_id=<? echo $group_id; ?>"><? echo $Language->getText ("gforge-plugin-mantis", "backto"); ?></a>

<?
	site_project_footer (array ());
}
?>
