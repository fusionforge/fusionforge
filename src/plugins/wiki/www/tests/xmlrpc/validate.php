<?php // $Id: validate.php 7181 2009-10-05 14:25:48Z vargenau $

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


include("xmlrpc_utils.php");

// ensure extension is loaded.
xu_load_extension();

/*   
 * This handler takes a single parameter, an array of structs, each of which 
 * contains at least three elements named moe, larry and curly, all <i4>s.  
 * Your handler must add all the struct elements named curly and return the 
 * result.  
 */
function validator1_arrayOfStructsTest($method_name, $params, $app_data) {
   $iCurly = 0;

   foreach(array_pop($params) as $iter) {
      $iCurly += $iter["curly"];
   }
                              
   return $iCurly;
}

/*
 * This handler takes a single parameter, a string, that contains any number 
 * of predefined entities, namely <, >, &, ' and ".  
 *
 * Your handler must return a struct that contains five fields, all numbers: 
 * ctLeftAngleBrackets, ctRightAngleBrackets, ctAmpersands, ctApostrophes, 
 * ctQuotes.  
 *
 * To validate, the numbers must be correct.
 */
function validator1_countTheEntities ($method_name, $params, $app_data) {
   $xStruct = array();

   //returns struct
   $counts = count_chars(array_pop($params), 0);

   $xStruct["ctLeftAngleBrackets"] = $counts[ord('<')];
   $xStruct["ctRightAngleBrackets"] = $counts[ord('>')];
   $xStruct["ctAmpersands"] = $counts[ord('&')];
   $xStruct["ctApostrophes"] = $counts[ord("'")];
   $xStruct["ctQuotes"] = $counts[ord('"')];

   return $xStruct;
}

/*
 * This handler takes a single parameter, a struct, containing at least three 
 * elements named moe, larry and curly, all <i4>s.  Your handler must add the 
 * three numbers and return the result.  
 */
function validator1_easyStructTest ($method_name, $params, $app_data) {
   $iSum = 0;

   $xStruct = array_pop($params);
   if($xStruct) {
      $iSum += $xStruct[curly];
      $iSum += $xStruct[moe];
      $iSum += $xStruct[larry];
   }

   return $iSum;
}

/*
 * This handler takes a single parameter, a struct.  Your handler must return 
 * the struct.  
 */
function validator1_echoStructTest($method_name, $params, $app_data) {
   return $params[0];
}


/*
 * This handler takes six parameters, and returns an array containing all the 
 * parameters.  
 */
function validator1_manyTypesTest ($method_name, $params, $app_data) {
   $xArray = array();
   
   foreach($params as $iter) {
     array_push($xArray, $iter);
   }


   return $xArray;
}

/*
 * This handler takes a single parameter, which is an array containing 
 * between 100 and 200 elements.  Each of the items is a string, your handler 
 * must return a string containing the concatenated text of the first and 
 * last elements.  
 */
function validator1_moderateSizeArrayCheck ($method_name, $params, $app_data) {
   $xArray = array_pop($params);
   if($xArray) {
      $buf = $xArray[0] . $xArray[count($xArray) - 1];
   }

   return $buf;
}

/*
 * This handler takes a single parameter, a struct, that models a daily 
 * calendar.  At the top level, there is one struct for each year.  Each year 
 * is broken down into months, and months into days.  Most of the days are 
 * empty in the struct you receive, but the entry for April 1, 2000 contains 
 * a least three elements named moe, larry and curly, all <i4>s.  Your 
 * handler must add the three numbers and return the result.  
 * 
 * Ken MacLeod: "This description isn't clear, I expected '2000.April.1' when 
 * in fact it's '2000.04.01'.  Adding a note saying that month and day are 
 * two-digits with leading 0s, and January is 01 would help." Done.  
 */
function validator1_nestedStructTest ($method_name, $params, $app_data) {

   $iSum = 0;

   $xStruct = array_pop($params);
   $xYear   = $xStruct['2000'];
   $xMonth  = $xYear['04'];
   $xDay    = $xMonth['01'];

   $iSum += $xDay["larry"];
   $iSum += $xDay["curly"];
   $iSum += $xDay["moe"];

   return $iSum;
}

/*
 * This handler takes one parameter, and returns a struct containing three 
 * elements, times10, times100 and times1000, the result of multiplying the 
 * number by 10, 100 and 1000.  
 */
function validator1_simpleStructReturnTest ($method_name, $params, $app_data) {
   $xStruct = array();

   $iIncoming = array_pop($params);

   $xStruct["times10"] = $iIncoming * 10;
   $xStruct["times100"] = $iIncoming * 100;
   $xStruct["times1000"] = $iIncoming * 1000;

   return $xStruct;
}


   /* create a new server object */
   $server = xmlrpc_server_create();

   xmlrpc_server_register_method($server, "validator1.arrayOfStructsTest", "validator1_arrayOfStructsTest");
   xmlrpc_server_register_method($server, "validator1.countTheEntities", "validator1_countTheEntities");
   xmlrpc_server_register_method($server, "validator1.easyStructTest", "validator1_easyStructTest");
   xmlrpc_server_register_method($server, "validator1.echoStructTest", "validator1_echoStructTest");
   xmlrpc_server_register_method($server, "validator1.manyTypesTest", "validator1_manyTypesTest");
   xmlrpc_server_register_method($server, "validator1.moderateSizeArrayCheck", "validator1_moderateSizeArrayCheck");
   xmlrpc_server_register_method($server, "validator1.nestedStructTest", "validator1_nestedStructTest");
   xmlrpc_server_register_method($server, "validator1.simpleStructReturnTest", "validator1_simpleStructReturnTest");

	// name differently for soap.
   xmlrpc_server_register_method($server, "arrayOfStructsTest", "validator1_arrayOfStructsTest");
   xmlrpc_server_register_method($server, "countTheEntities", "validator1_countTheEntities");
   xmlrpc_server_register_method($server, "easyStructTest", "validator1_easyStructTest");
   xmlrpc_server_register_method($server, "echoStructTest", "validator1_echoStructTest");
   xmlrpc_server_register_method($server, "manyTypesTest", "validator1_manyTypesTest");
   xmlrpc_server_register_method($server, "moderateSizeArrayCheck", "validator1_moderateSizeArrayCheck");
   xmlrpc_server_register_method($server, "nestedStructTest", "validator1_nestedStructTest");
   xmlrpc_server_register_method($server, "simpleStructReturnTest", "validator1_simpleStructReturnTest");


   /* Now, let's get the client's request from the post data.... */
   $request_xml = $HTTP_RAW_POST_DATA;
   if(!$request_xml) {
      $request_xml = $HTTP_GET_VARS[xml];
   }
   if(!$request_xml) {
      echo "<h1>No XML input found!</h1>";
   }
   else {
      /* setup some (optional) output options */
      $display = array();
      if($HTTP_POST_VARS[verbosity]) {
         $display[verbosity] = $HTTP_POST_VARS[verbosity];
      }
      if($HTTP_POST_VARS[escaping]) {
         $display[escaping] = $HTTP_POST_VARS[escaping];
      }
      else {
         $display[escaping] = array("non-ascii", "markup");
      }
      if($HTTP_POST_VARS[version]) {
         $display[version] = $HTTP_POST_VARS[version];
      }
      if($HTTP_POST_VARS[encoding]) {
         $display[encoding] = $HTTP_POST_VARS[encoding];
      }
      if($HTTP_POST_VARS[output_type]) {
         $display[output_type] = $HTTP_POST_VARS[output_type];
      }

      /* handle the request */
      $response = xmlrpc_server_call_method($server, $request_xml, $response, $display);
 
      if($HTTP_POST_VARS[disp] === "html") {
         if($HTTP_POST_VARS[output_type] === "php") {
            echo "<xmp>\n";
            var_dump($response);
            echo "\n</xmp>\n";
         }
         else {
            echo "<xmp>\n$response\n</xmp>\n";
         }
      }
      else {
         echo "$response";
      }
   }

?>
