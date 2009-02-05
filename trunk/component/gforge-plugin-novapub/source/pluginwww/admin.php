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
if (getPublisherProjects ($group->getID (), $array_ids, $array_names) == false)
{
	exit_error (dgettext ("gforge-plugin-novapub", "title_create"), dgettext ("gforge-plugin-novapub", "database_error"));
}
site_project_header (array ("title" => dgettext ("gforge-plugin-novapub", "title_admin"), "group" => $group_id, "toptab" => "admin"));
echo "<h2>" . dgettext ("gforge-plugin-novapub", "title_admin") . "</h2>";
echo $GLOBALS ["HTML"]->boxTop ("<a href=\"create.php?group_id=" .$group->getID () . "\">" . dgettext ("gforge-plugin-novapub", "create_project") . "</a>");
if ((count ($array_ids) > 0) && (count ($array_names) == count ($array_ids)))
{
	echo $GLOBALS ["HTML"]->boxMiddle (dgettext ("gforge-plugin-novapub", "projects_list"));
	echo "<table>";
	$index = 0;
	while ($index < count ($array_ids))
	{
?>
<tr>
<td><b><? echo $array_names [$index]; ?></b></td>
<td><a href="edit.php?project_id=<? echo $array_ids [$index]; ?>"><? echo dgettext ("gforge-plugin-novapub", "edit_project"); ?></a></td>
<td>&nbsp;</td>
<td><a href="delete.php?project_id=<? echo $array_ids [$index]; ?>"><? echo dgettext ("gforge-plugin-novapub", "delete_project"); ?></a></td>
</tr>
<?
		$index++;
	}
	echo "</table>\n";
}
echo $GLOBALS ["HTML"]->boxBottom ();
site_project_footer (array ());
?>
