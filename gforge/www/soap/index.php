<?php

// 0. Include GForge files for access to GForge system
require_once('www/include/squal_pre.php');
require_once('www/include/BaseLanguage.class');

$uri = 'http://'.$sys_default_domain;
// 1. include client and server
require_once('./nusoap.php');
//$debug = true;
// 2. instantiate server object
$server = new soap_server();
//configureWSDL($serviceName,$namespace = false,$endpoint = false,$style='rpc', $transport = 'http://schemas.xmlsoap.org/soap/http');
//$server->configureWSDL('GForgeAPI',$uri);
$server->configureWSDL('GForgeAPI',$uri,false,'rpc','http://schemas.xmlsoap.org/soap/http',$uri);

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

//
//	Include Group Functions
//
require_once('www/soap/common/group.php');

//
//	Include User Functions
//
require_once('www/soap/common/user.php');

//
//	Include tracker Functions
//
require_once('www/soap/tracker/tracker.php');

//
//	Include Docman Functions
//
require_once('www/soap/docman/docman.php');

//
//	Include task manager Functions
//
require_once('www/soap/pm/pm.php');

//
//	Include frs Functions
//
require_once('www/soap/frs/frs.php');


$wsdl_data = $server->wsdl->serialize();

if ($wsdl == "save") {
   $fp = fopen ("/tmp/SoapAPI1.wsdl", 'w');
   fputs ($fp, $wsdl_data);
   fclose ($fp);
}

if ($wsdl) {
	echo $wsdl_data;
	return;
}

/**
 * continueSession - A utility method to carry on with an already established session
 *
 * @param 	string		The session key
 */
function continue_session($sessionKey) {
	session_continue($sessionKey);
}

// session/authentication
/**
 * login - Logs in a SOAP client
 * 
 * @param	string	userid	The user's unix id
 * @param	string	passwd	The user's passwd in clear text
 *
 * @return	string	the session key
 */
function login($userid, $passwd) {
	global $feedback, $Language, $session_ser;
		
	$Language=new BaseLanguage();
	$Language->loadLanguage("English"); // TODO use the user's default language
	setlocale (LC_TIME, $Language->getText('system','locale'));
	$sys_strftimefmt = $Language->getText('system','strftimefmt');
	$sys_datefmt = $Language->getText('system','datefmt');

	$res = session_login_valid($userid, $passwd);
	
	if (!$res) {
		return new soap_fault('1001', 'user', "Unable to log in with userid of ".$userid." and password of ".$passwd, $feedback);
 	}
	
	return $session_ser;
}

/**
 * logout - Logs out a SOAP client
 *
 * @param 	string	sessionkey	The session key
 */
function logout($session_ser) {
	continue_session($session_ser);
	session_logout();
   	return "OK";
}


// 4. call the service method to initiate the transaction and send the response
$HTTP_RAW_POST_DATA = isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : '';
$server->service($HTTP_RAW_POST_DATA);

if(isset($log) and $log != ''){
	harness('nusoap_r2_base_server',$server->headers['User-Agent'],$server->methodname,$server->request,$server->response,$server->result);
}

?>
