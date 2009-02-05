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

require_once ("common/include/Error.class.php");
require_once ("common/include/Plugin.class.php");

class novapubPlugin extends Plugin 
{

	function novapubPlugin () 
	{
		$this->Plugin ();
		$this->name = "novapub";
		$this->text = "NovaForge Publishing";
		$this->hooks [] = "groupisactivecheckbox";
		$this->hooks [] = "groupisactivecheckboxpost";
		$this->hooks [] = "project_admin_plugins";
		$this->hooks [] = "site_admin_option_hook";
		$this->hooks [] = "fill_cron_arr";
	}

	function CallHook ($hookname, $params) 
	{
		global
			$Language;

		switch ($hookname) 
		{
			case "groupisactivecheckbox" :
				$group = &group_get_object ($params ["group"]);
				echo "<tr><td><input type=\"checkbox\" name=\"use_novapub\" value=\"1\"";
				if ($group->usesPlugin ($this->name) == true)
				{
					echo " checked";
				}
				echo "></td><td><strong>". dgettext ("gforge-plugin-novapub", "use_novapub") . "</strong></td></tr>\n";
				break;
			case "groupisactivecheckboxpost" :
				$group = &group_get_object ($params ["group"]);
				if (getIntFromRequest ("use_novapub") == 1)
				{
					$group->setPluginUse ($this->name, true);
				}
				else
				{
					$group->setPluginUse ($this->name, false);
				}
				break;
			case "project_admin_plugins" :
				$group = &group_get_object ($params ["group_id"]);
				if ((isset ($group) !== false)
				&&  (is_object ($group) == true)
				&&  ($group->isError () == false)
				&&  ($group->isProject () == true)
				&&  ($group->usesPlugin ($this->name) == true))
				{
					echo "<a href=\"/plugins/" . $this->name . "/admin.php?group_id=" . $group->getID () . "\">" . dgettext ("gforge-plugin-novapub", "title_admin") . "</a><br/>";
				}
				break;
			case "site_admin_option_hook" :
				echo "<li><a href=\"/plugins/" . $this->name . "/site_admin.php\">" . dgettext ("gforge-plugin-novapub", "title_site_admin") . "</a><br/></li>";
				break;
			case "fill_cron_arr" :
				$params ["cron_arr"] [25] = "novapub_publish.php";
				break;
		}
	}

}

?>
