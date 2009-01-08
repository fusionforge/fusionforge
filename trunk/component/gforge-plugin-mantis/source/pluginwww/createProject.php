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
if ((getDefaultEntries ("url", $urls) == false)
||  (getDefaultEntry ("visibility", $visibility) == false)
||  (getDefaultEntry ("status", $status) == false)
||  (getDefaultEntry ("css_regex_1", $css_regex_1) == false)
||  (getDefaultEntry ("css_regex_2", $css_regex_2) == false)
||  (getDefaultEntry ("css_regex_3", $css_regex_3) == false)
||  (getDefaultEntry ("css_regex_4", $css_regex_4) == false))
{
	exit_error ($Language->getText ("gforge-plugin-mantis", "title_create"), $Language->getText ("gforge-plugin-mantis", "database_error"));
}
if (count ($urls) == 0)
{
	exit_error ($Language->getText ("gforge-plugin-mantis", "title_create"), $Language->getText ("gforge-plugin-mantis","no_instance_declared"));
}
site_project_header (array ("title" => $Language->getText ("gforge-plugin-mantis", "title_create"), "group" => $group_id, "toptab" => "admin"));
?>
<h2><? echo $Language->getText ("gforge-plugin-mantis", "title_create"); ?></h2>
<form action="createProjectAction.php" method="post">
	<input type="hidden" name="group_id" value="<? echo $group->getID () ?>" />
	<h3><? echo $Language->getText ("gforge-plugin-mantis", "name"); ?></h3>
	<input type="text" name="name" size="40" maxlength="128">
	<h3><? echo $Language->getText("gforge-plugin-mantis", "description"); ?></h3>
	<textarea name="description" wrap="virtual" cols="30" rows="2"></textarea>
	<h3><? echo $Language->getText ("gforge-plugin-mantis", "url"); ?></h3>
	<select name="url">
<?
		for ($i=0; $i < count ($urls); $i++)
		{
?>		<option><? echo $urls [$i]; ?></option>
<?
		}
?>
	</select>
	<h3><? echo $Language->getText ("gforge-plugin-mantis", "visibility"); ?></h3>
	<input type="radio" name="visibility" value="1"<? if ($visibility == 1) { echo " checked"; } ?>><? echo $Language->getText ("gforge-plugin-mantis", "public"); ?><br/>
	<input type="radio" name="visibility" value="0"<? if ($visibility != 1) { echo " checked"; } ?>><? echo ($Language->getText ("gforge-plugin-mantis", "private")); ?><br/>

	<h3><? echo $Language->getText ("gforge-plugin-mantis", "status"); ?></h3>
	<select name="status">
		<option value="D"<? if ($status == "D") { echo " selected"; } ?>><? echo $Language->getText ("gforge-plugin-mantis", "development"); ?></option>
		<option value="R"<? if ($status == "R") { echo " selected"; } ?>><? echo $Language->getText ("gforge-plugin-mantis", "release"); ?></option>
		<option value="S"<? if ($status == "S") { echo " selected"; } ?>><? echo $Language->getText ("gforge-plugin-mantis", "stable"); ?></option>
		<option value="O"<? if ($status == "O") { echo " selected"; } ?>><? echo $Language->getText ("gforge-plugin-mantis", "obsolete"); ?></option>
	</select>
	<h3><? echo ($Language->getText ("gforge-plugin-mantis", "css")); ?></h3>
	<? echo ($Language->getText ("gforge-plugin-mantis", "css_info")); ?><br/>
	<input type=text name="css_regex_1" size="80" value="<? echo $css_regex_1; ?>"><br/>
	<input type=text name="css_regex_2" size="80" value="<? echo $css_regex_2; ?>"><br/>
	<input type=text name="css_regex_3" size="80" value="<? echo $css_regex_3; ?>"><br/>
	<input type=text name="css_regex_4" size="80" value="<? echo $css_regex_4; ?>"><br/>
	<input type="submit" name="submit" value="<? echo ($Language->getText ("gforge-plugin-mantis", "submit_create_project")); ?>" />
</form>
<p>
<a href="admin.php?group_id=<? echo $group_id; ?>"><? echo $Language->getText ("gforge-plugin-mantis", "backto"); ?></a>
<? site_project_footer (array ()); ?>
