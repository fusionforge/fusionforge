<?php

// 0. Include GForge files for access to GForge system
require_once('www/include/squal_pre.php');
require_once('www/include/BaseLanguage.class');

// includes for bug operations
require_once('www/tracker/include/ArtifactTypeHtml.class');
require_once('www/tracker/include/ArtifactHtml.class');
require_once('common/tracker/ArtifactFactory.class');
require_once('common/tracker/ArtifactTypeFactory.class');

// requires for general site info
require_once('common/include/GForge.class');
require_once('common/include/Stats.class');

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

// Add The definition of a group object
$server->wsdl->addComplexType(
	'GroupObject',
	'complexType',
	'struct',
	'sequence',
	'',
	array(
	'group_id' => array('name'=>'group_id', 'type' => 'xsd:integer'), 
	'group_name' => array('name'=>'group_name', 'type' => 'xsd:string'),
	'is_public' => array('name'=>'is_public', 'type' => 'xsd:integer'),
	'status' => array('name'=>'status', 'type' => 'xsd:string'),
	'unix_group_name' => array('name'=>'unix_group_name', 'type' => 'xsd:string')
	)
);

// Add the definition of a SiteStatsDataPoint object
$server->wsdl->addComplexType(
	'SiteStatsDataPoint',
	'complexType',
	'struct',
	'',
	'',
	array(
	'date' => array('name'=>'date', 'type' => 'xsd:string'), 
	'users' => array('name'=>'users', 'type' => 'xsd:string'), 
	'sessions' => array('name'=>'sessions', 'type' => 'xsd:string') 
	)
);

// An array of SiteStatsDataPoint objects
$server->wsdl->addComplexType(
	'ArrayOfSiteStatsDataPoint',
	'complexType',
	'array',
	'',
	'SOAP-ENC:Array',
	array(),
	array(array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'tns:SiteStatsDataPoint[]')),
	'tns:SiteStatsDataPoint'
);

// Add the definition of a Bug object
$server->wsdl->addComplexType(
	'Bug',
	'complexType',
	'struct',
	'',
	'',
	array(
	'id' => array('name'=>'id', 'type' => 'xsd:string'), 
	'summary' => array('name'=>'summary', 'type' => 'xsd:string')
	)
);

// And here's the definition of an array of bugs - for use in bugList, for example
$server->wsdl->addComplexType(
	'ArrayOfBug',
	'complexType',
	'array',
	'sequence',
	'SOAP-ENC:Array',
	array(),
	array(array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'tns:Bug[]')),
	'tns:Bug'
	);

$server->wsdl->addComplexType(
	'ArrayOfGroupObject',
	'complexType',
	'array',
	'',
	'SOAP-ENC:Array',
	array(),
	array(array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'tns:GroupObject[]')), 
	'tns:GroupObject');


// TODO: Create and add a definition for a bug object
// 3. call the register() method for each service (function) you want to expose:
$server->register(
	'hello',
	array('parm'=>'xsd:string'),
	array('helloResponse'=>'xsd:string'),
	$uri);

$server->register(
	'getSiteStats',
	null,
	array('siteStats'=>'tns:ArrayOfSiteStatsDataPoint'),
	$uri);

$server->register(
	'group',
	array('func'=>'xsd:string','params'=>'tns:ArrayOfstring'),
	array('groupResponse'=>'tns:ArrayOfGroupObject'),
	$uri);

$server->register(
	'getNumberOfHostedProjects',
	null,
	array('hostedProjects'=>'xsd:string'),
	$uri);

$server->register(
	'getNumberOfActiveUsers',
	null,
	array('activeUsers'=>'xsd:string'),
	$uri);

$server->register(
	'getPublicProjectNames',
	null,
	array('projectNames'=>'tns:ArrayOfstring'),
	$uri);

$server->register(
	'user',
	array('func'=>'xsd:string','params'=>'tns:ArrayOfstring'),
	array('userResponse'=>'tns:ArrayOfstring'),
	$uri);

$server->register(
	'bugFetch',
	array('sessionkey'=>'xsd:string','project'=>'xsd:string','bugid'=>'xsd:string'),
	array('bugFetchResponse'=>'tns:Bug'),
	$uri);

$server->register(
	'bugList',
	array('sessionkey'=>'xsd:string','project'=>'xsd:string'),
	array('bugListResponse'=>'tns:ArrayOfstring'),
	$uri);

$server->register(
	'bugAdd',
	array('sessionkey'=>'xsd:string','project'=>'xsd:string','summary'=>'xsd:string','details'=>'xsd:string'),
	array('bugAddResponse'=>'xsd:string'),
	$uri);

$server->register(
	'bugUpdate',
	array('sessionkey'=>'xsd:string','project'=>'xsd:string','bugid'=>'xsd:string','comment'=>'xsd:string'),
	array('bugUpdateResponse'=>'xsd:string'),
	$uri);

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
	global $session_ser;
	$session_ser = $sessionKey;
	session_set();
}

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
	setcookie("session_ser", "", time() - 3600, "/", 0);
    	return new soapval('tns:soapVal','string',"OK");
}

/**
 * getNumberOfHostedProjects - gets the number of active projects
 *
 */
function getNumberOfHostedProjects() {
	$gforge = new GForge();
	return new soapval('tns:soapVal', 'string', $gforge->getNumberOfHostedProjects());
}

function getSiteStats() {
	$stats = new Stats();
	$res = $stats->getSiteStats();
	$rows=db_numrows($res);
	$resultwrapper = array();
	for ($i=0; $i<$rows; $i++) {
		$result = array();
		$yearmonth = db_result($res, $i, 'month');
		$result['date']= substr($yearmonth, 0, 4)."-".substr($yearmonth, 4,5)."-".db_result($res, $i, 'day');
		$result['users']= db_result($res, $i, 'total_users');
		$result['sessions']= db_result($res, $i, 'sessions');
		$resultwrapper[$i] = new soapval('tns:SiteStatsDataPoint', 'SiteStatsDataPoint', $result);
	}
	return new soapval('tns:ArrayOfSiteStatsDataPoint', 'ArrayOfSiteStatsDataPoint', $resultwrapper);
}

/**
 * getNumberOfActiveUsers - gets the number of active users
 *
 */
function getNumberOfActiveUsers() {
	$gforge = new GForge();
	return new soapval('tns:soapVal', 'string', $gforge->getNumberOfActiveUsers());
}

/**
 * getPublicProjectNames - gets a list of public project names
 *
 */
function getPublicProjectNames() {
	$gforge = new GForge();
	return new soapval('tns:ArrayOfString', 'ArrayOfstring', $gforge->getPublicProjectNames());
}

/**
 * bugList - Lists all open bugs for a project
 * 
 * @param	string	sessionkey	The current session key
 * @param	string 	project		The project that the bug is in
 */
function bugList($sessionkey, $project) {
	continueSession($sessionkey);

	$group =& group_get_object_by_name($project);
	if (!$group) {
		return new soapval('tns:soapVal','string',"Couldn't create group");
	}

	$atf = new ArtifactTypeFactory($group);
	$atf->setDataType("1"); // TODO reference a constant or something here
	$artifactType = $atf->getArtifactTypes();
	if (!$artifactType) {
		return new soapval('tns:soapVal','string',"Couldn't create ArtifactType: ".$atf->getErrorMessage());
	}
	$af = new ArtifactFactory($artifactType[0]);
	if (!$af) {
		return new soapval('tns:soapVal','string',"Couldn't create ArtifactFactory: ".$af->getErrorMessage());
	}
	
	$af->setup('','','','','',0,1,'','');
	$art_arr =& $af->getArtifacts();

	$result = array();
	for ($i = 0;$i < count($art_arr); $i++) {
		$result[$i] = $art_arr[$i]->getID();	
	}

	return new soapval('tns:ArrayOfString', 'ArrayOfstring', $result);
}

function bugFetch($sessionkey, $project, $bugid) {
continueSession($sessionkey);

$group =& group_get_object_by_name($project);
	if (!$group) {
		return new soapval('tns:soapVal','string',"Couldn't create group");
	}
	$atf = new ArtifactTypeFactory($group);
	$atf->setDataType("1"); // TODO reference a constant or something here
	$artifactType = $atf->getArtifactTypes();
	if (!$artifactType) {
		return new soapval('tns:soapVal','string',"Couldn't create ArtifactType: ".$atf->getErrorMessage());
	}

	$bug = new Artifact($artifactType[0], $bugid);
	if (!$bug) {
		return new soapval('tns:soapVal','string',"Couldn't fetch bug");
	}

	$result = array();
	$result["id"] = $bug->getID();
	$result["summary"] = $bug->getSummary();
	return new soapval('tns:Bug', 'Bug', $result);
}

/**
 * bugUpdate - Update a bug
 *
 * @param	string	sessionkey	The current session key
 * @param	string 	project		The project that the bug is in
 * @param	string	bugid		The bug id to be updated
 * @param	string	comment		The comment to add
 */
function bugUpdate($sessionkey, $project, $bugid, $comment) {
	continueSession($sessionkey);

	$group =& group_get_object_by_name($project);
	if (!$group) {
    		return new soapval('tns:soapVal','string',"Couldn't create group");
	}
	
	$atf = new ArtifactTypeFactory($group);
	$atf->setDataType("1"); // TODO reference a constant or something here
	$artifactType = $atf->getArtifactTypes();
	if (!$artifactType) {
    		return new soapval('tns:soapVal','string',"Couldn't create ArtifactType: ".$atf->getErrorMessage());
	}

	$bug = new Artifact($artifactType[0], $bugid);
	if (!$bug) {
    		return new soapval('tns:soapVal','string',"Couldn't fetch bug");
	}

	if (!$bug->update(	$bug->getPriority(),
			1,
			'100',
			'100',
			$bug->getResolutionID(),
			'100',
			$bug->getSummary(),
			'100',
			$comment,
			$artifactType[0]->getID())) {
    		return new soapval('tns:soapVal','string',"Couldn't update bug: ".$bug->getErrorMessage());
	}
	return new soapval('tns:soapVal','string',"new comment: ".$comment);
}

/**
 * bugAdd - Add a new bug
 *
 * @param	string	sessionkey	The current session key
 * @param	string 	project		The project that the bug is in
 * @param 	string	summary		The bug summary
 * @param 	string	details		The bug details
 */
function bugAdd($sessionkey, $project, $summary, $details) {
	continueSession($sessionkey);
	
	$group =& group_get_object_by_name($project);
	if (!$group) {
    		return new soapval('tns:soapVal','string',"Couldn't find a project named ".$project);
	}

	$atf = new ArtifactTypeFactory($group);
	$atf->setDataType("1"); // TODO reference a constant or something here
	$artifactType = $atf->getArtifactTypes();
	if (!$artifactType[0]) {
    		return new soapval('tns:soapVal','string',"Couldn't create ArtifactType: ".$artifactTypeFactory->getErrorMessage());
	}
	$artifact=new Artifact($artifactType[0]);
	if (!$artifact->create('100', '100', $summary, $details)) {
		return new soapval('tns:soapVal','string',"Couldn't create bug: ".$artifact->getErrorMessage());
	}
	return new soapval('tns:soapVal','string',$artifact->getID());
}

function hello($inputString){
return new soapval('tns:soapVal','string',$inputString.' echoed back to you');
}

function user($func, $params){
	if ($func == "get") {
		$where = "";
		$prefix = " where user_name in (";
		while (list($key, $name) = each($params)) {
			$where .= $prefix."'".$name."'";
			$prefix = ",";
		}
		if ($where != "") {
			$where .= ")";
		}
	
		$res = db_query("select * from users ".$where);
		$result_array = array();
		
		if ($res && db_numrows($res) > 0) {
			while ( $row = db_fetch_array($res) ) {
				while (list($key, $val) = each($row)) {
					if (!is_int($key)) {
						$result_array[] = $key;
						$result_array[] = "$val";
					}
				}
			}
		}
		return new soapval('tns:userInfo','ArrayOfstring',$result_array);
	} 
	return new soap_fault ('1001', 'user', 'Unknown Function('.$func.') Must be get|set|add|delete', 'No Detail');
}

function group($func, $params){
	if ($func == "get") {
		$where = "";
		$prefix = " where unix_group_name in (";
		while (list($key, $name) = each($params)) {
			if ($name != "all") {
				$where .= $prefix."'".$name."'";
				$prefix = ",";
			}
		}
		if ($where != "") {
			$where .= ")";
		}
		$res = db_query("select group_id, group_name, is_public, status, unix_group_name from groups ".$where);
		$result_array = array();
		if ($res && db_numrows($res) > 0) {
			while ( $row = db_fetch_array($res) ) {
				$inner_array = array();
				while (list($key, $val) = each($row)) {
					if (!is_int($key)) {
						$inner_array[$key] = $val;
					}
				}
				$result_array[] = $inner_array;
			}
		}
		return new soapval('GroupObject','tns:GroupObject',$result_array);
	} 
	return new soap_fault ('1001', 'user', 'Unknown Function('.$func.') Must be \'get\'', 'No Detail');
}


// 4. call the service method to initiate the transaction and send the response
$HTTP_RAW_POST_DATA = isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : '';
$server->service($HTTP_RAW_POST_DATA);

if(isset($log) and $log != ''){
	harness('nusoap_r2_base_server',$server->headers['User-Agent'],$server->methodname,$server->request,$server->response,$server->result);
}


//////////////////////////////////////////
// Here's some complex type example code:
/*
$server->wsdl->addComplexType(
	'SOAPStruct',
	'complexType',
	'struct',
	'',
	'',
	array(
  'varString' => array('name'=>'varString', 'type' => 'xsd:string'),
	)
);

$server->register(
	'echoStruct',
	null,
	array('return'=>'tns:SOAPStruct'),
	$uri);

function echoStruct(){
	$foo = array();
	$foo['varString'] = "hello";
  return new soapval('tns:SOAPStruct', 'SOAPStruct', $foo);
}

$server->wsdl->addComplexType(
	'ArrayOfSOAPStruct',
	'complexType',
	'array',
	'',
	'SOAP-ENC:Array',
	array(),
	array(array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'SOAPStruct[]')),
	'SOAPStruct'
);

$server->register('echoStructArray',
	null,
	array('ret'=>'tns:ArrayOfSOAPStruct'), 
	$uri
);

function echoStructArray(){
	$ss1 = array();
	$ss1['varString'] = "hello";
	$ss2 = array();
	$ss2['varString'] = "world";

	$ssarray = array();
	$ssarray[0] = new soapval('tns:SOAPStruct', 'SOAPStruct', $ss1);
	$ssarray[1] = new soapval('tns:SOAPStruct', 'SOAPStruct', $ss2);
  return new soapval('tns:ArrayOfSOAPStruct', 'ArrayOfSOAPStruct', $ssarray);
}
*/
//////////////////////////////////////////
?>
