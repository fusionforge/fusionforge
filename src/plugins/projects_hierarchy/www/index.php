<?php
/**
 * Copyright 2004 (c) GForge LLC
 * Copyright 2006 (c) Fabien Regnier - Sogeti
 * Copyright 2010-2011, Franck Villaume - Capgemini
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
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

require_once('../../env.inc.php');
require_once $gfcommon.'include/pre.php';
//require_once ('plugins/projects_hierarchy/config.php');

// the header that displays for the user portion of the plugin
function projects_hierarchy_Project_Header($params) {
	global $DOCUMENT_ROOT,$HTML,$id;
	$params['toptab']='projects_hierarchy';
	$params['group']=$id;
	
	/*
		Show horizontal links
	*/
	site_project_header($params);
}

// the header that displays for the project portion of the plugin
function projects_hierarchy_User_Header($params) {
	global $DOCUMENT_ROOT,$HTML,$user_id;
	$params['toptab']='projects_hierarchy';
	$params['user']=$user_id;

	/*
	 Show horizontal links
	 */
	site_user_header($params);
}


	$user = session_get_user(); // get the session user

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
		if ($type == 'group') {
			$group = group_get_object($id);
			if ( !$group) {
				exit_error("Invalid Project", "Inexistent Project");
			}
			if ( ! ($group->usesPlugin ( $pluginname )) ) {//check if the group has the projects_hierarchy plugin active
				exit_error("Error", "First activate the $pluginname plugin through the Project's Admin Interface");
			}

			session_require_perm ('project_admin', $id) ;

			projects_hierarchy_Project_Header(array('title'=>$pluginname . ' Project Plugin!','pagename'=>"$pluginname",'sectionvals'=>array(group_getname($id))));
			// DO THE STUFF FOR THE PROJECT PART HERE
			echo "We are in the Project projects_hierarchy plugin <br>";
			echo "Greetings from planet " . $world; // $world comes from the config file in /etc
		} elseif ($type == 'user') {
			$realuser = user_get_object($id);// 
			if (!($realuser) || !($realuser->usesPlugin($pluginname))) {
				exit_error("Error", "First activate the User's $pluginname plugin through Account Manteinance Page");
			}
			if ( (!$user) || ($user->getID() != $id)) { // if someone else tried to access the private projects_hierarchy part of this user
				exit_error("Access Denied", "You cannot access other user's personal $pluginname");
			}
			projects_hierarchy_User_Header(array('title'=>'My '.$pluginname,'pagename'=>"$pluginname",'sectionvals'=>array($realuser->getUnixName())));
			// DO THE STUFF FOR THE USER PART HERE
			echo "We are in the User projects_hierarchy plugin <br>";
			echo "Greetings from planet " . $world; // $world comes from the config file in /etc
		} elseif ($type == 'admin') {
			$group = group_get_object($id);
			if ( !$group) {
				exit_error("Invalid Project", "Inexistent Project");
			}
			if ( ! ($group->usesPlugin ( $pluginname )) ) {//check if the group has the projects_hierarchy plugin active
				exit_error("Error", "First activate the $pluginname plugin through the Project's Admin Interface");
			}

			session_require_perm ('project_admin', $id) ;

			projects_hierarchy_Project_Header(array('title'=>$pluginname . ' Project Plugin!','pagename'=>"$pluginname",'sectionvals'=>array(group_getname($id))));
			// DO THE STUFF FOR THE PROJECT ADMINISTRATION PART HERE
			echo "We are in the Project projects_hierarchy plugin <font color=\"#ff0000\">ADMINISTRATION</font> <br>";
			echo "Greetings from planet " . $world; // $world comes from the config file in /etc
		}
	}	 
	
	site_project_footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
