<?php
//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net
//
// $Id$

/*
	redirect to proper hostname to get around certificate problem on IE 5
*/

// Defines all of the Source Forge hosts, databases, etc.
// This needs to be loaded first becuase the lines below depend upon it.
require ('/etc/local.inc');

/*

	Override vars are useful during development cycle

	Each developer can set up a file that overrides
	vars in /etc/local.inc, if needed.

	Each developer has a different hostname, so $sys_default_domain
	at a minimum must be overridden.

*/

if ($OVERRIDES_PATH) {
	require ($OVERRIDES_PATH."overrides.inc");
}

if (($HTTP_HOST != $GLOBALS['sys_default_domain']) && ($HTTP_HOST != $GLOBALS['sys_fallback_domain']) && ($HTTP_HOST != 'localhost') && ($HTTP_HOST != $GLOBALS['sys_default_domain'].':80')) {
	if ($SERVER_PORT == '443') {
		header ("Location: https://".$GLOBALS['sys_default_domain']."$REQUEST_URI");
	} else {
		header ("Location: http://".$GLOBALS['sys_default_domain']."$REQUEST_URI");
	}
	exit;
}

//library to determine browser settings
require('browser.php');

//base error library for new objects
require('Error.class');

// HTML layout class, may be overriden by the Theme class
require('Layout.class');

$HTML = new Layout();

//various html utilities
require('utils.php');

//database abstraction
require('database.php');

//security library
require('session.php');

// LDAP library
require('ldap.php');

//user functions like get_name, logged_in, etc
require('User.class');

//group functions like get_name, etc
require('Group.class');

//Project extends Group and includes preference accessors
require('Project.class');

//Foundry extends Group and includes preference/data accessors
require ('Foundry.class');

//library to set up context help
require('help.php');

//exit_error library
require('exit.php');

//various html libs like button bar, themable
require('html.php');

//left-hand nav library, themable
require('menu.php');

//theme functions like get_themename, etc
require('theme.php');

$sys_datefmt = "Y-M-d H:i";

// #### Connect to db

db_connect();

if (!$conn) {
	print "$sys_name Could Not Connect to Database: ".db_error();
	exit;
}

//determine if they're logged in
session_set();

//set up the themes vars
theme_sysinit($sys_themeid);

// OSDN functions and defs
require('osdn.php');

//insert this page view into the database
require('logger.php');

/*

	Timezone must come after logger to prevent messups


*/

if (user_isloggedin()) {
	//set up the user's timezone if they are logged in
	putenv('TZ='.user_get_timezone());
} else {
	//just user pacific time as always
}

/*

	Now figure out what language file to instantiate

*/

require ('BaseLanguage.class');

if (user_isloggedin()) {
	$user=&user_get_object(user_getid());
	$res=$user->getData();
	$classfile=db_result($res,0,'filename');
	if ($classfile) {
		include ("languages/$classfile");
		$classname=db_result($res,0,'classname');
		$Language=new $classname();
	} else {
		include ('languages/English.class');
	        $Language=new English();
	}
} else {
	//if you aren't logged in, check your browser settings 
	//and see if we support that language
	//if we don't support it, just use English as default
	$res = language_code_to_result ($HTTP_ACCEPT_LANGUAGE);
	$classfile=db_result($res,0,'filename');
	if (!$classfile) $classfile="English.class";
	include ("languages/$classfile");
	$classname=db_result($res,0,'classname');
	if (!$classname) $classname="English";
	$Language=new $classname();
}


/*


RESERVED VARIABLES

$conn
$session_hash
$Language
$User
$HTML
$foundry
$project
$Group

*/

?>
