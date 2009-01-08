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

require_once ("auth_common.php");
require_once ("core.php");

session_start ();
error_reporting (E_ALL);
openlog ("mantis", LOG_PID | LOG_PERROR, LOG_LOCAL5);
$username = "";
if (authenticate (config_get ("gforge_servers_public_keys_dir"), $username) == true)
{
	$_SESSION ["gforge_authenticated"] = 1;
	if ((isset ($username) == true) && (strlen ($username) > 0))
	{
		$user_id = user_get_id_by_name ($username);
		if ($user_id !== false)
		{
			if (auth_attempt_script_login ($username, null) == true)
			{ 
				auth_set_cookies (user_get_id_by_name ($username), false);
			}
			else
			{
				syslog (LOG_ERR, __FILE__ . " - Function auth_attempt_script_login() failed for username '" . $username . "'");
				header ("AuthResult: failure", true);
			}
		}
		else
		{
			syslog (LOG_ERR, __FILE__ . " - Unknown user '" . $username . "'");
			header ("AuthResult: failure", true);
		}
	}
	else
	{
		syslog (LOG_DEBUG, __FILE__ . " - Username is not defined");
	}
}
else
{
	$_SESSION ["gforge_authenticated"] = 0;
}
?><html>
<head>
<title>GForge authentication</title>
</head>
<body>
GForge authentication
</body>
</html>
