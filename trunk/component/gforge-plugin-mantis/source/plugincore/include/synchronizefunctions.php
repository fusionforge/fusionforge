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

require_once ("common/novaforge/auth.php");
require_once ("common/novaforge/log.php");
require_once ("plugins/mantis/include/gforgefunctions.php");
require_once ("plugins/mantis/include/mantisfunctions.php");

function synchronize ($group_id,
                      &$array_errors)
{
	global $Language;

	$ok = true;
	$array_errors = array ();
	if (getMantisInstances ($group_id, $array_urls) == true)
	{
		$index = 0;
		while ($index < count ($array_urls))
		{
			if (synchronizeUsers ($array_urls [$index], $group_id) == false)
			{
				$ok = false;
				$array_errors [] = $Language->getText ("gforge-plugin-mantis", "sync_error_users", $array_urls [$index]);
			}
			$index++;
		}
	}
	else
	{
		$ok = false;
		$array_errors [] = $Language->getText ("gforge-plugin-mantis", "sync_error_servers");
	}
	if (getProjects ($group_id, $array_project_ids, $array_names) == true)
	{
		$index = 0;
		while ($index < count ($array_project_ids))
		{
			if (synchronizeProject ($array_project_ids [$index]) == false)
			{
				$ok = false;
				$array_errors [] = $Language->getText ("gforge-plugin-mantis", "sync_error_project", $array_names [$index]);
			}
			if (synchronizeRoles ($array_project_ids [$index]) == false)
			{
				$ok = false;
				$array_errors [] = $Language->getText ("gforge-plugin-mantis", "sync_error_roles", $array_names [$index]);
			}
			$index++;
		}
	}
	else
	{
		$ok = false;
		$array_errors [] = $Language->getText ("gforge-plugin-mantis", "sync_error_projects");
	}
	return $ok;
}

function synchronizeUsers ($url, $group_id)
{
	$ok = false;
	if (getMantisUsers ($url,
	                    $array_mantis_ids,
	                    $array_mantis_names,
	                    $array_mantis_passwords,
	                    $array_mantis_realnames,
	                    $array_mantis_mails,
	                    $array_mantis_statuses) == true)
{
		if (getGForgeUsers ($group_id,
		                    $url,
		                    $array_gforge_names,
		                    $array_gforge_passwords,
		                    $array_gforge_realnames,
		                    $array_gforge_mails,
		                    $array_gforge_statuses,
		                    $array_gforge_roles) == true)
		{
			$ok = true;
			for ($index = 0; $index < count ($array_gforge_names); $index++)
			{
				$index2 = array_search ($array_gforge_names [$index], $array_mantis_names);
				if ($index2 === false)
				{
					$ok = createMantisUser ($url,
					                        $array_gforge_names [$index],
					                        $array_gforge_passwords [$index],
					                        $array_gforge_realnames [$index],
								$array_gforge_mails [$index],
								$array_gforge_statuses [$index]);
				}
				else
				{
					if (($array_gforge_passwords [$index] != $array_mantis_passwords [$index2])
					||  ($array_gforge_realnames [$index] != $array_mantis_realnames [$index2])
					||  ($array_gforge_mails [$index] != $array_mantis_mails [$index2])
					||  ($array_gforge_statuses [$index] != $array_mantis_statuses [$index2]))
					{
						$ok = updateMantisUser ($url,
						                        $array_mantis_ids [$index2],
						                        $array_gforge_names [$index],
						                        $array_gforge_passwords [$index],
						                        $array_gforge_realnames [$index],
						                        $array_gforge_mails [$index],
						                        $array_gforge_statuses [$index]);
					}
				}
				if ($ok == false)
				{
					break;
				}
			}
		}
	}
	return $ok;
}

function synchronizeProject ($project_id)
{
	$ok = false;
	if (getProject ($project_id,
	                $gforge_gforge_id,
	                $gforge_url,
	                $gforge_mantis_id,
	                $gforge_name,
        	        $gforge_description,
	                $gforge_status,
	                $gforge_visibility,
	                $gforge_css_regex_1,
	                $gforge_css_regex_2,
	                $gforge_css_regex_3,
	                $gforge_css_regex_4) == true)
	{
		if (getMantisProject ($gforge_url,
		                      $gforge_mantis_id,
		                      $mantis_name,
		                      $mantis_description,
		                      $mantis_status,
		                      $mantis_visibility) == true)
		{
			if (($mantis_name != $gforge_name)
			||  ($mantis_description != $gforge_description)
			||  ($mantis_status != $gforge_status)
			||  ($mantis_visibility != $gforge_visibility))
			{
				$ok = updateMantisProject ($gforge_url,
				                           $gforge_mantis_id,
				                           $gforge_name,
				                           $gforge_description,
				                           $gforge_status,
				                           $gforge_visibility);
			}
			else
			{
				$ok = true;
			}
		}
	}
	return $ok;
}

function synchronizeRoles ($project_id)
{
	$ok = false;
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
	                $project_css_regex_4) == true)
	{
		if (getMantisUsers ($project_url,
		                    $array_mantis_ids,
		                    $array_mantis_names,
		                    $array_mantis_passwords,
		                    $array_mantis_realnames,
		                    $array_mantis_mails,
		                    $array_mantis_statuses) == true)
		{
			if (getGForgeUsers ($group_id,
			                    null,
			                    $array_gforge_names,
			                    $array_gforge_passwords,
			                    $array_gforge_realnames,
			                    $array_gforge_mails,
			                    $array_gforge_statuses,
			                    $array_gforge_roles) == true)
			{
				$ok = getRolesMapping ($project_id, $array_roles_mapping);
			}
		}
	}
	if ($ok == true)
	{
		$array_role_ids = array ();
		for ($index = 0; $index < count ($array_gforge_names); $index++)
		{
			if (array_key_exists ($array_gforge_roles [$index], $array_roles_mapping) == true)
			{
				$array_role_ids [] = $array_roles_mapping [$array_gforge_roles [$index]];
			}
			else
			{
				$array_role_ids [] = 0;
			}
		}
		$ok = setMantisRoles ($project_url,
		                      $project_mantis_id,
		                      $array_gforge_names,
		                      $array_role_ids);
	}
	return $ok;
}

?>
