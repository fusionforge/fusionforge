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

/*

	MUST USE SSL

	params: $user, $pass

	returns: either valid session_hash or ERROR string

*/

if (!session_issecure()) {
	//force use of SSL for login
	echo 'ERROR - MUST USE SSL';
	exit;
}

$success=session_login_valid($user,$pass);

if ($success) {
	echo $session_hash;
} else {
	echo 'ERROR - '.$feedback;
}

?>
