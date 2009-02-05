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
if ((isset ($project_id) == false) || ($project_id <= 0))
{
	exit_error (dgettext ("gforge-plugin-novapub", "title_edit"), dgettext ("gforge-plugin-novapub", "missing_project_id"));
}
if (getPublisherProjectById ($project_id,
                             $existing_name,
                             $group_id,
                             $existing_role_id,
                             $existing_url) == false)
{
	exit_error (dgettext ("gforge-plugin-novapub", "title_edit"), dgettext ("gforge-plugin-novapub", "incorrect_project_id"));
}
if ((isset ($group_id) == false) || ($group_id <= 0))
{
	exit_no_group ();
}
$group = & group_get_object ($group_id);
if ((isset ($group) == false) || (is_object ($group) == false))
{
	exit_no_group ();
}
else
{
	if ($group->isError () == true)
	{
		exit_error (dgettext ("gforge-plugin-novapub", "title_edit"), $group->getErrorMessage ());
	}
}
if ($group->usesPlugin ("novapub") == false)
{
	exit_error (dgettext ("gforge-plugin-novapub", "title_edit"), dgettext ("gforge-plugin-novapub", "not_enabled"));
}
$perm = &$group->getPermission (session_get_user ());
if ($perm->isAdmin () == false)
{
	exit_permission_denied ();
}
site_project_header (array ("title" => dgettext ("gforge-plugin-novapub", "title_edit"), "group" => $group_id, "toptab" => "admin"));
?>
<h2><? echo dgettext ("gforge-plugin-novapub", "title_edit"); ?></h2>
<p>
<h3><?
if (updatePublisherProject ($project_id, $name, $role_id, $url) == true)
{
	echo sprintf( dgettext ( "gforge-plugin-novapub" ,  "edit_project_success" ) , $existing_name);
}
else
{
	echo sprintf( dgettext ( "gforge-plugin-novapub" ,  "edit_project_failure" ) , $existing_name);
}
?></h3>
<p>
<a href="admin.php?group_id=<? echo $group_id; ?>"><? echo dgettext ("gforge-plugin-novapub", "back_to_admin"); ?></a>
<? site_project_footer (array ()); ?>
