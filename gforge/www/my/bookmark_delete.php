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

site_user_header(array("title"=>$Language->getText('my_bookmark_delete','title'),'pagename'=>'my_bookmark_delete'));

if ($bookmark_id) {
	bookmark_delete ($bookmark_id);
	print $Language->getText('my_bookmark_delete','bookmark_deleted')."<p><a href=\"/my/\">".$Language->getText('my_bookmark','return')."</a></p>";
}

site_user_footer(array());

?>
