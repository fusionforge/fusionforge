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

site_user_header(array("title"=>$Language->getText('my_bookmark_add','section'),'pagename'=>'my_bookmark_add'));

if ($bookmark_url) {
	print $Language->getText('my_bookmark_add','added_bookmark', array($bookmark_url,$bookmark_title)).".<p>&nbsp;</p>";

	bookmark_add ($bookmark_url, $bookmark_title);
	print "<a href=\"$bookmark_url\">".$Language->getText('my_bookmark_add','visit_page')."</a> - ";
	print "<a href=\"/my/\">".$Language->getText('my_bookmark_add','back')."</a>";
} else {
	?>
	<form method="post">
	<p><?php echo $Language->getText('my_bookmark_add','bookmark_url') ?>:<br />
	<input type="text" name="bookmark_url" value="http://" />
	</p>
	<p><?php echo $Language->getText('my_bookmark_add','bookmark_title') ?>:<br />
	<input type="text" name="bookmark_title" value="My Fav Site" />
	</p>
	<p><input type="submit" value="<?php echo $Language->getText('general','submit') ?>" /></p>
	</form>
	<?php
}

site_user_footer(array());

?>
