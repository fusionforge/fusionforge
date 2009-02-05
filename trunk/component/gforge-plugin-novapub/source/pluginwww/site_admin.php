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

require_once ("../../env.inc.php");
require_once ($gfwww."include/pre.php");
require_once ("www/admin/admin_utils.php");
require_once ("common/novaforge/log.php");
require_once ("plugins/novapub/include/functions.php");

session_require (array ("group" => "1", "admin_flags" => "A"));
$error_submit = "";
if ((isset ($action) == true))
{
	$url = trim ($url);
	switch ($action)
	{
		case "add" :
			$url = trim ($url);
			if (strlen ($url) > 0)
			{
				if (getPublisherSiteValues ("url", $urls) == true)
				{
					if (in_array ($url, $urls) == false)
					{
						if (addPublisherSiteValue ("url", $url) == false)
						{
							exit_error (dgettext ("gforge-plugin-novapub", "title_site_admin"), dgettext ("gforge-plugin-novapub", "database_error"));
						}
					}
					else
					{
						$error_submit = "<h3>". sprintf( dgettext ( "gforge-plugin-novapub" ,  "url_already_exists" ) , $url) ."</h3>";
					}
				}
				else
				{
					exit_error (dgettext ("gforge-plugin-novapub", "title_site_admin"), dgettext ("gforge-plugin-novapub", "database_error"));
				}
			}
			else
			{
				$error_submit = "<h3>". sprintf( dgettext ( "gforge-plugin-novapub" ,  "empty_url" ) , $url) ."</h3>";
			}
			break;
		case "update" :
			$url = trim ($url);
			if (strlen ($url) > 0)
			{
				db_begin ();
				if (updatePublisherSiteValue ("url", $url, $old_url) == false)
				{
					db_rollback ();
					exit_error (dgettext ("gforge-plugin-novapub", "title_site_admin"), dgettext ("gforge-plugin-novapub", "database_error"));
				}
				else
				{
					$query = "UPDATE plugin_novapub_project SET url='" . $url . "' WHERE url='" . $old_url . "'";
					$result = db_query ($query);
					if ($result === false)
					{	
						log_error ("Function db_query() failed with query '" . $query . "': " . db_error (), __FILE__, __FUNCTION__);
						db_rollback ();
						exit_error (dgettext ("gforge-plugin-novapub", "title_site_admin"), dgettext ("gforge-plugin-novapub", "database_error"));
					}
					else
					{
						db_commit ();
					}
			
				}
			}
			else
			{
				$error_submit = "<h3>". sprintf( dgettext ( "gforge-plugin-novapub" ,  "empty_url" ) , $url) ."</h3>";
			}
			break;
		case "delete" :
			$query = "SELECT p.name,g.group_name FROM plugin_novapub_project p,groups g WHERE p.url='" . $url . "' AND p.group_id=g.group_id";
			$result = db_query ($query);
			if ($result !== false)
			{
				$numrows = db_numrows ($result);
				if ($numrows > 0)
				{
					$error_submit = "<h3>".sprintf( dgettext ( "gforge-plugin-novapub" ,  "url_is_used" ) , $url). "</h3>\n<ul>\n";
					$index = 0;
					while ($index < $numrows)
					{
						$error_submit .= "<li>" . dgettext ("gforge-plugin-novapub", "url_is_used_item", array (db_result ($result, $index, 0), db_result ($result, $index, 1))) . "</li>\n";
						$index++;

					}
					$error_submit .= "</ul>";
				}
				else
				{
					if (deletePublisherSiteValue ("url", $url) == false)
					{
						exit_error (dgettext ("gforge-plugin-novapub", "title_site_admin"), dgettext ("gforge-plugin-novapub", "database_error"));
					}
				}
			}	
			else
			{
				log_error ("Function db_query() failed with query '" . $query . "': " . db_error (), __FILE__,__FUNCTION__);
				exit_error (dgettext ("gforge-plugin-novapub", "title_site_admin"), dgettext ("gforge-plugin-novapub", "database_error"));
			}
			break;
		}
}
if (getPublisherSiteValues ("url", $urls) == false)
{
	exit_error (dgettext ("gforge-plugin-novapub", "title_site_admin"),dgettext ("gforge-plugin-novapub", "database_error"));
}
$nb_url = count ($urls);
site_admin_header (array ("title" => dgettext ("gforge-plugin-novapub", "title_site_admin")));
if (strlen ($error_submit) > 0)
{
	echo $error_submit;
	echo "\n<p>\n<a href=\"" . $PHP_SELF . "\">" . dgettext ("gforge-plugin-novapub", "back_to_site_admin") ."</a>";
}
else
{
?>
<h2><? echo dgettext ("gforge-plugin-novapub", "title_site_admin"); ?><h2>
<? echo $HTML->boxMiddle (dgettext ("gforge-plugin-novapub", "available_urls"), false, false); ?>
<p>
<b><? echo dgettext ("gforge-plugin-novapub", "url"); ?></b>
<br>
<? echo (sprintf( dgettext ( "gforge-plugin-novapub" ,  "url_info" ) , $_SERVER ["SERVER_NAME"]));?>
<table>
<?
	for ($i = 0; $i < $nb_url; $i++)
	{
?>	<tr>
		<td><form action="<? echo $PHP_SELF; ?>" method="post">
			<input type="hidden" name="action" value="update">
			<input type="hidden" name="old_url" value="<? echo $urls [$i]; ?>">
			<input type="text" name="url" size="40" maxlength="128" value="<? echo $urls [$i]; ?>">
			<input type="submit" name="submit" value="<? echo dgettext ("gforge-plugin-novapub", "submit_update_url"); ?>">
		</form></td>
		<td><form action="<? echo $PHP_SELF; ?>" method="post">
			<input type="hidden" name="action" value="delete">
			<input type="hidden" name="url" value="<? echo $urls [$i]; ?>">
			<input type="submit" name="submit" value="<? echo dgettext ("gforge-plugin-novapub", "submit_delete_url"); ?>">
		</form></td>
	</td></tr>
<?
	}
?>	<tr>
		<td colspan="2"><form action="<? echo "$PHP_SELF"; ?>" method="post">
			<input type="hidden" name="action" value="add">
			<input type="text" name="url" size="40" maxlength="128">
			<input type="submit" name="submit" value="<? echo dgettext ("gforge-plugin-novapub", "submit_add_url"); ?>">
		</form></td>
	</tr>
</table>
<?php
}
site_admin_footer (array ());
?>
