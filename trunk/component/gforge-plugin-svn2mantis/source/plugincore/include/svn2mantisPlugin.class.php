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

require_once ("common/include/Error.class");
require_once ("common/include/Plugin.class");

class svn2mantisPlugin extends Plugin 
{

	function svn2mantisPlugin () 
	{
		$this->Plugin ();
		$this->name = "svn2mantis";
		$this->text = "Subversion to Mantis";
		$this->hooks [] = "groupisactivecheckbox";
		$this->hooks [] = "groupisactivecheckboxpost";
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
				echo "<tr><td><input type=\"checkbox\" name=\"use_svn2mantis\" value=\"1\"";
				if ($group->usesPlugin ($this->name) == true)
				{
					echo " checked";
				}
				echo "></td><td><strong>". dgettext ("gforge-plugin-svn2mantis", "use_svn2mantis") . "</strong></td></tr>\n";
				break;
			case "groupisactivecheckboxpost" :
				$group = &group_get_object ($params ["group"]);
				$use_svn2mantis = getIntFromRequest ("use_svn2mantis");
				if ($use_svn2mantis == 1)
				{
					$group->setPluginUse ($this->name, true);
				}
				else
				{
					$group->setPluginUse ($this->name, false);
				}
				break;
			case "fill_cron_arr" :
				$params ["cron_arr"] [26] = "svn2mantis.php";
				break;
		}
	}

}

?>
