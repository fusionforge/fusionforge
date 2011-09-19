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

/**
 * 
 * Show user toolbar menu which contains "My Personal Page", "Account Maintenance" 
 * and so on ... + the link to this user plugin
 *  
 * @param array $params
 */
function globaldashboard_header($params) {
	site_user_header($params);
}

/**
 * Show horizontal toolbar for Global Dashboard plugin section
 *  in "my" page menu.
 */
function globaldashboard_toolbar() {
	$user = session_get_user();
	$pluginname = getStringFromRequest('pluginname');
	echo '<p><ul class="widget_toolbar">';
	echo '<li>' . util_make_link ('/plugins/globaldashboard/admin/manage_accounts.php?type=user&id='. $user->getID().'&pluginname='.$pluginname,_('Manage Remote Accounts')).'</li>';
	echo '<li>' . util_make_link ('/plugins/globaldashboard/help.php?type=user&id='. $user->getID().'&pluginname='.$pluginname, _('Help')).'</li>';
	echo '</ul></p>';
}

/**
 * 
 * Display the welcome message on the plugin index page
 * and a few instrunctions to get started with it.
 */
function globaldashboard_body() {
	echo '<p> Put here some welcome message that invites user to go to manage accounts'.
		' section and configure some remote accounts and then activate the plugins widgets'.
		' in his /my/ page </p>';
}

?>