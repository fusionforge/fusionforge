<?php
/**
 * Bookmarks functions library.
 *
 * SourceForge: Breaking Down the Barriers to Open Source Development
 * Copyright 1999-2001 (c) VA Linux Systems
 * http://sourceforge.net
 *
 * @version   $Id$
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
	$result = db_query("INSERT into user_bookmarks (user_id, bookmark_url, "
		. "bookmark_title) values ('".user_getid()."', '".htmlentities($bookmark_url)."', "
		. "'".htmlspecialchars($bookmark_title)."');");
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
	db_query("UPDATE user_bookmarks SET bookmark_url='".htmlentities($bookmark_url)."', "
		."bookmark_title='".htmlspecialchars($bookmark_title)."' where bookmark_id='$bookmark_id' AND user_id='". user_getid() ."'");
}

/**
 * bookmark_deleted() - Delete an existing bookmark
 *
 * @param		int		The bookmark's ID
 */
function bookmark_delete ($bookmark_id) {
	db_query("DELETE from user_bookmarks WHERE bookmark_id='$bookmark_id' "
		. "and user_id='". user_getid() ."'");
}

?>
