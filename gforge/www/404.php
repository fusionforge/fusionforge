<?php
/**
  *
  * SourceForge HTTP 404 (Document Not Found) Page 
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */


require_once('pre.php');    // Initial db and session library, opens session

$HTML->header(array('title'=>"Requested Page not Found (Error 404)"));

if (session_issecure()) {
	echo "<a href=\"https://$GLOBALS[sys_default_domain]\">";
} else {
	echo "<a href=\"http://$GLOBALS[sys_default_domain]\">";
}

echo "<center><h1>PAGE NOT FOUND</h1>";

echo "<p>";

echo $HTML->searchBox();

echo "<p></center>";

$HTML->footer(array());

?>
