<?php
/**
  *
  * SourceForge Developer's Page
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */


/*
	Developer Info Page
	Written by dtype Oct 1999
*/

if (!$user_id) {
	$user_id=$form_dev;
}

require_once('pre.php');

header("Location: /users/". user_getname($user_id) ."/");

?>
