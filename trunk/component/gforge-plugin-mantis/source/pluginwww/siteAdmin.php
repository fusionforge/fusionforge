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

require_once ("pre.php");
require_once ("www/admin/admin_utils.php");
require_once ("common/include/session.php");
require_once ("plugins/mantis/include/gforgefunctions.php");

session_require (array ("group" => "1", "admin_flags" => "A"));
$error_submit = "";
if ((isset ($action) == true))
{
	$url = trim ($url);
	switch ($action)
	{
		case "changeURL" :
			$url = trim ($url);
			if (strlen ($url) > 0)
			{
				db_begin ();
				if (updateDefaultEntry ("url", $url, $old_url) == false)
				{
					db_rollback ();
					exit_error ($Language->getText ("gforge-plugin-mantis", "title_site_admin"), $Language->getText ("gforge-plugin-mantis", "database_error"));
				}
				else
				{
					$query = "UPDATE plugin_mantis_project SET url='" . $url . "' WHERE url='" . $old_url . "'";
					$result = db_query ($query);
					if ($result === false)
					{	
						log_error ("Function db_query() failed with query '" . $query . "': " . db_error (), __FILE__, __FUNCTION__);
						db_rollback ();
						exit_error ($Language->getText ("gforge-plugin-mantis", "title_site_admin"), $Language->getText ("gforge-plugin-mantis", "database_error"));
					}
					else
					{
						db_commit ();
					}
			
				}
			}
			else
			{
				$error_submit = "<h3>". $Language->getText ("gforge-plugin-mantis", "empty_url", $url) ."</h3>";
			}
			break;
		case "removeURL" :
			$query = "SELECT p.name,g.group_name FROM plugin_mantis_project p,groups g WHERE p.url='" . $url . "' AND p.gforge_id=g.group_id";
			$result = db_query ($query);
			if ($result !== false)
			{
				$numrows = db_numrows ($result);
				if ($numrows > 0)
				{
					$error_submit = "<h3>".$Language->getText ("gforge-plugin-mantis", "url_is_used", $url). "</h3>\n<ul>\n";
					$index = 0;
					while ($index < $numrows)
					{
						$error_submit .= "<li>" . $Language->getText ("gforge-plugin-mantis", "url_is_used_item", array (db_result ($result, $index, 0), db_result ($result, $index, 1))) . "</li>\n";
						$index++;

					}
					$error_submit .= "</ul>";
				}
				else
				{
					if (deleteDefaultEntry ("url", $url) == false)
					{
						exit_error ($Language->getText ("gforge-plugin-mantis", "title_site_admin"), $Language->getText ("gforge-plugin-mantis", "database_error"));
					}
				}
			}	
			else
			{
				log_error ("Function db_query() failed with query '" . $query . "': " . db_error (), __FILE__,__FUNCTION__);
				exit_error ($Language->getText ("gforge-plugin-mantis", "title_site_admin"), $Language->getText ("gforge-plugin-mantis", "database_error"));
			}
			break;
		case "addUrl" :
			$url = trim ($url);
			if (strlen ($url) > 0)
			{
				if (getDefaultEntries ("url", $urls) == true)
				{
					if (in_array ($url, $urls) == false)
					{
						if (addDefaultEntry ("url", $url) == false)
						{
							exit_error ($Language->getText ("gforge-plugin-mantis", "title_site_admin"), $Language->getText ("gforge-plugin-mantis", "database_error"));
						}
					}
					else
					{
						$error_submit = "<h3>". $Language->getText ("gforge-plugin-mantis", "url_already_exists", $url) ."</h3>";
					}
				}
				else
				{
					 exit_error ($Language->getText ("gforge-plugin-mantis", "title_site_admin"), $Language->getText ("gforge-plugin-mantis", "database_error"));
				}
			}	
			else
			{
				$error_submit = "<h3>". $Language->getText ("gforge-plugin-mantis", "empty_url", $url) ."</h3>";
			}
			break;
		case "changeValues" :
			if (isset ($resetValues) == true)
			{
				if ((deleteDefaultEntry ("visibility") == false)
				||  (deleteDefaultEntry ("status") == false)
				||  (deleteDefaultEntry ("css_regex_1") == false)
				||  (deleteDefaultEntry ("css_regex_2") == false)
				||  (deleteDefaultEntry ("css_regex_3") == false)
				||  (deleteDefaultEntry ("css_regex_4") == false))
				{
					exit_error ($Language->getText ("gforge-plugin-mantis", "title_site_admin"), $Language->getText ("gforge-plugin-mantis", "database_error"));
				}
			}
			else
			{
				if ((updateDefaultEntry ("visibility", trim ($visibility)) == false)
				||  (updateDefaultEntry ("status", trim ($status)) == false)
				||  (updateDefaultEntry ("css_regex_1", trim ($css_regex_1)) == false)
				||  (updateDefaultEntry ("css_regex_2", trim ($css_regex_2)) == false)
				||  (updateDefaultEntry ("css_regex_3", trim ($css_regex_3)) == false)
				||  (updateDefaultEntry ("css_regex_4", trim ($css_regex_4)) == false))
				{
					exit_error ($Language->getText ("gforge-plugin-mantis", "title_site_admin"), $Language->getText ("gforge-plugin-mantis", "database_error"));
				}
			}	
			break;
		}
}
$status = null;
$visibility = null;
$css_regex_1 = null;
$css_regex_2 = null;
$css_regex_3 = null;
$css_regex_4 = null;
if ((getDefaultEntry ("status", $status) == false)
||  (getDefaultEntry ("visibility", $visibility) == false)
||  (getDefaultEntry ("css_regex_1", $css_regex_1) == false)
||  (getDefaultEntry ("css_regex_2", $css_regex_2) == false)
||  (getDefaultEntry ("css_regex_3", $css_regex_3) == false)
||  (getDefaultEntry ("css_regex_4", $css_regex_4) == false))
{
	exit_error ($Language->getText ("gforge-plugin-mantis", "title_site_admin"),$Language->getText ("gforge-plugin-mantis", "database_error"));
}
if (isset ($visibility) == false)
{
	$visibility = 1;
	if (addDefaultEntry ("visibility", $visibility) == false)
	{
		exit_error ($Language->getText ("gforge-plugin-mantis", "title_site_admin"), $Language->getText ("gforge-plugin-mantis", "database_error"));
	}
}
if (isset ($status) == false)
{
	$status = "D";
	if (addDefaultEntry ("status", $status) == false)
	{
		exit_error ($Language->getText ("gforge-plugin-mantis", "title_site_admin"), $Language->getText ("gforge-plugin-mantis", "database_error"));	
	}
}
if ((isset ($css_regex_1) == false)
&&  (isset ($css_regex_2) == false)
&&  (isset ($css_regex_3) == false)
&&  (isset ($css_regex_4) == false))
{
	$css_regex_1 = "/^(body|td|div)[,\s][^}]*}/m ==> /* Removed */";
	$css_regex_2 = "";
	$css_regex_3 = "";
	$css_regex_4 = "";
	if (addDefaultEntry ("css_regex_1", addslashes ($css_regex_1)) == false)
	{
		exit_error ($Language->getText ("gforge-plugin-mantis", "title_site_admin"), $Language->getText ("gforge-plugin-mantis", "database_error"));
	}
	if (addDefaultEntry ("css_regex_2", $css_regex_2) == false)
	{
		exit_error ($Language->getText ("gforge-plugin-mantis", "title_site_admin"), $Language->getText ("gforge-plugin-mantis", "database_error"));
	}
	if (addDefaultEntry ("css_regex_3", $css_regex_3) == false)
	{
		exit_error ($Language->getText ("gforge-plugin-mantis", "title_site_admin"), $Language->getText ("gforge-plugin-mantis", "database_error"));
	}
	if (addDefaultEntry ("css_regex_4", $css_regex_4) == false)
	{
		exit_error ($Language->getText ("gforge-plugin-mantis", "title_site_admin"), $Language->getText ("gforge-plugin-mantis", "database_error"));
	}
}
if (isset ($css_regex_1) == false)
{
	$css_regex_1 = "";
	if (addDefaultEntry ("css_regex_1", $css_regex_1) == false)
	{
		exit_error ($Language->getText ("gforge-plugin-mantis", "title_site_admin"), $Language->getText ("gforge-plugin-mantis", "database_error"));
	}
}
if (isset ($css_regex_2) == false)
{
	$css_regex_2 = "";
	if (addDefaultEntry ("css_regex_2", $css_regex_2) == false)
	{
		exit_error ($Language->getText ("gforge-plugin-mantis", "title_site_admin"), $Language->getText ("gforge-plugin-mantis", "database_error"));
	}
}
if (isset ($css_regex_3) == false)
{
	$css_regex_3 = "";
	if (addDefaultEntry ("css_regex_3", $css_regex_3) == false)
	{
		exit_error ($Language->getText ("gforge-plugin-mantis", "title_site_admin"), $Language->getText ("gforge-plugin-mantis", "database_error"));
	}
}
if (isset ($css_regex_4) == false)
{
	$css_regex_4 = "";
	if (addDefaultEntry ("css_regex_4", $css_regex_4) == false)
	{
		exit_error ($Language->getText ("gforge-plugin-mantis", "title_site_admin"), $Language->getText ("gforge-plugin-mantis", "database_error"));
	}
}
if (getDefaultEntries ("url", $urls) == false)
{
	exit_error ($Language->getText ("gforge-plugin-mantis", "title_site_admin"),$Language->getText ("gforge-plugin-mantis", "database_error"));
}
$nb_url = count ($urls);
site_admin_header (array ("title" => $Language->getText ("gforge-plugin-mantis", "title_site_admin")));
if (strlen ($error_submit) > 0)
{
	echo $error_submit;
	echo "\n<p>\n<a href=\"" . $PHP_SELF . "\">" . $Language->getText ("gforge-plugin-mantis", "back_to_site_admin") ."</a>";
}
else
{
?>
<h2><? echo $Language->getText ("gforge-plugin-mantis", "title_site_admin"); ?><h2>
<? echo $HTML->boxMiddle ($Language->getText ("gforge-plugin-mantis", "mantis_urls"), false, false); ?>
<p>
<b><? echo $Language->getText ("gforge-plugin-mantis", "url"); ?></b>
<br>
<? echo ($Language->getText ("gforge-plugin-mantis", "url_info",$_SERVER["SERVER_NAME"]));?>
<table>
<?
	for ($i = 0; $i < $nb_url; $i++)
	{
?>	<tr>
		<form action="<? echo $PHP_SELF; ?>" method="post">
		<input type="hidden" name="action" value="changeURL">
		<input type="hidden" name="old_url" value="<? echo $urls [$i]; ?>">
		<td><input size="40" maxlength="128" type="text" name="url" value="<? echo $urls [$i]; ?>"></td>
		<td><input type="submit" name="changeURL" value="<? echo $Language->getText ("gforge-plugin-mantis", "submit_modify_url"); ?>"></td>
		</form>
		<form action="<? echo $PHP_SELF; ?>" method="post">
		<input type="hidden" name="action" value="removeURL">
		<input type="hidden" name="url" value="<? echo $urls [$i]; ?>">
		<td><input type="submit" name="removeURL" value="<? echo $Language->getText ("gforge-plugin-mantis", "submit_remove_url"); ?>"></td>
		</form>
	</tr>
<?
	}
?>	<tr>
		<form action="<? echo "$PHP_SELF"; ?>" method="post">
		<input type="hidden" name="action" value="addUrl">
		<td><input size="40" maxlength="128" type="text" name="url"></td>
		<td><input type="submit" name="updateURL" value="<? echo $Language->getText ("gforge-plugin-mantis", "submit_add_url"); ?>"></td>
		</form>
	</tr>
</table>
<? echo $HTML->boxMiddle ($Language->getText ("gforge-plugin-mantis", "mantis_settings"), false, false); ?>
<p>
<b><? echo $Language->getText ("gforge-plugin-mantis", "visibility"); ?></b>
<br>
<form action="<? echo "$PHP_SELF"; ?>" name="Udapte" method="post">
<input type="hidden" name="action" value="changeValues">
<input type="radio" name="visibility" value="1" <? if ($visibility == 1) { echo " checked"; } ?>><? echo $Language->getText ("gforge-plugin-mantis", "public");?>
<br>
<input type="radio" name="visibility" value="0" <? if ($visibility == 0) { echo " checked"; } ?>><? echo ($Language->getText ("gforge-plugin-mantis", "private"));?>
<p>
<b><? echo $Language->getText ("gforge-plugin-mantis", "status"); ?></b>
<br>
<select name="status">
<option value="D"<? if ($status == "D"){echo " selected";}?>><? echo $Language->getText ("gforge-plugin-mantis", "development");?></option>
<option value="R"<? if ($status == "R"){echo " selected";}?>><? echo $Language->getText ("gforge-plugin-mantis", "release");?></option>
<option value="S"<? if ($status == "S"){echo " selected";}?>><? echo $Language->getText ("gforge-plugin-mantis", "stable");?></option>
<option value="O"<? if ($status == "O"){echo " selected";}?>><? echo $Language->getText ("gforge-plugin-mantis", "obsolete");?></option>
</select>
<p>
<b><? echo ($Language->getText ("gforge-plugin-mantis", "css")); ?></b>
<br>
<? echo ($Language->getText ("gforge-plugin-mantis", "css_info"));?>
<br>
<input size="80" type="text" name="css_regex_1" value="<? echo $css_regex_1; ?>">
<br>
<input size="80" type="text" name="css_regex_2" value="<? echo $css_regex_2; ?>">
<br>
<input size="80" type="text" name="css_regex_3" value="<? echo $css_regex_3; ?>">
<br>
<input size="80"  type="text" name="css_regex_4" value="<? echo $css_regex_4; ?>">
<br>
<input type="submit" name="setValues" value="<? echo ($Language->getText ("gforge-plugin-mantis", "submit_mantis_settings")); ?>" />
<input type="submit" name="resetValues" value="<? echo ($Language->getText ("gforge-plugin-mantis", "reset_mantis_settings")); ?>" />
</form>
<?php
}
site_admin_footer (array ());
?>
