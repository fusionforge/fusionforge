<?php // $Id: xmlrpc_utils.php 7181 2009-10-05 14:25:48Z vargenau $

// This file has moved. keeping below for legacy compat reasons.

include_once("../../lib/XMLRPC/utils.php");

function xi_format($value) {
   include_once("./xmlrpc-introspect.php");
   return format_describe_methods_result($value);
}

?>
