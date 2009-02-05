<?
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

require_once ("../../env.inc.php");
require_once ($gfwww."include/pre.php");
require_once ("plugins/novapub/include/functions.php");

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
		exit_error (dgettext ("gforge-plugin-novapub", "title_create"), $group->getErrorMessage ());
	}
}
if ($group->usesPlugin ("novapub") == false)
{
	exit_error (dgettext ("gforge-plugin-novapub", "title_create"), dgettext ("gforge-plugin-novapub", "not_enabled"));
}
$perm = &$group->getPermission (session_get_user ());
if ($perm->isAdmin () == false)
{
	exit_permission_denied ();
}
if (getPublisherSiteValues ("url", $array_urls) == false)
{
	exit_error (dgettext ("gforge-plugin-novapub", "title_create"), dgettext ("gforge-plugin-novapub", "database_error"));
}
if (count ($array_urls) == 0)
{
	exit_error (dgettext ("gforge-plugin-novapub", "title_create"),dgettext ("gforge-plugin-novapub","no_instance_declared"));
}
if (getPublisherGForgeRoles ($group_id, $array_gforge_roles) == false)
{
	exit_error (dgettext ("gforge-plugin-novapub", "title_create"), dgettext ("gforge-plugin-novapub", "error_reading_gforge_roles"));
}
if (count ($array_gforge_roles) == 0)
{
	exit_error (dgettext ("gforge-plugin-novapub", "title_create"), dgettext ("gforge-plugin-novapub", "undefined_gforge_roles"));
}
site_project_header (array ("title" => dgettext ("gforge-plugin-novapub", "title_create"), "group" => $group_id, "toptab" => "admin"));
?>
<h2><? echo dgettext ("gforge-plugin-novapub", "title_create"); ?></h2>
<form action="create_action.php" method="post">
	<input type="hidden" name="group_id" value="<? echo $group->getID (); ?>">
	<h3><? echo dgettext ("gforge-plugin-novapub", "project_name"); ?></h3>
	<input type=text name="name" size="40" maxlength="128">
	<h3><? echo dgettext ("gforge-plugin-novapub", "project_instance"); ?></h3>
	<select name="url">
<?
for ($i = 0; $i < count ($array_urls); $i++)
{
?>		<option><? echo $array_urls [$i]; ?></option>
<?
}
?>	</select>
	<h3><? echo dgettext ("gforge-plugin-novapub", "project_role"); ?></h3>
	<select name="role_id">
<?
$roleFound = false;
foreach ($array_gforge_roles as $gforge_role_id => $gforge_role_name)
{
?>		<option value="<? echo $gforge_role_id; ?>"><? echo $gforge_role_name; ?></option>
<?
}
?>        </select>
	<p>
	<input type="submit" name="submit" value="<? echo (dgettext ("gforge-plugin-novapub", "submit_create")); ?>">
</form>
<p>
<a href="admin.php?group_id=<? echo $group_id; ?>"><? echo dgettext ("gforge-plugin-novapub", "back_to_admin"); ?></a>
<? site_project_footer (array ()); ?>
