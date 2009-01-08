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
require_once ("plugins/mantis/include/synchronizefunctions.php");

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
		exit_error ($Language->getText ("gforge-plugin-mantis", "title_sync"), $group->getErrorMessage ());
	}
}
if ($group->usesPlugin ("mantis") == false)
{
	exit_error ($Language->getText ("gforge-plugin-mantis", "title_sync"), $Language->getText ("gforge-plugin-mantis", "not_enabled"));
}
$perm = &$group->getPermission (session_get_user ());
if ($perm->isAdmin () == false)
{
	exit_permission_denied ();
}
site_project_header (array ("title" => $Language->getText ("gforge-plugin-mantis", "title_sync"), "group" => $group_id, "toptab" => "admin"));
if (synchronize ($group_id, $array_errors) == true)
{
	echo "<h2>" . $Language->getText ("gforge-plugin-mantis", "sync_success") . "</h2>";
}
else
{
	echo "<h2>" . $Language->getText ("gforge-plugin-mantis", "sync_failed") . "</h2>";
	if (count ($array_errors) > 0)
	{
		echo "<ul>\n";
		$index = 0;
		while ($index < count ($array_errors))
		{
			echo "<li>" . $array_errors [$index] . "</li>\n";
			$index++;
		}
		echo "</ul>\n";
	}
}
?>
<p>
<a href="admin.php?group_id=<? echo $group_id; ?>"><? echo $Language->getText ("gforge-plugin-mantis", "backto"); ?></a>
<? site_project_footer (array ()); ?>
