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

require_once ("common/novaforge/log.php");
require_once ("common/novaforge/auth.php");

$g_plugins_to_publish = array ("novadoc", "novafrs");
$g_xml_ok = false;
$g_xml_id = "";
$g_xml_status = "";
$g_xml_message = "";
$g_xml_upload_plugin = "";
$g_xml_upload_id = "";

function getPublisherGForgeRoles ($group_id, &$array_roles)
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

function getGroupsToPublish (&$array_group_ids)
{
	$ok = false;
	$query = "SELECT group_plugin.group_id FROM group_plugin, plugins WHERE plugins.plugin_name='novapub' AND group_plugin.plugin_id=plugins.plugin_id";
	$result = db_query ($query);
	if ($result === false)
	{
		log_error ("Function db_query() failed with query '" . $query . "': " . db_error (), __FILE__, __FUNCTION__);
	}
	else
	{
		$ok = true;
		$array_group_ids = array ();
		$numrows = db_numrows ($result);
		for ($i = 0; $i < $numrows; $i++)
		{
			$array_group_ids [] = db_result ($result, $i, 0);
		}
	}
	return $ok;
}

function getPublisherXml ($group_id, $group_name, $role_id, &$xml)
{
	global
		$sys_default_domain,
		$g_plugins_to_publish;

	$ok = true;
	$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<novaforge-publisher server=\"" . $sys_default_domain . "\" project=\"" . $group_name . "\">";
	$i = 0;
	while (($ok == true) && ($i < count ($g_plugins_to_publish)))
	{
		$handle = fopen ("plugins/novapub/include/plugins/" . $g_plugins_to_publish [$i] . "Publisher.class.php", "r", true);
		if ($handle !== false)
		{
			fclose ($handle);
			require_once ("plugins/novapub/include/plugins/" . $g_plugins_to_publish [$i] . "Publisher.class.php");
			$classname = $g_plugins_to_publish [$i] . "Publisher";
			$publisher = new $classname;
			if ((isset ($publisher) == false) || (is_object ($publisher) == false))
			{
				$ok = false;
				log_error ("Error while instanciating class '" . $g_plugins_to_publish [$i] . "Publisher'", __FILE__, __FUNCTION__);
			}
			else
			{
				$xml .= "<plugin name=\"" . $g_plugins_to_publish [$i] . "\">";
				if ($publisher->getXml ($group_id, $role_id, $xml) == false)
				{
					$ok = false;
					log_error ("Error while getting XML for plugin '" . $g_plugins_to_publish [$i] . "'", __FILE__, __FUNCTION__);
				}
				$xml .= "</plugin>";
			}
		}
		$i++;
	}
	$xml .= "</novaforge-publisher>";
	return $ok;
}

function callNovaForgePublisher ($url, $array_cookies, $array_parameters, &$xml_out)
{
	$ok = false;
	$ch = curl_init ();
	if ($ch !== false)
	{
		curl_setopt ($ch, CURLOPT_URL, $url);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 20);
		curl_setopt ($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt ($ch, CURLOPT_USERAGENT, "GForge");
		curl_setopt ($ch, CURLOPT_POSTFIELDS, $array_parameters);
		$cookieString = "";
		foreach ($array_cookies as $name => $value_and_expires)
		{
			$cookieString .= $name . "=" . $value_and_expires [0] . "; ";
		}
		if (strlen ($cookieString) > 0)
		{
			curl_setopt ($ch, CURLOPT_COOKIE, $cookieString);
		}
		$xml_out = curl_exec ($ch);
		if (($xml_out !== false) && (curl_errno ($ch) == 0))
		{
			$ok = true;
		}
		else
		{
			log_error ("The curl_exec() function failed for URL '" . $url . "' with CURL error " . curl_errno ($ch) . ": " . curl_error ($ch), __FILE__, __FUNCTION__);
		}
		curl_close ($ch);
	}
	else
	{
		log_error ("The curl_init() function failed", __FILE__, __FUNCTION__);
	}
	return $ok;
}

/*
 * Function element_open
 */
function element_open ($parser, $name, $attributes)
{
	global
		$g_xml_ok,
		$g_xml_id,
		$g_xml_status,
		$g_xml_message,
		$g_xml_upload_plugin,
		$g_xml_upload_id;

	if ($name == "GFORGE-PLUGIN-NOVAPUB")
	{
		if ($g_xml_ok == true)
		{
			if (array_key_exists ("ID", $attributes) == true)
			{
				$g_xml_id = $attributes ["ID"];
				if (strlen ($g_xml_id) == 0)
				{
					$g_xml_ok = false;
					log_error ("Attribute 'id' is empty", __FILE__, __FUNCTION__);
				}
			}
			else
			{
				$g_xml_ok = false;
				log_error ("Attribute 'id' is missing", __FILE__, __FUNCTION__);
			}
		}
		if ($g_xml_ok == true)
		{
			if (array_key_exists ("STATUS", $attributes) == true)
			{
				$g_xml_status = $attributes ["STATUS"];
				if (strlen ($g_xml_status) == 0)
				{
					$g_xml_ok = false;
					log_error ("Attribute 'status' is empty", __FILE__, __FUNCTION__);
				}
				else
				{
					if (($g_xml_status != "continue")
					&&  ($g_xml_status != "finished")
					&&  ($g_xml_status != "failure"))
					{
						$g_xml_ok = false;
						log_error ("Value '" . $g_xml_status . "' of attribute 'status' is incorrect", __FILE__, __FUNCTION__);
					}
				}
			}
			else
			{
				$g_xml_ok = false;
				log_error ("Attribute 'status' is missing", __FILE__, __FUNCTION__);
			}
		}
		if ($g_xml_ok == true)
		{
			if (array_key_exists ("MESSAGE", $attributes) == true)
			{
				$g_xml_message = $attributes ["MESSAGE"];
			}
		}
	}
	if ($name == "UPLOAD")
	{
		if (array_key_exists ("PLUGIN", $attributes) == true)
		{
			$g_xml_upload_plugin = $attributes ["PLUGIN"];
			if (strlen ($g_xml_upload_plugin) == 0)
			{
				$g_xml_ok = false;
				log_error ("Attribute 'plugin' is empty", __FILE__, __FUNCTION__);
			}
		}
		else
		{
			$g_xml_ok = false;
			log_error ("Attribute 'plugin' is missing", __FILE__, __FUNCTION__);
		}
		if (array_key_exists ("ID", $attributes) == true)
		{
			$g_xml_upload_id = $attributes ["ID"];
			if (strlen ($g_xml_upload_id) == 0)
			{
				$g_xml_ok = false;
				log_error ("Attribute 'id' is empty", __FILE__, __FUNCTION__);
			}
		}
		else
		{
			$g_xml_ok = false;
			log_error ("Attribute 'id' is missing", __FILE__, __FUNCTION__);
		}
	}
}

/*
 * Function element_close
 */
function element_close ($parser, $name)
{
}

function publishProjectFile ($group_id,
                             $project_id,
                             $url,
                             &$array_cookies,
                             $id,
                             $upload_plugin,
                             $upload_id,
                             &$error,
                             &$file,
                             &$function)
{
	global
		$Language;

	$ok = false;
	$error = "";
	$classname = $upload_plugin . "Publisher";
	$publisher = new $classname;
	if ((isset ($publisher) == false) || (is_object ($publisher) == false))
	{
		$error = dgettext ("gforge-plugin-novapub", "publish_error_class", array ($upload_id, $upload_plugin, $project_id, $group_id));
		$file = __FUNCTION__;
		$function = __FUNCTION__;
	}
	else
	{
		if ($publisher->getFileName ($group_id, $upload_id, $filename) == false)
		{
			$error = dgettext ("gforge-plugin-novapub", "publish_error_filename", array ($upload_id, $upload_plugin, $project_id, $group_id));
			$file = __FUNCTION__;
			$function = __FUNCTION__;
		}
		else
		{
			if (callNovaForgePublisher ($url . "upload.php", $array_cookies, array ("id" => $id, "upload_plugin" => $upload_plugin, "upload_id" => $upload_id, "file" => "@" . $filename), $xml_out) == false)
			{
				$error = dgettext ("gforge-plugin-novapub", "publish_error_calling_upload", array ($url, $upload_id, $filename, $upload_plugin, $project_id, $group_id));
				$file = __FUNCTION__;
				$function = __FUNCTION__;
			}
			else
			{
				if ($xml_out != "OK")
				{
					$error = dgettext ("gforge-plugin-novapub", "publish_error_remote", array ($project_id, $group_id, $xml_out));
					$file = __FUNCTION__;
					$function = __FUNCTION__;
				}
				else
				{
					$ok = true;
				}
			}
		}
	}
	return $ok;
}

function publishProjectFiles ($group_id,
                              $project_id,
                              $url,
                              &$array_cookies,
                              $id,
                              &$error,
                              &$file,
                              &$function)
{
	global
		$g_xml_ok,
		$g_xml_id,
		$g_xml_status,
		$g_xml_message,
		$g_xml_upload_plugin,
		$g_xml_upload_id,
		$Language;

	$ok = true;
	$error = "";
	$previous_upload_plugin = "";
	$previous_upload_id = 0;
	while (($ok == true) && ($g_xml_status == "continue"))
	{
		if (callNovaForgePublisher ($url . "step_2.php", $array_cookies, array ("id" => $g_xml_id), $xml_out) == false)
		{
			$ok = false;
			$error = dgettext ("gforge-plugin-novapub", "publish_error_calling_step_2", array ($url, $project_id, $group_id));
			$file = __FILE__;
			$function = __FUNCTION__;
		}
		else
		{
			$g_xml_ok = true;
			$g_xml_id = $id;
			$g_xml_status = "";
			$g_xml_message = "";
			$g_xml_upload_plugin = "";
			$g_xml_upload_id = "";
			$parser = xml_parser_create ();
			xml_parser_set_option ($parser, XML_OPTION_SKIP_WHITE, 1);
			xml_set_element_handler ($parser, "element_open", "element_close");
			if (xml_parse ($parser, $xml_out) != 1)
			{
				$ok = false;
				$error = sprintf( dgettext ( "gforge-plugin-novapub" ,  "publish_error_expat_step_2" ) , array (xml_get_current_line_number ($parser), xml_get_current_column_number ($parser), xml_get_error_code ($parser), xml_error_string (xml_get_error_code ($parser)), $project_id, $group_id));
				$file = __FILE__;
				$function = __FUNCTION__;
				xml_parser_free ($parser);
			}
			else
			{
				xml_parser_free ($parser);
				if ($g_xml_ok == false)
				{
					$ok = false;
					$error = dgettext ("gforge-plugin-novapub", "publish_error_parsing_step_2", array ($project_id, $group_id));
					$file = __FILE__;
					$function = __FUNCTION__;
				}
				else
				{
					if ($g_xml_status == "continue")
					{
						if (($g_xml_upload_plugin == $previous_upload_plugin)
						&&  ($g_xml_upload_id == $previous_upload_id))
						{
							$ok = false;
							$error = dgettext ("gforge-plugin-novapub", "publish_error_calling_step_2", array ($g_xml_upload_id, $g_xml_upload_plugin, $project_id, $group_id));
							$file = __FILE__;
							$function = __FUNCTION__;
						}
						else
						{
							$previous_upload_plugin = $g_xml_upload_plugin;
							$previous_upload_id = $g_xml_upload_id;
							if (publishProjectFile ($group_id,
							                        $project_id,
							                        $url,
							                        $array_cookies,
										$g_xml_id,
							                        $g_xml_upload_plugin,
										$g_xml_upload_id,
							                        $error,
							                        $file,
							                        $function) == false)
							{
								$ok = false;
								if (empty ($error) == true)
								{
									$error = dgettext ("gforge-plugin-novapub", "publish_error_publish_file", array ($g_xml_upload_id, $g_xml_upload_plugin, $project_id, $group_id));
									$file = __FILE__;
									$function = __FUNCTION__;
								}
							}
						}
					}
					else
					{
						if ($g_xml_status != "finished")
						{
							$ok = false;
							$error = dgettext ("gforge-plugin-novapub", "publish_error_remote", array ($project_id, $group_id, $g_xml_message));
							$file = __FILE__;
							$function = __FUNCTION__;
						}
					}
				}
			}
		}
	}
	return $ok;
}

function publishProject ($group_id,
                         $group_name,
                         $project_id,
                         &$error,
                         &$file,
                         &$function)
{
	global
		$g_xml_ok,
		$g_xml_id,
		$g_xml_status,
		$g_xml_message,
		$Language;

	$ok = false;
	$error = "";
	if (getPublisherProjectById ($project_id,
	                             $name,
	                             $group_id_tmp,
	                             $role_id,
	                             $url) == false)
	{
		$error = dgettext ("gforge-plugin-novapub", "publish_error_getting_params", array ($project_id, $group_id));
		$file = __FILE__;
		$function = __FUNCTION__;
	}
	else
	{
		if (getPublisherXml ($group_id, $group_name, $role_id, $xml_in) == false)
		{
			$error = dgettext ("gforge-plugin-novapub", "publish_error_getting_xml", array ($project_id, $group_id));
			$file = __FILE__;
			$function = __FUNCTION__;
		}
		else
		{
			$url = trim ($url);
			if ($url [strlen ($url) - 1] != "/")
			{
				$url .= "/";
			}
			if (authenticate ($url . "auth.php", $array_cookies, null) == false)
			{
				$error = dgettext ("gforge-plugin-novapub", "publish_error_calling_auth", array ($url, $project_id, $group_id));
				$file = __FILE__;
				$function = __FUNCTION__;
			}
			else
			{
				if (callNovaForgePublisher ($url . "step_1.php", $array_cookies, array ("xml" => urlencode ($xml_in)), $xml_out) == false)
				{
					$error = dgettext ("gforge-plugin-novapub", "publish_error_calling_step_1", array ($url, $project_id, $group_id));
					$file = __FILE__;
					$function = __FUNCTION__;
				}
				else
				{
					$g_xml_ok = true;
					$g_xml_id = "";
					$g_xml_status = "";
					$g_xml_message = "";
					$parser = xml_parser_create ();
					xml_parser_set_option ($parser, XML_OPTION_SKIP_WHITE, 1);
					xml_set_element_handler ($parser, "element_open", "element_close");
					if (xml_parse ($parser, $xml_out) != 1)
					{
						$error = sprintf( dgettext ( "gforge-plugin-novapub" ,  "publish_error_expat_step_1" ) , array (xml_get_current_line_number ($parser), xml_get_current_column_number ($parser), xml_get_error_code ($parser), xml_error_string (xml_get_error_code ($parser)), $project_id, $group_id));
						$file = __FILE__;
						$function = __FUNCTION__;
						xml_parser_free ($parser);
					}
					else
					{
						xml_parser_free ($parser);
						if ($g_xml_ok == false)
						{
							$error = dgettext ("gforge-plugin-novapub", "publish_error_parsing_step_1", array ($project_id, $group_id));
							$file = __FILE__;
							$function = __FUNCTION__;
						}
						else
						{
							if (publishProjectFiles ($group_id,
							                         $project_id,
							                         $url,
							                         $array_cookies,
							                         $g_xml_id,
							                         $error,
							                         $file,
							                         $function) == false)
							{
								if (empty ($error) == true)
								{
									$error = dgettext ("gforge-plugin-novapub", "publish_error_publish_files", array ($project_id, $group_id));
									$file = __FILE__;
									$function = __FUNCTION__;
								}
							}
							else
							{
								$ok = true;
							}
						}
					}
				}
			}
		}
	}
	return $ok;
}

function getPublisherSiteValue ($name, &$value)
{
	$ok = false;
	$query = "SELECT value FROM plugin_novapub_site WHERE name='". $name ."'";
	$result = db_query ($query);
	if ($result === false)
	{
		log_error ("Function db_query() failed with query '" . $query . "': " . db_error (), __FILE__, __FUNCTION__);
	}
	else
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
	return $ok;
}

function getPublisherSiteValues ($name, &$array_values)
{
	$ok = false;
	$query = "SELECT value FROM plugin_novapub_site WHERE name='". $name ."' ORDER BY value";
	$result = db_query ($query);
	if ($result === false)
	{
		log_error ("Function db_query() failed with query '" . $query . "': " . db_error (), __FILE__, __FUNCTION__);
	}
	else
	{
		$ok = true;
		$array_values = array ();
		$numrows = db_numrows ($result);
		for ($i = 0; $i < $numrows; $i++)
		{
			$array_values [] = db_result ($result, $i, "value");
		}
	}
	return $ok;
}

function addPublisherSiteValue ($name, $value)
{
	$ok = false;
	$query = "INSERT INTO plugin_novapub_site (name, value) VALUES ('". $name ."','" . $value . "')";
	$result = db_query ($query);
	if ($result === false)
	{
		log_error ("Function db_query() failed with query '" . $query . "': " . db_error (), __FILE__, __FUNCTION__);
	}
	else
	{
		$id = db_insertid ($result, "plugin_novapub_site", "id");
		if ($id == 0)
		{
			log_error ("Function db_insertid() failed after query '" . $query . "': " . db_error (), __FILE__, __FUNCTION__);
		}
		else
		{
			$ok = true;
		}
	}
	return $ok;
}

function updatePublisherSiteValue ($name, $value, $current_value = null)
{
	$ok = false;
	$query = "UPDATE plugin_novapub_site SET value='" . $value . "' WHERE name='" .  $name ."'";
	if ($current_value != null)
	{
		$query .= " AND value='". $current_value."'";
	}
	$result = db_query ($query);
	if ($result == false)
	{
		log_error ("Function db_query() failed with query '" . $query . "': " . db_error (), __FILE__, __FUNCTION__);
	}
	else
	{
		$ok = true;
	}
	return $ok;
}

function deletePublisherSiteValue ($name, $current_value = null)
{
	$ok = false;
	$query = "DELETE FROM plugin_novapub_site WHERE name='". $name ."'";
	if (isset ($current_value) == true)
	{
		$query .= " AND value='". $current_value."'";
	}
	$result = db_query ($query);
	if ($result === false)
	{
		log_error ("Function db_query() failed with query '" . $query . "': " . db_error (), __FILE__, __FUNCTION__);
	}
	else
	{
		$ok = true;
	}
	return $ok;
}

function getPublisherProjects ($group_id, &$array_ids, &$array_names)
{
	$ok = false;
	$query = "SELECT id,name FROM plugin_novapub_project WHERE group_id=" . $group_id . " ORDER BY name";
	$result = db_query ($query);
	if ($result === false)
	{
		log_error ("Function db_query() failed with query '" . $query . "': " . db_error (), __FILE__, __FUNCTION__);
	}
	else
	{
		$ok = true;
		$array_ids = array ();
		$array_names = array ();
		$numrows = db_numrows ($result);
		for ($i = 0; $i < $numrows; $i++)
		{
			$array_ids [] = db_result ($result, $i, "id");
			$array_names [] = db_result ($result, $i, "name");
		}
	}
	return $ok;
}

function createPublisherProject ($group_id, $name, $role_id, $url)
{
	$ok = false;
	$query = "INSERT INTO plugin_novapub_project (name,group_id,role_id,url) VALUES ('" . $name . "'," . $group_id . "," . $role_id . ",'" . $url . "')";
	$result = db_query ($query);
	if ($result === false)
	{
		log_error ("Function db_query() failed with query '" . $query . "': " . db_error (), __FILE__, __FUNCTION__);
	}
	else
	{	
		$project_id = db_insertid ($result, "plugin_novapub_site", "id");
		if ($project_id == 0)
		{
			log_error ("Function db_insertid() failed after query '" . $query . "': " . db_error (), __FILE__, __FUNCTION__);
		}
		else
		{
			$ok = true;
		}
	}
	return $ok;
}

function getPublisherProjectById ($project_id, &$name, &$group_id, &$role_id, &$url)
{
	$ok = false;
	$query = "SELECT name,group_id,role_id,url FROM plugin_novapub_project WHERE id=" . $project_id;
	$result = db_query ($query);
	if ($result === false)
	{
		log_error ("Function db_query() failed with query '" . $query . "': " . db_error (), __FILE__, __FUNCTION__);
	}
	else
	{
		$numrows = db_numrows ($result);
		if ($numrows == 1)
		{
			$ok = true;
			$name = db_result ($result, 0, "name");
			$group_id = db_result ($result, 0, "group_id");
			$role_id = db_result ($result, 0, "role_id");
			$url = db_result ($result, 0, "url");
		}
		else
		{
			log_error ("Function db_query() returned " . $numrows . " results with query '" . $query . "'", __FILE__, __FUNCTION__);
		}
	}
	return $ok;
}

function getPublisherProjectByName ($group_id, $name, &$project_id, &$role_id, &$url)
{
	$ok = false;
	$query = "SELECT id,role_id,url FROM plugin_novapub_project WHERE group_id=" . $group_id . " AND name='" . $name . "'";
	$result = db_query ($query);
	if ($result === false)
	{
		log_error ("Function db_query() failed with query '" . $query . "': " . db_error (), __FILE__, __FUNCTION__);
	}
	else
	{
		$numrows = db_numrows ($result);
		if ($numrows == 1)
		{
			$ok = true;
			$project_id = db_result ($result, 0, "id");
			$role_id = db_result ($result, 0, "role_id");
			$url = db_result ($result, 0, "url");
		}
		else
		{
			if ($numrows == 0)
			{
				$ok = true;
				$project_id = null;
				$url = null;
			}
			else
			{
				log_error ("Function db_query() returned " . $numrows . " results with query '" . $query . "'", __FILE__, __FUNCTION__);
			}
		}
	}
	return $ok;
}

function updatePublisherProject ($project_id, $name, $role_id, $url)
{
	$ok = false;
	$query = "UPDATE plugin_novapub_project SET name='" . $name . "',role_id=" . $role_id . ",url='" . $url . "' WHERE id=" . $project_id;
	$result = db_query ($query);
	if ($result === false)
	{
		log_error ("Function db_query() failed with query '" . $query . "': " . db_error (), __FILE__, __FUNCTION__);
	}
	else
	{
		$ok = true;
	}
	return $ok;
}

function deletePublisherProject ($project_id)
{
	$ok = false;
	$query = "DELETE FROM plugin_novapub_project WHERE id=" . $project_id;
	$result = db_query ($query);
	if ($result === false)
	{
		log_error ("Function db_query() failed with query '" . $query . "': " . db_error (), __FILE__, __FUNCTION__);
	}
	else
	{
		$ok = true;
	}
	return $ok;
}

?>
