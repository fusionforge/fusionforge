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

site_user_header(array("title"=>".$Language->getText('my_bookmark_edit','title').",'pagename'=>'my_bookmark_edit'));

if ($bookmark_url && $bookmark_title) {
	bookmark_edit($bookmark_id, $bookmark_url, $bookmark_title);
}

$result = db_query("SELECT * from user_bookmarks where "
	. "bookmark_id='".$bookmark_id."' and user_id='".user_getid()."'");
if ($result) {
	$bookmark_url = db_result($result,0,'bookmark_url');
	$bookmark_title = db_result($result,0,'bookmark_title');
}
?>
<form method="post">
<p><?php echo $Language->getText('my_bookmark_add','bookmark_url') ?>:<br />
<input type="text" name="bookmark_url" value="<?php echo $bookmark_url; ?>" />
</p>
<p><?php echo $Language->getText('my_bookmark_add','bookmark_title') ?>::<br />
<input type="text" name="bookmark_title" value="<?php echo $bookmark_title; ?>" />
</p>
<p><input type="submit" value=" <?php echo $Language->getText('general','submit') ?> "></p>
</form>
<?php

print "<p><a href=\"/my/\">".$Language->getText('my_bookmark','return')."</a>";

site_user_footer(array());

?>
