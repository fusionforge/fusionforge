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

include_once ("core.php");

function createProject ($name,
                        $description,
                        $status,
                        $visibility)
{
	$ok = false;
	$message = "Error";
	$name = trim ($name);
	if (empty ($name) == false)
	{
		if (project_get_id_by_name ($name) == 0)
		{
			$statusCode = getStatusCode ($status);
			$visibilityCode = getVisibilityCode ($visibility);
			$id = project_create ($name, decodeLineFeeds ($description), $statusCode, $visibilityCode, "", true);
			if ((isset ($id) == true) && (empty ($id) == false))
			{
				$ok = true;
			}
			else
			{
				$message = "The project_create() function failed for project '" . $name . "'";
			}
		}
		else
		{
			$message = "The '" . $name . "' project already exists";
		}
	}
	else
	{
		$message = "The project name is empty";
	}
	$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?><MANTIS><RESPONSE STATUS=\"";
	if ($ok == true)
	{
		$xml .= "success\"><PROJECT ID=\"" . $id . "\" NAME=\"" . xmlEncodeString ($name) . "\" DESCRIPTION=\"" . xmlEncodeString ($description) ."\" STATUS=\"" . $status . "\" VISIBILITY=\"" . $visibility . "\"/></RESPONSE>";
	}
	else
	{
		syslog (LOG_ERR, __FILE__ . " - " . __FUNCTION__ . " - " . $message);
		$xml .= "failure\" MESSAGE=\"" . xmlEncodeString ($message) . "\"/>";
	}
	$xml .= "</MANTIS>";
	return $xml;
}

function getProject ($id)
{
	$ok = false;
	$message = "Error";
	if ((isset ($id) == true) && (empty ($id) == false))
	{
		if (project_exists ($id) == true)
		{
			$ok = true;
			$name = project_get_name ($id);
			$description = encodeLineFeeds (project_get_field ($id, "description"));
			$status = getStatusName (project_get_field ($id, "status"));
			$visibility = getVisibilityName (project_get_field ($id, "view_state"));
		}
		else
		{
			$message = "The '" . $id . "' project does not exist";
		}
	}
	else
	{
		$message = "The project identifier is empty";
	}
	$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?><MANTIS><RESPONSE STATUS=\"";
	if ($ok == true)
	{
		$xml .= "success\"><PROJECT ID=\"" . $id . "\" NAME=\"" . xmlEncodeString ($name) . "\" DESCRIPTION=\"" . xmlEncodeString ($description) ."\" STATUS=\"" . $status . "\" VISIBILITY=\"" . $visibility . "\"/></RESPONSE>";
	}
	else
	{
		syslog (LOG_ERR, __FILE__ . " - " . __FUNCTION__ . " - " . $message);
		$xml .= "failure\" MESSAGE=\"" . xmlEncodeString ($message) . "\"/>";
	}
	$xml .= "</MANTIS>";
	return $xml;
}
		
function updateProject ($id,
                        $name,
                        $description,
                        $status,
                        $visibility)
{
	$ok = false;
	$message = "Error";
	if ((isset ($id) == true) && (empty ($id) == false))
	{
		$name = trim ($name);
		if (empty ($name) == false)
		{
			if (project_exists ($id) == true)
			{
				$statusCode = getStatusCode ($status);
				$visibilityCode = getVisibilityCode ($visibility);
				$ok = project_update ($id, $name, decodeLineFeeds ($description), $statusCode, $visibilityCode, "", true);
				if  ($ok == false)
				{
					$message = "The project_update() function failed for project '" . $name . "'";
				}
			}
			else
			{
				$message = "The '" . $id . "' project does not exist";
			}
		}
		else
		{
			$message = "The project name is empty";
		}
	}
	else
	{
		$message = "The project identifier is empty";
	}
	$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?><MANTIS><RESPONSE STATUS=\"";
	if ($ok == true)
	{
		$xml .= "success\"/>";
	}
	else
	{
		syslog (LOG_ERR, __FILE__ . " - " . __FUNCTION__ . " - " . $message);
		$xml .= "failure\" MESSAGE=\"" . xmlEncodeString ($message) . "\"/>";
	}
	$xml .= "</MANTIS>";
	return $xml;
}

function removeProject ($id)
{
	$ok = false;
	$message = "Error";
	if ((isset ($id) == true) && (empty ($id) == false))
	{
		if (project_exists ($id) == true)
		{
			if (auth_attempt_script_login ("admin", null) == true)
			{
				if  (project_delete ($id) == true)
				{
					$ok = true;
				}
				else
				{
					$message = "The project_delete() function failed for the id project  = '" . $id . "'";
				}
			}
			else
			{
				$message = "The auth_attempt_script_login() function failed for username 'admin'";
			}
		}
		else
		{
			$message = "The '" . $id . "' project does not exist";
		}
	}
	else
	{
		$message = "The project identifier is empty";
	}
	$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?><MANTIS><RESPONSE STATUS=\"";
	if ($ok == true)
	{
		$xml .= "success\"/>";
	}
	else
	{
		syslog (LOG_ERR, __FILE__ . " - " . __FUNCTION__ . " - " . $message);
		$xml .= "failure\" MESSAGE=\"" . xmlEncodeString ($message) . "\"/>";
	}
	$xml .= "</MANTIS>";
	return $xml;
}

function setRoles ($project_id,
                   $array_users)
{
	$ok = true;
	$message = "Error";
	if ((isset ($project_id) == true) && (empty ($project_id) == false))
	{
		if (count ($array_users) > 0)
		{
			if (project_exists ($project_id) == false)
			{
				$ok = false;
				$message = "The '" . $project_id . "' project does not exist";
			}
		}
		else
		{
			$ok = false;
			$message = "The array of users is empty";
		}
	}
	else
	{
		$ok = false;
		$message = "The project identifier is empty";
	}
	if  ($ok == true)
	{
		$users = project_get_all_user_rows ($project_id);
		foreach ($users as $user)
		{
			if (array_key_exists ($user ["id"], $array_users) == true)
			{
				if (project_remove_user ($project_id, $user ["id"]) == false)
				{
					$ok = false;
					$message = "The project_remove_user() function failed for user " . $user ["username"] . " of project " . $project_id;
					break;
				}
			}
		}
	}
	if ($ok == true)
	{
		foreach ($array_users as $name => $role_id)
		{
			if ($role_id !=0)
			{
				if (project_set_user_access ($project_id, user_get_id_by_name ($name), $role_id) != true)
				{
					$ok = false;
					$message = "The project_set_user_access() function failed for user " . $name . " with role " . $role_id . " of project " . $project_id;
					break;
				}
			}
			else
			{
				if (project_remove_user ($project_id, user_get_id_by_name ($name)) != true)
				{
					$ok = false;
					$message = "The project_remove_user() function failed for user " . $name . "  of project " . $project_id;
					break;
				}
			}
		}
	}
	$xml .= "<?xml version=\"1.0\" encoding=\"UTF-8\" ?><MANTIS><RESPONSE STATUS=\"";
	if ($ok == true)
	{
		$xml .= "success\"/>";
	}
	else
	{
		syslog (LOG_ERR, __FILE__ . " - " . __FUNCTION__ . " - " . $message);
		$xml .= "failure\" MESSAGE=\"" . xmlEncodeString ($message) . "\"/>";
	}
	$xml .= "</MANTIS>";
	return $xml;
}


function getRoles ()
{
	return "<?xml version=\"1.0\" encoding=\"UTF-8\" ?><MANTIS><RESPONSE STATUS=\"success\"><ROLES VALUES=\"" . config_get ("access_levels_enum_string") . "\"/></RESPONSE></MANTIS>";
}


function getUsers ()
{
	$ok = false;
	$message = "Error";
	$query = "SELECT * FROM ". config_get ("mantis_user_table");
	$result = db_query ($query);
	if ($result == true)
	{
		$ok = true;
		$xml_users = "";
		$num = db_num_rows ($result);
		for ($i = 0 ; $i < $num ; $i++)
		{
			$id = db_result ($result, $i, "id");
			$xml_users .= "<USER ID=\"" . $id . "\" NAME=\"" . db_result ($result, $i, "username") . "\" ";
			$xml_users .= "PASSWORD=\"" . xmlEncodeString (db_result ($result, $i, "password")) . "\" ";
			$xml_users .= "REALNAME=\"" . xmlEncodeString (db_result ($result, $i, "realname")) . "\" ";
			$xml_users .= "MAIL=\"" . db_result ($result, $i, "email") . "\" ";
			$xml_users .= "STATUS=\"" . getEnabledName (db_result ($result, $i, "enabled")) . "\"/>";
		}
	}
	else
	{
		$message = "The db_query() function failed for query '" . $query . "'";
	}
	$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?><MANTIS><RESPONSE STATUS=\"";
	if ($ok == true)
	{
		$xml .= "success\"><USERS>" . $xml_users . "</USERS></RESPONSE>";
	}
	else
	{
		syslog (LOG_ERR, __FILE__ . " - " . __FUNCTION__ . " - " . $message);
		$xml .= "failure\" MESSAGE=\"" . xmlEncodeString ($message) . "\"/>";
	}
	$xml .= "</MANTIS>";
	return $xml;
}

function getBugs ($project_id,
                  $username)
{
	$ok = false;
	$message = "Error";
	if ((isset ($project_id) == true) && (empty ($project_id) == false))
        {
		$user_id = user_get_id_by_name ($username);
		if ((isset ($user_id) == true) && (empty ($user_id) == false))
		{
			$query = "SELECT id,summary FROM " . config_get ("mantis_bug_table") . " WHERE project_id=" . $project_id . " AND status < " . config_get ("bug_resolved_status_threshold") . " AND handler_id=" . db_prepare_int ($user_id);
			$result = db_query ($query);
			if ($result !== false)
			{
				$ok = true;
				$bugs = "";
				$num = db_num_rows ($result);
				for ($i = 0 ; $i < $num; $i++)
				{
					$bugs .= "<BUG ID=\"" . db_result ($result, $i, 0) ."\" SUMMARY=\"" . xmlEncodeString (db_result ($result, $i, 1)) . "\"/>";
				}
			}
			else
			{
				$message = "The db_query() function failed for query '" . $query . "'";
			}
		}
		else
		{
			$message = "The '" . $username . "' user does not exist";
		}
	}
	else
	{
		$message = "The project identifier is empty";
	}
	$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?><MANTIS><RESPONSE STATUS=\"";
	if ($ok == true)
	{
		$xml .= "success\"><BUGS>" . $bugs . "</BUGS></RESPONSE>";
	}
	else
	{
		syslog (LOG_ERR, __FILE__ . " - " . __FUNCTION__ . " - " . $message);
		$xml .= "failure\" MESSAGE=\"" . xmlEncodeString ($message) . "\"/>";
	}
	$xml .= "</MANTIS>";
	return $xml;
}

function createUser ($name, 
                     $password,
                     $realname,
                     $mail,
                     $enabled)
{
	$ok = false;
	$message = "Error";
	$name = trim ($name);
	if (empty ($name) == false)
	{
		if (user_get_id_by_name ($name) == 0)
		{
			if (user_create ($name,
			                 $password,
			                 $mail,
			                 VIEWER,
			                 false,
			                 getEnabledCode ($enabled),
			                 $realname) !== false)
			{
				$ok = true;
			}
			else
			{
				$message = "The user_create() function failed for user '" . $name . "'";
			}
		}
		else
		{
			$message = "The '" . $name . "' user already exists";
		}
	}
	else
	{
		$message = "The user name is empty";
	}
	$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?><MANTIS><RESPONSE STATUS=\"";
	if ($ok == true)
	{
		$xml .= "success\"/>";
	}
	else
	{
		syslog (LOG_ERR, __FILE__ . " - " . __FUNCTION__ . " - " . $message);
		$xml .= "failure\" MESSAGE=\"" . xmlEncodeString ($message) . "\"/>";
	}
	$xml .= "</MANTIS>";
	return $xml;
}

function updateUser ($id,
                     $name, 
                     $password, 
                     $realname, 
                     $mail, 
                     $enabled)
{
	$ok = false;
	$message = "Error";
	$name = trim ($name);
	if (empty ($name) == false)
	{
		if  (user_exists ($id) == true)
		{
			if (user_get_id_by_name ($name) == $id)
			{
				if (user_is_administrator ($id) == false)
				{
					if (user_set_password ($id, $password) == true)
					{
						if (user_set_realname ($id, $realname) == true)
						{
							if (user_set_email ($id, $mail) == true)
							{
								if (user_set_field ($id, "enabled", getEnabledCode ($enabled)) == true)
								{
									$ok = true;
								}
								else
								{
									$message = "The user_set_field() function failed to set field 'enabled' for user " . $id;
								}
							}
							else
							{
								$message = "The user_set_email() function failed for user " . $id;
							}
						}
						else
						{
							$message = "The user_set_realname() function failed for user " . $id;
						}
					}
					else
					{
						$message = "The user_set_field() function failed to set field 'password' for user " . $id;
					}
				}
				else
				{
					$ok = true;
					syslog (LOG_INFO, __FILE__ . " - " . __FUNCTION__ . " - The user '" . $id . "' is an administrator");
				}
			}
			else
			{
				$message = "The identifier of user '" . $name . "' is not " . $id;
			}
		}
		else
		{
			$message = "The '" . $id . "' user does not exist";
		}
	}
	else
	{
		$message = "The user name is empty";
	}
	$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?><MANTIS><RESPONSE STATUS=\"";
	if ($ok == true)
	{
		$xml .= "success\"/>";
	}
	else
	{
		syslog (LOG_ERR, __FILE__ . " - " . __FUNCTION__ . " - " . $message);
		$xml .= "failure\" MESSAGE=\"" . xmlEncodeString ($message) . "\"/>";
	}
	$xml .= "</MANTIS>";
	return $xml;
}

function checkinBugs ($username,
                      $bug_ids,
                      $comment)
{
	$ok = false;
	$message = "Error";
	$username = trim ($username);
	if (empty ($username) == false)
	{
		if (auth_attempt_script_login ($username, null) == true)
		{
			auth_set_cookies (user_get_id_by_name ($username), false);
			$array_all_bug_ids = explode (" ", $bug_ids);
			if ($array_all_bug_ids == false)
			{
				$message = "Error while exploding bug identifiers string";
			}
			else
			{
				$array_bug_ids = array ();
				$array_fixed_bug_ids = array ();
				foreach ($array_all_bug_ids as $bug_id)
				{
					if (($bug_id [0] == "F") || ($bug_id [0] == "f"))
					{
						array_push ($array_fixed_bug_ids, substr ($bug_id, 1));
					}
					else
					{
						array_push ($array_bug_ids, $bug_id);
					}
				}
				$array_bug_ids = array_unique ($array_bug_ids);
				$array_fixed_bug_ids = array_unique ($array_fixed_bug_ids);
				if ((count ($array_bug_ids) == 0) && (count ($array_fixed_bug_ids) == 0))
				{
					$message = "No bug identifier received";
				}
				else
				{
					$ok = true;
					$comment = decodeLineFeeds ($comment);
					foreach ($array_bug_ids as $bug_id)
					{
						if (in_array ($bug_id, $array_fixed_bug_ids) == false)
						{
							helper_call_custom_function ("checkin", array ($bug_id, $comment, "", "", false));
						}
					}
					foreach ($array_fixed_bug_ids as $bug_id)
					{
						helper_call_custom_function ("checkin", array ($bug_id, $comment, "", "", true));
					}
				}
			}
		}
		else
		{
			$message = "The auth_attempt_script_login() function failed for username '" . $username . "'";
		}
	}
	else
	{
		$message = "The user name is empty";
	}
	$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?><MANTIS><RESPONSE STATUS=\"";
	if ($ok == true)
	{
		$xml .= "success\"/>";
	}
	else
	{
		syslog (LOG_ERR, __FILE__ . " - " . __FUNCTION__ . " - " . $message);
		$xml .= "failure\" MESSAGE=\"" . xmlEncodeString ($message) . "\"/>";
	}
	$xml .= "</MANTIS>";
	return $xml;
}

function getStatusCode ($name)
{
	$code = 10;
	switch ($name)
	{
		case "release" :
			$code = 30;
			break;
		case "stable" :
			$code = 50;
			break;
		case "obsolete" :
			$code = 70;
			break;
		case "development" :
		default :
			$code  = 10;
	}
	return $code;
}

function getStatusName ($code)
{
	$name = "development";
	switch ($code)
	{
		case 30:
			$name = "release";
			break;
		case 50 :
			$name = "stable";
			break;
		case 70 :
			$name = "obsolete";
			break;
		case 10 :
		default :
			$name = "development";
	}
	return $name;
}

function getVisibilityCode ($name)
{
	$code = VS_PRIVATE;
	if ($name == "public")
	{
		$code = VS_PUBLIC;
	}
	return $code;
}

function getVisibilityName ($code)
{
	$name = "private";
	if ($code == VS_PUBLIC)
	{
		$name = "public";
	}
	return $name;
}

function getEnabledCode ($name)
{
	$code = 0;
	if ($name == "enabled")
	{
		$code = 1;
	}
	return $code;
}

function getEnabledName ($code)
{
	$name = "disabled";
	if ($code == 1)
	{
		$name = "enabled";
	}
	return $name;
}

function encodeLineFeeds ($string)
{
	$value = $string;
	if (strchr ($string, "\n") !== false)
	{
		$value = str_replace ("\n", "[LF]", $value);
	}
	return $value;
}

function decodeLineFeeds ($string)
{
	$value = $string;
	if (strchr ($string, "[LF]") !== false)
	{
		$value = str_replace ("[LF]", "\n", $value);
	}
	return $value;
}

function xmlEncodeString ($string)
{
	$value = $string;
	if (strchr ($string, "&") !== false)
	{
		$value = str_replace ("&", "&amp;", $value);
	}
	if (strchr ($string, "<") !== false)
	{
		$value = str_replace ("<", "&lt;", $value);
	}
	if (strchr ($string, ">") !== false)
	{
		$value = str_replace (">", "&gt;", $value);
	}
	if (strchr ($string, "'") !== false)
	{
		$value = str_replace ("'", "&apos;", $value);
	}
	if (strchr ($string, "\"") !== false)
	{
		$value = str_replace ("\"", "&quot;", $value);
	}
	return $value;
}

?>
