<?php
/**
 * SourceForge User's bookmark Page
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 *
 * @version   $Id$
 *
 * This file is part of GForge.
 *
 * GForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */


require_once('pre.php');
require_once('bookmarks.php');

site_user_header(array("title"=>$Language->getText('my_bookmark_add','section')));

if ($bookmark_url) {

	print $Language->getText('my_bookmark_add','added_bookmark', array($bookmark_url,$bookmark_title)).".<p>&nbsp;</p>";

	bookmark_add ($bookmark_url, $bookmark_title);
	print "<a href=\"$bookmark_url\">".$Language->getText('my_bookmark_add','visit_page')."</a> - ";
	print "<a href=\"/my/\">".$Language->getText('my_bookmark_add','back')."</a>";

} else {
	?>
	<form action="<?php echo $PHP_SELF; ?>" method="post">
	<p><?php echo $Language->getText('my_bookmark_add','bookmark_url') ?>:<br />
	<input type="text" name="bookmark_url" value="http://" />
	</p>
	<p><?php echo $Language->getText('my_bookmark_add','bookmark_title') ?>:<br />
	<input type="text" name="bookmark_title" value="" />
	</p>
	<p><input type="submit" value="<?php echo $Language->getText('general','submit') ?>" /></p>
	</form>
	<?php
}

site_user_footer(array());

?>
