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
	exit_error ($Language->getText ("gforge-plugin-mantis", "title_edit"), $Language->getText ("gforge-plugin-mantis", "missing_project_id"));
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
	exit_error ($Language->getText ("gforge-plugin-mantis", "title_edit"), $Language->getText ("gforge-plugin-mantis", "incorrect_project_id"));
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
		exit_error ($Language->getText ("gforge-plugin-mantis", "title_edit"), $group->getErrorMessage ());
	}
}
if ($group->usesPlugin ("mantis") == false)
{
	exit_error ($Language->getText ("gforge-plugin-mantis", "title_edit"), $Language->getText ("gforge-plugin-mantis", "not_enabled"));
}
$perm = &$group->getPermission (session_get_user ());
if ($perm->isAdmin () == false)
{
	exit_permission_denied ();
}

if (getDefaultEntries ("url", $urls) == false)
{
	exit_error ($Language->getText ("gforge-plugin-mantis", "title_edit"), $Language->getText ("gforge-plugin-mantis", "database_error"));
}
if (count ($urls) == 0)
{
	exit_error ($Language->getText ("gforge-plugin-mantis", "title_create"), $Language->getText ("gforge-plugin-mantis", "no_instance_declared"));
}
site_project_header (array ("title" => $Language->getText ("gforge-plugin-mantis", "title_edit"), "group" => $group_id, "toptab" => "admin"));
?>
<b><? echo $group->getPublicName () . " - " . $Language->getText ("gforge-plugin-mantis", "title_edit"); ?></b>
<form action="editProjectAction.php" method="post">
	<input type="hidden" name="project_id" value="<? echo $project_id ?>">
	<h3><? echo $Language->getText ("gforge-plugin-mantis", "name"); ?></h3>
	<input type=text name="name" size="40" maxlength="128" value="<? echo $project_name ?>">
	<h3><? echo $Language->getText ("gforge-plugin-mantis", "description"); ?></h3>
	<textarea name="description" wrap="virtual" cols="30" rows="2"><? echo $project_description ?></textarea>
	<h3><? echo $Language->getText ("gforge-plugin-mantis", "url"); ?></h3>
	<select name="url">
<?
$serverFound = false;
for ($i=0; $i < count ($urls); $i++)
{
?>		<option<? if ($urls [$i] == $project_url) { echo " selected"; $serverFound = true; } ?>><? echo $urls [$i]; ?></option>
<?
}
?>	</select>
<?
if ($serverFound == false)
{
?>	<br>
	<h4><? echo $Language->getText ("gforge-plugin-mantis", "instance_not_declared", $project_url); ?></h4>
<?
}
?>	<h3><? echo $Language->getText ("gforge-plugin-mantis", "visibility"); ?></h3>
	<input type="radio" name="visibility" value="1"<? if ($project_visibility == 1) { echo " checked"; } ?>><? echo $Language->getText ("gforge-plugin-mantis", "public"); ?><br/>
	<input type="radio" name="visibility" value="0"<? if ($project_visibility != 1) { echo " checked"; } ?>><? echo ($Language->getText ("gforge-plugin-mantis", "private")); ?><br/>
	<h3><? echo $Language->getText ("gforge-plugin-mantis", "status"); ?></h3>
	<select name="status">
		<option value="D"<? if ($project_status == "D") { echo " selected"; } ?>><? echo $Language->getText ("gforge-plugin-mantis", "development"); ?></option>
		<option value="R"<? if ($project_status == "R") { echo " selected"; } ?>><? echo $Language->getText ("gforge-plugin-mantis", "release"); ?></option>
		<option value="S"<? if ($project_status == "S") { echo " selected"; } ?>><? echo $Language->getText ("gforge-plugin-mantis", "stable"); ?></option>
		<option value="O"<? if ($project_status == "O") { echo " selected"; } ?>><? echo $Language->getText ("gforge-plugin-mantis", "obsolete"); ?></option>
	</select>
	<h3><? echo ($Language->getText ("gforge-plugin-mantis", "css")); ?></h3>
	<? echo ($Language->getText ("gforge-plugin-mantis", "css_info")); ?><br/>
	<input type="text" name="css_regex_1" size="80" value="<? echo $project_css_regex_1; ?>"><br/>
	<input type="text" name="css_regex_2" size="80" value="<? echo $project_css_regex_2; ?>"><br/>
	<input type="text" name="css_regex_3" size="80" value="<? echo $project_css_regex_3; ?>"><br/>
	<input type="text" name="css_regex_4" size="80" value="<? echo $project_css_regex_4; ?>"><br/>
	<input type="submit" name="submit" value="<? echo ($Language->getText ("gforge-plugin-mantis", "submit_edit_project")); ?>" />
</form>
<p>
<a href="admin.php?group_id=<? echo $group_id; ?>"><? echo $Language->getText ("gforge-plugin-mantis", "backto"); ?></a>
<? site_project_footer (array ()); ?>
