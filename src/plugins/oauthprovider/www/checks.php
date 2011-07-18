<?php

/*
 * oauthprovider plugin
 *
 * Daniel Perez <danielperez.arg@gmail.com>
 * 
 * FIXME : FIX copyright
 *
 * This is an example to watch things in action. You can obviously modify things and logic as you see fit
 */

require_once $gfwww.'include/pre.php';
require $gfconfig.'/plugins/oauthprovider/config.php';
require_once $gfwww.'admin/admin_utils.php';

$pluginname = 'oauthprovider';

$type = getStringFromRequest('type');
$name = getStringFromRequest('name');
$id = getStringFromRequest('id');
if ($name) $type_param = array('name', $name);
elseif ($id) $type_param = array('id', $id);

// the header that displays for the project portion of the plugin
function oauthprovider_Project_Header($params) {                                                                                                                                         
	global $DOCUMENT_ROOT,$HTML,$id, $group_id;
	$params['toptab']='oauthprovider'; 
	                                                                                                                                                              
	site_project_header($params);														
}

// the header that displays for the user portion of the plugin
function oauthprovider_User_Header($params) {
	global $DOCUMENT_ROOT,$HTML,$user_id;
	$params['toptab']='oauthprovider'; 
	$params['user']=$user_id;
	/*                                                                                                                                                              
	 Show horizontal links                                                                                                                                   
	 */                                                                                                                                                              
	site_user_header($params);    
}

function oauthprovider_Admin_Header() {
	site_admin_header(array('title'=>_('OAuth')));
}

			
function oauthprovider_CheckGroup() {
	if (!session_loggedin()) {
		exit_not_logged_in();
	}	

	$user = session_get_user(); // get the session user
	global $pluginname, $name, $id;
	
	if (!$user || !is_object($user) || $user->isError() || !$user->isActive()) {
		exit_error("Invalid User, Cannot Process your request for this user.", 'oauthprovider');
	}
	
	if ((!$name)&&(!$id)) {
		exit_error("Cannot Process your request: No NAME or ID specified",'oauthprovider');
	}
	
	if($name)	{
		$group = group_get_object_by_name($name);
		$id = $group->getID();
	}
	else if($id) $group = group_get_object($id);
	//print_r($group);
	
	if ( !$group) {
		exit_error("Invalid Project", 'oauthprovider');
	}
	if ( ! ($group->usesPlugin ( $pluginname )) ) {//check if the group has the oauthprovider plugin active
		exit_error("Error, First activate the $pluginname plugin through the Project's Admin Interface", 'oauthprovider');			
	}
	$userperm = $group->getPermission($user);//we'll check if the user belongs to the group (optional)
	if ( !$userperm->IsMember()) {
		exit_error("Access Denied, You are not a member of this project", 'oauthprovider');
	}
	
	oauthprovider_Project_Header(array('group'=>$group->getID(),'title'=>_('OAuth Provider'),'pagename'=>$pluginname,'sectionvals'=>array($group->getPublicName())));    
	return $group;
	//echo "We are in the Project oauthprovider plugin page for group (project) $id <br><br>";
}

function oauthprovider_CheckUser() {
	if (!session_loggedin()) {
		exit_not_logged_in();
	}	
	
	global $pluginname;
	
	$user = session_get_user(); // get the session user

	if (!$user || !is_object($user) || $user->isError() || !$user->isActive()) {
		exit_error("Invalid User, Cannot Process your request for trequire_once $gfwww.'admin/admin_utils.php';
		his user.", $pluginname);
	}

	$id = $user->getID();
	
	if (!$id) {
		exit_error("Cannot Process your request: Invalid User", $pluginname);
	}
	
	$realuser = user_get_object($id);
	if (!($realuser) || !($realuser->usesPlugin($pluginname))) {
		exit_error("First activate the User's $pluginname plugin through Account Maintenance Page", 'oauthprovider');
	}
	
	oauthprovider_User_Header(array('title'=>'Personal page for OAuth','pagename'=>"$pluginname",'sectionvals'=>array($realuser->getUnixName())));    
	// DO THE STUFF FOR THE USER PART HERE
	//echo "We are in the User oauthprovider plugin page for user <br><br>";
}

/*
 * checks whether the user is a forge admin or an admin of the corresponding project
 */
function oauthprovider_CheckAdmin() {

	if (!session_loggedin()) {
		exit_not_logged_in();
	}	

	$user = session_get_user(); // get the session user
	global $pluginname, $name, $id;

	if (!$user || !is_object($user) || $user->isError() || !$user->isActive()) {
		exit_error("Invalid User, Cannot Process your request for this user.", 'oauthprovider');
	}

	if($name)	{
		$group = group_get_object_by_name($name);
		$id = $group->getID();
	}
	else if($id) $group = group_get_object($id);
	
	if ( !$group) {
		exit_error("Invalid Project", $pluginname);
	}
	if ( ! ($group->usesPlugin ( $pluginname )) ) {//check if the group has the oauthprovider plugin active
		exit_error("Error, First activate the $pluginname plugin through the Project's Admin Interface", $pluginname);			
	}
	
	$userperm = $group->getPermission($user);//we'll check if the user belongs to the group
	if ( !$userperm->IsMember()) {
		exit_error("Access Denied, You are not a member of this project", $pluginname);
	}
	
	//only project admin can access here
	if ($userperm->isAdmin() || forge_check_global_perm ('forge_admin')) {
		if($userperm->isAdmin()) {
			oauthprovider_Project_Header(array('group'=>$id, 'title'=>_('OAuth Provider'), 'pagename'=>"$pluginname",'sectionvals'=>array(group_getname($id))));
		}else {
			oauthprovider_Admin_Header();
		}    
		return 0;
	} 
	else if(! forge_check_global_perm ('forge_admin')) {
		//exit_error("Access Denied, You are not a forge Admin", 'oauthprovider');
		return 1;
	}
	else {
		//exit_error("Access Denied, You are not a project Admin", 'oauthprovider');
		return 2;
	}
}

/*
 * exits with error if user is ot a forge or project admin
 */
function oauthprovider_CheckAdminExit() {
	switch(oauthprovider_CheckAdmin())	{
		case 1: exit_error("Access Denied, You are not a forge Admin", 'oauthprovider');
			break;
		case 2: exit_error("Access Denied, You are not a project Admin", 'oauthprovider');
			break;
	};
}

/*
 * checks whether the user is a forge admin
 */
function oauthprovider_CheckForgeAdmin() {

	if(! forge_check_global_perm ('forge_admin')) {
		return false;
	}
		
	oauthprovider_Admin_Header();
	return true;
}

/*
 * checks whether the user is a forge admin and exits
 */
function oauthprovider_CheckForgeAdminExit() {

	if(! forge_check_global_perm ('forge_admin')) {
		exit_error("Access Denied, You are not a forge Admin", 'oauthprovider');
	}
		
	oauthprovider_Admin_Header();
	
}

?>