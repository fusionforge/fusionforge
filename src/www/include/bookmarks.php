<?php
/**
 * Bookmarks functions library.
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * http://fusionforge.org
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

/**
 * bookmark_add() - Add a new bookmark
 *
 * @param		string	The bookmark's URL
 * @param		string	The bookmark's title
 */
function bookmark_add ($bookmark_url, $bookmark_title="") {
	if (!$bookmark_title) {
		$bookmark_title = $bookmark_url;
	}
	$result = db_query_params ('INSERT into user_bookmarks (user_id, bookmark_url, bookmark_title) values ($1, $2, $3)',
				   array (user_getid(),
					  htmlentities($bookmark_url),
					  htmlspecialchars($bookmark_title)));
	if (!$result) {
		echo db_error();
	}
}

/**
 * bookmark_edit() - Edit an existing bookmark
 *
 * @param		int		The bookmark's ID
 * @param		string	The new or existing bookmark URL
 * @param		string	The new or existing bookmark title
 */
function bookmark_edit ($bookmark_id, $bookmark_url, $bookmark_title) {
	$result = db_query_params ('UPDATE user_bookmarks SET bookmark_url=$1, bookmark_title=$2 WHERE bookmark_id=$3 AND user_id=$4',
				   array (htmlentities($bookmark_url),
					  htmlspecialchars($bookmark_title),
					  $bookmark_id,
					  user_getid()));
	if (!$result) {
		echo db_error();
		return false;
	} else {
		return true;
	}
}

/**
 * bookmark_deleted() - Delete an existing bookmark
 *
 * @param		int		The bookmark's ID
 */
function bookmark_delete ($bookmark_id) {
	db_query_params ('DELETE from user_bookmarks WHERE bookmark_id=$1 AND user_id=$2',
			 array ($bookmark_id,
				user_getid()));
}

?>
