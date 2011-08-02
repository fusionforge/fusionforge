<?php

/*
 * SoapAdmin plugin
 *
 * Daniel Perez <danielperez.arg@gmail.com>
 *
 * This is an example to watch things in action. You can obviously modify things and logic as you see fit
 */

require_once('../../../env.inc.php');
require_once $gfwww.'include/pre.php';
require_once $gfconfig.'plugins/soapadmin/config.php';

// the header that displays for the user portion of the plugin
function soapadmin_Project_Header($params) {
	global $DOCUMENT_ROOT,$HTML,$id;
	$params['toptab']='soapadmin';
	$params['group']=$id;
	/*
		Show horizontal links
	*/
	site_project_header($params);
}

// the header that displays for the project portion of the plugin
function soapadmin_User_Header($params) {
	global $DOCUMENT_ROOT,$HTML,$user_id;
	$params['toptab']='soapadmin';
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
			if ( ! ($group->usesPlugin ( $pluginname )) ) {//check if the group has the SoapAdmin plugin active
				exit_error("Error", "First activate the $pluginname plugin through the Project's Admin Interface");
			}
			$userperm = $group->getPermission($user);//we'll check if the user belongs to the group (optional)
			if ( !$userperm->IsMember()) {
				exit_error("Access Denied", "You are not a member of this project");
			}
			// other perms checks here...
			soapadmin_Project_Header(array('title'=>$pluginname . ' Project Plugin!','pagename'=>"$pluginname",'sectionvals'=>array(group_getname($id))));
			// DO THE STUFF FOR THE PROJECT PART HERE
			echo "We are in the Project SoapAdmin plugin <br>";
			echo "Greetings from planet " . $world; // $world comes from the config file in /etc
		} elseif ($type == 'user') {
			$realuser = user_get_object($id);//
			if (!($realuser) || !($realuser->usesPlugin($pluginname))) {
				exit_error("Error", "First activate the User's $pluginname plugin through Account Manteinance Page");
			}
			if ( (!$user) || ($user->getID() != $id)) { // if someone else tried to access the private SoapAdmin part of this user
				exit_error("Access Denied", "You cannot access other user's personal $pluginname");
			}
			soapadmin_User_Header(array('title'=>'My '.$pluginname,'pagename'=>"$pluginname",'sectionvals'=>array($realuser->getUnixName())));
			// DO THE STUFF FOR THE USER PART HERE
			echo "We are in the User SoapAdmin plugin <br>";
			echo "Greetings from planet " . $world; // $world comes from the config file in /etc
		} elseif ($type == 'admin') {
			$group = group_get_object($id);
			if ( !$group) {
				exit_error("Invalid Project", "Inexistent Project");
			}
			if ( ! ($group->usesPlugin ( $pluginname )) ) {//check if the group has the SoapAdmin plugin active
				exit_error("Error", "First activate the $pluginname plugin through the Project's Admin Interface");
			}
			$userperm = $group->getPermission($user);//we'll check if the user belongs to the group
			if ( !$userperm->IsMember()) {
				exit_error("Access Denied", "You are not a member of this project");
			}
			//only project admin can access here
			if ( $userperm->isAdmin() ) {
				soapadmin_Project_Header(array('title'=>$pluginname . ' Project Plugin!','pagename'=>"$pluginname",'sectionvals'=>array(group_getname($id))));
				// DO THE STUFF FOR THE PROJECT ADMINISTRATION PART HERE
				echo "We are in the Project SoapAdmin plugin <font color=\"#ff0000\">ADMINISTRATION</font> <br>";
				echo "Greetings from planet " . $world; // $world comes from the config file in /etc
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
