<?php
/**
 * SOAP interface
 *
 * Previous Copyright FusionForge Team
 * Copyright 2016, Franck Villaume - TrivialDev
 * http://gforge.org
 *
 * This file is part of FusionForge. FusionForge is free software;
 * you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or (at your option)
 * any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with FusionForge; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

$no_debug     = true;

// 0. Include FusionForge files for access to FusionForge system
require_once '../env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfcommon.'include/gettext.php';
require_once $gfcommon.'include/FusionForge.class.php';

ini_set('memory_limit','32M');
sysdebug_off();

// Disable error_reporting as it breaks XML generated output.
error_reporting(0);

$uri = util_make_url();
// 1. include client and server
require_once 'nusoap/nusoap.php';
//$debug = true;
// 2. instantiate server object
$server = new soap_server();
$server->setDebugLevel(0);
$server->configureWSDL('FusionForgeAPI',$uri,false,'rpc','http://schemas.xmlsoap.org/soap/http',$uri);

// add types
$server->wsdl->addComplexType(
	'ArrayOfstring',
	'complexType',
	'array',
	'',
	'SOAP-ENC:Array',
	array(),
	array(array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'xsd:string[]')),
	'xsd:string'
);

$server->wsdl->addComplexType(
	'ArrayOfInteger',
	'complexType',
	'array',
	'',
	'SOAP-ENC:Array',
	array(),
	array(array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'xsd:integer[]')),
	'xsd:integer'
);

$server->wsdl->addComplexType(
	'ArrayOflong',
	'complexType',
	'array',
	'',
	'SOAP-ENC:Array',
	array(),
	array(array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'xsd:long[]')),
	'xsd:long'
);

$server->wsdl->addComplexType(
	'ArrayOfint',
	'complexType',
	'array',
	'',
	'SOAP-ENC:Array',
	array(),
	array(array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'xsd:int[]')),
	'xsd:int'
);

// session/authentication
$server->register(
	'login',
	array('userid'=>'xsd:string','passwd'=>'xsd:string'),
	array('loginResponse'=>'xsd:string'),
	$uri,
	$uri.'#login');

$server->register(
	'logout',
	array('session_ser'=>'xsd:string'),
	array('logoutResponse'=>'xsd:string'),
	$uri,
	$uri.'#logout');

$server->register(
	'version',
	array(),
	array('versionResponse'=>'xsd:string'),
	$uri,
	$uri.'#version');
//
//	Include Project Functions
//
require_once $gfwww.'soap/common/group.php';

//
//	Include User Functions
//
require_once $gfwww.'soap/common/user.php';

//
//	Include tracker & tracker query Functions
//
require_once $gfwww.'soap/tracker/tracker.php';
require_once $gfwww.'soap/tracker/query.php';

//
//	Include Docman Functions
//
require_once $gfwww.'soap/docman/docman.php';

//
//	Include tasks Functions
//
require_once $gfwww.'soap/pm/pm.php';
require_once $gfwww.'soap/reporting/timeentry.php';

//
//	Include frs Functions
//
require_once $gfwww.'soap/frs/frs.php';

//
//	Include SCM Functions
//
require_once $gfwww.'soap/scm/scm.php';

$wsdl_data = $server->wsdl->serialize();

if (isset($wsdl)) {
	echo $wsdl_data;
	return;
}

/**
 * continueSession - A utility method to carry on with an already established session
 *
 * @param 	string		$sessionKey	The session key
 */
function continue_session($sessionKey) {
	session_continue($sessionKey);
}

// session/authentication
/**
 * login - Logs in a SOAP client
 *
 * @param	string	$userid	userid	The user's unix id
 * @param	string	$passwd	passwd	The user's passwd in clear text
 *
 * @return	string	the session key
 */
function login($userid, $passwd) {
	global $feedback, $session_ser;

	setlocale (LC_TIME, _('en_US'));

	$res = session_check_credentials_in_database($userid, $passwd);

	if (!$res) {
		return new soap_fault('1001', 'user', "Unable to log in with userid of ".$userid, $feedback);
 	}

	return session_build_session_token(user_getid());
}

/**
 * logout - Logs out a SOAP client
 *
 * @param 	string	$session_ser	sessionkey	The session key
 * @return string
 */
function logout($session_ser) {
	continue_session($session_ser);
	session_logout();
   	return "OK";
}

/**
 * version - get the running version of FusionForge
 *
 * @return	string 	the version of FusionForge running on the server
 */
function version() {
	$ff = new FusionForge();
	return $ff->software_version;
}

// 4. call the service method to initiate the transaction and send the response
$postdata = file_get_contents("php://input");
$server->service($postdata);

if(isset($log) and $log != ''){
	harness('nusoap_r2_base_server',$server->headers['User-Agent'],$server->methodname,$server->request,$server->response,$server->result);
}
