<?php
/**
  *
  * "API" Page to get current session hash
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */


require_once('squal_pre.php');

$success=session_login_valid($user,$pass);

if ($success) {
	echo $session_ser;
} else {
	echo 'ERROR - '.$feedback;
}

?>
