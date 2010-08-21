<?php // -*- php -*-
// $Id: XmlRpcClient.php 7638 2010-08-11 11:58:40Z vargenau $
/* Copyright (C) 2002, Lawrence Akka <lakka@users.sourceforge.net>
 * Copyright (C) 2004,2005,2006 $ThePhpWikiProgrammingTeam
 */
// All these global declarations that this file
// XmlRpcClient.php can be included within a function body
// (not in global scope), and things will still work.

global $xmlrpcI4, $xmlrpcInt, $xmlrpcBoolean, $xmlrpcDouble, $xmlrpcString;
global $xmlrpcDateTime, $xmlrpcBase64, $xmlrpcArray, $xmlrpcStruct;
global $xmlrpcTypes;
global $xmlEntities;
global $xmlrpcerr, $xmlrpcstr;
global $xmlrpc_defencoding;
global $xmlrpcName, $xmlrpcVersion;
global $xmlrpcerruser, $xmlrpcerrxml;
global $xmlrpc_backslash;
global $_xh;
global $_xmlrpcs_debug;

define('XMLRPC_EXT_LOADED', true);
if (loadPhpExtension('xmlrpc')) { // fast c lib
    global $xmlrpc_util_path;
    $xmlrpc_util_path = dirname(__FILE__)."/XMLRPC/";
    include_once("lib/XMLRPC/xmlrpc_emu.inc");
 } else { // slow php lib
    // Include the php XML-RPC library
    include_once("lib/XMLRPC/xmlrpc.inc");
}

// API version
// See http://www.jspwiki.org/wiki/WikiRPCInterface  for version 1
// See http://www.jspwiki.org/wiki/WikiRPCInterface2 for version 2 (we support 80%)
define ("WIKI_XMLRPC_VERSION", 1);

/*
 * Helper functions for encoding/decoding strings.
 *
 * According to WikiRPC spec, all returned strings take one of either
 * two forms.  Short strings (page names, and authors) are converted to
 * UTF-8, then rawurlencode()d, and returned as XML-RPC <code>strings</code>.
 * Long strings (page content) are converted to UTF-8 then returned as
 * XML-RPC <code>base64</code> binary objects.
 */

/**
 * Urlencode ASCII control characters.
 *
 * (And control characters...)
 *
 * @param string $str
 * @return string
 * @see urlencode
 */
function UrlencodeControlCharacters($str) {
    return preg_replace('/([\x00-\x1F])/e', "urlencode('\\1')", $str);
}

/**
 * Convert a short string (page name, author) to xmlrpcval.
 */
function short_string ($str) {
    return new xmlrpcval(UrlencodeControlCharacters(utf8_encode($str)), 'string');
}

/**
 * Convert a large string (page content) to xmlrpcval.
 */
function long_string ($str) {
    return new xmlrpcval(utf8_encode($str), 'base64');
}

/**
 * Decode a short string (e.g. page name)
 */
function short_string_decode ($str) {
    return utf8_decode(urldecode($str));
}

function wiki_xmlrpc_post($method, $args = null, $url = null, $auth = null) {
    if (is_null($url)) {
	//$url = deduce_script_name();
	$url = DATA_PATH . "/RPC2.php"; // connect to self
    }
    $debug = 0;
    $server = parse_url($url);
    if (empty($server['host'])) {
	$server['host'] = 'localhost';
    }
    if (!empty($_GET['start_debug'])) {
	$debug = 2;
    }
    if (DEBUG & _DEBUG_REMOTE) {  // xmlrpc remote debugging
	$debug = 2;
	$server['path'] .= '?start_debug=1';
    }
    $params = array('method' => $method,
		    'args'   => $args,
		    'host'   => $server['host'],
		    'uri'    => $server['path'],
		    'debug'  => $debug,
		    'output' => null);
    //TODO: auth and/or session cookie
    if (isset($auth['sid']))
        $params['cookies'] = array(session_name() => $auth['sid']);
    if (isset($auth['user']))
        $params['user'] = $auth['user'];
    if (isset($auth['pass']))
        $params['pass'] = $auth['pass'];
    $result = xu_rpc_http_concise($params);
    return $result;
}

// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End: 
?>
