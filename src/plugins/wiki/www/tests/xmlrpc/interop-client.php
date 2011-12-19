<?php // #!/usr/local/bin/php -Cq
/*
  This file is part of, or distributed with, libXMLRPC - a C library for 
  xml-encoded function calls.

  Author: Dan Libby (dan@libby.com)
  Epinions.com may be contacted at feedback@epinions-inc.com
  $Id: interop-client.php 7181 2009-10-05 14:25:48Z vargenau $
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
   echo "<font color='green'><b>pass</b></font> $method()<br>";
}

function fail($method, $sent, $received) {
   echo "<font color='red'><b>fail</b></font> $method()<br>";
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

function run_test($server, $debug, $output, $method, $args) {
    echo "<hr>";
    $params = array($args);
    $result =  xu_rpc_http_concise(array(method => $method,
                                         args   => $params, 
                                         host   => $server[host], 
                                         uri    => $server[uri], 
                                         port   => $server[port], 
                                         debug  => $debug,
                                         output => $output));
    check_if_matches($method, $args, $result);
    echo "</hr>";
    flush();
}

function run_no_param_test($server, $debug, $output, $method) {
    echo "<hr>";
    $result =  xu_rpc_http_concise(array(method => $method,
                                         host => $server[host], 
                                         uri  => $server[uri], 
                                         port => $server[port], 
                                         debug => $debug,
                                         output => $output));

    if($result && gettype($result) === "integer") {
        pass($method);
    }
    else {
        fail($method, false, $result);
    }
   
    flush();
}


$decl_1 = "IN CONGRESS, July 4, 1776.";
$decl_2 = "The unanimous Declaration of the thirteen united States of America,";
$decl_3 = "When in the Course of human events, it becomes necessary for one people to dissolve the political bands which have connected them with another, and to assume among the powers of the earth, the separate and equal station to which the Laws of Nature and of Nature's God entitle them, a decent respect to the opinions of mankind requires that they should declare the causes which impel them to the separation.";
$decl_4 = "We hold these truths to be self-evident, that all men are created equal, that they are endowed by their Creator with certain unalienable Rights,that among these are Life, Liberty and the pursuit of Happiness.--That to secure these rights, Governments are instituted among Men, deriving their just powers from the consent of the governed, --That whenever any Form of Government becomes destructive of these ends, it is the Right of the People to alter or to abolish it, and to institute new Government, laying its foundation on such principles and organizing its powers in such form, as to them shall seem most likely to effect their Safety and Happiness. Prudence, indeed, will dictate that Governments long established should not be changed for light and transient causes; and accordingly all experience hath shewn, that mankind are more disposed to suffer, while evils are sufferable, than to right themselves by abolishing the forms to which they are accustomed. But when a long train of abuses and usurpations, pursuing invariably the same Object evinces a design to reduce them under absolute Despotism, it is their right, it is their duty, to throw off such Government, and to provide new Guards for their future security.--Such has been the patient sufferance of these Colonies; and such is now the necessity which constrains them to alter their former Systems of Government. The history of the present King of Great Britain is a history of repeated injuries and usurpations, all having in direct object the establishment of an absolute Tyranny over these States. To prove this, let Facts be submitted to a candid world.";

// a method to run interop tests against remote server. tests described at bottom.
function run_easy_tests($server, $debug=0, $output = null) {
    global $decl_1, $decl_2, $decl_3, $decl_4;
    run_test($server, $debug, $output, "interopEchoTests.echoString", "That government is best, which governs least");
    run_test($server, $debug, $output, "interopEchoTests.echoBoolean", true);
    run_test($server, $debug, $output, "interopEchoTests.echoInteger", 42);
    run_test($server, $debug, $output, "interopEchoTests.echoFloat", 3.1416);
    run_test($server, $debug, $output, "interopEchoTests.echoStruct", array("varFloat" => 1.2345,
                                                                            "varInt" => 186000,
                                                                            "varString" => "a string" ));
    run_test($server, $debug, $output, "interopEchoTests.echoStringArray", array($decl_1, $decl_2, $decl_3, $decl_4));
    run_test($server, $debug, $output, "interopEchoTests.echoIntegerArray", array(23, 234, 1, 0, -10, 999));
    run_test($server, $debug, $output, "interopEchoTests.echoFloatArray", array(2.45, 9.9999));
    run_test($server, $debug, $output, "interopEchoTests.echoStructArray", array(array("varFloat" => 1.2345,
                                                                                       "varInt" => 186000,
                                                                                       "varString" => "a string"),
                                                                                 array("varFloat" => 10.98765,
                                                                                       "varInt" => 3200,
                                                                                       "varString" => "happy little string" )
                                                                                 ));
                                                                  
    $foo = "some base64 string";
    xmlrpc_set_type($foo, "base64");
    run_test($server, $debug, $output, "interopEchoTests.echoBase64", $foo);

    $foo="19980717T14:08:55";
    xmlrpc_set_type($foo, "datetime");
    run_test($server, $debug, $output, "interopEchoTests.echoDate", $foo);
   
    run_no_param_test($server, $debug, $output, "interopEchoTests.noInParams");
}

function ident($server, $debug=0, $output=null) {
    $method = "interopEchoTests.whichToolkit";
    $result =  xu_rpc_http_concise(array('method' => $method,
                                         'host'   => $server['host'],
                                         'uri'    => $server['uri'],
                                         'port'   => $server['port'], 
                                         'debug'  => $debug,
                                         'output' => $output));
    if ($result && $result['toolkitDocsUrl'] && !$result['faultCode']) {
        pass($method);
        echo "<br>";
        foreach($result as $key => $value) {
            if(substr($value, 0, 7) === "http://") {
                $value = "<a href='$value'>$value</a>";
            }
            echo "<b>$key:</b> $value<br>";
        }
    }
    else {
        fail($method, false, $result);
    }
}

function run_stress_tests($server, $debug=0, $output=null) {
    global $decl_1, $decl_2, $decl_3, $decl_4;
    run_test($server, $debug, $output, "interopEchoTests.echoString", "XML Comment in a string: <!-- A comment -->");
    run_test($server, $debug, $output, "interopEchoTests.echoInteger", 4200000000);
    run_test($server, $debug, $output, "interopEchoTests.echoFloat", 1.2);
    run_test($server, $debug, $output, "interopEchoTests.echoStruct", 
             array("varFloat" => 1.2345,
                   "varInt" => 186000,
                   "varString" => "18 > 2 && 2 < 18 && 42 == the answer to life, the universe, and everything" ));
    run_test($server, $debug, $output, "interopEchoTests.echoStringArray", 
             array($decl_1, $decl_2, $decl_3, $decl_4, "non-ascii chars above 127 (165-170): ¥, ¦, §, ¨, ©, ª"));
    run_test($server, $debug, $output, "interopEchoTests.echoIntegerArray", 
             array(23, 234, 1, 0, -10, 999));
    run_test($server, $debug, $output, "interopEchoTests.echoFloatArray", 
             array(2.45, 9.9999));
    run_test($server, $debug, $output, "interopEchoTests.echoStructArray", 
             array(array("varFloat" => 1.2345,
                         "varInt" => 186000,
                         "varString" => "non-print char (8): "),
                   array("varFloat" => 10.98765,
                         "varInt" => 3200,
                         "varString" => "happy little string" )
                   ));
}

// a method to display an html form for invoking the script
function print_html_form($servers_list) {

   echo <<< END
<h1>Choose an xmlrpc server to run interop tests against <i>live!</i></h1>
END;

   print_servers_form($servers_list);

echo <<< END
<p>
<i>if you know of any other servers that support interop tests, please send a note to
   <a href='mailto:xmlrpc-epi-devel@lists.sourceforge.net'>xmlrpc-epi-devel@lists.sourceforge.net</a>
   and we will add it to the list</i>.
END;

}

// some code which determines if we are in form display or response mode.
$server_list = get_interop_servers();
$server = get_server_from_user($server_list);
if ($server) {
   $debug = $GLOBALS['HTTP_GET_VARS']['debug'] || $GLOBALS['HTTP_GET_VARS']['start_debug'];
   $output['version'] = $GLOBALS['HTTP_GET_VARS']['version'];
   if ($server) {
      $title = $server['title'];
      echo "<h2><CENTER>Results for $title</CENTER></H2>";
      
      ident($server, $debug, $output);
      
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

/* Interop tests description:

interopEchoTests.echoString (inputString) -- Sends a random string to the server, and checks that the
response is a string whose value is the same as the string that was sent.

interopEchoTests.echoInteger (inputInteger) -- Sends a random integer to the server, and checks that
the response is an integer whose value is the same as the integer that was sent.

interopEchoTests.echoFloat (inputFloat) -- Sends a random floating point number to the server, and
checks that the response is a float whose value is the same as the number that was sent.

interopEchoTests.echoStruct (inputStruct) -- Sends a struct to the server, which contains three
elements: varString, varInt, and varFloat, a random string, a random integer, and a random float,
respectively, and checks that the response is a struct containing the same values that were sent.

interopEchoTests.echoStringArray (inputStringArray) -- Sends an array of random strings to the server,
and checks that the response is an array of strings, whose values are the same as the values that were
sent.

interopEchoTests.echoIntegerArray (inputIntegerArray) -- Sends an array of random integers to the
server, and checks that the response is an array of integers, whose values are the same as the values that
were sent.

interopEchoTests.echoFloatArray (inputFloatArray) -- Sends an array of random floating point numbers
to the server, and checks that the response is an array of floats, whose values are the same as the values
that were sent.

interopEchoTests.echoStructArray (inputStructArray) -- Sends an array of structs to the server, and
checks that the response is an array of structs, which contain the same values that were sent. Each struct
contains three elements: varString, varInt, and varFloat, a string, an integer, and a floating point number,
respectively.

 */
