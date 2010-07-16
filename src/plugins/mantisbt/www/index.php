<?php

/*
 * MantisBT plugin
 * Copyright 2010, Capgemini
 * Author: Franck Villaume - Capgemini
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
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA
 */

require_once('../../env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once $gfcommon.'ldap/ldapUtils.php';
require_once $gfconfig.'plugins/mantisbt/config.php';

// the header that displays for the user portion of the plugin
function mantisbt_Project_Header($params) {
	global $DOCUMENT_ROOT,$HTML,$id;
	$params['toptab']='mantisbt';
	$params['group']=$id;
	/*
		Show horizontal links
		*/
	site_project_header($params);
}

// the header that displays for the project portion of the plugin
function mantisbt_User_Header($params) {
	global $DOCUMENT_ROOT,$HTML,$user_id;
	$params['toptab']='mantisbt';
	$params['user']=$user_id;
	/*
	 Show horizontal links
	 */
	site_user_header($params);
}

/*
 * Ce page est proteger par SSO lemonLDAP
 */

if (!session_loggedin()) {
	if(isset($_SERVER['HTTP_AUTH_USER']) && $_SERVER['HTTP_AUTH_USER'] != ''){
		$userId = $_SERVER['HTTP_AUTH_USER'];
		$authorized = verifyEtConstructCookie($userId);
	}else{
		exit_permission_denied();	
	}	
}

$user = session_get_user(); // get the session user

if (!$user || !is_object($user) || $user->isError() || !$user->isActive()) {
	exit_error("Invalid User", "Cannot Process your request for this user.");
}

$type = getStringFromRequest('type');
$id = getStringFromRequest('id');
$idProjetMantis = getIdProjetMantis($id);
$pluginname = getStringFromRequest('pluginname');

$password = getPasswordFromLDAP($user);
$username = $user->getUnixName();

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
		if ( ! ($group->usesPlugin ( $pluginname )) ) {//check if the group has the MantisBT plugin active
			exit_error("Error", "First activate the $pluginname plugin through the Project's Admin Interface");
		}
		$userperm = $group->getPermission($user);//we'll check if the user belongs to the group (optional)
		if ( !$userperm->IsMember()) {
			exit_error("Access Denied", "You are not a member of this project");
		}
		// other perms checks here...
		mantisbt_Project_Header(array('title'=>$pluginname . ' Project Plugin!','pagename'=>"$pluginname",'sectionvals'=>array(group_getname($id))));
			
		// recuperer les info de URL
		$sort = getStringFromRequest('sort');
		$dir = getStringFromRequest('dir');
		$action = getStringFromRequest('action');
		$idBug = getStringFromRequest('idBug');
			
		$idNote = getStringFromRequest('idNote');
		$idAttachment = getStringFromRequest('idAttachment');
		$actionAttachment = getStringFromRequest('actionAttachment');
		$page = getStringFromRequest('page');
		// Si la variable $_GET['page'] existe...
		if($page != null && $page != ''){
			$pageActuelle=intval($page);
		}
		else {
			$pageActuelle=1; // La page actuelle est la n°1 
		}
			
		$format = "%07d";


		if($idProjetMantis == 0){
		 	echo "Projet non initialisé. Pour forcer son activation, il faut désactiver/activer mantis pour ce projet";
		} else if (is_int($password)){
			echo "Impossible de récupérer les identifiants de connexions depuis le LDAP";
		} else {
			// do the job
			include ('mantisbt/www/group/index.php');
		}
	} elseif ($type == 'user') {
		$realuser = user_get_object($id);//
		if (!($realuser) || !($realuser->usesPlugin($pluginname))) {
			exit_error("Error", "First activate the User's $pluginname plugin through Account Manteinance Page");
		}
		if ( (!$user) || ($user->getID() != $id)) { // if someone else tried to access the private MantisBT part of this user
			exit_error("Access Denied", "You cannot access other user's personal $pluginname");
		}
		mantisbt_User_Header(array('title'=>'My '.$pluginname,'pagename'=>"$pluginname",'sectionvals'=>array($realuser->getUnixName())));
			
		$password = getPasswordFromLDAP($realuser);
		$username = $realuser->getUnixName();
			
		// recuperer les info de URL
		$sort = getStringFromRequest('sort');
		$dir = getStringFromRequest('dir');
		$action = getStringFromRequest('action');
		$idBug = getStringFromRequest('idBug');
			
		$idNote = getStringFromRequest('idNote');
		$page = getStringFromRequest('page');
		// Si la variable $_GET['page'] existe...
		if($page != null && $page != ''){
			$pageActuelle=intval($page);
		}
		else {
			$pageActuelle=1; // La page actuelle est la n°1 
		}
		
		$format = "%07d";
			
		if (!is_int($password)){
			// do the job
			include ('mantisbt/www/user/index.php');
		} else {
			echo "Un problème est survenu lors de la récupération des tickets";
		}
	} elseif ($type == 'admin') {
		$group = group_get_object($id);
		if ( !$group) {
			exit_error("Invalid Project", "Inexistent Project");
		}
		if ( ! ($group->usesPlugin ( $pluginname )) ) {//check if the group has the MantisBT plugin active
			exit_error("Error", "First activate the $pluginname plugin through the Project's Admin Interface");
		}
		$userperm = $group->getPermission($user);//we'll check if the user belongs to the group
		if ( !$userperm->IsMember()) {
			exit_error("Access Denied", "You are not a member of this project");
		}
		//only project admin can access here
		if ( $userperm->isAdmin() ) {
			// DO THE STUFF FOR THE PROJECT ADMINISTRATION PART HERE
			mantisbt_Project_Header(array('title'=>$pluginname . ' Project Plugin!','pagename'=>"$pluginname",'sectionvals'=>array(group_getname($id))));	
			include ('mantisbt/www/admin/index.php');
		} else {
			exit_error("Access Denied", "You are not a project Admin");
		}
	}
}

site_project_footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:


?>
