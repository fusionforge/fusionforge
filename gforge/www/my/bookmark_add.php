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


require_once('../env.inc.php');
require_once $gfwww.'include/pre.php';
require_once $gfwww.'include/bookmarks.php';

site_user_header(array("title"=>_('My Personal Page')));

$bookmark_url = trim(getStringFromRequest('bookmark_url'));
$bookmark_title = trim(getStringFromRequest('bookmark_title'));

if (getStringFromRequest('submit') && $bookmark_url && $bookmark_title) {

	printf(_('Added bookmark for <strong>%1$s</strong> with title <strong>%2$s</strong>'), htmlspecialchars(stripslashes($bookmark_url)),htmlspecialchars(stripslashes($bookmark_title))).".<p>&nbsp;</p>";

	bookmark_add ($bookmark_url, $bookmark_title);
	print "<a href=\"$bookmark_url\">"._('Visit the bookmarked page')."</a> - ";
	print "<a href=\"/my/\">"._('Back to your homepage')."</a>";

} else {
	?>
	<form action="<?php echo getStringFromServer('PHP_SELF'); ?>" method="post">
	<p><?php echo _('Bookmark URL') ?>:<br />
	<input type="text" name="bookmark_url" value="http://" />
	</p>
	<p><?php echo _('Bookmark Title') ?>:<br />
	<input type="text" name="bookmark_title" value="" />
	</p>
	<p><input type="submit" name="submit" value="<?php echo _('Submit') ?>" /></p>
	</form>
	<?php
}

site_user_footer(array());

?>
