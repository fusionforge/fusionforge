<?php
//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net
//

require ('pre.php');
require ('bookmarks.php');

site_user_header(array("title"=>"Delete Bookmark"));

print "<H3>Delete Bookmark</H3>";

if ($bookmark_id) {
	bookmark_delete ($bookmark_id);
	print "Bookmark deleted.<P><A HREF=\"/my/\">Return</A>";
}

site_user_footer(array());

?>
