<?php
/**
  *
  * Gforge cvsweb php wrapper
  *
  * Copyright 2003 (c) Gforge 
  * http://gforge.org
  *
  * @version   $Id$
  *
  */

require_once('www/include/pre.php');    // Initial db and session library, opens session

if ("${contenttype}" != "text/plain") {
	$HTML->header(array('title'=>$Language->getText('index','welcome'),'pagename'=>'home'));
} else {
	header("Content-type: $contenttype" );
}

/*
echo "<H3>QUERY_STRING    =====> $QUERY_STRING <=====</H3>";
echo "<H3>PATH_INFO       =====> $PATH_INFO <=====</H3>";
echo "<H3>HTTP_USER_AGENT =====> $HTTP_USER_AGENT <=====</H3>";
echo "<H3>SCRIPT_NAME     =====> $SCRIPT_NAME <=====</H3>";
echo "<H3>contenttype     =====> ${contenttype} <=====</H3>";
*/

passthru("PHPWRAPPER=$SCRIPT_NAME /usr/lib/gforge/plugins/scmcvs/cgi-bin/cvsweb.cgi \"$PATH_INFO\" \"$QUERY_STRING\" ");
//putenv("PHPWRAPPER=/scm/cvsweb.php");
//passthru("/usr/lib/gforge/cgi-bin/cvsweb.cgi \"$PATH_INFO\" \"$QUERY_STRING\" ");
//passthru("PHPWRAPPER=/scm/cvsweb.php /usr/lib/gforge/cgi-bin/cvsweb.cgi \"$PATH_INFO\" \"$QUERY_STRING\" ");

if ("$contenttype" != "text/plain") {
$HTML->footer(array());
}

?>
