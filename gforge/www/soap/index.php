<?php

// 0. Include GForge files for access to GForge system
require_once('www/include/squal_pre.php');
require_once('www/include/BaseLanguage.class');

$uri = 'http://'.$sys_default_domain;

// 1. include client and server
require_once('./nusoap.php');

// 2. instantiate server object
$server = new soap_server();
//configureWSDL($serviceName,$namespace = false,$endpoint = false,$style='rpc', $transport = 'http://schemas.xmlsoap.org/soap/http');
$server->configureWSDL('GForgeAPI',$uri);

// set schema target namespace
$server->wsdl->schemaTargetNamespace = $uri.'/';
$server->namespaces['s0'] = $uri;
// add types

$server->wsdl->addComplexType(
	'ArrayOfstring',
	'complexType',
	'array',
	'',
	'',
	array(),
	array(array('ref'=>'SOAP-ENC:Array','wsdl:arrayType'=>'string[]')),
	'xsd:string'
);

// session/authentication
$server->register(
	'login',
	array('userid'=>'xsd:string','passwd'=>'xsd:string'),
	array('loginResponse'=>'xsd:string'),
	$uri);

$server->register(
	'logout',
	null,
	array('logoutResponse'=>'xsd:string'),
	$uri);

//
//	Include Group Functions
//
require_once('www/soap/common/group.php');

//
//	Include User Functions
//
require_once('www/soap/common/user.php');



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
function continueSession($sessionKey) {
	global $session_ser, $Language;
	$session_ser = $sessionKey;
	session_set();
	$Language=new BaseLanguage();
	$Language->loadLanguage("English"); // TODO use the user's default language
	setlocale (LC_TIME, $Language->getText('system','locale'));
	$sys_strftimefmt = $Language->getText('system','strftimefmt');
	$sys_datefmt = $Language->getText('system','datefmt');

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
		return new soap_fault('1001', 'user', "Unable to log in with userid of ".$userid." and password of ".$passwd, 'No Detail');
 	}
	
	return new soapval('tns:soapVal','string',$session_ser);
}

/**
 * logout - Logs out a SOAP client
 *
 * @param 	string	sessionkey	The session key
 */
function logout($sessionkey) {
	continueSession($sessionkey);
	session_logout();
   	return new soapval('tns:soapVal','string',"OK");
}


// 4. call the service method to initiate the transaction and send the response
$HTTP_RAW_POST_DATA = isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : '';
$server->service($HTTP_RAW_POST_DATA);

if(isset($log) and $log != ''){
	harness('nusoap_r2_base_server',$server->headers['User-Agent'],$server->methodname,$server->request,$server->response,$server->result);
}

?>
