<?php

// 0. Include GForge files for access to GForge system

require_once('squal_pre.php');
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
	'SOAP-ENC:Array',
	array(),
	array(array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'string[]')),
	'xsd:string'
);
//
// Add The definition of a group object
//
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

$server->wsdl->addComplexType(
	'ArrayOfGroupObject',
	'complexType',
	'array',
	'',
	'SOAP-ENC:Array',
	array(),
	array(array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'tns:GroupObject[]')),
	'tns:GroupObject');

// 3. call the register() method for each service (function) you want to expose:
$server->register(
	'hello',
	array('parm'=>'xsd:string'),
	array('helloResponse'=>'xsd:string'),
	$uri);

function hello($inputString){
    return new soapval('tns:soapVal','string',$inputString.'echoed back to you');
}

$server->register(
	'user',
	array('func'=>'xsd:string','params'=>'tns:ArrayOfstring'),
	array('userResponse'=>'tns:ArrayOfstring'),
	$uri);

$server->register(
	'group',
	array('func'=>'xsd:string','params'=>'tns:ArrayOfstring'),
	array('groupResponse'=>'tns:ArrayOfGroupObject'),
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
?>

