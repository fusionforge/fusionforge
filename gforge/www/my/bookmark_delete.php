<?php
/**
  *
  * SourceForge User's Personal Page
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */


require_once('pre.php');
require_once('bookmarks.php');

if ($bookmark_id) {
	bookmark_delete ($bookmark_id);
	session_redirect('/my/');
}

?>
