<?php
/*
 * MantisBT plugin
 *
 * Copyright 2009-2010, Franck Villaume - Capgemini
 * Copyright 2009, Fabien Dubois - Capgemini
 * Copyright 2010, Antoine Mercadal - Capgemini
 * http://fusionforge.org
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
require_once $gfconfig.'plugins/mantisbt/config.php';

// the header that displays for the user portion of the plugin
function mantisbt_Project_Header($params) {
	global $DOCUMENT_ROOT,$HTML,$id;
	$params['toptab']='mantisbt';
	$params['group']=$id;
	/*
	 * Show horizontal links
	*/
	site_project_header($params);
}

// the header that displays for the project portion of the plugin
function mantisbt_User_Header($params) {
	global $DOCUMENT_ROOT,$HTML,$user_id;
	$params['toptab']='mantisbt';
	$params['user']=$user_id;
	/*
	 * Show horizontal links
	 */
	site_user_header($params);
}

if (!session_loggedin()) {
	exit_not_logged_in();
}

$user = session_get_user(); // get the session user

if (!$user || !is_object($user)) {
	exit_error(_('Invalid User'),'home');
} else if ( $user->isError()) {
	exit_error($user->isError(),'home');
} else if ( !$user->isActive()) {
	exit_error(_('User not active'),'home');
}

$type = getStringFromRequest('type');
$id = getStringFromRequest('id');
$idProjetMantis = getIdProjetMantis($id);
$pluginname = getStringFromRequest('pluginname');
$feedback = htmlspecialchars(getStringFromRequest('feedback'));
$error_msg = htmlspecialchars(getStringFromRequest('error_msg'));
$warning_msg = htmlspecialchars(getStringFromRequest('warning_msg'));

$password = '';
$username = $user->getUnixName();

if (!$type) {
	exit_missing_params($_SERVER['HTTP_REFERER'], array('No TYPE specified'),'mantisbt');
} elseif (!$id) {
	exit_missing_params($_SERVER['HTTP_REFERER'], array('No ID specified'),'mantisbt');
} else {
	switch ($type) {
		case 'group': {
			$group = group_get_object($id);
			if ( !$group) {
				exit_no_group();
			}
			if ( ! ($group->usesPlugin ( $pluginname )) ) {//check if the group has the MantisBT plugin active
				exit_error(sprintf(_('First activate the %s plugin through the Project\'s Admin Interface'),$pluginname),'home');
			}
			$userperm = $group->getPermission($user);//we'll check if the user belongs to the group (optional)
			if ( !$userperm->IsMember()) {
				exit_permission_denied(_('You are not a member of this project'),'home');
			}

			// URL analysis
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
				exit_error(_('Uninitialized Project. Force his activation by desactivate/activate mantisbt for this project'),'home');
			} else if (is_int($password)){
				exit_error(_('Impossible de récupérer les identifiants de connexions depuis le LDAP'),'home');
			} else {
				// do the job
				mantisbt_Project_Header(array('title'=>$pluginname . ' Project Plugin!','pagename'=>"$pluginname",'sectionvals'=>array(group_getname($id))));
				include ('mantisbt/www/group/index.php');
			}
			break;
		}
		case 'user': {
			$realuser = user_get_object($id);//
			if (!($realuser) || !($realuser->usesPlugin($pluginname))) {
				exit_error(sprintf(_('First activate the User\'s %s plugin through Account Maintenance Page'),$pluginname),'my');
			}
			if ( (!$user) || ($user->getID() != $id)) { // if someone else tried to access the private MantisBT part of this user
				exit_permission_denied(sprintf(_('You cannot access other user\'s personal %s'),$pluginname),'my');
			}

			$password ='';
			$username = $realuser->getUnixName();

			// URL analysis
			$sort = getStringFromRequest('sort');
			$dir = getStringFromRequest('dir');
			$action = getStringFromRequest('action');
			$idBug = getStringFromRequest('idBug');

			$idNote = getStringFromRequest('idNote');
			$page = getStringFromRequest('page');
			// Si la variable $_GET['page'] existe...
			if($page != null && $page != ''){
				$pageActuelle=intval($page);
			} else {
				$pageActuelle=1; // La page actuelle est la n°1 
			}

			$format = "%07d";

			if (!is_int($password)){
				// do the job
				mantisbt_User_Header(array('title'=>'My '.$pluginname,'pagename'=>"$pluginname",'sectionvals'=>array($realuser->getUnixName())));
				include ('mantisbt/www/user/index.php');
			} else {
				exit_error(_('Impossible de récupérer les identifiants de connexions depuis le LDAP'),'home');
			}
			break;
		}
		case 'admin': {
			$group = group_get_object($id);
			if ( !$group) {
				exit_no_group();
			}
			if ( ! ($group->usesPlugin ( $pluginname )) ) {//check if the group has the MantisBT plugin active
				exit_error(sprintf(_('First activate the %s plugin through the Project\'s Admin Interface'),$pluginname),'home');
			}
			$userperm = $group->getPermission($user);//we'll check if the user belongs to the group
			if ( !$userperm->IsMember()) {
				exit_permission_denied(_('You are not a member of this project'));
			}
			//only project admin can access here
			if ( $userperm->isAdmin() ) {
				// DO THE STUFF FOR THE PROJECT ADMINISTRATION PART HERE
				mantisbt_Project_Header(array('title'=>$pluginname . ' Project Plugin!','pagename'=>"$pluginname",'sectionvals'=>array(group_getname($id))));	
				include ('mantisbt/www/admin/index.php');
			} else {
				exit_permission_denied(_('You are not Admin of this project'),'home');
			}
			break;
		}
	}
}

site_project_footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
