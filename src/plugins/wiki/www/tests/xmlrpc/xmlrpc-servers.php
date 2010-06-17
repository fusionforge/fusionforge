<?php
/**
  * List of various interop and wiki servers to test against.
  * interop for the basic library functionality, and then the wiki API.
  */

if (empty($GLOBALS['SERVER_NAME'])) {
    global $HTTP_SERVER_VARS;
    $GLOBALS['SERVER_NAME'] = $HTTP_SERVER_VARS['SERVER_NAME'];
    $GLOBALS['SERVER_PORT'] = $HTTP_SERVER_VARS['SERVER_PORT'];
    $GLOBALS['PHP_SELF'] = $HTTP_SERVER_VARS['SCRIPT_NAME'];
}

function get_user_server() {
   return
   array('title' => "user defined",
         'desc' => "Enter your own server to test against",
         'args' => null,
         'host' => $GLOBALS['HTTP_GET_VARS']['user_host'],
         'uri' => $GLOBALS['HTTP_GET_VARS']['user_uri'],
         'port' => ($GLOBALS['HTTP_GET_VARS']['user_port'] ? $GLOBALS['HTTP_GET_VARS']['user_port'] : 80),
         'type' => "user"
         );
}

function get_wiki_servers($include_user=true) {
   $list = 
   array (
      array('title' => " local ",
            'info_link' => "http://".$GLOBALS['SERVER_NAME'].dirname($GLOBALS['PHP_SELF'])."/../../index.php",
            'args' => null,
            'host' => $GLOBALS['SERVER_NAME'],
            'uri' => dirname($GLOBALS['PHP_SELF'])."/../../index.php",
            'port' => $GLOBALS['SERVER_PORT'],
            "default" => true
            ),
      array('title' => "local dba-test",
            'info_link' => "http://".$GLOBALS['SERVER_NAME']."/wiki/dba",
            'args' => null,
            'host' => $GLOBALS['SERVER_NAME'],
            'uri' => "/wiki/dba",
            'port' => 80
            ),
      array('title' => "local pear-test",
            'info_link' => "http://".$GLOBALS['SERVER_NAME']."/wiki/pear",
            'args' => null,
            'host' => $GLOBALS['SERVER_NAME'],
            'uri' => "/wiki/pear",
            'port' => 80
            ),
      array('title' => "local adodb-test",
            'info_link' => "http://".$GLOBALS['SERVER_NAME']."/wiki/adodb",
            'args' => null,
            'host' => $GLOBALS['SERVER_NAME'],
            'uri' => "/wiki/adodb",
            'port' => 80
            ),
      array('title' => "PhpWiki",
            'info_link' => "http://phpwiki.sourceforge.net/phpwiki/",
            'args' => null,
            'host' => "phpwiki.sourceforge.net",
            'uri' => "/phpwiki/",
            'port' => 80
            ),
      array('title' => "PhpWikiDemo",
            'info_link' => "http://phpwiki.sourceforge.net/demo/",
            'args' => null,
            'host' => "phpwiki.sourceforge.net",
            'uri' => "/demo/",
            'port' => 80
            ),
      );

   if($include_user) {
      $list[] = get_user_server();
   }

   return $list;
}

function get_intro_useful_servers($include_user=true) {
   $list = 
   array (
      array('title' => "xmlrpc-epi ( local! )",
            'info_link' => "http://".$GLOBALS['SERVER_NAME'].dirname($GLOBALS['PHP_SELF'])."/interop-server.php",
            'args' => null,
            'host' => $GLOBALS['SERVER_NAME'],
            'uri' => substr($GLOBALS['PHP_SELF'], 0, strrpos($GLOBALS['PHP_SELF'], "/") + 1) . "interop-server.php",
            'port' => $GLOBALS['SERVER_PORT'],
            "default" => true
            ),
      array('title' => "Meerkat",
            'info_link' => "http://www.oreillynet.com/meerkat/xml-rpc/",
            'args' => null,
            'host' => "www.oreillynet.com",
            'uri' => "/meerkat/xml-rpc/server.php",
            'port' => 80
            ),
      array('title' => "Usefulinc",
            'info_link' => "http://xmlrpc.usefulinc.com/php.html",
            'args' => null,
            'host' => "xmlrpc.usefulinc.com",
            'uri' => "/demo/server.php",
            'port' => 80
            ),
      );

   if($include_user) {
      $list[] = get_user_server();
   }

   return $list;
}

function get_introspection_servers($include_user=true) {
   $list = 
   array (
      array('title' => "xmlrpc-epi interop server",
            'info_link' => "http://".$GLOBALS['SERVER_NAME'].dirname($GLOBALS['PHP_SELF'])."/interop-server.php",
            'args' => null,
            'host' => $GLOBALS['SERVER_NAME'],
            'uri' => substr($GLOBALS['PHP_SELF'], 0, strrpos($GLOBALS['PHP_SELF'], "/") + 1) . "interop-server.php",
            'port' => $GLOBALS['SERVER_PORT'],
            "default" => true
            ),
      array('title' => "xmlrpc-epi validation server",
            'info_link' => "http://".$GLOBALS['SERVER_NAME'].dirname($GLOBALS['PHP_SELF'])."/validate.php",
            'args' => null,
            'host' => $GLOBALS['SERVER_NAME'],
            'uri' => substr($GLOBALS['PHP_SELF'], 0, strrpos($GLOBALS['PHP_SELF'], "/") + 1) . "validate.php",
            'port' => $GLOBALS['SERVER_PORT'],
            ),
      );

   if($include_user) {
      $list[] = get_user_server();
   }

   return $list;
}


function get_interop_servers($include_user=true) {
   global $HTTP_SERVER_VARS;
   $list = 
   array (
      array('title' => "local xmlrpc interop-server",
            'info_link' => "http://".$GLOBALS['SERVER_NAME'].dirname($GLOBALS['PHP_SELF'])."/interop-server.php",
            'desc' => "a php C extension utilizing the xmlrpc-epi library.  running locally.  by Dan Libby",
            'args' => "",
            'host' => $HTTP_SERVER_VARS['SERVER_NAME'],
            'uri' => dirname($HTTP_SERVER_VARS['SCRIPT_NAME']) . "/interop-server.php",
            'port' => $HTTP_SERVER_VARS['SERVER_PORT'],
            "default" => true
            ),
      array('title' => "Frontier 7.0b43",
            'info_link' => "http://groups.yahoo.com/group/xml-rpc/message/2585",
            'desc' => "Jake Savin's Server.  First Interop Node",
            'args' => "",
            'host' => "www.soapware.org",
            'uri' => "/RPC2",
            'port' => 80
            ),
      array('title' => "XMLRPC.Net",
            'info_link' => "http://aspx.securedomains.com/cookcomputing/interopechotests.aspx",
            'desc' => "client/server by Charles Cook",
            'args' => "",
            'host' => "aspx.securedomains.com",
            'uri' => "/cookcomputing/interopechotests.aspx",
            'port' => 80
            ),
      array('title' => "xmlrpc-c",
            'info_link' => "http://xmlrpc-c.sourceforge.net",
            'desc' => "a C library by Eric Kidd",
            'args' => "",
            'host' => "xmlrpc-c.sourceforge.net",
            'uri' => "/cgi-bin/interop.cgi",
            'port' => 80
            ),
      array('title' => "Usefulinc",
            'info_link' => "http://xmlrpc.usefulinc.com/",
            'desc' => "a PHP script library by Edd Dumbill",
            'args' => "",
            'host' => "xmlrpc.usefulinc.com",
            'uri' => "/demo/server.php",
            'port' => 80
            ),
      array('title' => "XML-RPC for ASP",
            'info_link' => "http://aspxmlrpc.sourceforge.net",
            'args' => "",
            'host' => "www.wc.cc.va.us",
            'uri' => "/dtod/xmlrpc/testing/interop.asp",
            'port' => 80
            ),
      array('title' => "Frontier-RPC 0.07b3 (Perl)",
            'args' => "",
            'host' => "bitsko.slc.ut.us",
            'uri' => "/cgi-bin/interop.pl",
            'port' => 80
            ),
      array('title' => "XMLRPC::Lite, v0.50",
            'args' => "",
            'host' => "xmlrpc.soaplite.com",
            'uri' => "/interop.cgi",
            'port' => 80
            )
   );

   if($include_user) {
      $list[] = get_user_server();
   }

   return $list;
}

function print_servers_form($server_list, $action_url=false, $print_user=true) {
   $action = $action_url ? "action='$action_url'" : "";

   echo "<form method='get' $action>";

   foreach ($server_list as $key => $server) {
      $title = $server['title'];
      $link = $server['info_link'];
      $type = $server['type'];
      $port = $server['port'];
      $default = $server["default"] ? "checked" : "";
      if($link) {
         $title = "<a href='$link'>$title</a>";
      }
      echo "<input type='radio' name='server' value='$key' $default>&nbsp;$title<br>";
      
      if($type === "user") {
         echo "<DL><DT><DL><DT>host: <input type='text' name='user_host' size='50'></DT>" .
              "<DT>uri: <input type='text' name='user_uri' size='50'>" .
              "<DT>port: <input type='text' name='user_port' size='4' MAXLENGTH='4' VALUE='$port'></DT></DL></DT></DL>";
         
      }
   }
   if ($GLOBALS['HTTP_GET_VARS']['start_debug'])
       echo "<input type='hidden' name='start_debug' value='1'>";
   echo <<< END
   <h3>Verbosity level</h3>
      <input type='radio' name='debug' value='0' checked>none &nbsp;&nbsp;
      <input type='radio' name='debug' value='1'>some &nbsp;&nbsp;
      <input type='radio' name='debug' value='2'>much &nbsp;&nbsp;

	<h3>XML Serialization</h3>
		<input type='radio' name='version' value='xmlrpc' checked>XML-RPC &nbsp;&nbsp;
		<input type='radio' name='version' value='soap 1.1'>SOAP &nbsp;&nbsp;
		<input type='radio' name='version' value='simple'>simpleRPC &nbsp;&nbsp;
<p>
<input type='submit' value='Start test'>
</form>

END;
}

function get_server_from_user($server_list) {
   return $server_list[$GLOBALS['HTTP_GET_VARS']['server']];
}

function server_uri_vars() {
   extract($GLOBALS['HTTP_GET_VARS']);
   return "server=$server&user_host=$user_host&user_port=$user_port&user_uri=$user_uri";
}

function server_vars() {
   extract($GLOBALS['HTTP_GET_VARS']);
   return array(
      'server' => $server,
      'user_host' => $user_host,
      'user_port' => $user_port,
      'user_uri' => $user_uri
      );
}

?>