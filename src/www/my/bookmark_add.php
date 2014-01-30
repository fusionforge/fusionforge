<?php
/**
 * User's bookmark Page
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 *
 * This file is part of FusionForge. FusionForge is free software;
 * you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or (at your option)
 * any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with FusionForge; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

require_once '../env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'include/bookmarks.php';

site_user_header(array("title"=>_('Add a new Bookmark')));

$bookmark_url = trim(getStringFromRequest('bookmark_url'));
$bookmark_title = trim(getStringFromRequest('bookmark_title'));

if (getStringFromRequest('submit') && $bookmark_url && $bookmark_title) {

	echo "<p>\n";
	printf(_('Added bookmark for <strong>%1$s</strong> with title <strong>%2$s</strong>'),
		htmlspecialchars($bookmark_url),
		htmlspecialchars($bookmark_title));
	echo "</p>\n";

	bookmark_add ($bookmark_url, $bookmark_title);
	echo "<p>\n";
	print "<a href=\"$bookmark_url\">"._('Visit the bookmarked page')."</a>";
	echo "</p>\n";
	echo "<p>\n";
	print "<a href=\"/my/\">"._('Back to your homepage')."</a>";
	echo "</p>\n";

} else {
	?>
	<form action="<?php echo getStringFromServer('PHP_SELF'); ?>" method="post">
	<p>
		<label for="bookmark_url">
			<?php echo _('Bookmark URL')._(':'); ?><br />
		</label>
		<input id="bookmark_url" required="required" type="url" name="bookmark_url" value="http://" />
	</p>
	<p>
		<label for="bookmark_title">
			<?php echo _('Bookmark Title')._(':'); ?><br />
		</label>
		<input id="bookmark_title" required="required" type="text" name="bookmark_title" value="" />
	</p>
	<p><input type="submit" name="submit" value="<?php echo _('Submit') ?>" /></p>
	</form>
	<?php
}

site_user_footer();
