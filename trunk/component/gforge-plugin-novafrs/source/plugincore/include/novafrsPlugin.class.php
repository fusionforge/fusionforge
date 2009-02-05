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

/**
 * GForge Plugin NovaFrs Class
 *
 * This file is part of gforge-plugin-novafrs
 *
 */

require_once ("common/include/Error.class.php");
require_once ("common/include/Plugin.class.php");
require_once ("common/novaforge/log.php");
require_once ("plugins/novafrs/include/FileConfig.class.php");
require_once ("plugins/novafrs/include/FileGroupFrs.class.php");

class novafrsPlugin extends Plugin
{

	function novafrsPlugin ()
	{
		$this->Plugin ();
		$this->name = "novafrs";
		$this->hooks [] = "groupmenu";
		$this->hooks [] = "groupisactivecheckbox";
		$this->hooks [] = "groupisactivecheckboxpost";
	}

	function createRepository (&$Group)
	{
		$config = &FileConfig::getInstance ();
		$dir_name = $config->sys_novafrs_path . "/" . $Group->getUnixName ();
		if (is_dir ($dir_name) == false)
		{
			if (mkdir ($dir_name) == false)
			{
				log_error ("Error: Can't create repository directory '" . $dir_name . "'", __FILE__, __FUNCTION__, __CLASS__);
				exit_error ("Error: Can't create repository directory '" . $dir_name . "'");
			}
		}
		$dg = new FileGroupFrs ($Group);
		if ($dg->createDefaultArbo () == false)
		{
			log_error ("Error: " . $dg->getErrorMessage (), __FILE__, __FUNCTION__, __CLASS__);
			exit_error ("Error: " . $dg->getErrorMessage ());
		}
	}

	function CallHook ($hookname, $params)
	{
		global
			$Language;

		switch ($hookname)
		{
			case "groupmenu" :
				$group = &group_get_object ($params ["group"]);
				if ((isset ($group) == false)
				||  (is_object ($group) == false)
				||  ($group->isError () == true)
				||  ($group->isProject () == false))
				{
					return;
				}
				if ($group->usesPlugin ($this->name))
				{
					$params ["DIRS"] [] = "/plugins/novafrs/?group_id=" . $group->getID ();
					$params ["TITLES"] [] = dgettext ("gforge-plugin-novafrs", "tab_title");
				}
				if ($params ["toptab"] == $this->name)
				{
					$params ["selected"] = count ($params ["TITLES"]) - 1;
				}
				break;
			case "groupisactivecheckbox" :
				$group = &group_get_object ($params ["group"]);
				echo "<tr><td><input type=\"checkbox\" name=\"use_novafrs\" value=\"1\"";
				if ($group->usesPlugin ($this->name))
				{
					echo "checked";
				}
				echo "></td><td><strong>" . dgettext ("gforge-plugin-novafrs", "use_novafrs") . "</strong></td></tr>\n";
				break;
			case "groupisactivecheckboxpost" :
				$group = &group_get_object ($params ["group"]);
				if (getIntFromRequest ("use_novafrs") == 1)
				{
					$this->createRepository ($group);
					$group->setPluginUse ($this->name, true);
				}
				else
				{
					$group->setPluginUse ($this->name, false);
				}
				break;
		}
	}

}

?>
