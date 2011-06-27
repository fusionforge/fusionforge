<?php
/**
 * Admin MantisBT page
 *
 * Copyright 2010-2011, Franck Villaume - Capgemini
 * Copyright 2011, Franck Villaume - TrivialDev
 * http://fusionforge.org
 *
 * This file is part of FusionForge. FusionForge is free software;
 * you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or (at your option)
 * any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with FusionForge; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

global $mantisbt;
global $mantisbtConf;
global $username;
global $password;
global $view;

$mantisbt->getSubMenu();

switch ($view) {
	case "editVersion":
	case "stat": {
		include ("mantisbt/view/admin/$view.php");
		break;
	}
	default: {
		/* affichage principal */
		if (!isset($clientSOAP)) {
			try {
				$clientSOAP = new SoapClient($mantisbtConf['url']."/api/soap/mantisconnect.php?wsdl", array('trace'=>true, 'exceptions'=>true));
			} catch (SoapFault $soapFault) {
				echo '<div class="warning" >'. _('Technical error occurs during data retrieving:'). ' ' .$soapFault->faultstring.'</div>';
				$errorPage = true;
			}
		}
		if (!isset($errorPage)) {
			echo '<table><tr><td valign="top">';
			include ("mantisbt/view/admin/viewCategorie.php");
			echo '</td><td valign="top">';
			include ("mantisbt/view/admin/viewVersion.php");
			echo '</td></tr><tr><td valign="top">';
			include ("mantisbt/view/admin/addCategory.php");
			echo '</td><td valign="top">';
			include ("mantisbt/view/admin/addVersion.php");
			echo '</td></tr></table>';
			include ("mantisbt/view/admin/viewConfiguration.php");
		}
		break;
	}
}
?>
