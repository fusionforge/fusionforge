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

require_once ("common/novaforge/log.php");

function getGForgeRoles ($group_id, &$array_roles)
{
	$ok = false;
	$array_roles = array ();
	$query = "SELECT role_id,role_name FROM role WHERE group_id=" . $group_id . " ORDER BY role_name";
	$result = db_query ($query);
	if ($result !== false)
	{
		$numrows = db_numrows ($result);
		if ($numrows > 0)
		{
			$ok = true;
			for ($i = 0; $i < $numrows; $i++)
			{
				$array_roles [db_result ($result, $i, "role_id")] = db_result ($result, $i, "role_name");
			}
		}
		else
		{
			log_error ("No roles defined for group " . $group_id, __FILE__, __FUNCTION__);
		}
	}
	else
	{
		log_error ("Function db_query() failed with query '" . $query . "': " . db_error (), __FILE__, __FUNCTION__);
	}
	return $ok;
}

function getProjectId ($group_id,
                       $url,
                       $mantis_id,
                       &$project_id)
{
	$ok = false;
	$query = "SELECT project_id FROM plugin_mantis_project WHERE gforge_id=" . $group_id . " AND url='" . $url . "' AND mantis_id=" . $mantis_id;
	$result = db_query ($query);
	if ($result !== false)
	{
		if (db_numrows ($result) == 1)
		{
			$ok = true;
			$project_id = db_result ($result, 0, "project_id");
		}
		else
		{
			log_error ("No project for id " . $mantis_id . " on server '" . $url . "' for group " . $group_id, __FILE__, __FUNCTION__);
		}
	}
	else
	{
		log_error ("Function db_query() failed with query '" . $query . "': " . db_error (), __FILE__, __FUNCTION__);
	}
	return $ok;
}

function getProjects ($group_id,
                      &$array_project_ids,
                      &$array_names)
{
	$ok = false;
	$array_project_ids = array ();
	$array_names = array ();
	$query = "SELECT project_id,name FROM plugin_mantis_project";
	if ((isset ($group_id) == true) && ($group_id > 0))
	{
		$query .= " WHERE gforge_id=" . $group_id;
	}
	$query .= " ORDER BY name ASC";
	$result = db_query ($query);
	if ($result !== false)
	{
		$ok = true;
		$numrows = db_numrows ($result);
		$index = 0;
		while ($index < $numrows)
		{
			$array_project_ids [$index] = db_result ($result, $index, 0);
			$array_names [$index] = stripslashes (db_result ($result, $index, 1));
			$index++;
		}
	}
	else
	{
		log_error ("Function db_query() failed with query '" . $query . "': " . db_error (), __FILE__, __FUNCTION__);
	}
	return $ok;
}

function getGForgeUsers ($group_id,
                         $url,
                         &$array_names,
                         &$array_passwords,
                         &$array_realnames,
                         &$array_mails,
                         &$array_statuses,
                         &$array_roles)
{
	$ok = false;
	if ((isset ($group_id) == true) && ($group_id > 0))
	{
		$group = &group_get_object ($group_id);
		if ((isset ($group) == true)
		&&  (is_object ($group) == true)
		&&  ($group->isError () == false))
		{
			$ok = true;
			$members = &$group->getMembers ();
		}
		else
		{
			log_error ("Error while getting object for group " . $group_id, __FILE__, __FUNCTION__);
		}
	}
	else
	{
		$query = "SELECT DISTINCT users.* FROM users"
		         . " INNER JOIN user_group ON users.user_id=user_group.user_id"
		         . " INNER JOIN plugin_mantis_project ON user_group.group_id=plugin_mantis_project.gforge_id"
		         . " WHERE plugin_mantis_project.url='" . $url . "'";
		$result = db_query ($query);
		if ($result !== false)
		{
			$ok = true;
			$members = array ();
			$numrows = db_numrows ($result);
			for ($index = 0; $index < $numrows; $index++)
			{
				$array_fields = &db_fetch_array ($result);
				$members [] = &new User ($array_fields ["user_id"], $array_fields);
			}
		}
		else
		{
			log_error ("Function db_query() failed with query '" . $query . "': " . db_error (), __FILE__, __FUNCTION__);
		}
	}
	if ($ok == true)
	{
		$array_names = array ();
		$array_passwords = array ();
		$array_realnames = array ();
		$array_mails = array ();
		$array_statuses = array ();
		$array_roles = array ();
		for ($index = 0; $index < count ($members); $index++)
		{
			$array_names [] = $members [$index]->getUnixName ();
			$array_passwords [] = $members [$index]->getMD5Passwd ();
			$array_realnames [] = $members [$index]->getRealName ();
			$array_mails [] = $members [$index]->getEmail ();
			if ($members [$index]->getStatus () == "A")
			{
				$array_statuses [] = "enabled";
			}
			else
			{
				$array_statuses [] = "disabled";
			}
			if (isset ($group) == true)
			{
				$permission = &$group->getPermission ($members [$index]);
				$permData = $permission->getPermData ();
				$array_roles [] = $permData ["role_id"];
			}
			else
			{
				$array_roles [] = 0;
			}
		}
	}
	return $ok;
}

function createProject ($group_id,
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
                        &$project_id)
{
	$ok = false;
	$query = "INSERT INTO plugin_mantis_project "
	         . "(gforge_id,url,mantis_id,name,description,status,visibility,css_regex_1,css_regex_2,css_regex_3,css_regex_4)"
	         . " VALUES "
	         . "(" . $group_id . ",'" . $url . "', " . $mantis_id . ",'" . addslashes ($name) . "','" . addslashes ($description) . "','" . $status . "', " . $visibility . ",'" . addslashes ($css_regex_1) . "','" .  addslashes ($css_regex_2) . "','" . addslashes ($css_regex_3) . "','" . addslashes ($css_regex_4) . "')";
	$result = db_query ($query);
	if ($result !== false)
	{
		$project_id = db_insertid ($result, "plugin_mantis_project", "project_id");
		if ($project_id != 0)
		{
			$ok = true;
		}
		else
		{
			log_error ("Function db_insertid() failed after query '" . $query . "': " . db_error (), __FILE__, __FUNCTION__);
		}
	}
	else
	{
		log_error ("Function db_query() failed with query '" . $query . "': " . db_error (), __FILE__, __FUNCTION__);
	}
	return $ok;
}

function updateProject ($project_id,
                        $name,
                        $description,
                        $status,
                        $visibility,
                        $css_regex_1,
                        $css_regex_2,
                        $css_regex_3,
                        $css_regex_4)
{
	$ok = false;
	$query = "UPDATE plugin_mantis_project SET "
	         . "name='" . addslashes ($name) . "',"
	         . "description='". addslashes ($description). "',"
	         . "status='" . $status . "',"
	         . "visibility=" . $visibility . ","
	         . "css_regex_1='" . addslashes ($css_regex_1) . "',"
	         . "css_regex_2='" . addslashes ($css_regex_2) . "',"
	         . "css_regex_3='" . addslashes ($css_regex_3) . "',"
	         . "css_regex_4='" . addslashes ($css_regex_4) . "'"
	         . " WHERE project_id=" . $project_id;
	$result = db_query ($query);
	if ($result !== false)
	{
		$ok = true;
	}
	else
	{
		log_error ("Function db_query() failed with query '" . $query . "': " . db_error (), __FILE__, __FUNCTION__);
	}
	return $ok;
}

function getProject ($project_id,
                     &$gforge_id,
                     &$url,
                     &$mantis_id,
                     &$name,
                     &$description,
                     &$status,
                     &$visibility,
                     &$css_regex_1,
                     &$css_regex_2,
                     &$css_regex_3,
                     &$css_regex_4)
{
	$ok = false;
	$query = "SELECT * FROM plugin_mantis_project WHERE project_id=" . $project_id;
	$result = db_query ($query);
	if ($result !== false)
	{
		if (db_numrows ($result) == 1)
		{
			$ok = true;
			$gforge_id = db_result ($result, 0, "gforge_id");
			$url = db_result ($result, 0, "url");
			$mantis_id = db_result ($result, 0, "mantis_id");
			$name = stripslashes (db_result ($result, 0, "name"));
			$description = stripslashes (db_result ($result, 0, "description"));
			$status = db_result ($result, 0, "status");
			$visibility = db_result ($result, 0, "visibility");
			$css_regex_1 = stripslashes (db_result ($result, 0, "css_regex_1"));
			$css_regex_2 = stripslashes (db_result ($result, 0, "css_regex_2"));
			$css_regex_3 = stripslashes (db_result ($result, 0, "css_regex_3"));
			$css_regex_4 = stripslashes (db_result ($result, 0, "css_regex_4"));
		}
		else
		{
			log_error ("there are " . db_numrows ($result) ." project(s) for the query '" . $query . "' ", __FILE__, __FUNCTION__);
		}
	}
	else
	{
		log_error ("Function db_query() failed with query '" . $query . "': " . db_error (), __FILE__, __FUNCTION__);
	}
	return $ok;
}

function deleteProject ($project_id)
{
	$ok = false;
	db_begin ();
	$query = "DELETE FROM plugin_mantis_role WHERE project_id=" . $project_id;
	$result = db_query ($query);
	if ($result !== false)
	{
		$query = "DELETE FROM plugin_mantis_project WHERE project_id=" . $project_id;
		$result = db_query ($query);
		if ($result !== false)
		{
			db_commit ();
			$ok = true;
		}
		else
		{
			log_error ("Function db_query() failed with query '" . $query . "': " . db_error (), __FILE__, __FUNCTION__);
			db_rollback ();
		}
	}
	else
	{
		log_error ("Function db_query() failed with query '" . $query . "': " . db_error (), __FILE__, __FUNCTION__);
		db_rollback ();
	}
	return $ok;
}

function getMantisInstances ($group_id,
                             &$array_urls)
{
	$ok = false;
	$query = "SELECT DISTINCT url FROM plugin_mantis_project";
	if ((isset ($group_id) == true) && ($group_id > 0))
	{
		$query .= " WHERE gforge_id=" . $group_id;
	}
	$result = db_query ($query);
	if ($result !== false)
	{
		$ok = true;
		$array_urls = array ();
		$numrows = db_numrows ($result);
		for ($i = 0; $i < $numrows; $i++)
		{
			$array_urls [] = db_result ($result, $i, 0);
		}
	}
	else
	{
		log_error ("Function db_query() failed with query '" . $query . "': " . db_error (), __FILE__, __FUNCTION__);
	}
	return $ok;
}

function setRolesMapping ($project_id,
                          $array_roles_mapping)
{

	$ok = false;
	if (deleteRolesMapping ($project_id) == true)
	{
		$ok = true;
		db_begin ();
		foreach ($array_roles_mapping as $gforge_id => $mantis_id)
		{
			$query = "INSERT INTO plugin_mantis_role (project_id,gforge_id,mantis_id) VALUES (" . $project_id . ", " . $gforge_id . ", " . $mantis_id . ")";
			$result= db_query ($query);
			if (($result === false) || (db_affected_rows ($result) != 1))
			{
				log_error ("Function db_query() failed with query '" . $query . "': " . db_error (), __FILE__, __FUNCTION__);
				$ok = false;
				break;
			}
		}
		if ($ok == false)
		{
			db_rollback ();
		}
		else
		{
			db_commit();
		}
	}
	return $ok;
}

function getRolesMapping ($project_id,
                          &$array_roles_mapping)
{
	$ok = false;
	$query = "SELECT gforge_id,mantis_id FROM plugin_mantis_role WHERE project_id=" . $project_id;
	$result = db_query ($query);
	if ($result !== false)
	{
		$ok = true;
		$array_roles_mapping = array ();
		$numrows = db_numrows ($result);
		for ($i = 0; $i < $numrows; $i++)
		{
			$array_roles_mapping [db_result ($result, $i, "gforge_id")] = db_result ($result, $i, "mantis_id");
		}
	}
	else
	{
		log_error ("Function db_query() failed with query '" . $query . "': " . db_error (), __FILE__, __FUNCTION__);
	}
	return $ok;
}

function deleteRolesMapping ($project_id)
{
	$ok = false;
	$query = "DELETE FROM plugin_mantis_role WHERE project_id=" . $project_id;
	$result = db_query ($query);
	if ($result !== false)
	{
		$ok = true;
	}
	else
	{
		log_error ("Function db_query() failed with query '" . $query . "': " . db_error (), __FILE__, __FUNCTION__);
	}
	return $ok;
}

function deleteDefaultEntry ($name, $current_value = null)
{
	$ok = false;
	$query = "DELETE FROM plugin_mantis_default WHERE name='". $name ."'";
	if (isset ($current_value) == true)
	{
		$query .= " AND value='". $current_value."'";
	}
	$result = db_query ($query);
	if ($result !== false)
	{
		$ok = true;
	}
	else
	{
		log_error ("Function db_query() failed with query '" . $query . "': " . db_error (), __FILE__, __FUNCTION__);
	}
	return $ok;
}
function addDefaultEntry ($name, $value)
{
	$ok = false;
	$query = "INSERT INTO plugin_mantis_default (name, value) VALUES ('". $name ."','" . $value . "')";
	$result = db_query ($query);
	if ($result !== false)
	{
		$id = db_insertid ($result, "plugin_mantis_default", "default_id");
		if ($id != 0)
		{
			$ok = true;
		}
		else
		{
			log_error ("Function db_insertid() failed after query '" . $query . "': " . db_error (), __FILE__, __FUNCTION__);
		}
	}
	else
	{
		log_error ("Function db_query() failed with query '" . $query . "': " . db_error (), __FILE__, __FUNCTION__);
	}
	return $ok;
}
function updateDefaultEntry ($name, $value, $current_value = null)
{
	$ok = false;
	$query = "UPDATE plugin_mantis_default SET value='" . $value . "' WHERE name='" .  $name ."'";
	if ($current_value != null)
	{
		$query .= " AND value='". $current_value."'";
	}
	$result = db_query ($query);
	if ($result !== false)
	{
		$ok = true;
	}
	else
	{
		log_error ("Function db_query() failed with query '" . $query . "': " . db_error (), __FILE__, __FUNCTION__);
	}
	return $ok;
}
function getDefaultEntry ($name, &$value)
{
	$ok = false;
	$query = "SELECT value FROM plugin_mantis_default WHERE name='". $name ."'";
	$result = db_query ($query);
	if ($result !== false)
	{
		$ok = true;
		if (db_numrows ($result) > 0)
		{
			$value = db_result ($result, 0, "value");
		}
		else
		{
			$value = null;
		}
	}
	else
	{
		log_error ("Function db_query() failed with query '" . $query . "': " . db_error (), __FILE__, __FUNCTION__);
	}
	return $ok;
}
function getDefaultEntries ($name, &$array_values)
{
	$ok = false;
	$query = "SELECT value FROM plugin_mantis_default WHERE name='". $name ."' ORDER BY value";
	$result = db_query ($query);
	if ($result !== false)
	{
		$ok = true;
		$array_values = array ();
		$numrows = db_numrows ($result);
		if ($numrows > 0)
		{
			for ($i = 0; $i < $numrows; $i++)
			{
				$array_values [] = db_result ($result, $i, "value");
			}
		}
	}
	else
	{
		log_error ("Function db_query() failed with query '" . $query . "': " . db_error (), __FILE__, __FUNCTION__);
	}
	return $ok;
}
?>
