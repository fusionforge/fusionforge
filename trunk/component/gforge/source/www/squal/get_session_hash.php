<?php
/**
  *
  * "API" Page to get current session hash
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id: get_session_hash.php 6506 2008-05-27 20:56:57Z aljeux $
  *
  */


require_once $gfwww.'include/squal_pre.php';

$success=session_login_valid($user,$pass);

if ($success) {
	echo $session_ser;
} else {
	echo 'ERROR - '.$feedback;
}

?>
