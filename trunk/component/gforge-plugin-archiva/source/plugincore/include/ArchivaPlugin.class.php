<?php
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

class ArchivaPlugin extends Plugin
{

	function ArchivaPlugin ()
	{
		$this->Plugin () ;
		$this->name = "archiva";
		$this->text = "Archiva";
		$this->hooks [] = "site_admin_option_hook";
		$this->hooks [] = "session_before_login";
		$this->hooks [] = "before_logout_redirect";
	}

	function CallHook ($hookname, $params)
	{
		global
			$Language;

		switch ($hookname)
		{
			case "site_admin_option_hook" :
				echo "<li><a href=\"/plugins/archiva/\">" . dgettext ("gforge-plugin-archiva", "title_site_admin") . "</a><br/></li>";
				break;
			case "session_before_login" :
			case "before_logout_redirect" :
/*
				setcookie ("MANTIS_PROJECT_COOKIE", "", time () - 3600, "/");
				setcookie ("MANTIS_STRING_COOKIE", "", time () - 3600, "/");
				setcookie ("MANTIS_VIEW_ALL_COOKIEE", "", time () - 3600, "/");
*/
				break;
		}
	}
}

?>
