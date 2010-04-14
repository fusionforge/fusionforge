<?php
/**
 * GForge News Facility
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * The rest Copyright 2002-2004 (c) GForge Team
 * http://gforge.org/
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
require_once $gfwww.'news/news_utils.php';
require_once $gfcommon.'forum/Forum.class.php';

$group_id = getIntFromRequest('group_id');
$limit = getIntFromRequest('limit');
$offset = getIntFromRequest('offset');
$feedback = htmlspecialchars(getStringFromRequest('feedback'));

news_header(array('title'=>_('News')));
echo '<h1>' . _('News') . '</h1>';

plugin_hook ("blocks", "news index");

echo _('<p>Choose a News item and you can browse, search, and post messages.</p>');

/*
	Put the result set (list of forums for this group) into a column with folders
*/
if ( !$group_id || $group_id < 0 || !is_numeric($group_id) ) {
	$group_id = 0;
}
if ( !$offset || $offset < 0 || !is_numeric($offset) ) {
	$offset = 0;
}
if ( !$limit || $limit < 0 || $limit > 50 || !is_numeric($limit) ) {
	$limit = 50;
}

if ($group_id && ($group_id != $sys_news_group)) {
	$result = db_query_params ('SELECT * FROM news_bytes WHERE group_id=$1 AND is_approved <> 4 ORDER BY post_date DESC',
				   array ($group_id),
				   $limit+1,
				   $offset);
} else {
	$result = db_query_params ('SELECT * FROM news_bytes WHERE is_approved=1 ORDER BY post_date DESC',
				   array ());
}

$rows=db_numrows($result);
$more=0;
if ($rows>$limit) {
	$rows=$limit;
	$more=1;
}

if ($rows < 1) {
	if ($group_id) {
		echo '<h2>'.sprintf(_('No News Found For %s'),group_getname($group_id)).'</h2>';
	} else {
		echo '<h2>'._('No News Found').'</h2>';
	}
	echo '
		<p>' . _('No items were found') . '</p>';
	echo db_error();
} else {
	echo news_show_latest($group_id,10,true,false,false,-1, true);
}

news_footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
