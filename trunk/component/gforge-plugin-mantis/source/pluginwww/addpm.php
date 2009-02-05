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

require_once ('../../env.inc.php');
require_once ($gfwww.'include/pre.php');
require_once ("www/pm/include/ProjectGroupHTML.class.php");
require_once ("common/novaforge/log.php");
require_once ("plugins/mantis/include/gforgefunctions.php");

if (session_loggedin () == false)
{
	exit_not_logged_in ();
}
if ((isset ($mantis_url) == false) || (strlen ($mantis_url) <= 0)
||  (isset ($mantis_id) == false) || (strlen ($mantis_id) <= 0)
||  (isset ($bug_id) == false) || (strlen ($bug_id) <= 0))
{
	exit_missing_param ();
}
$result = db_query ("SELECT project_id FROM plugin_mantis_project WHERE url='" . $mantis_url . "' AND mantis_id='" . $mantis_id . "'");
if (($result === false) || (db_numrows ($result) != 1))
{
	exit_error (dgettext ("pm_addtask", "title"), dgettext ("gforge-plugin-mantis", "incorrect_project_id"));
}
else
{
	$project_id = db_result ($result, 0, "project_id");
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
	exit_error (dgettext ("pm_addtask", "title"), dgettext ("gforge-plugin-mantis", "incorrect_project_id"));
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
		exit_error (dgettext ("pm_addtask", "title"), $group->getErrorMessage ());
	}
}
if ($group->usesPlugin ("mantis") == false)
{
	exit_error (dgettext ("pm_addtask", "title"), dgettext ("gforge-plugin-mantis", "not_enabled"));
}
$result = db_query ("SELECT role_id FROM user_group WHERE user_id=" . user_getid () . " and group_id=" . $group->getID ());
if (($result === false) || (db_numrows ($result) != 1))
{
	exit_error (dgettext ("pm_addtask", "title"), dgettext ("gforge-plugin-mantis", "database_error"));
}
else
{
	$role = & new Role ($group, db_result ($result, 0, "role_id"));
}
$array_trackers = array ();
if (array_key_exists ("pm", $role->role_values) == true)
{
	$query = "SELECT group_project_id,project_name FROM project_group_list WHERE group_id='" . $group->getID () . "'";
	$result = db_query ($query);
	if ($result === false)
	{
		exit_error (dgettext ("pm_addtask", "title"), dgettext ("gforge-plugin-mantis", "database_error"));
	}
	else
	{
		for ($i = 0; $i < db_numrows ($result); $i++)
		{
			$task_id = db_result ($result, $i, "group_project_id");
			// si le user a les droits administratif ou la selectionne
			// 3 = technicien et administrateur
			// 4 =administrateur
			if ( (($role->getVal ('pm', $task_id)+1) == '3')
			|| (($role->getVal ('pm', $task_id)+1) == '4'))
			{
				$array_trackers [$task_id] = db_result ($result, $i, "project_name");
			}
		}
	}
}
else
{
	log_error ("Error while getting the task 'pm' ", __FILE__, __FUNCTION__);
}
pm_header (array ("title" => dgettext ("pm_addtask", "title"), "pagename" => "pm_addtask"));
if (count ($array_trackers) > 0)
{
	$bug_url = "http";
	if ($sys_use_ssl == true)
	{
		$bug_url .= "s";
	}
	$bug_url .= "://" . $sys_default_domain . "/plugins/mantis/proxy/" . $project_id ."/view.php?id=" . $bug_id;
	echo "<form name=\"add_task\" action=\"/pm/task.php\" method=\"post\">\n";
	echo "<input type=\"hidden\" name=\"func\" value=\"addtask\">\n";
	echo "<input type=\"hidden\" name=\"group_id\" value=\"" . $group->getID () . "\">\n";
	echo "<input type=\"hidden\" name=\"related_artifact_summary\" value=\"" . $bug_summary . "\">\n";
	echo "<input type=\"hidden\" name=\"bug_url\" value=\"" . $bug_url . "\">\n";
	echo "<select name=\"group_project_id\">\n";
	foreach ($array_trackers as $key => $value)
	{
		echo "\t<option value=\"" . $key . "\">" . $value . "</option>\n";
	}
	echo "</select>\n";
	echo "<br>\n";
	echo "<input type=\"submit\" name=\"add_task\" value=\"" . dgettext ("tracker_taskmgr", "create_task") . "\">\n";
	echo "</form>\n";
}
else
{
	echo (dgettext ("tracker", "no_trackers"));
}
pm_footer (array ());
?>
