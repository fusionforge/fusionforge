<?php
/**
* Copyright 2011, Sabri LABBENE - Institut Télécom
*
*
* This file is part of FusionForge.
*
* FusionForge is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
*
* FusionForge is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License along
* with this program; if not, write to the Free Software Foundation, Inc.,
* 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
*/

require_once '../../env.inc.php';
require_once $gfwww.'include/pre.php';
require_once $gfplugins.'globaldashboard/include/globalDashboard_utils.php';

$user = session_get_user(); // get the user session 

if (!$user || !is_object($user) || $user->isError() || !$user->isActive()) {
	exit_error("Invalid User", "Cannot Process your request for this user.");
}

$type = getStringFromRequest('type');
$id = getStringFromRequest('id');
$pluginname = getStringFromRequest('pluginname');

if (!$type) {
	exit_error("Cannot Process your request","No TYPE specified"); // you can create items in Base.tab and customize this messages
} elseif (!$id) {
	exit_error("Cannot Process your request","No ID specified");
} else {
	if ($type == 'user') {
		$realuser = user_get_object($id);//
		if (!($realuser) || !($realuser->usesPlugin($pluginname))) {
			exit_error("Error", "First activate the User's $pluginname plugin through Account Manteinance Page");
		}
		if ( (!$user) || ($user->getID() != $id)) {
			// if someone else tried to access the private GlobalDashboard part of this user
			exit_error("Access Denied", "You cannot access other user's personal $pluginname");
		}
		// show the header 
		globaldashboard_header(array('title'=> _('Global Dashboard Help')));
		globaldashboard_toolbar();
		
		echo '<p> This is Help section. @TODO: include few tips on how to use and config the plugin. </p>';
	}
}
site_project_footer(array());
