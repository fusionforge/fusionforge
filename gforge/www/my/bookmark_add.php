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

site_user_header(array("title"=>"Add New Bookmark",'pagename'=>'my_bookmark_add'));

if ($bookmark_url) {
	print "Added bookmark for <strong>'$bookmark_url'</strong> with title <strong>'$bookmark_title'</strong>.<p>&nbsp;</p>";

	bookmark_add ($bookmark_url, $bookmark_title);
	print "<a href=\"$bookmark_url\">Visit the bookmarked page</a> - ";
	print "<a href=\"/my/\">Back to your homepage</a>";
} else {
	?>
	<form method="post">
	<p>Bookmark URL:<br />
	<input type="text" name="bookmark_url" value="http://" />
	</p>
	<p>Bookmark Title:<br />
	<input type="text" name="bookmark_title" value="My Fav Site" />
	</p>
	<p><input type="submit" value=" Submit Form " /></p>
	</form>
	<?php
}

site_user_footer(array());

?>
