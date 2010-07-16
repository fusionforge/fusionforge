<?php

/*
 * Mantis plugin 2
 *
 * Christopher Mann <chris@mann.fr>
 *
 */

require_once('../../env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once $gfconfig.'plugins/mantis/config.php';

// the header that displays for the user portion of the plugin
function helloworld_Project_Header($params) {
	global $DOCUMENT_ROOT,$HTML,$id;
	$params['toptab']='mantis';
	$params['group']=$id;
	/*
		Show horizontal links
	*/
	site_project_header($params);
}

// the header that displays for the project portion of the plugin
function helloworld_User_Header($params) {
	global $DOCUMENT_ROOT,$HTML,$user_id;
	$params['toptab']='mantis';
	$params['user']=$user_id;
	/*
	 Show horizontal links
	 */
	site_user_header($params);
}


$user = session_get_user(); // get the session user
$type = getStringFromRequest('type');
$id = getStringFromRequest('id');
$pluginname = getStringFromRequest('pluginname');

if (!$user || !is_object($user) || $user->isError() || !$user->isActive()) {
	exit_error("Invalid User", "Cannot Process your request for this user.");
}

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
		if ( ! ($group->usesPlugin ( $pluginname )) ) {//check if the group has the HelloWorld plugin active
			exit_error("Error", "First activate the $pluginname plugin through the Project's Admin Interface");
		}
		$userperm = $group->getPermission ();//we'll check if the user belongs to the group (optional)
		/**
		if ( !$userperm->IsMember()) {
			exit_error("Access Denied", "You are not a member of this project");
		}
		**/
		// other perms checks here...
		helloworld_Project_Header(array('title'=>$pluginname . ' Project Plugin!','pagename'=>"$pluginname",'sectionvals'=>array(group_getname($id))));
		// DO THE STUFF FOR THE PROJECT PART HERE
			echo "Cet onglet vous permet d'initialiser un compte Mantis &agrave; partir de votre compte GForge. <br>";
		echo "Le serveur est " . $serveur_mantis; // $serveur_mantis comes from the config file in /etc
	} elseif ($type == 'user') {
		$realuser = user_get_object($id);
		if (!($realuser) || !($realuser->usesPlugin($pluginname))) {
			exit_error("Error", "First activate the User's $pluginname plugin through Account Manteinance Page");
		}
		if ( (!$user) || ($user->getID() != $id)) { // if someone else tried to access the private HelloWorld part of this user
			exit_error("Access Denied", "You cannot access other user's personal $pluginname");
		}
		helloworld_User_Header(array('title'=>'My '.$pluginname,'pagename'=>"$pluginname",'sectionvals'=>array($realuser->getUnixName())));
		// DO THE STUFF FOR THE USER PART HERE
			echo "Bienvenue &agrave; l'initiateur de compte Mantis <br>";
		echo "Cette page vous permet de charger votre compte dans " . $serveur_mantis; // $serveur_mantis comes from the config file in /etc
		?>
            <form>
            <input type="hidden" name="init_user_mantis" value="true">
            <input type="submit" name="Initialiser compte Mantis" value="Initialiser compte Mantis">
            </form>
            <form>
            <input type="hidden" name="update_user_mantis" value="true">
            <input type="submit" name="Mettre à jour votre compte Mantis" value="Mettre à jour votre compte Mantis">
            </form>            
            <?php
		if ($_REQUEST["init_user_mantis"]) {
			insert_mantis_user($id);
		} elseif ($_REQUEST["init_user_mantis"]) {
			update_mantis_user($id);
		}
	} elseif ($type == 'admin') {
		$group = group_get_object($id);
		if ( !$group) {
			exit_error("Invalid Project", "Inexistent Project");
		}
		if ( ! ($group->usesPlugin ( $pluginname )) ) {//check if the group has the HelloWorld plugin active
			exit_error("Error", "First activate the $pluginname plugin through the Project's Admin Interface");
		}
		$userperm = $group->getPermission ();//we'll check if the user belongs to the group
		if ( !$userperm->IsMember()) {
			exit_error("Access Denied", "You are not a member of this project");
		}
		//only project admin can access here
		if ( $userperm->isAdmin() ) {
			helloworld_Project_Header(array('title'=>$pluginname . ' Project Plugin!','pagename'=>"$pluginname",'sectionvals'=>array(group_getname($id))));
			// DO THE STUFF FOR THE PROJECT ADMINISTRATION PART HERE
			echo "Nous y voilà dans l'administration du connecteur Mantis <font color=\"#ff0000\">ADMINISTRATION</font> <br>";
			echo "Le serveur en question est " . $serveur_mantis; // $serveur_mantis comes from the config file in /etc
		} else {
			exit_error("Access Denied", "You are not a project Admin");
		}
	}
}

site_project_footer(array());


function insert_mantis_user ($id) {
	$realuser = user_get_object($id);
	$sql = "INSERT INTO users
				    (id, username, email, password, date_created, last_visit,
				     enabled, access_level, login_count, cookie_string, realname
                     )
				  VALUES
				    ( $id, '".$realuser->getUnixName()."', '".$realuser->getEmail()
	. "', '".$realuser->getMD5Passwd()."', " . db_now() . ","
	. db_now() . ", TRUE, 5, 1, '', '"
	.str_replace("'","\\'",$realuser->getRealName())
	."')";
	$mycn = mysql_connect(forge_get_config('db_host','mantis'),forge_get_config('db_user','mantis'),forge_get_config('db_passwd','mantis'));
	$test = mysql_select_db(forge_get_config('db_name','mantis'),$mycn);
	$test = mysql_query($sql,$mycn);
	if ($test) {
		echo "Insertion dans Mantis BT OK";
	} else {
		echo "Echec d'insertion dans Mantis BT";
	}
}

function update_mantis_user ($id) {
	$realuser = user_get_object($id);
	$sql = "UPDATE users SET (username='".$realuser->getUnixName()."',email='".$realuser->getEmail()."',password='".$realuser->getMD5Passwd()."',realname='".str_replace("'","\\'",$realuser->getRealName())."') WHERE id=$id";
	$mycn = mysql_connect(forge_get_config('db_host','mantis'),forge_get_config('db_user','mantis'),forge_get_config('db_passwd','mantis'));
	$test = mysql_select_db(forge_get_config('db_name','mantis'),$mycn);
	$test = mysql_query($sql,$mycn);
	if ($test) {
		echo "Mise à jour dans Mantis BT OK";
	} else {
		echo "Echec d'insertion dans Mantis BT";
	}
}
  
// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
