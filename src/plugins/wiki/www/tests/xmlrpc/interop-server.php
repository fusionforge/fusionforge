<?php // $Id: interop-server.php 7181 2009-10-05 14:25:48Z vargenau $

include("xmlrpc_utils.php");

/* echos whatever it receives */
function method_echo($method, $params) {
  // we use array_pop instead of $params[0] because it works with either
  // soap (named params) or xmlrpc (ordered params)
	$foo = array_pop($params);
	//var_dump($foo);
  return $foo;
}

/* takes no params, returns none. */
function method_echo_void($method, $params) {
}

/* takes no params, returns a random int */
function method_no_in_params($method, $params) {
	return (int)5;
}



/* describes toolkit */
function method_toolkit($method, $params) {
   // outer array = params, inner = struct.
   return array(toolkitDocsUrl => "http://xmlrpc-epi.sourceforge.net/",
                toolkitName => "xmlrpc-epi-php",
                toolkitVersion => "0.26",   // (need to implement xmlrpc_get_version() 
                toolkitOperatingSystem => $GLOBALS[MACHTYPE]
                );
}
 
/*
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

$request_xml = $HTTP_RAW_POST_DATA;
if(!$request_xml) {
  $request_xml = $HTTP_POST_VARS[xml];
}

if(!$request_xml) {
  echo "<h1>No XML input found!</h1>";
}
else {
    // create server
    $xmlrpc_server = xmlrpc_server_create();
    
    if($xmlrpc_server) {
        // register xmlrpc methods
        xmlrpc_server_register_method($xmlrpc_server, "interopEchoTests.echoBoolean", "method_echo");
        xmlrpc_server_register_method($xmlrpc_server, "interopEchoTests.echoString", "method_echo");
        xmlrpc_server_register_method($xmlrpc_server, "interopEchoTests.echoInteger", "method_echo");
        xmlrpc_server_register_method($xmlrpc_server, "interopEchoTests.echoFloat", "method_echo");
        xmlrpc_server_register_method($xmlrpc_server, "interopEchoTests.echoBase64", "method_echo");
        xmlrpc_server_register_method($xmlrpc_server, "interopEchoTests.echoDate", "method_echo");
        xmlrpc_server_register_method($xmlrpc_server, "interopEchoTests.echoValue", "method_echo");
        xmlrpc_server_register_method($xmlrpc_server, "interopEchoTests.echoStruct", "method_echo");
        xmlrpc_server_register_method($xmlrpc_server, "interopEchoTests.echoStringArray", "method_echo");
        xmlrpc_server_register_method($xmlrpc_server, "interopEchoTests.echoIntegerArray", "method_echo");
        xmlrpc_server_register_method($xmlrpc_server, "interopEchoTests.echoFloatArray", "method_echo");
        xmlrpc_server_register_method($xmlrpc_server, "interopEchoTests.echoStructArray", "method_echo");
        xmlrpc_server_register_method($xmlrpc_server, "interopEchoTests.echoVoid", "method_echo_void");
        xmlrpc_server_register_method($xmlrpc_server, "interopEchoTests.whichToolkit", "method_toolkit");
        xmlrpc_server_register_method($xmlrpc_server, "interopEchoTests.noInParams", "method_no_in_params");

		  // soap methods  (interop test naming conventions are slightly different for soap)
        xmlrpc_server_register_method($xmlrpc_server, "echoBoolean", "method_echo");
        xmlrpc_server_register_method($xmlrpc_server, "echoString", "method_echo");
        xmlrpc_server_register_method($xmlrpc_server, "echoInteger", "method_echo");
        xmlrpc_server_register_method($xmlrpc_server, "echoFloat", "method_echo");
        xmlrpc_server_register_method($xmlrpc_server, "echoBase64", "method_echo");
        xmlrpc_server_register_method($xmlrpc_server, "echoDate", "method_echo");
        xmlrpc_server_register_method($xmlrpc_server, "echoValue", "method_echo");
        xmlrpc_server_register_method($xmlrpc_server, "echoStruct", "method_echo");
        xmlrpc_server_register_method($xmlrpc_server, "echoStringArray", "method_echo");
        xmlrpc_server_register_method($xmlrpc_server, "echoIntegerArray", "method_echo");
        xmlrpc_server_register_method($xmlrpc_server, "echoFloatArray", "method_echo");
        xmlrpc_server_register_method($xmlrpc_server, "echoStructArray", "method_echo");
        xmlrpc_server_register_method($xmlrpc_server, "echoVoid", "method_echo_void");
        xmlrpc_server_register_method($xmlrpc_server, "whichToolkit", "method_toolkit");
        xmlrpc_server_register_method($xmlrpc_server, "noInParams", "method_no_in_params");


        xmlrpc_server_register_introspection_callback($xmlrpc_server, "introspection_cb");

		  //$val =  xmlrpc_decode($request_xml, &$method);
		  //echo "xml: $request_xml\n";
		  //echo "method: $method\nvar: ";
		  //print_r($val);
        // parse xml and call method
        echo xmlrpc_server_call_method($xmlrpc_server, $request_xml, $response, array(output_type => "xml", version => "auto"));
    
        // free server resources
        $success = xmlrpc_server_destroy($xmlrpc_server);
    }
}



/********************
* API Documentation *
********************/

function introspection_cb($method) {
   /* if our xml is not parsed correctly, a php warning will be displayed.
    * however, very little structural validation is done, so we must be careful.
    * make sure to test this xml for syntax errors by calling system.describeMethods()
    *  after making any changes.
    */

   $methods = array("boolean", "string", "integer", "float", "base64", "date", "value", "struct");
   $type_map = array(integer => "int", float => "double", date => "datetime", value => "any");

   foreach($methods as $method) {
      $title = ucfirst($method);
      $type = $type_map[$method] ? $type_map[$method] : $method;

   $xml_methods .= <<< END
<methodDescription name='interopEchoTests.echo$title'>

 <author>Dan Libby &lt;dan@libby.com&gt;</author>
 <purpose>echos $type parameter back to caller</purpose>
 <version>1.0</version>

 <signatures>

  <signature>
   <params>
    <value type='$type' desc='$type value of caller&apos;s choosing'/>
   </params>
   <returns>
    <value type='$type' desc='same value that was passed in'/>
   </returns>
  </signature>

 </signatures>

</methodDescription>
END;


   }

   $methods = array("string", "integer", "float", "struct");
   foreach($methods as $method) {
      $title = ucfirst($method) . "Array";
      $type = $type_map[$method] ? $type_map[$method] : $method;

   $xml_methods .= <<< END
<methodDescription name='interopEchoTests.echo$title'>

 <author>Dan Libby &lt;dan@libby.com&gt;</author>
 <purpose>echos array of $type parameter back to caller</purpose>
 <version>1.0</version>

 <signatures>

  <signature>
   <params>
    <value type='array' desc='an array of $type values'>
     <value type='$type' desc='$type value(s) of caller&apos;s choosing'/>
    </value>
   </params>
   <returns>
    <value type='array' desc='same array of $type values that was passed in'>
     <value type='$type' desc='same value(s) that were passed in'/>
    </value>
   </returns>
  </signature>

 </signatures>

</methodDescription>
END;


   }

   return <<< END
<?xml version='1.0'?>

<introspection version='1.0'>

 <methodList>

 $xml_methods

<methodDescription name='interopEchoTests.whichToolkit'>

 <author>Dan Libby &lt;dan@libby.com&gt;</author>
 <purpose>returns information about the xml-rpc library in use</purpose>
 <version>1.0</version>

 <signatures>

  <signature>
   <returns>
    <value type='struct' desc='toolkit info'> 
     <value type='string' name='toolkitDocsUrl' desc='url of library documentation'/>
     <value type='string' name='toolkitName' desc='name of library'/>
     <value type='string' name='toolkitVersion' desc='version # of library'/>
     <value type='string' name='toolkitOperatingSystem' desc='operating system of host machine'/>
    </value>
   </returns>
  </signature>

 </signatures>

</methodDescription>

<methodDescription name='interopEchoTests.noInParams'>

 <author>Dan Libby &lt;dan@libby.com&gt;</author>
 <purpose>tests calling methods with no parameters</purpose>
 <version>1.0</version>

 <signatures>

  <signature>
   <returns>
    <value type='int' desc='a random integer'/>
   </returns>
  </signature>

 </signatures>

</methodDescription>


 </methodList>
</introspection>
END;

}



?>
