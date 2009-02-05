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

require_once ("squal_pre.php");
require_once ("common/include/cron_utils.php");
require_once ("common/novaforge/log.php");
require_once ("plugins/novapub/include/functions.php");

$cron_entry_log = "";
log_cron_error (date ("d/m/Y H:i:s") . " - Start of NovaForge publication", __FILE__);
$query = "SELECT user_id FROM user_group WHERE admin_flags='A' AND group_id='1'";
$result = db_query ($query);
if ($result === false)
{
	log_cron_error ("Function db_query() failed with query '" . $query . "': " . db_error (), __FILE__);
}
else
{
	if (db_numrows ($result) == 0)
	{
		log_cron_error ("No site administrator found in database", __FILE__);
	}
	else
	{
		session_set_new (db_result($result, 0, 0));
		$LUSER = &session_get_user ();
		if (getGroupsToPublish ($array_group_ids) == false)
		{
			log_cron_error ("Error while getting groups to publish", __FILE__);
		}
		else
		{
			foreach ($array_group_ids as $group_id) 
			{
				$group = group_get_object ($group_id);
				if ((isset ($group) == false) || (is_object ($group) == false))
				{
					log_cron_error ("Error while getting object for group " . $group_id, __FILE__);
				}
				else
				{
					if (getPublisherProjects ($group_id, $array_ids, $array_names) == false)
					{
						log_cron_error ("Error while getting publishing projects for group " . $group_id, __FILE__);
					}
					else
					{
						$i = 0;
						while ($i < count ($array_ids))
						{
							if (publishProject ($group_id,
							                    $group->getUnixName (),
							                    $array_ids [$i],
							                    $error,
							                    $file,
							                    $function) == false)
							{
								log_cron_error ("Error while publishing project " .$array_ids [$i] . " of group " . $group_id, __FILE__);
								if (empty ($error) == false)
								{
									log_cron_error ($error, $file, $function, $class);
								}
							}
							$i++;
						}
					}
				}
			}
		}
	}
}
log_cron_error (date ("d/m/Y H:i:s") . " - End of NovaForge publication", __FILE__);
cron_entry (25, $cron_entry_log);
session_logout ();

function log_cron_error ($message, $file = null, $function = null, $class = null)
{
	global
		$cron_entry_log;

	if (strlen ($cron_entry_log) > 0)
	{
		$cron_entry_log .= "\n";
	}
	$cron_entry_log .= $message;
	log_error ($message, $file, $function, $class);
}

?>
