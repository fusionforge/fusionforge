<?php

/*
 * This file contains the functionality of the different checks 
 * needed to be done before displaying any page of the
 * oauthconsumer plugin
 */ 

require_once $gfwww.'include/pre.php';

$pluginname = 'oauthconsumer';
// the header that displays for the user portion of the plugin
function oauthconsumer_User_Header($params) {
	global $DOCUMENT_ROOT,$HTML, $user_id, $pluginname;
	$params['toptab']=$pluginname; 
	$params['user']=$user_id;
	site_user_header($params);    
}

/*
 * checks whether the user is logged in and has activated the plugin
 */
function oauthconsumer_CheckUser() {
	
	if (!session_loggedin()) { //check if user logged in
		exit_not_logged_in();
	}	
	
	global $pluginname;
	
	$user = session_get_user(); // get the session user

	if (!$user || !is_object($user) || $user->isError() || !$user->isActive()) {
		exit_error("Invalid User, Cannot Process your request for this user.", $pluginname);
	}

	$id = $user->getID();
	
	if (!$id) {
		exit_error("Cannot Process your request: Invalid User", $pluginname);
	}
	
	$realuser = user_get_object($id);
	if (!($realuser) || !($realuser->usesPlugin($pluginname))) { //check if user has activated the plugin
		exit_error("First activate the User's $pluginname plugin through Account Maintenance Page", $pluginname);
	}

	//displays the page header
	oauthconsumer_User_Header(array('title'=>'Personal page for OAuth','pagename'=>"$pluginname",'sectionvals'=>array($realuser->getUnixName())));    
	
}

/*
 * checks whether the user is a forge admin
 */
function oauthconsumer_CheckForgeAdmin() {

	if(! forge_check_global_perm ('forge_admin')) {
		return false;
	}
		
	oauthconsumer_User_Header(array('title'=>'Admin page for OAuthConsumer','pagename'=>"$pluginname"));
	return true;
}

/*
 * checks whether the user is a forge admin and exits
 */
function oauthconsumer_CheckForgeAdminExit() {

	if(! forge_check_global_perm ('forge_admin')) {
		exit_error("Access Denied, You are not a forge Admin", 'oauthconsumer');
	}
		
	oauthconsumer_User_Header(array('title'=>'Admin page for OAuthConsumer','pagename'=>"$pluginname"));
	
}

?>