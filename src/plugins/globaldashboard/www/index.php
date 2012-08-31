<?php

/*
 * GlobalDashboard plugin
 *
 * Daniel Perez <danielperez.arg@gmail.com>
 *
 * This is an example to watch things in action. You can obviously modify things and logic as you see fit
 */

require_once('../../env.inc.php');
require_once $gfwww.'include/pre.php';
require_once $gfplugins.'globaldashboard/include/globalDashboard_utils.php';

$user = session_get_user(); // get the session user

if (!$user || !is_object($user) || $user->isError() || !$user->isActive()) {
	exit_error("Invalid User", "Cannot Process your requglobaldashboardest for this user.");
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
			exit_error("Error", "First activate the User's $pluginname plugin through Account Maintenance Page");
		}
		if ( (!$user) || ($user->getID() != $id)) { // if someone else tried to access the private GlobalDashboard part of this user
			exit_error("Access Denied", "You cannot access other user's personal $pluginname");
		}
		globaldashboard_header(array('title'=> _('Global Dashboard Configuration')));
		globaldashboard_toolbar();
		globaldashboard_body();
	}
}

site_project_footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
