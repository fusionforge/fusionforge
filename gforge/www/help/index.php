<?php
/**
  *
  * SourceForge Help Facility
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */


require_once('pre.php');

$HTML->header(array(title=>$Language->getText('help','title',array($GLOBALS['sys_name']))));

print "<p>" .$Language->getText('help','page_information')."</p>";
/**
print "<p>Page Information</p>";
*/

$HTML->footer(array());

?>
