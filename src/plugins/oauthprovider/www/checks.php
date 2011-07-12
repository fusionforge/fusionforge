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


// the header that displays for the project portion of the plugin
function oauthprovider_Project_Header($params) {
	global $DOCUMENT_ROOT,$HTML,$id, $group_id;
	$group_id = $id;
	$params['toptab']='oauthprovider';
	$params['group']=$id;
	/*
		Show horizontal links
	*/
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

	if (!$user || !is_object($user) || $user->isError() || !$user->isActive()) {
		exit_error("Invalid User, Cannot Process your request for this user.", 'oauthprovider');
	}

	$name = getStringFromRequest('name');
	if ((!$name)&&(!$id)) {
		exit_error("Cannot Process your request: No NAME or ID specified",'oauthprovider');
	}
	$pluginname = 'oauthprovider';

			if($name)	{
				$group = group_get_object_by_name($name);
				$id = $group->getID();
			}
			else $group = group_get_object($id);
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
			// other perms checks here...
			oauthprovider_Project_Header(array('group'=>$group->getID(),'title'=>$pluginname . ' Project Plugin!','pagename'=>$pluginname,'sectionvals'=>array($group->getPublicName())));
			// DO THE STUFF FOR THE PROJECT PART HERE

			echo "We are in the Project oauthprovider plugin page for group (project) $id <br><br>";
}

function oauthprovider_CheckUser() {
	if (!session_loggedin()) {
		exit_not_logged_in();
	}

	$user = session_get_user(); // get the session user

	if (!$user || !is_object($user) || $user->isError() || !$user->isActive()) {
		exit_error("Invalid User, Cannot Process your request for trequire_once $gfwww.'admin/admin_utils.php';
		his user.", 'oauthprovider');
	}

	$name = getStringFromRequest('name');
	//$id = getStringFromRequest('id');
	$id = $user->getID();

	if ((!$name)&&(!$id)) {
		exit_error("Cannot Process your request: No NAME or ID specified",'oauthprovider');
	}
	$pluginname = 'oauthprovider';

			if($name) $realuser = user_get_object_by_name($name);
			else  $realuser = user_get_object($id);
			if (!($realuser) || !($realuser->usesPlugin($pluginname))) {
				exit_error("First activate the User's $pluginname plugin through Account Maintenance Page", 'oauthprovider');
			}
			if ( (!$user) || ($user->getID() != $id)) { // if someone else tried to access the private oauthprovider part of this user
				exit_error("Access Denied, You cannot access other user's personal $pluginname", 'oauthprovider');
			}
			oauthprovider_User_Header(array('title'=>'Personal page for OAuth','pagename'=>"$pluginname",'sectionvals'=>array($realuser->getUnixName())));
			// DO THE STUFF FOR THE USER PART HERE
			echo "We are in the User oauthprovider plugin page for user <br><br>";
}

function oauthprovider_CheckProjectAdmin() {

	if (!session_loggedin()) {
		exit_not_logged_in();
	}

	$user = session_get_user(); // get the session user

	if (!$user || !is_object($user) || $user->isError() || !$user->isActive()) {
		exit_error("Invalid User, Cannot Process your request for this user.", 'oauthprovider');
	}

	$name = getStringFromRequest('name');
	$pluginname = 'oauthprovider';

			if($name)	{
				$group = group_get_object_by_name($name);
				$id = $group->getID();
			}
			else $group = group_get_object($id);

			if ( !$group) {
				exit_error("Invalid Project", 'oauthprovider');
			}
			if ( ! ($group->usesPlugin ( $pluginname )) ) {//check if the group has the oauthprovider plugin active
				exit_error("Error, First activate the $pluginname plugin through the Project's Admin Interface", 'oauthprovider');
			}

			$userperm = $group->getPermission($user);//we'll check if the user belongs to the group
			if ( !$userperm->IsMember()) {
				exit_error("Access Denied, You are not a member of this project", 'oauthprovider');
			}

			//only project admin can access here
			if ( $userperm->isAdmin() ) {
				oauthprovider_Project_Header(array('group'=>$id, 'title'=>$pluginname . ' Project Plugin!','pagename'=>"$pluginname",'sectionvals'=>array(group_getname($id))));
				// DO THE STUFF FOR THE PROJECT ADMINISTRATION PART HERE
				//echo "We are in the Project oauthprovider plugin page for <font color=\"#ff0000\">ADMINISTRATION</font> <br><br>";

			} else {
				exit_error("Access Denied, You are not a project Admin", 'oauthprovider');
			}
}

function oauthprovider_CheckSiteAdmin() {

	if (!session_loggedin()) {
		exit_not_logged_in();
	}

	$user = session_get_user(); // get the session user

	if (!$user || !is_object($user) || $user->isError() || !$user->isActive()) {
		exit_error("Invalid User, Cannot Process your request for this user.", 'oauthprovider');
	}

	$name = getStringFromRequest('name');
	$pluginname = 'oauthprovider';
	/*
			if($name)	{
				$group = group_get_object_by_name($name);
				$id = $group->getID();
			}
			else $group = group_get_object($id);

			if ( !$group) {
				exit_error("Invalid Project", 'oauthprovider');
			}
			if ( ! ($group->usesPlugin ( $pluginname )) ) {//check if the group has the oauthprovider plugin active
				exit_error("Error, First activate the $pluginname plugin through the Project's Admin Interface", 'oauthprovider');
			}

			$userperm = $group->getPermission($user);//we'll check if the user belongs to the group
			if ( !$userperm->IsMember()) {
				exit_error("Access Denied, You are not a member of this project", 'oauthprovider');
			}

			//only project admin can access here
			if ( $userperm->isAdmin() ) {
				oauthprovider_Project_Header(array('group'=>$id, 'title'=>$pluginname . ' Project Plugin!','pagename'=>"$pluginname",'sectionvals'=>array(group_getname($id))));
				// DO THE STUFF FOR THE PROJECT ADMINISTRATION PART HERE
				//echo "We are in the Project oauthprovider plugin page for <font color=\"#ff0000\">ADMINISTRATION</font> <br><br>";

			} else {
				exit_error("Access Denied, You are not a project Admin", 'oauthprovider');
			}
			*/
	if(! forge_check_global_perm ('forge_admin')) {
		exit_error("Access Denied, You are not a site Admin", 'oauthprovider');
	}

	oauthprovider_Admin_Header();


}

?>
