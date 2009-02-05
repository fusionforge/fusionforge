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
require_once ("plugins/mantis/include/gforgefunctions.php");

// Path to wget binary
$wget_binary = "/usr/bin/wget";

/*
 * Return identifiers of groups using Subversion
 */
function getSubversionGroups (&$array_group_ids)
{
	$ok = false;
	$query = "SELECT group_plugin.group_id FROM group_plugin, plugins WHERE plugins.plugin_name='scmsvn' AND group_plugin.plugin_id=plugins.plugin_id";
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
		$row = 0;
		while ($row < $numrows)
		{
			array_push ($array_group_ids, db_result ($result, $row, 0));
			$row++;
		}
	}
	return $ok;
}

/*
 * Update Subversion repository of a group
 * to enable or disable Subversion to Mantis link
 */
function updateSubversionRepository ($group_id)
{
	global
		$svndir_prefix;

	$ok = false;
	$group = group_get_object ($group_id);
	if ((isset ($group) == false) || (is_object ($group) == false))
	{
		log_error ("Error while getting object for group " . $group_id, __FILE__, __FUNCTION__);
	}
	else
	{
		$repository = $svndir_prefix;
		if ($repository [strlen ($repository) - 1] != "/")
		{
			$repository .= "/";
		}
		$repository .= $group->getUnixName ();
		if (is_dir ($repository) == false)
		{
			log_error ("Repository '" . $repository . "' of group " . $group_id . " does not exist", __FILE__, __FUNCTION__);
		}
		else
		{
			if (($group->usesPlugin ("svn2mantis") == true)
			&&  ($group->usesPlugin ("mantis") == true))
			{
				if (getProjects ($group_id, $array_project_ids, $array_names) == false)
				{
					log_error ("Error while getting Mantis projects of group " . $group_id, __FILE__, __FUNCTION__);
				}
				else
				{
					if (count ($array_project_ids) > 0)
					{
						$ok = setSubversionToMantisLink ($group_id, $repository, true);
					}
					else
					{
						$ok = setSubversionToMantisLink ($group_id, $repository, false);
					}
				}
			}
			else
			{
				$ok = setSubversionToMantisLink ($group_id, $repository, false);
			}
		}
	}
	return $ok;
}

/*
 * Enable or disable Subversion to Mantis link
 */
function setSubversionToMantisLink ($group_id, $repository, $enable)
{
	global
		$wget_binary,
		$sys_use_ssl,
		$sys_default_domain,
		$sys_auth_private_key_passphrase_file,
		$sys_auth_private_key_passphrase_header;

	$ok = false;
	$post_commit_file = $repository . "/hooks/post-commit";
	$content = false;
	if (file_exists ($post_commit_file) == true)
	{
		$content = file_get_contents ($post_commit_file);
	}
	if (($content === false) || (strstr ($content, "/plugins/svn2mantis/commit.php") == false))
	{
		if ($enable == true)
		{
			$passphrase = file_get_contents ($sys_auth_private_key_passphrase_file);
			if ($passphrase === false)
			{
				log_error ("Error while reading passphrase in file '" . $sys_auth_private_key_passphrase_file . "'", __FILE__, __FUNCTION__);
			}
			else
			{
				if (($content === false) || (empty ($content) == true))
				{
					$content = "#!/bin/sh\n";
				}
				else
				{
					$content = "";
				}
				$content .= "\n# BEGIN gforge svn2mantis plugin";
				$content .= "\n" . $wget_binary . " -O - --no-proxy --header='" . $sys_auth_private_key_passphrase_header . ": " . trim ($passphrase) . "' http";
				if ($sys_use_ssl == true)
				{
					$content .= "s";
				}
				$content .= "://" . $sys_default_domain . "/plugins/svn2mantis/commit.php?group_id=" . $group_id . "\&revision=$2";
				$content .= "\n# END gforge svn2mantis plugin";
				$ok = writeFile ($post_commit_file, $content, true);
			}
		}
		else
		{
			$ok = true;
		}
	}
	else
	{
		if ($enable == false)
		{
			$content = preg_replace ("/\n# BEGIN gforge svn2mantis plugin.*# END gforge svn2mantis plugin/s", "", $content);
			$ok = writeFile ($post_commit_file, $content, false);
		}
		else
		{
			$ok = true;
		}
	}
	if (file_exists ($post_commit_file) == true)
	{
		if (chmod ($post_commit_file, 0750) == false)
		{
			$ok = false;
			log_error ("Error while changing mode of file '" . $post_commit_file . "'", __FILE__, __FUNCTION__);
		}
	}
	return $ok;
}

/*
 * Write file with exclusive locking
 */
function writeFile ($file, $content, $append)
{
	$ok = false;
	if ($append == true)
	{
		$fd = fopen ($file, "a");
	}
	else
	{
		$fd = fopen ($file, "w");
	}
	if ($fd === false)
	{
		log_error ("Error while opening file '" . $file . "'", __FILE__, __FUNCTION__);
	
	}
	else
	{
		if (flock ($fd, LOCK_EX) == false)
		{
			log_error ("Error while locking file '" . $file . "'", __FILE__, __FUNCTION__);
		}
		else
		{
			if (empty ($content) == false)
			{
				if (fwrite ($fd, $content) == false)
				{
					log_error ("Error while writing to file '" . $file . "'", __FILE__, __FUNCTION__);
				}
				else
				{
					$ok = true;
				}
			}
			else
			{
				$ok = true;
			}
			if (flock ($fd, LOCK_UN) == false)
			{
				$ok = false;
				log_error ("Error while unlocking file '" . $file . "'", __FILE__, __FUNCTION__);
			}
		}
		if (fclose ($fd) == false)
		{
			$ok = false;
			log_error ("Error while closing file '" . $file . "'", __FILE__, __FUNCTION__);
		}
	}
	return $ok;
}

?>
