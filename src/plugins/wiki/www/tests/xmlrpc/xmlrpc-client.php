<?php // #!/usr/local/bin/php -Cq
/*
 * Test the wiki XMLRPC interface methods.
 * This is a client app used to query most methods from our server.

 * The interface specification is that discussed at 
 * http://www.ecyrd.com/JSPWiki/Wiki.jsp?page=WikiRPCInterface

 * Author: Reini Urban
 * $Id: xmlrpc-client.php 7181 2009-10-05 14:25:48Z vargenau $
 */

/*
  This file is part of, or distributed with, libXMLRPC - a C library for 
  xml-encoded function calls.

  Author: Dan Libby (dan@libby.com)
  Epinions.com may be contacted at feedback@epinions-inc.com
*/

/*  
  Copyright 2001 Epinions, Inc. 

  Subject to the following 3 conditions, Epinions, Inc.  permits you, free 
  of charge, to (a) use, copy, distribute, modify, perform and display this 
  software and associated documentation files (the "Software"), and (b) 
  permit others to whom the Software is furnished to do so as well.  

  1) The above copyright notice and this permission notice shall be included 
  without modification in all copies or substantial portions of the 
  Software.  

  2) THE SOFTWARE IS PROVIDED "AS IS", WITHOUT ANY WARRANTY OR CONDITION OF 
  ANY KIND, EXPRESS, IMPLIED OR STATUTORY, INCLUDING WITHOUT LIMITATION ANY 
  IMPLIED WARRANTIES OF ACCURACY, MERCHANTABILITY, FITNESS FOR A PARTICULAR 
  PURPOSE OR NONINFRINGEMENT.  

  3) IN NO EVENT SHALL EPINIONS, INC. BE LIABLE FOR ANY DIRECT, INDIRECT, 
  SPECIAL, INCIDENTAL OR CONSEQUENTIAL DAMAGES OR LOST PROFITS ARISING OUT 
  OF OR IN CONNECTION WITH THE SOFTWARE (HOWEVER ARISING, INCLUDING 
  NEGLIGENCE), EVEN IF EPINIONS, INC.  IS AWARE OF THE POSSIBILITY OF SUCH 
  DAMAGES.    

*/

$cur_dir = getcwd();
# Add root dir to the path
if (substr(PHP_OS,0,3) == 'WIN')
    $cur_dir = str_replace("\\","/", $cur_dir);
$rootdir = $cur_dir . '/../../';
$ini_sep = substr(PHP_OS,0,3) == 'WIN' ? ';' : ':';
$include_path = ini_get('include_path') . $ini_sep . $rootdir . $ini_sep . $rootdir . "lib/pear";
ini_set('include_path', $include_path);

if ($HTTP_SERVER_VARS["SERVER_NAME"] == 'phpwiki.sourceforge.net') {
    ini_set('include_path', ini_get('include_path') . ":/usr/share/pear");
}
define('PHPWIKI_NOMAIN', true);
# Quiet warnings in IniConfig.php
$HTTP_SERVER_VARS['REMOTE_ADDR'] = '127.0.0.1';
$HTTP_SERVER_VARS['HTTP_USER_AGENT'] = "PHPUnit";
# Other needed files
require_once $rootdir.'index.php';

// fake a POST request to be able to load our interfaces
$save = $GLOBALS['HTTP_SERVER_VARS']['REQUEST_METHOD'];
$GLOBALS['HTTP_SERVER_VARS']['REQUEST_METHOD'] = "POST";
include($rootdir."lib/XmlRpcServer.php");
$GLOBALS['HTTP_SERVER_VARS']['REQUEST_METHOD'] = $save;
// now we have $wiki_dmap

include("./xmlrpc-servers.php");

function match($a, $b) {
   $matches = true;
   if (gettype($a) === "array") {
      foreach ($a as $key => $c) {
         $d = $b[$key];
         if (!match($c, $d)) {
            $matches = false;
            break;
         }
      }
   } else {
      if ($a !== $b || xmlrpc_get_type($a) !== xmlrpc_get_type($b)) {
         $matches = false;
      }
   }
   return $matches;
}

function pass($method) {
   echo "<font color=\"green\"><b>pass</b></font> $method()<br>";
}

function fail($method, $sent, $received) {
   echo "<font color=\"red\"><b>fail</b></font> $method()<br>";
   if ($sent) {
     echo "<h3>sent</h3><xmp>";
     var_dump($sent);
     echo "</xmp>";
   }

   if ($received) {
       echo "<h3>received</h3><xmp>";
       var_dump($received);
       echo "</xmp>";
   }
}

// this needs to be fixed.
function check_if_matches($method, $sent, $received) {
   if (match($sent, $received)) {
       pass($method);
   }
   else {
       fail($method, $sent, $received);
   }
}

function foo($method_name, $args) {
    xmlrpc_encode_request($method_name, $args);
}

function run_test($server, $debug, $output, $method, $args='', $expected='') {
    global $HTTP_GET_VARS;
    echo "<hr>";
    if (!is_array($args))
        $params = $args ? array($args) : array();
    else
        $params = $args;
    if (!empty($HTTP_GET_VARS['start_debug'])) // zend ide support
        $server['uri'] .= "?start_debug=1";
    $result =  xu_rpc_http_concise(array('method' => $method,
                                         'args'   => $params, 
                                         'host'   => $server['host'], 
                                         'uri'    => $server['uri'], 
                                         'port'   => $server['port'], 
                                         'debug'  => $debug,
                                         'output' => $output));
    check_if_matches($method, $expected, $result);
    echo "</hr>";
    flush();
}

// should return non-zero integer
function run_no_param_test($server, $debug, $output, $method) {
    global $HTTP_GET_VARS;
    echo "<hr>";
    if (!empty($HTTP_GET_VARS['start_debug'])) // zend ide support
        $server['uri'] .= "?start_debug=1";
    $result =  xu_rpc_http_concise(array('method' => $method,
                                         'host'   => $server['host'], 
                                         'uri'    => $server['uri'], 
                                         'port'   => $server['port'], 
                                         'debug'  => $debug,
                                         'output' => $output));

    if ($result && gettype($result) === "integer") {
        pass($method);
    }
    else {
        fail($method, false, $result);
    }
   
    flush();
}


// a method to run wiki tests against remote server. tests described at bottom.
function run_easy_tests($server, $debug=0, $output = null) {

    //global $wiki_dmap;

    run_test($server, $debug, $output, "wiki.getRPCVersionSupported", '', 1);
    
    // getRecentChanges of the last day:
    // Note: may crash with dba on index.php, not on RPC2.php
    run_test($server, $debug, $output, "wiki.getRecentChanges", iso8601_encode(time()-86400));
    
    run_test($server, $debug, $output, "wiki.getPage", "HomePage", "* What is a WikiWikiWeb? A description of this application. * Learn HowToUseWiki and learn about AddingPages. * Use the SandBox page to experiment with Wiki pages. * Please sign your name in RecentVisitors. * See RecentChanges for the latest page additions and changes. * Find out which pages are MostPopular. * Read the ReleaseNotes and RecentReleases. * Administer this wiki via PhpWikiAdministration. * See more PhpWikiDocumentation.");
    run_test($server, $debug, $output, "wiki.getPageVersion", array("HomePage", 1));
    run_test($server, $debug, $output, "wiki.getPageHTML", "HomePage");
    run_test($server, $debug, $output, "wiki.getPageHTMLVersion", array("HomePage", 1));
    run_test($server, $debug, $output, "wiki.getAllPages");
    run_test($server, $debug, $output, "wiki.getPageInfo", "HomePage");
    run_test($server, $debug, $output, "wiki.getPageInfoVersion", array("HomePage", 1));
    run_test($server, $debug, $output, "wiki.listLinks", "HomePage");

    run_test($server, $debug, $output, "wiki.putPage", 
             array("PutPage", "new PutPage content", "XxXx"),
             array('code' => 200, 'version' => 1, 'message' => "Page PutPage version 1 created"));
    run_test($server, $debug, $output, "wiki.putPage", 
             array("PutPage", "new PutPage content", "XxXx"),
             array('code' => 400, 'version' => 1, 'message' => "Page PutPage unchanged"));
    run_test($server, $debug, $output, "wiki.putPage",
             array("PutPage", "new PutPage content", "disallowed"),
             array('code' => 401, 'version' => 0, 'message' => "no permission for disallowed"));

    run_test($server, $debug, $output, "wiki.rssPleaseNotify", "HomePage", 0);
    run_test($server, $debug, $output, "wiki.mailPasswordToUser", ADMIN_USER);

    run_test($server, $debug, $output, "wiki.titleSearch", "Hom");
}

function run_stress_tests($server, $debug=0, $output=null) {

    global $wiki_dmap;

    run_no_param_test($server, $debug, $output, "wiki.getRPCVersionSupported");
    // of the last day:
    run_test($server, $debug, $output, "wiki.getRecentChanges", iso8601_encode(time()-86400, 1));
    /* ... */
}

// a method to display an html form for invoking the script
function print_html_form($servers_list) {

   echo <<< END
<h1>Choose an xmlrpc wiki server to run tests against</h1>
END;

   print_servers_form($servers_list);
}

// some code which determines if we are in form display or response mode.
$server_list = get_wiki_servers();
$server = get_server_from_user($server_list);
if ($server) {
   $debug = $GLOBALS['HTTP_GET_VARS']['debug'] || $GLOBALS['HTTP_GET_VARS']['start_debug'];
   $output['version'] = $GLOBALS['HTTP_GET_VARS']['version'];
   if ($server) {
      $title = $server['title'];
      echo "<h2><center>Results for $title</center></h2>";
      
      if($GLOBALS['HTTP_GET_VARS']['stress'] == 1) {
         run_stress_tests($server, $debug, $output);
      }
      else {
         run_easy_tests($server, $debug, $output);
      }
   }
   else {
      echo "<h3>invalid option</h3>";
   }
}
else {
   print_html_form($server_list);
}
