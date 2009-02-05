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

require_once ('../../env.inc.php');
require_once ($gfwww."include/pre.php");
require_once ("www/admin/admin_utils.php");
require_once ("common/include/session.php");
require_once ("plugins/archiva/include/functions.php");

session_require (array ("group" => "1", "admin_flags" => "A"));
$error_submit = "";
if ((isset ($action) == true) && ($action == "update"))
{
	$remote_url = trim ($remote_url);
	if (empty ($remote_url) == true)
	{
		$error_submit = "<h3>". dgettext ("gforge-plugin-archiva", "empty_remote_url") ."</h3>";
	}
	else
	{
		$admin_username = trim ($admin_username);
		if (empty ($admin_username) == true)
		{
			$error_submit = "<h3>". dgettext ("gforge-plugin-archiva", "empty_admin_username") ."</h3>";
		}
		else
		{
			$admin_password = trim ($admin_password);
			if (empty ($admin_password) == true)
			{
				$error_submit = "<h3>". dgettext ("gforge-plugin-archiva", "empty_admin_password") ."</h3>";
			}
			else
			{
				if ((updateArchivaConfigEntry ("remote_url", $remote_url) == false)
				||  (updateArchivaConfigEntry ("admin_username", trim ($admin_username)) == false)
				||  (updateArchivaConfigEntry ("admin_password", trim ($admin_password)) == false))
				{
					exit_error (dgettext ("gforge-plugin-archiva", "title_site_admin"), dgettext ("gforge-plugin-archiva", "database_error"));
				}
			}
		}
	}
}	
$remote_url = null;
$admin_username = null;
$admin_password = null;
if ((getArchivaConfigEntry ("remote_url", $remote_url) == false)
||  (getArchivaConfigEntry ("admin_username", $admin_username) == false)
||  (getArchivaConfigEntry ("admin_password", $admin_password) == false))
{
	exit_error (dgettext ("gforge-plugin-archiva", "title_site_admin"),dgettext ("gforge-plugin-archiva", "database_error"));
}
if (isset ($remote_url) == false)
{
	$remote_url = "";
	if (addArchivaConfigEntry ("remote_url", $remote_url) == false)
	{
		exit_error (dgettext ("gforge-plugin-archiva", "title_site_admin"), dgettext ("gforge-plugin-archiva", "database_error"));
	}
}
if (isset ($admin_username) == false)
{
	$admin_username = "";
	if (addArchivaConfigEntry ("admin_username", $admin_username) == false)
	{
		exit_error (dgettext ("gforge-plugin-archiva", "title_site_admin"), dgettext ("gforge-plugin-archiva", "database_error"));	
	}
}
if (isset ($admin_password) == false)
{
	$admin_password = "";
	if (addArchivaConfigEntry ("admin_password", $admin_password) == false)
	{
		exit_error (dgettext ("gforge-plugin-archiva", "title_site_admin"), dgettext ("gforge-plugin-archiva", "database_error"));
	}
}
site_admin_header (array ("title" => dgettext ("gforge-plugin-archiva", "title_site_admin")));
if (strlen ($error_submit) > 0)
{
	echo $error_submit;
	echo "\n<p>\n<a href=\"" . $PHP_SELF . "\">" . dgettext ("gforge-plugin-archiva", "back_to_site_admin") ."</a>";
}
else
{
?>
<h2><? echo dgettext ("gforge-plugin-archiva", "title_site_admin"); ?><h2>
<? echo $HTML->boxMiddle (dgettext ("gforge-plugin-archiva", "plugin_config"), false, false); ?>
<p>
<form action="<? echo "$PHP_SELF"; ?>" name="Udapte" method="post">
<b><? echo dgettext ("gforge-plugin-archiva", "remote_url"); ?></b>
<br>
<? echo (printf( dgettext ( "gforge-plugin-archiva" ,  "remote_url_info" ) , $_SERVER ["SERVER_NAME"] )); ?>
<br>
<input type="text" name="remote_url" size="40" maxlength="128" value="<? echo $remote_url; ?>">
<p>
<b><? echo dgettext ("gforge-plugin-archiva", "admin_username"); ?></b>
<br>
<? echo (dgettext ("gforge-plugin-archiva", "admin_username_info")); ?>
<br>
<input type="text" name="admin_username" size="40" maxlength="80" value="<? echo $admin_username; ?>">
<p>
<b><? echo dgettext ("gforge-plugin-archiva", "admin_password"); ?></b>
<br>
<? echo (dgettext ("gforge-plugin-archiva", "admin_password_info")); ?>
<br>
<input type="text" name="admin_password" size="40" maxlength="40" value="<? echo $admin_password; ?>">
<p>
<input type="hidden" name="action" value="update">
<input type="submit" name="update" value="<? echo (dgettext ("gforge-plugin-archiva", "submit_archiva_config")); ?>" />
</form>
<? echo $HTML->boxMiddle (dgettext ("gforge-plugin-archiva", "archiva_admin"), false, false); ?>
<p>
<a href="proxy/"><? echo (dgettext ("gforge-plugin-archiva", "click_here")); ?></a>
<?php
}
site_admin_footer (array ());
?>
