<?php

/*
 *
 * Novaforge is a registered trade mark from Bull S.A.S
 * Copyright (C) 2007 Bull S.A.S.
 * 
 * http://novaforge.org/
 *
 */

require_once ("common/include/Error.class.php");
require_once ("common/include/Plugin.class.php");

class NovaMonitorPlugin extends Plugin {

	function NovaMonitorPlugin () {
		$this->Plugin() ;
		$this->name = "novamonitor" ;
		$this->text = "Nova Monitor" ;
		$this->hooks [] = "groupisactivecheckbox" ; // The "use ..." checkbox in editgroupinfo
		$this->hooks [] = "groupisactivecheckboxpost" ; //
		$this->hooks [] = "groupmenu";
	}

	function CallHook ($hookname, $params) {
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
					$params ["DIRS"] [] = "/plugins/novamonitor/?group_id=" . $group->getID ();
					$params ["TITLES"] [] = dgettext ("gforge-plugin-novamonitor", "Monitor");
				}
				if ($params ["toptab"] == $this->name)
				{
					$params ["selected"] = count ($params ["TITLES"]) - 1;
				}
				break;
			case "groupisactivecheckbox" :
				$group = &group_get_object ($params ["group"]);
				echo "<tr><td><input type=\"checkbox\" name=\"use_novamonitor\" value=\"1\"";
				if ($group->usesPlugin ($this->name) == true)
				{
					echo " checked";
				}
				echo "></td><td><strong>". dgettext ("gforge-plugin-novamonitor", "Monitoring d'applications Novaforge") ."</strong></td></tr>\n";
				break;
			case "groupisactivecheckboxpost" :
				$group = &group_get_object ($params ["group"]);
				if (getIntFromRequest ("use_novamonitor") == 1)
				{
					$group->setPluginUse ($this->name, true);
				}
				else
				{
					$group->setPluginUse ($this->name, false);
				}
				break;
			case "session_before_login" :
			case "before_logout_redirect" :
				// setcookie ("NOVAMONITOR_PROJECT_COOKIE", "", time () - 3600, "/");
				// setcookie ("NOVAMONITOR_STRING_COOKIE", "", time () - 3600, "/");
				// setcookie ("NOVAMONITOR_VIEW_ALL_COOKIEE", "", time () - 3600, "/");
				
				break;

		}
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
